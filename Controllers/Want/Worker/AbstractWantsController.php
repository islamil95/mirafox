<?php


namespace Controllers\Want\Worker;

use Controllers\BaseController;
use Core\DB\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Model\User;
use Model\Want;
use Model\WantView;
use Symfony\Component\HttpFoundation\Request;

/**
 * Абстрактный класс для получения запросов на услуги от покупателей для продавцов
 *
 * Class AbstractWantsController
 * @package Controllers\Want
 */
abstract class AbstractWantsController extends BaseController {

	/**
	 * Определяет текущие активные фильтры пользователя
	 * @var array
	 */
	private
		$filtersByKworks,
		$filtersByBudget;

	/**
	 * @var array Массив любимых рубрик пользователя
	 */
	private $favourites;

	/**
	 * Получить выбранную категорию
	 *
	 * @param Request $request запрос
	 * @return mixed
	 */
	abstract protected function getSelectedCategoryId(Request $request);

	/**
	 * Показывать запросы из категорий, где есть кворки
	 *
	 * @param Request $request запрос
	 * @return mixed
	 */
	abstract protected function getShowWantsForMyKworks(Request $request);

	/**
	 * Получение идентификатора выбранной любимой рубрики
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return mixed
	 */
	abstract protected function getFavouriteSelectedCategoryId(Request $request);

	/**
	 * Получить массив с фильтром
	 * @param Request $request запрос
	 * @return array
	 */
	abstract protected function getFilter(Request $request);

	/**
	 * Получение любимых рубрик пользователя c кешированием
	 *
	 * @return \Illuminate\Support\Collection
	 */
	private function getFavourites() {
		if (is_null($this->favourites)) {
			$this->favourites = DB::table(\CategoryManager::TABLE_NAME)
				->where(\CategoryManager::FIELD_LANG, \Translations::getLang())
				->select(\CategoryManager::F_CATEGORY_ID, \CategoryManager::F_NAME)
				->get()
				->keyBy(\CategoryManager::F_CATEGORY_ID);
		}

		return $this->favourites;
	}

	/**
	 * Получить доступные категории для отображения запросов
	 *
	 * @param bool $showWantsForMyKworks показывать только запросы в категориях, где есть кворки у продавца
	 * @param int $selectedCategoryId выбранная категория
	 * @param int $selectedParentCategoryId родительская категория, выбранной категории
	 * @return \Illuminate\Support\Collection набор категорий
	 */
	private function getAvailableCategories($showWantsForMyKworks, $selectedCategoryId, $selectedParentCategoryId) {
		if ($showWantsForMyKworks) {
			$categories = $this->getFavourites();
		} else {
			$queryBuilder = DB::table(\CategoryManager::TABLE_NAME);
			$queryBuilder->where(\CategoryManager::F_PARENT, "<>", 0)
				->where(\CategoryManager::F_CUSTOM_OFFER, 0)
				->where(\CategoryManager::FIELD_LANG, \Translations::getLang())
				->select(\CategoryManager::F_CATEGORY_ID, \CategoryManager::F_NAME);
			if ($selectedCategoryId) {
				$queryBuilder->where(function($query) use ($selectedCategoryId, $selectedParentCategoryId) {
					$query->where(\CategoryManager::F_CATEGORY_ID, $selectedCategoryId);
					if ($selectedParentCategoryId == $selectedCategoryId) {
						$query->orWhere(\CategoryManager::F_PARENT, $selectedParentCategoryId);
					}
				});
			}
			$categories = $queryBuilder->get()->keyBy(\CategoryManager::F_CATEGORY_ID);
		}
		return $categories;
	}

	/**
	 * Получить список категорий на основе выбранной, если выбрана родительская категория,
	 * то вернуться все родительские категории
	 *
	 * @param int $selectedCategoryId выбранная категория
	 * @return array
	 */
	private function getFilteredCategoryIds($selectedCategoryId): array {
		$filteredCategories = [];
		$categories = \CategoryManager::getList(\Translations::getLang(), 2);
		foreach ($categories as $category) {
			foreach ($category->cats as $subCategory) {
				if ($subCategory->id == $selectedCategoryId || $category->id == $selectedCategoryId) {
					$filteredCategories[] = $subCategory->id;
				}
			}
		}
		return empty($filteredCategories) ? [0] : $filteredCategories;
	}

