<?php


namespace Controllers\Api\Offer;

use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteOfferController
 * @package Controllers\Api\Offer
 */
class HideOfferController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \OfferManager::api_hideOffer();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "OfferManager.api_hideOffer";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}