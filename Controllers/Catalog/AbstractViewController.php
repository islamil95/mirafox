<?php

namespace Controllers\Catalog;

use Attribute\AttributeManager;
use CategoryManager;
use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Model\Category;
use Model\User;
use Model\Attribute;
use CategoryManager as CM;
use Attribute\AttributeManager as AM;
use UserManager;

/**
 * Базовый контроллер для страниц каталога (работы портфолио и кворки)
 *
 * Class AbstractViewController
 * @package Controllers\Catalog
 */
abstract class AbstractViewController extends BaseController {
	use RoutingTrait;

	/**
	 * Тип отображения для таба "Кворки" страницы каталога
	 */
	const VIEW_KWORKS = "categories";

	/**
	 * Тип отображения для таба "Работы" страницы каталога
	 */
	const VIEW_PORTFOLIO = "gallery";

	/**
	 * Тип отображения по-умолчанию
	 */
	const DEFAULT_VIEW = self::VIEW_KWORKS;

	/**
	 * Взаимоодназначное соответствие между типом отображения и значением
	 * пользовательских настроек
	 */
	const USER_CATALOG_SETTINGS = [
		self::VIEW_KWORKS => User::CATALOG_VIEW_KWORKS,
		self::VIEW_PORTFOLIO => User::CATALOG_VIEW_PORTFOLIO,
	];

	/**
	 * Роут для таба "Работы" страницы каталога
	 */
	const ROUTE_PORTFOLIO = "catalog_portfolio";

	/**
	 * Роут для таба "Кворки" страницы каталога
	 */
	const ROUTE_KWORKS = "catalog_kworks";

	/**
	 * Роут по-умолчанию
	 */
	const DEFAULT_ROUTE = self::ROUTE_KWORKS;

	/**
	 * Роут для страницы "не найдено", задается в routes.yml
	 */
	const ROUTE_NOT_FOUND = "notfound";

	/**
	 * Алиас для отображения списка всех категорий
	 */
	const ALIAS_ALL = "all";

	/**
	 * Шаблон для страницы, должен быть переопределен в наследуемых контроллерах
	 */
	const TEMPLATE = "";

	/**
	 * @var string Роут контроллера, определяется константами self::ROUTE_*
	 */
	protected static $route;

	/**
	 * @var string Тип отображения контроллера, определяется константами self::VIEW_*
	 */
	protected static $view;

	/**
	 * @var Request Запрос
	 */
	protected $request;

	/**
	 * @var string|null Алиас выбранной категории
	 */
	protected $alias = null;

	/**
	 * @var string|null Алиас выбранного атрибута
	 */
	protected $attributeAlias = null;

	/**
	 * @var User|null Модель пользователя или null для неавторизованного
	 */
	protected $actor = null;

	/**
	 * @var Category|null Модель выбранной категории или null, если категория не выбрана
	 */
	protected $category = null;

	/**
	 * @var \Paging|null – Пагинатор
	 */
	protected $paging = null;

	/**
	 * @var Attribute|null Модель выбранного атрибута или null, если атрибут не выбран
	 */
	protected $volumeAttribute = null;

	/**
	 * @var bool Есть ли возможность просмотра работ портфолио
	 */
	protected $hasPortfolioCategoryView = false;

	/**
	 * @var string|false Роут для редиректа, задается константами self::ROUTE_* или false,
	 * если редирект не требуется
	 */
	protected $redirectRoute = false;

	/**
	 * @var RedirectResponse|false Редирект ответ или false, если не требуется
	 */
	private $redirectResponse = false;

	/**
	 * @var array Фильтры каталога
	 */
	protected $filters;

	/**
	 * @var array Параметры контроллера, используется как временное хранилище
	 * и для передачи данных шаблонизации
	 */
	protected $params = [];

	/**
	 * @var array Данные результата поиска
	 */
	protected $result = [];

    abstract protected function processFirstLevel();
	abstract protected function getAjaxPageResponse();

	/**
	 * Получить список роутов
	 *
	 * @return array Ключи – типы отображения, значения – роуты
	 */
	private function routes() {
		return [
			self::VIEW_KWORKS => self::ROUTE_KWORKS,
			self::VIEW_PORTFOLIO => self::ROUTE_PORTFOLIO,
		];
	}

	/**
	 * Получить титл страницы каталога
	 *
	 * @return string
	 */
	protected function getPageTitle(): string {
		$name = $this->getParam(Category::FIELD_NAME);
		return $name . " " . \Translations::t("Задания, которые люди выполнят за деньги");
	}

