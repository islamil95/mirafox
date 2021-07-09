<?php

use Core\DB\DB;
use Attribute\AttributeManager as AM;
use Helpers\Kwork\GetKworks\GetKworksArrayAdapter;
use Helpers\Kwork\GetKworks\GetKworksByCategoryService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Kwork\KworkGroup\KworkRatingGroupPrecalcManager;
use Kwork\KworkGroup\KworkRatingGroupPrecalcManagerNew;
use Kwork\KworkGroup\KworkRatingGroupSortManager;
use Model\Category;
use Model\Kwork;
use Model\KworkPackage;

class CategoryManager {

	const TABLE_NAME = 'categories';

	/**
	 * Идентификатор русской категории "Верстка" - для показа уведомления в "Мои кворки"
	 */
	const CATEGORY_MARKUP_ID = 79;

	/**
	 * Идентификаторы категорий переводов - для отображения списка языков в них
	 */
	const CATEGORY_TRANSLATIONS_ID_RU = 35;
	const CATEGORY_TRANSLATIONS_ID_EN = 152;

	/**
	 * Идентификаторы категорий "Ссылки" - для работы дополнительных фильтров
	 */
	const CATEGORY_LINKS_ID_RU = 59;
	const CATEGORY_LINKS_ID_EN = 176;

	/*
	 * Идентификатор категории "SEO и траффик". Используется в KworkManager::getRsKwork()
	 */
	const CATEGORY_SEO_TRAFFIC = 17;

	/**
	 * Процент площадок, в которых есть заявленные характеристики при фильтрации
	 */
	const LINKS_FILTER_DONOR_PERCENT = 50;

	const F_NAME = "name";
	const F_CATEGORY_ID = "CATID";
	const F_PARENT = "parent";
	const F_CUSTOM_OFFER = "custom_offer";
	const F_ALLOW_MIRROR = "allow_mirror";
	const F_ORDER_MONTH = "order_month";
	const F_COLLAGE_IMAGE = "collage_image";

	/**
	 * Короткое название категории
	 */
	const FIELD_SHORT_NAME = "short_name";

	/**
	 * Средняя конверсия по категории
	 */
	const FIELD_CONVERSION = "conversion";

	/**
	 * Средний рейтинг качества на заказ по категории
	 */
	const FIELD_QUALITY_RATING_RELATIVE = "quality_rating_relative";

	/**
	 * Язык
	 */
	const FIELD_LANG = "lang";

	/**
	 * Месячный оборот категории (пересчитывается раз в неделю)
	 */
	const FIELD_MONTH_REVENUE = "month_revenue";

	/**
	 * Является ли категория Индивидуальным предложением
	 */
	const FIELD_CUSTOM_OFFER = "custom_offer";

	/**
	 * Алиас категории для отображения в адресной строке
	 */
	const FIELD_SEO = "seo";

	/**
	 * Идентификатор категории близнеца в другой языковой версии
	 */
	const FIELD_TWIN_CATEGORY_ID = "twin_category_id";

	/**
	 * Идентификатор категории-соответствия в другом языке
	 */
	const FIELD_MAPPED_CATEGORY_ID = "mapped_category_id";

	/**
	 * Количество использования категории
	 */
	const FIELD_USE_COUNT = "use_count";

	/**
	 * Максимальное количество элементов портфолио в кворке
	 */
	const FIELD_MAX_PHOTO_COUNT = "max_photo_count";

	/**
	 * Среднее количество предложений на запрос по категории
	 */
	const FIELD_AVG_OFFERS_COUNT = "avg_offers_count";

	/**
	 * Поле - тип портфолио
	 */
	const F_PORTFOLIO_TYPE = "portfolio_type";

	/**
	 * Короткое описание категории, для мобильного приложения
	 */
	const F_MOBILE_DESCRIPTION = "mobile_description";

	/**
	 * Поле - Портфолио доступно
	 */
	const FIELD_PORTFOLIO_AVALIABLE = "portfolio_avaliable";

	/**
	 * Название в именительном падеже, для лендинга
	 */
	const FIELD_SEO_I = "seo_i";

	/**
	 * Название в винительном падеже, для лендинга
	 */
	const FIELD_SEO_V = "seo_v";

	/**
	 * Описание для тега title
	 */
	const FIELD_MTITLE = "mtitle";

	/**
	 * Описание для тега meta description
	 */
	const FIELD_MDESC = "mdesc";

	/**
	 * Описание внизу страницы depricated
	 */
	const FIELD_MINFO = "minfo";

	/** @var int|null медианное время ответа на сообщения, требующие ответа в течении 24 часов, в секундах */
	const FIELD_RESPONSE_TIME = "response_time";

	/**
	 * Портфолио в заказе не разрешено
	 */
	const CAT_PORTFOLIO_NONE = 'none';

	/**
	 * Обязательное портфолило в заказе - изображение
	 */
	const CAT_PORTFOLIO_PHOTO = 'photo';

	/**
	 * Обязательное портфолио в заказе - видео
	 */
	const CAT_PORTFOLIO_VIDEO = 'video';

	const FREE_PRICE_ENABLE = 1;
	const FREE_PRICE_DISABLE = 0;
	const F_IS_PACKAGE_FREE_PRICE = 'is_package_free_price';

	/**
	 * Максимальный срок выполнения коворков со свободной ценой
	 */
	const FREE_PRICE_MAX_DAYS = 30;

	/**
	 * Количество популярных подкатегорий, которые должны быть отображены вместе с их популярными кворками
	 *  на странице с коллажом
	 *
	 */
	const POPULAR_SUB_CATS_QTY = 3;

	/**
	 * Количество кворков в слайдере на странице с коллажом
	 */
	const ELEMENTS_QTY_IN_SLIDER = 25;

	/**
	 * Количество кворков в слайдере на странице с коллажом для мобильных девайсов
	 */
	const ELEMENTS_QTY_FOR_MOBILE = 3;

	/**
	 * Количество элементов портфолио начиная с которого рубрика считается дизайнерской
	 */
	const DESIGN_CATEGORY_PORTFOLIO_QUANTITY = 7;

	const F_BASE_VOLUME = 'base_volume';
	const F_VOLUME_TYPE_ID = 'volume_type_id';

	/**
	 * Сортировка кворков по рейтингу (итоговый рейтинг с конверсией)
	 */
	const SORT_POPULAR = "popular";

	/**
	 * Сортировка кворков по новизне
	 */
	const SORT_NEW = "new";

	/**
	 * Сортировка кворков по рейтингу с учетом их оборота
	 */
	const SORT_NEWRATING = "newrating";

	/**
	 * Сортировка кворков по сервисному рейтингу
	 */
	const SORT_OLD = "old";

	/**
	 * Сортировка кворков по группам "Бывалые" "Проверенные" "Укрепляющиеся" "Новички"
	 */
	const SORT_GROUPS = "groups";

	/**
	 * Доступные типы сортировки кворков в виде по категориям
	 */
	const AVAILABLE_SORTS = [
		self::SORT_NEW,
		self::SORT_POPULAR,
		self::SORT_NEWRATING,
		self::SORT_OLD,
		self::SORT_GROUPS,
	];

	/**
	 * Сортировка по умолчанию
	 */
	const DEFAULT_SORT = self::SORT_GROUPS;

	/**
	 * Тип выдачи getList - заполненные, отсортированные по популярности, подкатегории по алфавиту
	 */
	const TYPE_FILLED_POPULAR = 1;

	/**
	 * Тип выдачи getList - заполненные, отсортированные по алфавиту, подкатегории по алфавиту
	 */
	const TYPE_FILLED_ALPHABET = 2;

	/**
	 * Тип выдачи getList - все категории, отсортированные по алфавиту, подкатегории по алфавиту
	 */
	const TYPE_ALL_ALPHABET = 3;

	/**
	 * Тип выдачи getList - заполненные, по популярности, максимум 5 родительских
	 */
	const TYPE_FIVE_FILLED_POPULAR = 4;

	/**
	 * Тип выдачи getList - все по алфавиту, вместе с индивидуальным предложением
	 */
	const TYPE_ALL_WITH_CUSTOM_OFFER = 5;

