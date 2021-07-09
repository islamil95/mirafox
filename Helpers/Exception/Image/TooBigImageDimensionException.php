<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Слишком большие размеры изображения по ширине или высоте
 */
class TooBigImageDimensionException extends JsonException {

	/**
	 * TooBigImageDimensionException constructor.
	 *
	 * @param string $message Текст ошибки
	 */
	public function __construct($message = "") {
		if (!$message) {
			\Translations::t("Некорректный размер изображения по ширине или высоте");
		}
		parent::__construct($message, Constants::CODE_BIG_PORTFOLIO_IMAGE);
	}
}