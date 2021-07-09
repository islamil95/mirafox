<?php


namespace Controllers\Api\Offer;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateOfferController
 * @package Controllers\Api\Offer
 */
class CreateOfferController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \WantManager::api_createOffer();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Want.api_createOffer";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}