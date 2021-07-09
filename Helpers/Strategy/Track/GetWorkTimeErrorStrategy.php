<?php


namespace Strategy\Track;


/**
 * Превышает ли время сдачи заказа текущее время
 *
 * Class GetWorkTimeErrorStrategy
 * @package Strategy\Track
 */
class GetWorkTimeErrorStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return $this->order->deadline ?
			$this->order->deadline < time() :
			null;
	}
}