<?php

use Core\DB\DB;
use Core\Exception\BalanceDeficitWithOrderException;
use Core\Exception\SimpleJsonException;
use Currency\CurrencyExchanger;
use Helpers\Rating\AutoRating\GetAutoRatingInfoService;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;
use Model\Category;
use Model\CurrencyModel;
use Model\Kwork;
use Model\Operation;
use Model\Order;
use Model\OrderData;
use Model\OrderNames;
use Model\Rating;
use Model\Track;
use Model\User;
use Model\Want;
use Order\MyOrders;
use Order\MyOrdersFactory;
use Order\OrderDataManager as ODM;
use Track\Type;

class OrderManager {
	/**
	 * Класс с utility методами для orderManager
	 * которые используются в кронах
	 */
	use \Order\Utils\CronActions;

	const MULTI_KWORK_RATE = 0.5;

	/**
	 * Таблица заказов orders
	 */
	const TABLE_NAME = "orders";

	/**
	 * Идетификатор зазказа orders.OID
	 */
	const F_OID = "OID";
	const F_ORDER_ID = "OID";
	/**
	 * Идетификатор покупателя orders.USERID
	 */
	const F_USERID = "USERID";
	const F_PAYER_ID = "USERID";
	/**
	 * Идентификатор продавца orders.worker_id
	 */
	const F_WORKER_ID = "worker_id";
	/**
	 * Идентификатор кворка orders.PID
	 */
	const F_PID = "PID";
	const F_KWORK_ID = "PID";
	/**
	 * Статус заказа orders.status
	 */
	const F_STATUS = "status";

	/**
	 * Id последнего трека в заказе orders.last_track_id
	 */
	const F_LAST_TRACK_ID = "last_track_id";

	/**
	 * Время создания заказа orders.time_added
	 */
	const F_TIME_ADDED = "time_added";

	/**
	 * Время начала заказа orders.stime
	 */
	const F_STIME = "stime";

	/**
	 * Общая цена заказа orders.price
	 */
	const F_PRICE = "price";

	/**
	 * Средства которые получит продавец после выполнения заказа
	 */
	const F_CRT = "crt";

	/**
	 * Пометка того что заказчик предоставил необходимые для выполнения заказа данные
	 */
	const F_DATA_PROVIDED = "data_provided";

	const F_COUNT = "count";
	const F_DURATION = "duration";
	const F_KWORK_TITLE = "kwork_title";
	const F_KWORK_DAYS = "kwork_days";
	const F_DATE_DONE = "date_done";
	const F_DATE_CANCEL = "date_cancel";
	const F_DEADLINE = "deadline";
	const F_IN_WORK = "in_work";

	/**
	 * Игнорировать ли заказ при расчете рейтинга
	 * <p>orders.rating_ignore tinyint</p>
	 * <p>DEFAULT 0</p>
	 */
	const F_RATING_IGNORE = "rating_ignore";

	/**
	 * Поле состояния рейтинга заказа.
	 */
	const F_RATING_TYPE = "rating_type";

	/**
	 * Cостояния для rating_type
	 * good - оставлен положительный отзы.
	 * bad - оставлен отрицательный отзыв
	 * first - без отзыва первичный
	 * second - без отзыва повторный
	 * tips - без отзыва с чаевыми
	 * new - новый
	 */
	const RATING_TYPE_GOOD = 'good';
	const RATING_TYPE_BAD = 'bad';
	const RATING_TYPE_FIRST = 'first';
	const RATING_TYPE_SECOND = 'second';
	const RATING_TYPE_TIPS = 'tips';
	const RATING_TYPE_NEW = 'new';

	/**
	 * Откуда был создан заказ
	 * <p>orders.source_type enum</p>
	 * <p>DEFAULT NULL</p>
	 */
	const F_SOURCE_TYPE = "source_type";
	const SOURCE_KWORK = "kwork";              // Из магазина
	const SOURCE_CART = "cart";                // Из корзины
	const SOURCE_INBOX = "inbox";              // Из личных сообщений, готовый кворк
	const SOURCE_INBOX_PRIVATE = "inbox_private";      // Из личных сообщений, индивидуальный кворк
	const SOURCE_WANT = "want";                // Из предложения на запрос, готовый кворк
	const SOURCE_WANT_PRIVATE = "want_private";        // Из предложения на запрос, индивидуальный кворк

	/**
	 * Из "кворк повсюду", готовый кворк
	 * @deprecated "кворк повсюду" был удален, поддержка исторических значений
	 */
	const SOURCE_ANYWHERE = "anywhere";

	/**
	 * Из "кворк повсюду", индивидуальный кворк
	 * @deprecated "кворк повсюду" был удален, поддержка исторических значений
	 */
	const SOURCE_ANYWHERE_PRIVATE = "anywhere_private";

	// псевдо-источники
	const SOURCE_INBOX_INDIRECT = "inbox_indirect";                // Из личных сообщений, готовый кворк, косвенный заказ
	const SOURCE_INBOX_PRIVATE_INDIRECT = "inbox_private_indirect";        // Из личных сообщений, индивидуальный кворк, косвенный заказ

	/**
	 * Массив как был произведен заказ
	 */
	const SOURCE_ARRAY = [
		self::SOURCE_KWORK => "Магазин",
		self::SOURCE_CART => "Корзина",
		self::SOURCE_INBOX => "Личка (готовый)",
		self::SOURCE_INBOX_PRIVATE => "Личка (индивид)",
		self::SOURCE_WANT => "Биржа (готовый)",
		self::SOURCE_WANT_PRIVATE => "Биржа (индивид)",
		self::SOURCE_ANYWHERE => "Кворк повсюду (готовый)",
		self::SOURCE_ANYWHERE_PRIVATE => "Кворк повсюду (индивид)"
	];

	/*
	 * Массив дополнительных псевдо-источников
	 */
	const SOURCE_PSEUDO_ARRAY = [
		self::SOURCE_INBOX_INDIRECT => "Личка косв. (готовый)",
		self::SOURCE_INBOX_PRIVATE_INDIRECT => "Личка косв. (индивид)",
	];

	const F_CURRENCY_ID = "currency_id";
	const F_CURRENCY_RATE = "currency_rate";
	const F_BONUS_TEXT = "bonus_text";

	/**
	 * Id запроса для заказов с Биржи
	 */
	const F_PROJECT_ID = "project_id";

	/**
	 *  В работе
	 */
	const STATE_IN_WORK = 1;
	const STATE_NOT_IN_WORK = 0;

	/**
	 * Статус "новый" он же "предложение" - еще не оплаченный заказ
	 */
	const STATUS_NEW = 0;

	/**
	 * Статус "в работе" - деньги под заказ уже зарезервированы
	 */
	const STATUS_INPROGRESS = 1;

	/**
	 * Статус "арбитраж" - деньги под заказ зарезервированы
	 */
	const STATUS_ARBITRAGE = 2;

	/**
	 * Статус "отменен" - деньги вернули на счет покупателя
	 */
	const STATUS_CANCEL = 3;

	/**
	 * Статус "на проверке" - деньги под заказ зарезервированы
	 */
	const STATUS_CHECK = 4;

	/**
	 * Статус "выполнен" - вознаграждение выплачено продавцу
	 */
	const STATUS_DONE = 5;

	/**
	 * OrderStages
	 *
	 * Статус "требует оплаты для продолжения" требуется внесение средств покупателем для продолжения заказа
	 * заказ показывается покупателю только если было резервирование средств для первого этапа поле was_staged_payment
	 */
	const STATUS_UNPAID = 6;

	/**
	 * Статусы загрузки портфолио
	 */
	const PORTFOLIO_NEW = 'new';
	const PORTFOLIO_ALLOW = 'allow';
	const PORTFOLIO_DENY = 'deny';

	/**
	 * Ошибки создания заказа
	 */
	const ERROR_KWORK_NOT_FOUND = "wrong_kwork_id";
	const ERROR_NEED_LOGIN = "login";
	const ERROR_NEED_MORE_MONEY = "purse";
	const ERROR_INCORRECT_PACKAGE_TYPE = "wrong_package_type";
	const ERROR_WRONG_LANGUAGE = "wrong_language";
	const ERROR_LANG_MISMATCH = "lang_mismatch";

	// time_isLost:
	// 1 - Осталось App::config("kwork.need_worker_new_inwork_hours") часов на взятие заказа в работу до автоотмены
	const IS_LOST_TO_AUTOCANCEL = 1;
	// 0 - осталось более часа, что бы взять заказ, так как прошло менее 23 часа с момента оплаты,
	const IS_LOST_AFTER_PAY = 0;
	// -1 - все остальные заказы, которые не требуется брать в работу.
	const IS_LOST_FREE = -1;
	// -2 - заказ не сдан вовремя
	const IS_LOST_OVERDUE = -2;

	/**
	 * Статусы заказов, которые считаются активными
	 */
	const ACTIVE_ORDERS_STATUSES = [
		OrderManager::STATUS_INPROGRESS,
		OrderManager::STATUS_ARBITRAGE,
		OrderManager::STATUS_CHECK,
	];

	//Время (в минутах) прибавляемое к deadline заказов на паузе
	const DEFAULT_EXTEND_TIME = 15;

	/**
	 * Кол-во дней устанавливаемое в срок выполнения заказа после запуска заказа админом
	 * @see Type::isAdminRunOrderType()
	 */
	const DEADLINE_EXTEND_TIME_BY_ADMIN = 3 * Helper::ONE_DAY;

	/*
	 * Массив для кешированных заказов (в статическом поле)
	 */
	private static $_cachedOrders = [];

	/**
	 * Создание заказа пользователем
	 *
	 * @param int $kworkId - идентификатор кворка
	 * @param float $price - цена за заказ
	 * @param int $count - количество кворков
	 * @param int $duration - длительной
	 * @param int|false $workTime - время работы
	 * @param string|bool $packageType - пакетный ли кворк
	 * @param bool $isQuick - срочность
	 * @param int $cartPosition - позиция в корзине
	 * @param int $cartCount - количество кворка в корзине пользователя
	 * @param string $sourceType - откуда был создан заказ
	 * @param int $currencyId Идентификатор валюты заказа
	 * @param float $currencyRate Текущий курс обмена валюты
	 *
	 * @return boolean
	 * @global object $conn - подключение к бд
	 * @global object $actor - пользователь
	 */
	public static function create($kworkId, $price, $count, $duration, $workTime = false, $packageType = false, $isQuick = false, $cartPosition = 0, $cartCount = 0, $sourceType = self::SOURCE_KWORK, $currencyId = CurrencyModel::RUB, $currencyRate = 1.0) {
		$actor = UserManager::getCurrentUser();

		if (!$actor) {
			return false;
		}

		// кворк
		{
			$kwork = App::pdo()->fetch("SELECT 
												PID as 'id', 
												USERID as 'userId', 
												gtitle, 
												gdesc,  
												price, 
												ctp, 
												lang,
												category
											FROM posts 
											WHERE PID = :id", ["id" => $kworkId], PDO::FETCH_OBJ);
		}

		if ($kwork->userId == $actor->id) {
			return false;
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$turnover = self::getTurnover($kwork->userId, $actor->id, $kwork->lang);
		$commission = self::calculateCommission($price, $turnover, $kwork->lang);
		$crt = $commission->priceWorker;

		if ($workTime) {
			$kwork->days = $workTime;
		}

		$workerAmount = $crt;
		$workerLang = User::where(User::FIELD_USERID, $kwork->userId)->value(User::FIELD_LANG);
		if ($workerLang != $kwork->lang) {
			$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$workerAmount,
				$kwork->lang,
				$workerLang
			);
		}

		$payerAmount = $price;
		if ($actor->lang != $kwork->lang) {
			$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$payerAmount,
				$kwork->lang,
				$actor->lang
			);
		}

		$insertData = [
			"USERID" => $actor->id,
			"worker_id" => $kwork->userId,
			"PID" => $kworkId,
			"status" => OrderManager::STATUS_NEW,
			"time_added" => time(),
			"stime" => time(),
			"price" => $price,
			"crt" => $crt,
			"count" => $count,
			"duration" => $duration,
			"kwork_title" => $kwork->gtitle,
			"kwork_days" => 0,
			self::F_SOURCE_TYPE => $sourceType,
			"currency_id" => $currencyId,
			"currency_rate" => $currencyRate,
			Order::FIELD_WORKER_AMOUNT => $workerAmount,
			Order::FIELD_PAYER_AMOUNT => $payerAmount,
			Order::FIELD_INITIAL_DURATION => $duration,
		];
		if ($isQuick) {
			$insertData["is_quick"] = 1;
		}
		$orderId = App::pdo()->insert(OrderManager::TABLE_NAME, $insertData);


		$orderData = [
			"order_id" => $orderId,
			"kwork_desc" => $kwork->gdesc,
			"kwork_category" => $kwork->category,
			"kwork_price" => $kwork->price,
			"kwork_ctp" => $kwork->ctp,
		];

		ODM::add($orderData);
		OrderManager::logStatus($orderId, OrderManager::STATUS_NEW);

		$volumeExtraTitle = "";
		if (!empty($orderVolumeType)) {
			$volumeExtraTitle = Translations::t("Количество %s", $orderVolumeType->getPluralizedName(0));
		}

		return $orderId;
	}

	/**
	 * Возвращает актуальное время автоматического принятия заказа.
	 *
	 * Проверяет, была ли заявка Продавца на проверку и учитывает временные паузы на обработку заявки Покупателя
	 * на отмену заказа после заявки на проверку.
	 * @param Order $order - заказ, по которому вычисляется время автозакрытия
	 * @return int - актуальное время автозакрытия в timestamp
	 * @throws Exception
	 */
	public static function getDateForAutoAccept(Order $order): int {
		$dateCheck = $order->date_check;
		$tracks = $order->tracks->all();

		//если поэтапный заказ
		if ($order->has_stages && $order->stages) {
			$earliestCheckingStage = $order->stages->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
				->where(OrderStage::FIELD_PROGRESS, OrderStage::PROGRESS_FULL)
				->sortBy(OrderStage::FIELD_CHECK_DATE)
				->first();
			if ($earliestCheckingStage instanceof OrderStage) {
				$dateCheck = $earliestCheckingStage->check_date;
			}
		}

		$dateCheck = strtotime($dateCheck);
		//высчитываем временную паузу по заявкам на проверку заказа
		$pauseDuration = self::getOrderPauseDuration($order->OID, $tracks, $dateCheck);
		//новая дата приемки заказа, с учетом паузы
		$dateCheck = $dateCheck + $pauseDuration;
		return $dateCheck + (int)App::config("kwork.autoaccept_days") * Helper::ONE_DAY;
	}

	/**
	 * Заказчик отправил заказ в арбитраж
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Аргументы для перевода в арбитраж
	 * @param ?int $articleId Идентификатор статьи базы знаний (определяет тему, по которой пользователь обращается в Арбитраж)
	 * @param ?int $roleId Идентификатор роли базы знаний (пользователь обратился в Арбитраж как продавец или покупатель)
	 * @param array $stageIds Массив идентификаторов выбранных этапов для арбитража
	 *
	 * @return bool
	 */
	public static function payer_check_arbitrage($orderId, $message, $articleId = null, $roleId = null, $stageIds = []) {
		if (!App::config("arbitrage.enable")) {
			return false;
		}

		// заказ
		{
			$order = Order::find($orderId);

			$allow = $order->status == OrderManager::STATUS_CHECK;
			if (!$allow) {
				return false;
			}
		}

		self::setArbitrage($order->OID);

		$trackId = TrackManager::create($order->OID, "payer_check_arbitrage", $message, null,
			null, null, null, null, null, null, $articleId, $roleId);

		self::arbitrageFileAttachProcess($trackId);

		return true;
	}

	/**
	 * Заказчик отправил заказ в арбитраж
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Аргументы для отправки в арбитраж
	 * @param ?int $articleId Идентификатор статьи базы знаний (определяет тему, по которой пользователь обращается в Арбитраж)
	 * @param ?int $roleId Идентификатор роли базы знаний (пользователь обратился в Арбитраж как продавец или покупатель)
	 * @param array $stageIds Массив идентификаторов выбранных этапов для арбитража
	 *
	 * @return bool
	 */
	public static function payer_inprogress_arbitrage($orderId, $message, $articleId = null, $roleId = null, $stageIds = []) {
		if (!App::config("arbitrage.enable")) {
			return false;
		}

		// заказ
		{
			$order = Order::find($orderId);

			$allow = $order->status == OrderManager::STATUS_INPROGRESS;
			if (!$allow) {
				return false;
			}
		}

		self::setArbitrage($order->OID);

		$trackId = TrackManager::create($order->OID, "payer_inprogress_arbitrage", $message, null,
			null, null, null, null, null, null, $articleId, $roleId);
		self::arbitrageFileAttachProcess($trackId);


		return true;
	}

	/**
	 * Процесс прикрепления к треку арбитража загруженных файлов
	 * @param int $trackId Идентификатор трека
	 */
	public static function arbitrageFileAttachProcess($trackId) {
		$arbitrageFiles = post("conversations-files");
		if ($trackId && !empty($arbitrageFiles)) {
			$newArbitrageFiles = !empty($arbitrageFiles["new"]) ? $arbitrageFiles["new"] : [];
			FileManager::saveFiles($trackId, $newArbitrageFiles, FileManager::ENTITY_TYPE_TRACK);

			$deleteArbitrageFiles = !empty($arbitrageFiles["delete"]) ? $arbitrageFiles["delete"] : [];
			FileManager::deleteEntityFilesByType($trackId, $deleteArbitrageFiles, [FileManager::ENTITY_TYPE_TRACK]);
		}
	}


