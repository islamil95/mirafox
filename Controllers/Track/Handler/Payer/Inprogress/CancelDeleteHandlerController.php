<?php


namespace Controllers\Track\Handler\Payer\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель удалил свой запрос на обоюдную отмену заказа
 *
 * Class CancelDeleteHandlerController
 * @package Controllers\Track\Handler\Payer\Inprogress
 */
class CancelDeleteHandlerController extends AbstractTrackHandlerController {

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
		\OrderManager::payer_inprogress_cancel_delete($this->getOrderId());
		return $this->getResponse();
	}
}