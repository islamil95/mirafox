<?php


namespace Strategy\Track;

/**
 * Показывать ли промежуточный отсчет
 *
 * Class IsShowIntermediateReportStrategy
 * @package Strategy\Track
 */
class IsShowIntermediateReportStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return false;
	}
}