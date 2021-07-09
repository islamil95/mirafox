<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class IsPaymentPayedController
 * @package Controllers\Api\User
 */
class IsPaymentPayedController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_isPaymentPayed();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_isPaymentPayed";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}