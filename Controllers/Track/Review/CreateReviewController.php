<?php

namespace Controllers\Track\Review;

use Model\Order;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RatingManager;

/**
 * Созданиe отзыва
 *
 * Class CreateReviewController
 * @package Controllers\Track\Review
 */
class CreateReviewController extends AbstractPayerReviewController {

	const BADGE_TEMPLATE = "track/popup/badge";

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	protected function processReview(Request $request, Order $order) {
		$comment = \Helper::unescapeSlashes($request->request->get("comment"));

		$actorId = $this->getUserId();

		$checkResult = \TrackManager::isValidReviewText($comment);
		if (!$checkResult["success"]) {
			return new JsonResponse($checkResult);
		}

		$return = ["success" => RatingManager::create($order->OID, $request->request->get("vote"), $comment)];

		if (!$return["success"]) {
			$return["data"] = ["error" => "add comment"];
		} else {
			$order->load("review");
			$return["html"] = $this->getAjaxOrderReviewHtml($order);
		}

		return $this->success($return);
	}
}