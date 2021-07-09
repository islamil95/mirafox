<?php


namespace Strategy\Track;


use Carbon\Carbon;

/**
 * Вывод оставшегося времени выполнения заказа, на котором висит запрос на отмену
 *
 * Class GetDeadlineTimeCancelRequestStrategy
 * @package Strategy\Track
 */
class GetDeadlineTimeCancelRequestStrategy extends AbstractTrackStrategy {

	/**
	 * Вывод оставшегося времени выполнения заказа на котором висит запрос на отмену
	 *
	 * @return int|null
	 */
	public function get() {
		$isInCancelRequestStrategy = new IsInCancelRequestStrategy($this->order);
		if ( $this->order->tracks->isEmpty() ||
			! $isInCancelRequestStrategy->get()) {
			return null;
		}
		return $this->order->deadline;
	}
}