	/**
	 * Тип выдачи getList - все по обороту, отсортированы по обороту, подкатегории тоже отсортированы по обороту
	 */
	const TYPE_REVENUE = 6;

	/**
	 * Доступные типы выдачи getList
	 */
	const LIST_TYPES = [
		self::TYPE_FILLED_POPULAR,
		self::TYPE_FILLED_ALPHABET,
		self::TYPE_ALL_ALPHABET,
		self::TYPE_FIVE_FILLED_POPULAR,
		self::TYPE_ALL_WITH_CUSTOM_OFFER,
		self::TYPE_REVENUE,
	];

	/**
	 * ID категории ссылки.
	 */
	const LINK_CAT_ID = 59;

	/*
	 * Базовая минимальная цена кворка для категорий.
	 * если не задано - берется из App::config("price")
	 */
	const BASE_PRICE_BY_CATEGORY = [
		self::LINK_CAT_ID => 750,
	];

	/**
	 * Интервал за который считаем среднее количество предложений
	 */
	const OFFERS_AVG_INTERVAL = 6 * Helper::ONE_MONTH;

	/**
	 * Максимальное значение цены кворков в категории, после которого начинается использование второго варианта
	 *  отображение фильтра по цене
	 */
	const FIRST_CASE_UPPER_PRICE_BOUND = [
		\Translations::DEFAULT_LANG => 5000,
		\Translations::EN_LANG => 100
	];

	public static $withoutPortfolio = array('none');
	private static $categoryesData = array();

	/**
	 * Идентификатор категории индивидуального предложения
	 *
	 * @var array Для кеширования значения возвращаемого методом customOfferCategoryId()
	 */
	private static $customOfferCategoryId = [
		Translations::DEFAULT_LANG => null,
		Translations::EN_LANG => null,
	];

	/**
	 * Получить категорию как лендинг по значению какого-либо поля
	 * @param array $param ключ - поле, значение - значение поля для выборки
	 * @return bool|\Illuminate\Database\Eloquent\Model|null|object|static
	 */
	public static function getAsLandBy(array $param) {
		$field = key($param);
		$value = $param[$field];

		if (!in_array($field, [self::F_CATEGORY_ID])) {
			return false;
		}

		return Category::select([
			self::F_CATEGORY_ID . " as id",
			self::F_CATEGORY_ID . " as category_id",
			self::F_NAME . " as category_name",
			self::FIELD_SEO_I,
			self::FIELD_SEO_I . " as seo",
			self::F_PARENT . " as category_parent_id",
			self::FIELD_SEO_I . " as name",
			self::FIELD_SEO_V,
			self::FIELD_MTITLE . " as title",
			self::FIELD_MDESC . " as description",
			self::FIELD_MINFO . " as info",
			self::FIELD_SEO . " as url_key",
		])
			->where($field, $value)
			->first();
	}

	/**
	 * Получение объекта stdClass для сохранения совместимости с CategoryManager::getBy() из модели
	 *
	 * @param Category $category Модель категории
	 *
	 * @return \stdClass|null
	 */
	public static function getByCompatybilityFromModel($category) {
		if (!$category) {
			return null;
		}

		$land = new stdClass();

		$land->id = $category->CATID;
		$land->category_id = $category->CATID;
		$land->category_name = $category->name;
		$land->seo_i = $category->seo_i;
		$land->seo = $category->seo_i;
		$land->category_parent_id = $category->parent;
		$land->name = $category->seo_i;
		$land->seo_v = $category->seo_v;
		$land->title = $category->mtitle;
		$land->description = $category->mdesc;
		$land->info = $category->minfo;
		$land->url_key = $category->seo;

		return $land;
	}

	/**
	 * Отредактировать поля записи в таблице категоий, связанных с лендингами
	 * @param array $params Значения полей для обновления записи
	 * @param int $id Идентификатор записи
	 * @return bool|int
	 */
	public static function landEdit($params, $id = 0) {
		if (empty($id)) {
			return false;
		}
		if (!self::checkParams($params)) {
			return false;
		}

		$updateData = [
			self::FIELD_SEO_I => $params["seo"],
			self::FIELD_SEO_V => $params["seo_v"] ?? "",
			self::FIELD_MTITLE => $params["title"] ?? "",
			self::FIELD_MDESC => $params["description"] ?? "",
			self::FIELD_MINFO => $params["info"] ? Helper::getSqlHtmlString($params['info']) : "",
		];
		return DB::table(self::TABLE_NAME)->where(self::F_CATEGORY_ID, $id)
			->update($updateData);
	}

	public static function checkParams($params, $addReqParams = []) {
		$requiredParams = [
			'seo'
		];
		$requiredParams = array_merge($requiredParams, $addReqParams);
		foreach ($requiredParams as $param) {
			if (!isset($params[$param]) || $params[$param] == '') {
				return false;
			}
		}
		return true;
	}

	// рассчитывает популярность категории, в зависимости от количества кворков и заказов
	public static function calcUses() {
		global $conn;

		// заказов за месяц
		$orders = $conn->getColumn("SELECT p.category, COUNT(*) AS count FROM orders o JOIN posts p ON p.PID = o.PID WHERE o.time_added > unix_timestamp(now() - interval 1 month) GROUP BY p.category");

		// кворков за месяц
		$kworks = $conn->getColumn("SELECT category, COUNT(*) AS count FROM posts WHERE time_added > unix_timestamp(now() - interval 1 month) GROUP BY category");

		// заказов за 3 дня
		$orders3day = $conn->getColumn("SELECT p.category, COUNT(*) AS count FROM orders o JOIN posts p ON p.PID = o.PID WHERE o.time_added > unix_timestamp(now() - interval 3 day) and o.status in (1,4,5,8) GROUP BY p.category");

		// заказов за месяц
		$ordersMonth = $conn->getColumn("SELECT p.category, COUNT(*) AS count FROM orders o JOIN posts p ON p.PID = o.PID WHERE o.time_added > unix_timestamp(now() - interval 1 month) and o.status in (1,4,5) GROUP BY p.category");

		$categories = $conn->getList("SELECT CATID as 'id', parent FROM categories ORDER BY parent, CATID");
		foreach ($categories as $category) {
			$useCount = 0;
			$order3day = 0;
			$orderMonth = 0;

			if (array_key_exists($category->id, $orders))
				$useCount += $orders[$category->id];

			if (array_key_exists($category->id, $kworks))
				$useCount += $kworks[$category->id];

			if (array_key_exists($category->id, $orders3day))
				$order3day += $orders3day[$category->id];

			if (array_key_exists($category->id, $ordersMonth))
				$orderMonth += $ordersMonth[$category->id];

			$category->useCount = $useCount;
			$category->order3day = $order3day;
			$category->orderMonth = $orderMonth;

			if ($category->parent > 0) {
				$categories[$category->parent]->useCount += $useCount;
				$categories[$category->parent]->order3day += $order3day;
				$categories[$category->parent]->orderMonth += $orderMonth;
			}
		}

		foreach ($categories as $category)
			$conn->execute("UPDATE categories SET use_count = '" . mres($category->useCount) . "', order_3_day = '" . mres($category->order3day) . "', order_month = '" . mres($category->orderMonth) . "' WHERE CATID = '" . mres($category->id) . "'");
	}

	/**
	 * Присваивает use_count русскоязычных категорий их англоязычным аналогам
	 */
	public static function twinUseCount() {
		App::pdo()->execute('
			UPDATE ' . self::TABLE_NAME . ' t1
			INNER JOIN ' . self::TABLE_NAME . ' t2 ON t2.' . self::F_CATEGORY_ID . ' = t1.' . self::FIELD_TWIN_CATEGORY_ID . '
			SET t1.' . self::FIELD_USE_COUNT . ' = t2.' . self::FIELD_USE_COUNT . '
			WHERE t1.' . self::FIELD_LANG . ' = :lang',
			[
				'lang' => Translations::EN_LANG
			]
		);
	}

