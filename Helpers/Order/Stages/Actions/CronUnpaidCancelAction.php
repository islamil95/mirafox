<?php

namespace Order\Stages\Actions;

use Model\Track;
use Order\Stages\OrderStageManager;
use Pull\PushManager;
use Track\Type;
use Helpers\Notification\NotificationManager\OrderNotificationManager;

/**
 * Отмена неоплаченного заказа оставашегося неоплаченным более 5 дней
 */
class CronUnpaidCancelAction extends OrderStageAction {

	/**
	 * Запуск
	 *
	 * @return void
	 * @throws \Throwable
	 */
	public function run() {
		$order = $this->order;
		if (!$order->isUnpaid() || !$order->has_stages || !$order->stages) {
			\Log::daily("Некорректный заказ в CronUnpaidCancelAction, заказ: {$order->OID}", "error");
			return;
		}

		$lastTrack = $order->tracks()
			->where(Track::FIELD_TYPE, Type::STAGE_UNPAID)
			->orderByDesc(Track::FIELD_ID)
			->first();
		if (! $lastTrack instanceof Track) {
			\Log::daily("Не найден stage_unpaid в CronUnpaidCancelAction, заказ: {$order->OID}", "error");
			return;
		}

		$trackDatetime = new \DateTime($lastTrack->date_create);

		if ($trackDatetime >= OrderStageManager::getUnpaidCancelThresholdDatetime()) {
			\Log::daily("Время отмены заказа еще не подошло в CronUnpaidCancelAction, заказ: {$order->OID}", "error");
		}

		$doneOrder = new \Order\Done\CronDoneOrder($order, Type::CRON_UNPAID_CANCEL);

		if ($doneOrder->process() === false) {
			return;
		}

		OrderNotificationManager::cronCancellation($order);

		PushManager::sendOrderUpdatedBoth($order);
	}
}