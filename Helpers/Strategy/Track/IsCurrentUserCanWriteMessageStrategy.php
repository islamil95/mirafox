<?php


namespace Strategy\Track;

/**
 * Может ли пользователь писать сообщения
 *
 * Class IsCurrentUserCanWriteMessageStrategy
 * @package Strategy\Track
 */
class IsCurrentUserCanWriteMessageStrategy extends AbstractTrackStrategy {

	/**
	 * @return bool
	 */
	public function get() {
		if ($this->order->isNotBelongToPayerOrWorker($this->getUserId())) {
			return false;
		}

		$writableStatuses = [
			\OrderManager::STATUS_INPROGRESS,
			\OrderManager::STATUS_CHECK,
			\OrderManager::STATUS_ARBITRAGE,
			\OrderManager::STATUS_UNPAID,
		];

		$allowConversationInDoneOrder = new IsAllowConversationInDoneOrderStrategy($this->order);
		return in_array($this->order->status, $writableStatuses) ||
			($allowConversationInDoneOrder->get() && $this->order->isPayer($this->getUserId()));
	}
}