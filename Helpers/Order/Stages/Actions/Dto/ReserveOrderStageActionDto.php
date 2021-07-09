<?php

namespace Order\Stages\Actions\Dto;

use Illuminate\Support\Collection;
use Model\Order;
use Order\Stages\Actions\Traits\FinishToInprogressTrait;
use OrderManager;
use OrderStageReserve;
use Track\Dto\CreateTrackDto;
use Track\Type;

final class ReserveOrderStageActionDto {

	/**
	 * Типы основного трека, при которых надо переводить заказ в работу
	 */
	const TO_INPROGRESS_TRACK_TYPES = [
		Type::PAYER_UNPAID_INPROGRESS,
		Type::PAYER_CANCEL_INPROGRESS,
		Type::PAYER_DONE_INPROGRESS,
		Type::PAYER_DONE_INPROGRESS_UNPAID,
	];

	/**
	 * Типы основного трека, при которых надо посылать Email продавцу - нужно взять
	 * в работу возобновленный заказ
	 */
	const SEND_RESERVE_MAIL_TRACK_TYPES = [
		Type::PAYER_CANCEL_INPROGRESS,
		Type::PAYER_DONE_INPROGRESS,
		Type::PAYER_DONE_INPROGRESS_UNPAID,
	];

	/**
	 * Массив результатов работы транзакционной части метода создания трека
	 *
	 * @var Collection|CreateTrackDto[]
	 */
	 private $trackDtos;

	/**
	 * Модель заказа до изменения статуса в транзакционной части
	 *
	 * @var Order|null
	 */
	 private $order;

	/**
	 * ReserveOrderStageActionDto constructor
	 *
	 * @param Order|null $order модель заказа до изменения статуса
	 * @param int|null $trackId идентификатор основного трека
	 * @param string|null $trackType тип основного трека
	 */
	public function __construct(?Order $order = null, ?int $trackId = null, ?string $trackType = null) {
		$this->order = $order;
		$this->trackDtos = new Collection();

		$this->addCreateTrackDto(new CreateTrackDto($trackId, $trackType));
	}

	/**
	 * Добавить результат работы транзакционной части метода создания трека в массив
	 *
	 * @param CreateTrackDto $createTrackDto результат работы транзакционной части метода создания трека
	 * @return $this
	 */
	public function addCreateTrackDto(CreateTrackDto $createTrackDto): ReserveOrderStageActionDto {
		$this->trackDtos->push($createTrackDto);
		return $this;
	}

	/**
	 * Получить первый (основной) результат работы транзакционной части метода создания трека
	 *
	 * @return CreateTrackDto
	 */
	public function getPrimaryTrackDto(): CreateTrackDto {
		return $this->trackDtos->first();
	}

	/**
	 * Выполнить вне-транзакционные действия
	 *
	 * @param Order $order заказ
	 * @param int $originalStatus оригинальный статус заказа
	 *
	 * @throws \Exception
	 */
	public function nonTransactionalActions(Order $order, int $originalStatus): void {
		// вне-транзакционные действия после создания треков в транзакции
		foreach ($this->trackDtos as $trackDto) {
			if ($trackDto->isNotEmpty()) {
				\TrackManager::postCreateTrack($trackDto->getTrackId(), $order->OID, $trackDto->getTrackType());
			}
		}

		if ($this->getPrimaryTrackDto()->isNotEmpty()) {
			// Email продавцу - нужно взять в работу возобновленный заказ
			if (in_array($this->getPrimaryTrackDto()->getTrackType(), self::SEND_RESERVE_MAIL_TRACK_TYPES)) {
				$emailHandler = new OrderStageReserve(["orderId" => $order->OID, "trackId" => $this->getPrimaryTrackDto()->getTrackId()]);
				$emailHandler->sendEmail();
			}

			if (in_array($this->getPrimaryTrackDto()->getTrackType(), self::TO_INPROGRESS_TRACK_TYPES)) {
				// вне-транзакционная часть setInprogress
				OrderManager::postOrderStatusChanged($order, $originalStatus);

				if ($this->getOrder()) {
					// Сюда подается заказ с неизмененным еще статусом
					FinishToInprogressTrait::recalculateStatistic($this->getOrder());
				}
			}
		}
	}

	/**
	 * Получить модель заказа до изменения статуса
	 *
	 * @return Order|null
	 */
	public function getOrder(): ?Order {
		return $this->order;
	}

}
