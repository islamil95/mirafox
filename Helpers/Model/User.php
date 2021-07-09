<?php

namespace Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use WantManager;

/**
 * Пользователь
 *
 * @property int USERID
 * @property string username
 * @property string role
 * @property string type
 * @property string status
 * @property string fullname
 * @property string phone
 * @property bool phone_verified
 * @property double funds
 * @property double bfunds бонусный счет
 * @property double bill_funds безналичный счет
 * @property double card_funds карточный счет
 * @property double card_funds_transfer_amount сумма на карточном счету, которую нужно перевести на основной
 * @property string email
 * @property bool verified
 * @property string password
 * @property int addtime
 * @property string vk_open_id
 * @property string fb_open_id
 * @property string description
 * @property int lastlogin
 * @property string profilepicture
 * @property string avatar_type
 * @property string remember_me_key
 * @property string remember_me_time
 * @property string ip
 * @property string lip
 * @property string allow_ip
 * @property int country_id
 * @property int city_id
 * @property bool toprated
 * @property int level
 * @property int live_date
 * @property string webmoneyId
 * @property string qiwiId
 * @property string cardId
 * @property int smtp_error количество недоставленных писем
 * @property int isEvent
 * @property string refType
 * @property int is_orders
 * @property int order_count Кол-во заказов покупателя
 * @property int email_flag
 * @property int order_done_count
 * @property int cache_rating_count
 * @property int cache_rating
 * @property int cache_service
 * @property bool is_want_confirm
 * @property string cover
 * @property string portfolio_type Решение о публикации результатов старых заказов в портфолио
 * @property int inbox_archive_count Количество переписок в архиве
 * @property int unread_dialog_count Общее количество непрочитанных диалогов
 * @property int notify_unread_count
 * @property bool show_poll_notify Показывать ли уведомления на опрос
 * @property bool message_sound Флаг, включены ли звуковые уведомления о новых сообщениях
 * @property bool smm_access
 * @property int notify_count
 * @property string kwork_allow_status
 * @property string lang
 * @property int timezone
 * @property int timezone_id
 * @property int warning_inbox_count
 * @property bool red_notify
 * @property bool hide_en_kworks
 * @property bool disable_en_kworks
 * @property bool is_available_at_weekends - Принимает ли продавец заказы в выходные
 * @property string fullnameen
 * @property string descriptionen
 * @property int abuse_block_level
 * @property int last_abuse_date
 * @property string worker_status Статус продавца
 * @property int worker_status_switch_all При включении статуса «Принимаю заказы» активировать все кворки или только те, которые были активны до включения статуса «Занят»
 * @property int catalog_view_type - Тип отображения каталога при последнем посещении
 * @property int cache_rating_count_en - Количество английских отзывов
 * @property int last_viewed_category_id Идентификатор последней просмотренной категорий второго уровня
 * @property int is_subscribe_special Статус подписки на специальные письма
 * @property int is_subscribe_marketing Статус подписки на маркетинговые письма
 * @property bool is_more_payer Является ли больше покупателем чем продавцом
 * @property bool is_project_store Проект Магазин - флаг указывающий что пользователь работает на основном сайте kwork
 * @property bool is_project_exchange Проект Биржа - флаг указывающий что пользователь работает поддомене connect
 *
 * Связанные модели
 *
 * @property-read Collection|Order[] workerOrders Заказы в качестве исполнителя
 * @property-read Collection|Want[] wants Запросы пользователя
 *
 * @mixin \EloquentTypeHinting
 *
 * Локальные скоупы
 * @method static Builder|static notDeleted() Не удалённые пользователи
 */
class User extends Model {

	/**
	 * Основная таблица пользователей
	 */
	const TABLE_NAME = "members";

	/**
	 * Идентификатор пользователя
	 */
	const FIELD_USERID = "USERID";

	/**
	 * Средства пользователя доступные для вывода
	 */
	const FIELD_FUNDS = "funds";

	/**
	 * Бонусные средства
	 */
	const FIELD_BFUNDS = "bfunds";

	/**
	 * Средства по безналичному расчету
	 */
	const FIELD_BILL_FUNDS = "bill_funds";

	/**
	 * Средства заведенные с карты
	 */
	const FIELD_CARD_FUNDS = "card_funds";
	const FIELD_CARD_FUNDS_TRANSFER_AMOUNT = "card_funds_transfer_amount";

	/**
	 * Пароль в хешированном виде
	 */
	const FIELD_PASSWORD = "password";

	/**
	 * Логин пользователя в системе
	 */
	const FIELD_USERNAME = "username";

	/**
	 * Имя пользователя в системе
	 */
	const FIELD_FULLNAME = "fullname";

	/**
	 * Имя пользователя EN в системе
	 */
	const FIELD_FULLNAME_EN = "fullnameen";

