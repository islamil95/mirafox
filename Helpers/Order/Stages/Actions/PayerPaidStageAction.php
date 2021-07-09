<?php

namespace Order\Stages\Actions;

use Model\OrderStages\OrderStage;
use Model\OrderStages\TrackStage;
use Helpers\Notification\NotificationManager\OrderNotificationManager;
use Order\Stages\Actions\Dto\ReserveOrderStageActionDto;
use Track\Type;

/**
 * Покупатель оплачивает этап
 */
class PayerPaidStageAction extends OrderStageAction {

	/**
	 * Запуск
	 *
	 * @param OrderStage $stage Оплаченный этап
	 *
	 * @return ReserveOrderStageActionDto
	 */
	public function run(OrderStage $stage) {
		if ($this->order->OID != $stage->order_id) {
			throw new \RuntimeException("Wrong stage");
		}

		// транзакционная часть создания трека
		$trackId = \TrackManager::createTrack($this->order->OID, Type::PAYER_STAGE_PAID);

		TrackStage::simpleAssociate($trackId, [$stage->id]);

		// забиваем на то, что это в транзакции и шлем пуши
		OrderNotificationManager::sendOrderChangedStatus($stage->order);

		return new ReserveOrderStageActionDto(null, $trackId, Type::PAYER_STAGE_PAID);
	}
}