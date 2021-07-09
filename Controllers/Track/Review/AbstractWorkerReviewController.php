<?php


namespace Controllers\Track\Review;

use Model\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * Абстрактный контроллер для отзывов покупателя
 *
 * Class AbstractWorkerReviewController
 * @package Controllers\Track\Review
 */
abstract class AbstractWorkerReviewController extends AbstractReviewController {

	/**
	 * @inheritdoc
	 */
	protected function validateAccess(Request $request, Order $order): bool {
		return $order->isWorker($this->getUserId());
	}
}