	/**
	 * Получить идентификатор родительской категории по дочерней
	 *
	 * @param int $selectedCategoryId идентификатор выбранной категории
	 * @return int идентификатор родительской категории
	 */
	private function getParentCategoryId($selectedCategoryId) {
		$categories = \CategoryManager::getList(\Translations::getLang(), 2);
		$parentCategoryId = null;
		foreach ($categories as $category) {
			foreach ($category->cats as $subCategory) {
				if ($subCategory->id == $selectedCategoryId || $category->id == $selectedCategoryId) {
					$parentCategoryId = $category->id;
					break;
				}
			}
		}
		return $parentCategoryId;
	}

	/**
	 * Получить запросы на услуги
	 *
	 * @param array $categoryIds идентификаторы категорий, по которым ищем предложения
	 * @param Builder $queryBuilder объект постоителя запросов
	 * @param array $filter Фильтр
	 * @return LengthAwarePaginator
	 */
	public function getWants(array $categoryIds, Builder $queryBuilder, array $filter = []) {
		$queryBuilder->with(["user"]);

		return $queryBuilder->orderByDesc(Want::FIELD_DATE_CONFIRM)
			->orderByDesc(Want::FIELD_ID)
			->paginate((int)\App::config("per_page_items"));
	}

	/**
	 * Получение количеств для предложений (без условий по предложениям)
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param array $categoryIds
	 * @param array $filter
	 *
	 * @return array
	 */
	private function getWantsCountsForKworks(Builder $queryBuilder, array $categoryIds, array $filter) {
		$queryBuilder = clone $queryBuilder;
		$queryBuilder = $this->addPriceFiltersCondition($queryBuilder, $filter);
		$queryBuilder = $this->addCategoriesInQueryBuilder($categoryIds, $queryBuilder);

		$countSql = "";
		$bindings = [];
		$this->addKworksSelect($countSql, $bindings);

		return $this->cachedCountsSelectRawQuery($queryBuilder, $countSql, $bindings);
	}

	/**
	 * Получение количеств для условий по цене (без условий по цене)
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param array $categoryIds
	 * @param array $filter
	 *
	 * @return array
	 */
	private function getWantsCountsForPrice(Builder $queryBuilder, array $categoryIds, array $filter) {
		$queryBuilder = clone $queryBuilder;
		$queryBuilder = $this->addKworksFiltersCondition($queryBuilder, $filter);
		$queryBuilder = $this->addCategoriesInQueryBuilder($categoryIds, $queryBuilder);

		$countSql = "";
		$bindings = [];
		$this->addPriceSelect($countSql, $bindings);

		return $this->cachedCountsSelectRawQuery($queryBuilder, $countSql, $bindings);
	}

	/**
	 * Получение количеств любимых рубрик (без условий по выбранной рубрике)
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param array $filter
	 *
	 * @return array
	 */
	private function getWantsCountsForFavouriteCategories(Builder $queryBuilder, array $filter) {
		$queryBuilder = clone $queryBuilder;
		$favourites = $this->getFavourites();
		$queryBuilder = $this->addCategoriesInQueryBuilder($favourites->keys()->all(), $queryBuilder);
		$queryBuilder = $this->addKworksFiltersCondition($queryBuilder, $filter);
		$queryBuilder = $this->addPriceFiltersCondition($queryBuilder, $filter);

		$countSql = "";
		$bindings = [];
		$this->addCategoriesSelect($countSql, $bindings);

		return $this->cachedCountsSelectRawQuery($queryBuilder, $countSql, $bindings);
	}

