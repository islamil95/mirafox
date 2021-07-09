<?php

namespace Controllers\Catalog;

use Model\Category;
use CategoryManager;
use \App;

/**
 * Контроллер для отображения таба "Кворки" на странице каталога
 *
 * Class KworksViewController
 * @package Controllers\KworkList
 */
class KworksViewController extends AbstractViewController {

	/**
	 * Шаблон страницы каталога
	 */
	const TEMPLATE = "catalog/kworks_view";

	/**
	 * @var string Роут контроллера
	 */
	protected static $route = self::ROUTE_KWORKS;

	/**
	 * @var string Тип отображения контроллера
	 */
	protected static $view = self::VIEW_KWORKS;

	/**
	 * Получить результат поиска для категории 1 уровня
	 */
	protected function processFirstLevel() {

		/**
		 * Задаем параметры поиска, фильтры и переменные шаблонизации
		 *
		 * Внимание! В методах не устранены зависимости. Нарушения порядка вызова
		 * может привести к непредсказуемым последствиям
		 */
		if (self::ALIAS_ALL === $this->alias) {
			$this->setParamsCategoryAll();
		} else {
			$this->setParamsCategoryFirstLevel();
		}
		$this->setParamsMobileSubCategory();
		$this->setParamsBase();

		$this->setParamsPaging();

		$this->result = $this->getResult();

		$this->setParamsFilterQuery();
		$this->setParamsResult();
		$this->setParamsPopularCatsPosts();
	}

	/**
	 * Получить данные результата поиска
	 */
	private function getResult() {
		$sort = $this->getParam("s");
		$pageData = $this->getParam("pageData");
		$data = CategoryManager::getByCategory($this->filters, $sort, $pageData);
		return $data;
	}

	/**
	 * Задать базовые параметры контроллера.
	 * Значения фильтров, пользователь, титл страницы
	 */
	private function setParamsBase() {
		$params = [
			"sdeliverytime" => intval(request("sdeliverytime")),
			"stoprated" => intval(request("stoprated")),
			"sMinReview" => intval(request("sminreview")),
			"sOrdersQueue" => intval(request("sordersqueue")),
			"sMinUserSales" => intval(request("sminusersales")),
			"sMinKworkSales" => intval(request("sminkworksales")),
			"sview" => intval(request("sview")),
			"strack" => intval(request("strack")),
			"scoefficient" => intval(request("scoefficient")),
			"sellerLvl" => intval(request('sellerlvl')),
			"swithreviews" => intval(request("swithreviews")),
			"isHidden" => intval(request("isHidden")),
			"sonline" => request("sonline"),
			"translationsTo" => request("translationsto"),
			"translationsFrom" => request("translationsfrom"),
			"price" => request('price'),
			"filterPrice" => request('price'),
			"kworksCount" => intval(request("kworksCount")),
			"excludeIds" => \Helper::intArrayNoEmpty(explode(",", request("excludeIds"))),
			"pagetitle" => $this->getPageTitle(),
			"actor" => $this->actor
		];
		if (0 == $params["scoefficient"]) {
			$params["scoefficient"] = 1;
		}

		$this->addToParams($params);
	}

	/**
	 * Задать параметры строки запроса
	 */
	private function setParamsFilterQuery() {
		$params = $this->params;
		$queryParams = [
			"sdisplay" => $params["sdisplay"],
			"sdeliverytime" => $params["sdeliverytime"],
			"stoprated" => $params["stoprated"],
			"sonline" => $params["sonline"],
			"sminreview" => $params["sMinReview"],
			"sordersqueue" => $params["sOrdersQueue"],
			"sminusersales" => $params["sMinUserSales"],
			"sminkworksales" => $params["sMinKworkSales"],
			"sview" => $params["sview"],
			"strack" => $params["strack"],
			"s" => $params["s"],
			"page" => $params["page"],
			"translationsto" => $params["translationsTo"],
			"translationsfrom" => $params["translationsFrom"],
			"volume_price_from" => $params["volumePriceFrom"],
			"volume_price_to" => $params["volumePriceTo"],
			"package_items_conditions" => $params["packageItemsConditions"],
			"sellerlvl" => $params["sellerLvl"],
			"price" => $params["price"],
			"volumeFrom" => $params["volumeFrom"],
			"volumeTo" => $params["volumeTo"],
		];

		$data = [
			"filterQuery" => http_build_query(array_filter($queryParams)),
			"queryParamsJson" => json_encode(array_filter($queryParams)),
		];

		$this->addToParams($data);
	}

