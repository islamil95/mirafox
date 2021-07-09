<?php

namespace Controllers\Track\Handler\Worker\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец удалил свой запрос на обоюдную отмену заказа
 *
 * Class CancelDeleteHandlerController
 * @package Controllers\Track\Handler\Worker\Inprogress
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
		\OrderManager::worker_inprogress_cancel_delete($this->getOrderId());
		return $this->getResponse();
	}
}