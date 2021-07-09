<?php

namespace Controllers\Track\Handler\Extra;

use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Абстрактный контроллер для обработки дополнительных опций
 *
 * Class AbstractExtraHandlerController
 * @package Controllers\Track\Handler\Extra
 */
abstract class AbstractExtraHandlerController extends AbstractTrackHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		if ($this->accessNotAllowed()) {
			return new RedirectResponse($this->getRedirectUrl());
		}
		return $this->processExtras();
	}

	/**
	 * Доступ не разрешен
	 *
	 * @return bool результат
	 */
	abstract protected function accessNotAllowed(): bool;

	/**
	 * Логика для обработки
	 *
	 * @return Response HTTP ответа
	 */
	abstract protected function processExtras(): Response;
}