<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetOnceOtherKworkSellerController
 * @package Controllers\Api\Kwork
 */
class GetOnceOtherKworkSellerController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \KworkManager::api_getOnceOtherKworkSeller();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork.api_getOnceOtherKworkSeller";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}