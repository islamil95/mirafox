<?php


namespace Controllers\Api\Order;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetOrdersCountController
 * @package Controllers\Api\Order
 */
class GetOrdersCountController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \OrderManager::api_getOrdersCount();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Order.api_getOrdersCount";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}