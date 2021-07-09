<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Не предоставлены размеры изображений для ресайза
 */
class NoCropSizesException extends JsonException {

	/**
	 * NoCropSizesException constructor.
	 *
	 * @param string $message Текст сообщения
	 */
	public function __construct($message = "") {
		if (!$message) {
			$message = \Translations::t("Не предоставлены размеры изображений для ресайза");
		}
		parent::__construct($message, Constants::CODE_NO_CROP_IMAGE_DATA);
	}
}