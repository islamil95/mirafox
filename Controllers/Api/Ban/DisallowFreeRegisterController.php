<?php


namespace Controllers\Api\Ban;

use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DisallowFreeRegisterController
 * @package Controllers\Api\Ban
 */
class DisallowFreeRegisterController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}