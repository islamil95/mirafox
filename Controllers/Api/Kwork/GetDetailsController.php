<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

class GetDetailsController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \KworkManager::api_getDetails();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork.api_getDetails";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}