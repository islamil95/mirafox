<?php

namespace Controllers\Track;

use Controllers\BaseController;
use Controllers\Track\Strategy\IGetOrderStrategy;
use Core\Exception\PageNotFoundException;
use Core\Exception\RedirectException;
use Core\Traits\Routing\RoutingTrait;
use Model\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Абстрактный контроллер
 *
 * Class AbstractTrackController
 * @package Controllers\Track
 */
abstract class AbstractTrackController extends BaseController {

	use RoutingTrait;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Получить идентификатор заказа из запроса
	 *
	 * @param Request $request HTTP запрос
	 * @return int идентификатор
	 */
	private function getOrderIdFromRequest(Request $request):int {
		$orderId = $request->query->getInt("id", 0);

		if ($orderId == 0) {
			throw new PageNotFoundException();
		}
		return $orderId;
	}

	/**
	 * Проверка запроса
	 *
	 * @param Request $request HTTP запрос
	 * @return $this
	 */
	private function validateRequest(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			$this->setBackUrl();
			throw (new RedirectException())->setRedirectUrl("/login");
		}
		return $this;
	}

	/**
	 * Получить заказ
	 *
	 * @param int $orderId идентификатор заказа
	 * @return Order заказ
	 */
	private function getOrder($orderId) {
		$strategy = $this->getOrderStrategy($orderId);
		$order = $strategy->getOrder();
		if (is_null($order->kwork) || // Нет кворка в заказе
			($order->isNotBelongToPayerOrWorker($this->getUserId()) && !\UserManager::isModer()) || // Заказ не принадлежит пользователю и пользователь не админ
			$order->isNew() || // Заказ новый и не инициализирован
			$order->tracks->isEmpty() // Нет треков, что само по себе уже странно
		) {
			throw (new RedirectException())->setRedirectUrl("/");
		}
		return $order;
	}

	/**
	 * Проверка типа пользователя и смена его типа при необоходимости
	 *
	 * @param Order $order заказ
	 * @return $this
	 */
	private function checkAndChangeUserType($order) {
		if (($order->USERID == $this->getUserId() && $this->isWorker())
			|| ($order->worker_id == $this->getUserId() && $this->isPayer())) {
			\UserManager::changeUserType();
		}
		return $this;
	}

	/**
	 * Обработка контроллера
	 *
	 * @param Request $request HTTP запрос
	 * @param int|null $orderId идентификатор заказа
	 * @return Response
	 */
	public function __invoke(Request $request, $orderId = null) {
		$this->validateRequest($request);
		if (is_null($orderId)) {
			$orderId = $this->getOrderIdFromRequest($request);
		}
		$order = $this->getOrder($orderId);
		$this->checkAndChangeUserType($order);
		return $this->processRequest($request, $order);
	}

	/**
	 * Логика потомка
	 *
	 * @param Request $request HTTP запрос
	 * @param Order $order заказ
	 * @return Response
	 */
	protected abstract function processRequest(Request $request, Order $order);

	/**
	 * Получить стратегию получения заказа
	 *
	 * @param int $orderId идентификатор заказа
	 * @return IGetOrderStrategy
	 */
	protected abstract function getOrderStrategy(int $orderId): IGetOrderStrategy;
}