<?php

namespace Order\Stages\Actions;

use Core\Exception\SimpleJsonException;
use Helpers\MailEvents\Events\OrderStageReturnedToWorker;
use Helpers\Notification\NotificationManager\StageNotificationManager;
use Model\OrderStages\OrderStage;
use Model\OrderStages\TrackStage;
use Pull\PushManager;
use Track\Type;
use Model\Notification\NotificationType;

/**
 * Покупатель отправляет этапы заказа на доработку
 */
class PayerRejectStagesAction extends OrderStageAction {

	/**
	 * Покупатель отправляет этапы заказа на доработку
	 *
	 * @param array $stageIds Массив идентификаторов отконяемых этапов
	 * @param string $message Тест сообщения
	 *
	 * @return int Идентификатор добавленного трека
	 * @throws \Throwable
	 */
	public function run(array $stageIds = [], $message = null) {
		$order = $this->order;
		$isOrderInAllowedStatus = $order->isArbitrage() || $order->isCheck();

		if (!$isOrderInAllowedStatus) {
			throw new SimpleJsonException(\Translations::t("Заказ в некорректном статусе"));
		}

		$stagesForReject = $order->stages
			->whereIn(OrderStage::FIELD_ID, $stageIds)
			->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
			->where(OrderStage::FIELD_PROGRESS, OrderStage::PROGRESS_FULL);

		if (count($stagesForReject) != count($stageIds)) {
			throw new SimpleJsonException(\Translations::t("Задачи в некорректном статусе"));
		}

		// Изменяем этапам прогресс (это и есть отправка на доработку)
		$stagesForReject->each(function (OrderStage $stage) {
			$stage->progress = OrderStage::PROGRESS_REWORK;
			$stage->save();
		});

		// Закрываем треки
		$this->closeCheckingStagesTracks();

		$orderToInprogress = $order->isCheck() && !$order->hasCheckStages();

		// Если есть задачи на проверке - то один тип трека, если нет то другой - чтобы считать время заказа
		if ($orderToInprogress) {
			$trackType = Type::PAYER_REJECT_STAGES_INPROGRESS;
		} else {
			$trackType = Type::PAYER_REJECT_STAGES;
		}

		$trackId = \TrackManager::create($order->OID, $trackType, $message);

		// Связываем этапы с треком
		TrackStage::simpleAssociate($trackId, $stageIds);

		// Переустанавливаем напоминание orderPayerEvent
		$this->reinstallOrderPayerEvent();

		if ($orderToInprogress) {
			\OrderManager::setInprogress($order->OID);
		} else {
			$order->show_as_inprogress_for_worker = $order->hasCheckStages();
			$order->save();
		}

		$emailHandler = new OrderStageReturnedToWorker([
			"orderId" => $order->getKey(),
			"trackId" => $trackId,
		]);
		$emailHandler->sendEmail();

		StageNotificationManager::kworkDotcomStageDeliveryReject($order, $order->worker_id, $stageIds);

		\NotityManager::setStagesRead($stageIds, NotificationType::STAGE_DELIVERED, $order->USERID);
		//Обновим трек передачи на проверку у Продавца
		PushManager::sendRefreshTrack($order->worker_id, $order->OID);
		return $trackId;
	}
}