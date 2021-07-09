<?php


namespace Controllers\Track\Handler\Worker\Check;

use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец отправил заказ в арбитраж
 *
 * Class ArbitrageHandlerController
 * @package Controllers\Track\Handler\Worker\Check
 */
class ArbitrageHandlerController extends AbstractTrackHandlerController {

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
		$message = $this->getMessage();
		$articleId = $this->getRequest()->request->getInt("article");
		$roleId = $this->getRequest()->request->getInt("role");
		$orderStagesIds = \Helper::intArrayNoEmpty(explode(",", (string)$this->getRequest()->request->get("stageIds")));
		if ($message) {
			\OrderManager::worker_check_arbitrage($this->getOrderId(), $message, $articleId, $roleId, $orderStagesIds);
		}
		return $this->getResponse();
	}
}