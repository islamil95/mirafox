<?php


namespace Strategy\Track;

/**
 * Разрешен ли арбитраж
 *
 * Class IsAllowArbitrageStrategy
 * @package Strategy\Track
 */
class IsAllowArbitrageStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return $this->config("arbitrage.enable")
			&& $this->order->date_check != false
			&& ($this->order->isCheck() || $this->order->isInProgress());
	}
}