<?php

use Core\DB\DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Core\Exception\SimpleJsonException;
use Order\Stages\OrderStageOfferManager;
use Session\SessionContainer;
use VirusTotal\{VirusTotalCheckManager, VirusTotalQueueManager};

use Model\Notification\Notification;
use Model\OrderStages\OrderStage;
use Model\{Want, WantLog, WantModer, WantView, Offer, Order, UserData, File};
use Model\VirusTotal\{VirusTotalCheckModel, VirusTotalListItemModel};

class WantManager {

	/**
	 * Основная таблица с запросами
	 */
	const TABLE_NAME = "want";
	const TABLE_WANT_VIEW = "want_view";
	const F_CATEGORY_ID = "category_id";
	const F_ID = "id";
	const F_STATUS = "status";
	const F_USER_ID = "user_id";
	const F_NEED_POSTMODER = "need_postmoder";
	const F_DESCRIPTION = "desc";
	const F_NAME = "name";
	const F_LANG = "lang";

	/**
	 * Дата начала учета просмотров по новому. Для более старых проектов не показывать просмотры. #6734
	 */
	const DATE_START_SHOW_VIEWS = "2019-02-07 00:00:00";
	/**
	 * Дата подтверждения запроса
	 */
	const FIELD_DATE_CONFIRM = "date_confirm";

	/**
	 * Дата истечения размещения
	 */
	const FIELD_DATE_EXPIRE = "date_expire";

	/**
	 * Лимит цены предложений
	 */
	const FIELD_PRICE_LIMIT = "price_limit";

	/**
	 * Количество заказов предложений
	 */
	const FIELD_ORDER_COUNT = "order_count";

	/**
	 * Дата создания запроса
	 */
	const FIELD_DATE_CREATE = "date_create";

	/**
	 * Количество предложений на запрос услуг
	 */
	const FIELD_KWORK_COUNT = "kwork_count";

	/**
	 * Таблица с данными по модерации запросов
	 */
	const TABLE_MODER = WantModer::TABLE_NAME;
	/*
	 * Константы типы модерации
	 */
	const MODER_TYPE_NONE = 'none';
	const MODER_TYPE_PREMODER = 'premoder';
	const MODER_TYPE_POSTMODER = 'postmoder';
	const MODER_TYPE_REMODER = 'remoder'; // перемодерация
	const MODER_TYPE_AUTO = "auto"; //Ставится при автоматической модерацией системой
	const STATUS_NEW = 'new';
	const STATUS_ACTIVE = 'active';
	const STATUS_CANCEL = 'cancel';
	const STATUS_STOP = 'stop';
	const STATUS_DELETE = 'delete';

	const MAX_ALLOW_DESC_SIMILARITY = 80; //Максимальная похожесть описания запроса на описание других запросов пользователя
	const MAX_ALLOW_WANT_COUNT_PER_DAY = 30; //Максимальное количество запросов от пользователя в день

	/**
	 * Максимальный лимит цены запроса в рублях
	 */
	const MAX_PRICE_LIMIT_RU = 500000;

	/**
	 * Максимальный лимит цены запроса в долларах
	 */
	const MAX_PRICE_LIMIT_EN = 10000;

	/**
	 * Срок через который скрывать остановленные запросы в списке покупателя
	 */
	const PAYER_HIDE_STOPPED_THRESHOLD_DAYS = 30;
	const PAYER_HIDE_STOPPED_THRESHOLD = self::PAYER_HIDE_STOPPED_THRESHOLD_DAYS * Helper::ONE_DAY;

	/**
	 * Интервал за который учитывать остановленные запросы в количестве показываемом в меню шапки напротив пункта "Биржа"
	 */
	const HEADER_COUNT_STOP_THRESHOLD = 14 * Helper::ONE_DAY;

	/**
	 * Статичная часть названия кеша с кол-вом новых запросов за сутки
	 */
	const CASH_NEW_WANTS_COUNT_BY_DAY = "new_wants_count_by_day_";

	/**
	 * Количество проектов, выводимых на главной
	 */
	const WORKER_WANTS_ON_INDEX_COUNT = 7;

	/**
	 * Количество проектов, за которые рассчитывать процент найма
	 */
	const USER_HIRE_PERCENT_WANTS_LIMIT = 30;

	/** @var int Минимальный порог кол-ва размещенных запросов на услуги для отображения процента найма */
	const WANTS_COUNT_SHOW_THRESHOLD = 3;

	/** @var int Минимальный порог процента найма для отображения проекта на главной странице, при условии
	 * кол-ва проектов, больше или равно WANTS_COUNT_SHOW_THRESHOLD
	 */
	const WANTS_HIRED_PERCENT_ON_INDEX_THRESHOLD = 5;

	/**
	 *  Имя input с передаваемыми файлами
	 */
	const FILES_INPUT_NAME = "want_files";

