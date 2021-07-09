<?php


namespace Strategy\Track;

/**
 * Может ли отправлять портфолио
 *
 * Class CanSendPortfolioStrategy
 * @package Strategy\Track
 */
class CanSendPortfolioStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return false;
	}
}