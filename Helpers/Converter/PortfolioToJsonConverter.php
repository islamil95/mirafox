<?php


namespace Converter;

use Model\Portfolio;
use Model\PortfolioVideo;
use Portfolio\PortfolioNumber;

/**
 * Конвертер для преобразования данных портфолио для js формы
 * Использовать только для этого !!! в нормальной модели не может не быть category_id и newAttributesIds
 */
class PortfolioToJsonConverter implements \JsonSerializable {

	/**
	 * @var Portfolio
	 */
	private $portfolio;

	/**
	 * @param Portfolio $portfolio
	 */
	public function __construct(Portfolio $portfolio) {
		$this->portfolio = $portfolio;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		$crop = $this->portfolio->cover_crop;
		if ($crop != "") {
			$crop = json_decode($crop);
		}
		$images = [];
		if ($this->portfolio->images) {
			foreach ($this->portfolio->images as $portfolioImage) {
				$images[] = new PortfolioImageToJsonCoverter($portfolioImage);
			}
		}
		$videos = [];
		if ($this->portfolio->videos) {
			$videos = $this->portfolio->videos->pluck(PortfolioVideo::FIELD_URL);
		}

		$cover = [
			"url" => "",
			"urlBig" => "",
			"hash" => "",
			"crop" => "",
		];
		if ($this->portfolio->cover) {
			if ($this->portfolio->is_resizing == 0) {
				$coverUrl = $this->portfolio->getCoverSizeUrl(\PhotoManager::IMAGE_SIZE_T4);
			} else {
				$coverUrl = $this->portfolio->getCoverSizeUrl(\PhotoManager::ORIGINAL_IMAGE_SUBCATEGORY);
			}
			$cover = [
				"url" => $coverUrl,
				"urlBig" => $this->portfolio->getCoverSizeUrl(\PhotoManager::ORIGINAL_IMAGE_SUBCATEGORY),
				"hash" => $this->portfolio->cover_hash,
				"type" => $this->portfolio->cover_type,
				"crop" => $crop,
			];
		}
		$portfolio = [
			"id" => $this->portfolio->id,
			"title" => $this->portfolio->title,
			"kwork_id" => $this->portfolio->kwork_id,
			"order_id" => $this->portfolio->order_id,
			"description" => $this->portfolio->description,
			"category_id" => $this->portfolio->category_id,
			"attributes_ids" => $this->portfolio->newAttributesIds, // Для данных формы портфолио
			"cover" => $cover,
			"images" => $images,
			"videos" => $videos,
			"workNum" => PortfolioNumber::get($this->portfolio),
		];

		return $portfolio;
	}
}
