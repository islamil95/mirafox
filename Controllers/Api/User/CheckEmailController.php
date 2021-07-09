<?php

namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class CheckEmailController
 * @package Controllers\Api\User
 */
class CheckEmailController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::checkEmail(
			$request->query->get("email")
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.checkEmail";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return !$request->query->has("email") ||
			empty($request->query->get("email"));
	}
}