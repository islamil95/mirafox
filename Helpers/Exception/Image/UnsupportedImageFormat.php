<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Неподдерживаемый формат изображения
 */
class UnsupportedImageFormat extends JsonException {

	/**
	 * UnsupportedImageFormat constructor.
	 *
	 * @param string $message Текст ошибки
	 */
	public function __construct($message = "") {
		if(!$message){
			$message = \Translations::t("Неподдерживаемый формат изображения");
		}
		parent::__construct($message, Constants::CODE_UNSUPPORTED_IMAGE_FORMAT);
	}
}