	/**
	 * Есть ли отмеченные фильтры требующие отдельного пересчета количеств
	 *
	 * @param array $filter Фильтр
	 * @param int $selectedFavouriteCategoryId Выбранная рубрика из люимых
	 *
	 * @return bool
	 */
	private function isFilterHasCheckedConditions(array $filter, $selectedFavouriteCategoryId) {
		// Если показываем все мои рубрики или конкретную рубрику из любимых
		if ($selectedFavouriteCategoryId) {
			return true;
		}
		// Отмечен фильтр по количеству предложений
		if ($this->isFilterCheckedKworks($filter)) {
			return true;
		}
		// Отмечен фильтр по ценам (предустановленные значения)
		if ($this->isFilterCheckedPrice($filter)) {
			return true;
		}

		return false;
	}

	/**
	 * Есть ли в фильтре условия по предложениями отмеченные галочками
	 *
	 * @param array $filter
	 *
	 * @return bool
	 */
	private function isFilterCheckedKworks(array $filter) {
		return !empty($filter["kworks_filters"]) && is_array($filter["kworks_filters"]);
	}

	/**
	 * Есть ли в фильтре условия по цене отмеченные галочками
	 *
	 * @param array $filter
	 *
	 * @return bool
	 */
	private function isFilterCheckedPrice(array $filter) {
		return !$this->isArbitraryPriceFilter($filter) && !empty($filter["prices_filters"]) && is_array($filter["prices_filters"]);
	}

	/**
	 * Применить callback к массиву чтобы получить sql и значения
	 *
	 * @param string $countSql К этому параметру будет добавлен sql
	 * @param array $bindings К этому массиву будет добавлены значения
	 * @param array $walk Массив для обхода
	 * @param array $callbackFieldData Данные 3 аргумента callback
	 */
	private function addBoundariesConditionsSql(string &$countSql, array &$bindings, array $walk, array $callbackFieldData) {
		$selectSqlCallback = function($filterItem, $key, $fieldData) use (&$countSql, &$bindings) {
			if (isset($filterItem["boundaries"]) && !is_null($filterItem["boundaries"])) {
				$countSql .= "COALESCE(SUM(CASE WHEN ";
				if (isset($filterItem["boundaries"]["equal"])) {
					$countSql .= " {$fieldData["name"]} = ? ";
					$bindings[] = $filterItem["boundaries"]["equal"];
				} else {
					if ($filterItem["boundaries"]["from"]) {
						$countSql .= " {$fieldData["name"]} > ? AND ";
						$bindings[] = $filterItem["boundaries"]["from"];
					}
					if ($filterItem["boundaries"]["to"]) {
						$countSql .= " {$fieldData["name"]} <= ? ";
						$bindings[] = $filterItem["boundaries"]["to"];
					} else {
						$countSql .= " 1";
					}
				}
				$countSql .= " THEN 1 ELSE 0  END), 0) ";
				$countSql .= "{$fieldData["filter_prefix"]}{$filterItem["id"]}, ";
			}
		};

		array_walk($walk, $selectSqlCallback, $callbackFieldData);
	}

	/**
	 * Добавление условий по предложениям в select Sql
	 *
	 * @param string $countSql Переменная в которую будет добавлен $sql
	 * @param array $bindings Массив в который будет добавлены значения
	 */
	private function addKworksSelect(string &$countSql, array &$bindings) {
		$fieldData = [
			"filter_prefix" => "kworks_filter_id_",
			"name" => \WantManager::TABLE_NAME . "." . \WantManager::F_ID
		];
		$this->addBoundariesConditionsSql($countSql, $bindings, $this->filtersByKworks, $fieldData);
	}

	/**
	 * Добавление условий по ценам в select Sql
	 *
	 * @param string $countSql Переменная в которую будет добавлен $sql
	 * @param array $bindings Массив в который будет добавлены значения
	 */
	private function addPriceSelect(string &$countSql, array &$bindings) {
		//колиество по стоимости
		$fieldData = [
			"filter_prefix" => "prices_filter_id_",
			"name" => \WantManager::TABLE_NAME . "." . \WantManager::FIELD_PRICE_LIMIT
		];
		$this->addBoundariesConditionsSql($countSql, $bindings, $this->filtersByBudget, $fieldData);
	}