	/**
	 * Покупатель принял работу
	 *
	 * @param int $orderId Идентификатор заказа заказа
	 * @param bool $allowPortfolio Решение покупателя разрешено ли публиковать портфолио
	 *
	 * @return bool
	 */
	public static function payer_check_done($orderId, $allowPortfolio = false) {
		global $actor;

		if (!$actor || !$orderId) {
			return false;
		}
		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::APPROVE_ORDER, $actor->id));

		// заказ
		$order = Order::find($orderId);
		if (!$order) {
			return false;
		}
		$allow = $actor->id == $order->USERID && $order->status == OrderManager::STATUS_CHECK;
		if (!$allow) {
			return false;
		}

		if ($order->has_stages) {
			throw new RuntimeException("Для принятия этапов необходимо использовать ApproveStagesController");
		}

		$trackId = TrackManager::create($order->OID, Type::PAYER_CHECK_DONE);

		self::setDone($order);

		//Если по заказу есть незавершенный арбитраж, удаляем его
		self::doneOrderRemoveArbitrage($order->OID);

		return true;
	}


	/**
	 * Покупатель отклонил работу
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Сообщение
	 *
	 * @return bool
	 */
	public static function payer_check_inprogress($orderId, string $message = "") {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заказ
		$order = DB::selectOne("SELECT 
											OID as 'id', 
											USERID as 'userId', 
											PID as 'kworkId', 
											status, 
											duration, 
											price as 
											totalprice, 
											worker_id as 'workerId',
											has_stages 
										FROM orders WHERE OID = :order_id", ["order_id" => $orderId]);

		if (!$order) {
			return false;
		}
		$allow = $actor->id == $order->userId && $order->status == OrderManager::STATUS_CHECK;
		if (!$allow) {
			return false;
		}

		// заявка
		$typesToCheck = [
			Type::WORKER_INPROGRESS_CHECK,
			Type::ADMIN_ARBITRAGE_CHECK,
		];
		$track = Track::where(Track::FIELD_ORDER_ID, $order->id)
			->whereIn(Track::FIELD_TYPE, $typesToCheck)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->first();
		if (!$track) {
			return false;
		}

		self::setInprogress($order->id, $order->kworkId, $order->workerId);

		if ($order->has_stages) {
			throw new RuntimeException("Для принятия этапов необходимо использовать ApproveStagesController");
		}

		$trackId = TrackManager::create($order->id, Type::PAYER_CHECK_INPROGRESS, $message);

		// закрываем активные запросы
		$typesToClose = [
			Type::WORKER_INPROGRESS_CHECK,
			Type::ADMIN_ARBITRAGE_CHECK,
			Type::PAYER_INPROGRESS_CANCEL_REQUEST,
			Type::WORKER_INPROGRESS_CANCEL_REQUEST,
		];
		Track::where(Track::FIELD_ORDER_ID, $order->id)
			->whereIn(Track::FIELD_TYPE, $typesToClose)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->update([
				Track::FIELD_STATUS => TrackManager::STATUS_CLOSE,
			]);

		return true;
	}

	/**
	 * Покупатель отменил заказ, если просрочен или заказал по ошибке
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Текст сообщения
	 * @param string $reasonType Причина отмены
	 *
	 * @return bool|int
	 */
	public static function payer_inprogress_cancel($orderId, $message, $reasonType) {
		global $conn;

		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заказ

		$order = Order::find($orderId);
		if (!$order) {
			return false;
		}

		if (!TrackManager::checkCancelTrackStatus(Type::PAYER_INPROGRESS_CANCEL, $order, $reasonType)) {
			return false;
		}

		$trackId = TrackManager::create($order->OID, Type::PAYER_INPROGRESS_CANCEL, $message, $reasonType, null, null, $order->USERID, $order->PID);

		if ($order->isCancelAsDone()) {
			self::setDone($order);
		} else {
			self::setCancel($order->OID, $order->PID);
		}

		return $trackId;
	}

	/**
	 * Покупатель согласился на обоюдную отмену заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $replyType Согласен ли покупатель с причиной отмены agree|disagree
	 *
	 * @return bool
	 * @throws Throwable
	 * @throws \Exception\FactoryIllegalTypeException
	 */
	public static function payer_inprogress_cancel_confirm($orderId, $replyType): bool {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заявка
		$track = Track::where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->first();
		if (!$track) {
			return false;
		}

		// заказ
		$order = Order::find($orderId);

		$allow = $order->USERID == $actor->id && $order->isCancelAvailableForWorker();
		if (!$allow) {
			return false;
		}

		TrackManager::close($track->MID);
		//если заказ с этапами, то выводим комменатрий из запроса отмены заказа
		if ($track->reason_type == \TrackManager::REASON_TYPE_WORKER_DISAGREE_STAGES) {
			$message = $track->message;
		} else {
			$message = "";
		}
		//создаем трек с подтверждением отказа заказа
		$trackId = TrackManager::create($orderId, Type::PAYER_INPROGRESS_CANCEL_CONFIRM, $message, $track->reason_type, $replyType, null, $order->USERID, $order->PID);

		if ($order->isCancelAsDone()) {
			self::setDone($order);
		} else {
			self::setCancel($orderId, $order->PID);
		}

		return true;
	}

	/**
	 * Покупатель удалил свой запрос на обоюдную отмену заказа
	 * @param int $orderId Идентификатор заказа
	 * @return bool
	 */
	public static function payer_inprogress_cancel_delete($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заявка
		{
			$track = Track::where(Track::FIELD_ORDER_ID, $orderId)
				->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
				->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
				->first();

			if (!$track) {
				return false;
			}
		}

		// заказ
		{
			$order = $track->order;

			$allow = $order->USERID == $actor->id && $order->isCancelAvailableForPayer();
			if (!$allow) {
				return false;
			}
		}

		TrackManager::close($track->MID);

		TrackManager::create($order->OID, Type::PAYER_INPROGRESS_CANCEL_DELETE);

		return true;
	}

	/**
	 * Покупатель не согласился на обоюдную отмену заказа
	 * @param int $orderId Идентификатор заказа
	 * @return bool
	 */
	public static function payer_inprogress_cancel_reject($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заявка
		/** @var Track $track */
		$track = Track::query()
			->where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->first();

		// заказ
		$order = $track->order;
		$allow = $order->USERID == $actor->id && $order->isCancelAvailableForWorker();
		if (!$allow) {
			return false;
		}

		if ($order->in_work == 0) {
			OrderManager::updateInprogress((int)$order->OID, Type::WORKER_INPROGRESS_CANCEL_REQUEST);
		}

		TrackManager::close($track->MID);

		$trackId = TrackManager::create($order->OID, Type::PAYER_INPROGRESS_CANCEL_REJECT);

		return true;
	}

	/**
	 * Покупатель запросил обоюдную отмену заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Текст комментария
	 * @param string $reasonType Тип причины из ENUM
	 *
	 * @return bool|int Идентификатор созданного трека или false
	 * @throws \Exception
	 */
	public static function payer_inprogress_cancel_request($orderId, $message, $reasonType) {
		$currentUserId = UserManager::getCurrentUserId();

		if (!$currentUserId || !$orderId) {
			return false;
		}

		// заказ
		$order = Order::find($orderId);
		if (!$order) {
			return false;
		}
		$allow = $order->USERID == $currentUserId && $order->isCancelAvailableForPayer();
		if (!$allow) {
			return false;
		}

		// заявка
		$track = TrackManager::getNewTrackByType($orderId, Type::PAYER_INPROGRESS_CANCEL_REQUEST);
		if ($track) {
			return false;
		}

		// закрываем активные запросы
		TrackManager::closeCancelRequestsTracks($orderId);

		$trackId = TrackManager::create($orderId, Type::PAYER_INPROGRESS_CANCEL_REQUEST, $message, $reasonType);

		return $trackId;
	}

	/**
	 * Покупатель оплачивает новый заказ
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @throws \Exception
	 */
	public static function payer_new_inprogress($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			throw new RuntimeException("No actor or order id");
		}

		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::ORDER, $orderId));

		// заказ
		$order = Order::find($orderId);
		if (empty($order)) {
			throw new RuntimeException("Order not found");
		}

		if (!$order->isPayer($actor->id)) {
			throw new RuntimeException("Not order payer");
		}

		if (!$order->isNew()) {
			throw new \Order\Exception\AlreadyPaidException($orderId);
		}

		$currencyId = \Translations::getCurrencyIdByLang($actor->lang);
		$currencyRate = $order->currency_rate;
		$convertedOrderPrice = $order->payer_amount;

		// Дополнительная блокировка по идентификатору пользователя,
		// чтобы по один пользователь не мог выполнить одновременную оплату нескольких заказов
		$userLock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::REFILL_USER, $actor->id));

		UserManager::refreshActorTotalFunds();
		if ($actor->totalFunds < $convertedOrderPrice) {
			throw new BalanceDeficitWithOrderException($convertedOrderPrice - $actor->totalFunds, $orderId);
		}

		$operation = OperationManager::orderOutOperation($convertedOrderPrice, $order->OID, 0, $currencyId, $currencyRate, $order->currency_id, $orderStageId);
		if (!$operation) {
			throw new RuntimeException("Order out operation fail");
		}

		$workerLang = $order->worker->lang;
		$payerLang = $order->payer->lang;
		$orderLang = \Translations::getLangByCurrencyId($currencyId);

		self::setInprogress($order->OID, $order->PID, $order->worker_id);

		$order->stime = time();
		$order->save();

		TrackManager::create($order->OID, Type::PAYER_NEW_INPROGRESS);

		// Выполняем необходимые действия при первом переходе заказа в статус "В работе", уведомления и т.д.
		$order->worker()->update([User::FIELD_IS_ORDERS => 1]);

		// если актор Продавец, то делаем его Покупателем
		if ($actor->type == UserManager::TYPE_WORKER) {
			UserManager::changeUserType();
		}

	}

	/**
	 * Исполнитель отправил заказ в арбитраж
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Аргументы для создания арбитража
	 * @param ?int $articleId Идентификатор статьи базы знаний (определяет тему, по которой пользователь обращается в Арбитраж)
	 * @param ?int $roleId Идентификатор роли базы знаний (пользователь обратился в Арбитраж какпродавец или покупатель)
	 * @param array $stageIds Массив идентификаторов выбранных этапов для арбитража
	 *
	 * @return int|false
	 */
	public static function worker_check_arbitrage($orderId, $message, $articleId = null, $roleId = null, $stageIds = []) {
		if (!App::config("arbitrage.enable")) {
			return false;
		}

		// заказ
		{
			$order = Order::find($orderId);

			$allow = $order->status == OrderManager::STATUS_CHECK;
			if (!$allow) {
				return false;
			}
		}

		Track::where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CHECK)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->update([Track::FIELD_STATUS => TrackManager::STATUS_CLOSE]);

		self::setArbitrage($order->OID);

		$trackId = TrackManager::create($order->OID, "worker_check_arbitrage", $message, null,
			null, null, null, null, null, null, $articleId, $roleId);
		self::arbitrageFileAttachProcess($trackId);

		return $trackId;
	}

	/**
	 * Исполнитель отправил заказ в арбитраж
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Аргументы для подачи в арбитраж
	 * @param ?int $articleId Идентификатор статьи базы знаний (определяет тему, по которой пользователь обращается в Арбитраж)
	 * @param ?int $roleId Идентификатор роли базы знаний (пользователь обратился в Арбитраж как продавец или покупатель)
	 * @param array $stageIds Массив идентификаторов выбранных этапов для арбитража
	 *
	 * @return int|false
	 */
	public static function worker_inprogress_arbitrage($orderId, $message, $articleId = null, $roleId = null, $stageIds = []) {
		if (!App::config("arbitrage.enable")) {
			return false;
		}

		// заказ
		{
			$order = Order::find($orderId);

			$allow = $order->status == OrderManager::STATUS_INPROGRESS && $order->date_check != false;
			if (!$allow) {
				return false;
			}
		}

		self::setArbitrage($order->OID);

		$trackId = TrackManager::create($order->OID, "worker_inprogress_arbitrage", $message, null,
			null, null, null, null, null, null, $articleId, $roleId);
		self::arbitrageFileAttachProcess($trackId);

		return $trackId;
	}

	/**
	 * Продавец вынужденно отменил заказ
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Тест сообщения
	 * @param string $reasonType Причина отмены
	 *
	 * @return bool
	 */
	public static function worker_inprogress_cancel($orderId, $message, $reasonType) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId || !$message) {
			return false;
		}

		// заказ
		$order = Order::find($orderId);
		if (!$order) {
			return false;
		}

		$allow = $actor->id == $order->worker_id && $order->isCancelAvailableForWorker();
		if (!$allow) {
			return false;
		}

		TrackManager::create($orderId, Type::WORKER_INPROGRESS_CANCEL, $message, $reasonType, null, null, $order->USERID, $order->PID);

		if ($order->isCancelAsDone()) {
			self::setDone($order);
		} else {
			self::setCancel($orderId, $order->PID);
		}

		return true;
	}

	/**
	 * Продавец согласился на обоюдную отмену заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return bool
	 */
	public static function worker_inprogress_cancel_confirm($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}

		// заявка
		$track = Track::where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->first();
		if (!$track) {
			return false;
		}

		// заказ
		$order = Order::find($orderId);
		$allow = $actor->id == $order->worker_id && $order->isCancelAvailableForPayer();
		if (!$allow) {
			return false;
		}

		TrackManager::close($track->MID);
		$trackId = TrackManager::create($orderId, Type::WORKER_INPROGRESS_CANCEL_CONFIRM, $track->message, $track->reason_type, null, null, $order->USERID, $order->PID);

		if ($order->isCancelAsDone()) {
			self::setDone($order);
		} else {
			self::setCancel($orderId, $order->PID);
		}

		return true;
	}

	/**
	 * Продавец удалил свой запрос на обоюдную отмену заказа
	 * @param $orderId
	 * @return bool
	 */
	public static function worker_inprogress_cancel_delete($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}
		// заявка
		{
			$track = Track::where(Track::FIELD_ORDER_ID, $orderId)
				->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CANCEL_REQUEST)
				->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
				->first();
			if (!$track) {
				return false;
			}
		}
		// заказ
		{
			$order = $track->order;
			$allow = $order->worker_id == $actor->id && $order->isCancelAvailableForWorker();
			if (!$allow) {
				return false;
			}
		}

		TrackManager::close($track->MID);
		TrackManager::create($order->OID, Type::WORKER_INPROGRESS_CANCEL_DELETE);
		return true;
	}

	/**
	 * Продавец не согласился на обоюдную отмену заказа
	 * @param int $orderId Идентификатор заказа
	 * @return bool
	 */
	public static function workerInprogressCancelReject($orderId) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return false;
		}
		// заявка
		/** @var Track $track */
		$track = Track::query()
			->where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->first();
		if (!$track) {
			return false;
		}
		// заказ
		$order = $track->order;
		$allow = $order->worker_id == $actor->id && $order->isCancelAvailableForPayer();
		if (!$allow) {
			return false;
		}

		if ($order->in_work == 0) {
			OrderManager::updateInprogress((int)$order->OID, Type::PAYER_INPROGRESS_CANCEL_REQUEST);
		}

		TrackManager::close($track->MID);

		$trackId = TrackManager::create($order->OID, Type::WORKER_INPROGRESS_CANCEL_REJECT);
	}

	/**
	 * Продавец запросил обоюдную отмену заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Текст комментария
	 * @param string $reasonType Причина отмены из ENUM
	 *
	 * @return bool|int
	 * @throws \Exception
	 */
	public static function worker_inprogress_cancel_request($orderId, $message, $reasonType) {
		$currentUserId = UserManager::getCurrentUserId();

		if (!$currentUserId || !$orderId || !$message) {
			return false;
		}

		// заказ
		$order = Order::find($orderId);
		if (!$order) {
			return false;
		}
		$allow = $order->worker_id == $currentUserId && $order->isCancelAvailableForWorker();
		if (!$allow) {
			return false;
		}

		// заявка
		$track = TrackManager::getNewTrackByType($orderId, Type::WORKER_INPROGRESS_CANCEL_REQUEST);
		if ($track) {
			return false;
		}

		// закрываем активный запрос от Покупателя
		TrackManager::closeCancelRequestsTracks($orderId);

		$trackId = TrackManager::create($orderId, Type::WORKER_INPROGRESS_CANCEL_REQUEST, $message, $reasonType);

		return $trackId;
	}

	/**
	 * Продавец отправил работу на проверку
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Текст к отправке на проверку
	 * @param array $ordersStageIds Массив идентификаторов этапов которые сдаются на проверку
	 *
	 * @return int|false
	 */
	public static function worker_inprogress_check($orderId, $message = null, array $ordersStageIds = []) {
		global $conn;
		global $actor;

		if (!$actor || !$orderId) {
			return false;
		}

		// заказ
		$order = $conn->getEntity("SELECT OID as 'id', PID as 'kworkId', status, worker_id as 'workerId', has_stages, show_as_inprogress_for_worker, USERID FROM orders WHERE OID = '" . mres($orderId) . "'");
		if (!$order) {
			return false;
		}

		$allow = $order->status == OrderManager::STATUS_INPROGRESS ||
			$order->status == OrderManager::STATUS_CHECK && $order->show_as_inprogress_for_worker;
		if (!$allow) {
			return false;
		}

		$disallow = $actor->id != $order->workerId;
		if ($disallow) {
			return false;
		}

		if ($order->has_stages) {
			if (empty($ordersStageIds)) {
				return false;
			}

			$orderStages = OrderStage::where(OrderStage::FIELD_ORDER_ID, $orderId)
				->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED)
				->where(OrderStage::FIELD_PROGRESS, "!=", OrderStage::PROGRESS_FULL)
				->whereKey($ordersStageIds)
				->get();

			if (count($ordersStageIds) != count($orderStages)) {
				return false;
			}

			foreach ($orderStages as $orderStage) {
				$orderStage->progress = OrderStage::PROGRESS_FULL;
				$orderStage->check_date = Helper::now();
				$orderStage->save();
			}
		}

		if ($order->status != OrderManager::STATUS_CHECK) {
			self::setCheck($order->id, $order->kworkId, $order->workerId);
		}

		if (!$order->has_stages) {
			// закрываю предыдущий трек отправки на проверку, если есть активный
			$track = $conn->getEntity("SELECT MID as 'id' FROM track WHERE OID = '" . mres($order->id) . "' and type = 'worker_inprogress_check' and status = 'new' LIMIT 1");
			if ($track) {
				TrackManager::close($track->id);
			}
		}

		$trackId = TrackManager::create($order->id, "worker_inprogress_check", $message);
		
		return $trackId;
	}

	// Продавец взял заказ в работу
	public static function worker_inwork($orderId) {
		global $conn;
		global $actor;

		if (!$actor || !$orderId) {
			return false;
		}

		// заказ
		$order = $conn->getEntity("SELECT OID as 'id', PID as 'kworkId', status, in_work,stime, deadline, duration, source_type, has_stages, has_payer_stages FROM orders WHERE OID = '" . mres($orderId) . "'");
		if (!$order) {
			return false;
		}
		$allow = $order->status == self::STATUS_INPROGRESS && $order->in_work == 0;
		if (!$allow) {
			return false;
		}

		// кворк
		$kwork = $conn->getEntity("SELECT PID as 'id', USERID as 'userId' FROM posts WHERE PID = '" . mres($order->kworkId) . "'");
		$disallow = $actor->id != $kwork->userId;
		if ($disallow) {
			return false;
		}

		//последний трек
		$lastTrack = $conn->getEntity("SELECT MID as 'id', type FROM track WHERE OID = '" . mres($order->id) . "' AND type = 'worker_inprogress_cancel_request' AND status = 'new' ORDER BY MID DESC LIMIT 1");

		if ($lastTrack) {
			return false;
		}

		$fieldsOrderUpdate = [
			self::F_IN_WORK => 1,
		];
		App::pdo()->update(self::TABLE_NAME, $fieldsOrderUpdate, self::F_OID . " = :" . self::F_OID, [self::F_OID => $orderId]);

		$trackId = TrackManager::create($order->id, Type::WORKER_INWORK);

		return true;
	}

	/**
	 * Получить идентификатор категории для кастомного кворка, взяв категорию запроса на бирже,
	 * в ответ на который был создан данный кворк (если есть)
	 *
	 * @param int $kworkId идентификатор кворка
	 * @return int|false идентификатор категории или false
	 */
	public static function getRealKworkCategoryId($kworkId) {
		if (!$kworkId) {
			return false;
		}
		return App::pdo()->fetchScalar("
				SELECT w.category_id
				FROM offer o
				JOIN want w ON w.id = o.want_id
				WHERE o.kwork_id = :kworkId 
			", [
			"kworkId" => $kworkId
		]);
	}

	/**
	 * Зачисление оплаты Продавцу и Системе
	 *
	 * @param Order $order Модель заказа
	 * @param OrderStage $stage Этап который оплачиваем
	 */
	public static function refill(Order $order, OrderStage $stage = null) {
		if ($order->has_stages && is_null($stage)) {
			// Защита от неправильного использования метода
			throw new RuntimeException("Необходимо указать этап для оплаты поэтапного заказа");
		}

		$orderStageId = $stage->id;

		// Цена которую должен уплатить покупатель (цена заказа или этапа)
		$payerPrice = $order->price;
		$convertedPayerPrice = $order->payer_amount;
		if ($stage) {
			$payerPrice = $stage->payer_price;
			$convertedPayerPrice = $stage->payer_amount;
		}

		// проверка: Покупателем должна быть уплачена стоимость заказа
		{
			$operationPayedSum = Operation::getOrderPayedSum($order->OID, $orderStageId);
			if (bccomp($operationPayedSum, $convertedPayerPrice, 2) === -1) {
				Log::daily($operationPayedSum . " " . $convertedPayerPrice, "error");
				throw new RuntimeException("Не удалось найти операции оплаты покупателем");
			}
		}

		// Валюта заказа
		$orderCurrency = $order->currency_id;

		// начислить деньги продавцу
		{
			$workerAmount = $order->worker_amount;
			if ($stage) {
				$workerAmount = $stage->worker_amount;
			}
			$payerAmount = $order->payer_amount;


			$order->worker()->increment(UserManager::FIELD_FUNDS, $workerAmount);

			$workerCurrency = Translations::getCurrencyIdByLang($order->worker->lang);
			$currencyRate = $workerAmount / $order->crt;
			if ($stage) {
				$currencyRate = $stage->currency_rate;
			} else {
				if ($order->currency_id != \Model\CurrencyModel::RUB && $currencyRate == 1) {
					// Если валюта заказа не рубли и совпадает с валютой продавца, попробуем определить курс через покупателя
					$currencyRate = $payerAmount / $order->price;
				}
				if ($order->currency_id != \Model\CurrencyModel::RUB && $currencyRate == 1) {
					// Если валюта заказа не рубли и совпадает с валютой продавца и покупателя, возьмём актуальный курс
					$currencyRate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($workerCurrency);
				}
			}
			$operationLang = OperationLanguageManager::detectLang("order_in", $workerCurrency, $orderCurrency);
			$langAmount = OperationLanguageManager::getAmountByType("order_in", $workerAmount, $workerCurrency, $operationLang, $currencyRate);

			$operation = new Operation();
			$operation->user_id = $order->worker_id;
			$operation->type = OperationManager::TYPE_ORDER_IN;
			$operation->amount = $workerAmount;
			$operation->base_amount = $workerAmount;
			$operation->order_id = $order->OID;
			$operation->status = OperationManager::FIELD_STATUS_DONE;
			$operation->date_done = Helper::now();
			$operation->currency_id = $workerCurrency;
			$operation->currency_rate = $currencyRate;
			$operation->order_stage_id = $orderStageId;
			$operation->lang = $operationLang;
			$operation->lang_amount = $langAmount;
			$operation->save();
		}

		if ($stage) {
			$stage->status = OrderStage::STATUS_PAID;
			$stage->paid_date = Helper::now();
			$stage->progress = OrderStage::PROGRESS_FULL;
			$stage->save();

			// Установить аткуальную цену по этапам в замисимости от состояния заказа
			$order->setStagesPrice();
			$order->save();
		}

		// реферальная программа
		{
			ReferalManager::refill($order->USERID, $order->OID, UserManager::TYPE_PAYER, $orderCurrency, $orderStageId);

			ReferalManager::refill($order->worker_id, $order->OID, UserManager::TYPE_WORKER, $orderCurrency, $orderStageId);
		}

		// увеличиваем сумму платежей по кворку
		$order->kwork()->increment(Kwork::FIELD_REV, $payerPrice);

		// Увеличиваем сумму used покупателя
		UserData::whereKey($order->USERID)
			->increment(UserData::FIELD_USED, $convertedPayerPrice);
	}

	/**
	 * Возврат оплаты заказа
	 * Проведен минимальный рефакторинг чтобы можно было запускать метод в транзации - все выполняемые в нем запросы через Core\DB\DB
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param float|int $limit Лимит возврата (указывать количество в валюте операции, а не в валюте заказа)
	 * @param bool $allowInprogress Разрешено ли возвращать в случае если заказ находится в статусе "В работе"
	 * @param int $abusePoints Штрафные баллы
	 * @param int $extraId Идентификатор опции возврат за которую производится
	 * @param float $currencyRate Курс валюты к рублю
	 * @param int|null $orderStageId Идентификатор этапа
	 *
	 * @return int|bool
	 */
	public static function refund($orderId, $limit = 0, $allowInprogress = false, $abusePoints = 0, $extraId = 0, $currencyRate = 0.0, $orderStageId = null) {
		if (!$orderId)
			return false;

		// есть ли ограничеие на сумму возврата
		$enableLimit = $limit > 0;

		// заказ
		{
			$order = Order::find($orderId);
			if (!$order)
				return false;

			$allow = $order->status == 3 || $order->status == 2 && $enableLimit || $allowInprogress;
			if (!$allow)
				return false;
		}

		$refundAmountArray = [];
		$refundOrder = array_reverse(OperationManager::getAccountOrder());

		$query = "SELECT
					  sum(" . OperationManager::FIELD_AMOUNT . ") " . UserManager::FIELD_FUNDS . ",
					  0 " . UserManager::FIELD_BFUNDS . ",
					  0 " . UserManager::FIELD_BILL_FUNDS . ",
					  0 " . UserManager::FIELD_CARD_FUNDS . "
					FROM " . OperationManager::TABLE_NAME . "
					WHERE " . OperationManager::FIELD_ORDER_ID . " = :order_id
					  AND " . OperationManager::FIELD_TYPE . " IN :types
					  AND " . OperationManager::FIELD_STATUS . " = 'done'
					  AND " . OperationManager::FIELD_IS_TIPS . " = 0";

		$orderOutAmount = (array)DB::selectOne(str_replace(':types', "('order_out', 'order_out_bonus', 'order_out_bill')", $query), [
			"order_id" => $order->OID
		]);
		$refundAmount = (array)DB::selectOne(str_replace(':types', "('refund')", $query), [
			"order_id" => $order->OID
		]);

		foreach ($refundOrder as $account) {
			$backAmount = $orderOutAmount[$account] - $refundAmount[$account];
			if ($backAmount > 0) {
				$refundAmountArray[] = [
					'account' => $account,
					'backAmount' => $backAmount
				];
			}
		}

		// если есть ограничеие на сумму возврата
		if ($enableLimit) {
			foreach ($refundAmountArray as &$data) {
				if ($data['backAmount'] > 0) {
					$data['backAmount'] = $limit > 0 ? min($data['backAmount'], $limit) : 0;
					$limit -= $data['backAmount'];
				}
			}
			unset($data);
		}

		// есть ли сумма к возврату
		$exist = false;
		foreach ($refundAmountArray as $data) {
			if ($data['backAmount'] > 0) {
				$exist = true;
			}
		}
		if (!$exist) {
			Log::daily("OrderId {$orderId}. Empty refundAmountArray when check backAmount.", 'error');
			return false;
		}

		$cardFundsTransferAmount = 0;
		if (empty($refundAmountArray)) {
			Log::daily("OrderId {$orderId}. Empty refundAmountArray.", 'error');
			return false;
		} else {
			$updateFields = []; // Поля обновления баланса
			foreach ($refundAmountArray as $data) {
				if ($data['backAmount'] > 0) {
					$updateFields[$data["account"]] = DB::raw($data["account"] . " + " . (float)$data["backAmount"]);

					if ($data['account'] == UserManager::FIELD_CARD_FUNDS) {
						$cardFundsTransferAmount = $data['backAmount'];
					}
				}
			}
			$order->payer()->update($updateFields);
		}

		$currencyId = \Translations::getCurrencyIdByLang($order->payer->lang);
		$refundAmount = array_sum(array_column($refundAmountArray, 'backAmount'));
		if (empty($currencyRate)) {
			if ($currencyId == $order->currency_id && $currencyId == \Model\CurrencyModel::USD) {
				// Если валюта покупателя и валюта заказа - USD, то используем текущий курс
				$currencyRate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($currencyId);
			} elseif ($currencyId == $order->currency_id && $currencyId == \Model\CurrencyModel::RUB) {
				// Если валюта покупателя и валюта заказа - RUB, то курс = 1
				$currencyRate = 1;
			} else {
				// Иначе - определим курс через payer_amount
				$refundPercent = (($refundAmount * 100) / $order->payer_amount) / 100;
				$currencyRate = $refundAmount / ($order->price * $refundPercent);
			}
		}

		$operation = new Operation();
		$operation->user_id = $order->USERID;
		$operation->type = OperationManager::TYPE_REFUND;
		$operation->amount = $refundAmount;
		$operation->status = OperationManager::FIELD_STATUS_DONE;
		$operation->order_id = $order->OID;
		$operation->date_done = date("Y-m-d H:i:s");
		$operation->currency_id = $currencyId;
		$operation->currency_rate = $currencyRate;
		$operation->lang = OperationLanguageManager::detectLang(OperationManager::TYPE_REFUND, $currencyId, $order->currency_id);
		$operation->lang_amount = OperationLanguageManager::getAmountByType(OperationManager::TYPE_REFUND, $operation->amount, $operation->currency_id, $operation->lang, $currencyRate);

		foreach ($refundAmountArray as $data) {
			if ($data['backAmount'] > 0) {
				if ($data['account'] == UserManager::FIELD_FUNDS) {
					$field = OperationManager::FIELD_BASE_AMOUNT;
				} elseif ($data['account'] == UserManager::FIELD_BFUNDS) {
					$field = OperationManager::FIELD_BONUS_AMOUNT;
				} elseif ($data['account'] == UserManager::FIELD_BILL_FUNDS) {
					$field = OperationManager::FIELD_BILL_AMOUNT;
				} elseif ($data['account'] == UserManager::FIELD_CARD_FUNDS) {
					$field = OperationManager::FIELD_CARD_AMOUNT;
				}
				$operation->{$field} = $data['backAmount'];
			}
		}
		if ($abusePoints > 0) {
			$operation->abuse_points = $abusePoints;
			$operation->abuse_points_total = DB::table(AbuseManager::TABLE_NAME)
				->where(AbuseManager::F_USER_ID, $order->worker_id)
				->where(AbuseManager::F_STATUS, AbuseManager::STATUS_ACTIVE)
				->sum(AbuseManager::F_POINT);
		}
		if ($extraId) {
			$operation->extra_id = $extraId;
		}
		if ($orderStageId) {
			$operation->order_stage_id = $orderStageId;
		}
		$operation->save();

		// Возрат денег на основной счет, если это необходимо
		$maxTransferAmount = \Model\Operation::where(\Model\Operation::FIELD_USER_ID, $order->USERID)
			->where(\Model\Operation::FIELD_STATUS, OperationManager::FIELD_STATUS_DONE)
			->where(\Model\Operation::FIELD_TYPE, \Model\Operation::TYPE_REFILL)
			->where(\Model\Operation::FIELD_CARD_AMOUNT, ">", 0)
			->where(\Model\Operation::FIELD_TRANSFERRED, OperationManager::NOT_TRANSFERED)
			->where(\Model\Operation::FIELD_DATE_CREATE, ">=", Helper::now(time() - Helper::ONE_MONTH))
			->sum(\Model\Operation::FIELD_CARD_AMOUNT);
		if ($cardFundsTransferAmount && ($cardFundsTransferAmount > $maxTransferAmount)) {
			$transferAmount = $cardFundsTransferAmount - $maxTransferAmount;
			OperationManager::refillCardFundsTransferAmount($order->USERID, $transferAmount);
			OperationManager::cardFundsTransfer($order->USERID, $transferAmount);
		}
		return $operation->id;
	}

	/**
	 * Лог изменений статусов заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $status Статус заказа
	 */
	public static function logStatus($orderId, $status) {
		$currentUserId = UserManager::getCurrentUserId();

		if (!$orderId)
			return;

		DB::table("order_log")
			->insert([
				"user_id" => $currentUserId,
				"order_id" => $orderId,
				"status" => $status,
			]);
	}

	/**
	 * Данные по заказу для рассылки
	 *
	 * @param int $orderId Идентификатор заказа
	 * @return \stdClass|null
	 */
	public static function getOrderData(int $orderId): ?\stdClass {
		$data = &kwork_static(__FUNCTION__ . $orderId);

		if (empty($data)) {
			$data = DB::table(Order::TABLE_NAME . " AS o")
				->join(Kwork::TABLE_NAME . " AS p",
					"p." . Kwork::FIELD_PID, "=", "o." . Order::FIELD_PID)
				->join(User::TABLE_NAME . " AS mo",
					"mo." . User::FIELD_USERID, "=", "o." . Order::FIELD_USERID)
				->join(User::TABLE_NAME . " AS mp",
					"mp." . User::FIELD_USERID, "=", "p." . Kwork::FIELD_USERID)
				->where("o." . Order::FIELD_OID, $orderId)
				->select([
					"o." . Order::FIELD_OID . " AS orderId",
					"o." . Order::FIELD_STATUS . " AS status",
					"o." . Order::FIELD_DATA_PROVIDED . " AS data_provided",
					"o." . Order::FIELD_KWORK_TITLE . " AS orderTitle",
					"o." . Order::FIELD_HAS_STAGES . " AS hasStages",
					"o." . Order::FIELD_RESTARTED,
					"p." . Kwork::FIELD_PID . " AS kworkId",
					"p." . Kwork::FIELD_CATEGORY . " AS kworkCategory",
					"p." . Kwork::FIELD_GTITLE . " AS kworkName",
					"p." . Kwork::FIELD_LANG . " AS kworkLang",
					"mo." . User::FIELD_USERID . " AS payerId",
					"mo." . User::FIELD_LANG . " AS payerLang",
					"mo." . User::FIELD_USERNAME . " AS payerLogin",
					DB::Raw("(CASE
							WHEN mo." . User::FIELD_FULLNAME . " = ''
							THEN mo." . User::FIELD_USERNAME . "
							ELSE mo." . User::FIELD_FULLNAME . "
						END) AS payerName"),
					"mo." . User::FIELD_EMAIL . " AS payerEmail",
					"mp." . User::FIELD_USERID . " AS workerId",
					"mp." . User::FIELD_LANG . " AS workerLang",
					"mp." . User::FIELD_USERNAME . " AS workerLogin",
					DB::Raw("(CASE
							WHEN mp." . User::FIELD_FULLNAME . " = ''
							THEN mp." . User::FIELD_USERNAME . "
							ELSE mp." . User::FIELD_FULLNAME . "
						END) AS workerName"),
					"mp." . User::FIELD_EMAIL . " AS workerEmail",
				])
				->first();

			if ($data) {
				$data->payerEmail = Crypto::decodeString($data->payerEmail);
				$data->workerEmail = Crypto::decodeString($data->workerEmail);

				// Получим пользовательские названия
				$uOrderNames = OrderNames::where(OrderNames::FIELD_ORDER_ID, "=", $data->orderId)
					->whereIn(OrderNames::FIELD_USER_ID, [$data->payerId, $data->workerId])
					->pluck(OrderNames::FIELD_ORDER_NAME, OrderNames::FIELD_USER_ID);
				$data->payerOrderName = $uOrderNames[$data->payerId] ? $uOrderNames[$data->payerId] : $data->orderTitle;
				$data->workerOrderName = $uOrderNames[$data->workerId] ? $uOrderNames[$data->workerId] : $data->orderTitle;
			}
		}

		return $data;
	}

	public static function getOpponentByOrderId(int $orderId): int {
		global $actor;
		$orderData = OrderManager::getOrderData($orderId);
		return ($actor->id == $orderData->payerId) ? $orderData->workerId : $orderData->payerId;
	}

	/**
	 * Расчёт времени выполнения заказа
	 * @param int $orderId id заказа
	 * @param \Illuminate\Database\Eloquent\Collection|\Model\Track[] $tracks
	 *
	 * @return int время выполнения заказа (количество секунд)
	 */
	public static function calculateWorkTime($orderId, Eloquent\Collection $tracks = null) {
		if (is_null($tracks)) {
			$tracks = Track::where(Track::FIELD_ORDER_ID, $orderId)
				->where(Track::FIELD_TYPE, "!=", Type::TEXT)
				->orderBy(Track::FIELD_ID)
				->get();
		}

		$startIntervalStatuses = Type::getToRunOrderTypes();
		$toPauseTypes = Type::getToPauseOrderTypes();
		$cancelTypes = Type::cancelTypes();
		$doneTypes = Type::doneTypes();

		$endIntervalStatuses = array_merge($toPauseTypes, $cancelTypes, $doneTypes);

		$resultTime = 0;
		$startTime = 0;
		$instructionProvided = false;

		// #8003 Возможна ситуация, когда в заказе нет ни одного трека, означающего
		// начало работы: покупатель создает заказ, пишет сообщение продавцу,
		// продавец отвечает, после этого у покупателя появляется возможность
		// подтвердить выполнение заказа, он его подтверждает - в этом случае
		// $resultTime будет равен 0.
		// Поэтому, если в заказе нет ни одного трека, означающего начало работы,
		// то считаем за начало работы трек payer_new_inprogress.
		{
			$startTrackFound = false;
			$trackPayerNewInprogress = null;

			foreach ($tracks as $track) {
				if (\in_array($track->type, $startIntervalStatuses, true)) {
					$startTrackFound = true;
					break;
				}

				if ($track->type === Type::PAYER_NEW_INPROGRESS) {
					$trackPayerNewInprogress = $track;
				}
			}

			if (!$startTrackFound) {
				$startTime = strtotime($trackPayerNewInprogress->date_create);
			}
		}

		foreach ($tracks as $track) {
			if (\in_array($track->type, $startIntervalStatuses, true)) {
				if (!$instructionProvided) {
					$startTime = strtotime($track->date_create);
					if ($track->type === Type::TEXT_FIRST) {
						$instructionProvided = true;
					}
				}
			} elseif (\in_array($track->type, $endIntervalStatuses, true)) {
				if ($startTime > 0) {
					if ($instructionProvided) {  //если инструкция была предоставлена, время работы исполнителя до этих пор не учитываем
						$resultTime = 0;
						$instructionProvided = false;
					}
					$resultTime += strtotime($track->date_create) - $startTime;
					$startTime = 0;
				}
			}
		}

		return $resultTime;
	}

	/**
	 * Создание заказа для пакетного кворка по данным из post
	 *
	 * @return array
	 */
	public static function api_package_create() {
		$actor = UserManager::getCurrentUser();
		if ($actor->lang != \Translations::getLang() && $actor->lang != \Translations::DEFAULT_LANG) {
			return [
				"success" => false,
				"error" => self::ERROR_WRONG_LANGUAGE,
			];
		}
		$volumeTypeId = (int)post("additional_volume_type_id");
		$result = self::packageCreate((int)post('kworkId'), post('packageType'), post('isCart'), post('isQuick'), cleanFloat(post("volume")), $volumeTypeId);
		return $result;
	}

	/**
	 * Создание заказа для пакетного кворка
	 *
	 * @param int $kworkId Идентификатор кворка
	 * @param string $packageType Тип пакета standard|medium|premium
	 * @param bool $cartMode Заказ в корзину
	 * @param bool $isQuick Заказ с опцией срочности
	 * @param float $volume Числовой объем заказа в единицах выбранных в кворке
	 * @param int $volumeTypeId Идентификатор числового объёма в котором будет создан заказ
	 *
	 * @return array ["success" => bool, "error" => string]
	 */
	public static function packageCreate($kworkId, $packageType, $cartMode = false, $isQuick = false, float $volume = 0.0, int $volumeTypeId = 0) {
		$actor = UserManager::getCurrentUser();
		$mobileApi = ApiManager::isMobileApi();

		$kworkId = (int)$kworkId;

		if (!($kworkId > 0)) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		if (!$cartMode && !$actor) {
			return [
				"success" => false,
				"error" => self::ERROR_NEED_LOGIN
			];
		}

		if (!in_array($packageType, ["standard", "medium", "premium"])) {
			return [
				"success" => false,
				"error" => self::ERROR_INCORRECT_PACKAGE_TYPE
			];
		}

		// Загружаем кворк (основные данные и пакеты) чтобы был тип числового объема, и объем пакетов в выбранных единицах
		$kwork = new KworkManager($kworkId, KworkManager::LOAD_KWORK_DATA | KworkManager::LOAD_PACKAGES);

		$stayonUnblock = false;
		// #5995 Если пользователь находится на разблокировке мы можем делать заказ
		if ($kwork->getActive() == KworkManager::STATUS_SUSPEND) {
			$stayonUnblock = \UserManager::onQueueforUnblock($kwork->get("userId"));
		}

		if (!$kwork->getId() ||
			(!in_array($kwork->getActive(), [KworkManager::STATUS_ACTIVE, KworkManager::STATUS_PAUSE]) && !$stayonUnblock) ||
			($kwork->getFeat() != KworkManager::FEAT_ACTIVE && !$stayonUnblock)
		) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		if (!$kwork->isPackage() && is_null($kwork->getStandardPackage())) {
			return [
				"success" => false,
				"error" => self::ERROR_INCORRECT_PACKAGE_TYPE
			];
		}

		$kworkCount = 1;
		$packageData = $kwork->get($packageType . "Package");
		$kworkOrderPrice = $packageData["price"];

		if (App::config(Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS) &&
			$kwork->getVolume() &&
			App::config("order.volume_max_total_" . $kwork->getLang()) &&
			$kwork->getVolumeType()) {
			// Подготовим данные по объёму и кол-ву для заказа с числовым объёмом
			list($orderPrice, $kworkCount, $customVolume, $volume, $volumeTypeId, $orderDuration) = OrderVolumeManager::prepareVolumeForOrderCreate($kwork, $kworkCount, $volume, $volumeTypeId, $packageData);
		} else {
			$volume = 0;
			$orderPrice = $kworkOrderPrice * $kworkCount;
			// Срок выполнения по кол-ву кворков
			$orderDuration = self::getDuration($packageData["duration"], $kworkCount, $kwork->getCategory());
		}

		$orderData = [
			"kworkId" => $kworkId,
			"kworkCount" => $kworkCount,
			"packageType" => $packageType,
			"volume" => $volume,
			"volumeTypeId" => $volumeTypeId,
		];

		if (App::config('module.quick.enable') && $isQuick == true) {
			if ($kwork->getLang() == Translations::DEFAULT_LANG) {
				$quickPrice = self::getQuickPrice($orderPrice, App::config('price'));
			} else {
				$quickPrice = self::getQuickPrice($orderPrice, App::config('price_en'));
			}

			$orderPrice += $quickPrice;
			$kworkOrderPrice += $quickPrice;

			$orderDuration = self::getQuickDuration($orderDuration);
			$orderData['isQuick'] = true;
		}

		$orderDuration *= Helper::ONE_DAY;

		$orderData['price'] = $orderPrice;

		$currencyId = \Translations::getCurrencyIdByLang($kwork->getLang());

		$kworkModel = Kwork::find($kwork->getId());

		if ($cartMode) {
			$orderData['timeAdded'] = time();
			if (isset($customVolume)) {
				$orderData["volume"] = $customVolume;
			}
			if (App::config('basket.enable') && App::config('redis.enable')) {
				$hash = RedisManager::getInstance()->addActor('cart', $orderData);
				CartManager::removeOverLimit();
				$data = [
					'cart_html' => CartManager::getHtml(),
					'action' => 'item_add'
				];
				\Pull\PushManager::sendToUser($actor->id, \Pull\PullEvents::UPDATE_CART, $data);

				return [
					"success" => true,
					"hash" => $hash,
					"dataLayer" => [
						GTM::getAddKworkToCart($kworkModel, $kworkCount, $kworkOrderPrice)
					],
				];
			} else {
				return [
					"success" => false
				];
			}
		}

		$currencyRate = CurrencyExchanger::getInstance()->getCurrencyRateByLang($kwork->getLang());

		$orderId = OrderManager::create($kworkId, $orderPrice, $kworkCount, $orderDuration, $packageData['duration'], $packageType, $isQuick, 0, 0, self::SOURCE_KWORK, $currencyId, $currencyRate, $volumeData);
		if (!$orderId) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$turnover = self::getTurnover($kwork->get("userId"), $actor->id, $kwork->getLang());
		$commission = self::calculateCommission($orderPrice, $turnover, $kwork->getLang());
		$crt = $commission->priceWorker / $kworkCount;
		PackageManager::createOrderPackage($orderId, $packageData, $packageType, $crt);

		$dataLayer[] = GTM::getKworkOrder($kworkModel, $kworkCount, $kworkOrderPrice);

		try {
			OrderManager::payer_new_inprogress($orderId);
			return [
				"success" => true,
				"orderId" => $orderId,
				"dataLayer" => $dataLayer,
				"redirect" => App::config('baseurl') . "/track?id=" . mres($orderId),
			];
		} catch (BalanceDeficitWithOrderException $exception) {
			Session\SessionContainer::getSession()->set("refillOrderId", $orderId);
			if (App::config('redis.enable') && App::config('basket.enable') && !$mobileApi) {
				RedisManager::getInstance()->setActor('last_order', $orderData);
				CartManager::addOrder($orderData);
				$data = [
					'cart_html' => CartManager::getHtml(),
					'action' => 'no_purse_add'
				];
				\Pull\PushManager::sendToUser($actor->id, \Pull\PullEvents::UPDATE_CART, $data);

				$dataLayer[] = GTM::getAddKworkToCart($kworkModel, $kworkCount, $kworkOrderPrice);

				// $kwork->get(Kwork::FIELD_URL) и $kwork->getUrl() могут вернуть разные значения, если gtitle и url рассинхронизированны
				$redirect = App::config('baseurl') . $kwork->get(Kwork::FIELD_URL) . '?fill=1&balance=1';
			} else {
				$redirect = App::config('baseurl') . $kwork->get(Kwork::FIELD_URL) . '?balance=1';
			}
			return [
				"success" => false,
				"error" => self::ERROR_NEED_MORE_MONEY,
				"purse_amount" => $exception->getNeedMoney(),
				"dataLayer" => $dataLayer,
				"redirect" => $redirect
			];
		} catch (Exception $exception) {
			Log::dailyErrorException($exception);
			return ["success" => false];
		}
	}

	public static function api_create($cartMode = false) {
		global $actor;
		$isQuick = post('is_quick') != "";

		if ($actor->lang != \Translations::getLang() && $actor->lang != \Translations::DEFAULT_LANG) {
			return [
				"success" => false,
				"error" => self::ERROR_WRONG_LANGUAGE,
			];
		}
		return self::createOrder((int)post("EPID"), post("kworkcnt"), [], $cartMode, $isQuick);
	}

	/**
	 * Получить время выполнения для определенного количества кворков с учетом категории
	 * Одноименная функция в fox.js
	 *
	 * @param int $days
	 * @param int $count
	 * @param Category|null $category Категория для получения множителя для времени кворка/опций
	 * @return float
	 */
	public static function getDuration($days, $count, Category $category = null): float {
		return ceil($days + ($count - 1) * $days * self::getMultiKworkRate($category));
	}

	public static function getMessageKworkTimeLeft($workDays, $deadline) {
		if (!$deadline) {
			return '';
		}

		$time = $deadline - time();
		$message = '';
		if ($time <= 0) {
			$daysMessage = declension($workDays, ["дня", "дней", "дней"]);
			$message = '<p>' . Translations::t('По условиям кворка заказ должен быть сдан в течение %s %s. Время истекло.', $workDays, $daysMessage) . '</p>'
				. '<p>' . Translations::t('Теперь покупатель в любой момент может отменить заказ, и это снизит рейтинг всех ваших кворков. Пожалуйста, свяжитесь с покупателем, сообщите ему причину задержки и согласуйте новый срок выполнения заказа.') . '</p>'
				. '<p>' . Translations::t('Настоятельно рекомендуем строго следить за сроками выполнения заказов, не допуская просрочки. Просрочка очень плохо влияет на пользовательский опыт покупателя, ведет к негативному отношению к вам как к продавцу и к Kwork как сервису.') . '</p>';
		} elseif ($workDays == 1 && $time <= 6 * Helper::ONE_HOUR) {
			$message = Translations::t('По условиям кворка заказ должен быть сдан в течение 1 дня. Постарайтесь сдать заказ вовремя, так как после истечения этого времени покупатель в любой момент сможет его отменить');
		} elseif ($workDays == 2 && $time <= 18 * Helper::ONE_HOUR) {
			$message = Translations::t('По условиям кворка заказ должен быть сдан в течение 2 дней. Постарайтесь сдать заказ вовремя, так как после истечения этого времени покупатель в любой момент сможет его отменить');
		} elseif ($workDays >= 3 && $time <= Helper::ONE_DAY) {
			$message = Translations::t('Остался 1 день на то, чтобы сдать заказ. Постарайтесь сдать заказ вовремя, так как после истечения этого времени покупатель в любой момент сможет его отменить');
		}

		return $message;
	}

	/**
	 * Установить заказ в статус "В работе"
	 *
	 * @param int $orderId ID заказа
	 * @param int $kworkId ID кворка
	 * @param int $workerId ID продавца
	 */
	public static function setInprogress($orderId, $kworkId, $workerId) {
		global $conn;

		$conn->execute("UPDATE orders SET status = " . OrderManager::STATUS_INPROGRESS . ", date_inprogress = now() WHERE OID = '" . mres($orderId) . "'");

		OrderManager::logStatus($orderId, OrderManager::STATUS_INPROGRESS);
	}

	/**
	 * Установить заказ в статус "Арбитраж"
	 *
	 * @param int $orderId ID заказа
	 */
	private static function setArbitrage($orderId) {
		global $conn;

		$conn->execute("UPDATE orders SET status = " . OrderManager::STATUS_ARBITRAGE . ", date_arbitrage = now() WHERE OID = '" . mres($orderId) . "'");
		$orderData = self::getOrderData($orderId);
		TrackManager::createAdviceTrack($orderData, 'bad_review');
		OrderManager::logStatus($orderId, OrderManager::STATUS_ARBITRAGE);
	}

	/**
	 * Перевод заказа в состояние "Требуется оплата"
	 *
	 * @param Order $order
	 */
	public static function setUnpaid(Order $order) {
		$order->status = OrderManager::STATUS_UNPAID;
		$order->setStagesPrice();
		$order->save();
		OrderManager::logStatus($order->OID, OrderManager::STATUS_UNPAID);
	}

	/**
	 * Установить заказ в статус "Отменён"
	 *
	 * @param int $orderId ID заказа
	 * @param int $kworkId ID кворка
	 * @return bool
	 * @throws Throwable
	 */
	public static function setCancel($orderId, $kworkId) {
		// заказ
		$order = Order::find($orderId);
		if (empty($order)) {
			return false;
		}

		$originalStatus = $order->status;

		$order->status = OrderManager::STATUS_CANCEL;
		$order->date_cancel = Helper::now();
		$order->save();

		OrderManager::logStatus($orderId, OrderManager::STATUS_CANCEL);

		// в track данные после изменения статуса и даты
		$track = TrackManager::getLastTrackInfo($orderId);

		// перед возвратом средств, вернём чаевые отдельной операцией, если они были
		OperationManager::refundTips($orderId);

		try {
			$operation = OrderManager::refund($orderId, 0, false, $abusePoints);
		} catch (Exception $exception) {
			// Делаем чтобы все работало как раньше - не прерывало поток выполнения в случае ошибки запроса
			Log::daily($exception->getMessage() . "\n" . $exception->getTraceAsString(), "error");
			$operation = false;
		}

		//Удаляем положительный отзыв
		{
			$review = Rating::where(Rating::FIELD_GOOD, 1)
				->where(Rating::FIELD_ORDER_ID, $orderId)
				->value(Rating::FIELD_ID);

			if ($review) {
				RatingManager::removeReviewByOrder($orderId);
			}
		}

		$autoRatingInfo = (new GetAutoRatingInfoService())
			->byTrackInfo($track);

		if ($autoMode = $autoRatingInfo->getAutoMode()) {
			RatingManager::createAuto($orderId, $autoRatingInfo->getComment(), $autoRatingInfo->getAutoMode());
		}

		TrackManager::closeCancelRequestsTracks($orderId);

		// Для поэтапных с выполненными этапами посчитаем время работы (если не особый подсчет)
		// Особый подсчет в случае если тип трека - cron_inprogress_cancel или payer_inprogress_cancel с причиной отмены payer_time_over
		$isSpecialWorkTimeCalculation = $track["type"] = Type::CRON_INPROGRESS_CANCEL ||
			($track["type"] = Type::PAYER_INPROGRESS_CANCEL && $track["reason_type"] == TrackManager::REASON_TYPE_PAYER_TIME_OVER);

		// #8168 если заказ выполнен успешно и он принадлежит запросу, это повод запрос отправить в архивный
		if (!empty($order->project_id)) {
			WantManager::checkAddArchived($order->project_id);
		}

		return true;
	}

	/**
	 * Установить заказ в статус "На проверке"
	 *
	 * @param int $orderId ID заказа
	 * @param int|null $kworkId ID кворка
	 * @param int $workerId ID продавца
	 */
	public static function setCheck($orderId, $kworkId, $workerId) {
		Order::whereKey($orderId)
			->update([
				Order::FIELD_STATUS => OrderManager::STATUS_CHECK,
				Order::FIELD_DATE_CHECK => Helper::now(),
			]);

		OrderManager::logStatus($orderId, OrderManager::STATUS_CHECK);
	}

	/**
	 * Установить заказ в статус "Выполнен"
	 *
	 * @param \Model\Order $order
	 * @throws Throwable
	 * @throws \Exception\FactoryIllegalTypeException
	 */
	public static function setDone($order) {
		$order->status = OrderManager::STATUS_DONE;
		$order->date_done = Helper::now();
		$order->save();

		OrderManager::logStatus($order->OID, OrderManager::STATUS_DONE);

		// начисление денег продавцу и системе
		try {
			OrderManager::refill($order);
		} catch (Exception $exception) {
			Log::dailyErrorException($exception);
		}

		// Обновим счётчики выполненных заказов
		self::updateOrderDoneCount($order->PID, $order->worker_id);

		// Время заказа и кворка
		$workTime = self::calculateWorkTime($order->OID);
		self::setWorkTime($order->OID, $workTime, $order->PID);

		TrackManager::closeCancelRequestsTracks($order->OID);

		// #8168 если заказ выполнен успешно и он принадлежит запросу, это повод запрос отправить в архивный
		if (!empty($order->project_id)) {
			WantManager::checkAddArchived($order->project_id);
		}
	}

	/**
	 * Обновление времени выполнения заказа.
	 *
	 * @param int $orderId Id заказа
	 * @param int $workTime Время выполнения заказа
	 * @param int $kworkId Id кворка
	 * @return void
	 */
	public static function setWorkTime($orderId, $workTime, $kworkId) {
		Order::whereKey($orderId)
			->update([Order::FIELD_WORK_TIME => $workTime]);
	}

	/**
	 * Покупатель самостоятельно докупает опции в заказ
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param array $extrasArray Массив идентификаторов и количеств заказываемых опций [id => count,...]
	 * @param bool $asVolume Флаг того что количество заказываемых опций указывается в единицах числового объема
	 *
	 * @return array
	 */
	public static function payer_buy_extras($orderId, $extrasArray, $asVolume) {
		$actor = UserManager::getCurrentUser();

		$order = Order::find($orderId);
		if ($order->has_stages && $order->stages && $order->stages->count() > 1) {
			throw new SimpleJsonException(Translations::t("Покупка опций в заказах с этапами запрещена"));
		}

		$orderData = ODM::get($orderId, [ODM::FIELD_VOLUME_TYPE_ID, ODM::FIELD_VOLUME, ODM::FIELD_KWORK_VOLUME]);

		$extras = \Model\Extra::whereIn(\Model\Extra::FIELD_ID, array_keys($extrasArray))
			->get()
			->keyBy(\Model\Extra::FIELD_ID);

		$orderedExtras = \Model\OrderExtra::where(\Model\OrderExtra::FIELD_ORDER_ID, $orderId)
			->where(\Model\OrderExtra::FIELD_STATUS, \Model\OrderExtra::STATUS_DONE)
			->groupBy(\Model\OrderExtra::FIELD_EXTRA_ID)
			->selectRaw("SUM(" . \Model\OrderExtra::FIELD_COUNT . ") as cnt, SUM(" . \Model\OrderExtra::FIELD_EXTRA_DURATION . ") as sum_duration")
			->addSelect([
				\Model\OrderExtra::FIELD_EXTRA_ID
			])
			->get()
			->keyBy(\Model\OrderExtra::FIELD_EXTRA_ID);

		$plusPrice = 0;
		foreach ($extrasArray as $extraId => $count) {
			if ($orderData->{ODM::FIELD_VOLUME} && $orderData->{ODM::FIELD_VOLUME_TYPE_ID} && $extras[$extraId]->{ExtrasManager::F_IS_VOLUME}) {
				// Если кворк был куплен как числовой с заданным объемом, то обрабатываем докупку объема как увеличение заказанного числового объема
				if ($asVolume) {
					$extras[$extraId]->volume = $count;
				} else {
					// Если не указано явно что как числовой объем использовать значение - поддерживаем совместимость, заказываем в кворках и ставим объем равный объему кворка
					$extras[$extraId]->count = $count;
					$extras[$extraId]->volume = $orderData->{ODM::FIELD_KWORK_VOLUME} * $extras[$extraId]->count;
				}
				if (!is_null($extras[$extraId]->volume)) {
					$extras[$extraId]->customVolume = $extras[$extraId]->volume;
					$extras[$extraId]->volume = $order->getVolumeInCustomType($extras[$extraId]->volume);
					$extras[$extraId]->volumePrice = $order->kwork->getVolumedPrice($extras[$extraId]->volume, $extras[$extraId]->eprice);
					$plusPrice += $extras[$extraId]->volumePrice;
					$extras[$extraId]->count = ceil($extras[$extraId]->volume / $orderData->{ODM::FIELD_KWORK_VOLUME});
					// Цена опции по объёму
					$extras[$extraId]->eprice = $extras[$extraId]->volumePrice / $extras[$extraId]->count;
				} else {
					$plusPrice += $extras[$extraId]->eprice * $extras[$extraId]->count;
				}
			} else {
				$extras[$extraId]->count = $count;
				$plusPrice += $extras[$extraId]->eprice * $extras[$extraId]->count;
			}
		}

		$currencyId = $order->currency_id;
		$rate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($currencyId);
		$convertedPlusPrice = $plusPrice;
		if ($currencyId == CurrencyModel::USD && $actor->lang == \Translations::DEFAULT_LANG) {
			$currencyId = \Translations::getCurrencyIdByLang(\Translations::DEFAULT_LANG);
			$convertedPlusPrice = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$plusPrice,
				\Translations::EN_LANG,
				\Translations::DEFAULT_LANG
			);
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$lang = \Translations::getLangByCurrencyId($order->currency_id);
		$turnover = self::getTurnover($order->worker_id, $order->USERID, $lang);
		$turnover += $order->price;
		$currentTurnover = $turnover;

		$workerLang = $order->worker->lang;

		UserManager::refreshActorTotalFunds();
		if ($actor->totalFunds >= $convertedPlusPrice && $plusPrice > 0) {
			$operation = OperationManager::orderOutOperation($convertedPlusPrice, $orderId, 1, $currencyId, $rate, $order->currency_id);
			if (!$operation) {
				throw new \Mobile\Exception\Unexpected();
			}
			$plusDays = 0;
			$plusWorkerPrice = 0;
			$orderExtraIds = [];
			$increaseOrderVolume = false;
			foreach ($extras as $key => $extra) {
				if ($extra->is_volume == 1) {
					$increaseOrderVolume = $extra;
					unset($extras[$key]);
					continue;
				}

				if (!empty($orderedExtras[$extra->EID])) {
					// если покупатель уже заказывал эту опцию, то все докупаемые идут с учетом коэффициента
					$duration = ceil($extra->duration * $extra->count * self::getMultiKworkRate($order->data->category));
				} else {
					// иначе первая идет полным сроком, а остальные - с коэффициентом
					$duration = self::getDuration($extra->duration, $extra->count, $order->data->category);
				}

				$plusDays += $duration * Helper::ONE_DAY;

				$commission = self::calculateCommission($extra->eprice * $extra->count, $currentTurnover, $lang);
				$extraCtp = $commission->priceKwork / $extra->count;
				$currentTurnover += $commission->price;
				$plusWorkerPrice += $commission->priceWorker;

				$workerAmount = $commission->priceWorker;
				if ($workerLang != $lang) {
					$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
						$workerAmount,
						$lang,
						$workerLang
					);
				}

				$payerAmount = $extra->eprice * $extra->count;
				if ($actor->lang != $lang) {
					$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
						$payerAmount,
						$lang,
						$actor->lang
					);
				}

				$insertData = [
					'order_id' => $orderId,
					'extra_id' => $extra->EID,
					'count' => $extra->count,
					'extra_title' => $extra->etitle,
					'extra_price' => $extra->eprice,
					'extra_duration' => $duration,
					'extra_ctp' => $extraCtp,
					'extra_is_volume' => $extra->is_volume,
					'status' => 'done',
					\Model\OrderExtra::FIELD_CURRENCY_RATE => $rate,
					\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE => $commission->priceWorker,
					\Model\OrderExtra::FIELD_WORKER_AMOUNT => $workerAmount,
					\Model\OrderExtra::FIELD_PAYER_AMOUNT => $payerAmount,
				];
				$order_extra_id = DB::table(OrderExtraManager::TABLE_NAME)->insertGetId($insertData);
				$orderExtraIds[] = $order_extra_id;
			}
			$plusKworkCount = 0;
			$days = $order->duration;
			$days += $plusDays;
			if ($increaseOrderVolume) {
				if ($order->processLikeVolumedKwork()) {
					$oldVolume = $orderData->{ODM::FIELD_VOLUME};
					$newVolume = $oldVolume + $increaseOrderVolume->volume;
					// Время выполения для числового объёма
					$oldKworkDays = OrderVolumeManager::getVolumedDuration($order->kwork_days, $order->kwork->getVolumeInSelectedType(), $oldVolume);
					$newKworkDays = OrderVolumeManager::getVolumedDuration($order->kwork_days, $order->kwork->getVolumeInSelectedType(), $newVolume);

					// На сколько надо увеличить общее кол-во кворков
					$kworkByVolumeCount = ceil($newVolume / $order->kwork->volume) - $order->count;
				} else {
					// Время выполнения по кол-ву кворков
					$oldKworkDays = self::getDuration($order->kwork_days, $order->count, $order->data->category);
					$newKworkDays = self::getDuration($order->kwork_days, ($increaseOrderVolume->count + $order->count), $order->data->category);

					// На сколько надо увеличить общее кол-во кворков
					$kworkByVolumeCount = $increaseOrderVolume->count;
				}
				$addDays = ($newKworkDays - $oldKworkDays) * Helper::ONE_DAY;
				$days += $addDays;
				$plusDays += $addDays;
				$plusKworkCount += $kworkByVolumeCount;
				$addKworkDays = ($newKworkDays - $oldKworkDays);

				$commission = self::calculateCommission($increaseOrderVolume->eprice * $increaseOrderVolume->count, $currentTurnover, $lang);
				$increaseOrderVolumeCtp = $commission->priceKwork / $increaseOrderVolume->count;
				$currentTurnover += $commission->price;
				$plusWorkerPrice += $commission->priceWorker;

				$workerAmount = $commission->priceWorker;
				if ($workerLang != $lang) {
					$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
						$workerAmount,
						$lang,
						$workerLang
					);
				}

				$payerAmount = $increaseOrderVolume->eprice * $increaseOrderVolume->count;
				if ($actor->lang != $lang) {
					$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
						$payerAmount,
						$lang,
						$actor->lang
					);
				}

				$insertData = [
					'order_id' => $orderId,
					'extra_id' => $increaseOrderVolume->EID,
					'count' => $increaseOrderVolume->count,
					'extra_title' => $increaseOrderVolume->etitle,
					'extra_price' => $increaseOrderVolume->eprice,
					'extra_duration' => $addKworkDays,
					'extra_ctp' => $increaseOrderVolumeCtp,
					'extra_is_volume' => $increaseOrderVolume->is_volume,
					'status' => "done",
					\Model\OrderExtra::FIELD_VOLUME => $increaseOrderVolume->volume ? $increaseOrderVolume->volume : null,
					\Model\OrderExtra::FIELD_CUSTOM_VOLUME => $increaseOrderVolume->customVolume ? $increaseOrderVolume->customVolume : null,
					\Model\OrderExtra::FIELD_CURRENCY_RATE => $rate,
					\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE => $commission->priceWorker,
					\Model\OrderExtra::FIELD_WORKER_AMOUNT => $workerAmount,
					\Model\OrderExtra::FIELD_PAYER_AMOUNT => $payerAmount,
				];

				$order_extra_id = DB::table(OrderExtraManager::TABLE_NAME)->insertGetId($insertData);
				$orderExtraIds[] = $order_extra_id;
				if ($increaseOrderVolume->volume) {
					// Увеличивам числовой объем заказа
					DB::table(ODM::TABLE_NAME)
						->where(ODM::F_ORDER_ID, $orderId)
						->update([
							ODM::FIELD_VOLUME => DB::raw(ODM::FIELD_VOLUME . " + " . (float)$increaseOrderVolume->volume),
							OrderData::FIELD_CUSTOM_VOLUME => DB::raw(OrderData::FIELD_CUSTOM_VOLUME . " + " . (float)$increaseOrderVolume->customVolume),
						]);
				}
			}

			$workerAmount = $plusWorkerPrice;
			if ($workerLang != $lang) {
				$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$workerAmount,
					$lang,
					$workerLang
				);
			}

			$payerAmount = $plusPrice;
			if ($actor->lang != $lang) {
				$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$payerAmount,
					$lang,
					$actor->lang
				);
			}
			DB::statement("UPDATE orders SET price = price + :plusPrice,
							crt = crt + :plusWorkerPrice,
							duration = :days,
							count = count + :plusKworkCount,
							deadline = IF(deadline IS NOT NULL, deadline + :plusDays, NULL),
							worker_amount = worker_amount + :workerAmount,
							payer_amount = payer_amount + :payerAmount
							WHERE OID = :orderId", [
				"plusPrice" => $plusPrice,
				"plusWorkerPrice" => $plusWorkerPrice,
				"days" => $days,
				"plusKworkCount" => $plusKworkCount,
				"plusDays" => $plusDays,
				"workerAmount" => $workerAmount,
				"payerAmount" => $payerAmount,
				"orderId" => $orderId,
			]);

			$trackId = TrackManager::create($orderId, 'extra', null, null, null, 'done');

			$rows = [];
			foreach ($orderExtraIds as $extraId) {
				$rows[] = [
					"track_id" => $trackId,
					"order_extra_id" => $extraId,
				];
			}
			DB::table(\Model\TrackExtra::TABLE_NAME)
				->insert($rows);

			TrackManager::updateOrderExtraDuration($orderId);

			if ($increaseOrderVolume) {
				TrackManager::payerDeclineUpgradeTracks($orderId);
			}

			if ($order->status == OrderManager::STATUS_CHECK) {
				OrderManager::setInprogress($orderId, $order->PID, $order->worker_id);
				TrackManager::create($orderId, Type::PAYER_INPROGRESS_ADD_OPTION);
				//сбрасываем оповещения о том, что заказ сдан на проверку
			}

			GTM::setExtrasTransaction($operation, $order, $extras);

			return [
				"status" => "success",
				"operation" => $operation,
			];
		} else {
			$paymentId = DB::table(Operation::TABLE_NAME)
				->insertGetId([
					Operation::FIELD_USER_ID => $actor->id,
					Operation::FIELD_TYPE => Operation::TYPE_REFILL,
					Operation::FIELD_STATUS => OperationManager::FIELD_STATUS_NEW,
				]);

			return [
				"status" => "error",
				"payment_id" => $paymentId,
				"difference" => $convertedPlusPrice - $actor->totalFunds,
			];
		}
	}

	/**
	 * Обновление дедлайна после возвращения заказа с паузы.
	 *
	 * Предусловие: вызывать следует до создания запускающего заказ трека.
	 *
	 * @param int $orderId id заказа
	 * @param string $trackType тип трека, который будет создан.
	 */
	public static function updateDeadline($orderId, $trackType) {
		if (!Type::isToRunOrder($trackType)) {
			return;
		}
		$lastUpdateSec = 0;

		//Берем последний трек по заказу
		$pausedTrack = Track::where(Track::FIELD_ORDER_ID, "=", $orderId)
			->orderByDesc(Track::FIELD_ID)
			->first();

		/// Начисляем время только если прошлый трек был останавливающим.
		if (in_array($pausedTrack->type, Type::getToPauseOrderTypes())) {
			//Получаем дату последнего запуска крона который перерасчитывает дедлайн
			$lastUpdateSec = self::DEFAULT_EXTEND_TIME * \Helper::ONE_MINUTE;

			$lastUpdateTimeInSec = time() - $lastUpdateSec;
			$trackTime = strtotime($pausedTrack->date_create);

			//Если трек был выставлен позже предыдущего запуска крона, нам нужно это учесть.
			if ($trackTime > $lastUpdateTimeInSec) {
				$lastUpdateSec = time() - $trackTime;
			}
		}

		$order = Order::find($orderId);
		if (empty($order->deadline)) {
			//если дедлайн не был задан, то задаем текущее время + время заказа
			$order->deadline = time() + $order->duration;
		} else {
			if (Type::isAdminRunOrderType($trackType)) {
				$order->deadline = time() + self::DEADLINE_EXTEND_TIME_BY_ADMIN;
			} else {
				$order->deadline += $lastUpdateSec;
			}
		}
		$order->save();
	}


	/**
	 * Покупатель отмечает заказ как выполненный из состояния inprogress
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param bool $allowPortfolio Разрешено ли портфолио в этом заказе
	 * @param string $message
	 *
	 * @return UpdatePayerLevelResultDto|null
	 * @throws Throwable
	 */
	public static function payer_inprogress_done($orderId, $allowPortfolio = false, $message = ''): ?UpdatePayerLevelResultDto {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$orderId) {
			return null;
		}

		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::APPROVE_ORDER, $actor->id));

		// заказ
		$order = Order::find($orderId);
		if (!$order) {
			return null;
		}
		$arAllowedStatuses = [
			self::STATUS_INPROGRESS,
			self::STATUS_ARBITRAGE,
			self::STATUS_CHECK,
		];
		$allow = $actor->id == $order->USERID && in_array($order->status, $arAllowedStatuses);
		if (!$allow) {
			return null;
		}

		if ($order->has_stages) {
			throw new RuntimeException("Для принятия этапов необходимо использовать ApproveStagesController");
		}

		// если во время принятия покупатель заполнил поле сообщения то сначала отправим его
		if ($message) {
			TrackManager::create($order->OID, Type::TEXT, $message);
		}

		$trackId = TrackManager::create($order->OID, Type::PAYER_INPROGRESS_DONE);

		self::setDone($order);

		return null;
	}

	/**
	 * Создание заказа по  предложению
	 *
	 * @param object $old_order Заказ предложения
	 * @param array $stages Массив данных этапов (только для поэтапных заказов)
	 * @param int $daysChange Изменение срока заказа (только для поэтапных заказов)
	 * @param \Model\Want|null $want Модель запроса на услуги (не будет в переписке)
	 *
	 * @return int Идентификатор созданного заказа
	 *
	 * @global object $actor
	 * @global DataBase $conn
	 */
	public static function createByOffersOrder($old_order, array $stages = [], int $daysChange = 0, Want $want = null) {
		global $conn, $actor;

		$old_order_id = $old_order->OID;
		unset($old_order->OID);

		$fields = (array)$old_order;
		foreach ($fields as $key => $item) {
			if ($item == null) {
				unset($fields[$key]);
			}
		}

		//Проверяем тип источника заказа, на валидность, если типа источника нет, его нужно добавить в
		// getDirectTypes или getIndirectTypes
		if (!WantManager::checkValidSourceType($fields["source_type"])) {
			throw new RuntimeException("Incorrect sorce type");
		}
		$lang = Translations::getLangByCurrencyId($fields[Order::FIELD_CURRENCY_ID]);

		$oldOrderUpdateFields = []; // Поля которые необходимо обновить в предложении продавца
		// Фоллбек для старых предложений
		if ($fields[Order::FIELD_INITIAL_OFFER_PRICE] == 0 && $fields[Order::FIELD_PRICE] > 0) {
			$fields[Order::FIELD_INITIAL_OFFER_PRICE] = $fields[Order::FIELD_PRICE];
			$oldOrderUpdateFields[Order::FIELD_INITIAL_OFFER_PRICE] = $fields[Order::FIELD_INITIAL_OFFER_PRICE];
		}
		if ($fields[Order::FIELD_INITIAL_DURATION] == 0 && $fields[Order::FIELD_DURATION] > 0) {
			$fields[Order::FIELD_INITIAL_DURATION] = $fields[Order::FIELD_DURATION];
			$oldOrderUpdateFields[Order::FIELD_INITIAL_DURATION] = $fields[Order::FIELD_INITIAL_DURATION];
		}

		$old_order_data = (array)\Order\OrderDataManager::get($old_order_id, ['*']);

		$fields[self::F_BONUS_TEXT] = "";

		$workerLang = \Model\User::where(\Model\User::FIELD_USERID, $fields[self::F_WORKER_ID])->value(\Model\User::FIELD_LANG);

		$workerAmount = $fields[self::F_CRT];
		if ($workerLang != $lang) {
			$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$workerAmount,
				$lang,
				$workerLang
			);
		}

		$payerAmount = $fields[self::F_PRICE];
		if ($actor->lang != $lang) {
			$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$payerAmount,
				$lang,
				$actor->lang
			);
		}

		$fields[Order::FIELD_WORKER_AMOUNT] = $workerAmount;
		$fields[Order::FIELD_PAYER_AMOUNT] = $payerAmount;
		$fields[Order::FIELD_CURRENCY_RATE] = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($fields[self::F_CURRENCY_ID]);

		$order_id = App::pdo()->insert(self::TABLE_NAME, $fields);
		if ($order_id <= 0) {
			throw new RuntimeException("Database insert failed");
		}
		$order = Order::find($order_id);

		if ($order->status != OrderManager::STATUS_NEW) {
			throw new \Order\Exception\AlreadyPaidException($order_id);
		}

		$old_order_data['order_id'] = $order_id;
		\Order\OrderDataManager::add($old_order_data);

		// копируем описание и файлы запроса в заказ
		if ($want !== null) {
			$trackId = TrackManager::create($order_id, Type::TEXT, html_entity_decode($want->desc));
		}

		OrderManager::logStatus($order_id, OrderManager::STATUS_NEW);

		OrderManager::payer_new_inprogress($order_id);
		return $order_id;
	}


	/**
	 * Разрешить портфолио в заказе
	 *
	 * @param int $orderId идентификатор заказа
	 * @return bool
	 */
	public static function allowPortfolio($orderId) {
		App::pdo()->update('orders', ['portfolio_type' => 'allow'], 'OID = :orderId', ['orderId' => $orderId]);
		return true;
	}

	/**
	 * Запретить портфолио в заказе
	 *
	 * @param int $orderId идентификатор заказа
	 * @return bool
	 */
	public static function denyPortfolio($orderId) {
		Order::whereKey($orderId)
			->update([Order::FIELD_PORTFOLIO_TYPE => OrderManager::PORTFOLIO_DENY]);

		// отметить прочитанным уведомление о загрухке портфолио
		$data = self::getOrderData($orderId);

		return true;
	}

	public static function getQuickPrice($orderPrice, $minPrice = 500) {
		$quickPrice = floor($orderPrice / 2);
		if ($quickPrice < $minPrice) {
			$quickPrice = $minPrice;
		}
		return $quickPrice;
	}

	public static function getQuickDuration($orderDuration) {
		$minDuration = 1;
		$quickDuration = round(1.0972 * pow($orderDuration, 0.6305));
		if ($quickDuration < $minDuration) {
			$quickDuration = $minDuration;
		}

		//Потому что по формуле 2 дня округляются до 2. Проверить необходимость хардкода, при изменении формулы.
		if ($quickDuration == 2) {
			$quickDuration = 1;
		}
		if (in_array($orderDuration, [4, 5])) {
			$quickDuration = 2;
		}
		if (in_array($orderDuration, [7])) {
			$quickDuration = 3;
		}
		return $quickDuration;
	}

	/**
	 * Получить предоставленные по заказу данные
	 * @param int $orderId Код заказа
	 * @return array array(<message> => текст, <files> => прикрепленные файлы)
	 */
	public static function getOrderProvidedData($orderId) {
		global $conn, $actor;
		$orderId = intval($orderId);
		if (!Helper::isConsoleRequest()) {
			if (empty($actor) || empty($actor->id)) {
				return false;
			}
		}

		$sql = 'SELECT
				*
			FROM track
			WHERE
				type="' . Type::TEXT_FIRST . '" AND 
				OID = ' . mres($orderId);
		if (!Helper::isConsoleRequest()) {
			$sql .= ' AND user_id = ' . mres($actor->id);
		}
		$sql .= ' ORDER BY MID DESC LIMIT 1';

		$resultData = $conn->execute($sql)->getRows();
		if (empty($resultData)) {
			return [];
		}
		$providedData = array_shift($resultData);

		return [
			'message' => $providedData['message'],
			'files' => [],
		];
	}

	public static function api_getOrderProvidedData() {
		$orderId = post('orderId');

		$answer = OrderManager::getOrderProvidedData($orderId);
		if (empty($answer)) {
			return false;
		}
		$return = array(
			'message' => htmlspecialchars_decode($answer['message'], ENT_QUOTES),
			'files' => array()
		);

		foreach ($answer['files'] as $id => $fileInfo) {
			$return['files'][$id] = FileManager::toUploaderFormat($fileInfo);
		}

		return $return;
	}

	/**
	 * Получить кол-во заказов текущего пользователя.
	 *
	 * @return array|bool
	 */
	public static function api_getOrdersCount() {
		global $actor;
		if (!empty($actor->id)) {
			$response = self::getUserActiveOrdersCount($actor->id);
			$response["payerWants"] = WantManager::getWantsCount($actor->id);
			return $response;
		}

		return false;
	}

	/**
	 * Получить хеш данные для выполнения заказа
	 * @param array $providedData данные из getOrderProvidedData
	 * @return string md5-хеш
	 */
	public static function getOrderProvidedHash(array $providedData) {
		$filesInfo = $providedData['files'];
		$filesHash = array();
		foreach ($filesInfo as $fileInfo) {
			$filesHash[] = md5($fileInfo->fname . "_" . $fileInfo->size);
		}
		sort($filesHash);

		$orderProvidedData = self::formatProvidedTextForHash($providedData['message']);
		if (!empty($filesHash)) {
			$orderProvidedData .= "\r\n" . implode("\r\n", $filesHash);
		}
		return md5($orderProvidedData);
	}

	/**
	 * Отформатировать текст для хеширования
	 * @param string $providedText Данные для выполнения зазака
	 * @return string отформатированный текст
	 */
	public static function formatProvidedTextForHash($providedText) {
		$removeChars = " !@#$%^&*()_+!;%:?-=.,\r\n\t";
		$providedText = preg_replace('![' . preg_quote($removeChars, '!') . ']+!i', '', $providedText);
		$rows = explode("\n", $providedText);
		$rows = array_map('trim', $rows);
		$rows = array_filter($rows);
		return implode('', $rows);
	}

	/**
	 * Заполнить хеш данных для выполнения заказа
	 * @param int $orderId Код заказа
	 */
	public static function fillProvidedHash($orderId) {
		global $conn;
		$orderId = intval($orderId);

		$data = OrderManager::getOrderProvidedData($orderId);
		$hash = OrderManager::getOrderProvidedHash($data);

		$sql = 'UPDATE orders
			SET data_provided_hash="' . mres($hash) . '"
			WHERE OID=' . mres($orderId);
		$conn->execute($sql);
	}


	/**
	 * Получить $field из orders
	 * @param array $oids Массив кодов (OID)
	 * @param string|array $field Поле или миссив полей
	 * @param bool $isObject Получить запись как объект(для нескольних полей)
	 * @return array|false array( <OID> => <field>) или false в случае невалидных данных
	 */
	public static function getField(array $oids, $field, $isObject = false) {
		global $conn;

		$fields = (array)$field;
		foreach ($fields as $f) {
			if (!Helper::isFieldDb($f)) {
				return false;
			}
		}

		$oids = array_filter(array_map('intval', $oids));
		if (empty($oids)) {
			return false;
		}

		$sql = 'SELECT
				OID, ' . mres(implode(',', $fields)) . '
			FROM orders
			WHERE OID IN (' . mres(implode(',', $oids)) . ')';
		$rows = $conn->execute($sql)->getrows();
		$return = array();
		foreach ($rows as $row) {
			if (count($row) && !is_array($field)) {
				$return[$row['OID']] = $row[$field];
			} else {
				$return[$row['OID']] = $isObject ? (object)$row : $row;
			}
		}
		return $return;
	}

	/**
	 * Получить заказы с похожими данными
	 * @param array $allowCats Список категорий
	 * @param int $limit Количество
	 * @return boolean|array array( <OID> => <PID>) или false в случае невалидных данных
	 */
	public static function getKworkSimilarData(array $allowCats, $limit = 3) {
		return [];
	}


	/**
	 * Расчет оставшегося времени выполнения заказа на котором висит запрос на отмену
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param bool $orderRestarted был ли перезапущен
	 * @return false|array [stopTime => int, deadline => int]
	 * @throws Exception
	 */
	public static function getOrderCancelRequestTime($orderId, $orderRestarted = false) {
		$orderId = (int)$orderId;
		if ($orderId > 0) {
			$timeInWorkCancel = Helper::ONE_HOUR * Helper::getAutoChancelHours($orderRestarted);

			$sql = "SELECT 
						UNIX_TIMESTAMP(t.date_create) AS stopTime, 
						IF (o.in_work = 1,
							o.deadline - UNIX_TIMESTAMP(t.date_create),	
							UNIX_TIMESTAMP(o.date_inprogress) + :autocancelTime - UNIX_TIMESTAMP(t.date_create)
							) AS deadline
					FROM orders AS o 
					LEFT JOIN track AS t ON t.OID = o.OID 
						AND t.type IN ('worker_inprogress_cancel_request', 'payer_inprogress_cancel_request') 
						AND t.status='new'
					WHERE o.OID = :orderId
					ORDER BY t.date_create DESC";

			$params = [
				'orderId' => $orderId,
				'autocancelTime' => $timeInWorkCancel,
			];

			return App::pdo()->fetch($sql, $params);
		}

		return false;
	}


	/**
	 * Обновить время, когда заказ поставлен в статус 1.
	 *
	 * Если заказ не был взят в работу, был отменен продавцом, покупатель вернул заказ в работу, и при этом покупатель
	 * не успел взять его в работу, до момента  отмены в cron_hour - отменяет заказ за 2 дня невзятия в работу.
	 *
	 * @param int $orderId - идентификатор заказа
	 * @param $trackType
	 * @return bool
	 * @throws Exception
	 */
	public static function updateInprogress($orderId, $trackType) {
		$orderId = (int)$orderId;
		if (!$orderId) {
			return false;
		}
		if (!in_array($trackType, ["payer_inprogress_cancel_request", "worker_inprogress_cancel_request"])) {
			return false;
		}

		$sql = "SELECT
					UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date_create)
				FROM
					track
				WHERE
					OID = :orderId
					AND type = :trackType
					AND status = 'new'
				ORDER BY MID desc LIMIT 1";
		$sleepTime = (int)App::pdo()->fetchScalar($sql, ["orderId" => $orderId,
			"trackType" => $trackType]);
		if ($sleepTime) {
			$sql = "UPDATE orders
						SET
						date_inprogress = FROM_UNIXTIME(UNIX_TIMESTAMP(date_inprogress) + :sleepTime)
					WHERE
						OID = :orderId";
			return App::pdo()->execute($sql, ["sleepTime" => $sleepTime, "orderId" => $orderId]);
		}
	}


	/**
	 * Покупатель повышает уровень пакетного кворка в заказе
	 * @param int $orderId Идентификатор заказа
	 * @param string $packageType уровень пакета на который повышаем standard|medium|premium
	 * @return array|bool
	 * Возвращает массив в случае ошибки - нехватки денег,
	 * true если все прошло успешно,
	 * false если не удалось провести операцию
	 * @global object $actor
	 */
	public static function payer_upgrade_package($orderId, $packageType) {
		$actor = UserManager::getCurrentUser();

		$order = Order::find($orderId);

		if (!$order || $actor->id != $order->USERID || $order->isNotInBuyStatus()) {
			return false;
		}

		if ($order->has_stages && $order->stages && $order->stages->count() > 1) {
			throw new SimpleJsonException(Translations::t("Покупка опций в заказах с этапами запрещена"));
		}

		if (!TrackManager::isPackageUpgradeWithVolumeAllowed($order->data->volume_type_id, $order->kwork->volume_type_id)) {
			// Запрещаем апгрейд пакетов если тип объема изменился
			return false;
		}

		$package = PackageManager::getPackageForUpgrade($order->PID, $packageType);
		$extras = TrackManager::getOrderedExtras($orderId)["orderedExtras"];
		$extrasDuration = array_sum(array_column($extras, "totaldays"));

		if ($order->processLikeVolumedKwork()) {
			$totalVolume = $order->data->volume;
			// Посчитаем стоимость для заказа с числовым объемом
			$price = 0;
			$volumeExtras = $order->extrasWithVolume();
			if ($volumeExtras) {
				foreach ($volumeExtras as $extra) {
					if (!is_null($extra->volume)) {
						$totalVolume -= $extra->volume;
						$price += $order->kwork->getVolumedPrice($extra->volume, $package["price"]);
					}
				}
			}
			if ($totalVolume > 0) {
				$price += $order->kwork->getVolumedPrice($totalVolume, $package["price"]);
			}

			// Время выполнения по купленному числовому объёму
			$duration = \OrderVolumeManager::getVolumedDuration($package["days"], $order->kwork->getVolumeInSelectedType(), $order->data->volume);
		} else {
			$price = ($package["price"] * $order->count);

			// Время выполнения по кол-ву кворков
			$duration = self::getDuration($package["days"], $order->count, $order->data->category);
		}
		$duration += $extrasDuration;

		$price += array_sum(array_column($extras, "BPrice"));
		$pay = $price - $order->price;

		// #6913 Добавляем время продления заказа администратором
		$duration += $order->extended_time / Helper::ONE_DAY;

		$newDeadline = 0;
		if ($order->deadline) {
			if ($order->duration / Helper::ONE_DAY > $duration) {
				$duration = $order->duration / Helper::ONE_DAY;
				$package["days"] = $order->kwork_days;
				$newDeadline = $order->deadline;
			} else {
				$newDeadline = $order->stime + $duration * Helper::ONE_DAY;
			}
		}

		$currencyId = $order->currency_id;
		$currencyRate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($currencyId);
		$convertedPay = $pay;
		if ($currencyId == \Model\CurrencyModel::USD && $actor->lang == \Translations::DEFAULT_LANG) {
			$currencyId = \Translations::getCurrencyIdByLang(\Translations::DEFAULT_LANG);
			$convertedPay = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$pay,
				\Translations::EN_LANG,
				\Translations::DEFAULT_LANG
			);
		}

		UserManager::refreshActorTotalFunds();
		if ($pay > 0 && $actor->totalFunds < $convertedPay) {
			$deficit = $convertedPay - $actor->totalFunds;

			$operationLang = OperationLanguageManager::detectLang("refill", $currencyId);
			$langAmount = OperationLanguageManager::getAmountByType("refill", $deficit, $currencyId, $operationLang);
			$insertData = [
				OperationManager::FIELD_USER_ID => $actor->id,
				OperationManager::FIELD_TYPE => "refill",
				OperationManager::FIELD_AMOUNT => $deficit,
				OperationManager::FIELD_STATUS => "new",
				OperationManager::FIELD_CURRENCY_ID => $currencyId,
				OperationManager::FIELD_CURRENCY_RATE => $currencyRate,
				OperationManager::FIELD_LANG => $operationLang,
				OperationManager::FIELD_LANG_AMOUNT => $langAmount,
			];
			App::pdo()->insert(OperationManager::TABLE_NAME, $insertData);
			return [
				"status" => "error",
				"payment_id" => App::pdo()->lastInsertId(),
				"difference" => $deficit
			];
		}
		$operation = OperationManager::orderOutOperation($convertedPay, $orderId, 0, $currencyId, $currencyRate, $order->currency_id);
		if (!$operation) {
			return false;
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$lang = \Translations::getLangByCurrencyId($order->currency_id);
		$turnover = \OrderManager::getTurnover($order->worker_id, $order->USERID, $lang);
		$turnover += $order->price;
		$commission = self::calculateCommission($pay, $turnover, $lang);
		$newWorkerPrice = $order->crt + $commission->priceWorker;

		$workerAmount = $commission->priceWorker;
		$workerLang = User::where(User::FIELD_USERID, $order->worker_id)->value(User::FIELD_LANG);
		if ($workerLang != $lang) {
			$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$workerAmount,
				$lang,
				$workerLang
			);
		}

		$payerAmount = $pay;
		if ($actor->lang != $lang) {
			$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$payerAmount,
				$lang,
				$actor->lang
			);
		}

		/**
		 * Обновляем данные о заказе
		 */
		$attributes = [
			"kwork_days" => $package["days"],
			"price" => $price,
			"crt" => $newWorkerPrice,
			"duration" => $duration * Helper::ONE_DAY,
			"worker_amount" => DB::raw("worker_amount + " . (float)$workerAmount),
			"payer_amount" => DB::raw("payer_amount + " . (float)$payerAmount),
		];
		if (0 < (int)$newDeadline) {
			$attributes["deadline"] = (int)$newDeadline;
		}
		Order
			::where(Order::FIELD_OID, "=", $orderId)
			->update($attributes);

		OrderManager::orderPackageUpgradeProcess($order, $package);

		TrackManager::create($orderId, "payer_upgrade_package");

		$factory = new \Factory\Letter\Service\ServiceLetterFactory();
		$letter = $factory->createPayerUpgradePackage($order->worker->getUnencriptedEmail(), $order->getOrderTitleForUser($order->worker_id), $orderId, $order->kwork->lang);
		MailSender::sendLetter($letter);

		// Отказываем все предложения с увеличением объема кворка
		TrackManager::payerDeclineVolumeTracks($orderId);
		// Отказываем все предложения с апгредом пакетов
		TrackManager::payerDeclineUpgradeTracks($orderId);

		// Если состояние заказа - на проверке переводим в работу
		if ($order->status == OrderManager::STATUS_CHECK) {
			\OrderManager::setInprogress($orderId, $order->PID, $order->worker_id);
			\TrackManager::create($orderId, Type::PAYER_INPROGRESS_ADD_OPTION);
		}

		GTM::setUpgradePackageTransaction($operation, $order, $package);

		return true;
	}

	/**
	 * Обновление данных заказанного пакета при повышении уровеня пакетного кворка в заказе
	 *
	 * @param Order $order Модель заказа
	 * @param array $package Массив данных пакета до которого апгрейдим (в формате выдаваемым PackageManager::getPackageForUpgrade)
	 */
	public static function orderPackageUpgradeProcess(Order $order, array $package) {
		$orderPackageDuration = $package["days"];

		// Если длительность более дорогого пакета - меньше - не уменьшаем длительность
		if ($orderPackageDuration < $order->orderPackage->duration) {
			$orderPackageDuration = $order->orderPackage->duration;
		}

		App::pdo()->execute("DELETE FROM order_package_item WHERE order_package_id = :orderPackageId",
			["orderPackageId" => $order->orderPackage->id]);
		DB::table(\Model\Package\Order\CustomOrderPackageItem::TABLE_NAME)
			->where(\Model\Package\Order\CustomOrderPackageItem::FIELD_ORDER_ID, $order->OID)
			->delete();
		foreach ($package["items"] as $packageItem) {
			if ($packageItem["id"]) { //без обязательных непредставленных
				$packageItemId = $packageItem["pi_id"];
				if ($packageItem["add_type"] == "custom") {
					$customOption = new \Model\Package\Order\CustomOrderPackageItem();
					$customOption->order_id = $order->OID;
					$customOption->name = $packageItem["name"];
					$customOption->min_value = $packageItem["min_value"];
					$customOption->max_value = $packageItem["max_value"];
					$customOption->type = $packageItem["type"];
					$customOption->save();
					$packageItemId = $customOption->id;
				}
				App::pdo()->insert("order_package_item", [
					"order_package_id" => $order->orderPackage->id,
					"package_item_id" => $packageItemId,
					"value" => $packageItem["value"],
					"type" => $packageItem["add_type"]
				]);
			}
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$turnover = self::getTurnover($order->worker_id, $order->USERID, $order->getLang());
		$turnover += $order->price;
		$commission = OrderManager::calculateCommission($package["price"], $turnover, $order->getLang());

		App::pdo()->execute(
			"UPDATE order_package SET
				type = :packageType,
				duration = :duration,
				price = :price,
				price_ctp = :priceCtp
				WHERE id = :orderPackageId",
			[
				"packageType" => $package["type"],
				"duration" => $orderPackageDuration,
				"price" => $package["price"],
				"priceCtp" => $commission->priceWorker,
				"orderPackageId" => $order->orderPackage->id
			]);

		$extrasUpdateFields = [
			ExtrasManager::F_EPRICE => $package["price"],
			ExtrasManager::F_CTP => $commission->priceKwork,
			ExtrasManager::F_DURATION => $orderPackageDuration,
		];
		if (!$order->data->volume_type_id) {
			// Если кворк не с числовым объемом то обновляем также название опции объема
			$extrasUpdateFields[ExtrasManager::F_ETITLE] = Translations::translateByLang($order->getLang(), 'Количество пакетов "%1$s"', PackageManager::getName($package["type"], $order->getLang()));
		}

		DB::table(ExtrasManager::TABLE_NAME)
			->where(ExtrasManager::F_OID, $order->OID)
			->where(ExtrasManager::F_IS_VOLUME, 1)
			->update($extrasUpdateFields);
	}

	/**
	 * Количество заказов на которые надо обратить внимание для текущего продавца
	 *
	 * @return int
	 */
	public static function workerAttentionCount(): int {
		global $actor;

		$inprogressStatus = self::STATUS_INPROGRESS;
		$inprogressCheckSeconds = App::config("kwork.need_worker_inprogress_check_hours") * Helper::ONE_HOUR;
		$newCheckSeconds = App::config("kwork.need_worker_new_inwork_hours") * Helper::ONE_HOUR;
		$query = "SELECT sum(
					CASE
					WHEN in_work = 1 THEN IF(deadline AND (deadline - UNIX_TIMESTAMP()) < $inprogressCheckSeconds, 1, 0)
					WHEN in_work = 0 THEN IF((UNIX_TIMESTAMP() - stime) > $newCheckSeconds, 1, 0)
					END) AS cnt
				FROM
					orders
				WHERE worker_id = :worker_id AND status = $inprogressStatus";

		return (int)App::pdo()->fetchScalar($query, ['worker_id' => $actor->id]);
	}

	/**
	 * Проверяет может ли пользовтаель изменить название в переданном заказе
	 * @param array
	 * @return boolean
	 */
	public static function checkCanEditName($order) {
		$tenDays = 10 * Helper::ONE_DAY;
		$now = time();
		//Если заказ создан менее 10 дней назад или же прошло 10 дней но заказ все равно активен
		return ($now - $order["stime"] < $tenDays || ($now - $order["stime"] > $tenDays && $order["status"] == 1));
	}

	/**
	 * Метод дополняет массив заказов данными из таблиц posts, members, want
	 * @param array $orders - масссив заказов
	 * @param $needJoinPosts - был ли ранее join кворков
	 * @param $needJoinMembers - был ли ранее join пользователей
	 * @param $userType - тип пользователя
	 * @return array
	 * @throws Exception
	 */
	public static function fillOrdersData(array $orders, $needJoinPosts, $needJoinMembers, $userType) {

		if (!in_array($userType, [UserManager::TYPE_WORKER, UserManager::TYPE_PAYER])) {
			throw new Exception("Type must be worker or payer");
		} else {
			$actor = UserManager::getCurrentUser();

			$userField = Order::FIELD_USERID;
			if ($userType == UserManager::TYPE_PAYER) {
				$userField = Order::FIELD_WORKER_ID;
			}

			$orderIds = array_column($orders, Order::FIELD_OID);
			if (count($orderIds) > 0) {
				$projectIds = array_map(function($order) {
					if (!empty($order[Order::FIELD_PROJECT_ID]))
						return $order[Order::FIELD_PROJECT_ID];
				}, $orders);

				//Определяем какие заказы были по запросам
				if (count($projectIds) > 0) {
					$wants = Want::select([Want::TABLE_NAME . ".*", Want::FIELD_NAME . " AS project"])
						->whereIn(Want::FIELD_ID, $projectIds)
						->pluck(Want::FIELD_DESCRIPTION, Want::FIELD_ID)
						->toArray();
				}
				// Если posts не было раньше заджойнено к orders нужно получить информацию с таблицы posts
				if (!$needJoinPosts) {
					$postIds = array_column($orders, Order::FIELD_PID);

					if (count($postIds)) {
						$postsFields = [Kwork::FIELD_PID, Kwork::FIELD_GTITLE, Kwork::FIELD_CATEGORY];
						if ($userType == UserManager::TYPE_PAYER) {
							$postsFields = [
								Kwork::FIELD_PID,
								Kwork::FIELD_PID . " AS kworkId",
								Kwork::FIELD_ACTIVE,
								KWORK::FIELD_FEAT,
								Kwork::FIELD_USERID,
								Kwork::FIELD_URL . " AS kworkUrl",
								Kwork::FIELD_GTITLE
							];
						}
						$kworks = Kwork::select($postsFields)
							->whereIn(Kwork::FIELD_PID, $postIds)
							->get()
							->keyBy(Kwork::FIELD_PID)
							->toArray();
						$orderNames = OrderNames::whereIn(OrderNames::FIELD_ORDER_ID, $orderIds)
							->where(OrderNames::FIELD_USER_ID, "=", $actor->id)
							->pluck(OrderNames::FIELD_ORDER_NAME, OrderNames::FIELD_ORDER_ID)
							->toArray();
					}

				}
				//Если таблица пользоватлей не была заджойнена раньше получаем список пользователей
				if (!$needJoinMembers) {
					$userIds = array_column($orders, $userField);

					$members = User::whereIn(User::FIELD_USERID, $userIds)
						->get()
						->keyBy(User::FIELD_USERID)
						->toArray();
				}
				$lastTrackIds = array_column($orders, Order::FIELD_LAST_TRACK_ID);
				if (count($lastTrackIds) > 0) {
					$tracks = Track::whereIn(Track::FIELD_ID, $lastTrackIds)
						->pluck(Track::FIELD_TYPE, Track::FIELD_ID)
						->toArray();
				}

				if ($userType == UserManager::TYPE_WORKER) {
					$categoryIds = array_unique(array_column($needJoinPosts ? $orders : $kworks, Kwork::FIELD_CATEGORY));

					$categories = Category::withoutGlobalScopes()
						->whereIn(CategoryManager::F_CATEGORY_ID, $categoryIds)
						->pluck(CategoryManager::F_CATEGORY_ID)
						->toArray();
				}


				//Соеденяем данные запроса в один
				foreach ($orders as $key => $order) {
					$pid = $order[Order::FIELD_PID];
					$oid = $order[Order::FIELD_OID];
					$orders[$key]["project"] = $wants[$order[Order::FIELD_PROJECT_ID]] ?? null;
					if (!$needJoinPosts) {
						$orders[$key] = array_merge($kworks[$pid] ?? [], $orders[$key]);
						if (isset($orderNames[$oid])) {
							$orders[$key]["displayTitle"] = $orderNames[$oid] ?? null;
						} else {
							$orders[$key]["displayTitle"] = $order[Order::FIELD_KWORK_TITLE];
						}
					}
					if (!$needJoinMembers) {
						$orders[$key]["username"] = $members[$order[$userField]]
							? $members[$order[$userField]]["username"]
							: null;

						$orders[$key]["profilepicture"] = $members[$order[$userField]]
							? $members[$order[$userField]]["profilepicture"]
							: null;
					}
					$orders[$key]["track_type"] = $tracks[$order[Order::FIELD_LAST_TRACK_ID]] ?? null;
					if ($userType == UserManager::TYPE_WORKER) {
						//Поскольку мы достаем все поля order это нужно убрать для продавца
						$orders[$key][Order::FIELD_PAYER_UNREAD_TRACKS] = 0;
						if ($needJoinPosts) {
							$categoryId = $order[Kwork::FIELD_CATEGORY];
							$orders[$key]["cat_portfolio_type"] = $categories[$categoryId] ?? null;
						}
					} else {
						//Поскольку мы достаем все поля order это нужно убрать для покупателя
						$orders[$key][Order::FIELD_WORKER_UNREAD_TRACKS] = 0;
					}
				}
			}
		}
		return $orders;
	}

	/**
	 * Заказы текущего продавца
	 *
	 * @param string $sort Поле по которому сортируем date|deadline|date_done|status|title|user|price|id
	 * @param string $direction Направление сортировки asc|desc
	 * @param int $pagingStart Оффсет
	 * @param int $limit Лимит
	 * @param int $filterUserId Идентификатор идентификатор пользователя, чьи заказы необходимо отобразить
	 * @param string $searchWord Строка поиска
	 *
	 * @return array [
	 *      "myOrders" (MyOrders | NULL) => объект статистики заказов пользователя,
	 *      "sort" => string,
	 *      "direction" => string,
	 *      "orders" => array
	 * ]
	 */
	public static function workerOrders($sort, $direction, $pagingStart, $limit, int $filterUserId = 0, $searchWord = null) {
		$actor = UserManager::getCurrentUser();

		$defaultSort = true;
		$needJoinPosts = false;
		$needJoinMembers = false;
		switch ($sort) {
			case 'id':
			case 'date':
				$addme2 = "orders.OID";
				$defaultSort = false;
				break;

			case 'date_done':
				$addme2 = "orders.date_done";
				break;

			case 'status':
				$addme2 = "orders.status";
				break;

			case 'title':
				$addme2 = "displayTitle";
				$needJoinPosts = true;
				break;

			case 'user':
				$addme2 = "members.username";
				$needJoinMembers = true;
				break;

			case 'price':
				$addme2 = "displayCrt";
				break;

			default:
				$addme2 = "orders.OID";
				$sort = "date";
				$defaultSort = false;
				break;
		}

		if (in_array($direction, ['asc', 'desc'])) {
			$addme3 = $direction;
		} else {
			if ($sort == "deadline") {
				$addme3 = "asc";
				$direction = "asc";
			} else {
				$addme3 = "desc";
				$direction = "desc";
			}
		}

		//Если есть поисковой запрос джойним posts и order_names
		if ($searchWord != null) {
			$needJoinMembers = true;
			$needJoinPosts = true;
		}

		//Получаем заказы
		$query = Order::query();
		$additionalFields = [];
		//Фильтр по заказчику
		if ($filterUserId > 0) {
			$query->where(Order::TABLE_NAME . "." . Order::FIELD_USERID, "=", $filterUserId);
		}
		//Все берем для текущего пользователя
		$query->where(Order::TABLE_NAME . "." . Order::FIELD_WORKER_ID, "=", $actor->id);

		//Фильтр по статусу
		$query->where(Order::TABLE_NAME . "." . Order::FIELD_STATUS, "<>", 0);

		//Если есть сортировка по названию, добавляем join постов и order_names
		if ($needJoinPosts) {
			$additionalFields[] = "
				posts.PID,
				posts.category,
				CASE WHEN 
			   		ons.id is not null 
				THEN 
					ons.order_name 
				ELSE 
					orders.kwork_title 
				END as 'displayTitle'
			";
			$query->join(Kwork::TABLE_NAME, Kwork::TABLE_NAME . "." . Kwork::FIELD_PID, Order::TABLE_NAME . "." . Order::FIELD_PID);
			$query->leftJoin(OrderNames::TABLE_NAME . " as ons", function(Query\JoinClause $q) use ($actor) {
				$q->on("ons." . OrderNames::FIELD_ORDER_ID, Order::TABLE_NAME . "." . Order::FIELD_OID)
					->where("ons." . OrderNames::FIELD_USER_ID, $actor->id);
			});
		}
		//Если сортировка по имени пользователя
		if ($needJoinMembers) {
			$additionalFields[] = "members.username";
			$additionalFields[] = "members.profilepicture";

			$query->join(User::TABLE_NAME,
				User::TABLE_NAME . "." . User::FIELD_USERID,
				Order::TABLE_NAME . "." . Order::FIELD_USERID
			);
		}

		if (!empty($searchWord)) {
			$query->where(function(Eloquent\Builder $query) use ($searchWord) {
				$query->whereRaw("IF(
							ons.id is not null,
							ons.order_name LIKE ?,
							posts.gtitle LIKE ?
						)", array_fill(0, 2, "%{$searchWord}%"))
					->orWhere(User::TABLE_NAME . "." . User::FIELD_USERNAME, "LIKE", "%" . $searchWord . "%");
			});
		}
		//Добавляем дополнительные поля с джойна
		if (count($additionalFields) > 0) {
			$additionalFields = ", " . implode(",", $additionalFields);
		} else {
			$additionalFields = "";
		}
		$autoCancelTime = Helper::ONE_HOUR * Helper::getAutoChancelHours();

		$query->selectRaw("
			orders.*,
			$autoCancelTime - (UNIX_TIMESTAMP() - orders.stime) AS timeToInWork,
			IF(orders.deadline > UNIX_TIMESTAMP(), 0, 1) AS 'late',
			IF(orders.date_done IS NOT NULL, orders.date_done, orders.deadline) AS 'date_done',
			orders.currency_id AS currencyId,
			orders.currency_rate AS currencyRate,
			orders.portfolio_type AS 'order_portfolio_type',
			orders.worker_id AS 'owner',
			IF(orders.worker_unread_tracks > 0, 1, 0) AS 'has_unread', 
			CASE WHEN orders.status = " . self::STATUS_INPROGRESS . " AND orders.in_work = 0 THEN 1 ELSE 0 END AS 'not_in_work',
			orders.USERID AS 'payer_id'
			$additionalFields
		");

		$query->addSelect(DB::raw("IF(orders.has_stages, orders.stages_crt, orders.crt) as displayCrt"));

		//Если сортировка не выбрана пользователем, по умолчанию сортируем по ШВ
		if (!empty($addme2)) {
			$query->orderBy($addme2, $addme3);
		}

		if ($defaultSort) {
			$query->orderByDesc(Order::TABLE_NAME . "." . Order::FIELD_OID);
		}

		$count = $query->count();

		// постраничная навигация
		$query->limit($limit)
			->offset($pagingStart);

		$orders = $query->get()
			->keyBy(Order::FIELD_OID)
			->toArray();

		try {
			$orders = self::fillOrdersData($orders, $needJoinPosts, $needJoinMembers, UserManager::TYPE_WORKER);
		} catch (Exception $exception) {
			Log::daily(__CLASS__ . "::" . __FUNCTION__ . ": " . $exception->getMessage(), "error");
		}

		$orderIds = array_keys($orders);
		// Обозначаем находящиеся в состоянии отмены заказы
		if (count($orderIds)) {
			// В состоянии отмены может быть только заказ в статусе в работе
			$inprogressOrderIds = array_column(array_filter($orders, function($order) {
				return $order[self::F_STATUS] == self::STATUS_INPROGRESS;
			}), self::F_OID);

			if (!empty($inprogressOrderIds)) {
				$inCancelOrders = Track::whereIn(Track::FIELD_ORDER_ID, $inprogressOrderIds)
					->whereIn(Track::FIELD_TYPE, [Type::WORKER_INPROGRESS_CANCEL_REQUEST, Type::PAYER_INPROGRESS_CANCEL_REQUEST])
					->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
					->pluck(Track::FIELD_DATE_CREATE, Track::FIELD_ORDER_ID)
					->toArray();

				foreach ($inCancelOrders as $orderId => $stopDate) {
					$orders[$orderId]["isCancelRequest"] = true;
				}
			}
		}

		foreach ($orders as &$item) {
			$item["canEditName"] = self::checkCanEditName($item);

			// заказчик онлайн или оффлайн
			$item["is_online"] = UserManager::checkUserOnlineStatus((int)$item["payer_id"]);

			//Выводим сумму в валюте продавца
			$item["crt"] = $item["worker_amount"];
			$item["currencyId"] = Translations::getCurrencyIdByLang($actor->lang);
		}
		unset($item);

		return [
			"sort" => $sort,
			"direction" => $direction,
			"orders" => array_values($orders),
			"totalCount" => $count,
		];
	}

	/**
	 * Получить длительность ожидания в момент когда заказ был на паузе(запросы на отмену)
	 *
	 * @param int $orderId
	 * @param array $tracks - треки конкретного ордера, если они заданы, выполняется без запроса
	 * @param int $fromDate - если указано, то учитывает только треки старше указанной даты в timestamp
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public static function getOrderPauseDuration(int $orderId, $tracks = null, int $fromDate = 0) {
		$pauseDuration = 0;
		$haveNoClose = false;
		$dateStart = 0;

		$requestTypes = [
			Type::PAYER_INPROGRESS_CANCEL_REQUEST,
			Type::WORKER_INPROGRESS_CANCEL_REQUEST,
		];
		$rejectTypes = [
			Type::WORKER_INPROGRESS_CANCEL_DELETE,
			Type::WORKER_INPROGRESS_CANCEL_REJECT,
			Type::PAYER_INPROGRESS_CANCEL_DELETE,
			Type::PAYER_INPROGRESS_CANCEL_REJECT,
		];

		$allTypes = array_merge($requestTypes, $rejectTypes);
		if ($tracks == null) {
			$tracks = Track::where(Track::FIELD_ORDER_ID, $orderId)
				->whereIn(Track::FIELD_TYPE, $allTypes)
				->orderBy(Track::FIELD_ID)
				->select(Track::FIELD_TYPE, Track::FIELD_DATE_CREATE)
				->get()->all();
		} else {
			$cancelTracks = array_filter($tracks, function($track) use ($allTypes) {
				if (in_array($track->type, $allTypes)) {
					return true;
				} else {
					return false;
				}
			});
			$tracks = array_values($cancelTracks);
		}

		if (!empty($tracks)) {
			foreach ($tracks as $key => $track) {
				if (in_array($track->type, $requestTypes)) {
					$dateStart = strtotime($track->date_create);
					if ($dateStart < $fromDate) {
						continue;
					}
					if ($closeTrack = self::getNextCloseOrderRequest($tracks, $key)) {
						if (!empty($closeTrack->date_create)) {
							$dateClose = strtotime($closeTrack->date_create);
							$pauseDuration += ($dateClose - $dateStart);
						}
					} else {
						$haveNoClose = true;
					}
				}
			}
		}

		// Если нет закрывающего трека, считаем от текущего времени
		if ($haveNoClose == true) {
			$pauseDuration += (time() - $dateStart);
		}

		return $pauseDuration;
	}

	/**
	 * Получить длительность ожидания в момент когда заказ был на паузе(запросы на отмену) для массива заказов
	 *
	 * @param array $ordersIds - массив содержащий ID заказов
	 *
	 * @return array
	 */
	public static function getMassOrdersPauseDuration(array $ordersIds) {

		if (is_array($ordersIds) && !empty($ordersIds)) {
			$sql = "SELECT OID, user_id, type, status, date_create FROM " . TrackManager::TABLE_NAME . " WHERE OID IN (" . implode(",", $ordersIds) . ")";
			$tracks = App::pdo()->fetchAll($sql, [], PDO::FETCH_OBJ);
			$orderTracks = [];

			if (count($tracks) > 0) {
				foreach ($tracks as $key => $track) {
					$orderTracks[$track->OID][] = $track;
				}

				$responce = [];
				foreach ($orderTracks as $key => $trackList) {
					$responce[$key] = self::getOrderPauseDuration(0, $trackList);
				}
				return $responce;
			}
		}
		return [];
	}

	/**
	 * Вернет трек который является отменой запроса на отмену
	 *
	 * @param array $tracks массив обьектов треков
	 * @param int $key ключ в маассиве треков который соответствует треку запрос на отмену
	 *
	 * @return mixed|null
	 */
	private static function getNextCloseOrderRequest(array $tracks, int $key) {
		$returnRequestArray = [
			Type::PAYER_INPROGRESS_CANCEL_DELETE,
			Type::WORKER_INPROGRESS_CANCEL_DELETE,
			Type::PAYER_INPROGRESS_CANCEL_REJECT,
			Type::WORKER_INPROGRESS_CANCEL_REJECT,
		];
		for ($i = ($key + 1); $i < count($tracks); $i++) {
			if (isset($tracks[$i])) {
				$track = $tracks[$i];
				if (in_array($track->type, $returnRequestArray)) {
					return $track;
				}
			}
		}

		return null;
	}

	/**
	 * Функция возвращает количество записей при активном поиске по названию или юзеру
	 *
	 * @param string $filter фильтр по типу заказа
	 * @param string $searchWord строка поиска
	 * @param string $projectId Id проекта если идет поиск по проекту
	 *
	 * @return int количество заказов по параметрам
	 */
	public static function getSearchWordOrdersCount($filter, $searchWord, $projectId = null) {
		global $actor;

		//Берем противоположный от себя тип пользователя, поскольку у продавца идет поиск по логину покупателя.
		$userToSearch = "ord.worker_id";
		// название поля для текущего пользователя, по которому будем делать фильтр ордеров
		$reverceUser = "ord.USERID";
		$forWorker = false;
		$userId = $actor->id;
		if ($actor->type == UserManager::TYPE_WORKER) {
			$userToSearch = "ord.USERID";
			$reverceUser = "ord.worker_id";
			$forWorker = true;
		}

		$addStatus = "  AND " . self::orderStatusConditionByFilter($filter, 'ord', $userId, $forWorker);

		$join = [];
		$join[] = "JOIN members m ON m.USERID = $userToSearch";
		$join[] = "LEFT JOIN order_names ons ON ons.order_id = ord.OID AND ons.user_id = :user_id";
		$search = "";
		$params = ["user_id" => $actor->USERID];

		if ($searchWord != null) {
			$searchWord = mres($searchWord);
			$search = " AND (IF(ons.id is not null, ons.order_name, p.gtitle) LIKE '%$searchWord%' OR m.username LIKE '%$searchWord%')";
			$join[] = "JOIN posts p ON ord.PID = p.PID";
		}
		if ($projectId) {
			$search .= "AND ord.project_id = :projectId ";
			$params["projectId"] = $projectId;
		}
		$joinStr = implode(" ", $join);
		$sql = "SELECT
                  COUNT(OID)
		        FROM 
		          orders ord
				{$joinStr}
	            WHERE 
	              $reverceUser = :user_id $addStatus $search";
		return App::pdo()->fetchScalar($sql, $params);

	}


	/**
	 * Заказы текущего покупателя
	 *
	 * @param string $sort Поле по которому сортируем date|status|title|user|price|id
	 * @param string $direction Направление сортировки asc|desc
	 * @param int $pagingStart Оффсет
	 * @param int $limit Лимит
	 * @param int $filterUserId Идентификатор пользователя, чьи заказы необходимо отобразить
	 * @param string $searchWord Строка поиска
	 * @param string $projectId Id проекта для выборки заказов по проекту
	 *
	 * @return array [
	 *      "myOrders" (MyOrders | NULL) => объект статистики заказов пользователя,
	 *      "filter" => string,
	 *      "sort" => string,
	 *      "direction" => string,
	 *      "orders" => array
	 * ]
	 */
	public static function payerOrders($sort, $direction, $pagingStart, $limit, int $filterUserId = 0, $searchWord = null, $projectId = null) {
		$actor = UserManager::getCurrentUser();

		$defaultSort = true;
		$needJoinPosts = false;
		$needJoinMembers = false;

		if ($sort === "date") {
			$secondOrderColumn = "orders.stime";
			$defaultSort = false;
		} elseif ($sort === "deadline") {
			$secondOrderColumn = "orders.date_check";
			$defaultSort = false;
		} elseif ($sort === "status") {
			$secondOrderColumn = "orders.status";
		} elseif ($sort === "title") {
			$secondOrderColumn = "displayTitle";
			$needJoinPosts = true;
		} elseif ($sort === "user") {
			$secondOrderColumn = "members.username";
			$needJoinMembers = true;
		} elseif ($sort === "price") {
			$secondOrderColumn = "displayPrice";
		} elseif ($sort === "id") {
			$secondOrderColumn = "orders.OID";
			$defaultSort = false;
		} else {
			$secondOrderColumn = "orders.stime";
			$sort = "date";
			$defaultSort = false;
		}

		if ($direction === "asc") {
			$secondOrderColumnDirection = "asc";
		} else {
			$secondOrderColumnDirection = "desc";
			$direction = "desc";
		}

		//Если есть поисковой запрос джойним posts и order_names
		if ($searchWord != null) {
			$needJoinMembers = true;
			$needJoinPosts = true;
		}

		//Получаем заказы
		$query = Order::query();
		$additionalFields = [];
		//Фильтр по исполнителю
		if ($filterUserId > 0) {
			$query->where(Order::FIELD_WORKER_ID, "=", $filterUserId);
		}
		//Все берем для текущего пользователя
		$query->where(Order::TABLE_NAME . "." . Order::FIELD_USERID, "=", $actor->id);

		//Фильтр по статусу
		$query->where(Order::TABLE_NAME . "." . Order::FIELD_STATUS, "<>", 0);

		//Если есть сортировка по названию, добавляем join постов и order_names
		if ($needJoinPosts) {
			$additionalFields[] = "
				posts.active,
				posts.feat,
				posts.lang,
				posts.PID as kworkId, 
				CASE WHEN 
			   		ons.id is not null 
				THEN 
					ons.order_name 
				ELSE 
					orders.kwork_title 
				END as 'displayTitle'
			";
			$query->join(Kwork::TABLE_NAME, Kwork::TABLE_NAME . "." . Kwork::FIELD_PID, Order::TABLE_NAME . "." . Order::FIELD_PID);
			$query->leftJoin(OrderNames::TABLE_NAME . " as ons", function(Query\JoinClause $q) use ($actor) {
				$q->on("ons." . OrderNames::FIELD_ORDER_ID, Order::TABLE_NAME . "." . Order::FIELD_OID)
					->where("ons." . OrderNames::FIELD_USER_ID, $actor->id);
			});
		}
		//Если сортировка по имени пользователя
		if ($needJoinMembers) {
			$additionalFields[] = "members.username";
			$additionalFields[] = "members.profilepicture";

			$query->join(User::TABLE_NAME,
				User::TABLE_NAME . "." . User::FIELD_USERID,
				Order::TABLE_NAME . "." . Order::FIELD_WORKER_ID
			);
		}

		if (!empty($searchWord)) {
			$query->where(function(Eloquent\Builder $query) use ($searchWord) {
				$query->whereRaw("IF(
							ons.id is not null,
							ons.order_name LIKE ?,
							posts.gtitle LIKE ?
						)", array_fill(0, 2, "%{$searchWord}%"))
					->orWhere(User::TABLE_NAME . "." . User::FIELD_USERNAME, "LIKE", "%" . $searchWord . "%");
			});
		}

		if (!empty($projectId)) {
			$query->where(Order::FIELD_PROJECT_ID, "=", $projectId);
		}
		//Добавляем дополнительные поля с джойна
		if (count($additionalFields) > 0) {
			$additionalFields = ", " . implode(",", $additionalFields);
		} else {
			$additionalFields = "";
		}
		$query->selectRaw("
			orders.*,
			orders.OID AS id,
			orders.USERID AS userId,
			IF(orders.deadline > UNIX_TIMESTAMP(), 0, 1) AS 'late',
			orders.currency_id AS currencyId,
			orders.currency_rate AS currencyRate,
			IF(orders.payer_unread_tracks > 0, 1, 0) AS 'has_unread'
			$additionalFields
		");

		$query->addSelect(DB::raw("IF(orders.has_stages, orders.stages_price, orders.price) as displayPrice"));

		//Если сортировка не выбрана пользователем, по умолчанию сортируем по id
		if (!empty($secondOrderColumn)) {
			$query->orderBy($secondOrderColumn, $secondOrderColumnDirection);
		}
		if ($defaultSort) {
			$query->orderByDesc(Order::TABLE_NAME . "." . Order::FIELD_OID);
		}

		$count = $query->count();

		// постраничная навигация
		$query->limit($limit)
			->offset($pagingStart);

		$orders = $query->get()
			->toArray();

		try {
			$orders = self::fillOrdersData($orders, $needJoinPosts, $needJoinMembers, UserManager::TYPE_PAYER);
		} catch (Exception $exception) {
			Log::daily(__CLASS__ . "::" . __FUNCTION__ . ": " . $exception->getMessage(), "error");
		}

		$orderIdsOnCheck = [];
		$ordersTracks = [];

		if (!empty($orders)) {
			foreach ($orders as $key => $order) {
				//Выводим сумму в валюте покупателя
				$orders[$key]["price"] = $orders[$key]["payer_amount"];
				$orders[$key]["currencyId"] = Translations::getCurrencyIdByLang($actor->lang);
				// продавец онлайн или оффлайн
				$orders[$key]["is_online"] = UserManager::checkUserOnlineStatus((int)$order[Order::FIELD_WORKER_ID]);
			}

			//получаем треки на проверку заказа и запросов на отмену, отклонений
			if ($orderIdsOnCheck) {
				$checkAndRejectTracks = Track::whereIn(Track::FIELD_ORDER_ID, $orderIdsOnCheck)
					->whereIn(Track::FIELD_TYPE, [
						Type::ADMIN_ARBITRAGE_CHECK,
						Type::WORKER_INPROGRESS_CHECK,
						Type::PAYER_INPROGRESS_CANCEL_REQUEST,
						Type::WORKER_INPROGRESS_CANCEL_REQUEST,
						Type::WORKER_INPROGRESS_CANCEL_DELETE,
						Type::WORKER_INPROGRESS_CANCEL_REJECT,
						Type::PAYER_INPROGRESS_CANCEL_DELETE,
						Type::PAYER_INPROGRESS_CANCEL_REJECT,
					])
					->select(Track::FIELD_ID, Track::FIELD_ORDER_ID, Track::FIELD_TYPE, Track::FIELD_DATE_CREATE, Track::FIELD_STATUS)
					->get()->all();
				if (count($checkAndRejectTracks)) {
					foreach ($checkAndRejectTracks as $track) {
						$ordersTracks[$track->OID][$track->MID] = $track;
					}
				}
			}
		}

		foreach ($orders as $key => $order) {
			$orders[$key]["can_add_review"] = TrackManager::canAddReview($order["id"]);
			$orders[$key]["canEditName"] = self::checkCanEditName($order);
		}

		return [
			"sort" => $sort,
			"direction" => $direction,
			"orders" => $orders,
			"totalCount" => $count,
		];
	}

	/**
	 * Количество оставшегося времени до автопринятия
	 * @param int $starttime метка времени
	 * @param string $lang язык заказа
	 * @return string
	 */
	public static function timeUntilAutoaccept($starttime, $lang) {
		// Рассчитать кол-во часов, оставшихся до автоматического принятия работ по заказу
		// Дедлайн до автоматического принятия работ 3 дня
		// Время округляется в большую сторону, если больше 30 минут от часа. Например, 24 часа 31 минута = 25 часов
		// Округляется в меньшую сторону, если меньше 29 минут. Например, 24 часа 15 минут = 24 часа
		// Если осталось менее часа, то так и пишем: "(осталось менее 1 ч. до автопринятия)"

		// Расчитаем время автопринятия заказа с учетом выходных и праздников
		$autoAcceptTimestamp = self::getAutoAcceptTimestamp($starttime, $lang);

		// сколько осталось секунд до автопринятия, исключая выходные и праздники
		$diff = $autoAcceptTimestamp - time();

		$hoursLeftRaw = $diff / \Helper::ONE_HOUR;

		if ($hoursLeftRaw <= 0) {
			return "0 " . Translations::t("ч.");
		}
		if ($hoursLeftRaw < 1) {
			return Translations::t("Менее 1 ч.");
		}

		$hoursLeft = round($hoursLeftRaw);
		$daysUntilAutoacceptLeft = (int)($hoursLeft / 24);
		$hoursUntilAutoacceptLeft = $hoursLeft % 24;

		return ($daysUntilAutoacceptLeft > 0 ? $daysUntilAutoacceptLeft . " " . Translations::t("д.") : "") .
			($daysUntilAutoacceptLeft > 0 && $hoursUntilAutoacceptLeft > 0 ? " " : "") .
			($hoursUntilAutoacceptLeft > 0 ? $hoursUntilAutoacceptLeft . " " . Translations::t("ч.") : "");
	}

	/**
	 * Колонка для сортировки по фильтру в payerOrders
	 * @param string $filter
	 * @return string
	 */
	private static function dateColumnByGroup($filter) {
		$filterColumns = [
			"all" => "stime",
			"active" => "stime",
			"cancelled" => "date_cancel",
			"completed" => "date_done",
			"delivered" => "stime",
			"" => "stime",
		];

		if (array_key_exists($filter, $filterColumns)) {
			return $filterColumns[$filter];
		}

		return $filterColumns[""];
	}

	/**
	 * Возвращает строку с условием (например, для WHERE) для деления заказов по вкладкам.
	 *
	 * @param string $filter Название фильтра
	 * @param string $orderTableAlias Алиас таблицы orders
	 * @param int $userId Идентификатор пользователя
	 * @param bool $forWorker Пользователь выступает в заказах как продавец
	 *
	 * @return string
	 */
	private static function orderStatusConditionByFilter(string $filter, string $orderTableAlias, int $userId, bool $forWorker): string {
		switch ($filter) {
			case 'active':
				return "$orderTableAlias.status = " . OrderManager::STATUS_INPROGRESS;

			case 'cancelled':
				return "$orderTableAlias.status = " . OrderManager::STATUS_CANCEL;

			case 'unpaid':
				return "$orderTableAlias.status = " . OrderManager::STATUS_UNPAID;

			case 'missing_data':
				return "$orderTableAlias.data_provided = 0 AND $orderTableAlias.status = " . OrderManager::STATUS_INPROGRESS;

			case 'delivered':
				$statusCheck = "$orderTableAlias.status = " . OrderManager::STATUS_CHECK;
				$statusArbitrage = "$orderTableAlias.status = " . OrderManager::STATUS_ARBITRAGE;
				return "($statusCheck OR $statusArbitrage)";

			case 'completed':
				$statusCondition = "$orderTableAlias.status = " . OrderManager::STATUS_DONE;
				return $statusCondition;

			default:
				return "$orderTableAlias.status <> 0";
		}
	}

	/**
	 * Создание заказа для непакетного кворка (без post)
	 *
	 * @param int $kworkId Идентификатор кворка
	 * @param int $kworkCount Количество кворков
	 * @param array $extras Массив дополнительных опций в формате [id => count]
	 * @param boolean $cartMode Заказ в корзину
	 * @param boolean $isQuick Срочный заказ
	 * @param float $volume Числовой объем который хочет заказать покупатель
	 * @param int $volumeTypeId Идентификатор числового объёма в котором будет создан заказ
	 *
	 * @return array
	 * ["success" => false,"error" => "login"]
	 */
	public static function createOrder($kworkId, $kworkCount, $extras, $cartMode = false, $isQuick = false, float $volume = 0.0, int $volumeTypeId = 0) {

		$actor = UserManager::getCurrentUser();
		$mobileApi = false;

		if (!($kworkId > 0)) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		if (!$cartMode && !$actor) {
			return [
				"success" => false,
				"error" => self::ERROR_NEED_LOGIN
			];
		}

		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::CREATE_ORDER, $actor->id));
		$actor = UserManager::reloadActor();

		$kwork = new KworkManager($kworkId);

		if ($actor->lang != $kwork->getLang() && $actor->lang != \Translations::DEFAULT_LANG) {
			return [
				"success" => false,
				"error" => self::ERROR_LANG_MISMATCH,
			];
		}
		$stayonUnblock = false;
		// #5995 Если пользователь находится на разблокировке мы можем делать заказ
		if ($kwork->getActive() == KworkManager::STATUS_SUSPEND) {
			$stayonUnblock = \UserManager::onQueueforUnblock($kwork->get("userId"));
		}

		if (!$kwork->getId() || $kwork->isPackage() || (!KworkManager::canOrder($kwork->getFeat(), $kwork->getActive()) && !$stayonUnblock)) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		if ($kworkCount < 1) {
			$kworkCount = 1;
		}

		$kworkOrderPrice = $kwork->getPrice();

		if ($kworkCount > App::config("kwork.max_count")) {
			// Если заказано больше чем макс. кол-во кворков, то умпеньшим кол-во до максимального
			$kworkCount = App::config("kwork.max_count");
		}
		$volume = 0;

		$orderPrice = $kworkOrderPrice * $kworkCount;

		// Посчитаем срок выполнения от кол-ва кворков
		$orderDuration = self::getDuration($kwork->getWorkTime(), $kworkCount, $kwork->getCategory());

		$orderData = [
			"kworkId" => $kworkId,
			"kworkCount" => $kworkCount,
			"volume" => $volume,
			"volumeTypeId" => $volumeTypeId,
		];

		$currencyId = \Translations::getCurrencyIdByLang($kwork->getLang());
		$currencyRate = CurrencyExchanger::getInstance()->getCurrencyRateByLang($kwork->getLang());

		$turnover = self::getTurnover($kwork->getUserId(), $actor->id, $kwork->getLang());
		$currentTurnover = $turnover + $orderPrice;
		// Получение пакета для однопакетных кворков
		$packageData = $kwork->getStandardPackage();
		$packagePriceWorker = 0;

		if (App::config('module.quick.enable') && $isQuick) {
			$orderPrice *= 2;
			$kworkOrderPrice *= 2;
			$orderDuration = self::getQuickDuration($orderDuration);
			$orderData['isQuick'] = true;
		}

		$orderDuration *= Helper::ONE_DAY;

		$orderData["price"] = $orderPrice;

		$orderId = OrderManager::create($kworkId, $orderPrice, $kworkCount, $orderDuration, false, false, $isQuick, 0, 0, self::SOURCE_KWORK, $currencyId, $currencyRate);
		if (!$orderId) {
			return [
				"success" => false,
				"error" => self::ERROR_KWORK_NOT_FOUND
			];
		}

		try {
			OrderManager::payer_new_inprogress($orderId);
			return [
				"success" => true,
				"orderId" => $orderId,
				"redirect" => App::config("baseurl") . "/track?id=" . mres($orderId),
			];
		} catch (BalanceDeficitWithOrderException $exception) {
			Session\SessionContainer::getSession()->set("refillOrderId", $orderId);
			$redirect = App::config("baseurl") . $kwork->get(Kwork::FIELD_URL) . "?balance=1";
			return [
				"success" => false,
				"error" => self::ERROR_NEED_MORE_MONEY,
				"purse_amount" => $exception->getNeedMoney(),
				"redirect" => $redirect
			];
		} catch (Exception $exception) {
			Log::dailyErrorException($exception);
			return ["success" => false];
		}
	}

	/*
	 * Получить кешированный в статическом поле заказ
	 * и если его нет то из базы
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return object|false  - false если заказ не найден, иначе объект заказа
	 */
	public static function getCached($orderId) {

		if ($orderId < 1) {
			return false;
		}

		if (array_key_exists($orderId, self::$_cachedOrders)) {
			return self::$_cachedOrders[$orderId];
		}

		$order = self::getFromDB($orderId);
		if (empty($order)) {
			return false;
		}

		self::$_cachedOrders[$order->id] = $order;

		return $order;
	}

	/**
	 * Получение данных заказа из базы
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return object|false  - false если заказ не найден, иначе объект заказа
	 */
	public static function getFromDB($orderId) {
		global $actor;
		$sql = "SELECT
					ord.OID as 'id',
					ord.USERID as 'userId',
					ord.PID as 'kworkId',
					ord.status,
					ord.in_work,
					ord.date_done,
					ord.time_added,
					ord.count,
					ord.price as totalprice,
					ord.crt,
					ord.stime,
					ord.date_check,
					ord.deadline,
					ord.duration,
					case
						when
							(deadline IS NULL OR deadline > unix_timestamp())
						then 0
						else 1 end as 'late',
					ord.is_quick,
					ord.kwork_days,
					ord.data_provided,
					ord.date_cancel,
					ord.last_track_id,
					ord.currency_id,
					ord.currency_rate,
					ord.date_inprogress,
					ord.portfolio_type,
					ord.worker_id as 'workerId',
					ord.bonus_text,
					ord.project_id,
       				ord.restarted,
					CASE WHEN 
                        ons.id is not null 
                    THEN 
                        ons.order_name
                    ELSE 
                        ord.kwork_title end as 'kwork_title'
				FROM
					orders ord
				LEFT JOIN 
				    order_names ons
				ON 
				    ons.user_id = :userId AND ons.order_id = ord.OID
				WHERE
					ord.OID = :orderId";

		$params = ["orderId" => (int)$orderId, "userId" => $actor->USERID];

		return App::pdo()->fetch($sql, $params, PDO::FETCH_OBJ);
	}

	/**
	 * Добавляет в объект order необходимы для отображения track данные
	 * из OrderData и даты из track
	 *
	 * @param object $order Заказ
	 */
	public static function fillOrderData(&$order) {
		$orderDataFields = [
			"kwork_price",
			"kwork_desc",
			"kwork_category",
		];
		$orderData = \Order\OrderDataManager::get($order->id, $orderDataFields);
		$order->kwork_desc = $orderData->kwork_desc;
		$order->kwork_inst = replace_full_urls($orderData->kwork_inst);
		$order->kwork_work = $orderData->kwork_work;
		$order->kwork_linkType = $orderData->kwork_link_type;
		if ($order->kwork_desc) {
			$order->kwork_desc = htmlentities(replace_full_urls(html_entity_decode($order->kwork_desc)));
		}
		$order->kwork_category = $orderData->kwork_category;

		// Масимальный объем который можно указать при дозаказе опций
		if ($orderData->kwork_price > 0 && $order->kwork_volume > 0) {
			$lang = Translations::getLangByCurrencyId($order->currency_id);
			$maxTotal = \App::config("order.volume_max_total_$lang");
			$order->maxOrderVolume = floor($maxTotal / $orderData->kwork_price) * $order->kwork_volume;
		}

		// время с момента заказа (оплаты)
		$order->date_create = $order->stime;

		// TODOрасписывается время кворков и опций: до этого расчет дней был чуть другой
		$order->isExplainDate = $order->stime > 1461700000;
		if (!$order->in_work) {
			$timeFromOrder = Helper::getAutoChancelHours($order->restarted) * Helper::ONE_HOUR - (time() - strtotime($order->date_inprogress));
			$order->inworkTimeCancelString = "";
			$order->inworkAttention = false;
			$timeToAttention = Helper::ONE_HOUR * App::config("kwork.need_worker_new_inwork_hours");
			//Если кворк был на паузе нужно учитывать это время
			$pauseDuration = self::getOrderPauseDuration($order->id);
			$timeFromOrder = $timeFromOrder + $pauseDuration;
			$order->countHourToInwork = ceil($timeFromOrder / Helper::ONE_HOUR);
			$order->timeToTakeStr = $order->countHourToInwork . declension($order->countHourToInwork, [' ч.', '&nbsp;ч.', '&nbsp;ч.']);
			$order->timeToTake = Helper::autoCancelString(Helper::AUTOCANCEL_MODE_TEXT_IN, $order->restarted);
			if ($timeFromOrder < $timeToAttention) {
				$order->inworkTimeCancelString = Helper::timeLeft($timeToAttention, false, false);
				$order->inworkAttention = true;
			}
		}
	}


	/**
	 * Пометить в заказе что данные предоставлены
	 * @param int $orderId Идентификатор заказа
	 * @param int $userId Идентификатор покупателя
	 */
	public static function setDataProvided(int $orderId, int $userId) {
		App::pdo()->update(self::TABLE_NAME,
			[self::F_DATA_PROVIDED => 1],
			"OID = :orderId",
			["orderId" => $orderId]
		);
	}


	public static function getOrderTime($orderDeadline, $orderDuration, $cancelDeadline = null) {
		if (!empty($cancelDeadline)) {
			return [
				'time' => (int)$cancelDeadline,
				'type' => 'duration',
			];
		} else {
			if (!empty($orderDeadline)) {
				return [
					'time' => (int)$orderDeadline,
					'type' => 'deadline',
				];
			} else {
				return [
					'time' => (int)$orderDuration,
					'type' => 'duration',
				];
			}
		}
	}

	/**
	 * Возвращает кол-во активных заказов указанного пользователя.
	 * Активными считаем заказы, которые не новые, не отменены и не завершены.
	 *
	 * @param int $userId если не указан, метод возьмет текущего пользователя.
	 *
	 * @return array [
	 *    - 'asPayer': кол-во заказов как заказчика
	 *    - 'asWorker': кол-во заказов как исполнителя
	 *  ]
	 */
	public static function getUserActiveOrdersCount(int $userId = 0): array {
		if (empty($userId)) {
			$userId = UserManager::getCurrentUserId();
		}
		// - У покупателя "Мои заказы (2)" - выводить количество заказов в работе и на проверке
		// - У продавца "Управление заказами (4)" - выводить количество заказов в работе (всех, к которым приступил и в которых еще не нажал "Начать работу") и на проверке.
		//		То есть в обоих случаях выводим количество заказов, которые сейчас в процессе.

		$query = Order::whereIn(Order::FIELD_STATUS, OrderManager::ACTIVE_ORDERS_STATUSES);

		$activeOrdersCountAsPayer = (clone $query)
			->where(Order::FIELD_USERID, $userId)
			->count();

		$activeOrdersCountAsWorker = (clone $query)
			->where(Order::FIELD_WORKER_ID, $userId)
			->count();

		$orderCount = [
			'asPayer' => $activeOrdersCountAsPayer,
			'asWorker' => $activeOrdersCountAsWorker,
		];

		return $orderCount;
	}

	/**
	 * Проверяет есть ли у пользователя активные заказы в роли продавца или покупателя
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function userHasActiveOrders(int $userId): bool {
		$result = false;
		$ordersCount = self::getUserActiveOrdersCount($userId);
		if (array_sum($ordersCount) > 0) {
			$result = true;
		}

		return $result;
	}


	/**
	 * Были ли заказы для пользователя $worker (если не указан, то текущий пользователь)
	 * в качестве исполнителя от пользователя $payer
	 *
	 * @param int $payer
	 * @param int|null $worker
	 * @return int
	 */
	public static function payerOrdersCount(int $payer, int $worker = null): int {
		if (is_null($worker)) {
			$worker = UserManager::getCurrentUser()->USERID;
		}

		$sql = "SELECT COUNT(*) as `count`
                FROM " . self::TABLE_NAME . "
                WHERE USERID = :payer AND status != :status AND worker_id = :worker";

		$orders_count = App::pdo()->fetch($sql, [
			'payer' => $payer,
			'status' => self::STATUS_NEW,
			'worker' => $worker
		]);

		return $orders_count['count'];
	}

	/**
	 * Были ли заказы для пользователя $worker (если не указан, то текущий пользователь)
	 * в качестве исполнителя от пользователя $payer
	 *
	 * @param int $worker
	 * @param int|null $payer
	 * @return int
	 */
	public static function workerOrdersCount(int $worker, int $payer = null): int {
		if (is_null($payer)) {
			$payer = UserManager::getCurrentUser()->USERID;
		}

		$sql = "SELECT COUNT(*) as `count`
                FROM " . self::TABLE_NAME . "
                WHERE USERID = :payer AND status != :status AND worker_id = :worker";

		$orders_count = App::pdo()->fetch($sql, [
			'payer' => $payer,
			'status' => self::STATUS_NEW,
			'worker' => $worker
		]);

		return $orders_count['count'];
	}


	/**
	 * Метод находит общие заказы текущего пользователя с пользователями по списку
	 * @param array $userlist - массив юзеров
	 * @return array - возвращает масив key - userid, value ["date", "status"]
	 */
	public static function userOrdersWithList(array $userlist) {
		if (count($userlist) > 0) {
			$userlist = array_unique($userlist);
			//Получаем список совместных заказов
			$actor = UserManager::getCurrentUser();
			$orderQuery = Order::whereIn(Order::FIELD_STATUS, [\OrderManager::STATUS_DONE, \OrderManager::STATUS_CANCEL]);
			if ($actor->type == UserManager::TYPE_WORKER) {
				$orderQuery->where(Order::FIELD_WORKER_ID, "=", $actor->id)
					->whereIn(Order::FIELD_USERID, $userlist);
			} else {
				$orderQuery->where(Order::FIELD_USERID, "=", $actor->id)
					->whereIn(Order::FIELD_WORKER_ID, $userlist);
			}

			$orderList = $orderQuery->get();

			//Выбираем последние сделанные/отмененные заказы
			if (count($orderList)) {
				$userToOrder = [];
				foreach ($orderList as $order) {
					$orderDate = $order->status == \OrderManager::STATUS_DONE ? $order->date_done : $order->date_cancel;
					$orderDate = strtotime($orderDate);
					$userId = $actor->type == UserManager::TYPE_WORKER ? $order->USERID : $order->worker_id;
					if ($userToOrder[$userId]["date"] < $orderDate) {
						$userToOrder[$userId] = ["date" => $orderDate, "status" => $order->status];
					}
				}
				return $userToOrder;
			}
		}
		return [];
	}


	/**
	 * Возвращает текствое представление статуса заказа по ID статуса
	 * @param $statusId
	 * @return mixed
	 */
	public static function getStatusTitle($statusId) {
		$statusTitles = [
			self::STATUS_NEW => Translations::t("Заявка создана"),
			self::STATUS_INPROGRESS => Translations::t("В работе"),
			self::STATUS_ARBITRAGE => Translations::t("Арбитраж"),
			self::STATUS_CANCEL => Translations::t("Отменен"),
			self::STATUS_CHECK => Translations::t("На проверке"),
			self::STATUS_DONE => Translations::t("Выполнен"),
		];

		return $statusTitles[$statusId];
	}

	/**
	 * Возвращает метку времени, когда надо засчитать заказ сделанным, если по прошествии kwork.autoaccept_days дней
	 * покупатель не принял работу
	 * @param string $startDateTime
	 * @param string $lang язык заказа
	 * @return int timestamp
	 */
	public static function getAutoAcceptTimestamp($startDateTime, $lang): int {
		$startTimestamp = strtotime($startDateTime);
		$endTimestamp = $startTimestamp + App::config("kwork.autoaccept_days") * Helper::ONE_DAY;

		// для EN-заказов вернем время автопринятия заказа без учета праздников и выходных
		if ($lang == Translations::EN_LANG) {
			return $endTimestamp;
		}

		// получим кол-во секунд, которое приходится на выходные и праздники
		$timeOnHolidays = 0;

		// если на выходные приходится больше, чем задано часов
		if ($timeOnHolidays > App::config("kwork.autoaccept_holidays_threshold_hours") * Helper::ONE_HOUR) {

			// то расширим время на заданную величину
			$endTimestamp += App::config("kwork.autoaccept_holidays_extend_hours") * Helper::ONE_HOUR;
		}

		return $endTimestamp;
	}

	/**
	 * Проверяет наличие арбитража и удаляет запись о нем если арбитраж не был завершен
	 * @param $orderId
	 */
	public static function doneOrderRemoveArbitrage($orderId) {
		return;
	}

	/**
	 * Суммарный оборот между продавцом и покупателем
	 *
	 * @param int $workerId Id продавца
	 * @param int $payerId Id покупателя
	 * @param string $lang Язык валюты
	 *
	 * @return float
	 */
	public static function getTurnover(int $workerId, int $payerId, string $lang): float {
		static $cache = [];

		$cacheKey = $workerId . "_" . $payerId . "_" . $lang;

		$turnover = $cache[$cacheKey];

		if (is_null($turnover)) {
			$statusesWithoutPaidStages = [self::STATUS_NEW, self::STATUS_CANCEL];

			$orders = Order::with(["tips"])
				->where(Order::FIELD_WORKER_ID, $workerId)
				->where(Order::FIELD_USERID, $payerId)
				->whereNotIn(Order::FIELD_STATUS, $statusesWithoutPaidStages)
				->get();

			$turnover = 0.0;
			$targetCurrency = \Translations::getCurrencyIdByLang($lang);

			/**
			 * @var Order $order
			 */
			foreach ($orders as $order) {
				$orderPrice = 0;
				if ($order->currency_id == $targetCurrency) {
					if ($order->isDone()) {
						$orderPrice = $order->price;
					}
				} else {
					$payerAmount = 0;
					$orderCurrencyPrice = 0;
					$orderCurrencyCRT = 0;
					$workerAmount = 0;
					if ($order->isDone()) {
						$payerAmount = $order->payer_amount;
						$orderCurrencyPrice = $order->price;
						$orderCurrencyCRT = $order->crt;
						$workerAmount = $order->worker_amount;
					}

					$rate = null;
					if ($orderCurrencyPrice > 0 && $payerAmount != $orderCurrencyPrice) {
						$rate = $payerAmount / $orderCurrencyPrice;
					} elseif ($orderCurrencyCRT > 0 && $workerAmount != $orderCurrencyCRT) {
						$rate = $workerAmount / $orderCurrencyCRT;
					}
					$orderPrice = \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
						$orderCurrencyPrice,
						$order->currency_id,
						$targetCurrency,
						$rate
					);
				}

				if ($orderPrice < 0.001) {
					continue;
				}

				if ($order->tips) {
					if ($order->currency_id == $targetCurrency) {
						$orderPrice += $order->tips->amount;
					} else {
						$rate = $order->tips->currency_rate > 1 ? $order->tips->currency_rate : null;
						$orderPrice += CurrencyExchanger::getInstance()->convertByCurrencyId(
							$order->tips->amount,
							$order->currency_id,
							$targetCurrency,
							$rate
						);
					}
				}

				$turnover += $orderPrice;
			}

			$cache[$cacheKey] = $turnover;
		}

		return $turnover;
	}

	/**
	 * #6318 Расчет комиссии Kwork в зависимости от оборота с конкретным клиентом
	 *
	 * @param float $price Стоимость услуги
	 * @param float $turnover Оборот с клиентом
	 * @param string $lang Язык валюты
	 *
	 * @return \Commission
	 */
	public static function calculateCommission(float $price, float $turnover, string $lang): \Commission {
		$ranges = [];

		$priceWorker = round($price - $price * App::config("commission_percent") / 100, 2);
		$priceKwork = round(($price - $priceWorker), 2);

		return new \Commission($price, $priceWorker, $priceKwork, $turnover, $ranges);
	}

	/**
	 * Записать в заказ решение об опубликовании портфолио
	 *
	 * @param Order $order Модель заказа
	 * @param bool $payerAllowPortfolio Пользовательский ввод решения об разрешении/запрете публикации портфолио
	 *
	 * @return bool Результат разрешено ли портфолио в заказе
	 */
	public static function choosePortfolioAllowed($order, $payerAllowPortfolio) {
		if ($order->kwork && $order->kwork->getPortfolioType() !== \Attribute\AttributeManager::PORTFOLIO_TYPE_NONE) {
			if ($payerAllowPortfolio) {
				OrderManager::allowPortfolio($order->OID);

				return true;
			} else {
				OrderManager::denyPortfolio($order->OID);
			}
		}
		return false;
	}

	/**
	 * Обновить кол-ва выполненных заказов для кворка и пользователя
	 * @param int $kworkId Идентификатор кворка
	 * @param int $userId Идентификатор пользователя
	 */
	public static function updateOrderDoneCount($kworkId, $userId) {
		return;
	}


	/**
	 * Проверка существования действующего запроса на отмену от покупателя
	 *
	 * @param int $orderId
	 *
	 * @return bool
	 */
	public static function isNewCancelRequestExist(int $orderId) {
		$track = Track::where(Track::FIELD_ORDER_ID, $orderId)
			->whereIn(Track::FIELD_TYPE, Type::cancelRequestTypes())
			->where(Track::FIELD_STATUS, "new")
			->first();

		if ($track) {
			return false;
		}
		return true;
	}

	/**
	 * Возвращает идентификаторы заказов, в которых есть запросы на отмену от продавцов
	 *
	 * @return array
	 */
	public static function getOrderIdsWithWorkerCancelRequest(): array {
		return DB::table(Track::TABLE_NAME)
			->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
			->pluck(Track::FIELD_ORDER_ID)
			->toArray();
	}

	/**
	 * Автоотмена заказов, которые не взяли в работу
	 * @return int Кол-во отменённых заказов
	 * @throws Exception
	 */
	public static function autoCancelOrders() {
		$secToAutocancel = \App::config("kwork.autocancel_hours") * \Helper::ONE_HOUR;
		$autoCancelTime = time() - $secToAutocancel;

		$orders = Order::query()
			// Если заказ в статусе в работе
			->where(Order::FIELD_STATUS, \OrderManager::STATUS_INPROGRESS)
			// И продавец не взял его в работу
			->where(Order::FIELD_IN_WORK, \OrderManager::STATE_NOT_IN_WORK)
			// И заказ создан раньше чем kwork.autocancel_hours (из настроек) часов назад
			->where(Order::FIELD_STIME, "<", $autoCancelTime)
			// И в заказе нет заявки на отмену от продавца
			->whereNotIn(Order::FIELD_OID, self::getOrderIdsWithWorkerCancelRequest())
			// И заказ не был перезапущен из состояния "Выполнен"
			->where(Order::FIELD_RESTARTED, false)
			->get()
			->all();

		$orderIds = array_unique(array_filter(array_column($orders, Order::FIELD_OID)));
		$payerInProgressCancelTracks = TrackManager::getPayerCancelRequestTrackOrderIds($orderIds);
		$cancelled = 0;
		/**
		 * @var Order $order
		 */
		foreach ($orders as $order) {
			$startTime = intval($order->stime);
			$payerInProgressCancelTrack = $payerInProgressCancelTracks[$order->OID] ?? false;
			// Если была ли заявка от покупателя на отмену этого заказа
			if ($payerInProgressCancelTrack) {
				// Проверим были ли от продавца сообщения в заказе
				$workerMessages = Track::where(Track::FIELD_ORDER_ID, $order->OID)
					->where(Track::FIELD_TYPE, Type::TEXT)
					->where(Track::FIELD_USER_ID, $order->worker_id)
					->first();
				// Если от продавца были сообщения - ничего не предпринимать
				if ($workerMessages) {
					continue;
				}
			} else {
				//Считаем дату начала заказа с учетом паузы
				$pauseTime = self::getOrderPauseDuration($order->OID);
				$startTime += $pauseTime;
			}

			if ($startTime < $autoCancelTime) {
				// Отмена заказа кроном
				if (!self::cron_inprogress_cancel($order->OID, $payerInProgressCancelTrack)) {
					continue;
				}

				$cancelled++;

				usleep(1000);
			}
		}

		return $cancelled;
	}


	/**
	 * Статусы заказа в которых доступна возжность отмены заказа для покупателя
	 *
	 * @return array
	 */
	public static function getCancelAvailableStatusesForPayer(): array {
		$commonStatuses = self::getCommonCancelAvailableStatuses();
		$commonStatuses[] = self::STATUS_CHECK;
		return $commonStatuses;
	}

	/**
	 * Статусы заказа в которых доступна возжность отмены заказа для продавца
	 *
	 * @return array
	 */
	public static function getCancelAvailableStatusesForWorker(): array {
		return self::getCommonCancelAvailableStatuses();
	}

	/**
	 * Статусы заказа в которых доступна возжность отмены заказа и для продавца и для покупателя
	 *
	 * @return array
	 */
	private static function getCommonCancelAvailableStatuses(): array {
		return [
			self::STATUS_INPROGRESS,
			self::STATUS_ARBITRAGE,
			self::STATUS_UNPAID,
		];
	}

	/**
	 * Получить пропорцию, в которой в заказе используется дополнительное время кворка с учетом категории
	 *
	 * @param Category|null $category
	 *
	 * @return float
	 */
	public static function getMultiKworkRate(Category $category = null): float {
		return ($category && $category->isKworkFullRate()) ? 1 : self::MULTI_KWORK_RATE;
	}


}
