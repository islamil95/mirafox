<?php

namespace Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OrderManager;
use Translations;
use WantManager;

/**
 * Запрос на услуги
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int id Идентификатор
 * @property int user_id Идентификатор пользователя
 * @property string name Название
 * @property string desc Описание
 * @property int category_id дентификатор категории
 * @property int kwork_count Количество предложений
 * @property int order_count Количество заказов предложений
 * @property string status Статус
 * @property string date_create Дата создания
 * @property string date_confirm Дата первого подтверждения модератором
 * @property string date_expire Дата окончания
 * @property string date_reject Дата отклонения
 * @property string date_active Дата перехода в активный статус
 * @property int email_flag Флаг группировки предложений в письмах
 * @property string offer_letter_date Максимальная дата создания предложений, по которым было отправлено уведомление
 * @property int last_want_moder_id Идентификатор последней модерации
 * @property string date_moder Дата последней модерации
 * @property int need_postmoder Нужна ли постмодерация
 * @property string lang Язык
 * @property float price_limit Лимит цены предложений
 * @property-read string alt_status Виртуальное поле альтернативного статуса
 *
 * Связанные модели
 *
 * @property-read \Model\User $user Модель пользователя создателя (покупателя)
 * @property-read \Model\Category $category Модель категории
 * @property-read \Illuminate\Database\Eloquent\Collection|\Model\Offer[] $offers Предложения на запрос услуг
 * @property-read \Illuminate\Database\Eloquent\Collection|\Model\Order[] $orders Коллекция заказов запросов.
 *
 * Локальные скоупы
 * @method static Builder|static visible() Получение видимых запросов
 * @method static Builder|static active() Получение запросов в статусе "Активнй"
 * @method static Builder|static byLang($lang) Получение запросов по указанному языку
 * @method static Builder|static withoutLowHired() Исключить проекты от покупателей с долей найма до 5%.
 */
class Want extends Model {

	// Имя таблицы
	const TABLE_NAME = "want";

	/**
	 * Идентификатор
	 */
	const FIELD_ID = "id";

	/**
	 * Идентификатор категории
	 */
	const FIELD_CATEGORY_ID = "category_id";

	/**
	 * Статус
	 */
	const FIELD_STATUS = "status";

	/**
	 * Идентификатор создателя
	 */
	const FIELD_USER_ID = "user_id";

	/**
	 * Флаг постмодерации
	 */
	const FIELD_NEED_POSTMODER = "need_postmoder";

	/**
	 * Описание
	 */
	const FIELD_DESCRIPTION = "desc";

	/**
	 * Заголовок
	 */
	const FIELD_NAME = "name";

	/**
	 * Язык
	 */
	const FIELD_LANG = "lang";

	/**
	 * Дата активации запроса
	 */
	const FIELD_DATE_ACTIVE = "date_active";

	/**
	 * Дата подтверждения запроса
	 */
	const FIELD_DATE_CONFIRM = "date_confirm";

	/**
	 * Дата отклонения запроса
	 */
	const FIELD_DATE_REJECT = "date_reject";

	/**
	 * Дата истечения размещения
	 */
	const FIELD_DATE_EXPIRE = "date_expire";

	/**
	 * Лимит цены предложений
	 */
	const FIELD_PRICE_LIMIT = "price_limit";

	/**
	 * Количество предложений на запрос услуг
	 */
	const FIELD_KWORK_COUNT = "kwork_count";

	/**
	 * Количество заказов предложений
	 */
	const FIELD_ORDER_COUNT = "order_count";

	/**
	 * Дата создания запроса
	 */
	const FIELD_DATE_CREATE = "date_create";

	/**
	 * Максимальная дата создания предложений, по которым было отправлено уведомление
	 */
	const FIELD_OFFER_LETTER_DATE = "offer_letter_date";

	/**
	 * Идентификатор последней модерации
	 */
	const FIELD_LAST_WANT_MODER_ID = "last_want_moder_id";

