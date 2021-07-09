<?php

namespace Core\Traits\Moderator;

use Core\Response\BaseJsonResponse;

/**
 * Обработка ошибок при модерации
 *
 * Trait FailResponseTrait
 * @package Core\Traits\Moderator
 */
trait FailResponseTrait {

	/**
	 * Создать ответ с ошибкой
	 *
	 * @param string $message сообщение
	 * @return $this
	 */
	protected function responseFail($message) {
		return (new BaseJsonResponse())
			->setStatus(false)
			->setMessage($message);
	}
}