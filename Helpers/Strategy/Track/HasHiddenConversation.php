<?php


namespace Strategy\Track;


use Model\Track;

/**
 * Есть ли скрытые треки
 *
 * Class HasHiddenConversation
 * @package Strategy\Track
 */
class HasHiddenConversation extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return $this->order->tracks->sum(function ($track) {
			/**
			 * @var Track $track
			 */
			return empty($track->getHiddenConversation()) ? 0 : 1;
		}) > 0;
	}
}