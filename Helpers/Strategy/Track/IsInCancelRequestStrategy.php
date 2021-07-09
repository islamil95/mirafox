<?php


namespace Strategy\Track;


use Model\Track;
use Track\Type;

/**
 * Заказ в состоянии отмены
 *
 * Class IsInCancelRequestStrategy
 * @package Strategy\Track
 */
class IsInCancelRequestStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		if (empty($this->order->tracks)) {
			return false;
		}
		return $this->order->tracks
				->filter(function ($track, $key) {
					/**
					 * @var Track $track
					 */
					return Type::isInprogressCancel($track->type) && $track->isNew();
				})->count() > 0;
	}
}