	/**
	 * Дата последней модерации
	 */
	const FIELD_DATE_MODER = "date_moder";

	/**
	 * Просмотры от пользователей делавших заказы в течение полу года
	 */
	const FIELD_VIEWS = "views";

	/**
	 * Все просмотры
	 */
	const FIELD_VIEWS_DIRTY = "views_dirty";

	/**
	 * @deprecated
	 * Флаг группировки предложений в письмах
	 */
	const FIELD_EMAIL_FLAG = "email_flag";

	// Имя таблицы для модели
	protected $table = self::TABLE_NAME;

	// Ключ для модели
	protected $primaryKey = self::FIELD_ID;

	// Отключить timestamps
	public $timestamps = false;

	/** @var array Новые предложения с момента прошлого просмотра покупателем, наполняется вручную */
	public $newOffers = [];

	// Альтернативные статусы (вычисляются на лету, в базе не хранятся)
	// "Собираем отклики" - статус новый или активный + не выбран ни один исполнитель
	const ALT_STATUS_WAITING_FOR_PROPOSALS = "waiting_for_proposals";
	const ALT_STATUS_WAITING_FOR_PROPOSALS_NEW = "waiting_for_proposals_new";
	// "Выбрать исполнителя" - статус остановлен + прошла дата окончания + есть хотя бы
	// один отклик + не выбран ни один исполнитель
	const ALT_STATUS_CHOOSING_CONTRACTOR = "choosing_contractor";
	// "Выбран(о) Х исполнитель(я,ей)" = статус новый или активный или остановлен + 
	// выбран хотя бы один исполнитель
	const ALT_STATUS_CONTRACTOR_CHOSEN = "contractor_chosen";
	// "Остановлен" = статус остановлен + нет ни одного отклика
	const ALT_STATUS_STOPPED = "stopped";
	// "Отклонен модератором" = статус отклонен
	const ALT_STATUS_REJECTED = "rejected";
	// "Не определен" = все остальные запросы (если в какой-то момент появятся запросы, 
	// не соответствующие ни одному из перечисленных выше условий)
	const ALT_STATUS_UNDEFINED = "undefined";

	const STATUS_NEW = "new";
	const STATUS_ACTIVE = "active";
	const STATUS_CANCEL = "cancel";
	const STATUS_STOP = "stop";
	//Пользовательская остановка запроса
	const STATUS_USER_STOP = "user_stop";
	const STATUS_DELETE = "delete";
	const STATUS_ARCHIVED = "archived";

	/**
	 * Связь с пользователем создателем (покупателем)
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, \WantManager::F_USER_ID, User::FIELD_USERID);
	}

	/**
	 * Связь с категорией запроса
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function category() {
		return $this->belongsTo(Category::class, \WantManager::F_CATEGORY_ID, \CategoryManager::F_CATEGORY_ID)
			->withoutGlobalScopes();
	}

	/**
	 * Заказы
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function orders() {
		return $this->hasMany(Order::class, Order::FIELD_PROJECT_ID);
	}

	/**
	 * Кол-во исполнителей (берутся все исполнители заказов, статус которых
	 * выше, чем "заявка создана")
	 * @return int
	 */
	public function getContractorsCountAttribute() {
		return $this->orders()
			->where("status", "<>", OrderManager::STATUS_NEW)
			->groupBy("worker_id")
			->get()
			->count();
	}

