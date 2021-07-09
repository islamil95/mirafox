<?php


namespace Controllers\Track\Handler\Extra\Payer;


use Controllers\Track\Handler\Extra\AbstractExtraHandlerController;

/**
 * Абстрактный контроллер для обработки дополнительных опций для покупателя
 *
 * Class AbstractPayerExtraHandlerController
 * @package Controllers\Track\Handler\Extra\Payer
 */
abstract class AbstractPayerExtraHandlerController extends AbstractExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function accessNotAllowed(): bool {
		return !$this->getOrder()->isPayer($this->getUserId()) ||
			$this->getOrder()->isNotInBuyStatus();
	}
}