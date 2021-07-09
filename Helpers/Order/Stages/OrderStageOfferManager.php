<?php


namespace Order\Stages;

use Core\DB\DB;
use Core\Exception\SimpleJsonException;
use Core\Exception\UnauthorizedException;
use Illuminate\Support\Collection;
use Model\Operation;
use Model\Order;
use Model\OrderStages\ChangeLog;
use Model\OrderStages\OrderStage;
use Model\OrderStages\OrderStageOffer;
use Model\OrderStages\TrackStage;
use Model\Track;
use Track\Type;

class OrderStageOfferManager {

	/**
	 * Максимальное количество этапов в предложении по заказу
	 */
	const OFFER_MAX_STAGES = 10;

	/**
	 * Сохранение предложений по этапам заказа текущего пользователя
	 * (данные должы быть предварительно провалидированы!)
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param array $offers Массив данных предложений
	 *
	 * @return OrderStageOffer[]
	 * @throws \Throwable
	 */
	public static function saveOffers(int $orderId, array $offers) {
		$userId = \UserManager::getCurrentUserId();

		$savedOffers = [];
		foreach ($offers as $item) {
			if ($item[OrderStageOffer::FIELD_ORDER_STAGE_ID] && in_array($item[OrderStageOffer::FIELD_ACTION], OrderStageOffer::CHANGE_ACTIONS)) {
				$offer = OrderStageOffer::firstOrNew([
					OrderStageOffer::FIELD_USER_ID => $userId,
					OrderStageOffer::FIELD_ORDER_STAGE_ID => $item[OrderStageOffer::FIELD_ORDER_STAGE_ID]
				]);
			} else {
				$offer = new OrderStageOffer();
			}

			$offer->action = $item[OrderStageOffer::FIELD_ACTION];
			$offer->status = OrderStageOffer::STATUS_NEW;
			$offer->order_id = $orderId;
			$offer->title = $item[OrderStageOffer::FIELD_TITLE];
			$offer->user_id = $userId;
			if ($item[OrderStageOffer::FIELD_PAYER_PRICE]) {
				$offer->payer_price = $item[OrderStageOffer::FIELD_PAYER_PRICE];
			}
			if ($item[OrderStageOffer::FIELD_ORDER_STAGE_ID]) {
				$offer->order_stage_id = $item[OrderStageOffer::FIELD_ORDER_STAGE_ID];
			}
			$offer->save();
			$savedOffers[] = $offer;
		}

		return $savedOffers;
	}

	/**
	 * Принять предложения
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param array $offersIds Идентификаторы
	 * @param int $userId Идентификатор пользователя который принимает предложения
	 * @param int $days Изменение времени заказа
	 *
	 * @return Collection|OrderStage[] - созданные этапы
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public static function approveOffers(int $orderId, array $offersIds, int $userId, int $days): Collection {
		$newStages = collect();

		$needToSetDone = false;

		$order = Order::find($orderId);

		if (!$order->isWorkerOrPayer($userId)) {
			throw new UnauthorizedException();
		}

		/**
		 * @var \Illuminate\Database\Eloquent\Collection|OrderStageOffer[] $offers
		 */
		$offers = $order->stageOffers()
			->lockForUpdate()
			->where(OrderStageOffer::FIELD_STATUS, OrderStageOffer::STATUS_NEW)
			->where(OrderStageOffer::FIELD_USER_ID, "!=", $userId)
			->findMany($offersIds);

		if ($offers->isEmpty()) {
			throw new SimpleJsonException(\Translations::t("Предложения не найдены"));
		}

		$turnover = \OrderManager::getTurnover($order->worker_id, $order->USERID, $order->getLang());

		$stages = $order->stages;

		// Учет уже зарезервированной суммы заказа в обороте по заказу
		if ($stages->isEmpty() && $order->isNotDone()) {
			$turnover += $order->price;
		} else {
			$turnover += $order->getReservedStages()->sum(OrderStage::FIELD_PAYER_PRICE);
		}

