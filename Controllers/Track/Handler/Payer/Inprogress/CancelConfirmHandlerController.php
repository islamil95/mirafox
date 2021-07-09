<?php

namespace Controllers\Track\Handler\Payer\Inprogress;

use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель согласился на обоюдную отмену заказа
 *
 * Class CancelConfirmHandlerController
 * @package Controllers\Track\Handler\Payer\Inprogress
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
		\OrderManager::payer_inprogress_cancel_confirm($this->getOrderId(), $this->getReplyType());
		return $this->getResponse();
	}
}