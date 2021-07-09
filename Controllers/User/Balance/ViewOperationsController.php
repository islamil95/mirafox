<?php


namespace Controllers\User\Balance;


use Controllers\BaseController;

use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;
use OperationManager;
use Paging;


/**
 * Получить список операций на странице баланса с учетом значений фильтра
 *
 * Class ViewOperationsController
 * @package Controllers\Users\Balance
 */
class ViewOperationsController extends BaseController
{

	use AuthTrait;

	public function __invoke(Request $request)
	{
		$user = $this->getUserModel();
		$filter = array();
		$filter["dateFrom"] = $request->request->get("date_from", false);
		$filter["dateTo"] = $request->request->get("to_date", false);
		$filter["typeOperation"] = $request->request->get("type_operation");
		$filter["kwork"] = $request->request->get("name_kwork", false);
		$pagingstart = $request->request->get("page", 1);

		$paging = new Paging();
		$itemsPerPage = $paging->items_per_page;

		$pagingstart = ($pagingstart - 1) * $itemsPerPage;

		$operations = OperationManager::getUserOperations($itemsPerPage, $pagingstart, $filter);

		$total = $operations['total'];
		if ($total > 0) {
			$pagingData = [];
			$pagingData['paging'] = $paging;
			$pagingData['total'] = $total;
			$pagingData['adds'] = "";
			$pagingData['urlprefix'] = "/balance?";
		}


		$parameters = array();
		$parameters["o"] = $operations["operations"];
		$parameters["pagingdata"] = $pagingData;
		$parameters["totalSum"] = $operations["totalSum"];
		$parameters["totalItems"] = $total;

		$parameters["showAmount"] = true;
		if($filter["typeOperation"][0] == "all" && !$filter["kwork"]){
			$parameters["showAmount"] = false;
		}
		return $this->render("balance_table", $parameters);
	}
}