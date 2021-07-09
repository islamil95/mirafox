<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

/**
 * Class SetLookedLessonController
 * @package Controllers\Api\User
 */
class SetLookedLessonController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return UserManager::api_setLookedLesson($this->getUserId());
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.api_setLookedLesson";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return $this->isUserNotAuthenticated();
	}
}