<?php


namespace Strategy\Track;

/**
 * Показывать ли текстовое предупреждение об истекающем времени
 *
 * Class GetWorkTimeWarningStrategy
 * @package Strategy\Track
 */
class GetIsShowWorkTimeWarningStrategy extends AbstractTrackStrategy {
	/**
	 * Получить результат
	 *
	 * @return bool Показывать ли текстовое предупреждение об истекающем времени
	 */
	public function get(): bool {
		$workTimeWarning = (new GetWorkTimeWarningStrategy($this->order))->get();

		return (int)$this->order->worker_id === $this->getCurrentUserId()
			&&
			$this->order->isInProgress()
			&&
			$workTimeWarning;
	}
}