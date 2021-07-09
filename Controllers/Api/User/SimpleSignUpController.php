<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class SimpleSignUpController
 * @package Controllers\Api\User
 */
class SimpleSignUpController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::apiSimpleSignup();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.apiSimpleSignup";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}