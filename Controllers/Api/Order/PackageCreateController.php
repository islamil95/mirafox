<?php


namespace Controllers\Api\Order;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PackageCreateController
 * @package Controllers\Api\Order
 */
class PackageCreateController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \OrderManager::api_package_create();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Order.api_package_create";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}