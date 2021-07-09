<?php


namespace Controllers\Track\Review;


use Controllers\Track\AbstractTrackController;
use Controllers\Track\Strategy\GetFullOrderStrategy;
use Controllers\Track\Strategy\IGetOrderStrategy;
use Model\Order;
use Strategy\Track\GetAvailableReviewTypeStrategy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Track\Factory\ReviewToTrackViewFactory;
use Track\View\Review\ReviewView;

/**
 * Абстрактный контроллер для обработки отзывов
 *
 * Class AbstractReviewController
 * @package Controllers\Track\Review
 */
abstract class AbstractReviewController extends AbstractTrackController {

	/**
	 * Проверка доступа
	 *
	 * @param Request $request HTTP запрос
	 * @param Order $order заказ
	 * @return bool резульат
	 */
	protected abstract function validateAccess(Request $request, Order $order):bool;

	/**
	 * Основная логика
	 *
	 * @param Request $request HTTP запрос
	 * @param Order $order заказ
	 * @return Response
	 */
	protected abstract function processReview(Request $request, Order $order);

	/**
	 * Получение параметров для URL
	 *
	 * @param Order $order заказ
	 * @return array
	 */
	protected function getRedirectUrlParameters(Order $order) {
		return [
			"id" => $order->OID,
			"scroll" => 1,
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function processRequest(Request $request, Order $order) {
		if ($this->validateAccess($request, $order)) {
			return $this->processReview($request, $order);
		}
		$redirectUrl = $this->getUrlByRoute("track", $this->getRedirectUrlParameters($order));
		return new RedirectResponse($redirectUrl);
	}

	/**
	 * @inheritdoc
	 */
	protected function getOrderStrategy(int $orderId): IGetOrderStrategy {
		return new GetFullOrderStrategy($orderId);
	}

	/**
	 * Получить html блока с отзавом к заказу
	 * @param Order $order Заказ
	 * @return string
	 */
	protected function getAjaxOrderReviewHtml(Order $order) {
		$view = ReviewToTrackViewFactory::getInstance()->getView($order);
		if ($view instanceof ReviewView) {
			$view->setParameters([
				"order" => $order,
				"editTypeReview" => (new GetAvailableReviewTypeStrategy($order))->get(),
				"isFromAjax" => true,
			]);
		}
		return $view->render();
	}
}