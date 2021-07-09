<?php


namespace Strategy\Track;

/**
 * Есть ли обратный отсчет
 *
 * Class HasCountdownStrategy
 * @package Strategy\Track
 */
class HasCountdownStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return $this->order->duration == \Helper::ONE_DAY
			&& $this->order->data_provided == 1
			&& $this->order->isInProgress()
			&& $this->order->isWorker($this->getUserId());
	}
}