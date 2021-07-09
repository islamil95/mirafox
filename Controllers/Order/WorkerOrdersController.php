<?php


namespace Controllers\Order;

use Controllers\BaseController;
use Controllers\Order\Traits\PageLimitTrait;
use Model\Track;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;
use Track\TrackHistory;

/**
 * Страница "Мои заказы" для продавца
 */
class WorkerOrdersController extends BaseController {

	use AuthTrait, PageLimitTrait;

	const TEMPLATE = "manage_orders.tpl";

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
		if ($user->type != \UserManager::TYPE_WORKER) {
			\UserManager::changeUserType();
		}

		$parameters = [
			"ordersPath" => "manage_orders",
			"pagetitle" => \Translations::t("Заказы"),
		];

		$limit = \App::config("per_page_items");
		$paging = new \Paging();
		$paging->items_per_page = $limit;
		$parameters["page_limit"] = $limit;

		$result = \OrderManager::workerOrders(request("b"), request("a"), $paging->getPagingStart(), $paging->items_per_page);

		$sort = $result["sort"];
		$direction = $result["direction"];
		$ordersCount = $result["totalCount"];

		if (!empty($result["orders"])) {
			$tracks = Track::whereIn(Track::FIELD_ORDER_ID, array_column($result["orders"], "OID"))
				->get()
				->groupBy(Track::FIELD_ORDER_ID);
			foreach ($result["orders"] as &$order) {
				$orderTracks = $tracks[$order["OID"]] ?? null;
				if (empty($orderTracks)) {
					continue;
				}
				$order["trackHistory"] = collect((new TrackHistory($orderTracks, "worker"))->getHistory());
				$order["trackHistoryDescriptions"] = $order["trackHistory"]->map(function($item) {
					$item->description = $item->getShortDescription();
					return $item;
				})->pluck("description")->toArray();
				if ($order["status"] == \OrderManager::STATUS_DONE) {
					$start = date_create($order["date_inprogress"]);
					$end = date_create($order["date_done"]);
					$order["timeInWork"] = $end->getTimestamp() - $start->getTimestamp();
				}
			}
		}

		$parameters["userType"] = \UserManager::TYPE_WORKER;
		$parameters["orders"] = $result["orders"] ?? [];
		$parameters["b"] = $sort;
		$parameters["a"] = $direction;
		$parameters["ordersCount"] = $ordersCount;
		$parameters["includeLimitField"] = $ordersCount > 10;
		
		if ($ordersCount > 0) {
			$pagingData = [];
			$pagingData["paging"] = $paging;
			$pagingData["total"] = $ordersCount;
			$pagingData["adds"] = "";
			$pagingData["urlprefix"] = "/manage_orders?b=$sort&a=$direction";

			$parameters["pagingdata"] = $pagingData;
		}

		return $this->render(self::TEMPLATE, $parameters);
	}

}