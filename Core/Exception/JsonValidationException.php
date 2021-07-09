<?php


namespace Core\Exception;

/**
 * Ошибка вернет JSON данные
 *
 * Class JsonException
 * @package Core\Exception
 */
class JsonValidationException extends JsonException {

	/**
	 * JsonValidationException constructor.
	 *
	 * @param array $errors Массив ошибок полей
	 * @param string $message
	 * @param int $code Код ошибки
	 * @param \Throwable|null $previous
	 */
	public function __construct(array $errors = [], string $message = "", int $code = 422, \Throwable $previous = null) {
		if (empty($message)) {
			$message = \Translations::t("Ошибка валидации");
		}
		parent::__construct($message, $code, $previous);
		// Поддерживаем весь зоопарк наших ошибок
		$this->setData([
			"success" => false,
			"status" => "error",
			"message" => $message,
			"error" => $message,
			"response" => $message,
			"code" => $code,
			"errors" => $errors
		]);
	}
}