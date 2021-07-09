<?php

use Core\DB\DB;
use Core\Routing\Router;
use Illuminate\Database\Eloquent\Collection;
use Model\Kwork;
use Model\KworkLog;
use Model\RatedOrder;
use Model\Order;

class KworkManager {

	const STATUS_DRAFT = -1;
	const STATUS_MODERATION = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_SUSPEND = 2;
	const STATUS_DELETED = 3;
	const STATUS_REJECTED = 4;
	const STATUS_PAUSE = 5;
	const STATUS_CUSTOM = 6;

	const READY_FOR_BULK_SWITCH_STATUSES = [
		self::STATUS_MODERATION,
		self::STATUS_ACTIVE,
		self::STATUS_SUSPEND,
		self::STATUS_REJECTED,
		self::STATUS_PAUSE,
	];

	const FEAT_INACTIVE = 0;
	const FEAT_ACTIVE = 1;

	const MAX_ADD_RATING = 0.5;

	const MAX_TITLE_LENGTH = 80;
	const MAX_DESC_LENGTH = 50000;
	const MIN_DESC_LENGTH = 100;

	/**
	 * Множитель поля конверсии и веса для возможности хранить int вместо дроби в базе
	 */
	const KWORK_ROTATION_DB_FACTOR = 10000;

	/**
	 * Нельзя сделать заказ по кворку
	 */
	const CAN_NOT_ORDER = 1;
	/**
	 * Кворк не виден пользователям
	 */
	const CAN_NOT_VIEW = 2;
	/**
	 * Без ограничений
	 */
	const CAN_NO_LIMIT = 0;

	const LOAD_KWORK_DATA = 1;
	const LOAD_PHOTOS = 4;
	const LOAD_FILES = 16;
	const LOAD_EXTRAS = 32;
	const LOAD_PACKAGES = 64;
	const LOAD_ALL = 127;

	const PAUSE_REASON_DEFAULT = 'default';
	const PAUSE_REASON_LOW_PORTFOLIO = 'low_portfolio';

	/*
	 * Константы для таблицы kwork_log по типам логирования
	 */
	const KWORK_LOG_TYPE_ABUSE = "abuse";      // 10 штрафных баллов (обратно: нажал Разблокировать и прошло 3 дня)
	const KWORK_LOG_TYPE_ADMIN_BLOCK = "admin_block";      // Блокировка администратором
	const KWORK_LOG_TYPE_MORE_YEAR = "year";             // не заходил больше года (обратно: нажал Разблокировать)
	const KWORK_LOG_TYPE_TAKE_AWAY_BLOCK = "take_away_block"; // Блокировка за обмен контактами
	const KWORK_LOG_TYPE_PORTFOLIO = "portfolio";  // загрузил менее 50% портфолио и лимит очереди 3 заказа (обратно: загрузил портфолио или уменьшил очередь до 1 заказа)
	const KWORK_LOG_TYPE_PACKAGE = "package";    // не создан пакетный и лимит очереди 1 заказ (обратно: создал пакет или уменьшил очередь до 0 заказов)
	const KWORK_LOG_TYPE_QUEUE = "queue";      // лимит очереди 12 заказов (обратно: уменьшил очередь до 7 заказов)
	const KWORK_LOG_TYPE_MODER = "moder";      // выход с модерации
	const KWORK_LOG_TYPE_POSTMODER = "postmoder";   // выход с постмодерации
	const KWORK_LOG_TYPE_STAY_MODER = "stay_on_moder";   // постановка на модерацию
	const KWORK_LOG_TYPE_STAY_POSTMODER = "stay_on_postmoder";   // постановка на постмодерацию
	const KWORK_LOG_TYPE_AUTOACTIVE = "autoactive"; //кворк был автоматически опубликован
	const KWORK_LOG_TYPE_CREATE = "create"; //кворк был создан
	const KWORK_LOG_TYPE_AUTO_STOP = "auto_stop"; //кворк был автоматически остановлен
	const KWORK_LOG_TYPE_AUTO_REJECT = "auto_reject"; //кворк был автоотклонен
	const KWORK_LOG_TYPE_AUTO_DELETE = "auto_delete"; //кворк был автоудален, случай удаления кворков скриптами, программистами и тд.
	const KWORK_LOG_TYPE_RESTORED_AFTER_DELETE = "restored_after_delete"; //кворк был восстановлен после удаления
	const KWORK_LOG_TYPE_INACTIVE_REMOVE_FROM_MODER = "inactive_remove_from_moder"; //Остановлен и снят с очереди модерации
	const KWORK_LOG_TYPE_ACTIVE_ADD_TO_MODER = "active_add_to_moder"; //Активирован и восстановлен в очереди модерации
	const KWORK_LOG_TYPE_EDIT = "edit"; //Кворк был отредактирован
	const KWORK_LOG_TYPE_MODER_REOPEN = "moder_reopen"; //Пересмотр решения по кворку после модерации
	const KWORK_LOG_TYPE_ADMIN_CHANGE_CLASSIFICATION = "admin_change_classification"; //Смена классификации кворка в админке
	const KWORK_LOG_TYPE_ADMIN_CHANGE_CATEGORY = "admin_change_category"; //Смена категории кворка в админке
	const KWORK_LOG_TYPE_PACKAGE_PRICE_RATIO = "package_price_ratio"; // #6666 Кворк был приостановлен из-за большой разницы между ценами пакетов
	const KWORK_LOG_TYPE_SAVE_DRAFT = "save_draft"; // сохранение черновика

	/**
	 * Таблица с кворками
	 */
	const TABLE_KWORKS = "posts";


	/**
	 * Таблица логирования состояния кворков
	 */
	const TABLE_KWORKS_LOG = "kwork_log";

	/**
	 * Идентификатор кворка posts.PID
	 */
	const FIELD_PID = "PID";

	/**
	 * Поле заголовок кворка posts.gtitle
	 */
	const FIELD_TITLE = "gtitle";
	const FIELD_ACTIVE = 'active';
	const FIELD_FEAT = 'feat';
	/**
	 * Идентификатор создателя кворка
	 */
	const FIELD_USERID = "USERID";

	/**
	 * Описание кворка posts.gdesc
	 */
	const FIELD_DESC = "gdesc";

	/**
	 * url кворка в системе
	 */
	const FIELD_URL = "url";

	/**
	 * дата смены статуса posts.date_active
	 */
	const FIELD_DATE_ACTIVE = "date_active";
	/**
	 * дата изменения поля feat posts.date_feat
	 */
	const FIELD_DATE_FEAT = "date_feat";
	/**
	 * дата перевода кворка в статус «заблокирован за штрафные баллы» posts.date_block
	 */
	const FIELD_DATE_BLOCK = "date_block";

	/**
	 * Дата модерации
	 */
	const FIELD_MODER_DATE = "moder_date";

	/**
	 * Дата создания кворка в формате UNIXTIME posts.time_added
	 */
	const FIELD_TIME_ADDED = "time_added";

	/**
	 * Идентификатор последней модерации posts.last_kwork_moder_id
	 */
	const FIELD_LAST_KWORK_MODER_ID = "last_kwork_moder_id";


	/**
	 * Причина по которой кворк был поставлен на паузу
	 * <p>posts.pause_reason - ENUM</p>
	 */
	const FIELD_PAUSE_REASON = "pause_reason";

	/**
	 * Идентификатор категории кворка posts.category
	 */
	const FIELD_CATEGORY = "category";

	/**
	 * Сервисный рейтинг кворка
	 */
	const FIELD_RATING = "rating";

	/**
	 * Итоговый рейтинг кворка
	 */
	const FIELD_CRATING = "crating";

	/**
	 * итоговый рейтинг с конверсией
	 */
	const FIELD_IRATING = "irating";

	/**
	 * У скольки пользователей этот кворк находится в закладках
	 */
	const FIELD_BOOKMARK_COUNT = "bookmark_count";

	/**
	 * Источник ссылок для категории "Ссылки"
	 */
	const FILED_LINK_TYPE = 'linkType';
	/**
	 * Пакетный ли кворк posts.is_package
	 * <p>posts.is_package - int</p>
	 * <p>DEFAULT 0</p>
	 */
	const FIELD_IS_PACKAGE = "is_package";

	/**
	 * Количество продаж кворка
	 */
	const FIELD_DONE_ORDER_COUNT = "done_order_count";

	/**
	 * Количество положительных отзывов
	 */
	const FIELD_GOOD_COMMENTS_COUNT = 'good_comments_count';

	/**
	 * Язык
	 */
	const FIELD_LANG = "lang";

	/**
	 * Цена кворка (для покупателя)
	 */
	const FIELD_PRICE = "price";

	/**
	 * Главное фото кворка имеется в оригинальном размере
	 */
	const FIELD_PHOTO_HAS_ORIGINAL = "photo_has_original";

	const FIELD_BONUS_TEXT = "bonus_text";
	const FIELD_BONUS_MODERATE_STATUS = "bonus_moderate_status";
	const FIELD_BONUS_MODERATE_LAST_DATE = "bonus_moderate_last_date";

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Значение поля feat перед включением статуса продавца "Занят"
	 */
	const FIELD_WORKER_STATUS_FEAT = "worker_status_feat";

	/**
	 * Цифровой объем кворка
	 */
	const FIELD_VOLUME = "volume";

	/**
	 * Id Цифровой объем кворка
	 */
	const FIELD_VOLUME_TYPE_ID = "volume_type_id";

	/**
	 * Текстовый объем кворка
	 */
	const FIELD_GWORK = "gwork";

	/**
	 * Путь к обложке кворка
	 */
	const FIELD_PHOTO = "photo";

	/**
	 * Хеш обложки кворка для поиска одинаковых изображений
	 */
	const FIELD_PHOTO_HASH = "photo_hash";

	/**
	 * Необходимость модерации 0 - автомодерация, 1 - постмодерация, 2 - никаких больше автомодераций
	 */
	const FIELD_NEED_MODERATE = "need_moderate";

	/**
	 * Минимальный стоимость заказа для кворка
	 */
	const FIELD_MIN_VOLUME_PRICE = "min_volume_price";

	const AUTO_MODERATE = 0;
	const POST_MODERATE = 1;
	const NONE_AUTO_MODERATE = 2;

	/**
	 * Высший рейтинг
	 */
	const BEST_RATING = 370;

	/**
	 * Минимально допустимые процент ответственности испольнителя
	 */
	const RATING_USER_THRESHOLD = 0.8;

	/**
	 * Минимальный процент символов в тексте кворка на соответсвующем языке
	 */
	const MIN_LANG_PERCENT = 70;

	/**
	 * Максимально допустимы процент орфографических ошибок в коротких текстовых полях
	 */
	const MAX_MISTAKES_PERCENT_SHORT_TEXT_FIELD = 35;

	/**
	 * Максимально допустимы процент орфографических ошибок в длинных текстовых полях
	 */
	const MAX_MISTAKES_PERCENT_LONG_TEXT_FIELD = 20;

	/**
	 * Регулярное выражение для обработки строки с тегами
	 */
	const PATTERN_TRIM_TAGS = "/<p>|<\/p>|<li>|<\/li>|<ol>|<\/ol>|<strong>|<\/strong>|<em>|<\/em>|<br>|<\/br>|<div>|<\/div>|<span>|<\/span>/";

	const EN_WORKER_DEFAULT_PRICE = 8;
	const RU_WORKER_DEFAULT_PRICE = 400;

	const BONUS_STATUS_NEW = 0;
	const BONUS_STATUS_VERIFIED = 1;
	const BONUS_STATUS_CANCELED = 2;

	/**
	 * Степень возведения для просчета итогового рейтинга
	 */
	const IRATING_POWER_VALUE = 3;

	/**
	 * Процент совпадения текста, выше которого поле нельзя считать измененным.
	 * Изменение хотя бы одного слова в описании или инструкции "весит" больше одного процента
	 * Используется для проверки, были ли исправлены замечания модератора
	 */
	const MAX_ALLOWED_PERCENT_OF_SIMILARITY = 99.0;

	/**
	 * Регулярные выражения для валидации полей
	 */
	const REGEXP_TITLE = '/[^а-яА-ЯёЁa-zA-Z0-9,.\+\%\#\s\-\']/iu';
	// Ищет дубли букв и спецсимволов. {2,} - здесь меняется кол-во повторений (+1 вхождение)
	const REGEXP_LETTER_DUPLICATE = "/([^>\s;0-9]*[A-zА-яЁё_]*([A-zА-яЁё_])\\2{2,}[A-zА-яЁё_]*[^<\/\s&0-9]*)/ui";
	const REGEXP_SYMBOL_DUPLICATE = "/([^>\s;\w}]*([^a-zа-яё0-9_\s.!\-]+)\\2{2,}[^<\/\s&\w{]*)|[.!\-]{4,}/ui"; // точка, "!", и "-" попадает от 4х повторений чтобы исключить многоточие (...), (!!!) и (---)
	// Ищет слова длина которых более 45 букв. {46,} - здесь меняется кол-во букв
	const REGEXP_BIG_WORD = "/([^>\s;0-9]*([A-zА-яЁё_]){46,}[^<\/\s&0-9]*)/ui";
	// Ищет слова бувы в которых разделены пробелом или символом (прим. П_р_и_в_е_т). {5,} - минальное кол-во вхождений вида "(буква)(символ/пробел)" (к.у.к.у - будет ошибка)
	const REGEXP_SMALL_WORD = "/[^\w<0>;]+(?:[a-zа-яё]{1}(?:&nbsp;|(?=[_\W])[^<>&;]){1,2}){5,}/ui";

	/**
	 * Минимальное количество изображений с примерами размещения ссылок для
	 * классификации "Статейные ссылки" -> "На собственных сайтах"
	 */
	const MIN_LINKS_EXAMPLE_IMAGES = 3;

	/**
	 * Интервал обновления конверсии кворков - за последние две минуты
	 */
	const UPDATE_CONVERSION_INTERVAL_LAST_MINUTE = "last_minute";

	/**
	 * Интервал обновления конверсии кворков - за последний день
	 */
	const UPDATE_CONVERSION_INTERVAL_LAST_DAY = "last_day";

	/**
	 * Виртуальное поле в результате выборки кворков в котором находится подготовленная цена
	 * для пакетных кворков цена минимального пакета
	 * (или если задан фильтр цены от - цена минимального подходящего под условие пакета)
	 */
	const PACKAGE_PRICE = "package_price";

	/**
	 * Виртуальное поле в результате выборки кворков означающее нужно ли ставить "от " перед ценой
	 * для пакетных кворков если задан фильтр по цене - от убирается если это самый дорогой пакет
	 */
	const PACKAGE_IS_FROM = "package_is_from";


	/**
	 * #6666 Максимально допустимое соотношение цен пакетов Бизнес и Эконом.
	 */
	const MAX_ACCEPTABLE_PACKAGE_PRICE_RATIO = 5;

	/**
	 * Максимальная цена в списках цен для выбора (принимаю заказы от) ru
	 */
	const MAX_VOLUME_PRICE_RU = 30000;

	/**
	 * Максимальная цена в списках цен для выбора (принимаю заказы от) en
	 */
	const MAX_VOLUME_PRICE_EN = 600;

	static $smmStopWordList = ['ВК', 'Вконтакте', 'VK', 'vkontakte'];

	private static $kworksData = [];
	private static $kworksExtras = [];

	private $id;
	private $userId;
	private $active;
	private $feat;
	private $title;
	private $changed;
	private $needModeration;
	private $isPostmoderation;
	private $changedProperties;
	private $previousState;
	private $categoryId;
	private $description;
	private $workTime;
	private $serviceSize;
	private $instruction;
	/**
	 * @var \Model\ExtrasModel[]
	 */
	private $myExtras;
	private $firstPhoto;
	private $linkType;
	private $errors;
	private $descriptionFiles;
	private $uploadDescriptionFiles;
	private $instructionFiles;
	private $uploadInstructionFiles;
	private $deleteDescriptionFiles;
	private $deleteInstructionFiles;
	private $standardPackage;
	private $mediumPackage;
	private $premiumPackage;
	private $requiredPackageItemIds;
	private $packageType;
	private $favouritesKworkIds;
	private $deleteMyExtraIds;
	private $url;
	private $calcPrices;
	private $kworkPackageItemCustom;
	private $deletePackageExtrasCustomIds;
	private $isQuick;
	private $isAvailableAtWeekends;
	private $lang;
	//Близнец кворка в английской и русской версии
	//Для переведенных кворков, русский кворк тут содержит
	//id английского, английский - русского
	private $twinId;
	private $price;
	private $isCustomOffer = false;
	private $needModerate = false;
	/**
	 * @var int Для обложки кворка есть оригинальный размер
	 */
	private $photoHasOriginal;

	/**
	 * @var array Идентификаторы пакетных опций которые были до изменений в кворке
	 */
	private $oldPackageItemsIds = [];

	/**
	 * @var int|null Дата создания кворка в формате UNIXTIME
	 */
	private $time_added;

	/**
	 * @var int Числовой объем кворка
	 * (для типов из групп всегда храним в минимально возможной единице напр. если "время" - секунда)
	 */
	private $volume = 0;

	/**
	 * @var int|null Идентификатор типа цифрового объема кворка выбранного пользователем
	 * (влияет только на то какой запоминать тип из доступных)
	 */
	private $volumeTypeId = null;

	/**
	 * Модель выбранного объема кворка
	 *
	 * @var null|\Model\VolumeType
	 */
	private $volumeType = null;

	/**
	 * @var string Аудитория продвижения сайтов для ссылок
	 */
	private $linksSitesAuditory = "";
	/**
	 * @var \Model\KworkLinksSite[] Массив моделей сайтов для продвижения ссылок
	 */
	private $linksSites = [];

	/**
	 * Все классификации категории
	 * @var \Model\Attribute[]
	 */
	private $categoryClassifications = [];

	/**
	 * Классификации для валидации
	 * @var \Model\Attribute[]
	 */
	private $validationClassifications = [];

	/**
	 * Атрибутов кворка
	 * @var \Model\KworkAttribute[]
	 */
	private $attributes = [];

	/**
	 * Переводы
	 * @var array
	 */
	private $translates = [];

	/**
	 * Модель категории кворка
	 * @var null|\Model\Category
	 */
	private $category = null;

	/**
	 * @var string Тип портфолио
	 */
	private $portfolioType = null;

	/**
	 * @var Portfolio[] Массив портфолио
	 */
	private $portfolio = [];
	private $hasPortfolio = false;

	/** @var \Model\Package\AbstractPackageItem[] Пакеты опций */
	private $packagesItems;

