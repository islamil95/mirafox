<?php


namespace Strategy\Track;


/**
 * Получить оставшееся время в виде строки
 *
 * Class GetTimeLeftAsStringStrategy
 * @package Strategy\Track
 */
class GetTimeLeftAsStringStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$cancelDeadline = null;
		$isInCancel = new IsInCancelRequestStrategy($this->order);
		if ($isInCancel->get()) {
			$cancelDeadline = (new GetDeadlineTimeCancelRequestStrategy($this->order))->get();
		}
		$orderTime = \OrderManager::getOrderTime($this->order->deadline, $this->order->duration, $cancelDeadline);
		return insert_countdown_short($orderTime);
	}
}