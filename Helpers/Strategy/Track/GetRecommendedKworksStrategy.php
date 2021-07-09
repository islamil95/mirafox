<?php


namespace Strategy\Track;


use Kwork\Similar\ImportantWordsSelect;

/**
 * Получить рекомендуемые кворки
 *
 * Class GetRecommendedKworksStrategy
 * @package Strategy\Track
 */
class GetRecommendedKworksStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		return [];
	}
}