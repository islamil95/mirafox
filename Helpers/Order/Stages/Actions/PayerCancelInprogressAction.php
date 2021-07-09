<?php

namespace Order\Stages\Actions;

use Model\OrderStages\TrackStage;
use Model\Track;
use Order\Stages\Actions\Dto\ReserveOrderStageActionDto;
use Track\Type;

/**
 * Покупатель переводит этапный заказ из "отмененного" в "в работе"
 * (доступно только после автоотмены неоплачанного заказа)
 */
class PayerCancelInprogressAction extends OrderStageAction {

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

		// Если
		if (!$order->isCanStagesReserved()) {
			throw new \RuntimeException("Перевод заказов в работу не разрешен в данном статусе");
		}

		if (!$order->isCancel()) {
			throw new \RuntimeException("Данный метод только для перевода отмененных заказов в работу");
		}

		$lastTrack = $order->lastTrack;
		if (!$lastTrack instanceof Track) {
			throw new \RuntimeException("Не найден последний значительный трек");
		}

		if ($lastTrack->type != Type::CRON_UNPAID_CANCEL) {
			throw new \RuntimeException("Перевод в работу доступен только для автоотменных неоплаченных заказов");
		}

		// транзакционная часть создания трека
		$trackId = \TrackManager::createTrack($order->OID, Type::PAYER_CANCEL_INPROGRESS);

		TrackStage::simpleAssociate($trackId, [$stageId]);

		// модель заказа до изменения статуса
		$orderClone = clone $order;

		// Переводим в работу
		$secondaryTrackDto = $this->toInprogressFromFinish($order);

		$result = new ReserveOrderStageActionDto($orderClone, $trackId, Type::PAYER_CANCEL_INPROGRESS);
		$result->addCreateTrackDto($secondaryTrackDto);

		return $result;
	}
}