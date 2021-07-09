<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class CheckWorkerOffersController
 * @package Controllers\Api\User
 */
class CheckWorkerOffersController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_checkWorkerOffers();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_checkWorkerOffers";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}