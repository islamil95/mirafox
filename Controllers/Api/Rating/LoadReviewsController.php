<?php


namespace Controllers\Api\Rating;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoadReviewsController
 * @package Controllers\Api\Rating
 */
class LoadReviewsController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \RatingManager::api_loadReviews(
			$request->query->get("entity"),
			$request->query->getInt("id"),
			$request->query->get("type"),
			$request->query->getInt("offset")
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Rating.api_loadReviews";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return !$request->query->has("entity") ||
			!$request->query->has("id") ||
			!$request->query->has("type") ||
			!$request->query->has("offset");
	}
}