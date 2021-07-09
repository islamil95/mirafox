<?php


namespace Strategy\Track;


use Carbon\Carbon;
use Model\Track;
use Track\Type;

/**
 * Получить последний трек в статусе отмены
 *
 * Class GetLastTrackForInProgressCancelStrategy
 * @package Strategy\Track
 */
class GetLastTrackForInProgressCancelStrategy extends AbstractTrackStrategy {

	/**
	 * @return Track|null;
	 */
	public function get() {
		return $this->order->tracks
			->filter(function ($track, $key) {
				/**
				 * @var Track $track
				 */
				return Type::isInprogressCancel($track->type) && $track->isNew();
			})
			->sortByDesc(function ($track, $key) {
				/**
				 * @var Track $track
				 */
				return Carbon::parse($track->date_create)->timestamp;
			})
			->first();
	}
}