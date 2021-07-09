<?php


namespace Strategy\Track;

/**
 * Получить идентификатор противоположной стороны в заказе для текущего пользователя
 *
 * Class GetOpponentIdStrategy
 * @package Strategy\Track
 */
class GetOpponentIdStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return $this->order->isWorker($this->getUserId()) ?
			(int) $this->order->USERID :
			(int) $this->order->worker_id;
	}
}