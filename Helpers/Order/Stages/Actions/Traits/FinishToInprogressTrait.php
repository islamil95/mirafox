<?php


namespace Order\Stages\Actions\Traits;

use Admin\Statistic\CategoryOrdersRecalc;
use Category\SalesByCategoryManager;
use Model\Notification\NotificationType;
use Model\Order;
use Track\Dto\CreateTrackDto;
use Order\Stages\OrderStageManager;
use Track\Type;

/**
 * Возврат в работу завершенного заказа
 */
trait FinishToInprogressTrait {

	/**
	 * Необходимые действия при переводе заказа в работу из состояния выполнен или отменен
	 * Транзакционная часть
	 *
	 * @param \Model\Order $order
	 * @param bool $forceInwork Сценарий принудительного взятия в работу
	 *
	 * @return CreateTrackDto
	 * @throws \Throwable
	 */
	protected function toInprogressFromFinish(Order $order, bool $forceInwork = false) {
		$result = new CreateTrackDto();

		if ($forceInwork) {
			if (!$order->in_work) {
				$order->in_work = true;
				OrderStageManager::clearUnacceptedChangeDate($order->OID);

				// транзакционная часть создания трека
				$trackId = \TrackManager::createTrack($order->OID, Type::WORKER_INWORK);

				$result->setTrackId($trackId)
					->setTrackType(Type::WORKER_INWORK);
			}
		} else {
			$order->in_work = false; // При этом продавец должен опять взять в работу
			$order->restarted = true;
		}
		$order->load("stages");
		$order->setAmountsByAllStages();
		$order->save();

		\OrderManager::setInprogressTransactional($order);

		\KworkManager::updatePopularExtras($order->PID);

		// Производим обновление заказа из базы
		$order->refresh();
		// Устанавливаем значение поля для сортировки по этапам - т.к. зависит от актуального статуса заказа
		$order->setStagesPrice();
		$order->save();

		return $result;
	}

	/**
	 * Обновление статистики по заказу в связи с его рестартом
	 *
	 * @param \Model\Order $order Модель заказа (до изменения статуса)
	 */
	public static function recalculateStatistic(Order $order) {
		// Вычисление даты для пересчета статистики
		$timeCancel = strtotime($order->date_cancel);
		$timeDone = strtotime($order->date_done);
		$recalculateDate = null;
		if ($timeCancel < $timeDone) {
			$recalculateDate = new \DateTime($order->date_done);
		} elseif ($timeCancel > $timeDone) {
			$recalculateDate = new \DateTime($order->date_cancel);
		} elseif ($timeCancel) {
			$recalculateDate = new \DateTime($order->date_cancel);
		} elseif ($timeDone) {
			$recalculateDate = new \DateTime($order->date_done);
		}

		if ($recalculateDate) {
			//Пересчет статистики по категориям и заказам
			CategoryOrdersRecalc::addWork($order->data->kwork_category, $recalculateDate, $order->getLang());
			// Пересчет продаж по категориям за дату завершения заказа
			$cacheRowId = SalesByCategoryManager::getSalesCatStatIdByDate($recalculateDate->format("Y-m-d H:i:s"));
		}

		// метрики пользователя на пересчет
		\UserStatisticManager::recalcUserOrderMetrics($order->worker_id, $order->kwork->category);

		// Обновим счётчики выполненных заказов
		\OrderManager::updateOrderDoneCount($order->PID, $order->worker_id);

		// Сбросим кеш проверок переписок между пользователями
		\PrivateMessageManager::removeAccessCache($order->worker_id, $order->USERID);
		\PrivateMessageManager::removeAccessCache($order->USERID, $order->worker_id);

		\NotityManager::clearNotify(NotificationType::KWORK_ACCEPT_PORTFOLIO, $order->OID, $order->worker_id);

		if ($order->isCancel()) {
			// Если заказ был в статусе отменен - увеличиваем число покупок
			\UserManager::incOrderCount($order->USERID);
		}
	}
}