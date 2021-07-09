<?php

namespace Order\Stages\Actions;

use Model\OrderStages\TrackStage;
use Order\Stages\Actions\Dto\ReserveOrderStageActionDto;
use Track\Type;

/**
 * Покупатель переводит этапный заказ из "необеспеченного" в "в работе"
 */
class PayerUnpaidInprogressAction extends OrderStageAction {

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

		if ($order->isNotUnpaid()) {
			throw new \RuntimeException("Перевод заказов в работу не разрешен в данном статусе");
		}

		// транзакционная часть установки статуса заказа
		\OrderManager::setInprogressTransactional($order);

		// транзакционная часть создания трека
		$trackId = \TrackManager::createTrack($order->OID, Type::PAYER_UNPAID_INPROGRESS);

		TrackStage::simpleAssociate($trackId, [$stageId]);

		return new ReserveOrderStageActionDto(null, $trackId, Type::PAYER_UNPAID_INPROGRESS);
	}
}