	/**
	 * @var array Массив позиций портфолио которые не привязаны к вкору
	 */
	private $notKworkPortfolios = [];

	/**
	 * @var null|int Пользовательская величина комиссии
	 */
	private $customCtp = null;

	/**
	 * Индивидуальный кворк являющийся предложением поэтапного заказа
	 *
	 * @var bool
	 */
	private $stagedOffer = false;

	/**
	 * Выбранный пользователем тип объёма
	 * @var \Model\VolumeType
	 */
	private $customVolumeType = null;

	/**
	 * @var array Массив с именами измененных свойств до отсева измененных свойств, которые не влияют на модерацию.
	 */
	private $unmoderatedChangedProperties = [];

	/**
	 * @var int минимальная стоимость заказа для кворка
	 */
	private $minVolumePrice = 0;

	public function __construct($kworkId = null, $loadMode = self::LOAD_ALL) {
		$this->changed = false;
		$this->setNeedModeration(false);
		$this->changedProperties = [];
		$this->previousState = [];
		$this->errors = [];
		$this->myExtras = [];
		$this->descriptionFiles = [];
		$this->uploadDescriptionFiles = [];
		$this->instructionFiles = [];
		$this->uploadInstructionFiles = [];
		$this->deleteDescriptionFiles = [];
		$this->deleteInstructionFiles = [];
		$this->standardPackage = [];
		$this->mediumPackage = [];
		$this->premiumPackage = [];
		$this->standardPackage['items'] = [];
		$this->mediumPackage['items'] = [];
		$this->premiumPackage['items'] = [];
		$this->packageType = 'single';
		$this->favouritesKworkIds = [];
		$this->deleteMyExtraIds = [];
		$this->calcPrices = false;
		$this->lang = Translations::getLang();
		$this->twinId = 0;
		if ($kworkId) {
			$this->id = $kworkId;
			$this->load($loadMode);
		}
	}

	/**
	 * Добавить причину изменения в коврк
	 * @param $property
	 */
	public function setChangedProperties($property) {
		$this->changed = true;

		if (!in_array($property, $this->changedProperties)) {
			$this->changedProperties[] = $property;
		}
	}

	public function setNeedModeration($value) {
		$this->needModeration = $value === true;
	}

	private function setIsPostmoderation($value) {
		$this->isPostmoderation = $value === true;
	}

	public function get($field) {
		return $this->{$field};
	}

	private function load($loadMode) {

		if ($loadMode & self::LOAD_KWORK_DATA) {
			$data = Kwork::find($this->id);

			if (empty($data)) {
				return false;
			}

			$this->userId = $data->USERID;
			$this->active = $data->active;
			$this->feat = $data->feat;
			$this->title = $data->gtitle;
			$this->categoryId = $data->category;
			$this->description = $data->gdesc;
			$this->url = $data->url;

			$this->time_added = $data->time_added;
			$this->price = $data->price;

			$this->isQuick = $data->is_quick;

			$this->lang = $data->lang;

		}

		return $this;
	}

	public function setUserId($userId) {
		if ($this->userId != $userId) {
			$this->setChangedProperties('userId');
			$this->setNeedModeration(true);
		}
		$this->userId = $userId;

		return $this;
	}

	/**
	 * Идентификатор пользователя - создателя кворка
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	public function setFeat($feat) {
		if ($this->feat != $feat) {
			$this->setChangedProperties('feat');
		}
		$this->feat = $feat;

		return $this;
	}

	/**
	 * Устанавливает флаг того что кворк является индивидуальным предложеним и цену кворка
	 * @param int $price Цена
	 * @return object KworkManager
	 */
	public function setCustomOfferPrice($price) {
		$this
			->setFeat(0)
			->setPackageType('single')
			->setNeedModeration(false);

		$this->isCustomOffer = true;
		$this->price = $price;

		return $this;
	}

	/**
	 * Устанавливает флаг того что кворк явялется предложением поэтапного заказа
	 *
	 * @return $this
	 */
	public function setStagedOffer() {
		$this->stagedOffer = true;
		return $this;
	}

	/**
	 * Задать заголовок для kwork
	 * @param string $title заголовок
	 * @return KworkManager
	 */
	public function setTitle($title): KworkManager {
		$title = strip_tags(html_entity_decode($title));
		$title = str_replace("&nbsp;", " ", $title);
		$title = preg_replace('/[\t\r\n ]+/', ' ', $title);
		$title = trim($title, ',. ');
		$title = force_lower($title);

		if ($this->title != $title) {
			$this->setChangedProperties('title');
			$this->setNeedModeration(true);
		}
		$title = mb_ucfirst(Helper::formatText($title));
		$this->title = $title;

		return $this;
	}


	public function setCategoryId($categoryId) {
		$categoryId = (int)$categoryId;
		if ($this->categoryId != $categoryId) {
			//Очистка уведомлений о загрузке портфолио при переходе кворка в категорию с запрещенным портфолио
			$allowPortfolioOld = CategoryManager::getData($categoryId)[CategoryManager::F_PORTFOLIO_TYPE] == CategoryManager::CAT_PORTFOLIO_NONE;
			$allowPortfolioNew = CategoryManager::getData($this->categoryId)[CategoryManager::F_PORTFOLIO_TYPE] == CategoryManager::CAT_PORTFOLIO_NONE;
			$this->setChangedProperties('categoryId');
			$this->setNeedModeration(true);
		}
		$this->categoryId = $categoryId;
		$this->calcPrices = false;

		return $this;
	}

	/**
	 * Проверяет необходимость постановки/снятия с паузы при изменении категории,
	 * в случае если загруженных работ по кворку меньше необходимого процента
	 *
	 * @param Kwork $kwork
	 */
	public static function checkNewCategoryNeedPauseByLowPortfolio(Kwork $kwork): void {
		if ($kwork->isDirty([self::FIELD_CATEGORY])) {
			$needCheck = false;
			if (
				$kwork->active == KworkManager::STATUS_PAUSE
				&& in_array($kwork->category, PortfolioManager::LIMITED_FILL_CATEGORIES_EXCEPT)
				&& !in_array($kwork->getOriginal(self::FIELD_CATEGORY), PortfolioManager::LIMITED_FILL_CATEGORIES_EXCEPT)
			) {
				$needCheck = true;
			}
			if (
			$kwork->active = KworkManager::STATUS_ACTIVE
				&& !in_array($kwork->category, PortfolioManager::LIMITED_FILL_CATEGORIES_EXCEPT)
				&& in_array($kwork->getOriginal(self::FIELD_CATEGORY), PortfolioManager::LIMITED_FILL_CATEGORIES_EXCEPT)
			) {
				$needCheck = true;
			}
			if ($needCheck) {
				$newReason = KworkManager::getLowPortfolioPauseReason($kwork->PID);
				KworkManager::doPauseAction($kwork->PID, $kwork->pause_reason, $newReason);
			}
		}
	}

	/**
	 * Set kwork description
	 * @param string $description
	 * @return $this
	 */
	public function setDescription($description) {
		$oldDescription = strip_tags(html_entity_decode($this->description));
		$newDescription = strip_tags(html_entity_decode($description));
		if (Helper::checkIfTextWasChanged($oldDescription, $newDescription)) {
			$this->setChangedProperties('description');
			$this->setNeedModeration(true);
		}
		$description = Helper::removeEmptyNestedTags($description);
		$description = Helper::removeEmptyLines($description, false);
		$description = str_replace("\\", "&#92;", $description);
		$description = Helper::getSqlHtmlString($description);
		$description = preg_replace('/[\t ]+/', ' ', $description);
		$description = force_lower($description);


		$withHtml = html_entity_decode($description);
		$withHtml = Helper::formatText($withHtml);
		$withHtml = Helper::firstUpHtml($withHtml);
		$withHtml = str_replace(": \\", "&#58;&#92;", $withHtml);
		$withHtml = str_replace("\\", "&#92;", $withHtml);
		$description = Helper::getSqlHtmlString($withHtml);

		$this->description = $description;

		return $this;
	}

	public function setQuick($isQuick) {
		if (!App::config('module.quick.enable')) {
			$isQuick = 0;
		}
		$isQuick = (int)$isQuick;
		if ($this->isQuick != $isQuick) {
			$this->setChangedProperties('isQuick');
		}
		$this->isQuick = $isQuick;

		return $this;
	}

	public function setWorkTime($workTime) {
		$workTime = (int)$workTime;
		if ($this->workTime != $workTime) {
			$this->setChangedProperties('workTime');
		}
		$this->workTime = $workTime;

		return $this;
	}

	public function setPackageWorkTime() {
		if ($this->packageType != 'package') {
			return false;
		}
		$workTime = $this->standardPackage['duration'];
		if ($this->mediumPackage['duration'] < $workTime) {
			$workTime = $this->mediumPackage['duration'];
		}
		if ($this->premiumPackage['duration'] < $workTime) {
			$workTime = $this->premiumPackage['duration'];
		}

		if (!is_null($this->workTime) && $this->workTime != $workTime) {
			$this->setChangedProperties('packageProperties');
		}

		$this->workTime = $workTime;

		return $this;
	}

	/**
	 * Устанавливает текстовый объем кворка
	 * (зависит от категории и атрибутов поэтому после них)
	 *
	 * @param string $serviceSize
	 *
	 * @return $this
	 */
	public function setServiceSize($serviceSize) {
		$serviceSize = Helper::normalizeToNoTags($serviceSize);
		$serviceSize = force_lower($serviceSize);
		if ($this->serviceSize != $serviceSize) {
			$this->setChangedProperties('serviceSize');
			$this->setNeedModeration(true);
		}
		$serviceSize = Helper::firstUpHtml(Helper::formatText(Helper::trimEndOfSentencePunctuationMarks($serviceSize)));
		$this->serviceSize = $serviceSize;

		return $this;
	}

	/**
	 * Set kwork instruction
	 * @param string $instruction
	 * @return $this
	 */
	public function setInstruction($instruction) {
		$oldInstruction = strip_tags(html_entity_decode($this->instruction));
		$newInstruction = strip_tags(html_entity_decode($instruction));
		if (Helper::checkIfTextWasChanged($oldInstruction, $newInstruction)) {
			$this->setChangedProperties('instruction');
			$this->setNeedModeration(true);
		}

		$instruction = Helper::removeEmptyNestedTags($instruction);
		$instruction = Helper::removeEmptyLines($instruction, false);
		$instruction = TrumbowygFixer::editorProcess($instruction);
		$instruction = Helper::getSqlHtmlString($instruction);
		$instruction = preg_replace('/[\t ]+/', ' ', $instruction);
		$instruction = force_lower($instruction);


		$withHtml = Helper::formatText(html_entity_decode($instruction));
		$withHtml = Helper::firstUpHtml($withHtml);
		$instruction = Helper::getSqlHtmlString($withHtml);
		$this->instruction = $instruction;

		return $this;
	}

	/**
	 * Заполнение новым портфолио
	 *
	 * @param array $portfolioInput Массив данных из post
	 *
	 * @return $this
	 */
	public function setPortfolio($portfolioInput) {
		if ($this->isPortfolioAllowed() && is_array($portfolioInput)) {
			// Помечаем существующие портфолио для удаления или отвязки
			foreach ($this->portfolio as $existedPortfolio) {
				$found = false;
				foreach ($portfolioInput as $portfolioJson) {
					$portfolioId = $portfolioJson["id"];
					if ($portfolioId && $portfolioId == $existedPortfolio->id) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					if ($this->active == self::STATUS_DRAFT) {
						$existedPortfolio->setForDelete();
						$existedPortfolio->delete();
					} else {
						$existedPortfolio->forUnbind = true;
						// Это нужно чтобы после отвязывания у портфолио сохранялись категория и атрибуты
						$existedPortfolio->category_id = $this->getCategoryId();
						$existedPortfolio->newAttributesIds = $this->getAttributesIdsWithoutCustom();
						if ($existedPortfolio->images && count($existedPortfolio->images)) {
							$this->setChangedProperties("photos");
						}
						if ($existedPortfolio->videos && count($existedPortfolio->videos)) {
							$this->setChangedProperties("youtubeLink");
						}
					}
				}
			}
			// Добявляем новые портфолио
			foreach ($portfolioInput as $position => $portfolioJson) {
				if (!PortfolioManager::isAvailableToSaveInDraft($portfolioJson) && $this->active == self::STATUS_DRAFT) {
					continue;
				}
				$portfolioId = $portfolioJson["id"];
				$portfolio = null;
				if ($portfolioId) {
					foreach ($this->portfolio as $existedPortfolio) {
						if ($existedPortfolio->id == $portfolioId) {
							$portfolio = $existedPortfolio;
							break;
						}
					}
					if (!$portfolio) {
						$this->notKworkPortfolios[] = $position;
					}
				} else {
					$portfolio = new Portfolio();
					$portfolio->status = Portfolio::STATUS_ACTIVE;
					$portfolio->user_id = UserManager::getCurrentUserId();
					$portfolio->kwork_id = $this->id;
					$this->portfolio[] = $portfolio;
				}

				if ($portfolio instanceof Portfolio) {
					$portfolio->fillFromJsonForm($portfolioJson);
					$portfolio->category_id = $this->getCategoryId();
					$portfolio->position = $position;
					$portfolio->newAttributesIds = $this->getAttributesIdsWithoutCustom();
					if ($portfolio->isImagesChanges) {
						$this->setChangedProperties("photos");
					}
					if ($portfolio->isVideosChanged) {
						$this->setChangedProperties("youtubeLink");
					}
				}
			}
		} else {
			$this->portfolio = [];
		}

		return $this;
	}

	private function setDeleteFiles($type, $fileIds) {
		if (empty($fileIds)) {
			return $this;
		}
		foreach ($fileIds as $fileId) {
			if (!$fileId) {
				continue;
			}
			$this->{'delete' . $type . 'Files'}[] = $fileId;
			$this->setChangedProperties(strtolower($type) . 'Files');
		}

		return $this;
	}

	public function setPackageType($type) {
		if ($this->packageType != $type) {
			$this->setChangedProperties('packageType');
			$this->setNeedModeration(true);
		}

		$this->packageType = $type;

		return $this;
	}

	/**
	 * Возвращает цену кворка
	 *
	 * @return float
	 */
	public function getPrice() {
		if ($this->price) {
			return $this->price;
		}

		return self::getDefaultPrice($this->lang);
	}

	/**
	 * Задать цену кворка
	 * @param $price
	 * @return $this
	 *
	 */
	public function setPrice($price) {
		$this->price = $price;
		return $this;
	}

	/**
	 * Возвращает цену кворка по-умолчанию с учетом локали
	 * @param string $lang Локаль
	 * @return float Цена для продавца
	 */
	public static function getDefaultPrice($lang = Translations::DEFAULT_LANG) {
		if ($lang == Translations::DEFAULT_LANG) {
			$price = intval(App::config('price'));
		} else {
			$price = doubleval(App::config('price_en'));
		}

		return number_format($price, 2, '.', '');
	}

	/**
	 * Считает комиссию системы
	 *
	 * @param int $price Цена
	 * @param float $turnover Оборот между продавцом и покупателем
	 * @param string $lang Локаль
	 *
	 * @return float Комиссия
	 */
	public static function getCtp($price = null, $turnover = null, $lang = Translations::DEFAULT_LANG) {
		$price = is_null($price) ? intval(App::config("price")) : $price;

		$comper = intval(App::config("commission_percent"));
		$ctp = $price * $comper / 100;

		return number_format($ctp, 2, ".", "");
	}

	/**
	 * Получить минимальную цену индивидуального предложения
	 *
	 * @param string $lang Язык (если не указан то текущий)
	 *
	 * @return float
	 */
	public static function getCustomMinPrice($lang = ""): float {
		if (empty($lang)) {
			$lang = Translations::getLang();
		}

		if ($lang == Translations::DEFAULT_LANG) {
			return (float)App::config("kwork.custom_min_price");
		}
		return (float)App::config("kwork.custom_min_price_en");
	}

	/**
	 * Получить максимальную цену индивидуального предложения
	 *
	 * @param string $lang Язык (если не указан то текущий)
	 *
	 * @return float
	 */
	public static function getCustomMaxPrice($lang = ""): float {
		if (empty($lang)) {
			$lang = Translations::getLang();
		}

		if ($lang == Translations::DEFAULT_LANG) {
			return (float)App::config("kwork.custom_max_price");
		}
		return (float)App::config("kwork.custom_max_price_en");
	}

