<?php

namespace Order\Stages\Actions;

use Model\OrderStages\TrackStage;
use Order\Stages\Actions\Dto\ReserveOrderStageActionDto;
use Track\Type;

/**
 * Покупатель переводит этапный заказ из "выполнен" в "в работе"
 */
class PayerDoneInprogressAction extends OrderStageAction {

	/**
	 * Запуск
	 *
	 * @param int $userId Идентификатор пользователя инициирующего действие
	 * @param int $stageId Идентификатор этапа который был оплачен
	 *
	 * @return ReserveOrderStageActionDto
	 * @throws \Throwable
	 */
	public function run(int $userId, int $stageId) {
		$order = $this->order;

		if (empty($userId)) {
			throw new \RuntimeException("No actor");
		}

		if (!$order->isPayer($userId)) {
			throw new \RuntimeException("Not order payer");
		}

		if (!$order->has_stages || !$order->stages || !count($order->stages)) {
			throw new \RuntimeException("Order not staged");
		}

		if ($order->isNotDone()) {
			throw new \RuntimeException("Перевод заказов в работу не разрешен в данном статусе");
		}

		if (!$order->lastTrack) {
			throw new \RuntimeException("Не найден последний трек");
		}

		$trackType = Type::PAYER_DONE_INPROGRESS;
		if ($order->lastTrack->type == Type::CRON_UNPAID_CANCEL) {
			// Если заказ был отменен (но на самом деле в статусе "Выполнен") изза не оплаты в требуемый срок, то нужен особый тип трека
			$trackType = Type::PAYER_DONE_INPROGRESS_UNPAID;
		}

		// транзакционная часть создания трека
		$trackId = \TrackManager::createTrack($order->OID, $trackType);

		TrackStage::simpleAssociate($trackId, [$stageId]);

		// модель заказа до изменения статуса
		$orderClone = clone $order;

		// Переводим в работу
		$secondaryTrackDto = $this->toInprogressFromFinish($order);

		$result = new ReserveOrderStageActionDto($orderClone, $trackId, $trackType);
		$result->addCreateTrackDto($secondaryTrackDto);

		return $result;
	}


}