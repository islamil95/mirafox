<?php


namespace Order\Stages;

use Core\DB\DB;
use Core\Exception\SimpleJsonException;
use Currency\CurrencyExchanger;
use Enum\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Model\Order;
use Model\OrderStages\ChangeLog;
use Model\OrderStages\OrderStage;
use Model\OrderStages\TrackStage;
use Model\Track;
use Order\MyOrders;
use RuntimeException;
use Track\Type;

class OrderStageManager {

	/**
	 * Массив источников заказа для которых разрешены этапные заказа
	 */
	const PRIVATE_SOURCES = [
		\OrderManager::SOURCE_WANT_PRIVATE,
		\OrderManager::SOURCE_INBOX_PRIVATE,
		\OrderManager::SOURCE_INBOX_PRIVATE_INDIRECT,
	];
	/**
	 * Срок через который после перезапуска заказа нужно его отменять если его не взяли в работу в секундах
	 */
	const RESTARTED_AUTOCANCEL_THRESHOLD = \Helper::ONE_DAY;

	/**
	 * Сохранение новых провалидированных этапов (только для заказов без существующих этапов)
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param array $stages Массив данных этапов
	 *
	 * @return OrderStage[] Массив моделей сохраненных этапов
	 */
	public static function saveStages(int $orderId, array $stages) {
		if (empty($stages)) {
			return [];
		}
		$order = Order::find($orderId);
		if (is_null($order)) {
			return [];
		}
		ksort($stages);

		$stages = array_values($stages);

		$turnover = \OrderManager::getTurnover($order->worker_id, $order->USERID, $order->getLang());

		$savedStages = [];
		foreach ($stages as $key => $item) {
			$stage = new OrderStage();
			$stage->order_id = $orderId;
			$stage->currency_id = $order->currency_id;
			$stage->currency_rate = CurrencyExchanger::getInstance()->getCurrencyRateByLang($order->getLang());
			$stage->title = $item[OrderStage::FIELD_TITLE];
			$stage->number = $key + 1;
			$stage->status = OrderStage::STATUS_NOT_RESERVED;
			$stage->payer_price = $item[OrderStage::FIELD_PAYER_PRICE];
			$stage->payer_amount = CurrencyExchanger::getInstance()->convertByLang($stage->payer_price, $order->getLang(), $order->payer->lang);
			$comission = \OrderManager::calculateCommission($stage->payer_price, $turnover, $order->getLang());
			$stage->worker_price = $comission->priceWorker;
			$stage->worker_amount = CurrencyExchanger::getInstance()->convertByLang($stage->worker_price, $order->getLang(), $order->worker->lang);
			$turnover += $stage->payer_price;
			$stage->save();
			$savedStages[] = $stage;
		}

		// Проставим цену этапов для этапных заказов
		OrderStageManager::setStagesPrice($orderId);

		return $savedStages;
	}

	/**
	 * Разрешено ли разбитие заказа на этапы со старта
	 *
	 * @param string $sourceType Источник создания заказа
	 *
	 * @return bool
	 */
	public static function isOrderCanBeStaged(string $sourceType): bool {
		return in_array($sourceType, self::PRIVATE_SOURCES);
	}

