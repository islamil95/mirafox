<?php


namespace Strategy\Track;


/**
 * Получить текущий идентификатор пользователя в заказе
 *
 * Class GetCurrentUserIdForOrderStrategy
 * @package Strategy\Track
 */
class GetCurrentUserIdForOrderStrategy extends AbstractTrackStrategy {

	/**
	 * @return int
	 */
	public function get() {
		return $this->order->isWorker($this->getUserId()) ?
			(int) $this->order->worker_id :
			(int) $this->order->USERID;
	}
}