	/**
	 * Возвращает название категории, в зависимости от популярности категории
	 *
	 * @param string $lang Язык категории
	 */
	public static function getPopular($lang) {
		global $conn;

		if (App::config('redis.enable')) {
			$categoryIds = RedisManager::getInstance()->get(RedisManager::WEIGHTED_RANDOM_CATEGORY_IDS . $lang);

			while (!empty($categoryIds)) {
				$randomIndex = mt_rand(0, count($categoryIds));
				$id = $categoryIds[$randomIndex];
				$categoryName = $conn->getColumn("SELECT name FROM categories WHERE custom_offer = 0 AND CATID = '" . mres($id) . "'");
				if (!empty($categoryName)) {
					return array_pop($categoryName);
				}
				array_splice($categoryIds, $randomIndex, 1);
			}
		}

		$categories = App::pdo()->fetchAll('
			SELECT `name`, `' . self::FIELD_SEO . '`
			FROM `' . self::TABLE_NAME . '`
			WHERE `custom_offer` = 0 AND `lang` = :lang
			ORDER BY `use_count` DESC
			LIMIT 20',
			[
				'lang' => $lang
			]);
		if (!$categories)
			return "";

		return $categories[array_rand($categories)];
	}

	/**
	 * Подготавливает массив id категорий, случайно выбранных с учётом веса
	 *
	 * @param string $lang язык категорий
	 * @param int $requiredQuantity требуемое количество элементов в массиве
	 */
	public static function preparePopular($lang, $requiredQuantity = 20) {
		global $conn;
		$sql = "SELECT CATID AS id, use_count AS count FROM categories WHERE custom_offer = 0 AND lang = '" . mres($lang) . "'";
		$categoryList = $conn->getList($sql);
		if (empty($categoryList)) {
			return;
		}

		$randomCategoryIds = [];
		$categories = array_values($categoryList);

		if (count($categories) <= $requiredQuantity) {
			$randomCategoryIds = array_column($categories, 'id');
		} else {
			while (count($randomCategoryIds) < $requiredQuantity) {
				$lookup = [];
				$total_weight = 0;

				for ($i = 0; $i < count($categories); $i++) {
					$total_weight += $categories[$i]->count;
					$lookup[$i] = $total_weight;
				}

				$threshold = mt_rand(1, $total_weight);
				$low = 0;
				$high = count($lookup) - 1;
				$probe = ($lookup[$low] >= $threshold) ? $low : $low + 1;

				while ($low < $high) {
					$probe = (int)(($high + $low) / 2);
					if ($lookup[$probe] < $threshold) {
						$low = $probe + 1;
					} elseif ($lookup[$probe] > $threshold) {
						$high = $probe - 1;
					} else {
						break;
					}
				}

				$randomCategoryIds[] = $categories[$probe]->id;
				array_splice($categories, $probe, 1);
			}
		}

		if (App::config("redis.enable")) {
			RedisManager::getInstance()->set(RedisManager::WEIGHTED_RANDOM_CATEGORY_IDS . $lang, $randomCategoryIds);
		}
	}

	public static function api_getList($type) {
		return self::getList(\Translations::getLang(), $type);
	}

	/**
	 * Возвращает данные категории из кэша редиса
	 *
	 * @param int $categoryId Идентификатор категории
	 * @param string $lang Язык ru|en|all
	 *
	 * @return array|null
	 */
	public static function getCategoryFromCasheById(int $categoryId, string $lang = "all") {
		$list = self::getList($lang, self::TYPE_ALL_ALPHABET);
		$cat = [];
		foreach ($list as $parentCategory) {
			if ($parentCategory->id == $categoryId) {
				$cat = $parentCategory;
				break;
			}
			foreach ($parentCategory->cats as $category) {
				if ($category->id == $categoryId) {
					$cat = $category;
					break;
				}

			}
		}
		if (!empty($cat)) {
			return (array)$cat;
		}
		return null;
	}