	/**
	 * Закодированная строка email
	 */
	const FIELD_EMAIL = "email";

	/**
	 * Уровень времени отправки накопительных писем
	 */
	const FIELD_EMAIL_FLAG = "email_flag";

	/**
	 * Верифицирован ли пользователь по email
	 */
	const FIELD_VERIFIED = "verified";

	/**
	 * Идентификатор киви
	 */
	const FIELD_QIWIID = "qiwiId";

	/**
	 * Идентификатор вебмани
	 */
	const FIELD_WEBMONEYID = 'webmoneyId';

	/**
	 * Номер телефона
	 */
	const FIELD_PHONE = 'phone';

	/**
	 * Верефицирован ли номер телефона
	 */
	const FIELD_PHONE_VERIFIED = 'phone_verified';

	/**
	 * Количество непрочитанных уведомлений
	 */
	const FIELD_NOTIFY_UNREAD_COUNT =  "notify_unread_count";

	/**
	 * Статус пользователя в системе. members.status
	 */
	const FIELD_STATUS = "status";

	/**
	 * Роль пользователя в системе. members.role
	 */
	const FIELD_ROLE = "role";

	/**
	 * Уровень пользователя
	 */
	const FIELD_LEVEL = "level";

	/**
	 * Количество выполнивших как продавец заказов
	 */
	const FIELD_ORDER_DONE_COUNT = "order_done_count";
	/**
	 * Кол-во заказов покупателя
	 */
	const FIELD_ORDER_COUNT = "order_count";
	/**
	 * Продавец
	 */
	const TYPE_WORKER = 'worker';

	/**
	 * Покупатель
	 */
	const TYPE_PAYER = 'payer';

	/**
	 * Роль пользователя "Пользователь"
	 */
	const ROLE_USER = "user";

	/**
	 * Роль пользователя "Модератор"
	 */
	const ROLE_MODER = "moder";

	/**
	 * Роль пользователя "Модератор английского кворка"
	 */
	const ROLE_MODER_EN = "moder_en";

	/**
	 * Роль пользователя "Администратор"
	 */
	const ROLE_ADMIN = "admin";

	/**
	 * Время создания пользователя.
	 */
	const FIELD_ADDTIME = "addtime";

	/**
	 * Поле означающее активен ли штраф кворков у пользователя
	 */
	const FIELD_IS_ABUSE_ACTIVE = "is_abuse_active";

	/**
	 * Ip алрес пользователя при регистрации
	 */
	const FIELD_IP = "ip";
	/**
	 * Ip алрес пользователя при последнем входе на сайт
	 */
	const FIELD_LIP = "lip";

	/*
	 * Количество переписок в архиве
	 */
	const FIELD_INBOX_ARCHIVE_COUNT = "inbox_archive_count";

	/**
	 * Поле, обозначающее, заблокированы кворки администратором или нет
	 */
	const FIELD_KWORK_ALLOW_STATUS = "kwork_allow_status";
	const KWORK_ALLOW_STATUS_ALLOW = "allow";
	const KWORK_ALLOW_STATUS_DENY = "deny";

	const PAYMENT_QIWI = "qiwi";
	const PAYMENT_WM = "webmoney";
	const PAYMENT_CARD = "card";
	const PAYMENT_SOLARCARD = "solarcard";

	/**
	 * Время последней разблокировки кворков
	 */
	const FIELD_LAST_ABUSE_DATE = "last_abuse_date";

	/**
	 * Поле означающее есть ли уведомление для пользователя
	 */
	const FIELD_IS_EVENT = "isEvent";

	/**
	 * Язык пользователя
	 */
	const FIELD_LANG = "lang";

	/**
	 * Id временной зоны
	 */
	const FIELD_TIMEZONE_ID = "timezone_id";

	/**
	 * Поле таблицы. Количество ошибок при отпрвке писем
	 */
	const FIELD_SMTP_ERROR = "smtp_error";

	/**
	 * Уровень блокировки для подсчета кол-ва дней для разблокировки кворков
	 */
	const FIELD_ABUSE_BLOCK_LEVEL = "abuse_block_level";

	const FIELD_VK_OPEN_ID = "vk_open_id";
	const FIELD_FB_OPEN_ID = "fb_open_id";
	const FIELD_TYPE = "type";

	/**
	 * Аватар пользователяв
	 */
	const FIELD_PROFILEPICTURE = "profilepicture";

	/**
	 * Рейтинг ответственности пользователя умноженный на UserManager::FIELD_SERVICE_MULTIPLIER
	 */
	const FIELD_SERVICE = "cache_service";

	/**
	 * Статус подписки на специальные письма
	 */
	const FIELD_IS_SUBSCRIBE_SPECIAL = "is_subscribe_special";