		// Если есть изменение срока заказа сделаем его сразу до изменения этапов в текущем заказе,
		// т.к. на это расчитаны методы для получение текущей и новой цены и добавления времени
		if ($days > 0) {
			$currentStagedPrice = OrderStageOfferManager::getCurrentStagesPrice($order);
			$newStagedPrice = OrderStageOfferManager::getNewStagesPrice($order, $offers->toArray());
			if (abs($currentStagedPrice - $newStagedPrice) >= 0.01) {
				if ($currentStagedPrice < $newStagedPrice) {
					self::addOrderTime($order, $days);
				} else {
					// В случае если уменьшение стоимости заказа то добавляем отрицательное время, т.е. вычитаем
					self::addOrderTime($order, $days * -1);
				}
			}
		}

		$wasDeleting = false;
		$stageNumber = 1;
		foreach ($stages as $key => $stage) {
			$stageNumber = $stage->number;
			if (! $stage->isNotReserved()) {
				continue; // Изменяем только этапы в изменяемых статусах
			}

			/**
			 * @var OrderStageOffer $offer
			 */
			$offer = $offers->firstWhere(OrderStageOffer::FIELD_ORDER_STAGE_ID, $stage->id);
			if ($offer) {
				if ($offer->action == OrderStageOffer::ACTION_DELETE) {
					ChangeLogManager::save($stage, ChangeLog::ACTION_DELETE);
					if ($order->in_work) {
						$stage->payer_unaccepted_change_date = \Helper::now();
						$stage->save();
					}
					$stage->delete();
					$wasDeleting = true;
					$offer->status = OrderStageOffer::STATUS_CONFIRM;
					$offer->save();
				} elseif ($offer->action == OrderStageOffer::ACTION_EDIT) {
					$turnover = $stage->fillFromOffer($offer, $turnover, $order);
					if ($fields = array_keys($stage->getDirty())) {
						ChangeLogManager::save($stage, ChangeLog::ACTION_EDIT, $fields);
					}
					$stage->save();
					$offer->status = OrderStageOffer::STATUS_CONFIRM;
					$offer->save();
				}
			} else {
				$turnover = $stage->setWorkerPriceByTurnover($turnover, $order);
				$stage->save();
			}
		}

		$offerAddStages = $offers->where(OrderStageOffer::FIELD_ACTION, OrderStageOffer::ACTION_ADD);

		// Если происходит добавление этапа в существующий заказ
		if ($stages->isEmpty() && !$order->isNew() && $offerAddStages->count()) {
			// Создадим первый этап из заказа
			$stageByOrder = OrderStage::makeFirstStageFromOrder($order);
			$stageByOrder->save();

			// Если заказ на проверке то нужно привязать этап к треку сдачи на проверку
			if ($order->isCheck()) {
				$lastInporgressCheckTrack = $order->tracks()
					->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CHECK)
					->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
					->orderByDesc(Track::FIELD_ID)
					->first();
				if ($lastInporgressCheckTrack instanceof Track) {
					TrackStage::simpleAssociate($lastInporgressCheckTrack->MID, [$stageByOrder->id]);
				}
			}

			// Привяжем все предыдущие операции по этому заказу к созданному первому этапу
			Operation::where(Operation::FIELD_ORDER_ID, $order->OID)
				->where(Operation::FIELD_IS_TIPS, 0)
				->update([Operation::FIELD_ORDER_STAGE_ID => $stageByOrder->id]);

			// Закрываем все открытые запросы на отмену заказа
			\TrackManager::closeCancelRequestsTracks($orderId);
			// Отменяем все предложения опций
			\TrackManager::declineAllOpenedExtraTracksWithoutNotification($orderId);

