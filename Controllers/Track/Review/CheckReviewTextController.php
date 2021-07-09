<?php


namespace Controllers\Track\Review;
use Controllers\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Проверка текста отзыва на валидность
 *
 * Class CreateReviewCommentController
 * @package Controllers\Track\Review
 */
class CheckReviewTextController extends BaseController {

	public function __invoke(Request $request): JsonResponse {
		$string = \Helper::unescapeSlashes($request->request->get('comment'));
		
		$checkResult = \TrackManager::isValidReviewText($string);

		return new JsonResponse($checkResult);
	}
}