	/**
	 * Задать параметры пагинации
	 */
	private function setParamsPaging() {
		$id = $this->getParam(Category::FIELD_ID);
		$this->setParam("curpage", $this->paging->cur_page);
		$sort = request("s");
		$key = $this->actor ? "kwork.category_auth_per_page" : "kwork.category_unauth_per_page";
		$this->paging->items_per_page = App::config($key);

		/**
		 * Если Ajax запрос пагинации
		 */
		if (\Paging::isPost()) {
			$itemsPerPage = $this->paging->items_per_page;
			/**
			 * Если это ajax запрос при скрытии кворка,
			 * то получаем всего 1 элемент для замены вместо скрытого
			 */
			if ($this->getParam("isHidden")) {
				$itemsPerPage = 1;
				$this->filters["isHidden"] = true;
			} elseif ($this->filters["kworksCount"]) {
				$itemsPerPage = $this->filters["kworksCount"];
				if ($itemsPerPage < 1) {
					$itemsPerPage = 1;
				} elseif ($itemsPerPage > App::config('kwork.per_page')) {
					$itemsPerPage = App::config('kwork.per_page');
				}
			}
			$pagingStart = $this->paging->getPagingStart();
			$this->paging->setMaxAllowPage("cat", $id);
		} else {
			$itemsPerPage = $this->paging->cur_page * $this->paging->items_per_page;
			$pagingStart = 0;
		}

		$pageData = [
			'page' => $this->paging->cur_page,
			'pagingstart' => $pagingStart,
			'items_per_page' => $itemsPerPage,
		];

		$params = [
			"items_per_page" => $this->paging->items_per_page,
			"page" => $this->paging->cur_page,
			"currentpage" => $this->paging->cur_page,
			"pageData" => $pageData,
		];

		$this->addToParams($params);
	}

	/**
	 * Задать параметры результата поиска
	 */
	private function setParamsResult() {
		$id = $this->getParam(Category::FIELD_ID);

		$this->setParam("sub_cats", $this->result["sub_cats"]);
		$this->setParam("imgAltTitle", $this->getParam(Category::FIELD_NAME));
		
		if ($this->result['isLinksCategory']) {
			$this->setParam("isLinksCategory", $this->result['isLinksCategory']);
			$this->setParam("linksLanguages", $this->result['linksLanguages']);
			$this->setParam("isKworkLinksSitesAttribute", $this->result['isKworkLinksSitesAttribute']);
			$this->setParam("linksFiltred", $this->result['linksFiltred']);
		}
		
		if ($this->result["total"] > 0) {
			$hasTopRated = $this->result["executeCountTopRated"] ? true : false;
			$this->setParam("hasTopRated", $hasTopRated);
		}

		$this->setParam("activeCat", 0);
		if ($this->result && $this->result["sub_cats"]) {
			foreach ($this->result["sub_cats"] as $cat) {
				if ($cat["CATID"] == $id) {
					$this->setParam("activeCat", $cat);
				}
			}
		}

		$this->setParam("total", $this->result["total"]);
		$this->setParam("posts", $this->result["posts"]);
	}

	/**
	 * Задать параметры отображения популярных кворков на странице катагории 1 уровня
	 */
	private function setParamsPopularCatsPosts() {
		$subCats = $this->getParam("sub_cats");
		$popularCatsPosts = [];
		if (!empty($subCats)) {
			foreach ($subCats as $key => $cat) {
				if ($key < CategoryManager::POPULAR_SUB_CATS_QTY) {
					$popularCatsPosts[] = CategoryManager::getPostsByCategory(
						$cat[Category::FIELD_ID],
						[
							'sort' => 'popular',
							'limit' => CategoryManager::ELEMENTS_QTY_IN_SLIDER,
						]
					);
				}
			}
		}
		$this->setParam("popularCatsPosts", $popularCatsPosts);
	}
	/**
	 * Получить данные для ajax ответа
	 *
	 * @return array
	 */
	protected function getAjaxPageResponse() {
		return [
			"html" => $this->renderView("fox_bit_ajax.tpl", $this->params),
			"paging" => [
				"page" => $this->paging->cur_page,
				"items_per_page" => $this->paging->items_per_page,
				"total" => $this->result['total'],
			],
		];
	}
}