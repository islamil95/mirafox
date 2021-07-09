<?php


namespace Controllers\Track\Review;


use Model\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * Абстрактный контроллер для отызов покупателя
 *
 * Class AbstractPayerReviewController
 * @package Controllers\Track\Review
 */
abstract class AbstractPayerReviewController extends AbstractReviewController {

	/**
	 * @inheritdoc
	 */
	protected function validateAccess(Request $request, Order $order): bool {
		return $order->isPayer($this->getUserId()) || \UserManager::isModer();
	}
}