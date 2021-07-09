<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class GetUserCurrencyByLoginController
 * @package Controllers\Api\User
 */
class GetUserCurrencyByLoginController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_getUserCurrencyByLogin(
			$request->query->get("userLogin")
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_getUserCurrencyByLogin";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return $request->query->has("userLogin") &&
			!empty($request->query->get("userLogin"));
	}
}