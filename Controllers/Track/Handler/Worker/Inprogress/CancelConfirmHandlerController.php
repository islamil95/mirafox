<?php


namespace Controllers\Track\Handler\Worker\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец согласился на обоюдную отмену заказа
 *
 * Class CancelConfirmHandlerController
 * @package Controllers\Track\Handler\Worker\Inprogress
 */
class CancelConfirmHandlerController extends AbstractTrackHandlerController {

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
		\OrderManager::worker_inprogress_cancel_confirm($this->getOrderId());
		return $this->getResponse();
	}
}