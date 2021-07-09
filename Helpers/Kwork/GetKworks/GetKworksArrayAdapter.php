<?php declare(strict_types=1);

namespace Helpers\Kwork\GetKworks;

use Illuminate\Database\Eloquent\Collection;
use Model\Category;
use Model\Kwork;
use Model\KworkPackage;

/**
 * Class GetKworksArrayAdapter
 * @package Helpers\Kwork\GetKworks
 */
final class GetKworksArrayAdapter {
	/**
	 * @param Collection|Kwork[] $kworks
	 * @return array
	 */
	public function postsByCategory(Collection $kworks): array {
		\KworkManager::fillPreparedPricesByCollection($kworks);

		return $kworks->map(function(Kwork $kwork) {
			$kworkArray = $kwork->toArray();

			$kworkArray['username'] = $kwork->user->username;
			$kworkArray['quecount'] = $kwork->queueCount;
			$kworkArray['cache_rating_count_en'] = $kwork->user->cache_rating_count_en;
			$kworkArray['userRatingCount'] = $kwork->user->cache_rating_count;
			$kworkArray['userRating'] = $kwork->user->cache_rating;

			return $kworkArray;
		})->toArray();
	}
}
