<?php


namespace Strategy\Track;


use Carbon\Carbon;
use Model\Track;

/**
 * Разрешена ли переписка после завершения заказа
 *
 * Class IsAllowConversationInDoneOrderStrategy
 * @package Strategy\Track
 */
class IsAllowConversationInDoneOrderStrategy extends AbstractTrackStrategy {

	/**
	 * @return bool
	 */
	public function get() {
		// если статус не done, то нет
		if ($this->order->status != \OrderManager::STATUS_DONE) {
			return false;
		}

		// если заказ принят менее месяца назад, то да
		$doneDateWithAllowPeriod = Carbon::parse($this->order->date_done)->addSeconds(\Helper::ONE_YEAR);
		if ($doneDateWithAllowPeriod->greaterThanOrEqualTo(Carbon::now())) {
			return true;
		}
		// если последнее сообщение менее месяца назад, то да
		/**
		 * @var Track $lastTextTrack
		 */
		$lastTextTrack = $this->order->tracks
			->where(Track::FIELD_TYPE, \TrackManager::TRACK_TYPE_TEXT)
			->sortBy(Track::FIELD_ID)
			->last();
		if (empty($lastTextTrack)) {
			return false;
		}
		$createDateWithPeriod = Carbon::parse($lastTextTrack->date_create)->addSeconds(\Helper::ONE_YEAR);

		return $createDateWithPeriod->greaterThanOrEqualTo(Carbon::now());
	}
}