	/**
	 * Константы фильтров на странице прооектов для использования на фронте
	 */
	const
		FILTERS_BY_KWORKS_RU = [
			[
				"id" => 0,
				"name" => "До 5",
				"boundaries" => [
					"from" => null,
					"to" => 5,
				]
			],
			[
				"id" => 1,
				"name" => "От 5 до 10",
				"boundaries" => [
					"from" => 5,
					"to" => 10,
				]
			],
			[
				"id" => 2,
				"name" => "От 10 до 15",
				"boundaries" => [
					"from" => 10,
					"to" => 15,
				]
			],
			[
				"id" => 3,
				"name" => "От 15 до 20",
				"boundaries" => [
				"from" => 15,
				"to" => 20,
				],
			],
			[
				"id" => 4,
				"name" => "От 20",
				"boundaries" => [
					"from" => 20,
					"to" => null,
				],
			]
		],
		FILTERS_BY_KWORKS_EN = [
			[
				"id" => 0,
				"name" => "To 5",
				"boundaries" => [
					"from" => null,
					"to" => 5,
				]
			],
			[
				"id" => 1,
				"name" => "From 5 to 10",
				"boundaries" => [
					"from" => 5,
					"to" => 10,
				]
			],
			[
				"id" => 2,
				"name" => "From 10 to 15",
				"boundaries" => [
					"from" => 10,
					"to" => 15,
				]
			],
			[
				"id" => 3,
				"name" => "From 15 to 20",
				"boundaries" => [
					"from" => 15,
					"to" => 20,
				],
			],
			[
				"id" => 4,
				"name" => "From 20",
				"boundaries" => [
					"from" => 20,
					"to" => null,
				],
			]
		],
		FILTERS_BY_BUDGET_RU = [
			[
				"id" => 0,
				"name" => "До 1 000 <span class=\"rouble\">Р</span>",
				"boundaries" => [
					"from" => null,
					"to" => 1000,
				]
			],
			[
				"id" => 1,
				"name" => "От 1 000 <span class=\"rouble\">Р</span> до 3 000 <span class=\"rouble\">Р</span>",
				"boundaries" => [
					"from" => 1000,
					"to" => 3000,
				]
			],
			[
				"id" => 2,
				"name" => "От 3 000 <span class=\"rouble\">Р</span> до 10 000 <span class=\"rouble\">Р</span>",
				"boundaries" => [
					"from" => 3000,
					"to" => 10000,
				]
			],
			[
				"id" => 3,
				"name" => "От 10 000 <span class=\"rouble\">Р</span> до 30 000 <span class=\"rouble\">Р</span>",
				"boundaries" => [
					"from" => 10000,
					"to" => 30000,
				]
			],
			[
				"id" => 4,
				"name" => "От 30 000 <span class=\"rouble\">Р</span>",
				"boundaries" => [
					"from" => 30000,
					"to" => null,
				],
			],
		],
		FILTERS_BY_BUDGET_EN = [
			[
				"id" => 0,
				"name" => "From $25",
				"boundaries" => [
					"from" => null,
					"to" => 25,
				]
			],
			[
				"id" => 1,
				"name" => "From $25 to $50",
				"boundaries" => [
					"from" => 25,
					"to" => 50,
				]
			],
			[
				"id" => 2,
				"name" => "From $50 to $250",
				"boundaries" => [
					"from" => 50,
					"to" => 250,
				]
			],
			[
				"id" => 3,
				"name" => "From $250 to $500",
				"boundaries" => [
					"from" => 250,
					"to" => 500,
				]
			],
			[
				"id" => 4,
				"name" => "From $500",
				"boundaries" => [
					"from" => 500,
					"to" => null,
				],
			],
		];
	
	
	/**
	 * Автоматическая остановка проекта
	 *
	 * @param int $wantId идентификатор проекта
	 * @param bool $sendMail информирвать ли пользователя
	 * @param bool $isAuto вызвана ли остановка автоматически
	 */
	public static function setStop(int $wantId, bool $sendMail = false, bool $isAuto = false) {
		global $conn;

		/** @var Want $want */
		$want = Want::query()
			->with("offers")
			->where([
				self::F_ID => $wantId,
				self::F_STATUS => self::STATUS_ACTIVE,
			])->first();
		if (!$want) {
			return;
		}

		self::clearViews($want->id);

		$wantStatus = self::STATUS_STOP;
		$conn->Execute("UPDATE " . self::TABLE_NAME . " SET status = '{$wantStatus}'  WHERE id='" . mres($want->id) . "'");

	}

	/**
	 * Рестарт размещения запроса на услуги
	 *
	 * @param int $wantId
	 *
	 * @return bool
	 */
	public static function restart($wantId) {
		$currentUser = UserManager::getCurrentUser();

		$want = Want::where(self::F_ID, $wantId)
			->where(self::F_USER_ID, $currentUser->id)
			->first();

		if (is_null($want)) {
			return false;
		}

		// Если запрос был ранее подтвержден или если у пользователя есть подтвержденный запрос то статус станет active
		$moderType = "";
		$dateExpire = null;
		$setActive = false;
		if ($want->status == Want::STATUS_ARCHIVED) {
			if (($want->date_confirm < $want->date_reject) || (empty($want->date_confirm) && !empty($want->date_reject))) {
				$want->status = self::STATUS_CANCEL;
			} elseif (empty($want->date_confirm) && empty($want->date_reject)) {
				$want->status = self::STATUS_NEW;
			} else {
				$setActive = true;
				$want->date_confirm = Helper::now();
			}
		} else {
			if ($want->date_confirm || $currentUser->is_want_confirm) {
				$setActive = true;
			} else {
				$want->status = self::STATUS_NEW;
				$moderType = WantLog::MODER_TYPE_STAY_ON_PREMODER;
			}
		}

		// Если запрос активируется
		if ($setActive) {
			$want->status = self::STATUS_ACTIVE;
			$want->date_active = Helper::now();
		}

		$want->save();

		// зафиксируем изменение в WantLog
		$wantLog = new WantLog();
		$wantLog->want_id = $want->id;
		$wantLog->status = $want->status;
		$wantLog->moder_type = $moderType;
		if (!$wantLog->admin_id) {
			$wantLog->user_id = UserManager::getCurrentUserId();
		}
		$wantLog->save();

		return true;
	}

