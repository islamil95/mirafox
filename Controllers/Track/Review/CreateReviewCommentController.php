<?php


namespace Controllers\Track\Review;

use Model\Order;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Создание ответа на отзыв
 *
 * Class CreateReviewCommentController
 * @package Controllers\Track\Review
 */
class CreateReviewCommentController extends AbstractWorkerReviewController {

	/**
	 * @inheritdoc
	 */
	protected function processReview(Request $request, Order $order) {
		$reviewId = $request->request->getInt("review_id");
		$comment = $request->request->get("comment");

		$checkResult = \TrackManager::isValidReviewText($comment);
		if (!$checkResult["success"]) {
			return new JsonResponse($checkResult);
		}

		$return = ["success" => \RatingManager::createAnswer($reviewId, $comment)];

		if (!$return["success"]) {
			$return["data"] = ["error" => "add comment"];
		} else {
			$order->load("review.answer");
			$return["html"] = $this->getAjaxOrderReviewHtml($order);
		}

		return $this->success($return);
	}
}