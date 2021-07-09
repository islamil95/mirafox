<?php

namespace Controllers\Api\Order;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateController
 * @package Controllers\Api\Order
 */
class CreateController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \OrderManager::api_create();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Order.api_create";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}