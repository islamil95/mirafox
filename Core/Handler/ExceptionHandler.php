<?php

namespace Core\Handler;

use Controllers\Error\JsonController;
use Controllers\Error\MaintenanceController;
use Controllers\Error\PageNotFoundController;
use Controllers\Error\RedirectController;
use Controllers\Error\UnauthorizedController;
use Core\Exception\JsonException;
use Core\Exception\PageNotFoundException;
use Core\Exception\RedirectException;
use Core\Exception\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * обработчик исключений
 *
 * Class ExceptionHandler
 * @package Core\Handler
 */
class ExceptionHandler {

	/**
	 * Проверка, нужно ли вывести исключение или обработать его
	 *
	 * @param \Exception $exception исключение
	 * @return bool резултат
	 */
	private function isThrowError(\Exception $exception):bool {
		return \App::isDebugEnable() &&
			!$exception instanceof RedirectException &&
			!$exception instanceof JsonException;
	}

	/**
	 * Обработка исключения
	 *
	 * @param \Exception $exception исключение
	 * @return Response ответ
	 * @throws \Exception
	 */
	public function handler(\Exception $exception): Response {
		if ($this->isThrowError($exception)) {
			throw $exception;
		}

		$controller = null;
		switch (true) {
			case $exception instanceof NotFoundHttpException:
			case $exception instanceof PageNotFoundException:
			case $exception instanceof MethodNotAllowedException:
			case $exception instanceof ModelNotFoundException:
				$controller = new PageNotFoundController();
				break;
			case $exception instanceof UnauthorizedException:
				$controller = new UnauthorizedController();
				break;
			case $exception instanceof RedirectException:
				$controller = new RedirectController($exception->getRedirectUrl());
				break;
			case $exception instanceof JsonException:
				$controller = new JsonController($exception->getData());
				break;
			default:
				$controller = new MaintenanceController();
				break;
		}
		return $controller();
	}

}