	/**
	 * Статус подписки на маркетинговые письма
	 */
	const FIELD_IS_SUBSCRIBE_MARKETING = "is_subscribe_marketing";

	/**
	 * Проект Магазин - флаг указывающий что пользователь работает на основном сайте kwork
	 */
	const FIELD_IS_PROJECT_STORE = "is_project_store";

	/**
	 * Проект Биржа - флаг указывающий что пользователь работает поддомене connect
	 */
	const FIELD_IS_PROJECT_EXCHANGE = "is_project_exchange";

	/**
	 * Дата последней активности пользователя
	 */
	const FIELD_LIVE_DATE = "live_date";

	const USER_STATUS_NOT_CONFIRMED = "new";

	const USER_STATUS_ACTIVE = "active";

	const USER_STATUS_BLOCKED = "blocked";

	const USER_STATUS_DELETED = "delete";

	/**
	 * Дефолтная картинка профиля
	 */
	const PROFILE_PICTURE_DEFAULT = "noprofilepicture.gif";

	/**
	 * Тип аватара
	 */
	const FIELD_AVATAR_TYPE = "avatar_type";
	/**
	 * отсутствует (noprofilepicture.gif)
	 */
	const AVATAR_TYPE_NONE = "none";

	/**
	 * маленький (100х100)
	 */
	const AVATAR_TYPE_BIG = "big";

	/**
	 * маленький (100х100), на некоторых страницах (в профиле, редактировании профиля)
	 * пользователю показываем дефолтный аватар
	 */
	const AVATAR_TYPE_CHUNK = "chunk";

	/**
	 * большой (200х200)
	 */
	const AVATAR_TYPE_LARGE = "large";

	/**
	 * Константы ролей пользователей
	 */
	const F_ROLE_USER = "user";
	const F_ROLE_MODER = "moder";
	const F_ROLE_ADMIN = "admin";

	/**
	 * Константы размеров изображений пользователей
	 */
	const AVATAR_SIZE_BIG = "big";
	const AVATAR_SIZE_SMALL = "small";
	const AVATAR_SIZE_LARGE = "large";
	const AVATAR_SIZE_MEDIUM = "medium";
	const AVATAR_SIZE_ORIGINAL = "orig";
	const AVATAR_SIZE_MEDIUM_R = "medium_r";

	/**
	 * Статус продавца
	 */
	const FIELD_WORKER_STATUS = "worker_status";

	/**
	 * Константы статусов продавцов
	 */
	const WORKER_STATUS_NONE = "none";
	const WORKER_STATUS_FREE = "free";
	const WORKER_STATUS_BUSY = "busy";

	/**
	 * При включении статуса «Принимаю заказы» активировать все кворки
	 * или только те, которые были активны до включения статуса «Занят»
	 */
	const FIELD_WORKER_STATUS_SWITCH_ALL = "worker_status_switch_all";

	/**
	 * Тип портфолио
	 */
	const FIELD_PORTFOLIO_TYPE = "portfolio_type";

	const FIELD_COUNTRY_ID = "country_id";
	const FIELD_CITY_ID = "city_id";

	/**
	 * Принимает ли продавец заказы в выходные
	 */
	const FIELD_IS_AVAILABLE_AT_WEEKENDS = "is_available_at_weekends";

	/**
	 * Продавец принимает заказы в выходные
	 */
	const IS_AVAILABLE_AT_WEEKENDS = 1;

	/**
	 * Продавец НЕ принимает заказы в выходные
	 */
	const IS_NOT_AVAILABLE_AT_WEEKENDS = 0;

	/**
	 * Поле "О себе" на русском
	 */
	const FIELD_DESCRIPTION = "description";

	/**
	 * Поле "О себе" на английском
	 */
	const FIELD_DESCRIPTION_EN = "descriptionen";

	/**
	 * Отключена ли работа с англиской версией
	 */
	const FIELD_DISABLE_EN_KWORKS = "disable_en_kworks";

	/**
	 * Были ли у этого продавца заказы
	 */
	const FIELD_IS_ORDERS = "is_orders";

	/**
	 * Тип отображения каталога при последнем посещении
	 */
	const FIELD_CATALOG_VIEW_TYPE = "catalog_view_type";

	/**
	 * Тип просмотра каталога для таба "Кворки"
	 */
	const CATALOG_VIEW_KWORKS = 0;
	/**
	 * Тип просмотра каталога для таба "Работы"
	 */
	const CATALOG_VIEW_PORTFOLIO = 1;

	/**
	 * Флаг нужно ли пользователю начислять реферальное вознаграждение за приглашенных им рефералов
	 */
	const FILED_REF_TYPE = "refType";