	/**
	 * Возвращает список категорий
	 *
	 * @param string $lang Язык категорий: ru|en|all - см. \Translations::$langs
	 * @param int $type Тип выдачи: 1|2|3|4|5|6 см. константы TYPE_*
	 * @param bool $hideSocialSeo Скрывать категорию "Продвижение в социальных сетях"
	 * @param bool $forceCache Пропустить выгрузку из кеша
	 *
	 * @return array
	 */
	public static function getList($lang, $type, $hideSocialSeo = false, $forceCache = false) {
		global $conn;

		if (!$type || !in_array($type, self::LIST_TYPES)) {
			return null;
		}

		if ($hideSocialSeo) {
			$alias = App::getHost() . "CategoryManager_getList_type_without_smm" . $type . $lang;
		} else {
			$alias = App::getHost() . "CategoryManager_getList_type" . $type . $lang;
		}
		$categories = false;
		if (!$forceCache && App::config('redis.enable')) {
			$categories = RedisManager::getInstance()->get($alias);
		}
		if ($categories)
			return $categories;

		$where = "";
		$orderBy = "";
		$limit = "";
		$subcategoriesOrderBy = "c.name";

		// заполненные, категории по популярности, подкатегории по алфавиту
		if ($type == self::TYPE_FILLED_POPULAR) {
			if ($lang == Translations::DEFAULT_LANG) {
				$where .= " and c.use_count > 0";
			}
			$orderBy = "c.use_count desc, c.name";
		}

		// заполненные, категории по алфавиту, подкатегории по алфавиту
		if ($type == self::TYPE_FILLED_ALPHABET) {
			$where .= " and c.use_count > 0";
			$orderBy = "c.name";
		}

		// все категории, сортировка по имени
		if ($type == self::TYPE_ALL_ALPHABET || $type == self::TYPE_ALL_WITH_CUSTOM_OFFER) {
			$orderBy = "c.name";
		}

		// заполненные, по популярности, максимум 5
		if ($type == self::TYPE_FIVE_FILLED_POPULAR) {
			$where .= " and c.use_count > 0";
			$orderBy = "c.use_count desc, c.name";
			$limit = " LIMIT 5";
		}

		// отсортированы по обороту, подкатегории тоже отсортированы по обороту
		if ($type == self::TYPE_REVENUE) {
			$where .= " and c.use_count > 0";
			$orderBy = "c." . self::FIELD_MONTH_REVENUE . " DESC";
			$subcategoriesOrderBy = "c." . self::FIELD_MONTH_REVENUE . " DESC";
		}

		// у зеркала не должно быть некоторых категорий
		if (App::isMirror()) {
			$where .= " and c.allow_mirror = 1";
		}

		//Запрет выбора категорий индивидуальных предложений если не 5 тип
		if ($type != 5) {
			$where .= " and c.custom_offer = 0";
		}

		// категории
		$categories = $conn->getList("
			SELECT 
				c.CATID as 'id', 
				c.*
			FROM 
				categories c
			WHERE 
				c.parent = 0
				" . mres($limit));
		foreach ($categories as $category) {
			$category->cats = [];

			//Так как в категориях со свободными ценами кворки могут быть очень дорогими, они вполне логично могут длиться дольше 10 дней.
			if ($category->is_package_free_price)
				$category->max_days = self::FREE_PRICE_MAX_DAYS;
		}

		if (App::config('redis.enable')) {
			RedisManager::getInstance()->set($alias, $categories, Helper::ONE_MINUTE * 10);
		}

		return $categories;
	}

	public static function getParent($categoryId, $type = 2) {
		$list = self::getList(\Translations::getLang(), $type);

		foreach ($list as $category) {
			foreach ($category->cats as $sub) {
				if ($sub->id == $categoryId) {
					return $category;
				}
			}
		}

		return false;
	}

	/**
	 * Возвращает кворки для категории
	 * Кладет полученные данные в кеш на 10 минут
	 *
	 * Доступные опции для запроса:
	 *      sort   [string]
	 *      limit  [int]
	 *      offset [int]
	 *
	 * @param int $categoryId
	 * @param array $options
	 * @param int $attributeId - ID атрибута/классификации в категории
	 * @return array
	 */
	public static function getPostsByCategory(int $categoryId, array $options, int $attributeId = 0): array {
		$lang = $options['lang'] ?? Translations::getLang();
		$limit = $options['limit'] ?? 0;
		$offset = $options['offset'] ?? 0;
		$sort = $options['sort'] ?? null;

		if (App::config('redis.enable')) {
			$alias = "get_posts_by_category_id_{$categoryId}_lang_{$lang}&s={$sort}&limit={$limit}&offset={$offset}";

			if ($attributeId) {
				$alias .= "&attributeId={$attributeId}";
			}

			if ($kworksArray = RedisManager::getInstance()->get($alias)) {
				return $kworksArray;
			}
		}

		$getKworksService = new GetKworksByCategoryService();

		$kworks = $getKworksService
			->setLang($lang)
			->setLimit($limit)
			->setOffset($offset)
			->getByCategoryId($categoryId, $sort, $attributeId);

		$kworksArray = (new GetKworksArrayAdapter())
			->postsByCategory($kworks);

		if (App::config('redis.enable')) {
			RedisManager::getInstance()->set($alias, $kworksArray, Helper::ONE_MINUTE * 10);
		}

		return $kworksArray;
	}

	/**
	 * Получить список кворков по категории
	 *
	 * @param array $filters Массив фильтров
	 * @param string $sort Сортировка
	 * @param array $pageData Массив данных пагинации
	 *
	 * @return array
	 */
	public static function getByCategory($filters, $sort, $pageData, $envData = null) {
		return self::getByCategoryOld($filters, $sort, $pageData);
	}


	/** TODO Удалить метод после тестирования #6267, убрать его вызов из метода getByCategory */
	/**
	 * Получить список кворков по категории
	 *
	 * @param array $filters Массив фильтров
	 * @param string $sort Сортировка
	 * @param array $pageData Массив данных пагинации
	 *
	 * @return array
	 */
	public static function getByCategoryOld($filters, $sort, $pageData) {

		$query = Kwork::query()
			->select([
				"posts.*",
				"members.username",
			])
			->join(\Model\User::TABLE_NAME, function(JoinClause $join) {
				return $join->on('posts.USERID', '=', 'members.USERID');
			})
			->where("posts." . Kwork::FIELD_ACTIVE, Kwork::FEAT_ACTIVE)
			->orderByDesc(Kwork::FIELD_RATING)
			->limit($pageData["items_per_page"])
			->offset($pageData["pagingstart"]);

		$catResult = [
			'posts' => $query->get()->toArray(),
			'total' => $query->count(),
		];

		return $catResult;
	}

	public static function loadData($reload = false) {
		if (empty(self::$categoryesData) || $reload) {
			self::$categoryesData = Category::get()
				->keyBy(CategoryManager::F_CATEGORY_ID)
				->toArray();
		}
		return self::$categoryesData;
	}

	public static function getData($id) {
		self::loadData();
		if (empty(self::$categoryesData[$id])) {
			return false;
		}
		return self::$categoryesData[$id];
	}

	public static function excludeForMirror() {
		self::loadData();
		$return = array();
		foreach (self::$categoryesData as $catId => $catInfo) {
			if ($catInfo['allow_mirror'] == 0) {
				$return[] = $catId;
			}
		}
		return $return;
	}

	public static function getCategoryWithStr($str, $withChild = true) {
		$categoryRows = self::loadData();
		$return = array();
		foreach ($categoryRows as $catId => $catInfo) {
			if (stripos($catInfo['name'], $str) !== false) {
				$return[$catId] = true;
				if ($withChild) {
					foreach ($categoryRows as $_catId => $_catInfo) {
						if ($_catInfo['parent'] == $catId) {
							$return[$_catId] = true;
						}
					}
				}
			}
		}
		return array_keys($return);
	}

	public static function getTagline($categoryId) {
		$taglines = [
			5 => Translations::t('Когда непросто подобрать слова'),
			7 => Translations::t('Ваш Голливуд в пару щелчков мыши'),
			11 => Translations::t('Эти программисты помогут создать даже новый Фейсбук'),
			15 => Translations::t('Все ваши мечты о дизайне станут реальностью'),
			17 => Translations::t('Потому что сайт должен приносить доход'),
			45 => Translations::t('Пусть о вашем бизнесе узнают новые клиенты'),
			83 => Translations::t('Начало пути в<br/>список Форбс'),
			85 => Translations::t('Хорошая возможность сделать жизнь комфортнее'),
			86 => Translations::t('Стоит того, чтобы попробовать'),
		];
		if (isset($taglines[$categoryId])) {
			return $taglines[$categoryId];
		}
		return '';
	}

	/**
	 * Средняя конверсия по рубрике
	 */
	public static function cronUpdateConversion() {
		// Если вносите изменения в этот метод вносите также в CategoryAttributeStatisticManager->calculateAttributeConversion()
		$sql = "UPDATE categories as CAT
							SET CAT.conversion = (
											SELECT (SUM(PD.rotation_order_count) / SUM(PD.rotation_view_count)) as avgConversion
											FROM posts as P
											JOIN posts_data AS PD ON PD.kwork_id = P.PID
											WHERE P.category = CAT.CATID
											GROUP BY P.category)";
		App::pdo()->execute($sql);
	}

	/**
	 * Обновление веса категорий
	 */
	public static function cronUpdateWeight() {
		global $conn;
		$data = $conn->getList("SELECT category, SUM(rotation_weight) weight_sum FROM posts WHERE " . StatusManager::kworkListEnable() . " GROUP BY category ORDER BY weight_sum");
		// ищем минимальный, но больше 0
		{
			$minWeight = 0;
			foreach ($data as $item) {
				if ($item->weight_sum == 0) {
					continue;
				}
				$minWeight = $item->weight_sum;
				break;
			}
			if ($minWeight == 0) {
				$minWeight = 1;
			}
		}
		foreach ($data as $item) {
			$conn->execute("UPDATE categories SET rotation_weight = '" . mres(ceil($item->weight_sum / $minWeight)) . "' WHERE CATID = '" . mres($item->category) . "'");
		}
	}

	/**
	 * Можно ли для данной категории задать портфолио
	 *
	 * @param Kwork $kwork - Модель кворка
	 *
	 * @return bool true - можно, false - нет
	 */
	public static function isSupportPortfolio(Kwork $kwork): bool {
		$portfolioType = $kwork->getPortfolioType();

		if (empty($portfolioType) || in_array($portfolioType, self::$withoutPortfolio)) {
			return false;
		}
		return true;
	}

	/**
	 * Идентификатор категории Индивидуальное предложение
	 *
	 * @param string $lang Язык (если не указан то текущий язык сайта)
	 *
	 * @return int
	 */
	public static function customOfferCategoryId($lang = "") {
		if (empty($lang)) {
			$lang = Translations::getLang();
		}

		if (empty(self::$customOfferCategoryId[$lang])) {
			$individualCategory = DB::table("categories")
				->where(self::FIELD_LANG, $lang)
				->where(self::F_PARENT, "<>", 0)
				->where(self::FIELD_CUSTOM_OFFER, 1)
				->first();
			self::$customOfferCategoryId[$lang] = $individualCategory->CATID;
		}
		return self::$customOfferCategoryId[$lang];
	}

	/**
	 * Перенаправить со страницы категории Smm
	 */
	public static function redirectFromSmm() {
		$catalog = \Controllers\Catalog\AbstractViewController::DEFAULT_VIEW;
		redirect("/$catalog/" . App::config("category.smm_redirect_cat_name"));
	}

	/**
	 * Проверить, попадает ли категория под свободное ценообразование
	 * @param int $categoryId
	 * @return bool
	 */
	public static function isPackageFreePrice(int $categoryId): bool {
		$sql = "SELECT is_package_free_price FROM " . self::TABLE_NAME . " WHERE " . self::F_CATEGORY_ID . " = :categoryId";
		$res = App::pdo()->fetchScalar($sql, ["categoryId" => $categoryId]);

		return (bool)$res;
	}

	/**
	 * @param int $categoryId Идентификатор категории
	 * @param int[] $attributesIds Массив идентификаторов атрибутов
	 * @param string $filter
	 * @return string
	 */
	private static function getPriceStatRedisKey(int $categoryId, array $attributesIds, string $filter) {
		$resultString = "";
		$resultString .= $categoryId;
		if (!empty($attributesIds)) {
			$resultString .= "_" . implode(",", array_sort($attributesIds));
		}
		if ($filter === 'new') {
			$resultString .= "_1";
		}

		return \Enum\Redis\RedisAliases::PRICE_STAT_REDIS_KEY . md5($resultString);
	}

	/**
	 * Получить максимальное и минимальное значение цены активных кворков в категории
	 *
	 * @param int $categoryId Идентификатор категории
	 * @param int[] $attributesIds Массив идентификаторов атрибутов
	 * @param string $sort
	 * @return array
	 */
	public static function getCheapestAndMostExpensivePricesInCategory(int $categoryId, array $attributesIds, string $sort): array {
		$redisKey = self::getPriceStatRedisKey($categoryId, $attributesIds, $sort);
		$result = RedisManager::getInstance()->get($redisKey);
		if ($result) {
			return $result;
		}

		$additionalConditions = "";
		$additionalJoin = "";

		if ($attributesIds) {
			foreach ($attributesIds as $attributeId) {
				$attributeId = (int)$attributeId;
				$attributesAlias = "ka{$attributeId}";
				$additionalJoin .= " JOIN kwork_attributes {$attributesAlias} ON {$attributesAlias}.kwork_id = sp.PID AND {$attributesAlias}.attribute_id = {$attributeId}";
			}
		}

		if ($sort === 'new') {
			$additionalConditions .= " AND sp.time_added > " . (time() - 3 * 30 * 24 * 60 * 60);
		}

		$subQuery = "SELECT
                        IF(MIN(kp.price) IS NOT NULL, MIN(kp.price), sp.price) min,
                        IF(MAX(kp.price) IS NOT NULL, MAX(kp.price), sp.price) max
                     FROM posts sp
                     LEFT JOIN 
                        kwork_package kp ON 
                        sp.is_package = 1 AND
                        kp.kwork_id = sp.PID AND
                        kp.price IS NOT NUll AND
                        kp.price > 0
                     $additionalJoin
                     WHERE 
                        sp.category = :categoryId AND 
                        sp.active = :status AND
                        sp.lang = :lang AND
                        sp.feat = :feat
                        $additionalConditions
                        GROUP BY sp.PID";

		$sql = "SELECT 
                  MIN(p.min) min,
                  MAX(p.max) max
                FROM ($subQuery) p";

		$result = App::pdo()->fetch($sql, [
			'categoryId' => $categoryId,
			'status' => KworkManager::STATUS_ACTIVE,
			'lang' => \Translations::getLang(),
			'feat' => 1
		]);

		array_walk($result, function(&$item) {
			$item = (int)$item;
		});

		RedisManager::getInstance()->set($redisKey, $result, Helper::ONE_HOUR);

		return $result;
	}

	/**
	 * Разбирает строку для фильтрации по цене
	 *
	 * @param string $priceString
	 * @return array
	 */
	public static function parseFilterPrice($priceString): array {
		$pricesArray = [];
		$priceString = $priceString ?: '';
		$priceParts = explode('_', $priceString);
		if (count($priceParts) === 2) {
			array_walk($priceParts, function(&$item) {
				$item = $item ? (float)$item : null;
			});

			$pricesArray['from'] = $priceParts[0];
			$pricesArray['to'] = $priceParts[1];
		}

		return $pricesArray;
	}

	/**
	 * Метод крона - расчет среднего качества на заказ по рубрике
	 */
	public static function cronUpdateAvgQualityRating() {
		$categoryIds = Category::query()
			->where(self::F_PARENT, "!=", 0)
			->pluck(self::F_CATEGORY_ID)
			->toArray();

		foreach ($categoryIds as $categoryId) {
			$avgRating = self::calcAvgCategoryQualityRelative($categoryId);
			Category::query()
				->where(self::F_CATEGORY_ID, $categoryId)
				->update([self::FIELD_QUALITY_RATING_RELATIVE => $avgRating]);
		}
	}

	/**
	 * Подсчет среднего рейтинга качества на заказ по категории
	 *
	 * @param int $categoryId Идентификатор категории
	 *
	 * @return float
	 */
	private static function calcAvgCategoryQualityRelative(int $categoryId): float {
		// Если вносите изменения в этот метод - внесите также изменения в метод CategoryAttributeStatisticManager->calculateAttributeOrderQuality

		$sql = "SELECT 
					r.RID, 
					o.PID, 
					r.good, 
					r.bad, 
					o.rating_type, 
					o.status, 
					t.type, 
					t.reason_type, 
					o.OID
				FROM orders o
				JOIN posts p ON p.PID = o.PID 
				LEFT JOIN ratings r ON r.OID = o.OID 
				LEFT JOIN track t ON t.MID = o.last_track_id
				WHERE 
					p.category = :categoryId
					AND o.time_added > unix_timestamp(NOW() - INTERVAL 6 MONTH) 
					AND (
						o.status = " . OrderManager::STATUS_DONE . "
						OR (
								o.status = " . OrderManager::STATUS_CANCEL . "
								AND (t.reason_type IN ('payer_time_over', 'payer_no_communication_with_worker') OR t.type = 'admin_arbitrage_cancel')
							)
					)
					AND o." . OrderManager::F_RATING_IGNORE . " = 0
				ORDER BY o.OID ASC
				LIMIT :offset, 1000";
		$params = [
			"categoryId" => $categoryId,
			"offset" => 0
		];
		$ratingSum = 0;
		$ratedOrders = 0;

		while ($orders = App::pdo()->fetchAll($sql, $params)) {
			$ratingSum += KworkManager::calculateRatingCounter($orders);
			$ratedOrders += count($orders);
			$params["offset"] += 1000;
		}

		if ($ratedOrders) {
			return $ratingSum / $ratedOrders;
		}

		return 0;
	}

	/**
	 * Метод крона, обновить месячный оборот по категориям
	 */
	public static function cronUpdateMonthRevenue() {
		$categoriesSum = [];
		foreach (Translations::getLangArray() as $lang) {
			$langCategoriesSum = self::getMonthRevenueByLang($lang);
			foreach ($langCategoriesSum as $categoryId => $categorySum) {
				$categoriesSum[$categoryId] += $categorySum;
			}
		}

		$categoriesParents = Category::query()
			->where(self::F_PARENT, "!=", 0)
			->pluck(self::F_PARENT, self::F_CATEGORY_ID)
			->toArray();

		$parentsSum = [];

		foreach ($categoriesSum as $categoryId => $categorySum) {
			if (array_key_exists($categoryId, $categoriesParents)) {
				$parentId = $categoriesParents[$categoryId];
				$parentsSum[$parentId] += $categorySum;
			}

			Category::query()
				->where(self::F_CATEGORY_ID, "=", $categoryId)
				->update([self::FIELD_MONTH_REVENUE => $categorySum]);
		}

		foreach ($parentsSum as $parentId => $parentSum) {
			Category::query()
				->where(self::F_CATEGORY_ID, "=", $parentId)
				->update([self::FIELD_MONTH_REVENUE => $parentSum]);
		}
	}

	/**
	 * Получить месячный оборот по категории с учетом языка
	 *
	 * @param string $lang Язык
	 *
	 * @return array|false
	 */
	private static function getMonthRevenueByLang(string $lang) {
		if ($lang == Translations::DEFAULT_LANG) {
			$tableName = "cache_sales_by_cat";
		} else {
			$tableName = "cache_sales_by_cat_en";
		}

		$sql = "SELECT cat_id, SUM(fund)
				FROM $tableName
				WHERE day >= :dateStart
				GROUP BY cat_id";

		$dateStart = date("Y-m-d", time() - Helper::ONE_MONTH);

		return App::pdo()->fetchAllAssocPair($sql, 0, 1, ["dateStart" => $dateStart]);
	}

	/**
	 * Получение категорий с объемом с идентфикаторами дополнительных допустимых объемов
	 *
	 * @param string $lang Язык
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|Category[]
	 */
	public static function getVolumeCategoriesWithAdditionalVolumeIds($lang) {
		$volumeCategories = Category::where(\CategoryManager::FIELD_LANG, $lang)
			->where(\CategoryManager::F_PARENT, "!=", 0)
			->whereNotNull(\CategoryManager::F_VOLUME_TYPE_ID)
			->select([\CategoryManager::F_CATEGORY_ID, \CategoryManager::F_VOLUME_TYPE_ID])
			->get();
		Category::addAdditionalTypesIdsToCollection($volumeCategories);

		return $volumeCategories;
	}

	/**
	 * Определение, нужно ли отображать в категории языки переводов
	 *
	 * @param int $cat_id Идентификатор категории
	 *
	 * @return bool - true если да, false если нет
	 */
	public static function hasTranslationLanguages($cat_id) {
		if ($cat_id == self::CATEGORY_TRANSLATIONS_ID_RU || $cat_id == self::CATEGORY_TRANSLATIONS_ID_EN) {
			return true;
		}
		return false;
	}

	/**
	 * Является ли рубрика дизайнерской
	 *
	 * @param int $categoryId Идентификатор категории
	 *
	 * @return bool
	 */
	public static function isDesignCategory(int $categoryId): bool {
		return Category::where(self::F_CATEGORY_ID, $categoryId)
			->whereIn(self::F_PORTFOLIO_TYPE, [self::CAT_PORTFOLIO_VIDEO, self::CAT_PORTFOLIO_PHOTO])
			->where(self::FIELD_MAX_PHOTO_COUNT, ">=", self::DESIGN_CATEGORY_PORTFOLIO_QUANTITY)
			->exists();
	}

	/**
	 * Является ли рубрика дизайнерской - проверка по параметрам рубрики без обращения к БД
	 *
	 * @param string $portfolioType Тип портфолио
	 * @param int $maxPhotoCount Максимальное количество элементов портфолио в кворке
	 *
	 * @return bool
	 */
	public static function isDesignCategoryByParams(string $portfolioType, int $maxPhotoCount): bool {
		return in_array($portfolioType, [self::CAT_PORTFOLIO_VIDEO, self::CAT_PORTFOLIO_PHOTO]) &&
			$maxPhotoCount >= self::DESIGN_CATEGORY_PORTFOLIO_QUANTITY;
	}

	/**
	 * Вовзращает массив с sql данными для фильтрации по сайтам
	 * @param array $filters - массив с фильтрами
	 * @return array
	 */
	public static function getLinksSitesSqlData(array $filters): array {
		$data = [
			"join" => "",
			"where" => ""
		];
		$arJoin = [];
		$arWhere = [];

		$universalJoinRange = "
			JOIN (
				SELECT
						*
					FROM
						" . \Model\KworkLinksSitesMediansRange::TABLE_NAME . " klsmr
					WHERE
					klsmr." . \Model\KworkLinksSitesMediansRange::F_FIELD . " = '%s'
					AND klsmr." . \Model\KworkLinksSitesMediansRange::F_MIN_VALUE . " >= %s
					AND klsmr." . \Model\KworkLinksSitesMediansRange::F_MAX_VALUE . " <= %s
				) %s ON A.PID = %s." . \Model\KworkLinksSitesMediansRange::F_KWORK_ID . "";

		// фильтр ссылок - ИКС
		if ($filters["lSqiFrom"] !== null && $filters["lSqiTo"] !== null) {
			$arJoin[] = sprintf($universalJoinRange, \Kwork\KworkLinksSitesMediansManager::FIELD_SQI, $filters["lSqiFrom"], $filters["lSqiTo"], "sqi_join", "sqi_join");

		} elseif (!empty($filters["lSqiFrom"]) || !empty($filters["lSqiTo"])) {
			if (!empty($filters["lSqiFrom"])) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SQI_MORE . " >= " . (int)$filters["lSqiFrom"];
			}
			if (!empty($filters["lSqiTo"])) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SQI_LESS . " <= " . (int)$filters["lSqiTo"];
			}
			$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::FIELD_IS_SQI . " = 1";
			$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SQI_LESS . " >= 0";
		}

