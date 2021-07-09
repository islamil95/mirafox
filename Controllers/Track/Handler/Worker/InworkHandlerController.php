<?php


namespace Controllers\Track\Handler\Worker;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец взял в работу заказ
 *
 * Class InworkHandlerController
 * @package Controllers\Track\Handler\Worker
 */
class InworkHandlerController extends AbstractTrackHandlerController {

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
		\OrderManager::worker_inwork($this->getOrderId());
		return $this->getResponse();
	}
}