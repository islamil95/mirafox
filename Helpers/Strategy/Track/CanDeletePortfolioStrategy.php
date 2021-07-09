<?php


namespace Strategy\Track;


/**
 * Может ли пользователь удалить портфолио
 *
 * Class CanDeletePortfolioStrategy
 * @package Strategy\Track
 */
class CanDeletePortfolioStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return false;
	}
}