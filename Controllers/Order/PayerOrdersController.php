<?php


namespace Controllers\Order;

use Controllers\BaseController;
use Controllers\Order\Traits\PageLimitTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;

/**
 * Страница "Мои заказы" для покупателя
 */
class PayerOrdersController extends BaseController {

	use AuthTrait, PageLimitTrait;

	const TEMPLATE = "orders.tpl";

	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}

		$user = $this->getUser();
		if ($user->type != \UserManager::TYPE_PAYER) {
			\UserManager::changeUserType();
		}

		// Для писем в которых уже ссылки на /orders?s=projects
		if ($request->get("s") == "projects") {
			return new RedirectResponse("/manage_projects");
		}

		$parameters = [
			"ordersPath" => "orders",
			"pagetitle" => \Translations::t("Мои заказы"),
		];

		if ($request->query->get("new") == 1) {
			$parameters["message"] = \Translations::t("Ваш заказ был успешно размещён.");
		}

		$limit = \App::config("per_page_items");
		$paging = new \Paging();
		$paging->items_per_page = $limit;
		$parameters["page_limit"] = $limit;

		$result = \OrderManager::payerOrders($request->get("b"), $request->get("a"), $paging->getPagingStart(), $paging->items_per_page);

		$sort = $result["sort"];
		$direction = $result["direction"];
		$ordersCount = $result["totalCount"];

		$dateHeadByColumn = [
			'stime' => \Translations::t('Заказан'),
			'date_done' => \Translations::t('Выполнен'),
			'date_cancel' => \Translations::t('Отменен')
		];
		$parameters["dateColumn"] = $result["dateColumn"];
		$parameters["dateHead"] = $dateHeadByColumn[$result["dateColumn"]];

		$parameters["orders"] = $result["orders"] ?? [];
		$parameters["b"] = $sort;
		$parameters["a"] = $direction;
		$parameters["o"] = $result["orders"];
		$parameters["ordersCount"] = $ordersCount;
		$parameters["includeLimitField"] = $ordersCount > 10;
		$parameters["isStageTester"] = \Order\Stages\OrderStageOfferManager::isTester();

		if ($ordersCount > 0) {
			$pagingData = [];
			$pagingData["paging"] = $paging;
			$pagingData["total"] = $ordersCount;
			$pagingData["adds"] = "";

			$pagingData["urlprefix"] = "/orders?b=$sort&a=$direction";

			$parameters["pagingdata"] = $pagingData;
		}

		return $this->render(self::TEMPLATE, $parameters);
	}

}