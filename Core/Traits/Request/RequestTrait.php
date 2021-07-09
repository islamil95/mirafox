<?php

namespace Core\Traits\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Методы для информации о запросе
 *
 * @property Request $request
 */
trait RequestTrait {

	/**
	 * Является ли текущий запрос GET запросом
	 *
	 * @return bool
	 */
	protected function isGetRequest(): bool {
		return $this->request->getMethod() == "GET";
	}

	/**
	 * Является ли текущий запрос POST запросом
	 *
	 * @return bool
	 */
	protected function isPostRequest(): bool {
		return $this->request->getMethod() == "POST";
	}
}