	/**
	 * Добавление условий по любимым рубрикам в select Sql
	 *
	 * @param string $countSql Переменная в которую будет добавлен $sql
	 * @param array $bindings Массив в который будет добавлены значения
	 */
	private function addCategoriesSelect(string &$countSql, array &$bindings) {
		$categoryIds = $this->getFavourites()->keys()->all();

		//колиество в категориях
		array_walk($categoryIds, function($categoryId, $key) use (&$categoryIds) {
			$categoryIds[$key] = [
				"id" => $categoryId,
				"boundaries" => [
					"equal" => $categoryId,
				]
			];
		});

		$fieldData = [
			"filter_prefix" => "category_id_",
			"name" => \WantManager::TABLE_NAME . "." . \WantManager::F_CATEGORY_ID
		];
		$this->addBoundariesConditionsSql($countSql, $bindings, $categoryIds, $fieldData);
	}

	/**
	 * Возвращает количество найденных запросов на услуги для каждого фильтра
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder Базовый билдер
	 * @param array $categoryIds
	 * @param array $filter
	 * @param int $selectedFavouriteCategoryId
	 *
	 * @return array
	 */
	private function getWantsCountInFilters(Builder $queryBuilder, array $categoryIds, array $filter, $selectedFavouriteCategoryId) {
		if (!$this->isFilterHasCheckedConditions($filter, $selectedFavouriteCategoryId)) {
			// Простой вариант если нет выбранных условий
			return $this->getWantsCountInFiltersWithoutChecked($categoryIds, $queryBuilder, $filter);
		}

		$counts = [];
		$counts += $this->getWantsCountsForKworks($queryBuilder, $categoryIds, $filter);
		$counts += $this->getWantsCountsForPrice($queryBuilder, $categoryIds, $filter);
		if ($this->getFavourites()->count()) {
			$counts += $this->getWantsCountsForFavouriteCategories($queryBuilder, $filter);
		}

		return $counts;
	}

	/**
	 * Возвращает количество найденных запросов на услуги для каждого фильтра
	 * Используется если нет отмеченных галочками условий для которых нужно подсчитать количества
	 *
	 * Метод учитывае текущие выставленные фильтры, переданные в $queryBuilder
	 * @param array $categoryIds массив идентификаторов категорий
	 * @param Builder $queryBuilder конструктор запроса
	 * @param array $filter
	 *
	 * @return array
	 */
	private function getWantsCountInFiltersWithoutChecked(array $categoryIds, Builder $queryBuilder, array $filter): array {
		$queryBuilder = clone $queryBuilder;
		$queryBuilder = $this->addCategoriesInQueryBuilder($categoryIds, $queryBuilder);
		$queryBuilder = $this->addPriceFiltersCondition($queryBuilder, $filter);

		$countSql = "";
		$bindings = [];
		$this->addKworksSelect($countSql, $bindings);
		$this->addPriceSelect($countSql, $bindings);
		$this->addCategoriesSelect($countSql, $bindings);

		return $this->cachedCountsSelectRawQuery($queryBuilder, $countSql, $bindings);
	}

	/**
	 * Кешированный запрос по количествам
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param string $countSql Строка для selectRaw
	 * @param array $bindings Значения для selectRaw
	 *
	 * @return array
	 */
	private function cachedCountsSelectRawQuery(Builder $queryBuilder, string $countSql, array $bindings) {
		$countSql = trim($countSql, ", ");
		$queryBuilder->selectRaw($countSql, $bindings);
		$data = $queryBuilder->get()->first()->toArray();
		return $data;
	}

	/**
	 * Добавляет в билдер условия выбора по категориям, согласно настройкам пользователя
	 *
	 * @param array $categoryIds категории, которые надо добавить в условия
	 * @param Builder $queryBuilder
	 * @return Builder
	 */
	private function addCategoriesInQueryBuilder(array $categoryIds, Builder $queryBuilder): Builder {
		$queryBuilder = clone $queryBuilder;
		$queryBuilder->whereIn(\WantManager::F_CATEGORY_ID, $categoryIds);
		return $queryBuilder;
	}

