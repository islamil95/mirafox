<?php

/**
 * Class Result
 * Класс для хранения результата, и оъединения различных результатов
 */
class Result {
	private $_result;

	public function __construct() {
		$this->_result = true;
	}

	/**
	 * Проверяет результат на успешность
	 * @return bool
	 */
	public function isSuccess() {
		return $this->_result;
	}

	/**
	 * Проверяет результат на НЕ успешность
	 * @return bool
	 */
	public function isNotSuccess() {
		return !$this->_result;
	}

	/**
	 * Объединеят текущий результат с новым
	 * Результат в виде `0` считается ошибкой
	 *
	 * @param $result
	 */
	public function mergeResult($result) {
		if($this->isSuccess()) {
			if($result == false) {
				$this->_result = false;
			}
		}
	}

	/**
	 * Производит абсолютное сравнение с false и null
	 * Проверяет на идентичность: результат в виде `0` НЕ считается ошибкой
	 *
	 * @param $result
	 */
	public function mergeResultIdentical($result) {
		if($this->isSuccess()) {
			if($result === false || $result === null) {
				$this->_result = false;
			}
		}
	}
}