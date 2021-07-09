<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetOnceSimilarKworkController
 * @package Controllers\Api\Kwork
 */
class GetOnceSimilarKworkController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \KworkManager::api_getOnceSimilarKwork();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork.api_getOnceSimilarKwork";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}