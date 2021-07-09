<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class HideTechnicalWorksNotificationController
 * @package Controllers\Api\User
 */
class HideTechnicalWorksNotificationController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		UserManager::api_hideTechnicalWorksNotification();
		return "";
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_hideTechnicalWorksNotification";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}