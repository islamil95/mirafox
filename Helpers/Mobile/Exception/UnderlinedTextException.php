<?php

namespace Mobile\Exception;

use Mobile\Constants;

class UnderlinedTextException extends \Exception\JsonException
{
	private $text;

	/**
	 * Текст с подчеркиваниями
	 *
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * UnderlinedTextException constructor.
	 *
	 * @param string $message Сообщение об ошибке
	 * @param string $text Текст с подчеркиваними
	 * @param \Throwable|null $previous
	 */
	public function __construct($message, $text, \Throwable $previous = null) {
		$this->text = $text;
		parent::__construct($message,Constants::CODE_SPELL_MISTAKES);
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
			"error_code" => $this->getCode(),
			"text" => $this->text,
		];
	}
}