	/**
	 * Альтернативный статус
	 * @return String - self::ALT_STATUS_*
	 */
	public function getAltStatusAttribute() {

		$sourceTypes = [OrderManager::SOURCE_WANT, OrderManager::SOURCE_WANT_PRIVATE];
		$canceledOrders = $this->orders
			->where(Order::FIELD_STATUS, "=", OrderManager::STATUS_CANCEL)
			->whereIn(Order::FIELD_SOURCE_TYPE, $sourceTypes)
			->count();

		$inProgressOrders = $this->orders
			->whereIn(Order::FIELD_STATUS, [OrderManager::STATUS_INPROGRESS, OrderManager::STATUS_CHECK, OrderManager::STATUS_ARBITRAGE, OrderManager::STATUS_UNPAID])
			->whereIn(Order::FIELD_SOURCE_TYPE, $sourceTypes)
			->count();

		// Альтернативные статусы (вычисляются на лету, в базе не хранятся)
		if ($this->status == WantManager::STATUS_CANCEL) {
			// "Отклонен модератором" = статус отклонен
			return self::ALT_STATUS_REJECTED;
		} elseif ((in_array($this->status, [Want::STATUS_NEW, Want::STATUS_ACTIVE])) && $this->order_count == 0) {
			// "Собираем отклики" - статус новый или активный + не выбран ни один исполнитель
			if (empty($this->date_expire) || $this->date_expire < Carbon::now()) {
				return self::ALT_STATUS_WAITING_FOR_PROPOSALS_NEW;
			} else {
				return self::ALT_STATUS_WAITING_FOR_PROPOSALS;
			}
		} elseif (($this->status == WantManager::STATUS_STOP && $this->kwork_count == 0) || $this->status == Want::STATUS_USER_STOP) {
			// "Остановлен" = Проект был остановлен покупателем и исполнитель не выбран. Либо срок размещения проекта на бирже истек, но не получено ни одного предложения.
			return self::ALT_STATUS_STOPPED;
		} elseif ($this->order_count > 0 && $inProgressOrders > 0) {
			// "Выбран(о) Х исполнитель(я,ей)" = статус новый или активный или остановлен +
			// выбран хотя бы один исполнитель
			return self::ALT_STATUS_CONTRACTOR_CHOSEN;
		} elseif (($this->status == WantManager::STATUS_STOP && $this->kwork_count > 0) || $this->order_count == $canceledOrders) {
			// Если проект останавливается системой по окончании срока размещения и по нему есть предложения
			// Либо исполнители были выбраны, но все заказы были отменены.
			return self::ALT_STATUS_CHOOSING_CONTRACTOR;
		} else {
			// "Не определен" = все остальные запросы (если в какой-то момент появятся запросы,
			// не соответствующие ни одному из перечисленных выше условий)
			return self::ALT_STATUS_UNDEFINED;
		}
	}

	/**
	 * Локализованное описание локализованного статуса для отображения пользователю
	 * @return String
	 */
	public function getAltStatusDescriptionAttribute() {

		switch ($this->alt_status) {
			case self::ALT_STATUS_WAITING_FOR_PROPOSALS:
				return Translations::t("сбор откликов до %s", \Helper::date($this->date_expire, "j M Y, H:i") );
				break;
			case self::ALT_STATUS_CHOOSING_CONTRACTOR:
				return Translations::t("выбрать исполнителя");
				break;
			case self::ALT_STATUS_CONTRACTOR_CHOSEN:
				return Translations::tn("выбран %s исполнитель",  $this->contractors_count, $this->contractors_count);
				break;
			case self::ALT_STATUS_STOPPED:
				return Translations::t("остановлен");
				break;
			case self::ALT_STATUS_REJECTED:
				return Translations::t("отклонен модератором");
				break;
			default:
				return Translations::t("статус не определен");
				break;
		}
	}