	public static function genRestartToken($wishId) {
		return md5($wishId . microtime(true) . rand(1, 100000000) . $wishId);
	}

	/**
	 * Дата истечения размещения запроса
	 *
	 * @param string $lang Язык запроса
	 * @param float $priceLimit Лимит цены предложений
	 * @param int $time Дата Unixtime от которой считать размещение (если нет от текущей)
	 *
	 * @return int
	 */
	public static function getExpireDate(string $lang, float $priceLimit = 0, $time = 0) {
		if (!$time) {
			$time = time();
		}
		return $time + Helper::ONE_DAY * self::getExpireDays($lang, $priceLimit);
	}

	/**
	 * Дата отправки письма об отсутствующих предложениях
	 *
	 * @param string $lang Язык запроса
	 * @param float $priceLimit Лимит цены предложений
	 * @param int $time Дата Unixtime от которой считать размещение (если нет от текущей)
	 *
	 * @return int
	 */
	public static function getWithoutOffersDate(string $lang, float $priceLimit = 0, $time = 0) {
		if (!$time) {
			$time = time();
		}

		$days =  self::getExpireDays($lang, $priceLimit);

		if ($days == 1) {
			$days = $days - 0.5;
		} else {
			$days = $days - 1;
		}

		return $time + Helper::ONE_DAY * $days;
	}

	/**
	 * Получение срока размещения запроса в днях
	 *
	 * @param string $lang Язык запроса
	 * @param float $priceLimit Лимит цены предложений
	 *
	 * @return int
	 */
	public static function getExpireDays(string $lang, float $priceLimit): int {
		$days = 1;
		if ($lang == Translations::EN_LANG) {
			// Для долларов
			if ($priceLimit >= 50 && $priceLimit <= 100) {
				$days = 2;
			} elseif ($priceLimit > 100) {
				$days = 3;
			}
		} else {
			// Для рублей
			if ($priceLimit >= 5000 && $priceLimit <= 20000) {
				$days = 2;
			} elseif ($priceLimit > 20000) {
				$days = 3;
			}
		}

		return $days;
	}

	/**
	 * Добавление просмотра запроса
	 *
	 * @param array $wantIds
	 * @param int|null $userId
	 * @return bool
	 */
	public static function addView($wantIds, $userId) {
		if (empty($wantIds) || !is_array($wantIds) || empty($userId)) {
			return false;
		}
		$wantViews = WantView::getUserWantViews($wantIds, $userId);
		$addWantViewIds = array_diff($wantIds, $wantViews);
		// Если просмотры учтены, то не добавляем их.
		if (empty($addWantViewIds)) {
			return false;
		}
		$toUpdate = [Want::FIELD_VIEWS_DIRTY => DB::raw(Want::FIELD_VIEWS_DIRTY . " + 1")];
		if (!UserManager::isDirty($userId)) {
			$toUpdate[Want::FIELD_VIEWS]=  DB::raw(Want::FIELD_VIEWS . " + 1");
		}
		Want::whereIn(Want::FIELD_ID, $addWantViewIds)
			->limit(count($addWantViewIds))
			->update($toUpdate);
		return WantView::addUserWantView($addWantViewIds, $userId);
	}

	/**
	 * Добавление просмотров
	 * @param array $wantIds
	 * @return array
	 */
	public static function api_addView($wantIds) {
		global $actor;

		if (!empty($actor) && self::addView($wantIds, $actor->id)) {
			$result = [
				'result' => true
			];
		} else {
			$result = [
				'result' => false
			];
		}
		return $result;
	}

	public static function clearViews($wantId) {
		global $conn;

		$query = "DELETE FROM want_view WHERE want_id = '" . mres($wantId) . "'";
		return $conn->Execute($query);
	}

	public static function registerViewOfferByOrder($orderId) {
		global $actor, $conn;
		if (empty($actor)) {
			return false;
		}

		$orderId = intval($orderId);
		if (empty($orderId)) {
			return false;
		}

		$sql = "SELECT id, want_id FROM offer WHERE order_id=" . $orderId;
		$offerInfo = $conn->getEntity($sql);
		if (empty($offerInfo)) {
			return false;
		}

		$sql = "SELECT 1 FROM offer_view WHERE offer_id=" . $offerInfo->id;
		$exist = $conn->getCell($sql);
		if (!empty($exist)) {
			return true;
		}

		$sql = "INSERT INTO offer_view 
			SET
				want_id=" . $offerInfo->want_id . ",
				offer_id=" . $offerInfo->id . ",
				date_create=NOW()";
		$conn->Execute($sql);
		return true;
	}

	/**
	 * Возвращает массив ИД проектов которые не нужно показывать текущему пользователю.
	 *
	 * @return array
	 */
	public static function getNotViewProjects() {
		$actorId = UserManager::getCurrentUserId();

		$ownOffersIds = Offer::where(Offer::FIELD_USER_ID, $actorId)
				->whereIn(Offer::FIELD_STATUS, [OfferManager::STATUS_ACTIVE, OfferManager::STATUS_CANCEL, OfferManager::STATUS_DONE])
				->pluck(Offer::FIELD_WANT_ID)
				->toArray();

		$ownWantIds = Want::where(Want::FIELD_USER_ID, $actorId)
			->pluck(Want::FIELD_ID)
			->toArray();

		return array_unique(array_merge($ownOffersIds, $ownWantIds));
	}

