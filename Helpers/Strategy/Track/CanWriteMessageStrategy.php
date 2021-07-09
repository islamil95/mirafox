<?php


namespace Strategy\Track;

/**
 * Может ли писать сообщения пользователю
 *
 * Class CanWriteMessageStrategy
 * @package Strategy\Track
 */
class CanWriteMessageStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$currentUserCanWriteMessage = new IsCurrentUserCanWriteMessageStrategy($this->order);
		$canWriteMessage = $currentUserCanWriteMessage->get();
		if (! $canWriteMessage) {
			return ($this->getUserId() == $this->config("kwork.user_id") || $this->isVirtual())
				&& $this->order->status == \OrderManager::STATUS_ARBITRAGE
				&& \AdminManager::getCurrent();
		}
		return $canWriteMessage;

	}
}