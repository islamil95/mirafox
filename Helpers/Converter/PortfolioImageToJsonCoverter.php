<?php


namespace Converter;

use Model\PortfolioImage;

/**
 * Конвертер для преобразования данных изображения портфолио для js формы
 */
class PortfolioImageToJsonCoverter implements \JsonSerializable {

	/**
	 * @var PortfolioImage
	 */
	private $portfolioImage;

	public function __construct(PortfolioImage $portfolioImage) {
		$this->portfolioImage = $portfolioImage;
	}

	public function jsonSerialize() {
		if ($this->portfolioImage->is_resizing == 0) {
			$url = $this->portfolioImage->getSizeUrl(\PhotoManager::IMAGE_SIZE_T4);
		} else {
			$url = $this->portfolioImage->getSizeUrl(\PhotoManager::ORIGINAL_IMAGE_SUBCATEGORY);
		}
		return [
			"id" => $this->portfolioImage->id,
			"hash" => $this->portfolioImage->hash,
			"url" =>$url,
			"urlBig" => $this->portfolioImage->getMaxSizeUrl(),
		];
	}
}