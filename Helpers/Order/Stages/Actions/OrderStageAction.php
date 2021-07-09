<?php


namespace Order\Stages\Actions;


use Model\Order;
use Model\OrderStages\OrderStage;
use Model\OrderStages\TrackStage;
use Model\Track;
use Order\Stages\Actions\Traits\FinishToInprogressTrait;
use OrderStageReserve;
use Track\Type;

abstract class OrderStageAction {

	/**
	 * Трейт с методами для возврата завершенного заказа в работу
	 */
	use FinishToInprogressTrait;

	/**
	 * @var \Model\Order $order
	 */
	protected $order;

	/**
	 * Конструктор
	 *
	 * @param \Model\Order $order Модель заказа
	 */
	public function __construct(Order $order) {
		$this->order = $order;
	}

	/**
	 * Закрытие треков отправки на проверку в которых все связанные этапы оплачены или отправлены на доработку
	 */
	protected function closeCheckingStagesTracks() {
		$order = $this->order;
		// Получим идентификаторы этапов на проверке
		$checkingStagesIds = $order->stages()
			->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
			->where(OrderStage::FIELD_PROGRESS, OrderStage::PROGRESS_FULL)
			->pluck(OrderStage::FIELD_ID)
			->toArray();

		// Выберем треки отправки на проверку
		$tracks = $order->tracks()
			->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CHECK)
			->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
			->with("trackStages")
			->get();

		/**
		 * @var Track $track
		 */
		foreach ($tracks as $track) {
			$trackOrderStagesIds = $track->trackStages->pluck(TrackStage::FIELD_ORDER_STAGE_ID)->toArray();
			// Если в связанных этапах трека нет этапов на проверке, то закрываем трек
			if ($trackOrderStagesIds && empty(array_intersect($trackOrderStagesIds, $checkingStagesIds))) {
				$track->status = \TrackManager::STATUS_CLOSE;
				$track->save();
			}
		}
	}

	/**
	 * Переустановка напоминания order_payer
	 */
	protected function reinstallOrderPayerEvent() {
		$order = $this->order;

		$earliestStage = $order->getEarliestMostExpensiveStage();

		if (!($earliestStage instanceof OrderStage) || empty($earliestStage->check_date)) {
			// закрытие напоминания
			\EventManager::closeOrderPayerEvent($order->OID);
		} else {
			// создание или обновление времени напоминания
			$stageAutoAcceptTimestamp = $earliestStage->getAutoAcceptTimestamp($order->tracks->all());

			if ($stageAutoAcceptTimestamp) {
				$dateEvent = \Helper::now($stageAutoAcceptTimestamp);
				\EventManager::createOrUpdateOrderPayerEvent($order->OID, $order->USERID, $dateEvent);
			}
		}
	}

	/**
	 * Email продавцу - нужно взять в работу возобновленный заказ
	 *
	 * @param int $trackId Идентификатор трека
	 */
	protected function sendOrderStageReserveMail(int $trackId) {
		$order = $this->order;
		$emailHandler = new OrderStageReserve(["orderId" => $order->OID, "trackId" => $trackId]);
		$emailHandler->sendEmail();
	}

}