<?php


namespace Controllers\Balance;


use Controllers\BaseController;
use Core\Exception\UnauthorizedException;
use OperationManager;
use Paging;
use Symfony\Component\HttpFoundation\Request;
use UserManager;

class IndexController extends BaseController {

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			throw new UnauthorizedException();
		}
		$paging = new Paging();
		$pagingstart = $paging->getPagingStart();
		$itemsPerPage = $paging->items_per_page;

		$operations = OperationManager::getUserOperations($itemsPerPage, $pagingstart);

		$actor = UserManager::getCurrentUser();

		$params = [];
		$params["ordersList"] = $operations["ordersList"];
		$params["o"] = $operations["operations"];
		$params["role"] = $actor->role;
		$total = $operations["total"];
		$params["total"] = round($total, 0);
		$params["totalSum"] = $operations["totalSum"];
		$params["dateLimit"] = $operations["dateLimit"];
		$params["positiveOperations"] = $operations['allUserOperations']["positive"] ?? [];
		$params["negativeOperations"] = $operations['allUserOperations']["negative"] ?? [];
		$params["userBlocked"] = UserManager::isActorBlocked();
		$params["showAmount"] = false;
		if ($total > 0) {
			$pagingData = [];
			$pagingData["paging"] = $paging;
			$pagingData["total"] = $total;
			$pagingData["adds"] = "";
			$pagingData["urlprefix"] = "/balance?";
			$params["pagingdata"] = $pagingData;
		}

		return $this->render("balance", $params);
	}

}