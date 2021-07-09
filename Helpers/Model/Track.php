<?php


namespace Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Model\Inbox\Inbox;
use Model\Inbox\InboxData;
use Model\KB\KBArticle;
use Model\KB\Scopes\CurrentLangScope;
use Model\OrderStages\TrackStage;
use Track\Factory\TrackViewFactory;
use Track\Type;
use Track\View\EmptyView;
use UserManager;

/**
 * Трек в заказе
 *
 * @mixin \EloquentTypeHinting
 * @property int MID
 * @property int user_id
 * @property int OID
 * @property string message
 * @property string type
 * @property string status
 * @property int date_create
 * @property int date_update
 * @property string reason_type
 * @property string prev_reason_type
 * @property string reply_type
 * @property int support_id
 * @property bool unread
 * @property bool cron_worker_unread Прочитал ли трек продавец (для треков, генерируемых кронами)
 * @property int|null inbox_id Идентификатор сообщения (если трек создан на основе сообщения)
 * @property int|null quote_id Идентификатор цитируемого сообщения
 *
 * Связанные модели
 *
 * @property Order order
 * @property-read Tips tips
 * @property-read Track|null quote Цитируемое сообщение
 * @property-read User|null author Автор
 */
class Track extends Model {

	const TABLE_NAME = "track";

	const FIELD_ID = "MID";
	const FIELD_USER_ID = "user_id";
	const FIELD_ORDER_ID = "OID";
	const FIELD_MESSAGE = "message";
	const FIELD_TYPE = "type";
	const FIELD_STATUS = "status";
	const FIELD_DATE_CREATE = "date_create";
	const FIELD_DATE_UPDATE = "date_update";
	const FIELD_REASON_TYPE = "reason_type";
	const FIELD_PREV_REASON_TYPE = "prev_reason_type";
	const FIELD_REPLY_TYPE = "reply_type";
	const FIELD_SUPPORT_ID = "support_id";
	const FIELD_UNREAD = "unread";
	const FIELD_INBOX_ID = "inbox_id";

	/**
	 * Идентификатор цитируемого сообщения
	 */
	const FIELD_QUOTE_ID = "quote_id";

	/**
	 * Прочитал ли трек продавец (для треков, генерируемых кронами)
	 */
	const FIELD_CRON_WORKER_UNREAD = "cron_worker_unread";

	protected $table = self::TABLE_NAME;

	protected $primaryKey = self::FIELD_ID;

	public $timestamps = false;

	private $hiddenConversation = "";
	private $hide = false;
	private $skipAdditional = false;

	/**
	 * @var bool Является ли трек первой доработкой (нужно для показа продавцу сообщения)
	 */
	private $isFirstRework = false;

	/**
	 * @var bool Является ли трек (сообщение продавца) превышающим по количеству среднее по категории
	 */
	private $isMessageThreshold = false;

	public function isNew():bool {
		return $this->status == \TrackManager::STATUS_NEW;
	}

	public function isNotNew():bool {
		return ! $this->isNew();
	}

	public function isDone():bool {
		return $this->status == \TrackManager::STATUS_DONE;
	}

	public function isNotDone():bool {
		return ! $this->isDone();
	}

	public function isClose():bool {
		return $this->status == \TrackManager::STATUS_CLOSE;
	}

	public function isNotClose():bool {
		return ! $this->isClose();
	}

	public function isCancel():bool {
		return $this->status == \TrackManager::STATUS_CANCEL;
	}

	public function isNotCancel():bool {
		return ! $this->isCancel();
	}

	public function getHiddenConversation() {
		return $this->hiddenConversation;
	}

	public function setHiddenConversation(string $hiddenConversation) {
		$this->hiddenConversation = $hiddenConversation;
		return $this;
	}

	public function getHide():bool {
		return $this->hide;
	}

	public function setHide(bool $hide) {
		$this->hide = $hide;
		return $this;
	}

	public function getSkipAdditional():bool {
		return $this->skipAdditional;
	}

