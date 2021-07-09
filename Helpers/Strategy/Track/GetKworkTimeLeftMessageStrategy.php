<?php


namespace Strategy\Track;

/**
 * Получить сообщение об оставшемся времени
 *
 * Class GetKworkTimeLeftMessageStrategy
 * @package Strategy\Track
 */
class GetKworkTimeLeftMessageStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$isCancelRequest = (new IsInCancelRequestStrategy($this->order))->get();
		if ($isCancelRequest) {
			return null;
		}
		$duration = $this->order->duration / \Helper::ONE_DAY;
		return \OrderManager::getMessageKworkTimeLeft((int)$duration, $this->order->deadline);
	}
}