	/**
	 * Есть ли в заказе этапы с менее 100 под которые зарезервированы средства
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return bool
	 */
	public static function isOrderHasReservedNotFullProgress(int $orderId) {
		return OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)
			->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
			->where(OrderStage::FIELD_PROGRESS, "<", OrderStage::PROGRESS_FULL)
			->exists();
	}

	/**
	 * Получение общего прогресса по отдельным прогрессам
	 *
	 * @param OrderStage[] $stages
	 *
	 * @return int
	 */
	public static function getOverallStagesProgress($stages) {
		// В прогрессе заказа не участвуют прогрессы возвращенных этапов
		$progressStages = array_filter($stages, function(OrderStage $stage) {
			return !$stage->isRefund();
		});
		$stagesCount = count($progressStages);
		if (!$stagesCount) {
			return 0;
		}

		$totalProgess = 0;
		foreach ($progressStages as $stage) {
			$totalProgess += $stage->progress;
		}

		$overallProgress = round($totalProgess / $stagesCount);

		if ($overallProgress > 100) {
			return 100;
		}

		return $overallProgress;
	}


	/**
	 * Обновляет порядковые номера этапов (необходимо после удаления)
	 *
	 * @param int $orderId
	 * @return OrderStage[]
	 */
	public static function resetStagesNumbers(int $orderId) {
		$stages = OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)
			->orderBy(OrderStage::FIELD_NUMBER)
			->orderBy(OrderStage::FIELD_ID)
			->get();

		$updatedStages = [];
		foreach ($stages as $key => $stage) {
			$stage->number = $key + 1;
			if (array_key_exists(OrderStage::FIELD_NUMBER, $stage->getDirty())) {
				$updatedStages[] = $stage;
			}
			$stage->save();
		}

		return $updatedStages;
	}

	/**
	 * Есть ли в заказе свежие безакцептно отредактированные продавцом этапы
	 *
	 * @param \Model\Order $order
	 *
	 * @return bool
	 */
	public static function isOrderStagesWithFreshUnacceptedPayerChanges(Order $order) {
		// Если уже загружены этапы поищем сначала в них чтобы не делать sql запрос
		if ($order->relationLoaded("stages")) {
			foreach ($order->stages as $stage) {
				if ($stage->hasFreshUnacceptedChanges()) {
					return true;
				}
			}
		}

		$threshold = \Helper::now(time() - OrderStage::PAYER_UNACCEPTED_CHANGES_THRESHOLD);

		// Если есть свежие безакцептно отредактированные продавцом этапы, без прогресса (в том числе удаленные)
		return OrderStage::withTrashed()
			->where(OrderStage::FIELD_ORDER_ID, $order->OID)
			->whereNotNull(OrderStage::FIELD_PAYER_UNACCEPTED_CHANGE_DATE)
			->where(OrderStage::FIELD_PAYER_UNACCEPTED_CHANGE_DATE, ">", $threshold)
			->where(OrderStage::FIELD_STATUS, "!=", OrderStage::STATUS_REFUND)
			->where(OrderStage::FIELD_PROGRESS, 0)
			->exists();
	}

	/**
	 * Количество дней через которое автоматически отменяется заказ в статусе "Требует оплаты"
	 *
	 * @return int
	 */
	public static function getUnpaidCancelDays(): int {
		return (int)\App::config(Config::ORDER_STAGES_UNPAID_CANCEL_DAYS);
	}

	/**
	 * Получение порогового значения даты перевода заказа в статус "Требует оплаты" для автоматической отмены
	 *
	 * @return \DateTime
	 */
	public static function getUnpaidCancelThresholdDatetime(): \DateTime {
		return date_create("-" . OrderStageManager::getUnpaidCancelDays() . " days");
	}

	/**
	 * Валидация указанных при отправке в арбитраж этапов
	 *
	 * @param \Model\Order $order Модель заказа
	 * @param array $stageIds Идентификаторы поданных пользователем этапов
	 *
	 * @return array
	 * @throws \Core\Exception\SimpleJsonException
	 */
	public static function checkArbitrageStageIds(Order $order, array $stageIds) {
		if ($order->has_stages) {
			$reservedStages = $order->getReservedStages();
			if (count($reservedStages) == 1) {
				$stageIds = [$reservedStages->first()->id];
			} else {
				if (empty($stageIds)) {
					throw new SimpleJsonException(\Translations::t("Нужно указать задачи"));
				}
				$selectedReservedStages = $reservedStages->whereIn(OrderStage::FIELD_ID, $stageIds);
				if (count($selectedReservedStages) != count($stageIds)) {
					throw new SimpleJsonException(\Translations::t("Указаны некорректные задачи"));
				}
			}
		}

		return $stageIds;
	}

	/**
	 * Есть ли у заказа оплаченные этапы
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return bool
	 */
	public static function isOrderHasPaidStages(int $orderId) {
		return OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)
			->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_PAID)
			->exists();
	}

	/**
	 * Вернуть средства по зарезервированным этапам
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $abusePoints Количество штрафных баллов (при отмене заказа)
	 *
	 * @return int|null Идентификатор последней операции
	 * @throws \Throwable
	 */
	public static function refundReservedStages(int $orderId, int $abusePoints = 0) {
		$lastOperationId = null;

		DB::transaction(function() use ($abusePoints, $orderId, &$lastOperationId) {
			$lastOperationId = null;

			// Выбираем с блокировкой на чтение чтобы не возможно было два раза сделать возврат по одному этапу
			$stages = OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)
				->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
				->lockForUpdate()
				->get();

			// Возвращаем деньги за все зарезервированные этапы транзакционно
			foreach ($stages as $orderStage) {
				$lastOperationId = \OrderManager::refund($orderId, $orderStage->payer_amount, true, $abusePoints, 0, $orderStage->currency_rate, $orderStage->id);
				if (!$lastOperationId) {
					throw new RuntimeException("Refund for stage failed");
				}
				$abusePoints = 0; // Штрафные баллы только в первой операции
				$orderStage->status = OrderStage::STATUS_REFUND;
				$orderStage->progress = 0;
				$orderStage->save();
			}
		});

		// Проставим цену этапов для этапных заказов
		OrderStageManager::setStagesPrice($orderId);

		return $lastOperationId;
	}

	/**
	 * Массовое получение дат последнего перезапуска заказа
	 *
	 * @param array $orderIds Массив идентификаторов заказов
	 *
	 * @return array [OrderId => restartUnixTime, ...]
	 */
	public static function getOrdersRestartTimes(array $orderIds): array {
		if (empty($orderIds)) {
			return [];
		}

		$restartTracks = Track::whereIn(Track::FIELD_ORDER_ID, $orderIds)
			->whereIn(Track::FIELD_TYPE, Type::getOrderRestartTypes())
			->get();
		$lastTimes = [];
		foreach ($restartTracks as $track) {
			if (!isset($lastTimes[$track->OID]) || $lastTimes[$track->OID] < $track->date_create_unix()) {
				$lastTimes[$track->OID] = $track->date_create_unix();
			}
		}
		return $lastTimes;
	}


	/**
	 * Установить аткуальную цену по этапам в замисимости от состояния заказа
	 * @param int $orderId Идентификатор заказа
	 */
	public static function setStagesPrice($orderId) {
		$order = Order::find($orderId);

		if ($order->has_stages) {
			$order->setStagesPrice();
			$order->save();
		}
	}

	/**
	 * Суммирование массивов по ключам
	 *
	 * @param array $first Первый массив элементов (ассоциативных массивов)
	 * @param array $second Второй массив элементов (ассоциативных массивов)
	 * @param array $skipFields Пропускаемые поля
	 *
	 * @return array
	 */
	public static function sumKeyArrays(array $first, array $second, array $skipFields = []): array {
		// Получим все ключи
		$allKeys = array_unique(array_merge(array_keys($first), array_keys($second)));

		$merged = [];
		foreach ($allKeys as $key) {
			if (isset($first[$key]) && isset($second[$key])) {
				// Если есть значения в обоих массивам, суммируем, кроме пропускаемых полей
				$mergedItem = [];
				foreach ($first[$key] as $keyField => $field) {
					if (in_array($keyField, $skipFields)) {
						$mergedItem[$keyField] = $first[$key][$keyField];
					} else {
						$mergedItem[$keyField] = $first[$key][$keyField] + $second[$key][$keyField];
					}
				}
			} else {
				// Иначе берем тот который есть
				$mergedItem = isset($first[$key]) ? $first[$key] : $second[$key];
			}
			$merged[$key] = $mergedItem;
		}

		return $merged;
	}

	/**
	 * Получение идентификаторов отмененных заказов с выполненными этапами
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param bool $forWorker Заказы как продавца, иначе как покупателя
	 *
	 * @return array
	 */
	public static function getCancelOrdersWithDoneStagesIds(int $userId, bool $forWorker) {
		$query = Order::query()
			->join(OrderStage::TABLE_NAME, OrderStage::TABLE_NAME . "." . OrderStage::FIELD_ORDER_ID, "=", Order::TABLE_NAME . "." . Order::FIELD_OID)
			->where(Order::TABLE_NAME . "." . Order::FIELD_STATUS, \OrderManager::STATUS_CANCEL)
			->where(OrderStage::TABLE_NAME . "." . OrderStage::FIELD_STATUS, OrderStage::STATUS_PAID);

		if ($forWorker) {
			$query->where(Order::TABLE_NAME . "." . Order::FIELD_WORKER_ID, $userId);
		} else {
			$query->where(Order::TABLE_NAME . "." . Order::FIELD_USERID, $userId);
		}

		return $query->distinct()
			->pluck(Order::TABLE_NAME . "." . Order::FIELD_OID)
			->toArray();
	}

	/**
	 * Суммирование массивов по ключам c предварительным созданием комбинированных ключей
	 *
	 * @param array $first Первый массив элементов (ассоциативных массивов)
	 * @param array $second Второй массив элементов (ассоциативных массивов)
	 * @param array $keys Массив названий ключевых полей
	 *
	 * @return array
	 */
	public static function sumKeyArraysWithPrepare(array $first, array $second, array $keys) {
		$first = self::makeCombinedKeys($first, $keys);
		$second = self::makeCombinedKeys($second, $keys);

		return self::sumKeyArrays($first, $second, $keys);
	}

	/**
	 * Сделать в массиве комбинированные ключи по определенным полям
	 *
	 * @param array $rows Массив строк
	 * @param array $fields Массив полей
	 *
	 * @return array
	 */
	public static function makeCombinedKeys(array $rows, array $fields) {
		if (empty($fields)) {
			throw new RuntimeException("Некорректное использование метода");
		}
		$keys = array_map(function ($row) use ($fields) {
			$values = [];
			foreach ($fields as $field) {
				$values[$field] = $row[$field];
			}
			return implode("_", $values);
		}, $rows);

		return array_combine($keys, $rows);
	}

	/**
	 * Суммирование плоских массивов по ключам
	 *
	 * @param array $first Первый массив
	 * @param array $second Второй массив
	 *
	 * @return array
	 */
	public static function sumFlatArraysByKeys(array $first, array $second) {
		$allKeys = array_unique(array_merge(array_keys($first), array_keys($second)));

		$merged = [];
		foreach ($allKeys as $key) {
			if (isset($first[$key]) && isset($second[$key])) {
				// Если есть значения в обоих массивам, суммируем
				$merged[$key] = $first[$key] + $second[$key];
			} else {
				// Иначе берем тот который есть
				$merged[$key] = isset($first[$key]) ? $first[$key] : $second[$key];
			}
		}

		return $merged;
	}

	/**
	 * Получение пороговой цены для того чтобы сделать предложение с этапами
	 *
	 * @param string $lang Язык
	 *
	 * @return float
	 */
	public static function getPriceThreshold(string $lang): float {
		if (\App::isWorkbayApp()) {
			$setting = Config::WORKBAY_STAGES_MIN_ORDER_PRICE;
		} else {
			if ($lang === \Translations::DEFAULT_LANG) {
				$setting = Config::PHASES_MIN_ORDER_PRICE_RU;
			} else {
				$setting = Config::PHASES_MIN_ORDER_PRICE_EN;
			}
		}
		return \App::config($setting);
	}

	/**
	 * Получение минимальной цены этапа
	 *
	 * @param string|null $lang язык
	 * @param int|null $categoryId рубрика
	 *
	 * @return int
	 */
	public static function getStageMinPrice(?string $lang = null, ?int $categoryId = null): int {
		if (is_null($lang)) {
			$lang = \Translations::getLang();
		}

		return \CategoryManager::getCategoryBasePrice($categoryId, $lang);
	}

	/**
	 * Получить массив минимальных цен этапов по категориям, с ключем 0 - дефолтное значение
	 *
	 * @param string|null $lang язык
	 *
	 * @return array
	 */
	public static function getStageMinPrices(?string $lang = null): array {
		if (is_null($lang)) {
			$lang = \Translations::getLang();
		}

		$basePrices = \CategoryManager::BASE_PRICE_BY_CATEGORY;

		if ($lang == \Translations::DEFAULT_LANG) {
			$basePrices[0] = (int)\App::config("price");
		} else {
			$basePrices[0] = (int)\App::config("price_en");
		}
		return $basePrices;
	}

	/**
	 * Установить дату безакцептного изменения в null
	 * @param int $orderId - ID заказа
	 */
	public static function clearUnacceptedChangeDate(int $orderId) {
		OrderStage::withTrashed()->where(OrderStage::FIELD_ORDER_ID, $orderId)
			->update([
				OrderStage::FIELD_PAYER_UNACCEPTED_CHANGE_DATE => null
			]);
	}

	/**
	 * Получить самый дорогой этап на проверке из тех, которые должны автоприняться раньше всех
	 * среди переданных этапов
	 *
	 * @param array $stageIds идентификаторы этапов
	 * @param Order|null $order заказ (опционально, если пусто - берем по первому этапу)
	 *
	 * @return OrderStage|null
	 */
	public static function getEarliestMostExpensiveStage(array $stageIds, ?Order $order = null): ?OrderStage {
		if (is_null($order)) {
			$firstStage = OrderStage::find(Arr::first($stageIds));
			if (!$firstStage) {
				return null;
			}

			$order = $firstStage->order;
		}

		/**
		 * Этап, который должен автоприняться раньше всех
		 *
		 * @var OrderStage|null $earliestStage
		 */
		$earliestStage = null;
		$minAutoAcceptTimestamp = null;

		$checkingTrackStages = TrackStage::query()
			->whereIn(TrackStage::FIELD_ORDER_STAGE_ID, $stageIds)
			->whereHas("track", function(Builder $track) {
				$track->whereIn(Track::FIELD_TYPE, Type::getCheckingTypes());
			})
			->get()
			->groupBy(TrackStage::FIELD_TRACK_ID);

		$orderTracks = $order->tracks->all();

		/**
		 * @var Collection|TrackStage[] $trackStages
		 */
		foreach ($checkingTrackStages as $trackId => $trackStages) {
			if ($trackStages && $trackStages->isNotEmpty()) {
				// получим самый дорогой этап в текущем треке
				$mostExpensiveStage = TrackStage::getMostExpensiveStage($trackStages);

				// получим время автопринятия самого дорогого этапа
				$stageAutoAcceptTimestamp = $mostExpensiveStage->getAutoAcceptTimestamp($orderTracks);

				if ($stageAutoAcceptTimestamp && (is_null($earliestStage) || $stageAutoAcceptTimestamp < $minAutoAcceptTimestamp)) {
					$earliestStage = $mostExpensiveStage;
					$minAutoAcceptTimestamp = $stageAutoAcceptTimestamp;
				}
			}
		}

		return $earliestStage;
	}

	/**
	 * Получить время реального автопринятия этапов
	 *
	 * Если в одном треке сдается два этапа, дешевый и "дорогой" - у дешевого будет
	 * так же увеличенное время на проверку
	 *
	 * @param Collection|OrderStage[] $stages коллекция этапов
	 * @param Order|null $order заказ (опционально, если пусто - берем из первого этапа)
	 *
	 * @return array массив в формате [stageId => autoAcceptTimestamp, ...]
	 */
	public static function getStagesAutoAcceptTimestamps(Collection $stages, ?Order $order = null): array {
		if (is_null($order)) {
			$firstStage = $stages->first();
			if (!$firstStage instanceof OrderStage) {
				return [];
			}

			$order = $firstStage->order;
		}

		$timestamps = [];

		// получим идентификаторы этапов
		$stageIds = $stages->modelKeys();

		$checkingTrackStages = TrackStage::query()
			->whereIn(TrackStage::FIELD_ORDER_STAGE_ID, $stageIds)
			->whereHas("track", function(Builder $track) {
				$track->whereIn(Track::FIELD_TYPE, Type::getCheckingTypes());
			})
			->get()
			->groupBy(TrackStage::FIELD_TRACK_ID);

		$orderTracks = $order->tracks->all();

		/**
		 * @var Collection|TrackStage[] $trackStages
		 */
		foreach ($checkingTrackStages as $trackId => $trackStages) {
			if ($trackStages && $trackStages->isNotEmpty()) {
				// получим самый дорогой этап в текущем треке
				$mostExpensiveStage = TrackStage::getMostExpensiveStage($trackStages);

				// получим время автопринятия самого дорогого этапа
				$stageAutoAcceptTimestamp = $mostExpensiveStage->getAutoAcceptTimestamp($orderTracks);

				foreach ($trackStages as $trackStage) {
					$timestamps[$trackStage->order_stage_id] = $stageAutoAcceptTimestamp;
				}
			}
		}

		return $timestamps;
	}

}