	/**
	 * Добавляет условия по фильтру по количеству предложений в задании
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param array $filter
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	private function addKworksFiltersCondition(Builder $queryBuilder, array $filter) {
		$queryBuilder = clone $queryBuilder;
		if ($this->isFilterCheckedKworks($filter)) {
			$queryBuilder = $queryBuilder->where(function(Builder $query) use ($filter) {
				foreach ($filter["kworks_filters"] as $filterId) {
					if (isset($this->filtersByKworks[$filterId])) {
						$boundaries = $this->filtersByKworks[$filterId]["boundaries"];
						$query->orWhere(function(Builder $query) use ($boundaries) {
							if ($boundaries["from"] !== null) {
								$query->where(\WantManager::TABLE_NAME . "." . \WantManager::FIELD_KWORK_COUNT, ">", $boundaries["from"]);
							}
							if ($boundaries["to"] !== null) {
								$query->where(\WantManager::TABLE_NAME . "." . \WantManager::FIELD_KWORK_COUNT, "<=", $boundaries["to"]);
							}
						});
					}
				}
			});
		}

		return $queryBuilder;
	}

	/**
	 * Добавление к построителю запросов условий по бюджету
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $queryBuilder
	 * @param array $filter
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	private function addPriceFiltersCondition(Builder $queryBuilder, array $filter) {
		$queryBuilder = clone $queryBuilder;
		//Фильтрация по бюджету
		if ($this->isArbitraryPriceFilter($filter)) {
			//установка переданных значений в фильтрах
			$queryBuilder->where(function($query) use ($filter) {
				$this->setPriceQueryByLang([
					"from" => (isset($filter["price_from"]) && is_numeric($filter["price_from"])) ? $filter["price_from"] : null,
					"to" => (isset($filter["price_to"]) && is_numeric($filter["price_to"])) ? $filter["price_to"] : null,
				], $query);
			});
		} elseif ($this->isFilterCheckedPrice($filter)) {
			$queryBuilder->where(function($query) use ($filter) {
				foreach ($filter["prices_filters"] as $filterId) {
					if (isset($this->filtersByBudget[$filterId]) && is_array($this->filtersByBudget[$filterId]["boundaries"])) {
						$boundaries = $this->filtersByBudget[$filterId]["boundaries"];
						$this->setPriceQueryByLang($boundaries, $query);
					}
				}
			});
		}
		return $queryBuilder;
	}

	/**
	 * Установлен ли фильтр по цене по произвольным значениям вводимым пользователем
	 *
	 * @param array $filter
	 *
	 * @return bool
	 */
	private function isArbitraryPriceFilter(array $filter):bool {
		return (isset($filter["price_from"]) && is_numeric($filter["price_from"])) ||
			(isset($filter["price_to"]) && is_numeric($filter["price_to"]));
	}

	/**
	 * Выставляет условия выборки по переданными фильтрам в построителе запросов
	 *
	 * @param array $filter условия фильтрации
	 * @return Builder
	 */
	private function getWantsQueryBuilder(array $filter = []): Builder {
		$queryBuilder = Want::query()->where(\WantManager::F_STATUS, \WantManager::STATUS_ACTIVE);

		return $queryBuilder;
	}

	/**
	 * Добавляет условия фильтра стоимости запроса на услуги, учитывая насстройки пользователя
	 *
	 * @param array $boundaries массив границ условия цен
	 * @param Builder $queryBuilder конструктор запросов
	 */
	private function setPriceQueryByLang(array $boundaries, Builder &$queryBuilder) {
		$queryBuilder->orWhere(function(Builder $query) use ($boundaries) {
			if ($boundaries["from"] !== null) {
				$query->where(\WantManager::TABLE_NAME . "." . \WantManager::FIELD_PRICE_LIMIT, ">", $boundaries["from"]);
			}
			if ($boundaries["to"] !== null) {
				$query->where(\WantManager::TABLE_NAME . "." . \WantManager::FIELD_PRICE_LIMIT, "<=", $boundaries["to"]);
			}
			$query->where(\WantManager::F_LANG, \Translations::getLang());
		});
	}

