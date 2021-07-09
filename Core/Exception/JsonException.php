<?php


namespace Core\Exception;

/**
 * Ошибка вернет JSON данные
 *
 * Class JsonException
 * @package Core\Exception
 */
class JsonException extends \RuntimeException {

	private $data = [];


	public function setData(array $data): self {
		$this->data = $data;
		return $this;
	}

	public function getData(): array {
		return $this->data;
	}
}