	public function setSkipAdditional(bool $skipAdditional) {
		$this->skipAdditional = $skipAdditional;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isFirstRework(): bool {
		return $this->isFirstRework;
	}

	/**
	 * @param bool $isFirstRework
	 */
	public function setIsFirstRework(bool $isFirstRework) {
		$this->isFirstRework = $isFirstRework;
	}

	/**
	 * @return bool
	 */
	public function isMessageThreshold(): bool {
		return $this->isMessageThreshold;
	}

	/**
	 * @param bool $isMessageThreshold
	 */
	public function setIsMessageThreshold(bool $isMessageThreshold) {
		$this->isMessageThreshold = $isMessageThreshold;
	}


	/**
	 * Заказ к которому относится трек
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function order() {
		return $this->belongsTo(Order::class, self::FIELD_ORDER_ID, Order::FIELD_OID);
	}

	public function getMessageAttribute($value) {
		return replace_full_urls($value);
	}

	public function getTypeAttribute($value) {
		if ($this->prev_reason_type) {
			$trackType = \TrackManager::getCancelReason($this->prev_reason_type)["track_status"];
			if ($trackType == "payer_inprogress_cancel_request") {
				return "worker_inprogress_cancel_confirm";
			} elseif ($trackType == "worker_inprogress_cancel_request") {
				return "payer_inprogress_cancel_confirm";
			} elseif ($trackType) {
				return $trackType;
			}
		}
		return $value;
	}

	public function getReasonTypeAttribute($value) {
		if ($this->prev_reason_type) {
			return $this->prev_reason_type;
		}
		return $value;
	}

	public function isArbiter() {
		return $this->user_id == \App::config("kwork.user_id");
	}

	public function isModer() {
		return $this->user_id == \App::config("kwork.moder_id");
	}

	public function date_create_unix() {
		return strtotime($this->date_create);
	}

	public function getCancelReason() {
		if ($this->prev_reason_type) {
			return \TrackManager::getCancelReason($this->prev_reason_type);
		}
		return \TrackManager::getCancelReason($this->reason_type);
	}

	/**
	 * Получения общей стоимости опций трека для покупателя
	 *
	 * @return float
	 */
	public function getExtrasSum() {
		$sum = $this->extras->sum(function(OrderExtra $item) {
			return $item->buyerPrice();
		});

		if ($this->upgradePackageExtra) {
			$sum += $this->upgradePackageExtra->payer_price;
		}

		return $sum;
	}

	public function extras() {

		return $this->hasManyThrough(
			OrderExtra::class,
			TrackExtra::class,
			TrackExtra::FIELD_TRACK_ID,
			OrderExtra::FIELD_ID,
			self::FIELD_ID,
			TrackExtra::FIELD_ORDER_EXTRA_ID
		);
	}

	public function tips() {
		return $this->hasOne(Tips::class, Tips::F_TRACK_ID, self::FIELD_ID);
	}

	/**
	 * Получить кол-во дней до автоотмены при запросе отмены от покупателя
	 * @return bool|string
	 */
	public function getInprogressCancelAutoDays() {
		$order = $this->order;
		if (!$order) {
			return false;
		}
		if ($this->type == Type::PAYER_INPROGRESS_CANCEL_REQUEST && $order->isCancelAvailableForPayer()) {
			$workerMessages = self::where(self::FIELD_ORDER_ID, $order->OID)
				->where(self::FIELD_TYPE, Type::TEXT)
				->where(self::FIELD_USER_ID, $order->worker_id)
				->first();
			if (!$workerMessages && $order->in_work == \OrderManager::STATE_NOT_IN_WORK) {
				// Если в заказе не было сообщений от продавца и он не был взят в работу, автоотмена через 1 день
				return \Translations::t("1 дня");
			} else {
				// Иначе автоотмена через 2 дня
				return \Translations::t("2 дней");
			}
		}
		return false;
	}

	/**
	 * Доступен ли трек для редактирования пользователем
	 *
	 * @param int|null $userId Идентификатор пользователя
	 * @param array $allowedTypes Доступные для редактирования типы треков
	 * @return bool
	 */
	public function isEditable(int $userId = null, array $allowedTypes = [Type::TEXT, Type::TEXT_FIRST]) : bool {
		// авторизован ли пользователь
		if (!$userId) {
			return false;
		}

		// автор ли он
		if (!$this->isAuthor($userId)) {
			return false;
		}

		// текстового ли типа трек
		if (!in_array($this->type, $allowedTypes)) {
			return false;
		}

		// прочитан ли трек получателем
		if (!$this->unread) {
			return false;
		}

		// прошло ли больше времени с момента создания, чем разрешено
		$editablePeriod = \App::config("track.editable_period");
		if ($editablePeriod && ($this->date_create_unix() < \Carbon\Carbon::now()->subHours($editablePeriod)->timestamp)) {
			return false;
		}

		return true;
	}

	/**
	 * Получить секунды с момента создания трека
	 * @return int
	 */
	public function getTimeSinceCreate() {
		return time() - strtotime($this->date_create);
	}

	/**
	 * Доступен ли трек для удаления пользователем
	 *
	 * @param int|null $userId Идентификатор пользователя
	 * @return bool
	 */
	public function isRemovable(int $userId = null) : bool {
		// авторизован ли пользователь
		if (!$userId) {
			return false;
		}

		// автор ли он
		if (!$this->isAuthor($userId)) {
			return false;
		}

		// текстового ли типа трек
		if (!in_array($this->type, Type::getTextTypes())) {
			return false;
		}

		// младше 2-х минут - можно удалять независимо от прочитанности
		if (time() - strtotime($this->date_create) < \Helper::ONE_MINUTE * 2) {
			return true;
		}

		// прочитан ли трек получателем
		if (!$this->unread) {
			return false;
		}

		// прошло ли больше времени с момента создания, чем разрешено
		$editablePeriod = \App::config("track.editable_period");
		if ($editablePeriod && ($this->date_create_unix() < \Carbon\Carbon::now()->subHours($editablePeriod)->timestamp)) {
			return false;
		}

		return true;
	}

	/**
	 * Экранирование html в message
	 *
	 * @param  string  $value - текст сообщения
	 * @return void
	 */
	public function setMessageAttribute($value)
	{
		$this->attributes["message"] = htmlspecialchars($value, ENT_QUOTES);
	}

	/**
	 * Должно ли в треке отображатся сообщение "Отмена заказа повлияла/не повлила на ваш рейтинг"
	 *
	 * @return bool
	 */
	public function isCancelRatingMessage() {
		return in_array($this->type, Type::getCancelTypesForRatingMessage());
	}

	/**
	 * Получение типа причины отмены заказа
	 *
	 * @return int Константа TrackManager::CANCEL_REASON_*
	 */
	public function getCancelReasonMode() {
		return \TrackManager::getCancelReasonMode([
			"type" => $this->type,
			"reason_type" => $this->reason_type,
			"reply_type" => $this->reply_type,
			"in_work" => $this->order->in_work,
			"late" => $this->order->late(),
		]);
	}

	/**
	 * Является ли причина отмены заказа нормальной
	 *
	 * @return bool
	 */
	public function isCancelReasonModeNormal() {
		return $this->getCancelReasonMode() === \TrackManager::CANCEL_REASON_NORMAL;
	}

	/**
	 * Получение текста "Отмена заказа повлияла/не повлила на ваш рейтинг"
	 *
	 * @param bool $forWorker
	 *
	 * @return string
	 */
	public function getCancelRatingMessage(bool $forWorker = false) {
		return \TrackManager::cancelRatingMessage($this->getCancelReasonMode(), $forWorker, $this->order->deadline, $this->order->hasPaidStages());
	}

	/**
	 * Является ли пользователь автором
	 * @param int $userId Id пользователя
	 * @return bool
	 */
	public function isAuthor(?int $userId) {
		return $this->user_id == $userId;
	}

	/**
	 * Можно ли удалить трек через виртуальную авторизацию
	 * @return bool
	 */
	public function isRemovableByVirtual() {
		$session = \Session\SessionContainer::getSession();
		return \UserManager::isVirtual() &&
			$session->notEmpty("ADMINID") &&
			is_numeric($session->get("ADMINID")) &&
			in_array($this->type, Type::getTextTypes());
	}

	/**
	 * Цитируемое сообщение
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function quote() {
		return $this->hasOne(self::class, self::FIELD_ID, self::FIELD_QUOTE_ID);
	}

	/**
	 * Автор трека
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function author() {
		return $this->belongsTo(User::class, self::FIELD_USER_ID, User::FIELD_USERID);
	}

	/**
	 * Выдать данные для фронтенда
	 * @param $order Order Заказ, к которому относится трек (для оптимизации, чтобы не запрашивать снова)
	 *
	 * @return array|null
	 */
	public function getFrontendData($order = null) {
		$trackView = TrackViewFactory::getInstance()->getView($this, $order);
		if ($trackView instanceof EmptyView) {
			return null;
		}

		$userId = UserManager::getCurrentUserId();
		$data = [
			"id" => intval($this->MID),
			"author" => [
				"USERID" => $this->user_id,
				"username" => $this->author->username,
			],
			"editable" => $this->isEditable($userId),
			"removable" => $this->isRemovable($userId),
			"unread" => boolval($this->unread),
			"getHiddenConversation" => $this->getHiddenConversation(),
			"isHiddenConversation" => !empty($this->getHiddenConversation()),
			"type" => $this->type,
			"created_day" => \Carbon\Carbon::parse($this->date_create)->locale('ru')->translatedFormat('j F'),
			"time" => strtotime($this->date_create),
			"updateTime" => strtotime($this->date_update),
			"message" => $this->message,
		];

		$data["html"] = $trackView->render();
		$data = array_merge($data, $trackView->getFrontendData());
		return $data;
	}
}