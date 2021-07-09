<?php


namespace Strategy\Track;

/**
 * Получить текстовое предупреждение об истекающем времени
 *
 * Class GetWorkTimeWarningStrategy
 * @package Strategy\Track
 */
class GetWorkTimeWarningStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$isInCancel = new IsInCancelRequestStrategy($this->order);
		if ($isInCancel->get()) {
			return null;
		}
		$workDays = (int)($this->order->duration / \Helper::ONE_DAY);
		// @todo: это тоже в пуш
		return \OrderManager::getMessageKworkTimeLeft($workDays, $this->order->deadline);
	}
}