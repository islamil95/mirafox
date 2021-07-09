<?php


namespace Controllers\Api\Offer;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddViewController
 * @package Controllers\Api\Offer
 */
class AddViewController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \WantManager::api_addView(
			$request->get("wantIds")
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Want.api_addView";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return !$request->query->has("wantId") ||
			$request->query->getInt("wantId") <= 0;
	}
}