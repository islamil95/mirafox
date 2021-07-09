<?php

namespace Controllers\Want\Payer\Handler;

use Controllers\BaseController;
use Core\DB\DB;
use Core\Exception\BalanceDeficitWithOrderException;
use Core\Exception\JsonValidationException;
use Core\Traits\Routing\RoutingTrait;
use Model\Order;
use Model\OrderStages\OrderStage;
use Model\Want;
use Symfony\Component\HttpFoundation\JsonResponse;
use Order\Exception\AlreadyPaidException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка заказа из предложения на запрос услуг
 *
 * Class OrderHandlerController
 * @package Controllers\Want\Payer\Handler
 */
class OrderHandlerController extends BaseController {

	use RoutingTrait;

	/**
	 * Получить запрос на услуги
	 *
	 * @param int $wantId идентификатор запроса на услуги
	 * @return Want запрос на услуги
	 */
	private function getWant($wantId) {
		$want = Want::whereKey($wantId)
			->where(\WantManager::F_USER_ID, $this->getUserId())
			->first();
		if (empty($want) || $want->isArchive()) {
			return null;
		}
		return $want;
	}

	/**
	 * Получить заказ
	 *
	 * @param int $orderId идентификатор заказа
	 * @return \stdClass заказ
	 */
	private function getOrder($orderId) {
		return DB::table(\OrderManager::TABLE_NAME)
			->where(\OrderManager::F_OID, $orderId)
			->first();
	}

	/**
	 * Обновить стоимость заказа в последнем заказе
	 *
	 * @param int $orderPrice стоимость заказа
	 * @param int|null $orderCurrencyId Валюта заказа
	 * @param float|null $orderCurrencyRate Курс валюты заказа
	 *
	 * @return $this
	 */
	public function updateOrderPrice($orderPrice, $orderCurrencyId = null, $orderCurrencyRate = null) {
		$redisManager = \RedisManager::getInstance();
		$orderData = $redisManager->getActor("last_order");
		$orderData["price"] = $orderPrice;
		$orderData["currency_id"] = $orderCurrencyId;
		$orderData["currency_rate"] = $orderCurrencyRate;
		$redisManager->setActor("last_order", $orderData);
		return $this;
	}

	/**
	 * Обработка случая с нехваткой средств на счете
	 *
	 * @param int $orderId идентификатор заказа
	 * @param int $wantId идентификатор запроса на услуги
	 * @param int $createdOrderId идентификатор созданного заказа
	 * @param int $orderPrice стоимость заказа
	 * @param float $needMoney Сколько необходимо доплатить для создания заказа
	 *
	 * @return string ссылка для редиректа
	 */
	private function handleNotEnoughFounds($orderId, $wantId, $createdOrderId, $orderPrice, $needMoney) {

		$this->session->set("refillType", "project");
		$this->session->set("refillOrderId", $createdOrderId);
		$this->session->set("paramRefillOrder", [
			"wantId" => $wantId,
			"orderId" => $orderId,
			"newOrderId" => $createdOrderId,
		]);
		$urlParams = [
			"id" => $wantId,
			"balance" => 1,
		];

		$orderCurrencyData = \Model\Order::select([\OrderManager::F_CURRENCY_ID, \OrderManager::F_CURRENCY_RATE])
			->where(\OrderManager::F_ORDER_ID, $orderId)
			->get()
			->first()
			->toArray();

		if (\App::config("redis.enable")) {
			$this->updateOrderPrice($orderPrice, $orderCurrencyData[\OrderManager::F_CURRENCY_ID], $orderCurrencyData[\OrderManager::F_CURRENCY_RATE]);
			$urlParams["fill"] = 1;
		}

		$this->session->set("balance_popup_amount", $needMoney);
		return $this->getUrlByRoute("view_offers_all", $urlParams);
	}

	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function __invoke(Request $request) {
		$wantId = $request->request->getInt("id");
		$stages = (array)$request->request->get("stages", []);
		$days = $request->request->getInt("days");
		$want = $this->getWant($wantId);
		if (empty($want)) {
			return new JsonResponse([
				"success" => false,
				"redirectUrl" => $this->getUrlByRoute("manage_projects"),
			]);
		}
		$orderId = $request->request->getInt("order_id");
		$order = $this->getOrder($orderId);
		if (empty($order)) {
			return new JsonResponse([
				"success" => false,
				"redirectUrl" => $this->getUrlByRoute("view_offers_all", ["id" => $wantId]),
			]);
		}

		if ($request->request->get("test_with_stages")) {
			$stages = OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)->get()->toArray();
		}

		// Добавляем в заказ идентификтор запроса
		$order->project_id = $wantId;

		try {
			$createdOrderId = \OrderManager::createByOffersOrder($order, $stages, $days, $want);
			\OfferManager::setDoneByProject($wantId, $orderId);
			$result = [
				"success" => true,
				"redirectUrl" => "/track?id=" . $createdOrderId,
			];
		} catch (BalanceDeficitWithOrderException $exception) {
			$this->handleNotEnoughFounds($orderId, $wantId, $exception->getOrderId(), $order->price, $exception->getNeedMoney());
			$offerOrder = Order::find($orderId);
			$result = $exception->getData();
			$result["stagedData"] = [
				"stageMaxDecreaseDays" => $offerOrder->getMaxDecreaseDays(),
				"price" => $offerOrder->price,
				"kworkDays" => $offerOrder->kwork_days,
				"stages" => $offerOrder->stages,
			];
		} catch (AlreadyPaidException $exception) {
			$result = [
				"success" => false,
				"redirectUrl" => "/track?id=" . $exception->getOrderId(),
			];
		} catch (JsonValidationException $exception) {
			throw $exception;
		} catch (\Exception $exception) {
			$this->addFlashError(\Translations::t("Произошла ошибка при создании заказа"));
			$result = [
				"success" => false,
				"redirectUrl" => $this->getUrlByRoute("view_offers_all", ["id" => $wantId]),
			];
		}

		return new JsonResponse($result);
	}
}