			// Проставим цену этапов для этапных заказов
			OrderStageManager::setStagesPrice($orderId);
		}

		// Создадим новые этапы
		foreach ($offers as $offer) {
			if ($offer->action == OrderStageOffer::ACTION_ADD) {
				$stage = new OrderStage();
				$stage->number = ++$stageNumber;
				$stage->status = OrderStage::STATUS_NOT_RESERVED;
				$stage->payer_unaccepted_change_date = \Helper::now();
				$turnover = $stage->fillFromOffer($offer, $turnover, $order);
				$stage->save();
				ChangeLogManager::save($stage, ChangeLog::ACTION_ADD);
				$offer->status = OrderStageOffer::STATUS_CONFIRM;
				$offer->save();

				$newStages->push($stage);
			}
		}

		$stagesIds = $offers->pluck(OrderStageOffer::FIELD_ORDER_ID)->filter()->toArray();
		if ($stagesIds) {
			// Отменяем конфликтующие собственные предложения
			$order->stageOffers()
				->where(OrderStageOffer::FIELD_STATUS, OrderStageOffer::STATUS_NEW)
				->where(OrderStageOffer::FIELD_USER_ID, "=", $userId)
				->whereKey($stagesIds)
				->update([
					OrderStageOffer::UPDATED_AT => \Helper::now(),
					OrderStageOffer::FIELD_STATUS => OrderStageOffer::STATUS_REJECT,
				]);
		}

		if (!$order->has_stages && $order->initial_offer_price <= 0) {
			// Если заказ изначально неэтапный нужно проставить initial_offer_price
			$order->initial_offer_price = $order->price;
		}

		// Актуализируем поля в order
		$order->load("stages");
		if ($order->isDone()) {
			$order->setAmountsByPaidStages();
		} else {
			$order->setAmountsByAllStages();
		}

		$order->has_stages = true; // Помечаем заказ как этапный

		// Проставим цену этапов для этапных заказов
		$order->setStagesPrice();
		$order->save();

		if ($wasDeleting) {
			$stages = OrderStageManager::resetStagesNumbers($order->OID);
			foreach ($stages as $stage) {
				ChangeLogManager::save($stage, ChangeLog::ACTION_EDIT, [OrderStage::FIELD_NUMBER]);
			}
			// Если после удалений остались только выполненные этапы
			if ($order->stages->count() && $order->stages->count() === $order->getPaidStages()->count()) {
				$needToSetDone = true;
			} elseif ($order->stages->count() === 1) {
				$firstStage = $order->stages->first();
				if ($firstStage instanceof OrderStage && !$order->kwork->isCustom()) {
					/*
					 * Если первый этап не завершен и это не индивидуальный кворк,
					 * то чтобы не заморачиватся с поддержкой этапов вместе с опциями
					 * удаляем первый этап и делаем заказ не поэтапным
					 */
					$firstStage->delete();
					$order->has_stages = false;
					$order->show_as_inprogress_for_worker = false;
					$order->has_payer_stages = false;
					$order->save();
					// Отвяжем операции от этапов
					Operation::where(Operation::FIELD_ORDER_ID, $order->OID)
						->update([Operation::FIELD_ORDER_STAGE_ID => null]);
					// Отвяжем от треков
					TrackStage::where(TrackStage::FIELD_ORDER_STAGE_ID, $firstStage->id)->delete();
				}
			}
		}

		if ($needToSetDone) {
			\OrderManager::setDoneTransactional($order);
		}

		return $newStages;
	}

	/**
	 * Является ли пользователь тестером этапов заказа
	 *
	 * @param int|null Идентификатор пользователя, если не задан - текущий пользователь
	 *
	 * @return bool
	 */
	public static function isTester(int $userId = null):bool {

		// Открываем для всех пока-что тут
		return false;

		if (is_null($userId)) {
			$userId = \UserManager::getCurrentUserId();
		}

		if (!$userId) {
			return false;
		}

		$testers = \App::config("order_stages_testers");

		return is_array($testers) && in_array($userId, $testers);
	}

	/**
	 * Получение новой цены заказа с учетом изменений по этапам
	 *
	 * @param \Model\Order $order Модель заказа
	 * @param array $offers Массив массивов изменений для проверки (не модели)
	 *
	 * @return float
	 */
	public static function getNewStagesPrice(Order $order, array $offers):float {
		$stages = $order->stages;

		$orderPrice = 0;
		// Если нет этапов значит цена заказа будет считаться от текущей цены заказа
		if (!$stages->count()) {
			$orderPrice = $order->price;
		}

		// Подсчет цены по существующим этапам
		foreach ($stages as $key => $stage) {
			if (!$stage->isNotReserved()) {
				$orderPrice += $stage->payer_price;
				continue; // Изменяем цену только в изменяемых статусах
			}

			$offer = array_first(array_filter($offers, function ($offer) use ($stage) {
				return $offer[OrderStageOffer::FIELD_ORDER_STAGE_ID] == $stage->id;
			}));
			if ($offer) {
				if ($offer[OrderStageOffer::FIELD_ACTION] == OrderStageOffer::ACTION_EDIT) {
					$orderPrice += $offer[OrderStageOffer::FIELD_PAYER_PRICE];
				} elseif ($offer[OrderStageOffer::FIELD_ACTION] == OrderStageOffer::ACTION_DELETE) {
					continue; // если удаление этапа то не прибавляем к цене заказа
				}
			} else {
				$orderPrice += $stage->payer_price;
			}
		}

		// Посчитаем увеличение по новым этапам
		foreach ($offers as $offer) {
			if ($offer[OrderStageOffer::FIELD_ACTION] == OrderStageOffer::ACTION_ADD) {
				$orderPrice += $offer[OrderStageOffer::FIELD_PAYER_PRICE];
			}
		}

		return $orderPrice;
	}

	/**
	 * Получить текущую стоимость заказа по всем этапам
	 * в выполненных заказах цена равна цене выполненных этапов,
	 * поэтому этот метод и нужен
	 *
	 * @param \Model\Order $order Модель заказа
	 *
	 * @return float
	 */
	public static function getCurrentStagesPrice(Order $order):float {
		$stages = $order->stages;
		if ($stages->count()) {
			return $stages->sum(OrderStage::FIELD_PAYER_PRICE);
		} else {
			return $order->price;
		}
	}

	/**
	 * Добавление времени к заказу
	 *
	 * @param \Model\Order $order Заказ
	 * @param int $days Количество дней которое добавляем
	 */
	private static function addOrderTime(Order $order, int $days) {
		// Сколько нужно добавить в секундах
		$addTime = \Helper::ONE_DAY * $days;

		/*
		 Если дедлайн был уже проставлен то
		 устанавливаем время выполнения равное фактически уже отработанному
		 времени по заказу плюс дополнительное время
		 (это время влияет на дедлайн но не отображется в заказе пользователям)
		 */
		if ($order->deadline) {
			// Реально отработанное время по заказу
			$workTime = $order->calculateWorkTime();
			if ($order->isDone()) {
				$order->duration = $workTime + $addTime;
			} else {
				$order->duration += $addTime;
			}
			$order->deadline = time() + $order->duration - $workTime;
		} else {
			// Если дедлайн не проставлен то просто добавим к текущему сроку
			$order->duration += $addTime;
		}
		$order->save();
	}

	/**
	 * Получить максимальное количество дней на которое можно уменьшить срок заказа
	 * для еще не начатых заказов
	 *
	 * @param int $initialDuration Первоначальный срок заказа в предложении продавца в секундах
	 * @param int $duration Текущий срок заказа в секундах
	 *
	 * @return int
	 */
	public static function getMaxDecreaseDayForOffer($initialDuration, $duration) {
		if (!$initialDuration) {
			$initialDuration = $duration;
		}
		$maxDecrease = floor(($duration - $initialDuration) / \Helper::ONE_DAY);
		if ($maxDecrease < 0) {
			$maxDecrease = 0;
		}
		return $maxDecrease;
	}

}