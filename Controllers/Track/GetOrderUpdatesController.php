<?php


namespace Controllers\Track;


use Controllers\BaseController;
use Model\Order;
use Strategy\Track\GetRenderParametersStrategy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Получение html для обновления верхней части заказа, после пушей
 */
class GetOrderUpdatesController extends BaseController {

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Core\Response\BaseJsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function __invoke(Request $request) {
		$orderId = $request->request->getInt("orderId");

		$currentUserId = $this->getCurrentUserId();

		// Данные сообщение не выводятся пользователю, они для разработчиков
		if (empty($currentUserId)) {
			return $this->failure("Not authorized");
		}

		if (empty($orderId)) {
			return $this->failure("Incorrect orderId");
		}

		$order = Order::find($orderId);

		if (is_null($order)) {
			return $this->failure("Order not found");
		}

		if ($order->isNotBelongToPayerOrWorker($currentUserId)) {
			return $this->failure("Access denied");
		}

		$parameters = (new GetRenderParametersStrategy($order))->get();
		$orderHtmlView = $this->renderView("track/order", $parameters);
		$orderHtmlView .= $this->renderView("track/order_upgrade", $parameters);

		return new JsonResponse([
			"success" => true,
			"orderHtml" => $orderHtmlView,
		]);
	}
}