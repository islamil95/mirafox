<?php

namespace Core\Exception;

/**
 * Упрощенный эксепшен для отображения json ошибки
 */
class SimpleJsonException extends JsonException {

	/**
	 * SimpleJsonException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(string $message, int $code = 0, \Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		// Поддерживаем весь зоопарк наших ошибок
		$data = [
			"success" => false,
			"message" => $message,
			"error" => $message,
			"response" => $message,
			"status" => "error",
		];
		if ($code) {
			$data["code"] = $code;
		}
		$this->setData($data);
	}
}