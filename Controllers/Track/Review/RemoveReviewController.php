<?php


namespace Controllers\Track\Review;


use Model\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Удаление отзыва
 *
 * Class RemoveReviewController
 * @package Controllers\Track\Review
 */
class RemoveReviewController extends AbstractPayerReviewController {

	/**
	 * @inheritdoc
	 */
	protected function processReview(Request $request, Order $order) {
		$redirectUrl = $this->getUrlByRoute("track", $this->getRedirectUrlParameters($order));
		$resp = new RedirectResponse($redirectUrl);
		$reviewId = $request->request->getInt("ratingId");

		if (!\RatingManager::inEditTime($order->review->RID) || $reviewId != $order->review->RID) {
			return $resp;
		}

		\RatingManager::delete($reviewId);
		return $resp;
	}
}