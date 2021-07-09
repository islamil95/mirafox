<?php

use Core\DB\DB;
use Core\Exception\BalanceDeficitWithOperationException;
use Core\Exception\SimpleJsonException;
use Model\Operation;
use Model\OrderStages\OrderStage;
use Model\PaymoreRefill;
use Model\SolarOperation;
use Model\User;
use Model\UserData;
use Model\Work;
use Withdraw\Withdraw;
use Withdraw\WithdrawFactory;

class OperationManager {

	use \Operations\OperationUtils;
	const TABLE_NAME = "operation";
	const TABLE_NAME_REFILL_LOG = "refill_log"; //Таблица лога ручного пополнения баланса
	const FIELD_ID = "id"; //Идентификатор операции
	const FIELD_AMOUNT = "amount"; //Средства в операции
	const FIELD_USER_ID = "user_id"; //Идентификатор пользователя
	const FIELD_UNITPAYID = "unitpayId"; //Юнитпей id
	const FIELD_WEBMONEYID = "webmoneyId"; //Webmoney id
	const FIELD_PAYMENT = "payment"; //Тип платежной системы
	const FIELD_BASE_AMOUNT = "base_amount"; // Основной счет
	const FIELD_BONUS_AMOUNT = "bonus_amount"; //Бонусные средства
	const FIELD_BILL_AMOUNT = "bill_amount"; //Средства по безналичному расчету
	const FIELD_CARD_AMOUNT = "card_amount"; //Средства по карточному счету
	const FIELD_KWORK_ID = "kwork_id"; //Кворк к которому привязана операция
	const FIELD_ORDER_ID = "order_id"; //Заказ к которому привязана операция
	const FIELD_BONUS_ID = "bonus_id";
	const FIELD_TYPE = "type"; //Тип операции
	const FIELD_STATUS = "status"; //Статус операции
	const FIELD_DATE_CREATE = "date_create"; //Дата создания операции
	const FIELD_DATE_DONE = "date_done"; //Дата завершения операции
	const FIELD_IS_EXTRA = "is_extra";
	const FIELD_REF_ID = "ref_id"; //идентификатор реферала
	const FIELD_IS_SEND_ON_WITHDRAW = "is_send_on_withdraw"; //Отправлена ли операция на вывод
	const FIELD_WD_OPERATION_FROM_ID = "wd_operation_from_id"; //идентификатор операции которая была отправлена на вывод
	const FIELD_ABUSE_POINTS = "abuse_points";
	const FIELD_ABUSE_POINTS_TOTAL = "abuse_points_total";
	const FIELD_SOLAR_STAFF_AMOUNT = "solar_staff_amount";
	const FIELD_IS_TIPS = "is_tips"; // является операцией по чаевым (бонус продавцу)
	const FIELD_REQUEST_ID = "request_id"; //id запроса в операциях начисления оплаты за модерацию запроса
	const FIELD_SUB_TYPE = "sub_type"; //уточняющий подтип для операции
	const TABLE_UNITPAY = "unitpay";
	const FIELD_UNITPAY_ACCOUNT = "account";
	const FIELD_UNITPAY_OPERATIONID = "operation_id";

	/**
	 * Язык операции для фильтрации в админке (может не совпадать с валютой операции)
	 */
	const FIELD_LANG = "lang";

	/**
	 * Сумма в валюте языка операции
	 */
	const FIELD_LANG_AMOUNT = "lang_amount";

	/**
	 * Описание
	 */
	const FIELD_DESCRIPTION = "description";

	/**
	 * <p>Поле для хранения статуса, была ли средства операции пополнения с карты переведены на основной счет</p>
	 * <p>operation.transferred - tinyint</p>
	 * <p>DEFAULT NULL</p>
	 */
	const FIELD_TRANSFERRED = "transferred";
	const NOT_TRANSFERED = 0;
	const TRANSFERED = 1;

	/**
	 * Флаг безопасных пополнений с карты 3ds Unitpay
	 */
	const FIELD_WITH_3DS = "with_3ds";
	const FIELD_WITH_3DS_YES = 1;
	const FIELD_WITH_3DS_NO = 0;
	const REFILL_CARD_COUNT_LIMIT = 3;
	const REFILL_CARD_DAYS_LIMIT = 30;


	/**
	 * Константы типов платежных систем
	 */
	const FIELD_PAYMENT_QIWI1 = "qiwi";
	const FIELD_PAYMENT_QIWI3 = "qiwi3";
	const FIELD_PAYMENT_WEBMONEY = "webmoney";
	const FIELD_PAYMENT_WEBMONEY2 = "webmoney2";
	const FIELD_PAYMENT_WEBMONEY3 = "webmoney3";
	const FIELD_PAYMENT_CARD = "card";
	const FIELD_PAYMENT_CARD2 = "card2";
	const FIELD_PAYMENT_CARD3 = "card3";
	const FIELD_PAYMENT_ADMIN = "admin";
	const FIELD_PAYMENT_BILL = "bill";

	/**
	 * Способы вывода по типам
	 */
	public static $paymentsByType = [
		Withdraw::QIWI_PAYMENT => [
			self::FIELD_PAYMENT_QIWI1,
			self::FIELD_PAYMENT_QIWI3
		],
		Withdraw::WEBMONEY_PAYMENT => [
			self::FIELD_PAYMENT_WEBMONEY2,
			self::FIELD_PAYMENT_WEBMONEY3
		],
		Withdraw::SOLAR_CARD_PAYMENT => [
			self::FIELD_PAYMENT_CARD3
		]
	];

	/**
	 * Константы типов операций (дополняемый список)
	 */
	const FIELD_TYPE_REFILL = "refill"; //Пополнение
	const FIELD_TYPE_WITHDRAW = "withdraw"; //Вывод
	const FIELD_TYPE_MONEYBACK = "moneyback"; // Возврат пополнения

	/**
	 * Возврат из резервирования на заказ
	 */
	const TYPE_REFUND = "refund";

	/**
	 * Расход баланса пользователя по заказу (основной)
	 */
	const TYPE_ORDER_OUT = "order_out";

	/**
	 * Расход баланса пользователя по заказу (бонусный)
	 */
	const TYPE_ORDER_OUT_BONUS = "order_out_bonus";

	/**
	 * Расход баланса пользователя по заказу (безнал)
	 */
	const TYPE_ORDER_OUT_BILL = "order_out_bill";

	/**
	 * Пополнение баланса продавца за выполненный заказ
	 */
	const TYPE_ORDER_IN = "order_in";

	const TYPE_CANCEL_BONUS = "cancel_bonus"; // Сгорание неиспользованного бонуса
	const TYPE_REFILL_REFERAL = "refill_referal"; // Начисление за реферала

	/**
	 * Константы поля статуса операции (operation.status)
	 */
	const FIELD_STATUS_NEW = "new"; //Новая операция
	const FIELD_STATUS_INPROGRESS = "inprogress"; //Операция в процессе выполнения
	const FIELD_STATUS_DONE = "done"; // Операция завершена
	const FIELD_STATUS_CANCEL = "cancel"; // Операция отменена

	/**
	 * Константы поля статуса вывода (operation.wd_status)
	 */
	const FIELD_WD_STATUS_NEW = "new"; //Новый вывод
	const FIELD_WD_STATUS_INPROGRESS = "inprogress"; //Вывод в процессе выполнения
	const FIELD_WD_STATUS_DONE = "done"; // Вывод завершен
	const FIELD_WD_STATUS_CANCEL = "cancel"; // Вывод отменен

	/**
	 * Константы подтипа операции operation.sub_type
	 */
	const FIELD_ST_REFILL_UNITPAY = "unitpay"; //Пополнение через юнитпей
	const FIELD_ST_REFILL_BILL = "bill"; //Пополнение для юрлич через счет
	const FIELD_ST_REFILL_ADMIN = "admin"; //Пополнение руками через админку
	const FIELD_ST_REFILL_EPAYMENTS = "epayments"; //Пополнение через Epayments
	const FIELD_ST_REFILL_PAYPAL = "paypal"; //Пополнение через Paypal
	const FIELD_ST_REFILL_PAYMORE = "paymore"; // Пополнение через paymore (Касса)

	/**
	 * Константы поля  (operation.is_send_on_withdraw)
	 */
	const FIELD_IS_SEND_ON_WITHDRAW_SEND = 1; //Была отправлена
	const FIELD_IS_SEND_ON_WITHDRAW_NOT_SEND = 0; // Не была отправлена

	/**
	 * Идентификатор валюты операции
	 */
	const FIELD_CURRENCY_ID = "currency_id";

	/**
	 * Курс валюты
	 */
	const FIELD_CURRENCY_RATE = "currency_rate";

	/**
	 * ID конкурса
	 */
	const FIELD_CONTEST_ID = "contest_id";

	/**
	 * Соответствие полей денежных средств в таблице Операций и полей в таблице Пользователей
	 */
	const MEMBER_FUNDS_FIELD_CONSISTENCY = [
		self::FIELD_BASE_AMOUNT => UserManager::FIELD_FUNDS,
		self::FIELD_BONUS_AMOUNT => UserManager::FIELD_BFUNDS,
		self::FIELD_BILL_AMOUNT => UserManager::FIELD_BILL_FUNDS,
		self::FIELD_CARD_AMOUNT => UserManager::FIELD_CARD_FUNDS,
	];

    // типы операций в удобочитаемом человеческом виде для селекта в фильтре
    const PAYMENT_TYPES_DESCRIPTIONS = [
        "withdraw"		=> "Вывод",
	    self::FIELD_TYPE_MONEYBACK => "Возврат пополнения баланса",
        "refund"		=> "Возврат оплаты заказа",
        "refill"		=> "Пополнение баланса",
        "order_out"		=> "Оплата заказа",
        "order_in"		=> "Получение оплаты за заказ",
        "cancel_bonus"	=> "Бонус сгорание",
    ];

	/**
	 * Статическое поле для хранения типов refill unitpay
	 * @var array
	 */
	public static $paymentUnitpayRefillTypes;

	/**
	 * Статическое поле для хранения типов refill paymore
	 * @var array
	 */
	public static $paymentPaymoreRefillTypes;

	/**
	 * Статическое поле для хранения типов withdraw
	 * @var array
	 */
	public static $paymentWithdrawTypes;