	/**
	 * Есть ли предложение от пользователя по запросу.
	 *
	 * @param integer $projectId ID запроса.
	 *
	 * @return boolean
	 */
	public static function checkExistsOffer($projectId) {
		$actorId = UserManager::getCurrentUserId();

		return Offer::where(Offer::FIELD_USER_ID, $actorId)
			->where(Offer::FIELD_WANT_ID, $projectId)
			->whereIn(Offer::FIELD_STATUS, [OfferManager::STATUS_ACTIVE, OfferManager::STATUS_CANCEL, OfferManager::STATUS_DONE])
			->exists();
	}

	public static function forceStopWants() {
		$sql = "SELECT id FROM want WHERE status IN ('new','active') AND kwork_count = 0 AND date_expire < NOW()";
		$wantIds = App::pdo()->fetchAllByColumn($sql);
		if (!empty($wantIds)) {
			foreach ($wantIds as $id) {
				self::setStop($id, true, true);
			}
		}
	}

	/**
	 * Имеет ли запрос на услуги активные предложения от продавцов
	 * @param int $wantId Идентификатор запроса
	 * @return bool
	 */
	public static function hasActiveOffers(int $wantId) : bool {
		return Offer::where(Offer::FIELD_WANT_ID, $wantId)
			->whereNotIn(Offer::FIELD_STATUS, [\OfferManager::STATUS_REJECT, \OfferManager::STATUS_CANCEL, \OfferManager::STATUS_DELETE])
			->exists();
	}

