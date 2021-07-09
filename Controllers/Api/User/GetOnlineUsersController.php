<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class GetOnlineUsersController
 * @package Controllers\Api\User
 * @deprecated
 */
class GetOnlineUsersController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::getOnlineUsers();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.getOnlineUsers";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}