	public function getAltStatusHint() {
		$wantStatuses = array(
			Want::ALT_STATUS_WAITING_FOR_PROPOSALS => array(
				"color" => "green",
				"title" => "Сбор предложений",
				"tooltip" => "Проект опубликован на бирже до %s и сейчас доступен тысячам фрилансеров. Некоторые из них оставят вам свои предложения. Вы можете выбрать исполнителя в любой момент даже до окончания публикации на бирже.",
			),
			Want::ALT_STATUS_WAITING_FOR_PROPOSALS_NEW => array(
				"color" => "green",
				"title" => "Сбор предложений",
				"tooltip" => "Проект опубликован на бирже и сейчас доступен тысячам фрилансеров. Некоторые из них оставят вам свои предложения. Вы можете выбрать исполнителя в любой момент даже до окончания публикации на бирже.",
			),
			Want::ALT_STATUS_STOPPED => array(
				"color" => "orange",
				"title" => "На паузе",
				"tooltip" => "Проект остановлен и недоступен фрилансерам на бирже. Вы можете выбрать исполнителя, если получили предложения, или повторно разместить проект на бирже.",
			),
			Want::ALT_STATUS_REJECTED => array(
				"color" => "orange",
				"title" => "Требует доработки",
				"tooltip" => "Проект проверен модератором и отправлен вам на доработку со следующим комментарием: %s",
			),
			Want::ALT_STATUS_CHOOSING_CONTRACTOR => array(
				"color" => "green_link",
				"title" => "Выбрать исполнителя",
				"tooltip" => "",
			),
			Want::ALT_STATUS_CONTRACTOR_CHOSEN => array(
				"color" => "blue",
				"title" => "В работе",
				"tooltip" => "Исполнитель выбран. При этом в любой момент вы можете выбрать еще одного или нескольких исполнителей, чтобы одну и ту же задачу выполняли несколько фрилансеров.",
			),
		);
		return $wantStatuses[$this->getAltStatusAttribute()];
	}

	/**
	 * Видимые запросы - запрос не удален и не отменен, не остаовлен, то прошло менее
	 * WantManager::PAYER_HIDE_STOPPED_THRESHOLD_DAYS дней с даты создания запроса
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeVisible($query) {
		return $query->whereNotIn(Want::FIELD_STATUS, [Want::STATUS_DELETE, Want::STATUS_ARCHIVED])
			->orderByDesc(Want::FIELD_ID);
	}


	/**
	 * Активные запросы
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeActive(Builder $query) {
		return $query->where(\WantManager::F_STATUS, \WantManager::STATUS_ACTIVE);
	}

	/**
	 * Все запросы по языку
	 * @param Builder $query
	 * @param string $lang
	 * @return Builder
	 */
	public function scopeByLang(Builder $query, $lang) {
		return $query->where(\WantManager::F_LANG, $lang);
	}

	/**
	 * Связь с предложениями
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function offers() {
		return $this->hasMany(Offer::class, Offer::FIELD_WANT_ID);
	}

	/**
	 * Подсчитать дату истечения
	 *
	 * @return int
	 */
	public function calcExpireDate() {
		return \WantManager::getExpireDate($this->lang, (float)$this->price_limit);
	}

	/**
	 * Подсчитать дату отправки письма об отстствующих предложениях
	 *
	 * @return int
	 */
	public function calcWithoutOffersLetterDate() {
		return \WantManager::getWithoutOffersDate($this->lang, (float)$this->price_limit);
	}

	/**
	 * Является ли запрос на услуги архивным
	 *
	 * @return bool
	 */
	public function isArchive() {
		return $this->status == Want::STATUS_ARCHIVED;
	}

	/**
	 * Был ли запрос на услуги удален
	 *
	 * @return bool
	 */
	public function isDeleted(): bool {
		return $this->status === self::STATUS_DELETE;
	}

	/**
	 * Получить сумму прямых и непрямых заказов связанных с данным запросом и находящихся в активном статусе (1,2,4,5,6)
	 * $withDone - учитывать ли завершенные заказы
	 * @return int
	 */
	public function getSumOrderCount($withDone = true) {
		$statuses = [OrderManager::STATUS_INPROGRESS, OrderManager::STATUS_CHECK, OrderManager::STATUS_ARBITRAGE, OrderManager::STATUS_UNPAID];
		if ($withDone) {
			$statuses[] = OrderManager::STATUS_DONE;
		}
		return $this->orders
			->whereIn(Order::FIELD_STATUS, $statuses)
			->count();
	}

}