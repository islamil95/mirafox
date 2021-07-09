<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class CheckSettingsUsernameController
 * @package Controllers\Api\User
 */
class CheckSettingsUsernameController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_checkSettingsUsername();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_checkSettingsUsername";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}