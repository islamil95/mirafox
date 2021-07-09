<?php


namespace Strategy\Track;


use Model\Track;

/**
 * Получить количество для скрытия с треке сообщний
 *
 * Class GetHideQuantityStrategy
 * @package Strategy\Track
 */
class GetHideQuantityStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {

		$readed = $this->order->tracks->sum(function($track) {
			/**
			 * @var Track $track;
			 */
			if (empty($track->getHiddenConversation())) {
				if ($track->user_id != $this->getUserId() && $track->unread) {
					return 0;
				}
				return 1;
			}
			return 0;
		});

		if ($readed <= \TrackManager::SHOW_READ_TRACKS) {
			return 0;
		}
		return $readed - \TrackManager::SHOW_READ_TRACKS;
	}
}