	/**
	 * Получить модель категории по алиасу
	 *
	 * @return Category|null Модель найденой категории или null,
	 * если категории с таким алиасом нет
	 */
	protected function getCategory() {
		$category = Category::where(Category::FIELD_SEO, $this->alias)
			->where(Category::FIELD_LANG, \Translations::getLang())
			->first();

		return $category;
	}

	/**
	 * Получить роут для редиректа, связанного с табами.
	 * Вся логика редиректов в табах "Кворки", "Работы" каталога.
	 *
	 * @return string|false Определяется константами self::ROUTE_*,
	 * false – редирект не требуется, уже запрошен соответствующий контроллер
	 */
	protected function getRedirectRoute() {
		$routes = self::routes();

		$viewSave = request("viewSave");

		/**
		 * TODO убрать после тестирования
		 * #6172 – функционал только для админов, для остальных – каталог
		 * отображается без вкладок портфолио/кворки
		 */
		if (!\UserManager::isAdmin()) {
			return $this->getBasisRedirectRoute();
		}

		/**
		 * Для ajax запросов редиректы не производятся
		 */
		if (\Helper::isAjaxRequest()) {
			return false;
		}

		/**
		 * Если пользователь не авторизован,
		 * просмотр списка всех категорий или просмотр работ из портфолио запрещен,
		 * то перенаправление на базовый роут
		 */
		if (
			is_null($this->actor) ||
			self::ALIAS_ALL == $this->alias ||
			false == $this->hasPortfolioCategoryView
		) {
			return $this->getBasisRedirectRoute();
		}

		/**
		 * Если запрошена смена отображения (таба), то обновляем настройки просмотра каталога
		 * для пользователя и перенаправляем на роут запрошенного таба
		 */
		if (in_array($viewSave, [self::VIEW_KWORKS, self::VIEW_PORTFOLIO])) {
			$setting = $this->getSetting($viewSave);
			$save = \UserManager::saveCatalogViewType($this->actor->USERID, $setting);
			if (false !== $save) {
				return $routes[$viewSave];
			}
		}

		/**
		 * По-умолчанию перенаправление на роут,
		 * соответствующий последнему посещению каталога пользователем.
		 * Хранится в пользовательских настройках
		 */
		if ($this->getSetting(static::$view) != $this->actor->catalog_view_type) {
			$view = $this->getView($this->actor->catalog_view_type);
			return $routes[$view];
		}

		/**
		 * Иначе – редирект не требуется
		 */
		return false;
	}

	/**
	 * Получить базовый роут для редиректа
	 *
	 * @return string|false Определяется константой self::DEFAULT_ROUTE,
	 * false – редирект не требуется, уже запрошен соответствующий контроллер
	 */
	private function getBasisRedirectRoute() {
		if (self::DEFAULT_ROUTE != static::$route) {
			return self::DEFAULT_ROUTE;
		} else {
			return false;
		}
	}

	/**
	 * Получить значение пользовательских настроек, соответствующие выбранному типу отображения
	 *
	 * @param string $view Тип отображения, определяется константами self::VIEW_*
	 * @return int Значение пользовательских настроек, определяется константами User::CATALOG_VIEW_*
	 */
	private function getSetting (string $view) {
		$mapping = self::USER_CATALOG_SETTINGS;
		return $mapping[$view];
	}

	/**
	 * Получить тип отображения, соответствующее значению пользовательских настроек
	 *
	 * @param int Значение пользовательских настроек, определяется константами User::CATALOG_VIEW_*
	 * @return string $view Тип отображения, определяется константами self::VIEW_*
	 */
	private function getView (int $setting) {
		$mapping = array_flip(self::USER_CATALOG_SETTINGS);
		return $mapping[$setting];
	}

	/**
	 * Получить редирект ответ для смены табов
	 *
	 * @return RedirectResponse Редирект ответ
	 */
	protected function getCatViewRedirectResponse() {
		/**
		 * Удаляем параметр строки запроса на смену таба
		 */
		$this->request->query->remove("viewSave");
		$params = $this->request->query->all();
		/**
		 * Добавляем алиас выбранной категории
		 */
		$params["alias"] = $this->alias;
		$url = $this->getUrlByRoute($this->redirectRoute, $params);

		return new RedirectResponse($url);
	}

	/**
	 * Задать параметр титла страницы
	 */
	protected function setParamsPageTitle() {
		$this->addToParams(["title" => $this->getPageTitle()]);
	}

