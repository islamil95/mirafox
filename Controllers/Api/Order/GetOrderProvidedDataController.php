<?php


namespace Controllers\Api\Order;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetOrderProvidedDataController
 * @package Controllers\Api\Order
 */
class GetOrderProvidedDataController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \OrderManager::api_getOrderProvidedData();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Order.api_getOrderProvidedData";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}