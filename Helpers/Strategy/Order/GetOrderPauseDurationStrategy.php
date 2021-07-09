<?php


namespace Strategy\Order;

use Model\Order;

/**
 * Получить длительность ожидания в момент когда заказ был на паузе(запросы на отмену)
 *
 * Class GetOrderPauseDurationStrategy
 * @package Strategy\Order
 */
class GetOrderPauseDurationStrategy extends AbstractOrderStrategy {

	/**
	 * @inheritdoc
	 * @return int
	 */
	public function get() {
		$pauseDuration = 0;
		$haveNoClose = false;
		$dateStart = 0;
		$requestStatuses = [
			"payer_inprogress_cancel_request",
			"worker_inprogress_cancel_request",
		];

		foreach ($this->order->tracks as $key => $track) {
			if (!in_array($track->type, $requestStatuses)) {
				continue;
			}
			$dateStart = strtotime($track->date_create);
			$getNextCloseOrderRequestStrategy = new GetNextCloseOrderRequestStrategy($this->order, $key);
			$closeTrack = $getNextCloseOrderRequestStrategy->get();
			if ($closeTrack) {
				if (!empty($closeTrack->date_create)) {
					$dateClose = strtotime($closeTrack->date_create);
					$pauseDuration += ($dateClose - $dateStart);
				}
			} else {
				$haveNoClose = true;
			}
		}
		//Если нет закрывающего трека, считаем от текущего времени
		if($haveNoClose == true){
			$pauseDuration += (time() - $dateStart);
		}

		return $pauseDuration;
	}
}