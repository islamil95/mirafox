<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class GetWorkerStatusHelpController
 * @package Controllers\Api\User
 */
class GetWorkerStatusHelpController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_getWorkerStatusHelp();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_getWorkerStatusHelp";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}