	/**
	 * Валидация полей кворка, все ошибки накапливаются в $errors
	 * @return bool Есть ли ошибки
	 * @throws Exception
	 */
	public function checkErrors() {
		$this->errors = [];
		if ($this->title == "") {
			$this->errors[] = [
				'target' => 'title',
				'text' => Translations::t("Введите название кворка")
			];
		} elseif ((!UserManager::isModer() || $this->isCustomOffer) && mb_strlen(html_entity_decode($this->title)) > self::MAX_TITLE_LENGTH) {
			$this->errors[] = [
				'target' => 'title',
				'text' => Translations::t("Название кворка не может быть длиннее %s символов", self::MAX_TITLE_LENGTH)
			];
		}

		if ($this->title != "" && !$this->stagedOffer) {
			if (preg_match(self::REGEXP_TITLE, $this->title)) {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("Название кворка не должно содержать символы кроме букв, цифр, точки и запятой")
				];
			}
			if (seo_clean_titles($this->title) == '') {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("Недопустимое название кворка")
				];
			}
			if ($this->checkWithExcludes($this->title) || preg_match(self::REGEXP_SYMBOL_DUPLICATE, $this->title)) {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("Название кворка не соответствует нормам русского языка"),
					'mistakes' => self::wrapDuplicateSymbols($this->title, $cnt, true),
				];
			}
			if (preg_match(self::REGEXP_BIG_WORD, $this->title)) {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("Превышена максимальная длина слов"),
					'mistakes' => self::wrapBigWord($this->title),
				];
			}
			if (preg_match(self::REGEXP_SMALL_WORD, $this->title)) {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("Текст не соответствует нормам русского языка"),
					'mistakes' => self::wrapSmallWord($this->title),
				];
			}
		}

		if ($this->description == "") {
			$this->errors[] = [
				'target' => 'description',
				'text' => Translations::t("Введите описание услуг")
			];
		} elseif ((!UserManager::isModer() || $this->isCustomOffer) && self::getDesciptionLength($this->description) > self::MAX_DESC_LENGTH) {
			$this->errors[] = [
				'target' => 'description',
				'text' => Translations::t("Максимальная длина описания %s символов", self::MAX_DESC_LENGTH)
			];
		} elseif ((!UserManager::isModer() || $this->isCustomOffer) && self::getDesciptionLength($this->description) < self::MIN_DESC_LENGTH) {
			$this->errors[] = [
				'target' => 'description',
				'text' => Translations::t("Минимальная длина описания %s символов", self::MIN_DESC_LENGTH)
			];
		}
		if ($this->description != "") {
			if ($this->checkWithExcludes($this->description) || preg_match(self::REGEXP_SYMBOL_DUPLICATE, $this->description)) {
				$this->errors[] = [
					'target' => 'description',
					'text' => Translations::t("Текст не соответствует нормам русского языка"),
					'mistakes' => self::wrapDuplicateSymbols($this->description, $cnt, true),
				];
			}
			if (preg_match(self::REGEXP_BIG_WORD, $this->description)) {
				$this->errors[] = [
					'target' => 'description',
					'text' => Translations::t("Превышена максимальная длина слов"),
					'mistakes' => self::wrapBigWord($this->description),
				];
			}
			if (preg_match(self::REGEXP_SMALL_WORD, $this->description)) {
				$this->errors[] = [
					'target' => 'description',
					'text' => Translations::t("Текст не соответствует нормам русского языка"),
					'mistakes' => self::wrapSmallWord($this->description)
				];
			}
		}

		if (!$this->isCustomOffer) {

			if (self::checkTitleClone($this->userId, $this->title, $this->id)) {
				$this->errors[] = [
					'target' => 'title',
					'text' => Translations::t("У вас уже есть кворк с таким названием. Измените заголовок."),
				];
			}

			// Проверка языка
			// Для русского языка есть минимальный порог русских букв в процентах при котором текст валиден
			if ($this->lang == Translations::DEFAULT_LANG) {
				if (Helper::getRuSymbolsPercentInString(Helper::removeLinks($this->description)) < self::MIN_LANG_PERCENT) {
					$this->errors[] = [
						'target' => 'description',
						'text' => Translations::t("Не менее %s%% текста должно быть написано на русском языке.", KworkManager::MIN_LANG_PERCENT)
					];
				}
			}
			// Для английского языка валидна только латиница
			if ($this->lang == Translations::EN_LANG) {
				if (Helper::countNotEnSymbols($this->title) > 0) {
					$this->errors[] = [
						'target' => 'title',
						'text' => Translations::t("Текст должен быть только на английском языке.")
					];
				}
				if (Helper::countNotEnSymbols(Helper::removeLinks($this->description)) > 0) {
					$this->errors[] = [
						'target' => 'description',
						'text' => Translations::t("Текст должен быть только на английском языке.")
					];
				}
				if (Helper::countNotEnSymbols(Helper::removeLinks($this->instruction)) > 0) {
					$this->errors[] = [
						'target' => 'instruction',
						'text' => Translations::t("Текст должен быть только на английском языке.")
					];
				}
			}
		}

		if (!empty($this->errors)) {
			return false;
		}

		return true;
	}

	/**
	 * Получить длину описания
	 * @param string $description Описание кворка или пакета
	 * @return int
	 */
	public static function getDesciptionLength(string $description) {
		$withoutTags = preg_replace(self::PATTERN_TRIM_TAGS, "", html_entity_decode($description));
		$whthoutMultispace = preg_replace("/\s\s+/", " ", $withoutTags);
		return mb_strlen($whthoutMultispace);
	}

	/**
	 * Получение названия кворка-дубля для ошибки
	 * @param int $kworkId Идентификатор кворка
	 * @return mixed|string
	 */
	public static function cloneTitleForError(int $kworkId) {
		$kworsInfo = self::getField(array($kworkId), 'gtitle');
		return Helper::truncateText(mb_ucfirst($kworsInfo[$kworkId]));
	}

	/**
	 * Получить длину инструкций
	 * @return int
	 */
	private function getInstructionLength() {
		return self::getDesciptionLength($this->instruction);
	}

	public function getUrl() {
		if (!mb_strlen($this->title) || !$this->id) {
			return false;
		}

		return self::generateUrl($this->id, $this->title, 1);
	}

	private function updateUrl($url) {
		global $conn;

		$conn->Execute("UPDATE posts SET url = '" . mres($url) . "' WHERE PID = '" . mres($this->id) . "'");

		$this->url = $url;

		return $this;
	}

	private function updateTwinIdInTwin() {
		if ($this->twinId) {
			global $conn;
			$query = "UPDATE posts SET twin_id = '" . mres($this->id) . "' WHERE PID = '" . mres($this->twinId) . "';";

			return $conn->execute($query);
		}

		return false;
	}

	/**
	 * Пометить кворк как черновик
	 */
	public function setDraft() {
		$this->active = self::STATUS_DRAFT;
	}

	/**
	 * @param bool $isDraft true - сохраняется черновик, false - сохраняется кворк
	 * @return $this
	 */
	public function save($isDraft = false) {

		if (!$isDraft && !$this->checkErrors()) {
			return false;
		}

		$this->active = self::STATUS_ACTIVE;
		$isNewKwork = true;

		if (empty($this->id)) {
			$action = 'create';
		} else {
			$action = 'update';
		}

		if ($this->id) {
			$kwork = Kwork::find($this->id);
			if ((int)$this->categoryId > 0) {
				$kwork->url = mres($this->getUrl());
			}
		} else {
			$kwork = new Kwork();
		}

		$create_time = time();
		if ($this->userId) {
			$kwork->USERID = mres($this->userId);
		}
		if ($action == "create") {
			$kwork->time_added = mres($create_time);
		}

		$setStatus = self::STATUS_ACTIVE;
		if ($this->isCustomOffer) {
			$setStatus = self::STATUS_CUSTOM;
		}

		$kwork->gtitle = str_replace(array("\n", "\r"), "", trim(mres($this->title)));
		$kwork->gdesc = mres($this->description);
		$kwork->category = (int)$this->categoryId == 0 ? null : mres((int)$this->categoryId);
		$kwork->price = mres($this->getPrice());
		$kwork->active = $setStatus;
		$kwork->ctp = (!is_null($this->customCtp) ? mres($this->customCtp) : mres(self::getCtp($this->getPrice())));
		$kwork->feat = self::FEAT_ACTIVE;
		$kwork->is_quick = (int)$this->isQuick;
		$kwork->lang = mres($this->lang);
		$kwork->date_active = date("Y-m-d H:i:s", time());
		$kwork->url = mres($this->getUrl());

		if ($this->id && !$isDraft && !$isNewKwork) {
			$this->beforeChange();
		}
		$kwork->save();

		if ($this->id && !$isDraft && !$isNewKwork) {
			$this->afterChange();
		} else {
			if ($this->id) {
				$kworkId = $this->id;
			} else {
				$kworkId = $kwork->PID;
				$this->id = $kwork->PID;
			}
			if ((int)$this->categoryId > 0) {
				$this->updateUrl($this->getUrl());
			}
			$this->time_added = $create_time;
		}

		return $this;
	}

	private function beforeChange() {
		if (empty($this->unmoderatedChangedProperties)) {
			return $this;
		}

		$logKworkData = $this->createLogKworkData();
		if (!empty($logKworkData) && array_key_exists("kwork", $logKworkData) && !empty($logKworkData["kwork"])) {
			self::logStatus($this->id, null, null, self::KWORK_LOG_TYPE_EDIT, null, $logKworkData);
		}

		return $this;
	}

	private function afterChange() {
		if (empty($this->changedProperties)) {
			return $this;
		}

		foreach ($this->changedProperties as $property) {
			switch ($property) {
				case "active":
					KworkManager::logStatus($this->id, $this->active);
					if ($this->active != self::STATUS_ACTIVE) {
						self::removeOffers($this->id);
						self::tempKworkLog($this->id, 0);
					} else {
						self::tempKworkLog($this->id, 1);
					}
					break;

				default:
			}
		}

		return $this;
	}

	/**
	 * Создает дополнительные данные для логирования изменения кворка
	 * @return array
	 */
	public function createLogKworkData(): array {
		// Избегаем дублирования при логировании изменений в пакетах
		foreach ($this->unmoderatedChangedProperties as $key => $value) {
			if (in_array($value, ["packageType", "workTime", "packageProperties"])) {
				$this->unmoderatedChangedProperties[$key] = "packages";
			}
		};

		$this->unmoderatedChangedProperties = array_unique($this->unmoderatedChangedProperties);

		$changes = [];
		if (!empty($this->unmoderatedChangedProperties)) {
			$kwork = \Model\Kwork::where(\Model\Kwork::FIELD_PID, $this->id)
				->get([
					\Model\Kwork::FIELD_GTITLE,
					\Model\Kwork::FIELD_CATEGORY,
					\Model\Kwork::FIELD_GDESC,
				])
				->first()
				->toArray();

			foreach ($this->unmoderatedChangedProperties as $property) {
				$data = $this->getValue($property, $kwork);
				if (!empty($data)) {
					$changes[$property] = $data;
				}
			}
		}

		return ["kwork" => $changes];
	}

	/**
	 * Возвращает данные для логированию для измененного свойства
	 * @param string $property
	 * @param array $kwork
	 * @return mixed|string
	 */
	private function getValue(string $property, array $kwork) {
		switch ($property) {
			case "title":
				$result = $kwork[\Model\Kwork::FIELD_GTITLE];
				break;
			case "description":
				$result = $kwork[\Model\Kwork::FIELD_GDESC];
				break;
			default:
				$result = "";
		}

		return $result;
	}

	/**
	 * Установка статуса кворку с логированием изменений
	 * @param int $kworkId - идентификатор кворка
	 * @param int $status - устанавливаемый статус
	 * @param null|string $logType - тип блокировки если есть
	 * @global DataBase $conn
	 */
	public static function setStatus($kworkId, $status, $logType = null) {
		global $conn, $actor;

		$date = "";

		if ($status == self::STATUS_SUSPEND) {
			$date = ", date_block = now()";
		}

		$kwork = self::getFields($kworkId, ['USERID', 'active', 'pause_reason', 'feat', self::FIELD_LANG, self::FIELD_PID]);

		$conn->execute("UPDATE posts SET date_active = NOW(), active = '" . mres($status) . "'" . $date . " WHERE PID = '" . mres($kworkId) . "'");
		if ($status != self::STATUS_ACTIVE) {
			self::removeOffers($kworkId);
		}

		if ($status == self::STATUS_PAUSE && $logType) {
			//Если кворк становится на паузу
			self::logStatus($kworkId, $status, null, $logType);
		} elseif ($status == self::STATUS_ACTIVE && $kwork->active == self::STATUS_PAUSE) {
			$lastKworkLog = self::getLastKworkLog($kworkId, self::STATUS_PAUSE);
			self::logStatus($kworkId, $status, null, $lastKworkLog);
		} elseif ($status == self::STATUS_SUSPEND && $logType) {
			//Если кворк блокируется
			$logKworkData = (new KworkManager($kworkId))->createLogKworkData();
			self::logStatus($kworkId, $status, null, $logType, null, $logKworkData);

		} elseif ($kwork->active == self::STATUS_SUSPEND && $status == self::STATUS_ACTIVE) {//Если кворк разблокируется
			$lastKworkLog = self::getLastKworkLog($kworkId, self::STATUS_SUSPEND);
			self::logStatus($kworkId, $status, null, $lastKworkLog);
		} else {//Обычное логирование статуса
			self::logStatus($kworkId, $status);
		}
	}

	/**
	 * Получить последний логируемый тип для кворка
	 * @param int $kworkId
	 * @param int $status
	 * @return boolean|string
	 */
	public static function getLastKworkLog($kworkId, $status) {
		$kworkId = (int)$kworkId;
		$status = (int)$status;

		if (!$kworkId) {
			return false;
		}
		$sql = "SELECT log_type
					FROM kwork_log
				WHERE kwork_id = :kworkId AND status = :status ORDER BY id DESC LIMIT 1";

		return App::pdo()->fetchScalar($sql, ["kworkId" => $kworkId, "status" => $status]);
	}

	/**
	 * Обновить значение очереди кворка
	 * @param int $kworkId id кворка
	 */
	public static function updateQueueCount($kworkId) {
		$kwork = Kwork::find($kworkId);
		if (!$kwork || $kwork->isCustom()) {
			return;
		}

		$kwork->queueCount = $kwork->orders()
			->where(\Model\Order::FIELD_STATUS, OrderManager::STATUS_INPROGRESS)
			->count();
		$kwork->save();
		self::pauseProcess($kwork->toArray());
	}


	/**
	 * Инициализация процесса постановки кворка на паузу по количеству заказов в очереди
	 * @param array $kworkInfo данные о кворке
	 * @return int статус кворка
	 */
	public static function pauseProcess($kworkInfo): int {
		$reason = self::getLowPortfolioPauseReason($kworkInfo["PID"]);

		if (empty($reason)) {
			$pauseOn = App::config("kwork.pause.on");
			$pauseOff = App::config("kwork.pause.off");

			if (($kworkInfo["queueCount"] >= $pauseOn) ||
				($kworkInfo["pause_reason"] != "" && $kworkInfo["queueCount"] > $pauseOff && $kworkInfo["queueCount"] < $pauseOn)
			) {
				$reason = KworkManager::PAUSE_REASON_DEFAULT;
			}
		}

		$result = self::doPauseAction($kworkInfo["PID"], $kworkInfo["pause_reason"], $reason);

		return $result;
	}

	/**
	 * Возвращает название причины в случае если процент загруженных работ по кворку ниже требуемого
	 *
	 * @param int $kworkId
	 * @return string
	 */
	public static function getLowPortfolioPauseReason(int $kworkId): string {
		return "";
	}

	/**
	 * Ставит/Изменяет/Удаляет паузу для кворка в зависимости от текущего его статуса и новой причины паузы
	 *
	 * @param int $kworkId - ID кворка
	 * @param string|null $kworkReason - Текущая pause_reason у кворка
	 * @param string $newReason - Новая pause_reason
	 * @return int - Статус кворка
	 */
	public static function doPauseAction(int $kworkId, ?string $kworkReason, string $newReason = ""): int {
		if (!empty($newReason)) {
			self::setPause($kworkId, $newReason);
			$result = self::STATUS_PAUSE;

		} else {
			if (!empty($kworkReason)) {
				self::unsetPause($kworkId);
			}
			$result = self::STATUS_ACTIVE;
		}

		return $result;
	}

	/**
	 * Постановка кворка на паузу
	 *
	 * @param int $kworkId - ID кворка
	 * @param string $reason - Причина постановки кворка на паузу
	 */
	public static function setPause(int $kworkId, string $reason): void {
		$logType = "";
		if ($reason == self::PAUSE_REASON_DEFAULT) {
			$logType = self::KWORK_LOG_TYPE_QUEUE;
		}
		if ($reason == self::PAUSE_REASON_LOW_PORTFOLIO) {
			$logType = self::KWORK_LOG_TYPE_PORTFOLIO;
		}
		KworkManager::setStatus($kworkId, KworkManager::STATUS_PAUSE, $logType);
		KworkManager::tempKworkLog($kworkId, 0);
	}

	/**
	 * Снять паузу с кворка
	 * @param int $kworkId - идентификатор кворка
	 * @return boolean
	 */
	public static function unsetPause($kworkId) {

		$sql = "SELECT
					" . self::FIELD_USERID . ", " . self::FIELD_ACTIVE . "
				FROM
					" . self::TABLE_KWORKS . "
				WHERE
					" . self::FIELD_PID . " = :" . self::FIELD_PID;

		$result = App::pdo()->fetch($sql, [self::FIELD_PID => $kworkId]);
		$active = $result[self::FIELD_ACTIVE] == self::STATUS_PAUSE ? self::STATUS_ACTIVE : $result[self::FIELD_ACTIVE];

		$isActiveBlock = false;
		if (!in_array($active, [self::STATUS_DELETED, self::STATUS_REJECTED])) {
			$sql = "SELECT
					" . UserManager::FIELD_KWORK_ALLOW_STATUS . "
				FROM
					" . UserManager::TABLE_NAME . "
				WHERE
					" . UserManager::FIELD_USERID . " = :" . UserManager::FIELD_USERID;

			$isActiveBlock = App::pdo()->fetchScalar($sql, [UserManager::FIELD_USERID => $result[self::FIELD_USERID]]) == UserManager::FIELD_KWORK_ALLOW_STATUS_DENY;
			$active = $isActiveBlock ? self::STATUS_SUSPEND : $active;
		}

		if ($active == self::STATUS_ACTIVE) {
			self::setStatus($kworkId, $active);
			self::tempKworkLog($kworkId, 1);
		} else {
			self::setStatus($kworkId, $active);
			self::tempKworkLog($kworkId, 0);
		}

		App::pdo()->update(self::TABLE_KWORKS,
			[self::FIELD_PAUSE_REASON => ["value" => null, "PDOType" => PDO::PARAM_NULL]],
			self::FIELD_PID . " = :" . self::FIELD_PID,
			[self::FIELD_PID => $kworkId]);

		return true;
	}

	/**
	 * Количество выполненых по кворку заказов
	 *
	 * @param int $kworkId Идентификатор кворка
	 *
	 * @return int|false
	 */
	public static function doneOrdersCount(int $kworkId) {
		$sql = "SELECT count(*) 
				FROM orders 
				WHERE PID = :kworkId 
					AND status = :status 
					AND " . OrderManager::F_RATING_IGNORE . " = 0";
		return App::pdo()->fetchScalar($sql, [
			"kworkId" => $kworkId,
			"status" => OrderManager::STATUS_DONE,
		]);
	}

	/**
	 * Функция для пересчёта рейтингов кворков
	 *
	 * @return array
	 */
	public static function calculateRatings() {
		$info = [];
		$yearAgo = date_create("last year");
		Kwork::chunkById(1000, function(Collection $kworks) use (&$info, $yearAgo) {
			$cases = [];
			/** @var Kwork $kwork */
			foreach ($kworks as $kwork) {
				$doneOrders = $kwork
					->orders()
					->where(Order::FIELD_STATUS, OrderManager::STATUS_DONE)
					->where(Order::FIELD_DATE_DONE, ">=", $yearAgo);
				$count = $doneOrders->count();
				$sum = $doneOrders->sum(Order::FIELD_PAYER_AMOUNT);
				$rating = round($count * $sum * 0.01);
				$cases[] = "when " . Kwork::FIELD_PID . " = {$kwork->PID} then {$rating}";
				$info[$kwork->PID] = ["old" => $kwork->rating, "new" => $rating];
			}

			if (!empty($cases)) {
				Kwork::whereKey($kworks->pluck(Kwork::FIELD_PID))
					->update([
						Kwork::FIELD_RATING => DB::raw("CASE " . implode(" ", $cases). " END"),
					]);
			}
		});
		return $info;
	}

	/**
	 * Подсчет рейтинга качества кворков
	 *
	 * @param array $orders Массив заказов которые влияют на качество из KworkManager::getRatingOrders()
	 * @param bool $returnStatistics Вернуть ли статистику учтенных заказов
	 *
	 * @return array|float|int
	 */
	public static function calculateRatingCounter($orders, $returnStatistics = false) {
		$statistics = [
			'good_ratings' => 0,
			'bad_ratings' => 0,
			'empty_ratings_one' => 0,
			'empty_ratings_many' => 0,
			'cancel_wo_review' => 0,
			'cancel_with_review' => 0,
			'cancel_arbitrage' => 0,
			'tips' => 0
		];

		$ratingCounter = 0;

		if (!empty($orders)) {
			foreach ($orders as $order) {
				if (!is_null($order['RID'])) {                //если у заказа есть отзыв
					if ($order['good']) {
						$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::GOOD_REVIEW];              //хороший
						$statistics['good_ratings']++;
					} else {
						if ($order['status'] == OrderManager::STATUS_CANCEL) {
							$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::CANCEL_WITH_REVIEW];             //плохой отзыв на отмененный заказ
							$statistics['cancel_with_review']++;
						} else {
							$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::BAD_REVIEW];             //плохой отзыв на принятый заказ
							$statistics['bad_ratings']++;
						}
					}
				} else {
					if ($order["rating_type"] == OrderManager::RATING_TYPE_TIPS) { // без отзыва но с бонусом
						$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::TIPS];
						$statistics["tips"]++;
					} else { // без отзыва и без бонуса
						if ($order['status'] == OrderManager::STATUS_CANCEL) {  //если это отмененный заказ
							// Причины заказ просрочен
							$expiredReasons = [
								\TrackManager::REASON_TYPE_PAYER_TIME_OVER,
							];
							if (in_array($order['reason_type'], $expiredReasons)) {   //просрочен
								$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::CANCEL_WO_REVIEW];
								$statistics['cancel_wo_review']++;
							} elseif ($order['reason_type'] == \TrackManager::REASON_TYPE_PAYER_NO_COMMUNICATION_WITH_WORKER) {
								// #7604 Причина "нет связи с продавцом" после истечения срока должна учитываться как отмена с отрицательным отзывом
								$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::CANCEL_WITH_REVIEW];
								$statistics['cancel_with_review']++;
							} elseif ($order['type'] == \TrackManager::REASON_TYPE_ADMIN_ARBITRAGE_CANCEL) { //отменен в пользу покупателя через арбитраж
								$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::CANCEL_ARBITRAGE_NEW];
								$statistics['cancel_arbitrage']++;
							}
						} else {
							if ($order["rating_type"] != OrderManager::RATING_TYPE_SECOND) {
								$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::ONE_DONE_WO_REVIEW]; //если это первый заказ этого покупателя без отзыва
								$statistics['empty_ratings_one']++;
							} else {
								$ratingCounter += RatedOrder::ORDER_RATES[RatedOrder::REPEAT_DONE_WO_REVIEW]; //если это последующие заказы этого покупателя без отзыва
								$statistics['empty_ratings_many']++;
							}
						}
					}
				}
			}
		}
		$statistics['rating'] = $ratingCounter * 100;
		if ($returnStatistics) {
			return [
				'ratingCounter' => $ratingCounter,
				'statistics' => $statistics
			];
		}

		return $ratingCounter;
	}

	public static function getData($kworkId) {
		global $conn;

		$data = $conn->getEntity("	SELECT
										p.PID as 'kworkId',
										p.gtitle as 'kworkName',
										p.url as 'kworkUrl',
										m.USERID as 'workerId',
										m.username as 'workerLogin', 
										case when m.fullname = '' then m.username else m.fullname end as 'workerName', 
										m.email as 'workerEmail',
										m.lang as 'workerLang',
										p.category,
										p.lang,
									FROM
										posts p
										join members m on m.USERID = p.USERID
									WHERE
										p.PID = '" . mres($kworkId) . "'");

		$data->workerEmail = Crypto::decodeString($data->workerEmail);

		return $data;
	}

	/**
	 * Логирование изменения кворка
	 * @param int $kworkId - идентификатор кворка
	 * @param int $status - статус кворка
	 * @param int $feat - флаг активирования пользователем
	 * @param string $logType - тип лога
	 * @param int $kworkModerId - идентификатор модерации
	 * @param array $data - Дополнительные данные для лога
	 * @global object $actor - объект пользователя
	 */
	public static function logStatus($kworkId, $status = null, $feat = null, $logType = null, $kworkModerId = null, array $data = []) {
		$actor = UserManager::getCurrentUser();

		$fields = ["kwork_id" => $kworkId];
		if ($actor) {
			$fields["user_id"] = $actor->id;
			$fields[KworkLog::FIELD_IS_VIRTUAL] = (int)$actor->isVirtual;
		}
		if (!is_null($status)) {
			$fields["status"] = $status;
		}
		if (!is_null($feat)) {
			$fields["feat"] = $feat;
		}
		if (!is_null($logType)) {
			$fields["log_type"] = $logType;
		}
		if (!is_null($kworkModerId)) {
			$fields["kwork_moder_id"] = $kworkModerId;
		}
		if ($data) {
			$fields["data"] = json_encode($data);
		}

		$session = Session\SessionContainer::getSession();
		if ($session->notEmpty("ADMINID") && is_numeric($session->get("ADMINID"))) {
			$fields["admin_id"] = $session->get("ADMINID");
		}

		KworkLog::insertLog($fields);
	}

	/**
	 * Активация кворка (конкретной языковой версии)
	 *
	 * @param \Model\Kwork $kwork Модель кворка
	 * @param array $injectData Произвольный массив данных для использования внутри метода
	 * @return string Сообщение об успешной активации кворка
	 * @throws \Exception
	 */
	public static function activateLogic(\Model\Kwork $kwork, $injectData = []) {
		global $actor;

		if ($actor->kwork_allow_status == "deny") {
			throw new Exception(Translations::t("Вы не можете активировать кворк сейчас."));
		}

		if (isset($injectData["attributes"]) &&
			isset($injectData["haveLinksSites"]) &&
			isset($injectData["demoFileId"]) &&
			isset($injectData["requiredParentIds"])
		) {
			$isNeedUpdate = KworkManager::isNeedUpdateBase($injectData["attributes"],
				$injectData["haveLinksSites"],
				$injectData["demoFileId"]
			);
		} else {
			$isNeedUpdate = self::isNeedUpdate($kwork->PID);
		}

		if ($isNeedUpdate) {
			throw new Exception(Translations::t("Не удалось изменить кворк, обновите описание, так как в интерфейсе появились новые возможности."));
		}

		if (self::isNeedUpdateTranslates($kwork->PID)) {
			throw new Exception(Translations::t("Не удалось изменить кворк, обновите описание, так как в данной рубрике появилась возможность указать язык перевода"));
		}

		if (self::isNeedUpdatePackagePrices($kwork->PID)) {
			$timesNum = self::MAX_ACCEPTABLE_PACKAGE_PRICE_RATIO;
			$timesStrRu = $timesNum . " " . Helper::pluralNumber($timesNum, "раз", "раза", "раз");
			$timesStrEn = $timesNum;
			$times = Translations::isDefaultLang() ? $timesStrRu : $timesStrEn;
			throw new Exception(Translations::t("Стоимость цены Эконом пакета должна отличаться от цены Бизнес не более чем в %s. Повысьте цену пакета Эконом или понизьте цену Бизнес.", $times));
		}

		$message = "";
		switch ($kwork->active) {
			case KworkManager::STATUS_MODERATION:  // на модерации
				$kwork->moder_date = Helper::now();
				$message = Translations::t("Кворк активирован и станет активным после проверки модератором");
				break;

			case KworkManager::STATUS_ACTIVE:  // активный
				$message = Translations::t("Кворк активирован");
				break;

			case KworkManager::STATUS_SUSPEND: // заблокирован (за штрафные баллы)
				throw new Exception(Translations::t("Вы не можете активировать заблокированный кворк"));
				break;

			case KworkManager::STATUS_DELETED:  // удален
				throw new Exception(Translations::t("Вы не можете активировать удаленный кворк"));
				break;

			case KworkManager::STATUS_REJECTED:  // отклонен модератором
				throw new Exception(Translations::t("Вы не можете активировать кворк, отклоненный модератором. Вам нужно его отредактировать и исправить ошибки."));
				break;

			case KworkManager::STATUS_PAUSE:  // на паузе
				$message = Translations::t("Кворк активирован и станет активным после уменьшения очереди заказов в этом кворке");
				break;

			default:
				throw new \Exception("Не известный тип статуса кворка: '{$kwork->active}'");
		}

		// Если дошли до сюда без эксепшена значит сохраняем
		$kwork->feat = KworkManager::FEAT_ACTIVE;
		$kwork->date_feat = Helper::now();
		if ($kwork->isDirty(KworkManager::FIELD_FEAT)) {
			if ($kwork->active == self::STATUS_MODERATION) {
				KworkManager::logStatus($kwork->PID, null, self::FEAT_ACTIVE, self::KWORK_LOG_TYPE_ACTIVE_ADD_TO_MODER);
			} else {
				KworkManager::logStatus($kwork->PID, null, self::FEAT_ACTIVE);
			}
			KworkManager::tempKworkLog($kwork->PID, 0);
		}
		$kwork->save();

		return $message;
	}

	/**
	 * Удаление кворка
	 * @param int $kworkId Идентификатор кворка
	 * @param int $type Тип удаления
	 */
	public static function delete($kworkId, $type = 4) {
		global $conn;
		global $actor;
		if (!$actor || !$kworkId) {
			return;
		}
		$kwork = $conn->getEntity("SELECT PID AS 'id', USERID AS 'userId', active, lang FROM posts WHERE PID = '" . mres($kworkId) . "'");
		if (!$kwork || $kwork->userId != $actor->id) {
			return;
		}
		$twinKwork = null;
		$sql = "UPDATE posts SET date_active = NOW(), active = 3, feat = 0, date_feat = now() WHERE PID = '" . mres($kwork->id) . "';";
		switch ($type) {
			case 1://all
				if ($twinKwork) {
					$sql .= "UPDATE posts SET date_active = NOW(), active = 3, feat = 0, date_feat = now() WHERE PID = '" . mres($twinKwork->id) . "';";
				}
				break;
			case 2://russian
				if ($kwork->lang == Translations::DEFAULT_LANG) {
					if ($twinKwork) {
						$sql .= "UPDATE posts SET twin_id = NULL WHERE PID = '" . mres($twinKwork->id) . "';";
						self::cleanKwork($twinKwork);
					}
				} else {
					return;
				}
				break;
			case 3://english
				if ($kwork->lang == Translations::EN_LANG) {
					if ($twinKwork) {
						$sql .= "UPDATE posts SET twin_id = NULL WHERE PID = '" . mres($twinKwork->id) . "';";
						self::cleanKwork($twinKwork);
					}
				} else {
					if ($twinKwork) {
						$sql = "UPDATE posts SET date_active = NOW(), active = 3, feat = 0, date_feat = now() WHERE PID = '" . mres($twinKwork->id) . "';";
						$sql .= "UPDATE posts SET twin_id = NULL WHERE PID = '" . mres($kwork->id) . "';";
						$kwork = $twinKwork;
					} else {
						return;
					}
				}
				break;
			case 4://current
				break;
			default :
				break;
		}

		$conn->execute($sql);
		self::cleanKwork($kwork);
	}

	/**
	 * Очистка связанных данных при удалении кворка
	 * @param object $kwork Кворк с полями id, userId
	 */
	private static function cleanKwork($kwork) {
		self::removeOffers($kwork->id);
		KworkManager::logStatus($kwork->id, self::STATUS_DELETED, self::FEAT_INACTIVE);
		KworkManager::tempKworkLog($kwork->id, 0);
	}

	public static function getSameCategoryKworks($kworkId, $excludeKworkIds = [], $limit = 4, $excludeUserId = null) {
		return [];
	}

	/**
	 * Данные для отображения кворка
	 * @param $kworkId
	 * @return array|false
	 */
	public static function getViewData($kworkId) {

		$kworkInfo = self::getKworkData($kworkId);
		if (empty($kworkInfo)) {
			return false;
		}

		$sellerUserInfo = UserManager::getUserData($kworkInfo['USERID']);
		if (empty($sellerUserInfo)) {
			return false;
		}

		$p = array_merge($kworkInfo, $sellerUserInfo);

		$p['url'] = $kworkInfo['url'];
		$p['lang'] = $kworkInfo['lang'];
		$p["gtitle"] = mb_ucfirst($p["gtitle"]);
		$p["gwork"] = mb_ucfirst($p["gwork"]);
		$p['gdesc_source'] = Helper::getSqlHtmlString(Helper::firstUpHtml(Helper::formatText(html_entity_decode($p['gdesc']))));;
		$p['ginst_source'] = Helper::getSqlHtmlString(Helper::firstUpHtml(Helper::formatText(html_entity_decode($p['ginst']))));
		//Ссылки в описании "что понадобится продавцу" сделать ссылками

		$p['parent_cat'] = [];
		if ((int)$p['parent'] !== 0) {
			$parentCategoryInfo = CategoryManager::getData($p['parent']);
			$p['parent_cat']['id'] = $parentCategoryInfo['CATID'];
			$p['parent_cat']['name'] = $parentCategoryInfo['name'];
			$p['parent_cat']['alias'] = $parentCategoryInfo['seo'];
		}

		$p['was_moder_confirmed'] = true;

		return $p;
	}

	/**
	 * Получить отформатированный HTML текст
	 * @param string $text
	 * @return string
	 */
	public static function getViewHtmlText($text) {
		$text = str_replace("&amp;nbsp;", ' ', $text);
		$text = html_entity_decode($text);
		$text = Helper::closeTags($text);
		$text = htmlentities(replace_full_urls(Helper::firstUpHtml(Helper::formatText($text))));

		return $text;
	}

	public static function getViewDataMoreUserKworks($kworkId, $userId, $limit = 4, $isModer = false) {
		global $conn;

		if (!$isModer) {
			$limitSql = "LIMIT $limit";
			$statusCondition = "WHERE A.lang = '" . Translations::getLang() . "' AND A.USERID = '" . mres($userId) . "' and " . StatusManager::kworkListEnable('A');
		} else {
			$limitSql = "";
			$restrictedStatuses = [self::STATUS_DELETED, self::STATUS_CUSTOM, self::STATUS_DRAFT];
			$statusCondition = "WHERE A.lang = '" . Translations::getLang() . "' AND A.USERID = '" . mres($userId) . "' AND A.active NOT IN (" . implode(",", $restrictedStatuses) . ") AND A.PID <> '" . mres($kworkId) . "'";
			$join = "";
		}
		$hiddenCondition = '';
		$query = "SELECT
							A.PID, 
							A.gtitle, 
							A.gdesc, 
							A.price, 
							A.url, 
							C.username, 
							C.USERID, 
							A.category, 
							A.active,  
							A.feat, 
							A.lang
					FROM
							posts A
							$join
							join members C on C.USERID = A.USERID
						$statusCondition
					ORDER BY
							A.rating desc, A.PID desc
					$limitSql";
		$results = $conn->execute($query);
		$posts = $results->getrows();
		$limit = $limit - count($posts);
		if (!$isModer && $limit > 0) {
			$ids = [];
			foreach ($posts as $item) {
				$ids[] = $item['PID'];
			}
			$sql_ids = '';
			if (count($ids) > 0) {
				$sql_ids = "AND A.PID NOT IN (" . implode(',', $ids) . ")";
			}
			$query = "SELECT
								A.*
						FROM
								posts A
						WHERE
								A.lang = '" . Translations::getLang() . "'
								AND A.PID!='" . mres($kworkId) . "'
								AND A.USERID='" . mres($userId) . "'
								AND " . StatusManager::kworkListEnable('A') . "
								" . $sql_ids . "
								" . $hiddenCondition . "
						ORDER BY
								A.rating desc, A.PID desc
						LIMIT
							" . $limit;
			$res = $conn->Execute($query);
			$posts2 = $res->getrows();

			// от одного продавца
			KworkManager::fillUserInfo($posts2, [
				'username',
				'USERID',
				'toprated',
				"cache_rating_count_en",
				[
					'field' => 'cache_rating_count',
					'alias' => 'userRatingCount'
				],
				[
					'field' => 'cache_rating',
					'alias' => 'userRating'
				]
			]);

			$posts = array_merge($posts, $posts2);
		}
		// информация по категориям
		KworkManager::fillCategoryInfo($posts, ['name', 'seo']);

		foreach ($posts as $key => $post) {
			$posts[$key]['gdesc'] = str_replace("&amp;nbsp;", ' ', $posts[$key]['gdesc']);
			$posts[$key]['gdesc'] = strip_tags(replace_full_urls(html_entity_decode($posts[$key]['gdesc'])));
			$posts[$key]['cat_data'] = get_post_categories($posts[$key]['PID']);
		}

		return $posts;
	}

	public static function getKworkData($id) {
		if (!isset(self::$kworksData[$id])) {
			global $conn;

			$sql = 'SELECT * FROM posts WHERE PID=' . intval($id);
			$executequery = $conn->Execute($sql);
			$p = $executequery->getrows();
			$set = false;
			if (!empty($p)) {
				$set = array_shift($p);
				if (!empty($set["twin_id"])) {
					$url = \Model\Kwork::where(KworkManager::FIELD_PID, $set["twin_id"])
						->value(KworkManager::FIELD_URL);
					if (!empty($url)) {
						$set['twinUrl'] = $url[0]['url'];
					}
				}
			}
			self::$kworksData[$id] = $set;
		}

		return self::$kworksData[$id];
	}

	public static function getKworkForOrder(int $orderedKworkId) {
		$kworkInfo = KworkManager::getKworkData($orderedKworkId);
		if (empty($kworkInfo)) {
			return null;
		}
		UserManager::preLoadInfo($kworkInfo['USERID']);

		// кворк
		$kworkInfo['id'] = $kworkInfo['PID'];
		$kworkInfo['userId'] = $kworkInfo['USERID'];

		$userInfo = UserManager::getUserData($kworkInfo['USERID']);
		$kworkInfo['username'] = $userInfo['username'];
		$kwork = (object)$kworkInfo;

		$kwork->instructionFiles = FileManager::getEntityFiles($kwork->id, FileManager::ENTITY_TYPE_KWORK_INSTRUCTION);

		return $kwork;
	}

	public static function fillCategoryInfo(array &$posts, array $fields = []) {
		foreach ($posts as $key => $post) {
			if (empty($post['category'])) {
				continue;
			}
			$categoryInfo = CategoryManager::getData($post['category']);
			$setFields = $fields;
			if (empty($setFields)) {
				$setFields = array_keys($categoryInfo);
			}

			foreach ($setFields as $field) {
				if (is_string($field)) {
					$posts[$key][$field] = $categoryInfo[$field];
				} elseif (is_array($field) && !empty($field['field']) && !empty($field['alias'])) {
					$posts[$key][$field['alias']] = $categoryInfo[$field['field']];
				}
			}
		}
	}

	public static function fillUserInfo(array &$posts, array $fields = []) {
		foreach ($posts as $key => $post) {
			if (empty($post['USERID'])) {
				continue;
			}
			$userInfo = UserManager::getUserData($post['USERID']);
			$setFields = $fields;
			if (empty($setFields)) {
				$setFields = array_keys($userInfo);
			}

			foreach ($setFields as $field) {
				if (is_string($field)) {
					$posts[$key][$field] = $userInfo[$field];
				} elseif (is_array($field) && !empty($field['field']) && !empty($field['alias'])) {
					$posts[$key][$field['alias']] = $userInfo[$field['field']];
				}
			}
		}
	}

	public static function updateDateModify($kworkId) {
		global $conn;

		$query = "UPDATE posts SET date_modify = NOW() WHERE PID = '" . mres($kworkId) . "'";

		return $conn->execute($query);
	}

	public static function fillUsersInfo(array &$posts, array $fields = [], $userIdField = 'USERID') {
		global $conn;

		$usersInfo = [];
		foreach ($posts as $postInfo) {
			$usersInfo[$postInfo[$userIdField]] = true;
		}
		if (empty($usersInfo)) {
			return true;
		}

		$sql = 'SELECT * FROM members WHERE USERID IN (' . implode(',', array_keys($usersInfo)) . ')';
		$usersResult = $conn->Execute($sql)->getrows();
		foreach ($usersResult as $userInfo) {
			$usersInfo[$userInfo['USERID']] = $userInfo;
		}

		foreach ($posts as $key => $post) {
			if (empty($post[$userIdField])) {
				continue;
			}
			$userInfo = $usersInfo[$post[$userIdField]];
			$setFields = $fields;
			if (empty($setFields)) {
				$setFields = array_keys($userInfo);
			}

			foreach ($setFields as $field) {
				if (is_string($field)) {
					$posts[$key][$field] = $userInfo[$field];
				} elseif (is_array($field) && !empty($field['field']) && !empty($field['alias'])) {
					$posts[$key][$field['alias']] = $userInfo[$field['field']];
				}
			}
		}
	}

	public static function removeOffers($kworkId) {
		global $conn;
		$kworkId = intval($kworkId);

		$kworkInfo = $conn->getEntity("SELECT USERID FROM posts WHERE PID=" . $kworkId);
		if (empty($kworkInfo)) {
			return false;
		}

		$ordersId = $conn->getColumn("SELECT OID FROM orders WHERE PID=" . $kworkId . " AND status=" . OrderManager::STATUS_NEW);
		if (empty($ordersId)) {
			return true;
		}

		$offersId = $conn->getColumn("SELECT id FROM offer WHERE order_id IN (" . implode(',', $ordersId) . ")");
		if (empty($offersId)) {
			return true;
		}
	}

	public static function getWithOffers(array $kworksIds) {
		global $conn;

		if (!$kworksIds) {
			return [];
		}

		$kworksIds = implode(",", $kworksIds);

		return $conn->getColumn("SELECT DISTINCT kwork_id FROM offer WHERE kwork_id in (" . $kworksIds . ") and status in ('done', 'active', 'cancel')");
	}

	public static function api_getDetails() {
		global $conn, $smarty, $actor;
		$kworkId = intval(post('kworkId'));
		$orderId = intval(post('orderId'));
		if (!empty($orderId) && is_numeric($orderId)) {
			WantManager::registerViewOfferByOrder($orderId);
		}
		$kwork = $conn->getEntity("SELECT PID as id, gdesc FROM posts WHERE PID = '" . mres($kworkId) . "'");
		$kwork->gdesc = replace_full_urls(html_entity_decode(stripslashes($kwork->gdesc)));
		$goodReviews = RatingManager::getByKworkId($kwork->id, ["type" => "positive", "offset" => 0, "limit" => 5]);
		$goodReviews = RatingManager::getKworkReviewsHTML($kwork->id, $goodReviews, "positive", "block", true);
		if (!$goodReviews['html']) {
			$badReviews = RatingManager::getByKworkId($kwork->id, ["type" => "negative", "offset" => 0, "limit" => 5]);
			$badReviews = RatingManager::getKworkReviewsHTML($kwork->id, $badReviews, "negative", "block", true);
		}
		$smarty->assign('isProjectDetails', 1);

		return [
			'status' => 'success',
			'content' => [
				'kwork' => $kwork,
				'reviews' => $goodReviews['html'] != '' ? $goodReviews : $badReviews,
				'track_order' => self::getTrackOrder($orderId, $actor->type),
				'files' => $smarty->fetch('files_list.tpl'),
			],
		];
	}

	/**
	 * Выдает отрендеренный шаблон заказа
	 * @param int $orderId Идентификатор заказа
	 * @param string $forUser Для кого рендерится worker|payer
	 * @param bool $noFullInfo Не показывать полностью все данные
	 * @param string $inboxStatus Статус предложения в сообщении new|done|cancel
	 * @return array ["html" => string, "orderData" => array]
	 * @throws Exception
	 * @throws SmartyException
	 */
	public static function getTrackOrder($orderId, $forUser = "payer", $noFullInfo = true, $inboxStatus = "") {
		global $smarty;

		$order = \Model\Order::find($orderId);
		if (empty($order)) {
			return ['html' => ''];
		}

		$smarty->assign("order", $order);
		$smarty->assign('noFullInfo', $noFullInfo);
		$smarty->assign("priceFor", $forUser);
		$smarty->assign("isStageTester", \Order\Stages\OrderStageOfferManager::isTester());

		if ($forUser === UserManager::TYPE_PAYER && $inboxStatus === "new" &&
			$order->stime > (time() - Helper::ONE_DAY * 2)
		) {
			$smarty->assign("showDescription", true);
		}
		$arReturn['html'] = $smarty->fetch('track/order.tpl');
		$arReturn['orderData'] = $order->toArray();
		$arReturn["canBeStaged"] = \Order\Stages\OrderStageManager::isOrderCanBeStaged($order->source_type);
		$arReturn["customMinPrice"] = KworkManager::getCustomMinPrice($order->getLang());
		$arReturn["customMaxPrice"] = KworkManager::getCustomMaxPrice($order->getLang());
		$arReturn["stageMinPrice"] = KworkManager::getCustomMinPrice($order->getLang());
		$arReturn["stageMaxIncreaseDays"] = (int)$order->kwork->kworkCategory->max_days;
		$arReturn["stageMaxDecreaseDays"] = $order->getMaxDecreaseDays();
		$arReturn["lang"] = $order->getLang();

		return $arReturn;
	}

	public static function getStatus($kworkId) {
		global $conn;

		$query = "SELECT PID, active, feat FROM posts WHERE PID = '" . mres($kworkId) . "'";

		return $conn->getEntity($query);
	}

	/**
	 * Установить количество отрицательных и положительных отзывов у кворка
	 * @param $kworkId
	 * @return bool
	 */
	public static function setReviewCount($kworkId) {
		return true;
	}

	private static function getAdWordsShoppingXml(array $items) {
		$return = [];
		$paramsWithoutGoogleMark = ['title', 'description', 'link',];
		foreach ($items as $item) {
			$add = '<item>';
			foreach ($item as $_name => $_value) {
				$tagName = "g:{$_name}";
				if (in_array($_name, $paramsWithoutGoogleMark)) {
					$tagName = "{$_name}";
				}
				$add .= "<{$tagName}>{$_value}</{$tagName}>";
			}
			$add .= '</item>';
			$return[] = $add;
		}

		return implode("\n", $return);
	}

	/**
	 * @return array
	 */
	public static function api_getRotation() {
		global $smarty;
		$session = Session\SessionContainer::getSession();
		$page = (int)get('page');
		$page = $page < 1 ? 1 : $page;

		if ($session->get('rotation_date') < time() - Helper::ONE_HOUR) {
			$minute = date('i');
		} else {
			$minute = date('i', $session->get('rotation_date'));
		}

		$kworks = self::getWeightRotationKworks($page, $minute);

		$smarty->assign('posts', $kworks);

		return [
			'success' => $kworks ? true : false,
			'content' => $smarty->fetch('fox_bit.tpl')
		];
	}

	/**
	 * Удаление прикрепленного к кворку файла(в описании или инструкциях)
	 * @return array
	 */
	public static function api_deleteAttachment() {
		return [
			'success' => false
		];
	}

	/**
	 * Получить ротацию кворков для главной страницы пользователя
	 * @param int $page
	 * @param     $minute
	 * @param bool $isOnce подгрузка одного скрытого кворка
	 * @param array $excludeIds спискок id которые не учитывать в ввыборке
	 * @return array|bool|string
	 */
	public static function getWeightRotationKworks($page, $minute, $isOnce = false, array $excludeIds = []) {
		return false;
	}

	public static function getMoreKworkWeightRotation($count, $excludeIds, $minute) {
		global $conn;

		$query = "SELECT CATID, rotation_weight FROM categories WHERE parent > 0 AND custom_offer = 0";
		if (App::isMirror()) {
			$query .= " AND allow_mirror = 1";
		}
		if (UserManager::isHideSocialSeo()) {
			$query .= " AND CATID != " . UserManager::CATEGORY_SMM_ID;
		}
		$data = $conn->getList($query);

		$weightSum = 0;
		foreach ($data as $category) {
			$weightSum += $category->rotation_weight;
		}

		$catIds = [];
		for ($i = 1; $i <= $count; $i++) {
			$randWeight = mt_rand(0, $weightSum);
			foreach ($data as $category) {
				$randWeight -= $category->rotation_weight;
				if ($randWeight <= 0) {
					$catIds[] = $category->CATID;
					break;
				}
			}
		}

		if (empty($catIds)) {
			return [];
		}

		if (empty($kworkRotationPool)) {
			return [];
		}

		$selectedKworks = [];
		$selectedKworkIds = [];
		$catKworks = [];
		foreach ($catIds as $catId) {
			if (empty($kworkRotationPool[$catId])) {
				continue;
			}
			$catKworkIds = [];
			foreach ($kworkRotationPool[$catId] as $kwork) {
				$catKworkIds[] = $kwork['kwork_id'];
			}
			if (!empty($catKworkIds) && empty($catKworks[$catId])) {
				$sql = "SELECT SQL_CALC_FOUND_ROWS
					p.PID, 
					p.USERID, 
					p.gtitle, 
					p.youtube, 
					p.days, 
					p.price, 
					p.photo, 
					p.queueCount quecount, 
					p.category, 
					p.rating, 
					p.bookmark_count, 
					p.url, 
					p.is_package,
					c.seo, 
					c.name, 
					p.lang,
					p.bonus_text,
					p.bonus_moderate_status,
					m.username, 
					m.city_id, 
					m.toprated, 
					m.cache_rating_count_en,
					m.cache_rating_count as `userRatingCount`, 
					m.cache_rating as `userRating`
				FROM 
					posts p
					JOIN categories c ON c.CATID = p.category 
					JOIN members m ON m.USERID = p.USERID
				WHERE 
					" . StatusManager::kworkListEnable('p') . "
					AND p.PID NOT IN (" . mres(implode(',', $excludeIds)) . ")
					AND p.PID IN (" . mres(implode(',', $catKworkIds)) . ")";
				$catKworks[$catId] = $conn->Execute($sql)->getrows();
			}
			if (empty($catKworks[$catId])) {
				continue;
			}
			foreach ($catKworks[$catId] as $kwork) {
				if (!in_array($kwork['PID'], $selectedKworkIds)) {
					$selectedKworks[] = $kwork;
					$selectedKworkIds[] = $kwork['PID'];
					break;
				}
			}
		}

		return $selectedKworks;
	}

	/**
	 * Проверка доступности кворка
	 * @param array $kwork
	 * @return int
	 * @global object $actor
	 */
	public static function checkCanOrderAndView($kwork) {

		global $actor;

		if ($actor->id != $kwork['USERID'] && $kwork['feat'] == '0') {
			$result = KworkManager::CAN_NOT_ORDER;
		}

		switch ($kwork['active']) {
			case KworkManager::STATUS_MODERATION:
				$result = KworkManager::CAN_NOT_ORDER;
				break;

			case KworkManager::STATUS_PAUSE:
				break;

			case KworkManager::STATUS_REJECTED:
				if ($actor->id != $kwork['USERID']) {
					$result = KworkManager::CAN_NOT_VIEW;
				}
				break;

			case KworkManager::STATUS_DELETED:
				$result = KworkManager::CAN_NOT_VIEW;
				break;

			default:
				break;
		}

		return $result ? $result : KworkManager::CAN_NO_LIMIT;
	}

	/**
	 * Можно ли заказать кворк.
	 * Проверяет лишь базовые условия active и feat.
	 * Не проверяет условия по пользователю и т.п.
	 * @param $feat
	 * @param $active
	 * @return bool
	 */
	public static function canOrder($feat, $active) {
		if ($feat == 0 && $active == self::STATUS_CUSTOM) {
			return true;
		}

		if ($feat == 0) {
			return false;
		}

		if (!in_array($active, [KworkManager::STATUS_ACTIVE, KworkManager::STATUS_PAUSE])) {
			return false;
		}

		return true;
	}

	/**
	 * Сгенерировать url кворка (по идентификатору категории)
	 *
	 * @param int $kworkId Идентификатор кворка
	 * @param string $kworkTitle Название кворка
	 * @param int $categoryId Идентификатор категории
	 *
	 * @return string
	 */
	public static function generateUrl($kworkId, $kworkTitle, $categoryId) {
		return self::generateUrlBySeo($kworkId, $kworkTitle, 'name');
	}


	/**
	 * Сгенерировать url кворка (по алиасу категории)
	 *
	 * @param int $kworkId
	 * @param string $kworkTitle
	 * @param string $categorySeo
	 *
	 * @return string
	 */
	public static function generateUrlBySeo($kworkId, $kworkTitle, $categorySeo) {
		$title = seo_clean_titles($kworkTitle);

		return mb_strtolower('/' . $categorySeo . '/' . $kworkId . '/' . $title);
	}

	public static function getFields($kworkId, $fields) {
		global $conn;

		$query = "SELECT " . mres(implode(',', $fields)) . " FROM posts WHERE PID = '" . mres($kworkId) . "'";

		return $conn->getEntity($query);
	}

	/**
	 * Получить $field из posts
	 * @param array $pids Массив кодов (PID)
	 * @param string|array $field Поле или миссив полей
	 * @return array|false array( <PID> => <field>) или false в случае невалидных данных
	 */
	public static function getField(array $pids, $field) {
		global $conn;

		$fields = (array)$field;
		foreach ($fields as $f) {
			if (!Helper::isFieldDb($f)) {
				return false;
			}
		}

		$pids = array_filter(array_map('intval', $pids));
		if (empty($pids)) {
			return false;
		}

		if (array_search('PID', $fields) === false && array_search('*', $fields) === false) {
			array_unshift($fields, 'PID');
		}

		$sql = 'SELECT 
				' . mres(implode(',', $fields)) . ' 
			FROM posts
			WHERE PID IN (' . mres(implode(',', $pids)) . ')';
		$rows = $conn->execute($sql)->getrows();
		$return = [];
		foreach ($rows as $row) {
			if (count($row) && !is_array($field)) {
				$return[$row['PID']] = $row[$field];
			} else {
				$return[$row['PID']] = $row;
			}
		}

		return $return;
	}

	/**
	 * Получить коды кворков пользователя
	 * @param int $userId Код пользователя
	 * @param array $options Массив дополнительных параметров
	 * @return mixed pdo()->fetchAllByColumn
	 */
	public static function getIdsByUser($userId, array $options = []) {
		static $usersCache;
		if (!isset($usersCache)) {
			$usersCache = [];
		}
		if (!isset($usersCache[$userId])) {
			$sql = 'SELECT PID FROM posts WHERE USERID=:user AND active <> :active';
			$usersCache[$userId] = App::pdo()->fetchAllByColumn($sql, 0, [
				'user' => $userId,
				'active' => self::STATUS_DELETED
			]);
		}

		return $usersCache[$userId];
	}

	/**
	 * Получить массив идентификаторо активных кворков пользователя
	 * @param int $userId - идентификатор пользователя
	 * @return boolean|array - список активных кворков пользователя
	 */
	public static function getActiveKworkByUser($userId) {
		$userId = (int)$userId;
		if ($userId > 0) {
			$sql = "SELECT PID as id FROM posts WHERE USERID = :userId AND active = :statusActive";

			return App::pdo()->fetchAllByColumn($sql, 0, ['userId' => $userId, 'statusActive' => KworkManager::STATUS_ACTIVE]);
		}

		return false;
	}

	/**
	 * Проверить название кворка на дублирование
	 * @param int $userId Код пользователя
	 * @param string $title Название проверяемого кворка
	 * @param boolean|int $kworkId Код проверяемого кворка. Указывать при редактировании
	 * @return false|int Если найдена копия возвращает ID кворка, иначе false
	 */
	public static function checkTitleClone($userId, $title, $kworkId = false) {
		$userId = intval($userId);
		$titleWords = Helper::calcWordStat(Helper::explodeDescToWords($title));
		if (empty($titleWords)) {
			return false;
		}

		$restrictedStatuses = [self::STATUS_DELETED, self::STATUS_CUSTOM, self::STATUS_DRAFT];

		$sql = 'SELECT PID, gtitle 
			FROM posts 
			WHERE 
				USERID=' . $userId . ' AND 
				active NOT IN (' . implode(',', $restrictedStatuses) . ')';
		if (!empty($kworkId)) {
			$sql .= ' AND PID!=' . intval($kworkId);
		}
		$kworksTitle = App::pdo()->fetchAll($sql);
		foreach ($kworksTitle as $kworkInfo) {
			$kworkDesc = $kworkInfo['gtitle'];
			$_titleWordStat = Helper::calcWordStat(Helper::explodeDescToWords($kworkDesc));
			$hasDiff = array_diff(array_keys($titleWords), array_keys($_titleWordStat));
			if (empty($hasDiff) && count($titleWords) == count($_titleWordStat)) {
				return $kworkInfo['PID'];
			}
		}

		return false;
	}

	/**
	 * Получить список кворков для карточек
	 *
	 * @param array $kworkIds
	 * @return array
	 */
	public static function getListForCards(array $kworkIds) {
		if (!count($kworkIds)) {
			return [];
		}

		$kworksId = Helper::intArray($kworkIds);
		$params = [];

		$sql = "SELECT 
					p.PID, 
					p.USERID, 
					p.gtitle, 
					p.youtube, 
					p.days, 
					p.price,
					p.photo, 
					p.is_resizing, 
					p.rating, 
					p.url, 
					p.is_package, 
					p.lang,
					m.username, 
					m.toprated, 
					m.city_id,
					m.cache_rating_count_en,
					m.cache_rating_count AS userRatingCount, 
					m.cache_rating AS userRating,
					p.bonus_text, 
					p.bonus_moderate_status, 
					p.category
				FROM 
					posts p
					LEFT JOIN members m ON m.USERID  = p.USERID
				WHERE 
					p.PID IN (" . App::pdo()->arrayToStrParams($kworksId, $params, PDO::PARAM_INT) . ")";
		$kworkList = App::pdo()->fetchAllNameByColumn($sql, 0, $params);

		$result = [];
		foreach ($kworkIds as $id) {
			if ($kworkList[$id]) {
				$result[] = $kworkList[$id];
			}
		}

		return $result;
	}

	public static function api_getRestAuhorizedPopular() {
		global $smarty;

		$weightMinute = get("weightMinute");
		if ($weightMinute == "") {
			$weightMinute = date("i");
		}

		$kworks = KworkManager::getWeightRotationKworks(1, $weightMinute);
		$kworks = array_slice($kworks, 4, count($kworks) - 4);

		$smarty->assign("posts", $kworks);

		return [
			"result" => "success",
			"html" => $smarty->fetch("fox_bit.tpl")
		];
	}

	/**
	 * Принадлежит ли кворк пользователю
	 * @param int $userId
	 * @param int $kworkId
	 * @return boolean
	 */
	public static function isUserKwork($userId, $kworkId) {
		$userId = (int)$userId;
		$kworkId = (int)$kworkId;
		if ($userId > 0 && $kworkId > 0) {
			return App::pdo()->exist(self::TABLE_KWORKS, 'USERID = :userId AND PID = :kworkId', ['userId' => $userId, 'kworkId' => $kworkId]);
		}

		return false;
	}

	/**
	 * Функция для временного логирования состояния кворка
	 * @param int $kworkId - идентификатор кворка
	 * @param int $active - 0 или 1 в зависимости остановлен кворк или нет
	 * @return int|bool
	 */
	public static function tempKworkLog($kworkId, $active) {
		return false;
	}

	public function getLang() {
		return $this->lang;
	}

	public function setLang($lang) {
		if (!in_array($lang, [Translations::DEFAULT_LANG, Translations::EN_LANG])) {
			$lang = Translations::getLang();
		}
		$this->lang = $lang;

		return $this;
	}

	/**
	 * Кворки пользователя с общим количеством
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param int $limit Лимит
	 * @param int $offset Оффсет
	 * @param bool $showDisabled - показывать заблокированные кворки (для разблокировки)
	 * @return array ["kworks" => array, "total" => int]
	 */
	public static function getByUser(int $userId, int $limit = 0, int $offset = 0, bool $showDisabled = false) {
		$additional = "";
		if ($showDisabled) {
			$additional = "OR (A.active = 2 and A.feat = 1)";
		}
		$query = "SELECT 
						SQL_CALC_FOUND_ROWS 
						A.*,
						B.seo, 
						C.username, 
						C.USERID
					FROM 
						posts A, categories B, members C 
					WHERE 
						(" . StatusManager::kworkListEnable('A') . $additional . " ) AND
						A.category = B.CATID AND 
						A.USERID = C.USERID AND
						A.USERID = :userId AND 
						A.lang = :lang
					ORDER BY A.rating DESC";

		if ($limit > 0) {
			$query .= " LIMIT $offset, $limit";
		}
		$params = [
			"userId" => $userId,
			"lang" => Translations::getLang()
		];

		$kworks = App::pdo()->fetchAll($query, $params);

		$total = App::pdo()->foundRows();

		return ["kworks" => $kworks, "total" => $total];
	}

	/**
	 * Возвращает кворки пользователя для необходимого языка (для предложений)
	 * и информацию по пакетам к ним
	 * @param int $userId Идентификатор пользователя
	 * @param string $lang Идентификатор языка ru|en
	 * @param int $categoryId Идентификатор категории кворка
	 * @return array ["kworks" => [], "kworkPackage" => []]
	 */
	public static function getUserKworksWithPackages(int $userId, string $lang, int $categoryId = 0): array {
		$sql = "SELECT PID, gtitle, days, category, 
						price,
						(price - ctp) as workerPrice
						FROM posts 
						WHERE " . StatusManager::kworkListEnable() . " AND
						USERID = :userId AND lang = :lang";

		$params = ["userId" => $userId, "lang" => $lang];

		if ($categoryId) {
			$sql .= " AND category = :categoryId";
			$params["categoryId"] = $categoryId;
		}

		$kworks = App::pdo()->getList($sql, $params);

		if (!is_array($kworks)) {
			return ["kworks" => [], "kworkPackage" => []];
		}

		$kworkPackageIds = array_column(array_filter($kworks, function($kwork) {
			return $kwork->is_package;
		}), 'PID');

		if (empty($kworkPackageIds)) {
			return ["kworks" => array_values($kworks), "kworkPackage" => []];
		}

		$kworkPackage = [];

		$prices = PackageManager::getPackagePricesByCat(false, $lang);

		$params = [];
		$placheholders = App::pdo()->arrayToStrParams($kworkPackageIds, $params, PDO::PARAM_INT);
		$query = "SELECT id, kwork_id, price, price_ctp, type, duration FROM kwork_package WHERE kwork_id IN ($placheholders)";

		$kworkPackageData = Helper::toArray(App::pdo()->fetchAll($query, $params, PDO::FETCH_OBJ));

		foreach ($kworkPackageData as $item) {
			if (empty($item->price) || $item->price == 0) {
				// Поиск цены по категории и типу пакета
				$price = $prices[$kworks[$item->kwork_id]->category][$item->type];
				$commision = App::config("commission_percent");
				$workerPrice = $price - ($price * ($commision / 100));
			} else {
				$price = $item->price;
				$workerPrice = $price - $item->price_ctp;
			}

			$kworkPackage[$item->kwork_id][$item->type] = $item;
			$kworkPackage[$item->kwork_id][$item->type]->price = $workerPrice;
			$kworkPackage[$item->kwork_id][$item->type]->payerPrice = $price;
		}

		return ["kworks" => array_values($kworks), "kworkPackage" => $kworkPackage];
	}

	/**
	 * Получить список кворков по идентификаторам
	 * @param array $kworkIds
	 * @return array
	 */
	public static function getByIds(array $kworkIds): array {
		if (empty($kworkIds) || !is_array($kworkIds)) {
			return [];
		}
		$params = [];
		$kworkIds = App::pdo()->arrayToStrParams($kworkIds, $params, PDO::PARAM_INT);
		$sql = "SELECT
					*
				FROM
					" . self::TABLE_KWORKS . "
				WHERE
					" . self::FIELD_PID . " IN ({$kworkIds})";

		return App::pdo()->fetchAllNameByColumn($sql, self::FIELD_PID, $params);
	}

	/**
	 * Нуждается ли кворк в обновлении редактирования
	 * @param int $kworkId Идентификатор кворка
	 * @return bool
	 */
	public static function isNeedUpdate(int $kworkId): bool {
		return false;
	}

	/**
	 * Нуждается ли кворк в обновлении редактирования - "базовый метод".
	 * Можно использовать внутри цикла, предварительно получив параметры.
	 * Этот метод работает быстрее чем isNeedUpdate, т.к. в него передаются
	 * подготовленные заранее данные.
	 * @param array $attributes Массив KworkAttribute для $kworkId по которому делается проверка
	 * @param bool $haveLinksSites Имеется ли запись с данным kworkId в таблице kwork_links_sites_relations
	 * @param int $demoFileId Идентификатор записи с данным kworkId в таблице files
	 * @return bool
	 */
	public static function isNeedUpdateBase($attributes, $haveLinksSites, $demoFileId): bool {
		// Проверим нужно ли заполнение сайтов ссылок
		foreach ($attributes as $attribute) {
			if ($attribute->isKworkLinksSites()) {
				if (!$haveLinksSites) {
					return true;
				}
				break;
			}
		}

		// Проверим нужно ли загрузить демоотчет
		foreach ($attributes as $attribute) {
			if (!$attribute->isForDelete() && $attribute->isDemoFileUpload()) {
				if (empty($demoFileId)) {
					return true;
				}
				break;
			}
		}
		return false;
	}

	/**
	 * Проверка необходимости добавления языка перевода
	 * @param int $kworkId
	 * @return bool
	 */
	public static function isNeedUpdateTranslates(int $kworkId): bool {

		$kwork = \Model\Kwork::find($kworkId);
		if (!$kwork) {
			return false;
		}

		if (in_array($kwork->category, TranslateLangHelper::TRANSLATE_CATEGORIES)) {
			if ($kwork->translates->count() == 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * #6666 Проверка необходимости изменения стоимостей пакетов кворка.
	 * Стоимость пакета Эконом должна отличаться от стоимости пакета Бизнес не более чем в 5 раз.
	 *
	 * @param int $kworkId
	 *
	 * @return bool
	 */
	public static function isNeedUpdatePackagePrices(int $kworkId): bool {
		/** @var \Model\KworkPackage[] $kworkPackages */
		$kworkPackages = \Model\KworkPackage::query()
			->where(\Model\KworkPackage::FIELD_KWORK_ID, $kworkId)
			->get([
				\Model\KworkPackage::FIELD_TYPE,
				\Model\KworkPackage::FIELD_PRICE,
			]);

		list($standardPrice, $premiumPrice) = [0, 0];

		foreach ($kworkPackages as $package) {
			switch ($package->type) {
				case \Model\KworkPackage::TYPE_STANDARD:
					$standardPrice = $package->price;
					break;
				case \Model\KworkPackage::TYPE_PREMIUM:
					$premiumPrice = $package->price;
					break;
				default:
			}
		}

		// Проверка цен пакетов кворка.
		if (!$premiumPrice || !$standardPrice ||
			($premiumPrice / $standardPrice <= self::MAX_ACCEPTABLE_PACKAGE_PRICE_RATIO)
		) {
			return false;
		}

		// Определение, есть ли в кворке свободные цены.
		// Если нет, то ошибку показывать не нужно.
		$categoryId = \Model\Kwork::find($kworkId)->category;
		$isFreePrices = \CategoryManager::isPackageFreePrice($categoryId);
		if (!$isFreePrices) {
			$attributes = \Attribute\KworkAttributeManager::getByKworkId($kworkId);
			if ($attributes) {
				foreach (array_reverse($attributes) as $attribute) {
					if (!$attribute->isForDelete() && $attribute->isFreePrice()) {
						$isFreePrices = true;
						break;
					}
				}
			}
		}
		if (!$isFreePrices) {
			return false;
		}

		return true;
	}

	/**
	 * Заполнение подготовленных цен пакетных кворов кворков и определения нужно ли ставить "от " перед ценой
	 * с учетом фильтра
	 *
	 * @param array $kworks Массив кворков (по ссылке)
	 * @param float|string $priceTo Цена до (вообще float, но обрабатываются и пустые строки)
	 * @param float|string $priceFrom Цена от (вообще float, но обрабатываются и пустые строки)
	 */
	public static function fillPreparedPrices(array &$kworks, $priceTo = "", $priceFrom = "") {
		$packageKworkIds = [];
		foreach ($kworks as $key => $kwork) {
			if (!empty($kwork[self::FIELD_PID]) && !empty($kwork[self::FIELD_IS_PACKAGE])) {
				// Для пакетных кворков добавляем в массив для выборки
				$packageKworkIds[] = $kwork[self::FIELD_PID];
			}
		}
		$packagePrices = PackageManager::getPackagePricesByCat(false, \Translations::getLang());

		if (!empty($packageKworkIds)) {
			// Одним запросом получаем все пакеты для всех пакетных кворков
			$query = DB::table(KworkPackage::TABLE_NAME)->whereIn(KworkPackage::FIELD_KWORK_ID, $packageKworkIds);
			if ($priceTo > 0) {
				$query->where(KworkPackage::FIELD_PRICE, "<=", $priceTo);
			}
			if ($priceFrom > 0) {
				$query->where(KworkPackage::FIELD_PRICE, ">=", $priceFrom);
			}
			$kworksPackages = $query->get()->all();

			foreach ($kworks as $key => $kwork) {
				if (in_array($kwork[self::FIELD_PID], $packageKworkIds)) {
					$filtered = array_filter($kworksPackages, function($kworkPackage) use ($kwork) {
						return $kworkPackage->{KworkPackage::FIELD_KWORK_ID} == $kwork[KworkManager::FIELD_PID];
					});
					if (!empty($filtered)) {
						usort($filtered, function($a, $b) {
							return (float)$a->{KworkPackage::FIELD_PRICE} <=> (float)$b->{KworkPackage::FIELD_PRICE};
						});
						/**
						 * @var KworkPackage $minPricePackage Пакет с минимальной ценой из выбранных
						 */
						$minPricePackage = $filtered[0];
						$price = $minPricePackage->{KworkPackage::FIELD_PRICE};
						$type = $minPricePackage->{KworkPackage::FIELD_TYPE};
						if ((float)$price <= 0) {
							$price = $packagePrices[$kwork[KworkManager::FIELD_CATEGORY]][$type] ?? 0;
						}
						$kworks[$key][self::PACKAGE_PRICE] = $price;
						$kworks[$key][self::PACKAGE_IS_FROM] = $type != KworkPackage::TYPE_PREMIUM;
					}
				}
			}
		}
	}

	/**
	 * @param Collection $kworks
	 */
	public static function fillPreparedPricesByCollection(Collection $kworks) {
		$kworks->each(function(Kwork $kwork) {
			$price = $kwork->minPricePackage->{KworkPackage::FIELD_PRICE};
			$type = $kwork->minPricePackage->{KworkPackage::FIELD_TYPE};

			if ($price <= 0) {
				$packagePrices = \PackageManager::getPackagePricesByCategory($kwork->kworkCategory);

				$price = $packagePrices[$kwork->minPricePackage->{KworkPackage::FIELD_TYPE}];
			}

			$kwork->{self::PACKAGE_PRICE} = $price;
			$kwork->{self::PACKAGE_IS_FROM} = $type !== KworkPackage::TYPE_PREMIUM;
		});
	}

	/**
	 * Получить текущую цену кворка с учетом пакетных кворков
	 * @param $kwork
	 * @param bool $convertToRub
	 * @param string $filterPrice
	 * @return array
	 */
	public static function getCurrentKworkPrice($kwork, $convertToRub = false, $filterPrice = "") {
		$price = $kwork["price"];
		return [
			'price' => $price,
			'packageType' => "standart",
		];
	}

	/**
	 * Возвращает текстовое представление цены с "от / за" перед ценой и "руб / рублей" после.
	 * @param array $kwork
	 * @param bool $isLetter
	 * @param string $currency
	 * @return string
	 */
	public static function getKworkTextPrice(array $kwork, bool $isLetter = false, string $currency = ""): string {
		$result = "";
		if ($kwork["is_package"] == 1) {
			$result .= ($kwork["lang"] == Translations::DEFAULT_LANG ? 'от' : 'from');
		} elseif (!$isLetter) {
			$result .= ($kwork["lang"] == Translations::DEFAULT_LANG ? 'за' : 'for');
		}

		if (empty($currency)) {
			if ($kwork["lang"] == Translations::DEFAULT_LANG) {
				if ($isLetter) {
					$currency = "рублей";
				} else {
					$currency = "руб.";
				}
			} else {
				$currency = "$";
			}
		}

		if ($kwork["lang"] == Translations::DEFAULT_LANG) {
			$result .= " " . trim(round(self::getCurrentKworkPrice($kwork)["price"]) . " " . $currency);
		} else {
			$result .= " " . $currency . trim(round(self::getCurrentKworkPrice($kwork)["price"]));
		}

		return $result;
	}

	/**
	 * Обновление полей таблицы posts кворка
	 *
	 * @param int $kworkId Идентификатор кворка
	 * @param array $fields Массив полей которые нужно обновить [Имя поля => Новое значение]
	 *
	 * @return int|false Количество обновленных строк или false если ошибка запроса
	 */
	public static function update(int $kworkId, array $fields) {
		return App::pdo()->update(self::TABLE_KWORKS, $fields, self::FIELD_PID . " = :id", ["id" => $kworkId]);
	}

	/**
	 * @return bool
	 */
	public function isNeedModeration(): bool {
		return $this->needModeration;
	}

	/**
	 * @return null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param null $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return mixed
	 */
	public function getInstruction() {
		return $this->instruction;
	}

	/**
	 * @return mixed
	 */
	public function getWorkTime() {
		return $this->workTime;
	}

	/**
	 * @return mixed
	 */
	public function getCategoryId() {
		return $this->categoryId;
	}

	/**
	 * @return string
	 */
	public function getPackageType(): string {
		return $this->packageType;
	}

	/**
	 * @return mixed
	 */
	public function getFirstPhoto() {
		return $this->firstPhoto;
	}

	/**
	 * @return \Model\ExtrasModel[]
	 */
	public function getMyExtras(): array {
		return $this->myExtras;
	}

	/**
	 * @return array
	 */
	public function getChangedProperties(): array {
		return $this->changedProperties;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function deleteChangedProperties($name) {
		$key = array_search($name, $this->changedProperties);
		if (isset($this->changedProperties[$key])) {
			unset($this->changedProperties[$key]);
			return true;
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function getStandardPackage(): array {
		return $this->standardPackage;
	}

	/**
	 * @param bool $changed
	 */
	public function setChanged(bool $changed) {
		$this->changed = $changed;
	}

	/**
	 * Принимает в пост запрос набор полей для проверки
	 * @return array
	 * @throws Exception
	 */
	public static function api_checkText($data, $lang, $checkAll = false) {
		if (empty($lang)) {
			$lang = Translations::getLang();
		}
		$result = ["success" => true, "data" => []];
		return $result;
		$checkTextField = $checkBadWords = [];
		foreach ($data as $fieldId => $field) {
			if ($checkAll || in_array($field["field"], \Kwork\StopWords\KworkTextFields::getTextValidFields())) {
				$isLong = isset($field["isLong"]) && $field["isLong"];
				$checkTextField = self::checkTextField($field["string"], $field["descriptionString"], $lang, true, $isLong);
			}

			$checkBadWords = self::checkBadWords($field, $lang, true);

			$result["data"][$fieldId] = array_merge_recursive($checkTextField, $checkBadWords);

			// удаление повторных ошибок
			if (isset($result["data"][$fieldId]["mistakes"])) {
				// сортируем по возрастанию позиции в исходной строке
				usort($result["data"][$fieldId]["mistakes"], function($v1, $v2) {
					return $v1[0] == $v2[0] ? (strlen($v1[1]) <=> strlen($v2[1])) : $v1 <=> $v2;
				});
				$mistakes = [];
				/**
				 * Если найдено две ошибки в одинаковой позиции, например - одна найдена
				 * спеллчекером (короткое слово, орф-я ошибка и т.п.), а вторая найдена мат-фильтром, то
				 * в массиве останется та, которая длиннее - на фронте подчеркнется максимально длинная ошибка.
				 */
				array_walk($result["data"][$fieldId]["mistakes"], function($v, $k) use (&$mistakes) {
					$mistakes[$v[0]] = $v;
				});
				$result["data"][$fieldId]["mistakes"] = $mistakes;
			}
		}
		return $result;
	}

	/**
	 * Превращает html в обычный текст, для дальнейших проверок
	 *
	 * @param $string
	 * @return string
	 */
	public static function handleHtmlCode($string) {
		// Заменяем теги, которые могут вызывать перенос строки, на пробелы
		$string = preg_replace("/<[^>]*?(?:p|br|li|div)[^>]*?>/i", " ", $string);

		// Удаляем из текста оставшиеся теги
		$string = preg_replace("/<\/?[^>]*>/", "", $string);

		return $string;
	}

	/**
	 * Принимает переменную string с параметрами, которую
	 *  проверяет на запрещенные слова
	 *
	 * @param $field
	 * @param string $lang
	 * @return array
	 * @throws Exception
	 */
	public static function checkBadWords($field, string $lang, $rawHtml = false) {
		$string = $field["string"];
		if (!$rawHtml) {
			$string = html_entity_decode(html_entity_decode($string));
		}
		$string = self::handleHtmlCode($string);

		$fieldId = $field["field"];
		$category = $field["category"];
		$attributes = $field["attributes"];
		$noHint = isset($field["noHint"]) && $field["noHint"];

		$result = [
			"string" => null,
			"badWords" => null,
			"mistakes" => null
		];

		$badWords = \Kwork\StopWords\KworkStopWordManager::checkStopWords($string, $fieldId, $category, $attributes, $lang);
		if (count($badWords) > 0) {
			$result["badWords"] = true;
			$result['string'] = \Kwork\StopWords\KworkStopWordManager::generateHintBadWords($badWords, $noHint);
			unset($badWords["hints"]);

			$result["mistakes"] = [];
			foreach ($badWords as $badWord) {
				$result["mistakes"] = array_merge($result["mistakes"], self::findErrors("/\b(" . $badWord . ")/ui", $string, $count));
			}
		} else {
			$result = [];
		}

		return $result;
	}

	/**
	 * Принимает переменную string которую проверяет на валидность
	 * и оборачивает ошибки в тег word-error для их выделения
	 *
	 * @param string $string Значение поля для проверки
	 * @param string $compareString Значения поля с которым нужно сравнить (descriptionString например)
	 * @param string $lang Язык
	 * @param boolean $rawHtml
	 *
	 * @return array
	 */
	public static function checkTextField($string, $compareString = "", $lang = Translations::DEFAULT_LANG, $rawHtml = false, $isLong = false) {
		$maxMistakesPercent = $isLong ? self::MAX_MISTAKES_PERCENT_LONG_TEXT_FIELD : self::MAX_MISTAKES_PERCENT_SHORT_TEXT_FIELD;
		if (!$rawHtml) {
			$string = html_entity_decode(html_entity_decode($string));
		}

		$string = self::handleHtmlCode($string);

		$error = null;
		$count = 0;
		$mistakes = self::wrapWordMistakes($string, $count, $mistakesPercent, $lang);

		if ($count > 0) {
			// показываем ошибку только если кол-во ошибок больше допустимого
			if ($mistakesPercent > $maxMistakesPercent) {
				$error = "word_mistakes";
			}
		}

		if (is_null($error)) {
			$count = 0;
			$mistakes = array_merge($mistakes, self::wrapDuplicateSymbols($string, $count, false));
			if ($count > 0) {
				$error = "duplicate_symbols";

			} else {
				$mistakes = array_merge($mistakes, self::wrapBigWord($string, $count));
				if ($count > 0) {
					$error = "big_word";

				} else {
					$mistakes = array_merge($mistakes, self::wrapSmallWord($string, $count));
					if ($count > 0) {
						$error = "small_word";

					} else {
						// если передана строка compareString то определяем похожесть текстов
						if ($compareString) {
							$similarityPercent = ShingleManager::similarityPercent(ShingleManager::make($string), ShingleManager::make($compareString));
							if ($similarityPercent >= 50) {
								$error = "duplicate_description";
							}
						}
					}
				}
			}
		}

		return [
			"mistakes" => $mistakes,
			"validError" => $error,
		];
	}

	/**
	 * Ищет в строке орфографические ошибки и оборачивает их в тег word-error
	 *
	 * @param $string
	 * @param int $count
	 * @param $mistakesPercent
	 *
	 * @return array
	 */
	public static function wrapWordMistakes(string $string, &$count = 0, &$mistakesPercent, $lang = Translations::DEFAULT_LANG) {
		return [];
	}

	/**
	 * Ищет в строке ошибки по регулярному выражению и возвращает их позиции
	 *
	 * @param $regexp
	 * @param $string
	 * @param int $count
	 *
	 * @return array
	 */
	public static function findErrors(string $regexp, string $string, &$count = 0) {
		$mistakes = [];
		$matches = [];
		preg_match_all($regexp, $string, $matches, PREG_OFFSET_CAPTURE);
		foreach ($matches[0] as $v) {
			$mistakes[] = [
				mb_strlen(substr($string, 0, $v[1])),
				$v[0],
			];
			$count++;
		}
		return $mistakes;
	}

	/**
	 * Ищет в строке дубли букв и спецсимволов и возвращает в виде массива проблемных слов, дубли цифр ингорируются
	 *
	 * @param $string
	 * @param int $count
	 * @param bool $needReplace нужно ли провести замену разрешенных слов и ссылок
	 *
	 * @return array
	 */
	public static function wrapDuplicateSymbols(string $string, &$count = 0, $needReplace = false) {
		$mistakes = [];

		// выделяем слово
		$mistakes = array_merge($mistakes, self::findErrors(self::REGEXP_LETTER_DUPLICATE, $string, $count));
		// выделяем спецсимволы
		$mistakes = array_merge($mistakes, self::findErrors(self::REGEXP_SYMBOL_DUPLICATE, $string, $count));

		return $mistakes;
	}

	/**
	 * Ищет в строке слова которые превышают максимальную длину
	 *
	 * @param string $string
	 * @param int $count
	 *
	 * @return array
	 */
	public static function wrapBigWord(string $string, &$count = 0) {
		return self::findErrors(self::REGEXP_BIG_WORD, $string, $count);
	}

	/**
	 * Ищет слова бувы в которых разделены пробелом или символом (прим. П_р_и_в_е_т)
	 * Защищаемся от текста, когда пишется буква и пробел, либо буква и символ, чтобы написать какое-то запрещенного слово/текст.
	 *
	 * @param string $string
	 * @param int $count
	 *
	 * @return array
	 */
	public static function wrapSmallWord(string $string, &$count = 0) {
		return self::findErrors(self::REGEXP_SMALL_WORD, $string, $count);
	}

	/**
	 * Ищет атрибут со свободной ценой, среди выбранных атрибутов начиная с конца
	 *
	 * @return bool|int
	 */
	private function getSelectedPriceAttributeId() {
		foreach (array_reverse($this->attributes) as $attribute) {
			/** @var \Model\KworkAttribute $attribute */
			if (!$attribute->isForDelete() && $attribute->isFreePrice()) {
				return $attribute->getId();
			}
		}
		return false;
	}

	/**
	 * Выдает объем кворка в единицах хранения (минимальных)
	 *
	 * @return int
	 */
	public function getVolume() {
		return $this->volume;
	}

	/**
	 * Получить объем кворка в выбранных продавцом единицах
	 *
	 * @return int
	 */
	public function getVolumeInSelectedType() {
		return $this->convertVolumeToSelectedType($this->volume);
	}

	/**
	 * Конвертация объема в единицах храния (секундах) в выбранный в кворке тип объема (минуты, часы)
	 *
	 * @param int $volume
	 *
	 * @param \Model\VolumeType|null $volumeType Тип, в который нужно сконвертировать
	 * @return float|int
	 */
	public function convertVolumeToSelectedType($volume, \Model\VolumeType $volumeType = null) {
		if ($volume > 0) {
			$volumeType = $volumeType ?? $this->getVolumeType();
			if (!is_null($volumeType) && $volumeType->contains_value) {
				return $volume / $volumeType->contains_value;
			}
		}

		return $volume;
	}

	/**
	 * Возвращает значение минимальной стоимость заказа для кворка
	 * @return float
	 */
	public function getMinVolumePrice(): float {
		return $this->minVolumePrice;
	}

	/**
	 * Получить модель текущей категории кворка
	 * @return null|\Model\Category
	 */
	public function getCategory() {
		if ($this->categoryId) {
			if (is_null($this->category) || $this->category->CATID != $this->categoryId) {
				$this->category = \Model\Category::withoutGlobalScopes()
					->find($this->categoryId);
			}
		}

		return $this->category;
	}

	/**
	 * Получение идентификатора цифрового объема для текущей категории и классификации
	 * Использовать только после загрузки атритутов
	 *
	 * @return int|null
	 */
	private function getRequiredVolumeTypeId() {
		// Сначала попробуем получить тип числового объема из категории
		$category = $this->getCategory();
		if (!is_null($category) && $category->volume_type_id) {
			return $category->volume_type_id;
		}
		//Потом попробуем получить из атрибутов
		foreach ($this->attributes as $attribute) {
			if (!$attribute->isForDelete() && $attribute->getVolumeTypeId()) {
				return $attribute->getVolumeTypeId();
			}
		}
		return null;
	}

	/**
	 * Получить доступные типы цифрового объема кворка (идентификаторы)
	 *
	 * @return array
	 */
	private function getAllowedVolumeTypesIds(): array {
		$category = $this->getCategory();
		if (!is_null($category) && $category->volume_type_id) {
			$allowedVolumeTypes = [$category->volume_type_id];
			if ($category->additionalVolumeTypes) {
				$additionalTypesIds = $category->additionalVolumeTypes->pluck(VolumeTypeManager::F_ID)->toArray();
				$allowedVolumeTypes = array_merge($allowedVolumeTypes, $additionalTypesIds);
			}
			return $allowedVolumeTypes;
		}

		foreach ($this->attributes as $attribute) {
			if (!$attribute->isForDelete() && $attribute->getVolumeTypeId()) {
				$allowedVolumeTypes = [$attribute->getVolumeTypeId()];
				$additionalTypesIds = \VolumeType\AttributeAdditionalVolumeType::getAdditionalVolumeTypeIds($attribute->getId());
				$allowedVolumeTypes = array_merge($allowedVolumeTypes, $additionalTypesIds);
				return $allowedVolumeTypes;
			}
		}

		return [];
	}

	/**
	 * Получение модели выбранного типа числового объема с кешированием
	 *
	 * @return null|\Model\VolumeType
	 */
	public function getVolumeType() {
		if ($this->volumeTypeId) {
			if (is_null($this->volumeType) || $this->volumeType->id != $this->volumeTypeId) {
				$this->volumeType = \Model\VolumeType::find($this->volumeTypeId);
			}
		}

		return $this->volumeType;
	}

	/**
	 * Получить максимальное количество положительных отзывов
	 * @return int
	 */
	public static function getMaxGoodCommentsCount() {
		if (App::config("redis.enable")) {
			$redisValue = RedisManager::getInstance()->get(\Enum\Redis\RedisAliases::MAX_GOOD_COMMENTS_COUNT);
		}
		if (empty($redisValue)) {
			$sql = "SELECT MAX(" . self::FIELD_GOOD_COMMENTS_COUNT . ") 
				FROM " . self::TABLE_KWORKS;
			$redisValue = App::pdo()->fetchScalar($sql);

			$redisValue = round($redisValue * 1.1);

			if (App::config("redis.enable")) {
				RedisManager::getInstance()->set(\Enum\Redis\RedisAliases::MAX_GOOD_COMMENTS_COUNT, $redisValue, Helper::ONE_DAY);
			}
		}
		return $redisValue;
	}

	/**
	 * Проверка на дублирующиеся буквы с поддержкой исключений
	 *
	 * @param string $string Строка для проверки
	 *
	 * @return bool
	 */
	public static function checkWithExcludes($string): bool {
		return false;
	}

	/**
	 * Дата создания кворка в UNIXTIME
	 *
	 * @return int|null
	 */
	public function getTimeAdded() {
		return $this->time_added;
	}

	/**
	 * Поиск одного другого кворка продавца
	 * для слайдера на странице кворка
	 * для замены скрытого кворка
	 * @return array
	 * @throws SmartyException
	 */
	public static function api_getOnceOtherKworkSeller(): array {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			return [];
		}

		global $smarty;
		$excludesIds = request("excludeIds");
		$postId = request("postId");

		if (!is_array($excludesIds)) {
			$excludesIds = [];
		}

		$sql = "SELECT 
					p.USERID 
				FROM 
					posts p 
				WHERE 
					p.PID = :post_id";

		$thisKwork = App::pdo()->fetch($sql, ["post_id" => $postId]);

		$sql_ids = "";
		$params = [];
		if (count($excludesIds) > 0) {
			$sql_ids = "AND A.PID NOT IN " . App::pdo()->buildInQuery("A.PID", $excludesIds);
			$params = App::pdo()->buildInParams("A.PID", $excludesIds);
		}

		$sql = "SELECT
					A.PID, 
					A.gtitle, 
					A.gdesc, 
					A.photo, 
					A.rating, 
					A.youtube, 
					A.price, 
					A.USERID, 
					A.category, 
					A.url, 
					A.is_package, 
					A.bonus_text, 
					A.lang,
					A.bonus_moderate_status
				FROM
					posts A
				WHERE
					A.lang = :lang
					AND A.PID != :post_id
					AND A.USERID = :user_id
					AND " . StatusManager::kworkListEnable('A') . "
					" . $sql_ids . "
				ORDER BY
					A.rating desc, A.PID desc
				LIMIT 1";

		$params = array_merge($params, [
			"post_id" => $postId,
			"lang" => Translations::getLang(),
			"user_id" => $thisKwork["USERID"]
		]);

		$kwork = App::pdo()->fetchAll($sql, $params);

		self::fillIsBookmark($kwork);
		self::fillUserInfo($kwork, [
			"username",
			"USERID",
			"toprated",
			"cache_rating_count_en",
			[
				"field" => "cache_rating_count",
				"alias" => "userRatingCount"
			],
			[
				"field" => "cache_rating",
				"alias" => "userRating"
			]
		]);

		$smarty->assign("posts", $kwork);

		return [
			"result" => "success",
			"html" => $smarty->fetch("fox_bit.tpl")
		];
	}

	/**
	 * Поиск одного популярного кворка
	 * для слайдера на главной странице
	 * для замены скрытого кворка
	 * @return array
	 * @throws SmartyException
	 */
	public static function api_getOnceKworkPopular(): array {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			return [];
		}

		global $smarty;

		$excludesIds = request("excludeIds");
		if (!is_array($excludesIds)) {
			$excludesIds = [];
		} else {
			$excludesIds = array_map("intval", $excludesIds);
		}
		$weightMinute = date("i");

		$kwork = self::getWeightRotationKworks(1, $weightMinute, true, $excludesIds);

		$smarty->assign("posts", $kwork);

		return [
			"result" => "success",
			"html" => $smarty->fetch("fox_bit.tpl")
		];
	}

	/**
	 * Поиск одного избранного кворка
	 * для слайдера на главной странице
	 * для замены скрытого кворка
	 * @return array
	 * @throws SmartyException
	 */
	public static function api_getOnceKworkBookmark(): array {
		$actorId = \UserManager::getCurrentUserId();
		if (!$actorId) {
			return [];
		}
		global $smarty;

		$excludes = "";
		$excludesIds = [];
		$showedIds = request("excludeIds");
		$hiddenKworksIds = \HiddenManager::getAllHiddenIds($actorId);
		$params = [
			"user_id" => $actorId
		];

		if (is_array($hiddenKworksIds) && count($hiddenKworksIds) > 0) {
			$excludesIds = $hiddenKworksIds;
		}

		if (is_array($showedIds) && count($showedIds) > 0) {
			$excludesIds = array_unique(array_merge($excludesIds, $showedIds));
		}

		if (!empty($excludesIds)) {
			$excludes = " AND b.PID NOT IN " . App::pdo()->buildInQuery("b.PID", $excludesIds);
			$params = array_merge($params, App::pdo()->buildInParams("b.PID", $excludesIds));
		}

		$sql = "SELECT  
					p.PID, 
					p.USERID, 
					p.gtitle, 
					p.youtube, 
					p.days, 
					p.price, 
					p.photo, 
					p.queueCount quecount, 
					p.category,
					p.rating, 
					p.bookmark_count, 
					p.url, 
					p.is_package, 
					p.bonus_text, 
					p.bonus_moderate_status,
					c.seo, 
					c.name, 
					p.lang,
					m.username,
					m.city_id, 
					m.toprated, 
					m.cache_rating_count_en,
					m.cache_rating_count as `userRatingCount`, 
					m.cache_rating as `userRating`
				FROM 
					bookmarks b
					JOIN " . self::TABLE_KWORKS . " p ON p.PID = b.PID
					JOIN categories c ON c.CATID = p.category 
					JOIN members m ON m.USERID = p.USERID
				WHERE 
					b.USERID = :user_id
					{$excludes}
				LIMIT 1";

		$kwork = App::pdo()->fetchAll($sql, $params);

		$smarty->assign("posts", $kwork);

		return [
			"result" => "success",
			"html" => $smarty->fetch("fox_bit.tpl")
		];
	}

	/**
	 * Поиск одного похоожего кворка
	 * для слайдера на странице кворка
	 * для замены скрытого кворка
	 * @return array
	 * @throws SmartyException
	 */
	public static function api_getOnceSimilarKwork(): array {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			return [];
		}
		global $smarty;

		$thisKworkId = (int)request("postId");

		$showedIds = request("excludeIds");
		if (is_array($showedIds)) {
			$showedIds = array_map("intval", $showedIds);
		} else {
			$showedIds = [];
		}

		$kwork = self::getSameCategoryKworks($thisKworkId, $showedIds, 1);

		$smarty->assign("posts", $kwork);

		return [
			"result" => "success",
			"html" => $smarty->fetch("fox_bit.tpl")
		];
	}

	/**
	 * Получение статуса активности кворка
	 *
	 * @return int
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * Получение статуса показа в каталоге
	 *
	 * @return int
	 */
	public function getFeat() {
		return $this->feat;
	}

	/**
	 * Является ли пакетным с тремя пакетами
	 *
	 * @return bool
	 */
	public function isPackage() {
		return $this->packageType == "package";
	}

	/**
	 * Получение массива идентификаторов атрибутов кворка без помеченных к удалению и пользовательских
	 *
	 * @return array
	 */
	private function getAttributesIdsWithoutCustom() {
		$attributeIds = [];
		foreach ($this->attributes as $attr) {
			if (!$attr->isCustom() && !$attr->isForDelete()) {
				$attributeIds[] = $attr->getId();
			}
		}
		return $attributeIds;
	}

	/**
	 * Получение массива идентификаторов атрибутов кворка без помеченных к удалению и пользовательских
	 * вместе с идентификаторами родителей
	 *
	 * @return array
	 */
	private function getAttributesIdsWithParentIds() {
		$attributeIds = [];
		foreach ($this->attributes as $attribute) {
			if (!$attribute->isCustom() && !$attribute->isForDelete()) {
				$attributeIds[] = $attribute->getId();
				if ($attribute->getParentId()) {
					$attributeIds[] = $attribute->getParentId();
				}
			}
		}
		return $attributeIds;
	}

	/**
	 * Получение идентификаторов используемых в кворке пакетных опций
	 * (кроме пользовательских)
	 *
	 * @return array
	 */
	public function getPackageItemsIds() {
		return array_unique(array_merge(
			$this->getPackageItemsIdsFromPackage($this->standardPackage),
			$this->getPackageItemsIdsFromPackage($this->mediumPackage),
			$this->getPackageItemsIdsFromPackage($this->premiumPackage)
		));
	}

	/**
	 * Получение идентификаторов пакетных опций (кроме пользовательских) из пакета
	 *
	 * @param array $package
	 *
	 * @return array
	 */
	private function getPackageItemsIdsFromPackage($package) {
		$ids = [];

		if (!empty($package["items"]["category"])) {
			foreach ($package["items"]["category"] as $packageItem) {
				$ids[] = $packageItem["item_id"];
			}
		}

		return $ids;
	}

	/**
	 * Уменьшим счётчик закладок у кворков
	 * @param int[] $kworkIds
	 */
	public static function decrementBookmarkCount(array $kworkIds) {
		\Model\Kwork::query()
			->whereIn(self::FIELD_PID, $kworkIds)
			->where(self::FIELD_BOOKMARK_COUNT, ">", 0)
			->decrement(self::FIELD_BOOKMARK_COUNT);

		$data = \Model\Kwork::whereIn(\Model\Kwork::FIELD_PID, $kworkIds)
			->get([\Model\Kwork::FIELD_PID, \Model\Kwork::FIELD_BOOKMARK_COUNT, \Model\Kwork::FIELD_LANG, \Model\Kwork::FIELD_ACTIVE])
			->toArray();
		foreach ($data as $row) {
			if ($data->active == self::STATUS_ACTIVE && $data->feat == self::FEAT_ACTIVE) {
				KworkSearchUpdate::taskReplaceRow(
					$row[\Model\Kwork::FIELD_LANG],
					$row[\Model\Kwork::FIELD_PID]
				);
			}
		}
	}

	/**
	 * API: Предзагрузка обложки кворка
	 *
	 * @param int|null $_POST ["kwork_id"] Id кворка или черновика
	 * @param int|null $_POST ["category_id"] Id категории кворка, если kwork_id === null
	 * @param array|null $_POST ["hashes"] Хеши других изображений в кворке
	 * @param file $_FILES ["file"] Изображение
	 *
	 * @return array
	 */
	public static function api_saveFirstPhoto() {
		$result = ["success" => false];
		$userId = (int)\UserManager::getCurrentUserId();

		if (!$userId) {
			return $result;
		}

		$kworkId = (int)post("kwork_id");
		$categoryId = (int)post("category_id");
		$hashes = post("hashes") ? (array)post("hashes") : [];
		$file = $_FILES["file"];

		try {
			$hasher = ImageHasher::getInstance();
			$hash = $hasher->hash($file["tmp_name"]);
			foreach ($hashes as $otherHash) {
				$distance = $hasher->distance(Hash::fromHex($hash), Hash::fromHex($otherHash));
				if ($distance < PhotoManager::MIN_ACCEPTABLE_IMAGE_HASH_DISTANCE) {
					$result["is_similar"] = true;
					return $result;
				}
			}
		} catch (\Exception $e) {
			return $result;
		}

		if (!$kworkId) {
			if (!$categoryId) {
				return $result;
			}
			$kwork = new self();
			$kworkSaveManager = new \Helpers\KworkSaveManager(
				$kwork,
				\Symfony\Component\HttpFoundation\Request::createFromGlobals(),
				\Helpers\KworkSaveManager::MODE_ADD,
				true
			);
			if ($kworkSaveManager->save() === false) {
				return $result;
			}
			$kworkId = $kworkSaveManager->get("id");
		}
		$kwork = new KworkManager($kworkId, self::LOAD_KWORK_DATA);

		if ((int)$kwork->userId !== $userId) {
			return $result;
		}

		$imagePath = PhotoManager::preUploadMainKworkPhoto($kwork, $file["tmp_name"]);
		if ($imagePath === false) {
			return $result;
		}

		$result["success"] = true;
		$result["kwork_id"] = $kworkId;
		$result["image_path"] = $imagePath;
		$result["image_hash"] = $hash;

		return $result;
	}

	/**
	 * Установить тип портфолио кворка из категории и классификации кворка
	 * Выполнять только после установки атрирбутов кворка
	 * @return KworkManager
	 */
	public function setPortfolioType(): KworkManager {
		if (!$this->categoryId) {
			return $this;
		}
		$portfolioType = $this->getCategory()->portfolio_type;
		if (!empty($this->attributes)) {
			$attributeParents = $portfolioTypes = [];
			foreach ($this->attributes as $attribute) {
				if ($attribute->isForDelete()) {
					continue;
				}
				$attributeId = $attribute->getId();
				$attributeParents[$attributeId] = $attribute->getParentId();
				$portfolioTypes[$attributeId] = $attribute->getPortfolioType();
			}
			if (!empty($attributeParents)) {
				foreach ($attributeParents as $attributeId => $parentId) {
					if (in_array($attributeId, $attributeParents)) {
						continue;
					}
					if ($portfolioTypes[$attributeId] != AttributeManager::PORTFOLIO_TYPE_DEFAULT) {
						$portfolioType = $portfolioTypes[$attributeId];
					}
				}
			}
		}
		$this->portfolioType = $portfolioType;

		return $this;
	}

	/**
	 * Получить тип портфолио кворка
	 * @return string
	 */
	public function getPortfolioType(): string {
		if (empty($this->portfolioType)) {
			$this->setPortfolioType();
		}
		return $this->portfolioType;
	}

	/**
	 * Проверяет, доступен ли функционал портфолио для данного кворка
	 * @return bool
	 */
	public function isPortfolioAllowed(): bool {
		if (!$this->categoryId) {
			return false;
		}

		return $this->getPortfolioType() != AttributeManager::PORTFOLIO_TYPE_NONE;
	}

	/**
	 * Получить список аттрибутов кворка
	 *
	 * @return \Model\KworkAttribute[]
	 */
	public function getAttributes(): array {
		return $this->attributes;
	}

	/**
	 * Получить идентификатор кворка с наибольшим рейтингом из заданной категории
	 *
	 * @param int $categoryId идентификатор категории
	 * @return int|false идентификатор кворка или false
	 */
	public static function getMostRatedKworkInCategory($categoryId) {
		if (!$categoryId) {
			return false;
		}
		return \App::pdo()->fetchScalar("
			SELECT 
				" . self::FIELD_PID . " 
			FROM 
				" . self::TABLE_KWORKS . " 
			WHERE 
				" . self::FIELD_ACTIVE . " = :active AND 
				" . self::FIELD_FEAT . " = :feat AND 
				" . self::FIELD_CATEGORY . " = :category 
			ORDER BY rating DESC 
			LIMIT 1", [
			"active" => \KworkManager::STATUS_ACTIVE,
			"category" => $categoryId,
			"feat" => 1,
		]);
	}

	/**
	 * Получить список идентификаторов кворков, для которых доступно портфолио
	 * (внимание! при этом самих портфолио может и не быть в этих кворка)
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param int|null $categoryId Идентификатор категории, опционально
	 * @param string|null $lang Язык кворков, опционально
	 * @return array
	 */
	public static function getKworkIdsWithPortfolioAvailable($userId, $categoryId = null, $lang = null): array {
		$query = \Model\Kwork::where(\Model\Kwork::FIELD_USERID, $userId)
			->where(\Model\Kwork::FIELD_ACTIVE, "!=", self::STATUS_DRAFT);

		if ($categoryId) {
			$query->where(\Model\Kwork::FIELD_CATEGORY, $categoryId);
		}
		if (!is_null($lang)) {
			$query->where(\Model\Kwork::FIELD_LANG, $lang);
		}

		return $query->pluck(\Model\Kwork::FIELD_PID)->toArray();
	}

	/**
	 * Получение всех хешей всех обложек кворка
	 *
	 * @return array
	 */
	private function getAllCoversHashes() {
		$coversHashes = [];
		foreach ($this->getPortfoliosWithoutDeletedAndUnbinded() as $portfolio) {
			if ($portfolio->getNewCover() && $portfolio->getNewCover()->hash) {
				$coversHashes[] = $portfolio->getNewCover()->hash;
			} else {
				$coversHashes[] = $portfolio->cover_hash;
			}
		}

		return array_filter(array_merge($coversHashes, $this->getOrdersPortfolioCoversHashes()));
	}

	/**
	 * Получение хешей обложек портфолио привязанных к заказам
	 *
	 * @return array
	 */
	public function getOrdersPortfolioCoversHashes() {
		if ($this->id) {
			return Portfolio::where(Portfolio::FIELD_KWORK_ID, $this->id)
				->whereNotNull(Portfolio::FIELD_ORDER_ID)
				->pluck(Portfolio::FIELD_COVER_HASH)
				->filter()
				->toArray();
		}
		return [];
	}

	/**
	 * Получение массива url видео портфолио привязанных к заказам по этому кворку
	 *
	 * @return array
	 */
	public function getOrdersVideosUrls() {
		if ($this->id) {
			$ordersPortfolios = $this->getOrdersPortfoliosIds();
			if ($ordersPortfolios) {
				return PortfolioVideo::whereIn(PortfolioImage::FIELD_PORTFOLIO_ID, $ordersPortfolios)
					->pluck(PortfolioVideo::FIELD_URL)
					->filter()
					->toArray();
			}
		}
		return [];
	}

	/**
	 * Получение списка идентификаторов портфолио привязанных к заказам по этому кворку
	 *
	 * @return array
	 */
	public function getOrdersPortfoliosIds() {
		// Статическое кеширование
		static $portfolioIds;
		if (is_null($portfolioIds)) {
			if ($this->id) {
				$portfolioIds = Portfolio::where(Portfolio::FIELD_KWORK_ID, $this->id)
					->whereNotNull(Portfolio::FIELD_ORDER_ID)
					->pluck(Portfolio::FIELD_ID)
					->toArray();
			} else {
				$portfolioIds = [];
			}
		}
		return $portfolioIds;
	}

	/**
	 * Получение портфолио без удаляемых и отвязваемых
	 *
	 * @return \Model\Portfolio[]
	 */
	private function getPortfoliosWithoutDeletedAndUnbinded() {
		return array_filter($this->portfolio, function(Portfolio $portfolio) {
			return !$portfolio->isForDelete() && !$portfolio->forUnbind;
		});
	}

	/**
	 * Останавливает все кворки пользователя с логированием
	 * @param int $userId
	 */
	public static function stopUserKworks(int $userId) {
		$kworkIds = \Model\Kwork::query()
			->where(\Model\Kwork::FIELD_USERID, $userId)
			->where(\Model\Kwork::FIELD_FEAT, \Model\Kwork::FEAT_ACTIVE)
			->pluck(\Model\Kwork::FIELD_PID)
			->toArray();

		if (!empty($kworkIds)) {
			// останавливаем кворки
			\Model\Kwork::whereIn(\Model\Kwork::FIELD_PID, $kworkIds)->update([
				\Model\Kwork::FIELD_FEAT => \Model\Kwork::FEAT_INACTIVE
			]);

			// логируем
			$insertData = [];
			foreach ($kworkIds as $kworkId) {
				$insertData[] = [
					"kwork_id" => $kworkId,
					self::FIELD_FEAT => self::FEAT_INACTIVE
				];
			}
			DB::table(self::TABLE_KWORKS_LOG)->insert($insertData);
		}
	}

	/**
	 * Получение Builder для поиска видимых портфолио с учетом категории
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param int|null $categoryId Идентификатор категории
	 * @param string|null $lang Язык, если нужна фильтрация по языку ru|en
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|Portfolio
	 */
	public static function getCommonPortfolioBuider(int $userId, $categoryId = null, $lang = null) {
		$kworks = self::getKworkIdsWithPortfolioAvailable($userId, $categoryId, $lang);

		$builder = Portfolio::where(Portfolio::FIELD_USER_ID, $userId)
			->where(Portfolio::FIELD_STATUS, "<>", Portfolio::STATUS_DELETE)
			->where(function(\Illuminate\Database\Eloquent\Builder $query) use ($kworks, $categoryId, $lang) {
				$query->whereIn(Portfolio::FIELD_KWORK_ID, $kworks);
				if ($categoryId) {
					$query->orWhere(Portfolio::FIELD_CATEGORY_ID, $categoryId);
				} else {
					$query->orWhereNull(Portfolio::FIELD_KWORK_ID);
					if ($lang) {
						$query->whereIn(Portfolio::FIELD_CATEGORY_ID, CategoryManager::getLangCategoryIds($lang));
					}
				}
			});

		return $builder;
	}

	/**
	 * Установить пользовательский тип объёма
	 * @param int $customVolumeTypeId
	 * @return $this
	 */
	public function setCustomVolumeType($customVolumeTypeId) {
		if ($customVolumeTypeId != $this->getVolumeType()->id) {
			$allowedVolumeTypesIds = $this->getAllowedVolumeTypesIds();
			if (in_array($customVolumeTypeId, $allowedVolumeTypesIds)) {
				$this->customVolumeType = \Model\VolumeType::find($customVolumeTypeId);
			}
		}
		return $this;
	}

	/**
	 * Получить объем кворка в выбранных покупателем единицах
	 * @param float $volume Исходный объём
	 * @return int
	 */
	public function getVolumeInCustomType($volume) {
		if (!empty($this->customVolumeType)) {
			$volumeType = $this->getVolumeType();
			$quotient = $divider = 1;
			if ($this->customVolumeType->contains_value > 0) {
				$quotient = $this->customVolumeType->contains_value;
			}
			if ($volumeType->contains_value > 0) {
				$divider = $volumeType->contains_value;
			}
			$volume = $volume * ($quotient / $divider);
		}
		return $volume;
	}

	/**
	 * Получить объем кворка в выбранных в кворке единицах
	 * @param float $volume Исходный объём
	 * @return int
	 */
	public function getVolumeInKworkType($volume) {
		if (!empty($this->customVolumeType)) {
			$volumeType = $this->getVolumeType();
			$quotient = $divider = 1;
			if ($this->customVolumeType->contains_value > 0) {
				$quotient = $this->customVolumeType->contains_value;
			}
			if ($volumeType->contains_value > 0) {
				$divider = $volumeType->contains_value;
			}
			$volume = $volume * $divider / $quotient;
		}
		return $volume;
	}

	/**
	 * Получить используемый тип объёма
	 * @return \Model\VolumeType|null
	 */
	public function getUsedVolumeType() {
		if (!empty($this->customVolumeType)) {
			return $this->customVolumeType;
		} else {
			return $this->getVolumeType();
		}
	}

	/**
	 * Получить цену кворка для указанного объём
	 * @param float $volume Объём
	 * @param int $basePrice Цена базового объёма. Если не задана, берется актуальная цена кворка
	 * @return float|int
	 */
	public function getVolumedPrice($volume, $basePrice = 0) {
		if (empty($basePrice)) {
			$basePrice = $this->getPrice();
		}
		return \OrderVolumeManager::getVolumedPrice($basePrice, $this->getVolumeInSelectedType(), $volume, $this->getLang());
	}

	/**
	 * Устанавливает уведомления связанные с кворками для вывода, под хедером на странице
	 */
	public static function setEvents(): void {
		if (CustomOptionKworkPortfolio::haveMy() && !CustomOptionHidePortfolioLimit::isHide()) {
			EventManager::set("increase_orders_limit_by_portfolio");
		}
	}

	/**
	 * Если открыта страница кворка, то возвращает его ID, иначе возвращает 0
	 *
	 * @return int
	 */
	public static function getCurrentPageKworkId(): int {
		$result = null;
		$currentRequest = Router::getRequestStack()->getCurrentRequest();
		if ($currentRequest) {
			$result = $currentRequest->get("kworkId");
		}

		return (int)$result;
	}
}