	/**
	 * Положительные типы операций
	 * @return array
	 */
	public static function positiveOperType() {
	    return array(
            'order_in' => \Translations::t("Начисление за сделанный заказ"),
            'refill' => \Translations::t("Пополнение баланса"),
            'refund' => \Translations::t("Возврат оплаты заказа"),
            'refill_referal' => \Translations::t("Начисление за реферала"),
            'refill_moder_kwork' => \Translations::t("Начисление за модерацию кворка"),
            'refill_moder_request' => \Translations::t("Начисление за модерацию запроса")
        );
    }

	/**
	 * Отрицательные типы операций
	 * @return array
	 */
	public static function negativeOperType() {
        return [
            self::TYPE_ORDER_OUT => \Translations::t("Оплата заказа"),
            self::FIELD_TYPE_WITHDRAW => \Translations::t("Вывод"),
	        self::FIELD_TYPE_MONEYBACK => \Translations::t("Возврат пополнения баланса"),
            self::TYPE_CANCEL_BONUS => \Translations::t("Сгорание неиспользованного бонуса")
        ];
    }

	/**
	 * Массив типов платежных систем использующихся в SolarStaff
	 * @var array
	 */
	private static $_solarPaymentsArray = [
		self::FIELD_PAYMENT_QIWI3 => 'Qiwi',
		self::FIELD_PAYMENT_WEBMONEY3 => 'Webmoney',
		self::FIELD_PAYMENT_CARD3 => 'Карта'
	];

	/**
	 * Получить массив типов платежных систем solarStaff
	 * @param bool $withLabels true - вернётся массив с названиями, false - вернётся массив без названий
	 * @return array
	 */
	public static function getSolarStaffPayments($withLabels = false) {
		return ($withLabels) ? self::$_solarPaymentsArray : array_keys(self::$_solarPaymentsArray);
	}