	/**
	 * Получить параметры для урл, используется для построения ссылок на странице
	 *
	 * @param Request $request HTTP запрос
	 * @param bool $showWantsForMyKworks показывать ли только запросы на категории где мои кворки.
	 * @return array набор параметров для URL
	 */
	private function getUrlParameters(Request $request, bool $showWantsForMyKworks): array {
		/**
		 * Это написано для совместимости. Используется в пагинации и генерации ссылок.
		 * Значения задаются из методов определенных в потомках. Значение очищается, если оно пустое.
		 */
		$urlParameters = [
			"c" => $this->getSelectedCategoryId($request),
			"fc" => $this->getFavouriteSelectedCategoryId($request),
			"a" => $this->getShowWantsForMyKworks($request),
			"price-from" => $this->getFilter($request)["price_from"],
			"price-to" => $this->getFilter($request)["price_to"],
			"hiring-from" => $this->getFilter($request)["hiring_from"],
			"page" => $request->get("page"),
			"s" => $request->get("s"),
			"kworks-filters" => $this->getFilter($request)["kworks_filters"],
			"prices-filters" => $this->getFilter($request)["prices_filters"],
		];

		foreach ($urlParameters as $name => $value) {
			if (empty($urlParameters[$name])) {
				unset($urlParameters[$name]);
			}
		}

		if ($showWantsForMyKworks) {
			$urlParameters["a"] = 1;
		}

		return $urlParameters;
	}

	/**
	 * Добавить к URL в пагинации
	 *
	 * @param Request $request HTTP запрос
	 * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $wants запросы на услуги
	 * @param bool $showWantsForMyKworks показывать ли только запросы на категории где мои кворки.
	 * @return $this
	 */
	private function appendsPaginatorParameters(Request $request, $wants, $showWantsForMyKworks) {
		$appendsParameters = $this->getUrlParameters($request, (bool)$showWantsForMyKworks);
		if (isset($appendsParameters["page"])) {
			unset($appendsParameters["page"]);
		}
		$wants->appends($appendsParameters);
		return $this;
	}

	/**
	 * Нужно
	 *
	 * @param Request $request HTTP запрос
	 * @return bool результат
	 */
	private function checkForUserHaveFavourites(Request $request): bool {
		return !$this->getSelectedCategoryId($request) &&
			!$this->getShowWantsForMyKworks($request) &&
			!$this->getFavouriteSelectedCategoryId($request) &&
			!$request->isXmlHttpRequest() &&
			!$request->query->has("page");
	}

	/**
	 * Устанавливает текущие фильтры, взависимости от настроек пользователя
	 */
	private function setCurrentFilters() {
		if (!$this->getUserDisableEn() && \Translations::DEFAULT_LANG != ($this->getUserLang() ?: \Translations::getLang())) {
			$this->filtersByKworks = \WantManager::FILTERS_BY_KWORKS_EN;
			$this->filtersByBudget = \WantManager::FILTERS_BY_BUDGET_EN;
		} else {
			$this->filtersByKworks = \WantManager::FILTERS_BY_KWORKS_RU;
			$this->filtersByBudget = \WantManager::FILTERS_BY_BUDGET_RU;
		}
	}

