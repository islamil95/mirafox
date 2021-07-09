<?php

namespace Exception;

use Throwable;

/**
 * Class JsonException Эксепшен который сериализуется в json
 * @package Exception
 */
class JsonException extends \Exception implements \JsonSerializable
{
	/**
	 * JsonException constructor. Делаем обязательными поля
	 * @param string $message Сообщение об ошибке
	 * @param int $code Код ошибки
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message, int $code, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Реализуем метод для json сериализации
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			"success" => false,
			"error" => $this->getMessage(),
			"error_code" => $this->getCode()
		];
	}
}