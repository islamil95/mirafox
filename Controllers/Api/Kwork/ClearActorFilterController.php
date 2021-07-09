<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Kwork\KworkGroup\KworkRatingGroupSortManager;
use Symfony\Component\HttpFoundation\Request;

class ClearActorFilterController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		KworkRatingGroupSortManager::clearActorFilter();
		return "";
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork\KworkGroup\KworkRatingGroupSort.clearActorFilter";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}