	/**
	 * Получение всех необходимых параметров для отображения списка запросов
	 * (вся общая логика)
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return array
	 */
	protected function getWantsListParameters(Request $request): array {
		$selectedCategoryId = $this->getSelectedCategoryId($request);
		$selectedFavouriteCategoryId = $this->getFavouriteSelectedCategoryId($request);
		$favourites = $this->getFavourites();
		$favouritesIds = $favourites->keys()->all();
		if (!in_array($selectedFavouriteCategoryId, $favouritesIds)) {
			$selectedFavouriteCategoryId = null;
		}
		if (empty($selectedCategoryId) && $selectedFavouriteCategoryId) {
			$selectedCategoryId = $selectedFavouriteCategoryId;
		}
		$selectedParentCategoryId = $this->getParentCategoryId($selectedCategoryId);
		$showWantsForMyKworks = $this->getShowWantsForMyKworks($request);
		$filter = $this->getFilter($request);
		$withoutParamsRequest = !$selectedCategoryId && !$selectedFavouriteCategoryId && !$showWantsForMyKworks && !$filter;
		$this->setCurrentFilters();

		$categories = $this->getAvailableCategories($showWantsForMyKworks, $selectedCategoryId, $selectedParentCategoryId);
		$categoryIds = array_keys($categories->all());

		if ($this->getUserType() == \UserManager::TYPE_PAYER) {
			\UserManager::changeUserType();
		}

		// Базовый построитель запросов не со всеми условиями
		$queryBuilder = $this->getWantsQueryBuilder();

		$wants = $this->getWants($categoryIds, clone $queryBuilder, $filter);

		// В случае если это заход на страницу без указанных пользователем параметров и в его любимых рубриках нет проектов
		if ($withoutParamsRequest && $showWantsForMyKworks && !count($wants)) {
			// Переключаем на "Все рубрики" и ищем заново
			$showWantsForMyKworks = false;
			$categories = $this->getAvailableCategories($showWantsForMyKworks, $selectedCategoryId, $selectedParentCategoryId);
			$categoryIds = array_keys($categories->all());
			$queryBuilder = $this->getWantsQueryBuilder();
			$wants = $this->getWants($categoryIds, clone $queryBuilder, $filter);
		}

		//счетчики
		if (count($wants) > 0) {
			//Получаем список юзеров с запросов
			$wantsUsers = $wants->pluck(User::FIELD_USERID)->toArray();

			$userToOrder = \OrderManager::userOrdersWithList($wantsUsers);
			if (count($userToOrder)) {
				// Вносим значения "работал ранее" в запросы
				foreach ($wants as $want) {
					if ($userToOrder[$want->user_id]) {
						$want->alreadyWork = $userToOrder[$want->user_id]["status"];
					}
				}
			}
		}

		$wantViews = $this->isUserNotAuthenticated() ? [] : WantView::getUserWantsViewsDates($wants->pluck(Want::FIELD_ID)->toArray(), $this->getUserId());

		// Добавляем параметры к URL
		$this->appendsPaginatorParameters($request, $wants, $showWantsForMyKworks);

		// Получение количеств по любимым рубрикам и фильтрам
		$counts = $this->getWantsCountInFilters($queryBuilder, $categoryIds, $filter, $selectedFavouriteCategoryId);

		return [
			"wants" => $wants,
			"wantViews" => $wantViews,
			"filterCategories" => $this->getFilteredCategoryIds($selectedCategoryId),
			"showWantsForMyKworks" => $showWantsForMyKworks,
			"notShowWantsForMyKworks" => !$showWantsForMyKworks,
			"selectedCategoryId" => $selectedCategoryId,
			"selectedFavouriteCategoryId" => $selectedFavouriteCategoryId,
			"selectedParentCategoryId" => $selectedParentCategoryId,
			"availableCategories" => $categories,
			"urlParameters" => $this->getUrlParameters($request, (bool)$showWantsForMyKworks),
			"filter" => $filter,
			"canAddOfferStatus" => \OfferManager::checkProfileInfoToAdd(),
			"filters" => [
				"by_kworks" => (!$this->getUserDisableEn() && \Translations::DEFAULT_LANG != ($this->getUserLang() ?: \Translations::getLang())) ? \WantManager::FILTERS_BY_KWORKS_EN : \WantManager::FILTERS_BY_KWORKS_RU,
				"by_budget" => (!$this->getUserDisableEn() && \Translations::DEFAULT_LANG != ($this->getUserLang() ?: \Translations::getLang()) )? \WantManager::FILTERS_BY_BUDGET_EN : \WantManager::FILTERS_BY_BUDGET_RU,
			],
			"counts" => $counts,
		];
	}
}