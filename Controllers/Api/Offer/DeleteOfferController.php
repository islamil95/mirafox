<?php


namespace Controllers\Api\Offer;

use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteOfferController
 * @package Controllers\Api\Offer
 */
class DeleteOfferController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \WishOrderManager::api_deleteOffer();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "WishOrder.api_deleteOffer";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}