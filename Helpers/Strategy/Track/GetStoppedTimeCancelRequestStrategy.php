<?php


namespace Strategy\Track;


use Carbon\Carbon;

/**
 * Расчет времения остановки заказа, на котором висит запрос на отмену
 *
 * Class GetStoppedTimeCancelRequestStrategy
 * @package Strategy\Track
 */
class GetStoppedTimeCancelRequestStrategy extends AbstractTrackStrategy {

	/**
	 * Расчет времени остановки заказа на котором висит запрос на отмену
	 *
	 * @return int|null
	 */
	public function get() {
		$isInCancelRequest = new IsInCancelRequestStrategy($this->order);
		if ( $this->order->tracks->isEmpty() ||
			! $isInCancelRequest->get()) {
			return null;
		}
		$lastTrackForInProgressCancel = new GetLastTrackForInProgressCancelStrategy($this->order);
		$track = $lastTrackForInProgressCancel->get();
		return Carbon::parse($track->date_create)->timestamp;
	}
}