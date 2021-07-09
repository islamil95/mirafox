<?php


namespace Strategy\Track;


use Model\KworkReport;

/**
 * Показывать ли отчет
 *
 * Class IsShowReportStrategy
 * @package Strategy\Track
 */
class IsShowReportStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$newReport = $this->order->reports->firstWhere(KworkReport::FIELD_STATUS, KworkReport::STATUS_NEW);
		return $this->order->price >= KworkReport::minPriceForShow($this->order->getLang()) &&
			!is_null($newReport) &&
			$this->order->worker_id === $this->getUserId()&&
			$this->order->isInProgress() &&
			$this->order->isInWork();
	}
}