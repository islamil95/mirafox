<?php


namespace Controllers\Want\Worker;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * AJAX подгрузка списка запросов на услуги
 *
 * Class AjaxLoadingController
 * @package Controllers\Want\Worker
 */
class AjaxLoadingController extends AbstractWantsController {

	/**
	 * @inheritdoc
	 */
	protected function getSelectedCategoryId(Request $request) {
		return $request->request->get("c");
	}

	/**
	 * @inheritdoc
	 */
	protected function getFavouriteSelectedCategoryId(Request $request) {
		return $request->request->get("fc");
	}

	/**
	 * @inheritdoc
	 */
	protected function getShowWantsForMyKworks(Request $request) {
		return $request->request->get("a");
	}

	/**
	 * @inheritdoc
	 */
	protected function getFilter(Request $request) : array {
		$filter = [];
		if($request->request->has("price-from")) {
			$filter["price_from"] = $request->request->get("price-from");
		}
		if($request->request->has("price-to")) {
			$filter["price_to"] = $request->request->get("price-to");
		}
		if($request->request->has("hiring-from")) {
			$hiringFrom = $request->request->get("hiring-from");
			if (is_numeric($hiringFrom)) {
				if ($hiringFrom > 99) {
					$hiringFrom = 99;
				}
				$filter["hiring_from"] = $hiringFrom;
			}
		}
		//фильтр по количеству предложений на запросы на услуги
		if ($request->request->has("kworks-filters")) {
			$filter["kworks_filters"] = (array)$request->request->get("kworks-filters");
		}
		//множественные фильтры по биджету запросов на услуги
		if ($request->request->has("prices-filters")) {
			$filter["prices_filters"] = (array)$request->request->get("prices-filters");
		}
		return $filter;
	}

	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request) {
		$parameters = $this->getWantsListParameters($request);
		$parameters["html"] = $this->renderView("wants/worker/wants_list", $parameters);
		return $this->success($parameters);
	}
}