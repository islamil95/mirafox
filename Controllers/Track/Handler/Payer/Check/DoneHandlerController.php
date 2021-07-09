<?php


namespace Controllers\Track\Handler\Payer\Check;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель принял работу
 *
 * Class DoneHandlerController
 * @package Controllers\Track\Handler\Payer\Check
 */
class DoneHandlerController extends AbstractTrackHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * Получить значение из HTTP запроса
	 *
	 * @return mixed
	 */
	private function getAllowPortfolioItem() {
		return $this->getRequest()->request->get("allow_portfolio_item");
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		\OrderManager::payer_check_done($this->getOrderId(), $this->getAllowPortfolioItem());
		return $this->getResponse();
	}
}