	/**
	 * Вывод с аккаунта
	 *
	 * @param string $type Тип вывода
	 * @param int $amount Сумма
	 * @return bool|string
	 * @throws Exception
	 */
	public static function withdraw($type, $amount) {
		$userInfo = UserManager::getCurrentUser();
		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::WITHDRAW_USER, $userInfo->id));

		$withdraw = WithdrawFactory::build($type, $amount);
		//заполнить последний тип вывода
		$usedData = UserData::find($userInfo->id);
		$usedData->withdraw_type = self::getPurseTypeByPayment($type);
		$usedData->save();

		if ($withdraw->error) {
			return $withdraw->error;
		}

		$withdraw
				->setAccount()
				->checkAmount()
				->amountExplode()
				->execWithdraw();

		unset($lock);

		$error = $withdraw->getError();
		if ($error)
			return $error;

		return true;
	}

	/**
	 * Заменить номер карты при выводах
	 * @param string $cardNumber - номер карты
	 * @return string
	 */
	public static function replaceCardNumber($cardNumber) {
		return mb_substr($cardNumber, 0, 6) . "******" . mb_substr($cardNumber, 12);
	}

	/**
	 * Получить данные операции по идентификатору
	 * @param int $operationId
	 * @return boolean|object
	 */
	public static function get($operationId) {
		$operationId = (int) $operationId;
		if ($operationId > 0) {
			$operation = \Core\DB\DB::table(Operation::TABLE_NAME . " as o")
				->leftJoin(SolarOperation::TABLE_NAME . " as os", "o." . Operation::FIELD_ID, "os." . SolarOperation::FIELD_OPERATION_ID)
				->where("o." . Operation::FIELD_ID, $operationId)
				->select([
					"o.*",
					"os." . SolarOperation::FIELD_WAMOUNT,
					"os." . SolarOperation::FIELD_ACCOUNT,
					"os." . SolarOperation::FIELD_STATUS . " as wd_status",
					"os." . SolarOperation::FIELD_REASON . " as wd_reason",
					"os." . SolarOperation::FIELD_TASK_ID . " as solar_staff_id",
					"os." . SolarOperation::FIELD_SOLAR_STATUS . " as solar_staff_status",
					"os." . SolarOperation::FIELD_DATE_START . " as solar_staff_date_start",
					"os." . SolarOperation::FIELD_TRANSACTIONS . " as solar_staff_transactions",
					"os." . SolarOperation::FIELD_DONE_AMOUNT . " as solar_staff_done_amount",
					"os." . SolarOperation::FIELD_AMOUNT . " as solar_staff_amount",
					"os." . SolarOperation::FIELD_CHECK_ATTEMPT . " as solar_staff_check_attempt",
					"os." . SolarOperation::FIELD_DATE_START . " as date_start_withdraw",
					"os." . SolarOperation::FIELD_DATE_DONE . " as date_done_withdraw",
				])
				->first();
			if ($operation) {
				$unitPay = \Model\Unitpay::where(\Model\Unitpay::FIELD_OPERATION_ID, $operationId)
					->first();
				$operation->unitpayId = $unitPay ? $unitPay->unitpay_id : NULL;
				return $operation;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Создать запись в таблице operation
	 * @param array $fields Ассоциативный массив полей, которые надо установить ["field" <=> $value]
	 * @return int
	 */
	public static function create($fields) {
		if (empty($fields) || !is_array($fields)) {
			return false;
		}
		return \Core\DB\DB::table(self::TABLE_NAME)->insertGetId($fields);
	}

	/**
	 * Получить список операций текущего пользователя
	 * @param int $limit - количество операций
	 * @param int $offset - смещение
	 * @param null|array $filter - фильтр поиска операций
	 * @return array - ["operations" => спиок операций,
						"total" => количество операций,
						"totalSum" => сумма всех операций,
						"ordersList" => список заказов,
						"allUserOperations" => типы пользовательских операций,
						"dateLimit" => лимит от и до когда можно выбирать операции]
	 */
	public static function getUserOperations(int $limit, int $offset = 0, $filter = null) {
		$userId = UserManager::getCurrentUserId();
		//Обработка данных с пользовательского фильтра
		$filterQuery = [];
		$dbFilter = "";
		$sqlParams = [];
		$positiveOperTypeKeys = array_keys(self::positiveOperType());
		$leftJoin = '';
		if ($filter != null) {
			//Делаем поиск по ID кворка в ордерах или же по ID кворка в операции (если идет фильтрация модерируемых кворков)
			if ($filter["kwork"]) {
				$filterQuery[] = "ord.PID=:kwork_id AND o.type = 'order_in'";
				$sqlParams["kwork_id"] = $filter['kwork'];
				//Если у нас идет поиск по кворку, джойним таблицу, в остальных случаях она не нужна.
				$leftJoin = "LEFT JOIN
								orders ord
							ON
								ord.OID = o.order_id";
			}
			if ($filter["dateFrom"]) {
				$filterQuery[] = "o.date_create >= :date_from";
				$sqlParams["date_from"] = date("Y-m-d 00:00:00", strtotime($filter['dateFrom']));
			}
			if ($filter["dateTo"]) {
				$filterQuery[] = "o.date_create <= :date_to";
				$sqlParams["date_to"] = date("Y-m-d 23:59:59", strtotime($filter['dateTo']));
			}
			if ($filter["typeOperation"][0] != "all" && $filter["typeOperation"][0] != "") {
				$keys = array();
				foreach ($filter["typeOperation"] as $k => $v) {
					$keys[] = ":p" . $k;
					$sqlParams["p" . $k] = $v;
				}
				$filterQuery[] = "o.type IN(" . implode(",", $keys) . ")";
			}
			if (count($filterQuery) > 0) {
				$dbFilter = " AND " . implode(" AND ", $filterQuery);
			}
		}
		$limitContent = "";
		$sqlParams["user_id"] = $userId;

		//Если $limit указан как 0 то, убираем лимит запроса, это нужно для экспорта операций.
		if ($limit > 0) {
			$limitContent = " LIMIT :offset, :limit";
			$sqlParams["offset"] = $offset;
			$sqlParams["limit"] = $limit;
		}

		$sql = "SELECT
					o.*,
					o.status as operation_status,
					o.date_create as time,
					o.amount as price
				FROM
					operation o
				".$leftJoin."
				WHERE 
					o.user_id = :user_id AND NOT (o.type = 'refill' AND o.status = 'new')
					" . $dbFilter . "
				ORDER BY 
					o.id desc" . $limitContent;

		$operations = App::pdo()->fetchAll($sql, $sqlParams);
		unset($sqlParams["offset"]);
		unset($sqlParams["limit"]);

		$operationIds = array_unique(array_filter(array_column($operations, "id")));

		//Если у нас выводятся операции баланса, подгружаем данные с таблицы unitpay
		$unitPay = [];
		$paymoreRefills = [];
		if (strpos($dbFilter, "refill") || $filter["typeOperation"][0] == "all" || empty($filter["typeOperation"])) {

			//Если лимит задан (производится экспорт записей), берем платежи юнитпей по операциям, если нет то все по юзеру

			if ($limit) {
				$opids = array_filter(array_map(function($v) {
					if ($v['sub_type'] == "unitpay")
						return $v["id"];
				}, $operations));

				if(count($opids) > 0){
					$unitPay = self::getUnitpayByOperations($opids);
				}

				$paymoreRefillsOperationsIds = array_column(array_filter($operations, function ($operation) {
					return $operation[Operation::FIELD_SUB_TYPE] === OperationManager::FIELD_ST_REFILL_PAYMORE;
				}), Operation::FIELD_ID);

				if ($paymoreRefillsOperationsIds) {
					$paymoreRefills = PaymoreRefill::whereIn(PaymoreRefill::FIELD_OPERATION_ID, $paymoreRefillsOperationsIds)
						->get()
						->keyBy(PaymoreRefill::FIELD_OPERATION_ID)
						->all();
				}
			} else {
				$unitPay = self::getUnitpayByUser($userId);
				$paymoreRefills = PaymoreRefill::where(PaymoreRefill::FIELD_USER_ID, $userId)
					->get()
					->keyBy(PaymoreRefill::FIELD_OPERATION_ID)
					->all();
			}

		}

		//Получаем сумму всех операций, а так же типы операций которые есть
		$sql = "SELECT
				SUM(case when type IN('" . implode("','", $positiveOperTypeKeys). "')
					then o.amount 
					else -o.amount end) 
					as orderSum,
					o.type
				FROM 
					operation o
					" . $leftJoin . "
				WHERE 
					o.user_id = :user_id 
					AND o.status IN ('new', 'inprogress', 'done')
					AND NOT (o.type = 'refill' AND o.status = 'new') 
					".$dbFilter." 
				GROUP BY
					o.type";

		$operationsSumType = App::pdo()->fetchAll($sql, $sqlParams);

		$allUserOperations = array();
		$amountOperSum = 0;
		$operationsTypes = array();

		//Считаем сумму всех операций, попутно выбираем те типы операций которые есть у пользователя для фильтра
		if(count($operationsSumType) > 0) {
			foreach($operationsSumType as $os){
				$amountOperSum += $os["orderSum"];
				$operationsTypes[] = $os["type"];
				$isFind = 0;
				foreach(self::positiveOperType() as $key => $value){
					if($key == $os["type"]){
						$allUserOperations["positive"][] = array("name" => $key, "text" => $value);
						$isFind = 1;
						break;
					}
				}
				if($isFind == 0){
					foreach(self::negativeOperType() as $key => $value){
						if($key == $os["type"]){
							$allUserOperations["negative"][] = array("name" => $key, "text" => $value);
							break;
						}
					}
				}

			}
		}

		$ordersList = \Model\Kwork::where('USERID', $userId)
			->get(['PID as kwork_id', 'gtitle as kwork_title'])
			->toArray();

		//Максимальная и минимальная дата для задания ограничений календаря в фильтре #7139
		$user = UserManager::getCurrentUser();
		$dateLimits["maxDate"] = date("Y-m-d");
		$dateLimits["minDate"] = date("Y-m-d", $user->addtime);

		$ordersIds = [];
		$postsIds = [];
		$usersIds = [];
		$bonusesIds = [];
		$orderStageIds = [];

		foreach ($operations as $item) {
			if ($item['order_id']) {
				$ordersIds[$item['order_id']] = $item['order_id'];
			}

			if ($item['kwork_id']) {
				$postsIds[$item['kwork_id']] = $item['kwork_id'];
			}
			if ($item['ref_id']) {
				$usersIds[$item['ref_id']] = $item['ref_id'];
			}
			if ($item['bonus_id']) {
				$bonusesIds[$item['bonus_id']] = $item['bonus_id'];
			}
			if ($item["order_stage_id"]) {
				$orderStageIds[$item["order_stage_id"]] = $item["order_stage_id"];
			}
		}

		$orderStages = [];
		if ($orderStageIds) {
			$orderStages = OrderStage::whereKey($orderStageIds)
				->get([
					OrderStage::FIELD_ID,
					OrderStage::FIELD_TITLE,
					OrderStage::FIELD_NUMBER,
				])
				->keyBy(OrderStage::FIELD_ID)
				->toArray();
		}

		$operationOrders = [];
		$operationKworks = [];
		$operationUsers = [];
		$operationBonuses = [];
		$params = [];

		if (count($ordersIds)) {
			$sql = "SELECT 
						ord.OID id, 
						ord.cltime, 
						ord.status, 
						ord.crt, 
						ord.PID kwork_id, 
						ord.worker_id, 
						ord.USERID payer_id, 
						CASE WHEN 
                            ons.id is not null 
                        THEN 
                            ons.order_name 
                        ELSE 
                            ord.kwork_title end 'gtitle'
					FROM
						orders ord
					LEFT JOIN 
					    order_names ons
					ON 
					    ons.order_id = ord.OID
					AND 
					    ons.user_id = :userId
					WHERE
						ord.OID IN (" . App::pdo()->arrayToStrParams($ordersIds, $params, PDO::PARAM_INT, 'orderId') . ")";
            $params["userId"] = $userId;
			$operationOrders = App::pdo()->fetchAllNameByColumn($sql, 0, $params);

			foreach ($operationOrders as $order) {
				$postsIds[$order['kwork_id']] = $order['kwork_id'];
				$usersIds[$order['worker_id']] = $order['worker_id'];
				$usersIds[$order['payer_id']] = $order['payer_id'];
			}

		}

		if (count($postsIds)) {
			$params = [];
			$sql = "SELECT 
						PID as id, url as kworkUrl, gtitle, ctp
					FROM
						posts
					WHERE
						PID IN (" . App::pdo()->arrayToStrParams($postsIds, $params, PDO::PARAM_INT, 'postId') . ")";

			$operationKworks = App::pdo()->fetchAllNameByColumn($sql, 0, $params);
		}

		if (count($usersIds)) {
			$params = [];
			$sql = "SELECT 
						USERID as id, username
					FROM
						members
					WHERE
						USERID IN (" . App::pdo()->arrayToStrParams($usersIds, $params, PDO::PARAM_INT, 'userId') . ")";

			$operationUsers = App::pdo()->fetchAllNameByColumn($sql, 0, $params);
		}
		if (count($bonusesIds)) {
			$operationBonuses = BonusManager::getBonusesCodesByIds($bonusesIds);
		}

		$totalCount = self::getUserOperationCount($userId, $dbFilter, $sqlParams, $leftJoin);

		foreach ($operations as &$operation) {
			$order = $operationOrders[$operation['order_id']];
			if ($order) {
                $kwork = $operationKworks[$order['kwork_id']];
			} else {
				$kwork = $operationKworks[$operation['kwork_id']];
			}

			if(!empty($unitPay)) {
				if (array_key_exists($operation['id'], $unitPay)) {
					$up = $unitPay[$operation['id']];
					$operation['account'] = $up['account'];
					$operation['date_done'] = $up['date_create'];
					$operation['payment'] = $up['payment'];
				}
			}
			if (!empty($paymoreRefills)) {
				$paymoreRefill = $paymoreRefills[$operation[Operation::FIELD_ID]];
				if ($paymoreRefill instanceof PaymoreRefill) {
					$operation["account"] = $paymoreRefill->payment_account;
					$operation["payment"] = $paymoreRefill->payment_method;
				}
			}

			$operation['payer_name'] = $order ? $operationUsers[$order['payer_id']]['username'] : null;
			$operation['worker_name'] = $order ? $operationUsers[$order['worker_id']]['username'] : null;
			$operation['ctp'] = $kwork ? $kwork['ctp'] : null;
			$operation['PID'] = $kwork ? $kwork['id'] : null;
            $operation['gtitle'] = $order['gtitle'] ? $order['gtitle'] : null;
			$operation['kworkUrl'] = $kwork ? $kwork['kworkUrl'] : null;
			$operation['OID'] = $order ? $order['id'] : null;
			$operation['cltime'] = $order ? $order['cltime'] : null;
			$operation['status'] = $order ? $order['status'] : null;
			$operation['crt'] = $order ? $order['crt'] : null;
			$operation['referal_name'] = $operation['ref_id'] ? $operationUsers[$operation['ref_id']]['username'] : null;
			$operation['bonus_code'] = $operation['bonus_id'] ? $operationBonuses[$operation['bonus_id']]['code'] : null;
			$operation['bonus_code_day_count'] = $operation['bonus_id'] ? $operationBonuses[$operation['bonus_id']]['day_count'] : null;
			$operation['contest_name'] = $operation['contest_id'] ? PromoManager::getContestName($operation['contest_id']) : null;

			if ($operation["order_stage_id"] && $orderStages[$operation["order_stage_id"]]) {
				$orderStage = $orderStages[$operation["order_stage_id"]];
				$operation["orderStageName"] = $orderStage[OrderStage::FIELD_TITLE];
				$operation["orderStageNumber"] = $orderStage[OrderStage::FIELD_NUMBER];
			}

			if (in_array($operation['type'], $positiveOperTypeKeys)) {
				$operation['operationAmount'] = $operation['price'];
			} else {
				$operation['operationAmount'] = -$operation['price'];
			}
		}

		unset($operation);

		// Выводим список операций, общее количество, общую сумму и список кворков для фильтра с
		// учетом названий модерируемых кворков, а так же минимальную и максимальную дату операций для фильтра
		return [
			"operations" => $operations,
			"total" => $totalCount,
			"totalSum" => $amountOperSum,
			"ordersList" => $ordersList,
			"allUserOperations" => $allUserOperations,
			"dateLimit" => $dateLimits
		];
	}

	private static function getUserOperationCount($userId, $filterQuery = "", $sqlParams = array(), $leftJoin) {
		$sql = "SELECT 
					count(*)
				FROM 
					operation o
					" . $leftJoin . "
				WHERE 
					o.user_id = :user_id
				AND NOT (o.type = 'refill' AND o.status = 'new') " . $filterQuery;
		return App::pdo()->fetchScalar($sql, $sqlParams);
	}

	public static function getTypePattern($type) {
		$withdraw = WithdrawFactory::build($type);
		if ($withdraw->error) {
			return $withdraw->error;
		}

		return $withdraw->getPattern();
	}

	/**
	 * Выполнить возврат на карту
	 * @param int $operationId Идентификатор операции
	 * @return array
	 */
	public static function runWithdrawBack($operationId) {
		if ($operationId > 0) {
			$operation = Operation::find($operationId);
			if ($operation && $operation->type == self::FIELD_TYPE_MONEYBACK && $operation->status == Operation::STATUS_NEW) {
				if ($operation->wd_operation_from_id) {
					$operationUnitPay = \Model\Unitpay::where(\Model\Unitpay::FIELD_OPERATION_ID, $operation->wd_operation_from_id)
						->first();
					if ($operationUnitPay) {
						$unitpay = new UnitPay();
						$params = [
							"paymentId" => $operationUnitPay->unitpay_id,
							"sum" => $operation->amount,
							"secretKey" => App::config("unitpay.secret_key")
						];
						$response = $unitpay->api("refundPayment", $params);
						if (property_exists($response, "result")) {
							self::doneMoneybackOperation($operation);
							return ["success" => true];
						} else {
							return ["success" => false, "error" => $response->error->message];
						}
					}

					$paymoreRefill = PaymoreRefill::where(PaymoreRefill::FIELD_OPERATION_ID, $operation->wd_operation_from_id)
						->first();
					if ($paymoreRefill) {
						try {
							$moneyback = new \Paymore\PaymoreMoneyback();
							$paymoreMoneyback = $moneyback->make($paymoreRefill, $operation);
							if ($paymoreMoneyback->status === PaymoreRefill::STATUS_REFUND) {
								self::doneMoneybackOperation($operation);
								return ["success" => true];
							} elseif ($paymoreMoneyback->status === PaymoreRefill::STATUS_REFUND_PROCESS) {
								// Если вывод в процессе поставим операцию в статус "в процессе"
								$operation->status = Operation::STATUS_INPROGRESS;
								$operation->save();
								return ["success" => true];
							} else {
								throw new RuntimeException("Неожиданный статус возврата paymore");
							}
						} catch (\Core\Exception\ExternalApiException $exception) {
							return ["success" => false, "error" => $exception->getApiMessage()];
						} catch (\Exception $exception) {
							return ["success" => false, "error" => $exception->getMessage()];
						}
					}

					if (!$paymoreRefill && !$operationUnitPay) {
						return ["success" => false, "error" => "Не найдены связанные сущности paymore/unitpay"];
					}
				} else {
					self::doneMoneybackOperation($operation);
					return ["success" => true];
				}
			}
			return ["success" => false, "error" => "Операция вывода не найдена"];
		}
		return ["success" => false, "error" => "Не передан ID операции"];
	}

	/**
	 * Успешное завешение операции возврата средств пользователю
	 *
	 * @param \Model\Operation $operation Модель операции
	 */
	public static function doneMoneybackOperation(Operation $operation) {
		$operation->status = Operation::STATUS_DONE;
		$operation->date_done = Helper::now();
		$operation->save();

		InboxManager::sendInfoMessageWithdrawCardBack($operation->user_id);
	}

	/**
	 * Отменить возврат на карту
	 * @param int $operationId - идентификатор операции
	 * @param string $reason Причина отмены
	 * @return bool
	 */
	public static function declineWithdrawBack($operationId, $reason) {
		if ($operationId > 0) {
			$operation = Operation::find($operationId);
			if ($operation && $operation->type == self::FIELD_TYPE_MONEYBACK && $operation->status == Operation::STATUS_NEW) {
				try {
					\Core\DB\DB::transaction(function () use ($operation, $reason) {
						$operation->status = Operation::STATUS_CANCEL;
						$operation->date_done = Helper::now();
						$operation->save();

						// Сохранение причины отмены операции
						\Model\OperationMoneybackCancelReason::saveReason($operation->id, (string)$reason);

						if ($operation->wd_operation_from_id) {
							Operation::whereKey($operation->wd_operation_from_id)
								->update([
									self::FIELD_IS_SEND_ON_WITHDRAW => self::FIELD_IS_SEND_ON_WITHDRAW_NOT_SEND
								]);

							User::whereKey($operation->user_id)
								->increment(UserManager::FIELD_FUNDS, $operation->base_amount);

							User::whereKey($operation->user_id)
								->increment(UserManager::FIELD_CARD_FUNDS, $operation->card_amount);

						} else {
							if ($operation->{self::FIELD_BASE_AMOUNT} > 0) {
								$fieldUser = UserManager::FIELD_FUNDS;
							} elseif ($operation->{self::FIELD_BONUS_AMOUNT} > 0) {
								$fieldUser = UserManager::FIELD_BFUNDS;
							} elseif ($operation->{self::FIELD_CARD_AMOUNT} > 0) {
								$fieldUser = UserManager::FIELD_CARD_FUNDS;
							} elseif ($operation->{self::FIELD_BILL_AMOUNT} > 0) {
								$fieldUser = UserManager::FIELD_BILL_FUNDS;
							}
							if (isset($fieldUser)) {
								User::whereKey($operation->user_id)
									->increment($fieldUser, $operation->amount);
							}
						}
					}, 5);
				} catch (\Throwable $exception) {
					\Log::write(__CLASS__ . "::" . __FUNCTION__ . ": " . $exception->getMessage(), "transaction");
					return false;
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Список счетов в порядке списания с них средств
	 * @return array
	 */
	public static function getAccountOrder() {
		return [UserManager::FIELD_BFUNDS, UserManager::FIELD_BILL_FUNDS, UserManager::FIELD_CARD_FUNDS, UserManager::FIELD_FUNDS];
	}

	/**
	 * Получить массив с какого типа счета сколько списать
	 * @param float $sum Списываемая сумма
	 * @param array $payerInfo Информация о пользователе
	 * @return array|false Массив (field, amount) с полями из getAccountOrder и сколько списать<br>
	 * 	false - недостаточно средств на балансе
	 */
	public static function getDecFields($sum, array $payerInfo) {
		$needArray = [];
		$fieldArray = self::getAccountOrder();
		foreach ($fieldArray as $field) {
			if ($payerInfo[$field] > 0 && $sum > 0) {
				if ($sum >= $payerInfo[$field]) {
					$needArray[] = [
						'field' => $field,
						'amount' => $payerInfo[$field],
					];
					$sum -= $payerInfo[$field];
				} else {
					$needArray[] = [
						'field' => $field,
						'amount' => $sum
					];
					$sum = 0;
				}
			}
		}

		// php некорректно считает копейки, вместо 0 показывая 1.1368683772162E-13
		if ($sum > 0.0001) {
			return false;
		}

		return $needArray;
	}

	/**
	 * Списание средств со счетов юзера
	 *
	 * @param float $sum Сумма списания
	 * @param int $orderId Идентификатор заказа
	 * @param int $isExtra Является ли покупакой дополнительных опций
	 * @param int $currencyId Идентификатор валюты
	 * @param float $currencyRate Курс валюты
	 * @param int $orderCurrency Идентификатор валюты заказа
	 * @param int|null $orderStageId Идентификатор этапа заказа
	 *
	 * @return bool|int Идентификатор операции или false в случае отказа
	 */
	public static function orderOutOperation($sum, $orderId, $isExtra, $currencyId, $currencyRate, $orderCurrency, $orderStageId = null) {
		$actor = UserManager::getCurrentUser();


		$fullSum = $sum;
		$needArray = self::getDecFields($sum, (array) $actor);
		if (empty($needArray)) {
			return false;
		}

		$userUpdateFields = [];
		foreach ($needArray as $item) {
			$userUpdateFields[$item["field"]] = \Core\DB\DB::raw($item["field"] ." - ".(float)$item["amount"]);
		}
		\Model\User::whereKey($actor->id)->update($userUpdateFields);

		$operationLang = OperationLanguageManager::detectLang("order_out", $currencyId, $orderCurrency);
		$langAmount = OperationLanguageManager::getAmountByType("order_out", $sum, $currencyId, $operationLang, $currencyRate);

		$operation = new \Model\Operation();
		$operation->user_id = $actor->id;
		$operation->type = "order_out";
		$operation->amount = $fullSum;
		$operation->status = OperationManager::FIELD_STATUS_DONE;
		$operation->order_id = $orderId;
		$operation->date_done = Helper::now();
		$operation->currency_id = $currencyId;
		$operation->currency_rate = $currencyRate;
		$operation->lang = $operationLang;
		$operation->lang_amount = $langAmount;

		return $operation->save()
			? $operation->id
			: false;
	}

	/**
	 * Увеличить количество средств, которые нужно перевести с карточного счета на основной
	 * @param int $userId
	 * @param float $amount
	 * @return int
	 */
	public static function refillCardFundsTransferAmount($userId, $amount) {
		return \Model\User::whereKey($userId)->increment(UserManager::FIELD_CARD_FUNDS_TRANSFER_AMOUNT, $amount);
	}

	/**
	 * Установить попролнение с карты как переведенное на основной счет
	 * @param int $operationId
	 * @return bool
	 */
	public static function setRefilTransferred($operationId) {
		$operationId = (int) $operationId;
		if ($operationId > 0) {
			$condition = self::FIELD_ID . " = :" . self::FIELD_ID;
			return (bool) App::pdo()->update(self::TABLE_NAME,
					[self::FIELD_TRANSFERRED => self::TRANSFERED],
					$condition,
					[self::FIELD_ID => $operationId]);
		}
		return false;
	}

	/**
	 * Перевод средств с карточного счета в основной
	 * @param $userId
	 * @param mixed $amount Максимальная сумма перевода
	 * @return bool|int
	 */
	public static function cardFundsTransfer($userId, $amount) {
		$fieldArray = [
			UserManager::FIELD_USERID,
			UserManager::FIELD_CARD_FUNDS,
			UserManager::FIELD_CARD_FUNDS_TRANSFER_AMOUNT
		];
		$user = \Model\User::select($fieldArray)->find($userId);

		$cardFundsTransferAmount = $user->{UserManager::FIELD_CARD_FUNDS_TRANSFER_AMOUNT};
		$cardFunds = $user->{UserManager::FIELD_CARD_FUNDS};

		if ($cardFundsTransferAmount > 0 && $cardFunds > 0) {
			$transferAmount = min($cardFundsTransferAmount, $cardFunds, $amount);

			return \Model\User::whereKey($userId)
				->update([
					UserManager::FIELD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_FUNDS . " + " . (float)$transferAmount),
					UserManager::FIELD_CARD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_CARD_FUNDS . " - " . (float)$transferAmount),
					UserManager::FIELD_CARD_FUNDS_TRANSFER_AMOUNT => \Core\DB\DB::raw(UserManager::FIELD_CARD_FUNDS_TRANSFER_AMOUNT . " - " . (float)$transferAmount),
				]);
		}
		
		return false;
	}

	/**
	 * Получить название способа платежа
	 * @param string|array $operationInfo Строка - тип платежа, массив - строка из operations
	 * @return string Название способа пополнения
	 */
	public static function getPaymentTypeLabel($operationInfo) {
		if (is_array($operationInfo) && !empty($operationInfo['payment'])) {
			$return = $checkValue = $operationInfo['payment'];
		} elseif (is_array($operationInfo) && empty($operationInfo['payment'])) {
			$return = $checkValue = '';
		} else {
			$return = $checkValue = $operationInfo;
		}
		switch ($checkValue) {
			case 'card':
				$return = "Карта";
				break;

			case 'webmoney':
				$return = 'Webmoney';
				break;

			case 'beeline':
				$return = 'Сотовый - Билайн';
				break;

			case 'qiwi':
				$return = 'QIWI';
				break;

			case 'yandex':
				$return = 'Яндекс.Деньги';
				break;

			case 'mf':
				$return = 'Сотовый - Мегафон';
				break;

			case 'mts':
				$return = 'Сотовый - МТС';
				break;

			case 'liqpay':
				$return = 'LiqPay';
				break;

			case 'alfaClick':
				$return = 'Альфа-Банк';
				break;

			case 'euroset':
				$return = 'Наличные - Евросеть';
				break;

			case 'svyaznoy':
				$return = 'Наличные - Связной';
				break;

			case 'tele2':
				$return = 'Сотовый - Теле2';
				break;

			case 'bill':
				$return = 'Безналичное пополнение';
				break;

            case 'admin':
                $return = 'Ручное пополнение';
                break;
		}
		if (is_array($operationInfo) && $operationInfo['unitpayId']) {
			$return = "Unitpay - ". $return;
		} elseif (is_array($operationInfo) && $operationInfo['paymoreRefillId']) {
			$return = "Касса - ". $return;
		}

		return $return;
	}

	/**
	 * Получить все коды типов по операции (возможно стоит ограничить годом)
	 * @uses \OperationManager::$paymentWithdrawTypes
	 * @uses \OperationManager::$paymentUnitpayRefillTypes
	 * @uses \OperationManager::$paymentPaymoreRefillTypes
	 *
	 * @param string $type unitpayRefill|paymoreRefill|withdraw
	 *
	 * @return array|false
	 */
	public static function getPaymentTypes($type) {
		$redis = \RedisManager::getInstance();

		$field = "payment" . mb_ucfirst($type) . "Types";
		if (!empty(self::${$field})) {
			return self::${$field};
		}
		$alias = \Enum\Redis\RedisAliases::PAYMENT_TYPES . $type;

		$result = $redis->get($alias);

		if(empty($result)) {
			$result = self::getPaymentTypeNonCached($type);
			if(!empty($result))
				$redis->set($alias, $result);
		}

		return $result;
	}

	/**
	 * Запросы получения кодов типов по операции из базы
	 *
	 * @param string|null $type unitpayRefill|paymoreRefill|withdraw
	 *
	 * @return array
	 */
	public static function getPaymentTypeNonCached($type) {
		//Если проверяем уникальные знаения по операции пополнения, берем значения payment из unitpay и paymore
		if ($type === "unitpayRefill") {
			return \Model\Unitpay::getPaymentTypes();
		} elseif ($type === "paymoreRefill") {
			return PaymoreRefill::getPaymentMethods();
		}

		return Operation::where(Operation::FIELD_TYPE, $type)
			->whereNotNull(Operation::FIELD_PAYMENT)
			->distinct()
			->pluck(Operation::FIELD_PAYMENT)
			->toArray();
	}

	/**
	 * Сохраняет все возможные поля payment по операциям в редис.
	 */
	public static function saveOperationTypesToCache() {
		$redis = \RedisManager::getInstance();

		$opTypes = ["unitpayRefill", "paymoreRefill", self::FIELD_TYPE_WITHDRAW];
		if(count($opTypes) > 0){
			foreach ($opTypes as $ot) {
				$result = self::getPaymentTypeNonCached($ot);
				$alias = \Enum\Redis\RedisAliases::PAYMENT_TYPES . $ot;
				if(!empty($result)){
					$redis->set($alias, $result);
				}
			}
		}
	}

	/**
	 * Отмена операции вывода пользователем
	 * @param int $withdrawId - Идентификатор вывода
	 * @return \stdClass
	 */
	public static function declineUserWithdraw($withdrawId) {
		$actor = UserManager::getCurrentUser();
		$withdrawId = (int) $withdrawId;
		$result = new stdClass();
		$result->success = false;
		$result->message = "";
		if ($actor && $withdrawId > 0) {
			$operation = Operation::find($withdrawId);
			if (!$operation) {
				$result->message = "Вывод не найден";
			} else {
				if ($operation->type == self::FIELD_TYPE_WITHDRAW && $operation->user_id == $actor->id) {
					if ($operation->sub_type == Operation::SUB_TYPE_SOLAR) {
						$withdrawOperation = SolarOperation::find($withdrawId);
					} else {
						$withdrawOperation = $operation;
					}
				}
			}

			if (isset($withdrawOperation) && ($withdrawOperation instanceof SolarOperation || $withdrawOperation instanceof Operation)) {
				if ($withdrawOperation->status == SolarOperation::STATUS_NEW) {
					try {
						\Core\DB\DB::transaction(function () use ($withdrawOperation, $operation, $result) {
							if ($operation->bonus_amount > 0) {
								$field = UserManager::FIELD_BFUNDS;
							} elseif ($operation->card_amount > 0) {
								$field = UserManager::FIELD_CARD_FUNDS;
							} elseif ($operation->bill_amount > 0) {
								$field = UserManager::FIELD_BILL_FUNDS;
							} else {
								$field = UserManager::FIELD_FUNDS;
							}

							User::whereKey($operation->user_id)
								->increment($field,  $operation->amount);

							$operation->status = self::FIELD_STATUS_CANCEL;
							$operation->date_done = Helper::now();
							$operation->save();

							if ($withdrawOperation instanceof SolarOperation) {
								$withdrawOperation->status = SolarOperation::STATUS_CANCEL;
								$withdrawOperation->reason = "Отменено пользователем";
								$withdrawOperation->save();
							}

							if ($operation->wd_operation_from_id) {
								Operation::whereKey($operation->wd_operation_from_id)
									->update([
										self::FIELD_IS_SEND_ON_WITHDRAW => self::FIELD_IS_SEND_ON_WITHDRAW_NOT_SEND
									]);
							}

							$result->success = true;
							$result->message = "Вывод успешно отменён";
						}, 5);
					} catch (\Throwable $exception) {
						Log::write(__CLASS__ . "::" . __FUNCTION__ . ": " . $exception->getMessage(), "transaction");
						$result->message = "Ошибка при выполнении операции";
					}
				} elseif ($withdrawOperation->status == SolarOperation::STATUS_INPROGRESS) {

					$result->message = "Вывод отправлен в обработку, и не может быть отменён";
				} elseif ($withdrawOperation->status == SolarOperation::STATUS_DONE) {

					$result->message = "Вывод уже завершен, и не может быть отменён";
				} elseif ($withdrawOperation->status == SolarOperation::STATUS_CANCEL) {

					$result->message = "Вывод уже отменён";
				}
			} else {
				$result->message = "Вывод не найден";
			}
		} else {
			$result->message = "Неверные данные";
		}
		return $result;
	}

	public static function clearOldRefillCards() {
		global $conn;

		$query = "DELETE FROM user_refill_card WHERE date_create < NOW() - INTERVAL " . self::REFILL_CARD_DAYS_LIMIT . " DAY";
		return $conn->execute($query);
	}

	/**
	 * Включена ли сейчас проверка по номерам карт
	 *
	 * @param int $userId Идентификатор пользователя для которого делается пополнение
	 *
	 * @return bool
	 */
	public static function isCardNumberRefillCheckEnabled(int $userId): bool {
		$configValue = \App::config('module.card_number_refill_limit.enable');
		return $configValue === true || in_array($userId, explode(',', $configValue));
	}

	/**
	 * Получение текста ошибки при проверке пополнения по номерам карт
	 *
	 * @return string
	 */
	public static function getCardNumberRefillErrorText(): string {
		$countLimit = OperationManager::REFILL_CARD_COUNT_LIMIT;
		$dayLimit = OperationManager::REFILL_CARD_DAYS_LIMIT;
		return Translations::t("Пополнение баланса аккаунта возможно только с %s разных карт в течение %s дней.", $countLimit, $dayLimit);
	}

	/**
	 * Отменить все операции на вывод пользователя
	 * @param int $userId Идентификатор пользователя
	 * @param string $reason Причина отмены
	 * @return bool
	 */
	public static function declineAllWithdraw($userId, $reason) {
		$operations = \Core\DB\DB::table(SolarOperation::TABLE_NAME . " as os")
			->join(Operation::TABLE_NAME . " as o", "o." . Operation::FIELD_ID, "os." . SolarOperation::FIELD_OPERATION_ID)
			->select([
				"o." .  Operation::FIELD_ID,
				"o." . Operation::FIELD_TYPE,
				"o." . Operation::FIELD_PAYMENT,
				"o." . Operation::FIELD_STATUS,
			])
			->where("o." . Operation::FIELD_STATUS, Operation::STATUS_NEW)
			->where("os." . SolarOperation::FIELD_STATUS, SolarOperation::STATUS_NEW)
			->where("o." . Operation::FIELD_USER_ID, $userId)
			->get()
			->all();

		if (!$operations) {
			return false;
		}

		foreach ($operations as $operation) {
			self::declineWithdrawOnId($operation->id, $reason);
		}

		return true;
	}

	/**
	 * Операция через Solar Staff?
	 * @param object $operation(id, payment) Операция
	 * @return bool
	 */
	public static function isSS($operation) {
		if (in_array($operation->payment, \Withdraw\Withdraw::$_solarStaffTypes)) {
			return true;
		}

		return false;
	}

	/**
	 * Создание заявки на возврат пополнения
	 *
	 * @param int $operationId – Идентификатор операции пополнения
	 * @param int $userId – Идентификатор пользователя
	 * @param int $amount – Сумма частичного возрата, если 0 или больше суммы операции – возрат полный
	 *
	 * @throws \Core\Exception\SimpleJsonException
	 */
	public static function makeNewMoneyback(int $operationId, int $userId, int $amount = 0) {
		if (!$operationId || !$userId) {
			throw new SimpleJsonException("Не заданы параметры");
		}

		$threshold = \Carbon\Carbon::now()->subMonths(6);

		$operation = Operation::with(["unitpayOperation","paymoreRefill"])
			->where(Operation::FIELD_USER_ID, $userId)
			->where(Operation::FIELD_STATUS, Operation::STATUS_DONE)
			->where(Operation::FIELD_TYPE, Operation::TYPE_REFILL)
			->where(Operation::FIELD_IS_SEND_ON_WITHDRAW, self::FIELD_IS_SEND_ON_WITHDRAW_NOT_SEND)
			->where(Operation::FIELD_DATE_DONE, ">", $threshold)
			->find($operationId);

		if (empty($operation)) {
			throw new SimpleJsonException("Операция с таким идентификатором не найдена");
		}

		if (!$operation->unitpayOperation && !$operation->paymoreRefill) {
			throw new SimpleJsonException("Не найдены связанные операции пополнения Unitpay или Paymore");
		}

		if ($operation->unitpayOperation && $operation->unitpayOperation->payment !== self::FIELD_PAYMENT_CARD) {
			throw new SimpleJsonException("По Unitpay разрешены возвраты только на карту");
		}

		$user = User::find($userId);

		if (empty($user)) {
			throw new SimpleJsonException("Пользователь не найден");
		}
		$funds = (int) $user->card_funds + (int) $user->funds;
		if ($funds === 0) {
			throw new SimpleJsonException("На карточном и основном счетах пользователя отсутствуют средства");
		}

		// Если сумма частичного возрата превышает или равна сумме операции,
		// то делаем полный возрат
		if ($amount <= 0 || $amount >= $operation->amount) {
			$amount = $operation->amount;
		}
		/*
		 * Если значение суммы частичного/полного возрата больше чем те деньги,
		 * которые есть на карточном и основном счетах пользователя, то выдать ошибку
		 */
		if ($amount > $funds) {
			throw new SimpleJsonException("Сумма вывода больше чем средства на карточном и основном счетах пользователя");
		}

		if ($operation->paymoreRefill) {
			if ($operation->amount - $amount > 0.01) {
				// Если возврат частичный
				$userCurrencyName = Translations::getCurrencyByLang($user->lang);
				if (!$operation->paymoreRefill->available_partial_refund) {
					throw new SimpleJsonException("В данной операции запрещен частичный возврат Кассой");
				} elseif ($operation->paymoreRefill->available_for_refund_currency !== $userCurrencyName) {
					throw new SimpleJsonException("Валюта баланса пользователя не совпадает с валютой возврата Кассы");
				} elseif ($amount - $operation->paymoreRefill->available_for_refund_amount >= 0.01) {
					throw new SimpleJsonException("Сумма возврата превышает доступный лимит пришедший от Кассы");
				}
			} elseif(!$operation->paymoreRefill->available_full_refund) {
				throw new SimpleJsonException("Полный возврат по данной операции запрещен Кассой");
			}
		}

		$operationDate = strtotime($operation->date_done);
		$monthAgo = time() - Helper::ONE_MONTH;
		// Если еще не прошел месяц от даты выполнения операции, снимаем средства приоритетно с карточного счета,
		// если прошел – приоритетно с основного счета
		if ($operation->card_amount > 0.01 && $operationDate > $monthAgo) {
			$diff = $amount - (int) $user->card_funds;
			$cardAmount = ($diff > 0) ? (int) $user->card_funds : $amount;
			$baseAmount = ($diff > 0) ? $diff : 0;
		} else {
			$diff = $amount - (int) $user->funds;
			$baseAmount = ($diff > 0) ? (int) $user->funds : $amount;
			$cardAmount = ($diff > 0) ? $diff : 0;
		}

		try {
			\Core\DB\DB::transaction(function () use ($operation, $amount, $cardAmount, $baseAmount) {
				$fieldsOperation = [
					self::FIELD_USER_ID => $operation->user_id,
					self::FIELD_AMOUNT => $amount,
					self::FIELD_BASE_AMOUNT => $baseAmount,
					self::FIELD_CARD_AMOUNT => $cardAmount,
					self::FIELD_STATUS => self::FIELD_STATUS_NEW,
					self::FIELD_TYPE => self::FIELD_TYPE_MONEYBACK,
					self::FIELD_LANG => $operation->lang,
					self::FIELD_LANG_AMOUNT => $amount,
					self::FIELD_WD_OPERATION_FROM_ID => $operation->id,
				];
				if ($operation->unitpayOperation) {
					$fieldsOperation[self::FIELD_PAYMENT] = $operation->unitpayOperation->payment;
				} elseif ($operation->paymoreRefill) {
					$fieldsOperation[self::FIELD_PAYMENT] = $operation->paymoreRefill->payment_method;
				} else {
					throw new SimpleJsonException("Неожиданный тип платежной системы");
				}
				$newId = self::create($fieldsOperation);

				User::whereKey($operation->user_id)
					->decrement(UserManager::FIELD_CARD_FUNDS, $cardAmount);
				User::whereKey($operation->user_id)
					->decrement(UserManager::FIELD_FUNDS, $baseAmount);

				Operation::whereKey($operation->id)
					->update([
						self::FIELD_IS_SEND_ON_WITHDRAW => self::FIELD_IS_SEND_ON_WITHDRAW_SEND
					]);

				RefillWithdrawManager::addRefillWithdraw($operation->id, $newId);
			}, 5);
		} catch (\Throwable $exception) {
			\Log::write(__CLASS__ . "::" . __FUNCTION__ . ": " . $exception->getMessage(), "transaction");
			throw new SimpleJsonException("Ошибка при попытке отправить пополнения на вывод");
		}
	}

	/**
	 * Получить код страны по номеру карты
	 * @param string|array $cardNumber - номер карты
	 * @param bool $isArray Результат отдать как массив
	 * @return string|array - код страны
	 */
	public static function getCountryCodeByCardNumber($cardNumber, $isArray = false) {
		if (!is_array($cardNumber)) {
			$binList = [$cardNumber];
		} else {
			$binList = $cardNumber;
		}
		foreach ($binList as $key => $item) {
			$binList[$key] = self::getBinFromCardNumber($item);
		}
		$params = [];
		$query = "SELECT bin, iso_code FROM card_bin_country WHERE bin IN (" . App::pdo()->arrayToStrParams($binList, $params) . ")";
		$isoCode = App::pdo()->fetchAllNameByColumn($query, 0, $params);

		$result = [];
		foreach ($binList as $item) {
			$bin = self::getBinFromCardNumber($item);
			if (!isset($isoCode[$bin])) {
				$unitpay = new UnitPay(App::config("unitpay.out.api.secret_key"));
				$code = $unitpay->getCountryCode($item);
				App::pdo()->insert("card_bin_country", [
					"iso_code" => $code,
					"bin" => $bin
				]);
				$isoCode[$bin]["iso_code"] = $code;
			}
			$result[$item] = $isoCode[$bin]["iso_code"];
		}
		if (!$isArray) {
			return current($result);
		} else {
			return $result;
		}
	}

	public static function getBinFromCardNumber($cardNumber) {
		return (int) mb_substr(str_replace(" ", "", $cardNumber), 0, 6);
	}

	/**
	 * Возвращает количество активных операций вывода пользователя
	 * @param $userId
	 * @param bool $payment
	 * @return bool|string
	 */
	public static function getActiveWithdrawCount($userId, $payment = false) {
		if (!$userId) {
			return false;
		}
		$query = "SELECT count(id) FROM operation WHERE user_id = :user_id AND type = 'withdraw' AND status IN ('new', 'inprogress')";
		$params = [
			"user_id" => $userId
		];
		if ($payment) {
			$query .= " AND payment = :payment";
			$params["payment"] = $payment;
		}
		$res = App::pdo()->fetchScalar($query, $params);
		return $res;
	}


	public static function getUnitpayTypes() {
		return [
			"card", //Банковская карта
			"bank", //Безнал для юрлиц и ИП
			"alfaClick", //Альфа-Клик
			"webmoney", //WebMoney
			"yandex", //Яндекс.Деньги
			"qiwi", //QIWI Кошелек 
			"mc:mts", //МТС 
			"mc:mf", //Мегафон 
			"mc:beeline", //Билайн 
			"mc:tele2", //Теле 2
		];
	}

	/**
	 * Получить ссылку для оплаты
	 * @param $amount float Сумма
	 * @param $type
	 * @param null $kworkPaymentId
	 * @return bool|string
	 * @throws Exception
	 */
	public static function makeRedirectLink($amount, $type, $kworkPaymentId = null) {
		if (\Paymore\PaymoreManager::hasPaymorePrefix($type)) {
			if ($kworkPaymentId == "") {
				$kworkPaymentId = null;
			}
			return self::makeRefillRedirectLinkPaymore($amount, \Paymore\PaymoreManager::removePaymorePrefix($type), $kworkPaymentId);
		}

		// оплата через PayPal
		if ($type == PaypalManager::TYPE_PAYPAL_REFILL) {
			return self::makeRedirectLinkPayPal($amount, intval($kworkPaymentId));
		}

		if(PaymentStreamManager::getUserRefillType() == PaymentStreamManager::REFILL_SYSTEM_EPAYMENTS){
			/** TODO убрать после тестирования тикета 6181 */
			if (App::config("app.mode") == "local") {
				$url = self::makeRedirectLinkEpayments($amount, intval($kworkPaymentId));
				if ($url === false) {
					$url = $_SERVER["HTTP_REFERER"];
					$session = \Session\SessionContainer::getSession();
					$session->set("flashError", Translations::t("Ошибка перехода на страницу платежной системы Epayments."));
				}

				return $url;
			}
		}
		return self::makeRedirectLinkUnitpay($amount, $type, $kworkPaymentId);
	}

	/**
	 * Получить ссылку для оплаты через Paypal
	 *
	 * @param $amount float Сумма
	 * @param int|null $operationId id записи в таблице operation, если она была предварительно создана
	 * @return string|bool
	 * @throws Exception
	 */
	private static function makeRedirectLinkPayPal($amount, $operationId = null) {
		$actor = UserManager::getCurrentUser();

		// если функционал оплаты через Paypal не доступен для данного юзера - не будем продолжать
		if (!PaypalManager::isAvailableForUser($actor->USERID)) {
			return false;
		}

		$paypal = new PaypalManager();

		// если язык сайта не входит в разрешенные для Paypal - не будем продолжать
		if (!$paypal->isLangEnabled(Translations::getLang())) {
			return false;
		}

		$currency = Translations::getCurrencyByLang($actor->lang);
		$currencyCode = CurrencyManager::getCurrencyIdByCode($currency);
		$amountWithCommission = $paypal->getAmountWithCommission($amount, $currencyCode);

		$orderId = Model\Paypal::add($amount, $amountWithCommission, $operationId);

		$params = [
			"amount" => $amountWithCommission,
			"currency_code" => $currency,
			"lc" => Translations::getLang() == Translations::DEFAULT_LANG ? "ru_RU" : "en_US",
			"charset" => "utf-8",
			"custom" => (int) $orderId, // max length: 256 chars
			"item_name" => Translations::t("Пополнение баланса Kwork.ru"),
			"cmd" => "_xclick",
			"no_note" => 1,
			"no_shipping" => 1,
			"business" => App::config("paypal.account"),
			"return" => App::config("baseurl") . \Core\Routing\UrlGeneratorSingleton::getInstance()->generate("paypal_return"),
			"rm" => 1,
		];

		return $paypal->getOrderUri()."?".http_build_query($params);
	}

	/**
	 * Сделать ссылку для оплаты на paymore (Касса)
	 *
	 * @param float $amount Сумма
	 * @param string $paymentMethod Способ оплаты
	 * @param int|null $operationId Идентификатор оплаты
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function makeRefillRedirectLinkPaymore(float $amount, string $paymentMethod, ?int $operationId = null): string {
		if (\User\UserRefillSystemManager::getUserRefillSystem() !== \User\UserRefillSystemManager::PAYMORE) {
			throw new RuntimeException("Использование пополнений paymore запрещено для пользователя " . UserManager::getCurrentUserId());
		}
		$factory = new Paymore\PaymoreRefillFactory();
		$paymoreRefill = $factory->makeByCurrentUser($amount, $paymentMethod, $operationId);
		return $paymoreRefill->payment_url;
	}

	/**
	 * Получить ссылку для оплаты через Epayments
	 * @param $amount float Сумма
	 * @param int|null $operationId id записи в таблице operation, если она была предварительно создана
	 * @return bool
	 * @throws Exception
	 */
	private static function makeRedirectLinkEpayments(float $amount, int $operationId = null) {
		$lang = UserManager::getCurrentUser()->lang;
		$amountWithCommission = \Epayments\Epayments::getAmountBeforeCommission($amount);
		$orderNumber = \Model\Epayments::add($amount, $amountWithCommission, $operationId);
		$e = new \Epayments\Epayments();

		$data = new \ApiPaymentsPageData();
		$data->language = $lang;
		$data->orderName = $lang == Translations::DEFAULT_LANG ? "Пополнение баланса" : "Balance refill";
		$data->orderSumAmount = number_format($amountWithCommission, 2, ".", "");
		$data->orderSumCurrency = Translations::getCurrencyByLang($lang);
		$data->orderNumber = $orderNumber;
		$data->sha512 = $e->getSignature($data->orderNumber, $data->orderSumAmount, $data->orderSumCurrency);

		return $e->apiPaymentPage($data);
	}


	private static function makeRedirectLinkUnitpay($amount, $type, $kworkPaymentId = null) {
		global $actor;

		$operator = "";
		$currency = "RUB";
		$description = "Пополнение баланса";
		$publicKey = App::config("unitpay.public_key");
		if ($actor->lang == \Translations::EN_LANG) {
			$currency = "USD";
			$description = "Balance refill";
			$publicKey = App::config("unitpay.en.public_key");
		}
		if (stripos($type, ":") > 0) {
			list($type, $operator) = explode(":", $type);
		}

		$account = $actor->id;
		if ($kworkPaymentId > 0) {
			$account .= '_' . $kworkPaymentId;
		}

		$params = [
			"sum" => $amount,
			"account" => $account,
			"desc" => $description,
			"hideMenu" => "true",
			"currency" => $currency,
			"locale" => $actor->lang,
		];

		if ($operator) {
			$params["operator"] = $operator;
		}

		return "https://unitpay.ru/pay/" . $publicKey . "/$type?" . http_build_query($params);
	}

	/**
	 * Сумма списываемая соларом с аккаунта кворка
	 * @param int $solarStaffAmount - сумма которую мы отправляем солару
	 * @param string $paymentType - тип плетежной системы
	 * @return float
	 */
	public static function getWriteOffFundsFromSolar($solarStaffAmount, $paymentType): float {
		if (!array_key_exists($paymentType, self::$_solarPaymentsArray)) {
			return 0;
		}
		return $solarStaffAmount + $solarStaffAmount * App::config("purse.$paymentType.comission") / 100;
	}

    /**
     * Получить статистику по ручному пополнению баланса за последний месяц
     *
     * @return array|bool Массив ['count' => X, 'amount' => Y] в случае успеха, false в случае ошибки
     */
    public static function getAdminRefillStatistics() {
        $sql = 'SELECT
                    COUNT(1) AS count,
                    SUM(' . self::FIELD_AMOUNT . ') AS amount
                FROM
                    ' . self::TABLE_NAME . '
                WHERE
                    ' . self::FIELD_SUB_TYPE . ' = :payment
                    AND ' . self::FIELD_DATE_CREATE . ' > NOW() - INTERVAL 1 MONTH';

        return \App::pdo()->fetch($sql, ['payment' => self::FIELD_ST_REFILL_ADMIN]);
    }

	/**
	 * Расчёт такой суммы, чтобы после вычета комиссии получилось {$amountNetto} средств
	 * Округление идёт в большую сторону:
	 * 0.300 => 0.30
	 * 0.333 => 0.34
	 * 0.777 => 0.78
	 *
	 * @param int $amountNetto - исходная сумма, для которой нужно учесть комиссию платёжной системы
	 * @param string $paymentType - тип плетежной системы
	 * @param int $precision - количество разрядов после запятой
	 * @return float
	 */
	public static function getAmountByNetto($amountNetto, $paymentType, $precision = 2) {
		if (!array_key_exists($paymentType, self::$_solarPaymentsArray)) {
			return 0;
		}
		$offset = 0.5;
		if ($precision !== 0) {
			$offset /= pow(10, $precision);
		}
		$amount = $amountNetto / (1 - App::config("purse.$paymentType.comission") / 100);

		// return round($amount + $offset, $precision, PHP_ROUND_HALF_DOWN);

		return intval(($amount + $offset) * 100) / 100;
	}

	/**
	 * Отмена операции или нескольких операций
	 *
	 * @param int|array|\Illuminate\Support\Collection $operationId Идентификатор операции или массив идентификаторов операции
	 * @return int affectedRows
	 */
	public static function cancel($operationId) {
		return Operation::whereKey($operationId)->update([
			Operation::FIELD_STATUS => OperationManager::FIELD_STATUS_CANCEL,
			Operation::FIELD_DATE_DONE => DB::raw("NOW()"),
		]);
	}

	/**
	 * Возвращает тип платежной системы по способу вывода
	 * @param $payment
	 * @return int|string
	 */
	public static function getPurseTypeByPayment($payment) {
		$result = "";
		foreach(self::$paymentsByType as $type => $items) {
			if(in_array($payment, $items)) {
				$result = $type;
				break;
			}
		}

		return $result;
	}

	/**
	 * Функционал перевода средств от покупателя продавцу, в виде бонуса (чаевых) за хорошо выполнный кворк
	 *
	 * @param int $orderId - ID заказа
	 * @param int $payerId - ID покупателя
	 * @param int $workerId - ID продавца/исполнителя
	 * @param int $amount - переводимая сумма
	 * @param float $ctp - комиссия
	 * @return bool
	 * @throws Exception
	 */
	public static function sendTips(int $orderId, int $payerId, int $workerId, int $amount, float $ctp): bool {
		// получаем данные по исполнителю и покупателю
		$workerInfo = UserManager::getUserData($workerId);
		$payerInfo = UserManager::getUserData($payerId);

		$workerCurrencyId = \Translations::getCurrencyIdByLang($workerInfo[UserManager::FIELD_LANG]);
		$payerCurrencyId = \Translations::getCurrencyIdByLang($payerInfo[UserManager::FIELD_LANG]);

		$orderCurrencyData = \Model\Order::select([\OrderManager::F_CURRENCY_ID])
			->where(\OrderManager::F_ORDER_ID, $orderId)
			->get()
			->first()
			->toArray();
		$orderCurrencyId = $orderCurrencyData[\OrderManager::F_CURRENCY_ID];

		$orderCurrencyRate = ($orderCurrencyId == \Model\CurrencyModel::RUB) ? 1.0 : \Currency\CurrencyExchanger::getInstance()->convertToRUB(1.0);

		$convertedPayerAmount = \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
			$amount,
			$orderCurrencyId,
			$payerCurrencyId
		);

		$needArray = self::getDecFields($convertedPayerAmount, (array) $payerInfo);
		if (empty($needArray)) {
			return false;
		}

		$result = new Result();
		// списываем средства со счёта платильщика
		$userQuery = \Model\User::query();
		$userQuery->where(UserManager::FIELD_USERID, $payerId);
		foreach ($needArray as $item) {
			$result->mergeResult(
				$userQuery->update([
					$item['field'] => \Core\DB\DB::raw("{$item['field']} - {$item['amount']}")
				])
			);
		}

		$operationLang = OperationLanguageManager::detectLang(OperationManager::TYPE_ORDER_OUT, $payerCurrencyId, $orderCurrencyId);
		$langAmount = OperationLanguageManager::getAmountByType(OperationManager::TYPE_ORDER_OUT, $convertedPayerAmount, $payerCurrencyId, $operationLang);

		$insertData = [
			self::FIELD_USER_ID => $payerId,
			self::FIELD_TYPE => "order_out",
			self::FIELD_AMOUNT => $convertedPayerAmount,
			self::FIELD_STATUS => "done",
			self::FIELD_ORDER_ID => $orderId,
			self::FIELD_DATE_DONE => Helper::now(),
			self::FIELD_CURRENCY_ID => $payerCurrencyId,
			self::FIELD_CURRENCY_RATE => $orderCurrencyRate,
			self::FIELD_IS_TIPS => 1,
			self::FIELD_LANG => $operationLang,
			self::FIELD_LANG_AMOUNT => $langAmount,
		];

		// добавляем операцию о списании средств с платильщика
		$result->mergeResult(
			\Core\DB\DB::table(self::TABLE_NAME)
			->insert($insertData)
		);

		$convertedWorkerAmount = \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
			$amount,
			$orderCurrencyId,
			$workerCurrencyId
		);

		// Комиссия
		$convertedWorkerCtp = \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
			$ctp,
			$orderCurrencyId,
			$workerCurrencyId
		);

		// сумма бонуса за вычетом комиссии
		$amountCtp = $convertedWorkerAmount - $convertedWorkerCtp;

		// начисляем средства исполнителю
		if ($result->isSuccess()) {
			$result->mergeResult(
				\Model\User::query()
					->where(UserManager::FIELD_USERID, $workerId)
					->update([
						UserManager::FIELD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_FUNDS . " + {$amountCtp}")
					])
			);

			$operationLang = OperationLanguageManager::detectLang(OperationManager::TYPE_ORDER_IN, $workerCurrencyId, $orderCurrencyId);
			$langAmount = OperationLanguageManager::getAmountByType(OperationManager::TYPE_ORDER_IN, $amountCtp, $workerCurrencyId, $operationLang);

			$insertData[self::FIELD_USER_ID] = $workerId;
			$insertData[self::FIELD_AMOUNT] = $amountCtp;
			$insertData[self::FIELD_CURRENCY_ID] = $workerCurrencyId;
			$insertData[self::FIELD_CURRENCY_RATE] = $orderCurrencyRate;
			$insertData[self::FIELD_TYPE] = "order_in";
			$insertData[self::FIELD_LANG] = $operationLang;
			$insertData[self::FIELD_LANG_AMOUNT] = $langAmount;

			// добавляем операцию о зачислении средств исполнителю
			$result->mergeResult(
				\Core\DB\DB::table(self::TABLE_NAME)
					->insert($insertData)
			);
		}

		// если по какой-либо операции не произошел UPDATE, то выбрасываем Exception
		if ($result->isNotSuccess()) {
			throw new Exception("При отправке бонуса продавцу, во время списания/начисления средств возникла ошибка.");
		}

		return true;
	}

	/**
	 * Выполняет повторное начисление бонуса, если ранее заказ был отменён
	 *
	 * @param int $orderId - ID заказа
	 */
	public static function sendTipsAfterCancel(int $orderId) {
		// получим предыдущую операцию по чаевым
		$previousOperation = \Core\DB\DB::table(self::TABLE_NAME)
			->where(self::FIELD_ORDER_ID, $orderId)
			->where(self::FIELD_TYPE, "order_in")
			->where(self::FIELD_STATUS, self::FIELD_STATUS_CANCEL)
			->where(self::FIELD_IS_TIPS, true)
			->first();

		if (!empty($previousOperation)) {
			// операция по списанию чаевых с продавца
			$previousPayerOperation = \Core\DB\DB::table(self::TABLE_NAME)
				->where(self::FIELD_ORDER_ID, $orderId)
				->where(self::FIELD_TYPE, "order_out")
				->where(self::FIELD_STATUS, self::FIELD_STATUS_DONE)
				->where(self::FIELD_IS_TIPS, true)
				->first();

			// увеличиваем значение поля потраченных на заказы денег
			UserData::query()
				->where(UserData::FIELD_USER_ID, $previousPayerOperation->{self::FIELD_USER_ID})
				->increment(UserData::FIELD_USED, $previousPayerOperation->{self::FIELD_AMOUNT});

			// начисляем средства исполнителю
			\Model\User::query()
				->where(UserManager::FIELD_USERID, $previousOperation->{self::FIELD_USER_ID})
				->update([
					UserManager::FIELD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_FUNDS . " + " . $previousOperation->{self::FIELD_AMOUNT})
				]);

			/** @todo можно переделать на replicate(), когда будет описана модель операций */
			// Добавим операцию о зачислении, просто копируем предыдущую операцию, т.к. по сути данные практичкски не меняются
			$insertData = [
				self::FIELD_USER_ID => $previousOperation->{self::FIELD_USER_ID},
				self::FIELD_TYPE => $previousOperation->{self::FIELD_TYPE},
				self::FIELD_AMOUNT => $previousOperation->{self::FIELD_AMOUNT},
				self::FIELD_BASE_AMOUNT => $previousOperation->{self::FIELD_BASE_AMOUNT},
				self::FIELD_STATUS => self::FIELD_STATUS_DONE,
				self::FIELD_ORDER_ID => $previousOperation->{self::FIELD_ORDER_ID},
				self::FIELD_DATE_DONE => Helper::now(),
				self::FIELD_CURRENCY_ID => $previousOperation->{self::FIELD_CURRENCY_ID},
				self::FIELD_CURRENCY_RATE => $previousOperation->{self::FIELD_CURRENCY_RATE},
				self::FIELD_IS_TIPS => true,
				self::FIELD_LANG => $previousOperation->{self::FIELD_LANG},
				self::FIELD_LANG_AMOUNT => $previousOperation->{self::FIELD_LANG_AMOUNT},
			];
			\Core\DB\DB::table(self::TABLE_NAME)->insert($insertData);
		}
	}

	/**
	 * Делает возврат чаевых покупателю
	 *
	 * @param int $orderId - ID заказа
	 */
	public static function refundTips(int $orderId) {
		// найдём операцию списания по чаевым
		$tipsOperation = \Core\DB\DB::table(self::TABLE_NAME)
			->where(self::FIELD_ORDER_ID, $orderId)
			->where(self::FIELD_TYPE, "order_out")
			->where(self::FIELD_STATUS, self::FIELD_STATUS_DONE)
			->where(self::FIELD_IS_TIPS, true)
			->first();

		if (!empty($tipsOperation)) {
			// поищем операцию возврата по чаевым
			$refundTipsOperation = \Core\DB\DB::table(self::TABLE_NAME)
				->where(self::FIELD_ORDER_ID, $orderId)
				->where(self::FIELD_TYPE, "refund")
				->where(self::FIELD_STATUS, self::FIELD_STATUS_DONE)
				->where(self::FIELD_IS_TIPS, true)
				->first();

			// соберём массив с суммами
			$accounts = OperationManager::getAccountOrder();
			$refundAmountArray = [];
			foreach ($accounts as $account) {
				$backAmount = $tipsOperation->{self::getMemberFundsFieldName($account)};
				if ($refundTipsOperation) {
					// если уже был частичный вовзрат по чаевым учтём это, чтобы не начислить лишнего
					$backAmount -= $refundTipsOperation->{self::getMemberFundsFieldName($account)};
				}
				if ($backAmount > 0) {
					$refundAmountArray[self::getMemberFundsFieldName($account)] = $backAmount;
				}
			}

			if (!empty($refundAmountArray)) {
				// начисляем средства покупателю
				\Model\User::query()
					->where(UserManager::FIELD_USERID, $tipsOperation->{self::FIELD_USER_ID})
					->update([
						UserManager::FIELD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_FUNDS . " + " . $tipsOperation->{self::FIELD_AMOUNT})
					]);

				/** @todo можно переделать на replicate(), когда будет описана модель операций */
				// Добавим операцию о возврате чаевых, просто копируем предыдущую операцию, и меняем необходимые данные
				$insertData = [
					self::FIELD_USER_ID => $tipsOperation->{self::FIELD_USER_ID},
					self::FIELD_TYPE => "refund",
					self::FIELD_AMOUNT => array_sum($refundAmountArray),
					self::FIELD_STATUS => self::FIELD_STATUS_DONE,
					self::FIELD_ORDER_ID => $orderId,
					self::FIELD_DATE_DONE => Helper::now(),
					self::FIELD_CURRENCY_ID => $tipsOperation->{self::FIELD_CURRENCY_ID},
					self::FIELD_CURRENCY_RATE => $tipsOperation->{self::FIELD_CURRENCY_RATE},
					self::FIELD_IS_TIPS => true,
					self::FIELD_LANG => $tipsOperation->{self::FIELD_LANG},
					self::FIELD_LANG_AMOUNT => $tipsOperation->{self::FIELD_LANG_AMOUNT},
				];
				foreach ($refundAmountArray as $amountField => $amount) {
					$insertData[$amountField] = $amount;
				}

				\Core\DB\DB::table(self::TABLE_NAME)->insert($insertData);
			}
		}
	}

	/**
	 * Возвращает соответствующее название поля, с денежными средствами, из таблицы members
	 * @param $fieldName - название поля таблицы members
	 * @return mixed
	 */
	public static function getMemberFundsFieldName(string $fieldName) {
		$flip = array_flip(self::MEMBER_FUNDS_FIELD_CONSISTENCY);
		return $flip[$fieldName];
	}

	/**
	 * Возвращает средства за заказ по акции 4+1
	 *
	 * @param \Model\Order $order - Модель заказа по которому осуществляется возврат средств по акции
	 * @param int $contest Идентификатор акции
	 * @return bool
	 * @throws Exception
	 */
	public static function sendPromoCashback(\Model\Order $order, $contest = PromoManager::CONTEST_4_PLUS_1) {
		$result = new Result();
		$insertData = [
			self::FIELD_USER_ID => $order->USERID,
			self::FIELD_TYPE => "refill",
			self::FIELD_AMOUNT => $order->price,
			self::FIELD_BASE_AMOUNT => $order->price,
			self::FIELD_STATUS => 'done',
			self::FIELD_ORDER_ID => $order->OID,
			self::FIELD_DATE_DONE => Helper::now(),
			self::FIELD_CURRENCY_ID => $order->currency_id,
			self::FIELD_CURRENCY_RATE => $order->currency_rate,
			self::FIELD_CONTEST_ID => $contest,
		];

		// начисляем средства
		$result->mergeResult(
			\Model\User::query()
				->where(UserManager::FIELD_USERID, $order->USERID)
				->update([
					UserManager::FIELD_FUNDS => \Core\DB\DB::raw(UserManager::FIELD_FUNDS . " + {$order->price}")
				])
		);

		// добавляем операцию о зачислении средств
		if($result->isSuccess()) {
			$result->mergeResult(
				\Core\DB\DB::table(self::TABLE_NAME)
					->insert($insertData)
			);
		}

		if($result->isNotSuccess()) {
			throw new Exception("При начислении средств по акции 4+1 возникла ошибка.");
		}

		return true;
	}

	/**
	 * Посчитать кол-во операций определенного типа
	 *
	 * @param mixed $userId Идентификатор пользователя
	 * @param string $type Тип операции - withdraw, etc
	 * @param string $payment Платежка - card3, etc
	 * @param string $from Дата начала периода, строка вида "2018-10-22 13:32:45"
	 * @return int
	 */
	public static function countOperations($userId, $type, $payment, $from = null): int {
		$builder = \Core\DB\DB::table(self::TABLE_NAME)
			->where([
				self::FIELD_USER_ID => $userId,
				self::FIELD_TYPE => $type,
				self::FIELD_PAYMENT => $payment,
			]);

		if ($from) {
			$builder->where(self::FIELD_DATE_CREATE, ">=", $from);
		}

		$count = $builder->count();

		return $count;
	}

	/**
	 * Рассчитать сумму заданных операпций
	 *
	 * @param mixed $userId Идентификатор пользователя
	 * @param string $from Дата начала периода, строка вида "2018-10-22 13:32:45"
	 * @param string $type Тип операции - order_in, etc
	 * @return float
	 */
	public static function sumOperations(int $userId, string $from = null, string $type = self::TYPE_ORDER_IN): float {
		$operation = Operation::where(self::FIELD_USER_ID, $userId)
			->where(self::FIELD_TYPE, $type)
			->where(self::FIELD_STATUS, self::FIELD_STATUS_DONE);

		if ($from) {
			$operation->where(self::FIELD_DATE_CREATE, ">=", $from);
		}

		$sum = $operation->sum(self::FIELD_AMOUNT);

		return $sum;
	}

	/**
	 * Обработка события "недостаточно средств"
	 *
	 * @param float $needMoney
	 * @param int $userId
	 * @throws BalanceDeficitWithOperationException
	 */
	public static function handleBalanceDeficit(float $needMoney, int $userId): void {
		$operation = new Operation();
		$operation->status = self::FIELD_STATUS_NEW;
		$operation->type = self::FIELD_TYPE_REFILL;
		$operation->user_id = $userId;
		$operation->save();

		throw new BalanceDeficitWithOperationException($needMoney, $operation->id);
	}

	/**
	 * Получить менеджера для вывода по типу вывода
	 *
	 * @param string $type Тип
	 * @return WithdrawManagerInterface|null
	 */
	public static function getWithdrawManager(string $type) {
		$withdrawManager = null;
		if (in_array($type, Withdraw::$_solarStaffTypes)) {
			$withdrawManager = new SolarStaffManager();
		}

		return $withdrawManager;
	}
}
