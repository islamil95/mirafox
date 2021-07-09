<?php

namespace Controllers\Track\Handler\Payer\Check;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель отклонил работу
 *
 * Class InprogressHandlerController
 * @package Controllers\Track\Handler\Payer\Check
 */
class InprogressHandlerController extends AbstractTrackHandlerController {

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
		\OrderManager::payer_check_inprogress($this->getOrderId(), $this->getMessage());
		return $this->getResponse();
	}
}