<?php


namespace Strategy\Track;


use Model\Track;

/**
 * Получить количество оставшихся нескрытых сообщениях
 *
 * Class GetShownThenHidedStrategy
 * @package Strategy\Track
 */
class GetShownThenHidedStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {

		if ($this->order->tracks->count() <= \TrackManager::SHOW_READ_TRACKS) {
			return 0;
		}

		$hideQuantity = (new GetHideQuantityStrategy($this->order))->get();
		if ($hideQuantity == 0) {
			return 0;
		}

		return $this->order->tracks->sum(function ($track) {
			/**
			 * @var Track $track
			 */
			return empty($track->getHiddenConversation()) &&
				!$track->getHide() ?
				1 : 0;
		});
	}
}