<?php


namespace Controllers\Review;


use Controllers\BaseController;
use Model\Rating;
use Model\RatingComment;
use Model\RatingForDisplay;
use Symfony\Component\HttpFoundation\Request;

/**
 * Отправить комментарий к отзыву
 * Class CreateCommentController
 * @package Controllers\Review
 */
class CreateCommentController extends BaseController {

	public function __invoke(Request $request) {
		if (!$this->isUserAuthenticated()) {
			return $this->failure([
				"reason" => "not_authorized",
			]);
		}
		$reviewId = $request->request->getInt("review_id");
		$comment = $request->request->get("comment");

		$review = Rating::find($reviewId);
		if (!$review) {
			return $this->failure([
				"reason" => "not_found_review",
			]);
		}

		if ($review->order->worker_id != $this->getUserId()) {
			return $this->failure([
				"reason" => "not_allowed",
			]);
		}

		$checkResult = \TrackManager::isValidReviewText($comment);
		if (!$checkResult["success"]) {
			return $this->failure([
				"reason" => "not_valid_text",
				"extra" => $checkResult["result"] ?? [],
			]);
		}

		$created = \RatingManager::createAnswer($reviewId, $comment);

		if (!$created) {
			return $this->failure([
				"reason" => "not_created",
			]);
		}

		$review->load("answer");

		return $this->success([
			"html" => $this->renderView("reviews_answer_new", ["review" => $review->toRenderReviewsAnswerNew()])
		]);
	}
}