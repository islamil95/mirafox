<?php


namespace Controllers\Api\User;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckNotifyController
 * @package Controllers\Api\User
 */
class CheckNotifyController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \UserManager::checkNotify();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "User.checkNotify";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		//@todo: Временное решение при переключении механизма уведомлений. После полного запуска пуш-уведомлений можно удалить за ненадобностью
		return \Helper::isModuleChatEnable();
	}
}