<?php


namespace Controllers\Track\Handler\Worker\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец не согласился на обоюдную отмену заказа
 *
 * Class CancelRejectHandlerController
 * @package Controllers\Track\Handler\Worker\Inprogress
 */
class CancelRejectHandlerController extends AbstractTrackHandlerController {

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
		\OrderManager::workerInprogressCancelReject($this->getOrderId());
		return $this->getResponse();
	}
}