<?php

namespace Controllers\Track\Handler\Extra\Worker;


use Controllers\Track\Handler\Extra\AbstractExtraHandlerController;

/**
 * Абстрактный контроллер для обработки дополнительных опций для продавца
 *
 * Class AbstractWorkerExtraHandlerController
 * @package Controllers\Track\Handler\Extra\Worker
 */
abstract class AbstractWorkerExtraHandlerController extends AbstractExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function accessNotAllowed(): bool {
		return !$this->getOrder()->isWorker($this->getUserId());
	}
}