	/**
	 * Получить базовые параметры категории
	 * @return array
	 */
	protected function getParamsBaseCategory() {
		/**
		 * Параметры категории
		 */
		$params = [
			Category::FIELD_ID,
			Category::FIELD_PARENT,
			Category::FIELD_NAME,
			Category::FIELD_SEO,
			Category::FIELD_META_TITLE,
			Category::FIELD_META_DESCRIPTION,
			Category::FIELD_META_KEYWORDS,
			Category::FIELD_ALLOW_MIRROR,
			Category::FIELD_TWIN_CATEGORY_ID,
		];

		$category = $this->category->toArray();
		$params = array_intersect_key($category, array_flip($params));

		$params["cid"] = $this->alias;
		$params["categoryId"] = $category[Category::FIELD_ID];
		$params["catselect"] = $params[Category::FIELD_ID];
		$params["disallow"] = !$params[Category::FIELD_ALLOW_MIRROR];
		$params["metakeywords"] = $params[Category::FIELD_META_DESCRIPTION];
		$params["alternateUrls"] = \AlternateUrlManager::getByCategoryId($this->category->CATID, '/categories/');

		$params[Category::FIELD_META_TITLE] = $params[Category::FIELD_NAME];
		if ($this->isCategoryFirstLevel()) {
			$params[Category::FIELD_META_TITLE] = $params[Category::FIELD_META_TITLE] . " - Kwork";
		} else {
			$params[Category::FIELD_META_TITLE] = "Фриланс: Заказать "
				. mb_strtolower($params[Category::FIELD_META_TITLE])
				." от 500 руб.";
		}
		$metaDescription = "Заказать " . mb_strtolower($params[Category::FIELD_NAME]) . " от 500 руб. в магазине";
		$metaDescription .= " фриланс-услуг Кворк. Тысячи услуг фрилансеров. Высокая скорость и четкое соблюдение";
		$metaDescription .= " сроков. Контроль качества, поддержка и внутренний арбитраж сделок. 100% гарантия";
		$metaDescription .= " возврата средств в случае срыва или просрочки заказа. Система рейтингов фрилансеров.";
		$metaDescription .= " Отсев худших предложений. В каталоге остаются предложения только лучших исполнителей.";
		$metaDescription = cut_desc_for_meta(strip_tags(htmlspecialchars_decode($metaDescription)));
		$params[Category::FIELD_META_DESCRIPTION] = trim(htmlspecialchars($metaDescription));

		return $params;
	}

	/**
	 * Задать параметры категории в случае, когда категория не выбрана
	 */
	protected function setParamsCategoryAll() {
		/**
		 * Параметры для списка всех категорий
		 */
		$params = [
			Category::FIELD_ID => self::ALIAS_ALL,
			Category::FIELD_PARENT => 0,
			Category::FIELD_NAME => \Translations::t('Все'),
			Category::FIELD_SEO => self::ALIAS_ALL,
			Category::FIELD_META_TITLE => \Translations::t('Все'),
			Category::FIELD_META_DESCRIPTION => \Translations::t('Все'),
			Category::FIELD_META_KEYWORDS => '',
			"categoryId" => self::ALIAS_ALL,
			"alternateUrls" => \AlternateUrlManager::getByRequestUri(),
		];

		$this->addToParams($params);
	}

	/**
	 * Задать параметры категории 1 уровня
	 */
	protected function setParamsCategoryFirstLevel() {
		$params = $this->getParamsBaseCategory();
		$parentCategory = Category::find($params[Category::FIELD_PARENT]);

		$this->category = Category::where(\CategoryManager::FIELD_SEO, $this->alias)
			->where(\CategoryManager::FIELD_LANG, \Translations::getLang())
			->first();

		$actor = $this->getUser();
		if ($this->category instanceof \Model\Category && $this->category->parent && $actor) {
			// @TODO при переходе на роутинг для категорий нужно будет внести измения в метод UserManager::getLastViewedCategoryLink()
			if ($actor->{User::FIELD_LAST_VIEWED_CATEGORY_ID} != $this->category->CATID) {
				User::whereKey($actor->id)
					->update([User::FIELD_LAST_VIEWED_CATEGORY_ID => $this->category->CATID]);
				$actor->{User::FIELD_LAST_VIEWED_CATEGORY_ID} = $this->category->CATID;
			}
		}


		$params["is_root"] = 0;
		$params["parentname"] = $parentCategory->{Category::FIELD_NAME};
		$params["parentseo"] = $parentCategory->{Category::FIELD_SEO};

		$this->addToParams($params);
	}