	/**
	 * Проверка названия запроса на дубликаты
	 * @param $userId - ID пользователя
	 * @param $title - Название запроса
	 * @param $wantId - ID запроса, если редактируется
	 * @return bool - Найдены ли запросы с таким же названием
	 */
	public static function checkTitleClone($userId, $title, $wantId = null) {
		$userId = intval($userId);
		$titleWords = Helper::calcWordStat(Helper::explodeDescToWords($title));
		if (empty($titleWords)) {
			return false;
		}
		$sql = "SELECT id, name 
			FROM want 
			WHERE 
				user_id = :userId AND 
				status IN (:statusActive, :statusNew)";
		if (!empty($wantId)) {
			$sql .= " AND id != " . intval($wantId);
		}
		$wants = App::pdo()->fetchAll($sql, [
			"userId" => $userId,
			"statusActive" => self::STATUS_ACTIVE,
			"statusNew" => self::STATUS_NEW
		]);
		foreach ($wants as $want) {
			$_titleWordStat = Helper::calcWordStat(Helper::explodeDescToWords($want["name"]));
			$hasDiff = array_diff(array_keys($titleWords), array_keys($_titleWordStat));
			if (empty($hasDiff) && count($titleWords) == count($_titleWordStat)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка описания запроса на дубликаты
	 * @param $userId - ID пользователя
	 * @param $desc - Описание запроса
	 * @param $wantId - ID запроса, если редактируется
	 * @return bool - Найдены ли запросы, описание которых на MAX_ALLOW_DESC_SIMILARITY процентов схоже с проверяемым описанием
	 */
	public static function checkDescClonePercent($userId, $desc, $wantId = null) {
		$userId = intval($userId);

		$saveDescWordStat = Helper::calcWordStat(Helper::explodeDescToWords($desc));

		$sql = "SELECT id, `desc`
			FROM want 
			WHERE 
				user_id= :userId AND 
				status IN (:statusActive, :statusNew)";
		if (!empty($wantId)) {
			$sql .= ' AND id !=' . intval($wantId);
		}
		$wants = App::pdo()->fetchAll($sql, [
			"userId" => $userId,
			"statusActive" => self::STATUS_ACTIVE,
			"statusNew" => self::STATUS_NEW
		]);
		foreach ($wants as $want) {
			$_descWordStat = Helper::calcWordStat(Helper::explodeDescToWords($want["desc"]));
			$maxPercent = max(Helper::calcDescSimilarityPercent($saveDescWordStat, $_descWordStat), Helper::calcDescSimilarityPercent($_descWordStat, $saveDescWordStat));
			if ($maxPercent >= self::MAX_ALLOW_DESC_SIMILARITY) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Может ли пользователь создать запрос
	 * @param $userId - ID пользователя
	 * @return bool
	 */
	public static function canCreate($userId) {
		$existWantCount = Want::where(Want::FIELD_USER_ID, $userId)
			->whereNotIn(Want::FIELD_STATUS, [Want::STATUS_DELETE, Want::STATUS_STOP, Want::STATUS_USER_STOP])
			->where(Want::FIELD_DATE_CREATE, ">=", date("Y-m-d 00:00:00"))
			->count();
		return $existWantCount < self::MAX_ALLOW_WANT_COUNT_PER_DAY;
	}

	/**
	 * Отклонение запроса из-за вируса в файле или ссылке
	 * @param int $wantId - идентификатор запроса
	 * @param int $virustotalVerificationId - идентификатор проверки
	 * @return bool
	 */
	public static function rejectDueToVirus($wantId, $virustotalVerificationId) {
		if (!$wantId || !$virustotalVerificationId) {
			return false;
		}
		$wantId = (int) $wantId;
		$virustotalVerificationId = (int) $virustotalVerificationId;
		$sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id";
		$want = App::pdo()->fetch($sql, ["id" => $wantId], PDO::FETCH_OBJ);
		if ($want && $want->status != self::STATUS_CANCEL) {
			$sql = "DELETE FROM " . self::TABLE_MODER . " WHERE status = 'new' AND want_id = :want_id";
			$comment = "Файл или ссылка может быть вредоносным. Пожалуйста, замените файл / ссылку.";
			App::pdo()->execute($sql, ["want_id" => $wantId]);
			$moderType = self::MODER_TYPE_AUTO;
			$insertFields = [
				"user_id" => 0,
				"want_id" => $wantId,
				"status" => "reject",
				"comment" => $comment,
				"date_create" => Helper::now(),
				"date_moder" => Helper::now(),
				"moder_type" => $moderType,
				"virustotal_verification_id" => $virustotalVerificationId
			];
			$wantModerId = App::pdo()->insert(self::TABLE_MODER, $insertFields);

			$wantStatus = self::STATUS_CANCEL;
			App::pdo()->update(self::TABLE_NAME, [
				"status" => $wantStatus,
				"need_postmoder" => 0,
				"date_reject" => Helper::now(),
				"date_moder" => Helper::now(),
				"last_want_moder_id" => $wantModerId
					], "id = :id", ["id" => $wantId]);

			// зафиксируем изменение в WantLog
			$wantLog = new WantLog();
			$wantLog->want_id = $wantId;
			$wantLog->status = $wantStatus;
			$wantLog->want_moder_id = $wantModerId;
			$wantLog->action_type_id = WantLog::ACTION_TYPE_SYSTEM;
			$wantLog->action_name_id = WantLog::ACTION_NAME_SYSTEM_AUTO_CANCELED;
			$wantLog->info = "<p><b>Причина:<b/> " . $comment . "</p>";
			$session = SessionContainer::getSession();
			$wantLog->admin_id = $session->get("ADMINID");
			if (!$wantLog->admin_id) {
				$wantLog->user_id = UserManager::getCurrentUserId();
			}
			$wantLog->save();

			App::pdo()->update(UserManager::TABLE_NAME, ["is_want_confirm" => 0], UserManager::FIELD_USERID . " = :" . UserManager::FIELD_USERID, [UserManager::FIELD_USERID => $want->user_id]);
			InboxManager::sendMessage(App::config("kwork.moder_id"), $want->user_id, "Ваш запрос " . $want->name . " отклонен. <p>Причина: " . $comment . " </p>");

			return true;
		}
	}

	/**
	 * Создать проверку VirusTotal для запроса
	 * @param int $wantId
	 */
	public static function createVirusTotalCheck($wantId) {
		$vtcm = (new VirusTotalCheckModel())
			->setEntityType(VirusTotalCheckModel::ENTITY_WANT)
			->setEntityId($wantId);
		$sql = "SELECT `desc` FROM " . self::TABLE_NAME . " WHERE id = :id";
		$description = App::pdo()->fetchScalar($sql, ["id" => $wantId]);
		if ($description) {
			$urlArr = Helper::getLinksFromString($description);
			if (!empty($urlArr)) {
				foreach ($urlArr as $url) {
					if (VirusTotalCheckManager::isExcludedUrl($url)) {
						continue;
					}
					$vti = (new VirusTotalListItemModel())
						->setContentType(VirusTotalListItemModel::CONTENT_URL)
						->setContent($url)
						->setContentHash(md5($url));
					$vtcm->addItem($vti);
				}
			}
		}
		$sql = "SELECT s
				FROM
					" . FileManager::TABLE_NAME . "
				WHERE
					" . FileManager::FIELD_ENTITY_ID . " = :" . FileManager::FIELD_ENTITY_ID . "
					AND " . FileManager::FIELD_ENTITY_TYPE . " = :" . FileManager::FIELD_ENTITY_TYPE;
		$files = App::pdo()->fetchAllByColumn($sql, 0, [
			FileManager::FIELD_ENTITY_ID => $wantId,
			FileManager::FIELD_ENTITY_TYPE => FileManager::ENTITY_TYPE_WANT]);
		if (!empty($files)) {
			foreach ($files as $s) {
				$fpath = App::config("uploadeddir") . $s;
				$vti = (new VirusTotalListItemModel())
					->setContentType(VirusTotalListItemModel::CONTENT_FILE)
					->setContent($fpath)
					->setContentHash(md5_file($fpath));
				$vtcm->addItem($vti);
			}
		}
		VirusTotalQueueManager::save($vtcm);
	}

	/**
	 * Апи функция создания предложения
	 */
	public static function api_createOffer() {
		$actor = UserManager::getCurrentUser();

		$offerType = post("offerType");
		$wantId = post("wantId");

		// Подготовка данных страницы редиректа
		$redirectParams = [];
		if (post("a")) {
			$redirectParams["a"] = post("a");
		}
		if (post("c")) {
			$redirectParams["c"] = post("c");
		}
		if (post("page")) {
			$redirectParams["page"] = post("page");
		}
		$redirect = "/projects?" . http_build_query($redirectParams);

		if (!in_array($offerType, ["kwork", "custom"]) || !$wantId || !$actor) {
			throw new SimpleJsonException(Translations::t("Ошибка при отправлении предложения"));
		}

		// Ищем проект по ИДшнику, с проверкой владельца, чтобы им не был текущий пользователь
		$want = Want::where(self::F_ID, $wantId)
			->where(self::F_USER_ID, "<>", UserManager::getCurrentUserId())
			->first();

		if (empty($want)) {
			throw new SimpleJsonException(Translations::t("Ошибка при отправлении предложения"));
		} elseif ($want->{self::F_STATUS} != self::STATUS_ACTIVE) {
			throw new SimpleJsonException(Translations::t("Покупатель уже заказал предложение от другого продавца. Сбор предложений прекращен."));
		} elseif (self::checkExistsOffer($wantId)) {
			throw new SimpleJsonException(Translations::t("Вы уже отправили предложение. Повторное отправление не возможно."));
		}

		$description = \OfferManager::commentFilter(post("description"));

		$commentError = OfferManager::checkComment($description, $want->category_id, $want->lang, $offerType === "custom");
		if (!empty($commentError)) {
			throw new \Core\Exception\JsonValidationException($commentError);
		}

		//Если у пользователя есть коннекты
		if (1) {

			if ($offerType === "custom") {
				$stages = post("stages") ? (array)post("stages") : [];
				if (is_array($stages) && !empty($stages)) {
					$customKworkDescription = $description;
					if (count($stages) === 1) {
						$customKworkTitle = Arr::first($stages)[OrderStage::FIELD_TITLE];
					} else {
						$customKworkTitle = $want->name;
					}
					$customKworkTitle = \KworkManager::makeValidName($customKworkTitle);
				} else {
					$customKworkDescription = $description;
					$customKworkTitle = post("kwork_name");
				}
				
				$orderId = OfferManager::offerCustomKwork(
					$want->user_id,
					$customKworkTitle,
					$customKworkDescription,
					(int) post("kwork_duration"),
					(float) post("kwork_price"),
					OrderManager::SOURCE_WANT_PRIVATE,
					$want->lang,
					$want->price_limit,
					0,
					$want->category_id,
					$stages,
					$want->app_id
				);
			} else {
				$orderId = OfferManager::createOffersOrder(
					$want->user_id,
					OrderManager::SOURCE_WANT,
					post("kwork_id"),
					post("kwork_count"),
					post("kwork_package_type"),
					ExtrasManager::extrasFromPost(),
					ExtrasManager::customExtrasFromPost($want->lang),
					$want->price_limit,
					0,
					$want->app_id
				);
			}

		} else {
			//Если коннектов нет, возвращаем ошибку.
			throw new SimpleJsonException(Translations::t("Вы исчерпали лимит коннектов"));
		}
		// Получаем идентификатор кворка
		$sql = "SELECT
						PID
					FROM
						orders
					WHERE
						OID = :id";
		$kworkId = App::pdo()->fetchScalar($sql, ["id" => $orderId]);

		// Создаем запрос
		$offer = new Offer();
		$offer->user_id = $actor->id;
		$offer->want_id = $wantId;
		$offer->kwork_id = $kworkId;
		$offer->order_id = $orderId;
		$offer->status = OfferManager::STATUS_ACTIVE;
		$offer->comment = $description;
		if (OrderStageOfferManager::isTester() && $offerType === "custom") {
			$offer->comment_doubles_description = true;
		}

		$offer->save();
		$offerId = $offer->id;

		Session\SessionContainer::getSession()->set("send-kwork-request-success", $offerType);

		return [
			"success" => true,
			"redirect" => $redirect,
		];
	}

	/**
	 * Получить билдер, на получение запросов на услуги покупателя
	 *
	 * @param int|null $userId Идентификатор пользователя чьи запросы получаем
	 * @param null $status - статус по котором
	 *
	 * @return Builder
	 */
	public static function getWants($userId, $status = null) {
		$builder = Want::with([
				"orders" => function($query) {
					// реальные заказы, а не предложения
					$query->where(Order::FIELD_STATUS, "!=", OrderManager::STATUS_NEW);
				},
				"user",
				"offers",
			])
			->where(self::F_USER_ID, $userId)
			->when(!isset($status), function(Builder $query) use ($status) {
				return $query->whereNotIn(self::F_STATUS, [Want::STATUS_DELETE, Want::STATUS_ARCHIVED]);
			})
			->when(isset($status), function(Builder $query) use ($status) {
				return $query->where(Want::FIELD_STATUS, "=", $status);
			})
			->orderByDesc(self::F_ID);


		return $builder;
	}

	/**
	 * Количество видимых запросов пользователя
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param string $status - статус по которому считаем
	 * @return int
	 */
	public static function getWantsCount(int $userId, $status = null) {
		$query = Want::where(Want::FIELD_USER_ID, $userId);
		if (is_null($status)) {
			$query->whereNotIn(Want::FIELD_STATUS, [Want::STATUS_ARCHIVED, Want::STATUS_DELETE]);
		} else {
			$query->where(Want::FIELD_STATUS, "=", $status);
		}
		return $query->count();
	}

	/**
	 * Получение даты порога показа остановленных запросов пользователя
	 *
	 * @return string
	 */
	public static function getWantVisibleThresholdDate() {
		return date("Y-m-d H:i:s", time() - self::PAYER_HIDE_STOPPED_THRESHOLD);
	}

	/**
	 * Получение минимального лимита цены предложений
	 *
	 * @param string|null $lang Язык запроса
	 * @param int|null $categoryId Рубрика запроса
	 * @param int|null $appId идентификатор приложения запроса
	 *
	 * @return int
	 */
	public static function getMinPriceLimit(?string $lang = null, ?int $categoryId = null, ?int $appId = null): int {
		if (is_null($lang)) {
			$lang = Translations::getLang();
		}

		// если приложение запроса - Kwork (или мы на сайте Kwork)
		return CategoryManager::getCategoryBasePrice($categoryId, $lang);
	}

	/**
	 * Получение максимального лимита цены предложений
	 *
	 * @param string $lang Язык запроса
	 *
	 * @return int
	 */
	public static function getMaxPriceLimit(string $lang): int {
		if ($lang == \Translations::EN_LANG) {
			return self::MAX_PRICE_LIMIT_EN;
		}
		return self::MAX_PRICE_LIMIT_RU;
	}

	/**
	 * Получение запросов услуг которые можно привязать к индивидуальному предложению в переписке
	 *
	 * @param int $payerId Идентификатор покупателя
	 * @param int $workerId Идентификатор продавца
	 *
	 * @return array [wantId => WantName, ...]
	 */
	public static function getUserIndirectlyWants(int $payerId, int $workerId) {
		$workedWantsIds = Order::where(Order::FIELD_USERID, $payerId)
			->where(Order::FIELD_WORKER_ID, $workerId)
			->whereNotNull(Order::FIELD_PROJECT_ID)
			->where(Order::FIELD_PROJECT_ID, "<>", 0)
			->whereIn(Order::FIELD_STATUS, [
				OrderManager::STATUS_DONE,
				OrderManager::STATUS_INPROGRESS,
				OrderManager::STATUS_CHECK,
				OrderManager::STATUS_ARBITRAGE])
			->distinct()
			->pluck(Order::FIELD_PROJECT_ID)
			->toArray();

		$query = DB::table(self::TABLE_NAME)
			->join(Offer::TABLE_NAME,
				self::TABLE_NAME . "." . self::F_ID,
				"=",
				Offer::TABLE_NAME . "." . Offer::FIELD_WANT_ID)
			->where(self::TABLE_NAME . "." . self::F_USER_ID, $payerId)
			->where(Offer::TABLE_NAME . "." . Offer::FIELD_USER_ID, $workerId)
			->whereIn(Offer::TABLE_NAME . "." . Offer::FIELD_STATUS, [
				OfferManager::STATUS_ACTIVE,
				OfferManager::STATUS_CANCEL
			])
			->where(self::TABLE_NAME . "." . self::F_STATUS, "<>", self::STATUS_DELETE)
			->whereNested(function ($query) {
				$query->whereIn(self::TABLE_NAME . "." . self::F_STATUS, [Want::STATUS_STOP, Want::STATUS_USER_STOP])
					->where(self::TABLE_NAME . "." . self::FIELD_DATE_CREATE, "<", self::getWantVisibleThresholdDate());
			}, "AND NOT");

		if (!empty($workedWantsIds)) {
			$query->whereNotIn(self::TABLE_NAME . "." . self::F_ID, $workedWantsIds);
		}

		return $query->distinct()
			->pluck(self::TABLE_NAME . "." . self::F_NAME, self::TABLE_NAME . "." . self::F_ID)
			->toArray();
	}

	/**
	 * Есть ли у пользователя остановленные заказы без исполнителя
	 *
	 * @param int $userId Идентифкатор пользователя
	 *
	 * @return bool
	 */
	public static function isUserHasStopedRequestWithoutOrder(int $userId): bool {
		$wantsIds = Want::where(self::F_USER_ID, $userId)
			->whereIn(self::F_STATUS, [Want::STATUS_STOP, Want::STATUS_USER_STOP])
			->where(self::FIELD_DATE_CREATE, ">=", self::getWantVisibleThresholdDate())
			->pluck(self::F_ID)
			->toArray();

		if (empty($wantsIds)) {
			return false;
		}

		$orderedWantsCount = Order::where(Order::FIELD_USERID, $userId)
			->whereIn(Order::FIELD_PROJECT_ID, $wantsIds)
			->distinct()
			->count(Order::FIELD_PROJECT_ID);

		return count($wantsIds) > $orderedWantsCount;
	}

	/**
	 * Скрипт удаляющий уведомление о необходимости выбрать исполнителя, через 7 дней
	 */
	public static function cron_clearNeedSelectWorkerNotifications() {
		$time = Carbon::now()->subDays(7)->getTimestamp();

		$updated = DB::table(Notification::TABLE_NAME)
			->where([
				Notification::F_TYPE => WantNotify::NOTIFICATION_TYPE_SELECT_WORKER,
				Notification::F_UNREAD => 1,
			])
			->where(Notification::F_TIME_ADDED, "<=", $time)
			->update([Notification::F_UNREAD => 0])
		;

		return $updated;
	}


	/**
	 * Возвращает массив источников заказов, которые являются прямыми для запроса
	 * @return array
	 */
	public static function getDirectTypes() {
		return [OrderManager::SOURCE_WANT_PRIVATE, OrderManager::SOURCE_WANT];
	}

	/**
	 * Возвращает массив источников заказов, которые являются косвеными для запроса
	 * @return array
	 */
	public static function getInDirectTypes() {
		return [
			OrderManager::SOURCE_CART,
			OrderManager::SOURCE_INBOX,
			OrderManager::SOURCE_INBOX_PRIVATE,
			OrderManager::SOURCE_ANYWHERE_PRIVATE,
			OrderManager::SOURCE_ANYWHERE
		];
	}

	/**
	 * Проверяем принадлежность типа к косвеным или прямым заказам
	 * @param $type
	 * @return bool
	 */
	public static function checkValidSourceType($type) {
		$allTypes = array_merge(self::getDirectTypes(), self::getInDirectTypes());
		return in_array($type, $allTypes);
	}

	/**
	 * Посчитать кол-во новых проектов, которые были подтверждены начиная с заданной даты
	 * @param DateTime $date
	 * @param string $lang
	 * @return int
	 */
	public static function getConfirmedCountByDate(DateTime $date, $lang) {
		$count = Want::where(Want::FIELD_LANG, $lang)
			->whereIn(Want::FIELD_STATUS, [Want::STATUS_ACTIVE, Want::STATUS_NEW, Want::STATUS_STOP, Want::STATUS_USER_STOP])
			->where(Want::FIELD_DATE_CONFIRM, ">=", Helper::dateTimeToMysqlString($date))
			->selectRaw("count(distinct id) as cnt")
			->value("cnt");
		return (int)$count;
	}

	/**
	 * Получить кол-во новых проектов за последние 24 часа
	 * @return string
	 */
	public static function getNewWantsCount() {
		$day = date_create("now - 24 hours");
		return Want::where(Want::FIELD_DATE_CREATE, ">=",  \Helper::dateTimeToMysqlString($day))->count();
	}

	/**
	 * Посчитать кол-во новых проектов за последную неделю
	 * @param string $lang
	 * @return int
	 */
	public static function getNewWantsPerWeek($lang) {
		$weekTime = date_create("now - 1 week");
		$count = self::getConfirmedCountByDate($weekTime, $lang);
		return $count;
	}

	/**
	 * Проверяет видим ли проект для пользователя
	 * @param int wantId
	 * @return bool
	 */
	public static function isWantAccessableById($wantId) {
		return Want::where(Want::FIELD_ID, $wantId)->whereIn(self::F_STATUS, [Want::STATUS_ACTIVE, Want::STATUS_STOP, Want::STATUS_USER_STOP])->exists();
	}

	/**
	 * Останавливает все запросы пользователя с логированием
	 * @param int $userId
	 */
	public static function stopUserWants(int $userId) {
		$wantIds = Want::query()
			->where(Want::FIELD_USER_ID, $userId)
			->whereIn(Want::FIELD_STATUS, [Want::STATUS_NEW, Want::STATUS_ACTIVE])
			->pluck(Want::FIELD_ID)
			->toArray();

		if (!empty($wantIds)) {
			// остановим все запросы пользователя
			Want::whereIn(Want::FIELD_ID, $wantIds)->update([
				Want::FIELD_STATUS => Want::STATUS_STOP
			]);

		}
	}

	/**
	 * Проверяем запрос на необходимость добавления его в архив и добавляем если это нужно
	 * @param int $wantId - id запроса
	 * @return bool
	 */
	public static function checkAddArchived(int $wantId) {
		$want = Want::find($wantId);

		$haveOneDone = false;
		if (count($want->orders) > 0) {
			foreach ($want->orders as $order) {
				//Если есть хотя бы один не завершенный (отменный или выполненный) заказ, в архив мы не попадаем
				//(так же не учитываем предложения или неоплаченные заказы)
				if (!in_array($order->status, [OrderManager::STATUS_DONE, OrderManager::STATUS_CANCEL, OrderManager::STATUS_NEW])) {
					return false;
				}
				if ($order->isDone()) {
					$haveOneDone = true;
				}
			}
		}
		//Если есть хотя бы один завершенный заказ
		if ($haveOneDone) {
			$want->status = Want::STATUS_ARCHIVED;
			$want->save();

			//Сохраняем историю
			$wantLog = new WantLog();
			$session = SessionContainer::getSession();
			$wantLog->admin_id = $session->get("ADMINID");
			if (!$wantLog->admin_id) {
				$wantLog->user_id = UserManager::getCurrentUserId();
			}
			$wantLog->want_id = $want->id;
			$wantLog->save();
			return true;
		}
		return false;
	}

	/**
	 *
	 * Проверяет есть ли активные заказы пользователя, кроме переданного
	 *
	 * @param Want $want
	 * @return bool
	 */
	public static function isOtherActiveWantsExceptByWantId(Want $want) {
		return Want::where(Want::FIELD_ID, "<>", $want->{Want::FIELD_ID})
			->where(Want::FIELD_STATUS, Want::STATUS_ACTIVE)
			->where(Want::FIELD_USER_ID, $want->{Want::FIELD_USER_ID})
			->exists();
	}

	/**
	 * Количество файлов доступных для загрузки
	 *
	 * @param int $wantId Идентификатор проекта
	 * @return int количество файлов для загрузки
	 */
	protected static function getMaxUploadedFiles(int $wantId) {
		$maxUploadedFilesCount = \App::config("files.max_count");

		$filesCount = FileManager::getEntityFilesCount($wantId, File::ENTITY_TYPE_WANT);
		return max(0, $maxUploadedFilesCount - $filesCount);
	}

	/**
	 * Прикрепляет файлы к проекту
	 * Обязательный порядок: 1 - обработка удаляемых файлов 2 - обработка добавленных файлов
	 * иначе при добавлении мы можем не уложиться в лимит файлов
	 *
	 * @param int    $wantId Идентификатор проекта
	 * @param array  $files  Массив файлов, полученный из функции post()
	 * @param string $type   Тип сущности, к которой нужно прикрепить файлы (трек либо черновик трека)
	 * @throws Exception
	 */
	public static function attachUploadedFiles(int $wantId, array $files, string $type = File::ENTITY_TYPE_WANT) {
		if ($wantId > 0 && !empty($files)) {
			if (!empty($files["delete"])) {
				FileManager::deleteEntityFilesByType($wantId, $files["delete"], [$type]);
			}
			if (!empty($files["new"])) {
				FileManager::saveFiles($wantId, $files["new"], $type, self::getMaxUploadedFiles($wantId));
			}
		}
	}
}
