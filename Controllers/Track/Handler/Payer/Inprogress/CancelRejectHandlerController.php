<?php


namespace Controllers\Track\Handler\Payer\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель не согласился на обоюдную отмену заказа
 *
 * Class CancelRejectHandlerController
 * @package Controllers\Track\Handler\Payer\Inprogress
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
		\OrderManager::payer_inprogress_cancel_reject($this->getOrderId());
		return $this->getResponse();
	}
}