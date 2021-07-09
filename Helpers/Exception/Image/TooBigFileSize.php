<?php


namespace Exception\Image;

use Exception\JsonException;
use Mobile\Constants;

/**
 * Слишком большой размер изображения
 */
class TooBigFileSize extends JsonException {

	/**
	 * TooBigFileSize constructor.
	 *
	 * @param string $message Текст эксепшена
	 */
	public function __construct($message = "") {
		if (!$message) {
			\Translations::t("Слишком большой размер файла");
		}
		parent::__construct($message, Constants::CODE_FILE_SIZE_EXEED);
	}
}