	/**
	 * Идентификатор последней просмотренной категорий второго уровня
	 */
	const FIELD_LAST_VIEWED_CATEGORY_ID = "last_viewed_category_id";

	/**
	 * Является ли больше покупателем чем продавцом
	 */
	const FIELD_IS_MORE_PAYER = "is_more_payer";

	/**
	 * Есть ли у пользователя хотябы один прошедший модерацию проект(want)
	 */
	const FIELD_IS_WANT_CONFIRM = "is_want_confirm";

	/**
	 * Имя таблицы
	 */
	protected $table = self::TABLE_NAME;

	/**
	 * Первичный ключ
	 */
	protected $primaryKey = self::FIELD_USERID;

	/**
	 * Отключаем встроенную обработку created_at, updated_at
	 */
	public $timestamps = false;

	/**
	 * Получить URL на большой аватар
	 *
	 * @return string url
	 */
	public function getBigProfilePictureUrl():string {
		return userBigPicture($this->profilepicture);
	}

	/**
	 * Получить URL на средний аватар
	 *
	 * @return string url
	 */
	public function getMediumProfilePictureUrl():string {
		return userMediumPicture($this->profilepicture);
	}

	/**
	 * Получить URL на обложку профиля
	 *
	 * @return string url
	 */
	public function getCoverUrl():string {
		return "";
	}

	/**
	 * Получить время с прошлого online
	 *
	 * @return int время в секундах
	 */
	public function getLastOnline() {
		return time() - $this->live_date;
	}

	/**
	 * Получить имя пользователя в зависимости от локали
	 *
	 * @return string имя
	 */
	public function getTranslatedFullname():string {
		if (\Translations::isDefaultLang()) {
			return $this->fullname;
		}
		return $this->fullnameen;
	}

	/**
	 * Доступна ли аналитика для пользователя
	 *
	 * @return bool
	 */
	public function isAnalyticsEnabled():bool {
		return $this->order_done_count >= 3;
	}

	/**
	 * Заблокирована ли аналитика для пользователя
	 *
	 * @return bool
	 */
	public function isAnalyticsDisabled(): bool {
		return ! $this->isAnalyticsEnabled();
	}

	/**
	 * Пользователь с русской локалью
	 *
	 * @return bool
	 */
	public function isRu():bool {
		return $this->lang == \Translations::DEFAULT_LANG;
	}

	/**
	 * Пользователь с английской локалью
	 *
	 * @return bool
	 */
	public function isNotRu():bool {
		return ! $this->isRu();
	}

	/**
	 * Получить имя пользователя
	 *
	 * @return string имя пользователя
	 */
	public function getLogin(): string {
		if (empty($this->getTranslatedFullname())) {
			return $this->username;
		}
		return $this->getTranslatedFullname();
	}

	/**
	 * Баланс пользователя
	 *
	 * @return float
	 */
	public function getTotalFunds(): float {
		return $this->funds
			+ $this->bfunds
			+ $this->bill_funds
			+ $this->card_funds;
	}

	/**
	 * Получение незашифрованного email
	 *
	 * @return string
	 */
	public function getUnencriptedEmail() {
		return \Crypto::decodeString($this->email);
	}

	/**
	 * Запросы пользователя
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function wants() {
		return $this->hasMany(Want::class, WantManager::F_USER_ID, self::FIELD_USERID);
	}
	/**
	 * Заказы созданные в качестве исполнителя
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function workerOrders() {
		return $this->hasMany(Order::class, Order::FIELD_WORKER_ID, self::FIELD_USERID);
	}

	/**
	 * Проверка существования пользователя, по первичному ключу.
	 * @param $id
	 * @return bool
	 */
	public static function isExistsByPK($id) {
		return self::query()
			->whereKey($id)
			->exists();
	}

	/**
	 * Все пользователи, не в статусе "Удалён"
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeNotDeleted(Builder $query): Builder {
		return $query->where(self::FIELD_STATUS, '<>', \UserManager::USER_STATUS_DELETED);
	}

	/**
	 * Обновить значение members.is_more_payer для заданного массива $userIds
	 * @param int $isMorePayer
	 * @param array $userIds
	 */
	public static function updateIsMorePayer($isMorePayer, array $userIds) {
		User::whereKey($userIds)
			->update([User::FIELD_IS_MORE_PAYER => $isMorePayer]);
	}

	/**
	 * Кворки пользователя
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function kworks() {
		return $this->hasMany(Kwork::class, Kwork::FIELD_USERID, self::FIELD_USERID);
	}

	/**
	 * Возвращает true если статус пользователя "Заблокирован"
	 *
	 * @return bool
	 */
	public function isStatusBlocked(): bool {
		return $this->status == self::USER_STATUS_BLOCKED;
	}
}