	/**
	 * Задать параметры подкатегорий для мобильной версии
	 */
	protected function setParamsMobileSubCategory() {
		$id = $this->getParam(Category::FIELD_ID);

		$query = 'SELECT * FROM categories WHERE parent = :parentCategoryId';
		$params = ['parentCategoryId' => $id];

		if(\App::isMirror()) {
			$query .= ' AND allow_mirror = 1';
		}

		if(\UserManager::isHideSocialSeo()) {
			$query .= ' AND catid != :SMMId';
			$params['SMMId'] = \UserManager::CATEGORY_SMM_ID;
		}

		$categories = \App::pdo()->fetchAll($query, $params);

		$params = [
			"scats" => $categories,
			"scatsCnt" => count($categories),
		];
		$this->addToParams($params);
	}

	/**
	 * Задаить параметры отображения
	 */
	protected function setParamsView() {
		$this->addToParams([
			"viewPortfolio" => self::VIEW_PORTFOLIO,
			"viewKworks" => self::VIEW_KWORKS,
			"catViewType" => static::$view,
			"hasPortfolioCategoryView" => $this->hasPortfolioCategoryView,
		]);
	}

	/**
	 * Добавить данные в хранилище контроллера
	 *
	 * @param array $data Данные
	 */
	protected function addToParams(array $data) {
		$this->params = array_merge($this->params, $data);
	}

	/**
	 * Получить данные из хранилища по ключу
	 *
	 * @param string $name Ключ
	 * @return mixed|null Данные по ключу или null, если ключа нет в хранилище
	 */
	protected function getParam(string $name) {
		if (!array_key_exists($name, $this->params)) {
			return null;
		}
		return $this->params[$name];
	}

	/**
	 * Добавить данные в хранилище контроллера по ключу
	 *
	 * @param string $name Ключ
	 * @param mixed $value Значение
	 */
	protected function setParam(string $name, $value) {
		$this->addToParams([$name => $value]);
	}

	/**
	 * Является ли запрошанная категория категорией 1 уровня
	 *
	 * @return bool
	 */
	protected function isCategoryFirstLevel() {
		if ($this->category instanceof Category && 0 == $this->category->parent) {
			return true;
		}

		return false;
	}

	/**
	 * Реднер страницы
	 *
	 * @return Response Ответ
	 */
	protected function renderPage() {
		if (empty($this->params)) {
			$this->setParamsPageTitle();
		}
		$this->setParamsView();

		return $this->render(static::TEMPLATE, $this->params);
	}

	/**
	 * Инициализировать контроллер.
	 * Устанавливаются базовые параметры.
	 * Определяется роут для редиректа, если необходимо
	 *
	 * @param Request $request Запрос
	 * @param string $alias Алиас выбранной категории
	 * @param string $attributeAlias Алиас выбранного атрибута
	 * @return bool true, если редирект не нужен, false – иначе
	 */
	protected function init(Request $request, string $alias, string $attributeAlias) {
		$this->request = $request;
		$this->alias = $alias;
		$this->attributeAlias = $attributeAlias;
		$this->category = $this->getCategory();
		$this->actor = \UserManager::getCurrentUser();
		$this->paging = new \Paging();

		return true;
	}

	/**
	 * Точка входа. Произвести рендер страницы каталога
	 *
	 * @param Request $request Запрос
	 * @param string $alias Алиас категории, параметр роута
	 * @param string $attributeAlias Алиас атрибута
	 * @return Response Ответ
	 */
	public function __invoke(Request $request, string $alias = "", string $attributeAlias = "") {
		/**
		 * Инициализация контроллера
		 */

		$this->init($request, $alias, $attributeAlias);

		/**
		 * В случае, когда в процессе инициализации потребовался редирект
		 */
		if ($this->redirectResponse instanceof RedirectResponse) {
			return $this->redirectResponse;
		}

		/**
		 * Задание параметров для поиска, получение результатов поиска и
		 * подготовка данных шаблонизации
		 */
		$this->processFirstLevel();

		/**
		 * Если запрос по ajax: Paging, фильтры, то возвращаем json ответ
		 */
		if (\Helper::isAjaxRequest()) {
			$data = $this->getAjaxPageResponse();
			return new JsonResponse($data);
		}

		/**
		 * Рендерим страницу
		 */
		return $this->renderPage();
	}
}