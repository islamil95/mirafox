<?php

namespace Order\Stages\Actions;

use Order\Stages\OrderStageManager;
use Helpers\Notification\NotificationManager\OrderNotificationManager;
use Pull\PushManager;
use Track\Type;

/**
 * Автоматическая отмена заказов перезапущенных в работу из статуса "Выполнен"
 */
class CronCancelRestartedOrderAction extends OrderStageAction {

	/**
	 * Запуск
	 *
	 * @return void
	 * @throws \Throwable
	 */
	public function run() {
		$order = $this->order;

		$allow = $order->isInProgress() && $order->in_work == 0;
		if (!$allow) {
			return;
		}

		$startTime = strtotime($order->date_inprogress);

		$autoCancelTime = time() - OrderStageManager::RESTARTED_AUTOCANCEL_THRESHOLD;
		if ($startTime >= $autoCancelTime) {
			// если время с паузами меньше порога автоотмены - выходим
			return;
		}

		$doneOrder = new \Order\Done\CronDoneOrder($order, Type::CRON_RESTARTED_INPROGRESS_CANCEL);

		if ($doneOrder->process() === false) {
			return;
		}

		OrderNotificationManager::cronCancellation($order);

		$factory = new \Factory\Letter\Service\ServiceLetterFactory();

		$data = [
			"orderId" => $order->OID,
			"orderRestarted" => true,
		];
 		$letter = new \MailEvents\Events\AutoCancelOrderToWorker($data, $order);
 		$letter->sendEmail();

		$kworkCheckListEnable = \StatusManager::kworkCheckListEnable($order->kwork->toArray());
		$letter = $factory->createAutoCancelOrderToPayer($order->payer->getUnencriptedEmail(), $order->kwork->kworkCategory->name, $order->kwork->kworkCategory->seo, $kworkCheckListEnable, $order->getPayerOrderName(), $order->worker->username, $order->payer->lang, true, $order->hasPaidStages());
		\MailSender::sendLetter($letter);

		PushManager::sendOrderUpdatedBoth($order);
	}
}