<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class ChangeEmailController
 * @package Controllers\Api\User
 */
class ChangeEmailController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::changeemail();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.changeemail";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}