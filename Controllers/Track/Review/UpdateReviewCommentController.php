<?php


namespace Controllers\Track\Review;


use Model\Order;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Изменение коментария на отзыв
 *
 * Class UpdateReviewCommentController
 * @package Controllers\Track\Review
 */
class UpdateReviewCommentController extends AbstractWorkerReviewController {

	/**
	 * @inheritdoc
	 */
	protected function processReview(Request $request, Order $order) {
		$comment = $request->request->get("comment");

		$check_result = \TrackManager::isValidReviewText($comment);
		if(!$check_result["success"]) {
			return new JsonResponse($check_result);
		}

		$return = ['success' => \RatingManager::editAnswer($order->review->answer->id, $comment)];

		if (!$return["success"]) {
			$return["data"] = ["error" => "add comment"];
		} else {
			$order->load("review.answer");
			$return["html"] = $this->getAjaxOrderReviewHtml($order);
		}

		return $this->success($return);
	}
}