<?php


namespace Controllers\Track\Review;


use Model\Order;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Изменение отзыва
 *
 * Class UpdateReviewController
 * @package Controllers\Track\Review
 */
class UpdateReviewController extends AbstractPayerReviewController {

	/**
	 * @inheritdoc
	 */
	protected function processReview(Request $request, Order $order) {
		$actor = \UserManager::getCurrentUser();
		$string = \Helper::unescapeSlashes($request->request->get('comment'));

		$check_result = \TrackManager::isValidReviewText($string);
		if(!$check_result["success"]) {
			return new JsonResponse($check_result);
		}

		if ( $actor->id != $order->USERID && !\UserManager::isModer()) {
			return ['success' => false, 'result' => ['error' => 'wrong user']];
		}

		$return = ['success' => \RatingManager::update($order->OID, $request->request->get("vote"), $string)];

		if ($return["success"]) {
			$order->load("review");
			$return["html"] = $this->getAjaxOrderReviewHtml($order);
		}

		return $this->success($return);
	}
}