<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckLoginController
 * @package Controllers\Api\User
 */
class CheckLoginController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \UserManager::checkLogin();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.checkLogin";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}