		// фильтр ссылок - Траст
		if ($filters["lTrustFrom"] !== null && $filters["lTrustTo"] !== null) {
			$arJoin[] = sprintf($universalJoinRange, \Kwork\KworkLinksSitesMediansManager::FIELD_TRUST, $filters["lTrustFrom"], $filters["lTrustTo"], "trust_join", "trust_join");

		} elseif ($filters["lTrustFrom"] !== null || $filters["lTrustTo"] !== null) {
			if ($filters["lTrustFrom"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_TRUST_MORE . " >= " . $filters["lTrustFrom"];
			}
			if ($filters["lTrustTo"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_TRUST_LESS . " <= " . $filters["lTrustTo"];
			}
		}

		// фильтр ссылок - Траффик
		if (Translations::isDefaultLang()) {
			$filterField = \Kwork\KworkLinksSitesMediansManager::FIELD_TRAFFIC_RU;
		} else {
			$filterField = \Kwork\KworkLinksSitesMediansManager::FIELD_TRAFFIC_EN;
		}
		if ($filters["lTrafficFrom"] !== null && $filters["lTrafficTo"] !== null) {
			$arJoin[] = sprintf($universalJoinRange, $filterField, $filters["lTrafficFrom"], $filters["lTrafficTo"], "traffic_join", "traffic_join");

		} elseif ($filters["lTrustFrom"] !== null || $filters["lTrustTo"] !== null) {
			if ($filters["lTrafficFrom"] !== null) {
				$arWhere[] = "klsm.{$filterField}_more >= " . $filters["lTrafficFrom"];
			}
			if ($filters["lTrafficTo"] !== null) {
				$arWhere[] = "klsm.{$filterField}_less <= " . $filters["lTrafficTo"];
			}
		}

		// фильтр ссылок - Спам
		if ($filters["lSpamFrom"] !== null && $filters["lSpamTo"] !== null) {
			$arJoin[] = sprintf($universalJoinRange, \Kwork\KworkLinksSitesMediansManager::FIELD_SPAM, $filters["lSpamFrom"], $filters["lSpamTo"], "spam_join", "spam_join");

		} elseif ($filters["lSpamFrom"] !== null || $filters["lSpamTo"] !== null) {
			if ($filters["lSpamFrom"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SPAM_MORE . " >= " . $filters["lSpamFrom"];
			}
			if ($filters["lSpamTo"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SPAM_LESS . " <= " . $filters["lSpamTo"];
			}
		}

		// фильтр ссылок - Majestic CF
		if ($filters["lMajesticFrom"] !== null && $filters["lMajesticTo"] !== null) {
			$arJoin[] = sprintf($universalJoinRange, \Kwork\KworkLinksSitesMediansManager::FIELD_MAJESTIC, $filters["lMajesticFrom"], $filters["lMajesticTo"], "majestic_join", "majestic_join");

		} elseif ($filters["lMajesticFrom"] !== null || $filters["lMajesticTo"] !== null) {
			if ($filters["lMajesticFrom"] != null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_MAJESTIC_MORE . " >= " . $filters["lMajesticFrom"];
			}
			if ($filters["lMajesticTo"] != null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_MAJESTIC_LESS . " <= " . $filters["lMajesticTo"];
			}
		}

		// фильтр ссылок - Количество
		if ($filters["lCountFrom"] !== null || $filters["lCountTo"] !== null || !empty($arSelectForSitesFilter)) {
			if ($filters["lCountFrom"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SITES_COUNT . " >= " . $filters["lCountFrom"];
			}
			if ($filters["lCountTo"] !== null) {
				$arWhere[] = "klsm." . \Model\KworkLinksSitesMedians::F_SITES_COUNT . " <= " . $filters["lCountTo"];
			}
		}

		if (!empty($arJoin)) {
			$data["join"] .= implode(" ", $arJoin);
		}
		if (!empty($arWhere)) {
			$data["join"] .= " JOIN " . \Model\KworkLinksSitesMedians::TABLE_NAME . " AS klsm ON A.PID = klsm." . \Model\KworkLinksSitesMedians::F_KWORK_ID . "";
			$data["where"] = implode(" AND ", $arWhere);
		}

		return $data;
	}

	/**
	 * Получить список категорий, у которых есть классификации без настроек цен
	 * (то есть не указано, что свободная цена, не задан цифровой объем у классификаций
	 * или у самой категории)
	 *
	 * @return array – ключи (идентификаторы категорий), значения (названия категорий)
	 */
	public static function getCategoriesWithNoPrices() {
		$attributes = \Attribute\AttributeManager::getAllByCurrentLang();
		$noPriceCategories = [];
		foreach ($attributes as $attribute) {
			if (false == self::isAttributeWithPrice($attribute)) {
				$noPriceCategories[] = $attribute->getCategoryId();
			}
		}

		$categories = Core\DB\DB
			::table(self::TABLE_NAME)
			->select(self::F_CATEGORY_ID, self::F_NAME)
			->whereIn(self::F_CATEGORY_ID, $noPriceCategories)
			->where([
				[self::F_IS_PACKAGE_FREE_PRICE, '=', self::FREE_PRICE_DISABLE],
				[self::F_BASE_VOLUME, '=', null],
			])
			->get()
			->pluck(self::F_NAME, self::F_CATEGORY_ID)
			->toArray();

		return $categories;
	}

	/**
	 * Установлены ли цены для атрибута/классификации c учетом всех потомков
	 * (то есть указано, что свободная цена и задан цифровой объем)
	 *
	 * Магия. Важный момент. Если для классификации разрешен множественный выбор,
	 * то считается, что цена установленна.
	 *
	 * @param \Model\Attribute $attribute
	 * @return bool – true, если цена установлена, false – иначе
	 */
	private static function isAttributeWithPrice(\Model\Attribute $attribute) {
		if ($attribute->isFreePrice() ||
			$attribute->getBaseVolume() > 0 ||
			($attribute->isClassification() && $attribute->isAllowMultiple())) {
			return true;
		}
		if (empty($attribute->getChildren())) {
			return false;
		} else {
			$result = true;
			foreach ($attribute->getChildren() as $children) {
				$result = $result && self::isAttributeWithPrice($children);
			}
		}

		return $result;
	}

	/**
	 * Подсчет среднего по категории количества предложений на запрос
	 */
	public static function updateCategoryAvgOffersCount() {
		$dateStart = Helper::now(time() - self::OFFERS_AVG_INTERVAL);

		//считаем только по запросам в которых есть date_confirm
		$categoryAverages = DB::table(WantManager::TABLE_NAME)
			->where(WantManager::FIELD_DATE_CONFIRM, ">", $dateStart)
			->select([
				WantManager::F_CATEGORY_ID,
				DB::raw("AVG(" . WantManager::FIELD_KWORK_COUNT . ") as avg")])
			->groupBy([WantManager::F_CATEGORY_ID])
			->pluck("avg", WantManager::F_CATEGORY_ID);

		// Обновляем отдельно чтобы не создавать блокировку
		foreach ($categoryAverages as $categoryId => $avg) {
			Category::where(CategoryManager::F_CATEGORY_ID, $categoryId)
				->update([CategoryManager::FIELD_AVG_OFFERS_COUNT => $avg]);
		}

		if ($categoryAverages->count()) {
			// Сбрасываем значения в категориях в которых ничего не нашлось
			Category::whereNotIn(CategoryManager::F_CATEGORY_ID, $categoryAverages->keys())
				->update([CategoryManager::FIELD_AVG_OFFERS_COUNT => 0]);
		}
	}

	/**
	 * Получить идентификатор родительской категории
	 *
	 * @param int $categoryId идентификатор категории
	 * @param boolean $withCustomOffer включать ли категории индивидуальных предложений
	 * @return int идентификатор родительской категории
	 */
	public static function getParentCategoryId($categoryId, $withCustomOffer = false) {
		$query = Category::where(\CategoryManager::F_CATEGORY_ID, $categoryId);
		if ($withCustomOffer) {
			$query = $query->withoutGlobalScope(\Model\Scopes\Category\WithoutCustomOffer::class);
		}
		return $query->value(\CategoryManager::F_PARENT);
	}

	/**
	 * Расширить массив идентификаторов категорий категорями другого языка из таблицы соответствий
	 *
	 * @param array $categoryIds Массив идентификаторов категорий
	 * @return array
	 */
	public static function extendCategoriesWithMapped($categoryIds) {
		if (empty($categoryIds)) {
			return [];
		}

		$mappedIds = Category::whereIn(CategoryManager::FIELD_MAPPED_CATEGORY_ID, $categoryIds)
			->pluck(CategoryManager::F_CATEGORY_ID)
			->toArray();

		return array_merge($categoryIds, $mappedIds);
	}

	/**
	 * Получить список родительских категорий
	 *
	 * @param $lang Язык запрашиваемых категорий
	 * @param $type Тип выдачи: 1|2|3|4|5|6 см. константы TYPE_*
	 * @return array
	 */
	public static function getParentCategories($lang = \Translations::DEFAULT_LANG, $type = self::TYPE_ALL_ALPHABET) {
		$catData = self::getList($lang, $type);
		$parentCats = [];
		foreach ($catData as $catId => $cat) {
			$parentCats[$catId] = $cat->name;
		}
		return $parentCats;
	}

	/**
	 * Получить максимальные и минимальные цены по категории
	 * @param array $filters Список фильтров
	 * @return mixed|null
	 */
	public static function getPriceLimitsByCategory($filters) {
		return null;
	}

	/**
	 * Округлять ли значение цены в большую строну в зависимости от шага
	 * @param float $price Цена
	 * @param int $priceStep Шаг
	 * @return bool true - в большую сторону, false - в меньшую
	 */
	public static function isPriceStepDirectionUp($price, $priceStep) {
		$bound = floor($price / $priceStep) * $priceStep;
		$remainder = $price - $bound;
		return $priceStep / 2 > $remainder ? false : true;
	}

	/**
	 * Получить рассчетные значения для фильтров по цене в категории
	 *
	 * @param array $filters Список фильтров
	 * @param mixed|null $priceLimits Максимальные и минимальные цены по категории если они были посчитаны заранее
	 * @param mixed|null $priceLimitsQuery Запрос для проверки есть ли кворки в заданном ценовом интервале
	 * @return array
	 */
	public static function getPriceBoundsInCategory($filters, $priceLimits = null, $priceLimitsQuery = null): array {
		$bounds = [];
		$lang = \Translations::getLang();
		$currencyId = Translations::getLangCurrency();

		if (empty($priceLimits)) {
			$priceLimits = self::getPriceLimitsByCategory($filters);
		}

		if (!empty($priceLimits)) {
			$freePrices = \FreePrice\FilterPriceManager::getPrices((int)$priceLimits->min, (int)$priceLimits->max, $lang, $filters["categoryId"]);
			$priceGraduation = [];
			if ($freePrices->maxPrice <= self::FIRST_CASE_UPPER_PRICE_BOUND[$lang]) {
				$priceFirst = $freePrices->minPrice * sqrt($freePrices->maxPrice / $freePrices->minPrice);
				$priceSecond = 0;
			} else {
				$cubeRoot = pow($freePrices->maxPrice / $freePrices->minPrice, 1 / 3);
				$priceFirst = $freePrices->minPrice * $cubeRoot;
				$priceSecond = $priceFirst * $cubeRoot;
			}
			$prevPrice = $maxPrice = 0;
			while ($currentPrice = array_shift($freePrices->typicalPriceGradation)) {
				if (empty($priceGraduation)) {
					$priceGraduation[] = $currentPrice;
				} elseif ($currentPrice >= $priceFirst && $prevPrice < $priceFirst) {
					if (!in_array($prevPrice, $priceGraduation) && !self::isPriceStepDirectionUp($priceFirst, ($currentPrice - $prevPrice))) {
						$priceGraduation[] = $prevPrice;
					}
					$priceGraduation[] = $currentPrice;
				} elseif ($currentPrice >= $priceSecond && $prevPrice < $priceSecond) {
					if (!in_array($prevPrice, $priceGraduation) && !self::isPriceStepDirectionUp($priceSecond, ($currentPrice - $prevPrice))) {
						$priceGraduation[] = $prevPrice;
					}
					$priceGraduation[] = $currentPrice;
				} elseif (count($priceGraduation) % 2) {
					$priceGraduation[] = $currentPrice;
				}
				$prevPrice = $currentPrice;
			}

			if (!empty($priceGraduation)) {
				sort($priceGraduation);
				$minPrice = array_shift($priceGraduation);
				$bounds[] = [
					"priceFrom" => $minPrice,
					"priceTo" => $minPrice,
					"value" => "_" . $minPrice,
					"title" => Translations::getPriceWithCurrencySign($minPrice, $currencyId, "<span class=\"rouble\">Р</span>"),
				];
				while (isset($priceGraduation[2])) {
					$priceFrom = array_shift($priceGraduation);
					$priceTo = array_shift($priceGraduation);
					$bounds[] = [
						"priceFrom" => $priceFrom,
						"priceTo" => $priceTo,
						"value" => $priceFrom . "_" . $priceTo,
						"title" => Translations::getPriceWithCurrencySign($priceFrom, $currencyId, "<span class=\"rouble\">Р</span>") . " - " .
							Translations::getPriceWithCurrencySign($priceTo, $currencyId, "<span class=\"rouble\">Р</span>"),
					];
				}
				if (!empty($priceGraduation)) {
					$lastPrice = array_shift($priceGraduation);
					$bounds[] = [
						"priceFrom" => $lastPrice,
						"priceTo" => "",
						"value" => $lastPrice . "_",
						"title" => Translations::getPriceWithCurrencySign($lastPrice, $currencyId, "<span class=\"rouble\">Р</span>") . " " . Translations::t("и выше"),
					];
				}
			}
		}

		if (!empty($bounds)) {
			foreach ($bounds as $key => $bound) {
				$priceFrom = $bound["priceFrom"];
				$priceTo = $bound["priceTo"];
				$foxFilterByPrice = "";
				$precludingCondition = "A.is_package = 0";
				$includingCondition = "(A.is_package = 1 AND kp.price IS NOT NULL AND kp.price > 0)";

				if ($priceFrom !== '' && $priceTo !== '') {
					$foxFilterByPrice = " AND (({$precludingCondition} AND (A.price BETWEEN {$priceFrom} AND {$priceTo})) OR ({$includingCondition} AND (kp.price BETWEEN {$priceFrom} AND {$priceTo})))";
				} elseif ($priceFrom !== '') {
					$foxFilterByPrice = " AND (({$precludingCondition} AND A.price >= {$priceFrom}) OR ({$includingCondition} AND kp.price >= {$priceFrom}))";
				} elseif ($priceTo !== '') {
					$foxFilterByPrice = " AND (({$precludingCondition} AND A.price <= {$priceTo}) OR ({$includingCondition} AND kp.price <= {$priceTo}))";
				}
				$query = "SELECT A.PID " . $priceLimitsQuery . $foxFilterByPrice . " LIMIT 1";
				if (!DB::select($query)) {
					unset($bounds[$key]);
				}
			}
			

			$bounds = array_values($bounds);
		}

		return $bounds;
	}

	/**
	 * Получение идентификаторов категорий по языку с кешированием
	 *
	 * @param string $lang Язык ru|en
	 *
	 * @return array
	 */
	public static function getLangCategoryIds(string $lang) {
		return Category::where(\CategoryManager::FIELD_LANG, $lang)
			->pluck(\CategoryManager::F_CATEGORY_ID)
			->toArray();
	}

	/**
	 * @param int $categoryId
	 * @return string
	 */
	public static function getSubcategoriesAlias(int $categoryId): string {
		$alias = App::getHost() . "cat_sub_cats_" . $categoryId;

		if (App::isMirror()) {
			$alias .= "_mirror";
		}

		if (UserManager::isHideSocialSeo()) {
			$alias .= "_nosocial";
		}

		return $alias;
	}

	/**
	 * Проверка, есть ли свободные цены в категории, выбранных атрибутах
	 * или атрибутах категории, если ни один атрибут не выбран
	 *
	 * @param Category $category Модель категории
	 * @param array $selectedAttributeIds Id выбранных атрибутов
	 * @param \Model\Attribute[] $tree Дерево атрибутов категории
	 * @return bool
	 */
	public static function isFreePrice(Category $category, array $selectedAttributeIds, array $tree) {
		// Есть ли свободные цены в категории
		if ($category->is_package_free_price) {
			return true;
		}

		// Если есть выбранные атрибуты
		if (!empty($selectedAttributeIds)) {
			// Есть ли свободные цены в выбранных атрибутах
			foreach ($selectedAttributeIds as $attributeId) {
				// Есть ли свободные цены в атрибуте или его родителях
				$currentAttributeId = $attributeId;
				while ($currentAttributeId) {
					$currentAttribute = AM::findInTreeRecursive($tree, $currentAttributeId);

					if (!$currentAttribute) {
						break;
					}

					if ($currentAttribute->isFreePrice()) {
						return true;
					} else {
						$currentAttributeId = $currentAttribute->getParentId();
					}
				}

				// Есть ли свободные цены в потомках атрибута
				$attribute = AM::findInTreeRecursive($tree, $attributeId);
				if ($attribute && $attribute->isFreePrice()) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Получить минимальную базовую цену в категории
	 *
	 * @param int|null $categoryId идентификатор категории
	 * @param string $lang язык
	 * @return int
	 */
	public static function getCategoryBasePrice($categoryId, $lang = Translations::DEFAULT_LANG) {
		if ($categoryId && !empty(self::BASE_PRICE_BY_CATEGORY[$categoryId])) {
			return self::BASE_PRICE_BY_CATEGORY[$categoryId];
		}
		if ($lang == Translations::DEFAULT_LANG) {
			return (int)App::config("price");
		} else {
			return (int)App::config("price_en");
		}
	}

	/**
	 * Получить массив минимальных цен по категориям, с ключем 0 - дефолтное значение
	 *
	 * @param string $lang язык
	 * @return array
	 */
	public static function getBasePriceByCategory($lang = Translations::DEFAULT_LANG) {
		$basePrices = self::BASE_PRICE_BY_CATEGORY;

		if ($lang == Translations::DEFAULT_LANG) {
			$basePrices[0] = (int)App::config("price");
		} else {
			$basePrices[0] = (int)App::config("price_en");
		}

		return $basePrices;
	}

	/**
	 * Вернуть идентификаторы дизайнерских категорий
	 *
	 * @param array $categoryIds массив идентификаторов категорий
	 * @return array
	 */
	public static function filterDesignCategoryIds($categoryIds) {
		$ids = Category::query()
			->select(self::F_CATEGORY_ID)
			->whereIn(self::F_CATEGORY_ID, $categoryIds)
			->whereIn(self::F_PORTFOLIO_TYPE, [self::CAT_PORTFOLIO_VIDEO, self::CAT_PORTFOLIO_PHOTO])
			->where(self::FIELD_MAX_PHOTO_COUNT, ">=", self::DESIGN_CATEGORY_PORTFOLIO_QUANTITY)
			->pluck(self::F_CATEGORY_ID)
			->toArray();

		return $ids;
	}

	/**
	 * Количество активных кворков
	 * @param int $categoryId ID категории
	 * @return int
	 */
	public static function activeKworkCount($categoryId) {
		return \Model\Kwork::where(KworkManager::FIELD_CATEGORY, $categoryId)
			->where(KworkManager::FIELD_ACTIVE, KworkManager::STATUS_ACTIVE)
			->where(KworkManager::FIELD_FEAT, KworkManager::FEAT_ACTIVE)
			->count();
	}

	/**
	 * Получить категорию по ид заказа
	 * @param int $orderId
	 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Category|object|null
	 */
	public static function getOrderCategory(int $orderId) {
		if ($orderId && $order = OrderManager::getOrderData($orderId)) {
			return Category::find($order->kworkCategory);
		}
		return null;
	}
}
