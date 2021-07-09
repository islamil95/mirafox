<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Слишком маленькие размеры изображения по ширине или высоте
 */
class TooSmallImageDimensionException extends JsonException {

	/**
	 * TooSmallImageDimensionException constructor.
	 *
	 * @param string $message Сообщение об ошибке
	 */
	public function __construct($message = "") {
		if (!$message) {
			$message = \Translations::t("Слишком маленькие размеры изображения по ширине или высоте");
		}
		parent::__construct($message, Constants::CODE_SMALL_PORTFOLIO_IMAGE);
	}
}