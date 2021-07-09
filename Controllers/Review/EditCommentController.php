<?php


namespace Controllers\Review;


use Controllers\BaseController;
use Model\Rating;
use Symfony\Component\HttpFoundation\Request;

/**
 * Отредактировать комментарий к отзыву
 * Class EditCommentController
 * @package Controllers\Review
 */
class EditCommentController extends BaseController {

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
				"reason" => "not_found_review"
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

		$edited = \RatingManager::editAnswer($review->answer->id, $comment);

		if (!$edited) {
			return $this->failure([
				"reason" => "not_edited"
			]);
		}

		$review->load("answer");

		return $this->success([
			"html" => $this->renderView("reviews_answer_new", ["review" => $review->toRenderReviewsAnswerNew()])
		]);
	}
}