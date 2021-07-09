<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Не удалось обработать изображение
 */
class IncorrectImage extends JsonException {

	/**
	 * IncorrectImage constructor.
	 *
	 * @param string $message Текст ошибки
	 */
	public function __construct($message = "") {
		if (!$message) {
			$message = \Translations::t("Не удалось загрузить изображение");
		}
		parent::__construct($message, Constants::CODE_INCORRECT_IMAGE);
	}
}