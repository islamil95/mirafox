<?php


namespace Strategy\Track;

/**
 * Разрешена ли переписка в завершенном заказе
 *
 * Class IsDoneConversationAllowStrategy
 * @package Strategy\Track
 */
class IsDoneConversationAllowStrategy extends AbstractTrackStrategy {

	/**
	 * @return bool
	 */
	public function get() {
		if($this->order->status != \OrderManager::STATUS_DONE){
			return false;
		}
		$canWriteMessage = new CanWriteMessageStrategy($this->order);
		$opponentId = new GetOpponentIdStrategy($this->order);

		return $canWriteMessage->get();
	}
}