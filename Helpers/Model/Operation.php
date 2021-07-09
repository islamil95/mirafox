<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Withdraw\Withdraw;

/**
 * Class Operation Таблица операций пользователя. Содержит информацию о пополнении, оплатах, возвратах, выводах.
 *
 * @package Model
 * @mixin \EloquentTypeHinting
 * @property int id Id операции
 * @property int user_id Id пользователя
 * @property string type Тип операции
 * @property float amount Общая сумма операции
 * @property float base_amount Сумма операции по основному счету
 * @property float bonus_amount Сумма операции по основному счету
 * @property float bill_amount Сумма операции по основному счету
 * @property float card_amount Сумма операции основному счету
 * @property string status Статус: new — новый, inprogress — в процессе, done — выполнена, cancel — отменена
 * @property string payment Способ пополнения / вывода / возврата
 * @property int order_id Id заказа, если операция связана с заказом
 * @property int extra_id Id опции заказа, если операция связана с опцией заказа
 * @property int kwork_id Id кворка, если операция связана с модерацией кворка
 * @property int bonus_id Id промокода (таблица bonus) при пополнении по промокоды, сгорания за неиспользование промокода
 * @property int date_create Дата создания
 * @property int date_done Дата выполнения
 * @property int is_extra Флаг что операция это оплата опции заказа
 * @property int ref_id Id реферала в операциях пополнения за реферала
 * @property int is_send_on_withdraw была ли операция отправлена на вывод. Используется для выборки refill
 * @property int wd_operation_from_id из какой операции сделан возврат. Используется при возврате refill с карты
 * @property int request_id Id запроса в операциях начисления оплаты за модерацию запроса
 * @property int abuse_points Штрафные баллы при отмене заказа (для показа этого в админке)
 * @property int abuse_points_total Сумма штрафных баллов пользователя на момент операции (для показа этого в админке)
 * @property int currency_id Id валюты операции
 * @property float currency_rate Множитель валюты
 * @property int contest_id Приз за участие в конкурсе
 * @property int transferred поле для карточных пополнений. Если 1, то трансфер операции произведен на основной счет
 * @property int is_tips является операцией по чаевым (бонус продавцу)
 * @property string sub_type уточнение типа операции, unitpay — пополнение юнитпей, bill — безнал, admin — с админки
 * @property int epayments_id Id операции в платежной системе Epayments, таблица epayments
 * @property string lang Язык операции для фильтрации в админке (может не совпадать с валютой операции)
 * @property float lang_amount Сумма в валюте языка операции
 * @property int $order_stage_id Идентификатор этапа заказа
 *
 * Связанные модели
 * @property-read SolarOperation solarOperation операция вывода в солар
 * @property-read Unitpay unitpayOperation операция пополнения через unitpay
 * @property-read User user Пользователь
 * @property-read \Model\PaymoreRefill paymoreRefill Операция пополнения через paymore (Касса)
 */
class Operation extends Model {

	const TABLE_NAME = "operation";
	const FIELD_ID = "id";
	const FIELD_USER_ID = "user_id";
	const FIELD_TYPE = "type";
	const FIELD_AMOUNT = "amount";
	const FIELD_BASE_AMOUNT = "base_amount";
	const FIELD_BONUS_AMOUNT = "bonus_amount";
	const FIELD_BILL_AMOUNT = "bill_amount";
	const FIELD_CARD_AMOUNT = "card_amount";
	const FIELD_STATUS = "status";
	const FIELD_PAYMENT = "payment";
	const FIELD_ORDER_ID = "order_id";
	const FIELD_EXTRA_ID = "extra_id";
	const FIELD_KWORK_ID = "kwork_id";
	const FIELD_BONUS_ID = "bonus_id";
	const FIELD_DATE_CREATE = "date_create";
	const FIELD_DATE_DONE = "date_done";
	const FIELD_IS_EXTRA = "is_extra";
	const FIELD_REF_ID = "ref_id";
	const FIELD_IS_SEND_ON_WITHDRAW = "is_send_on_withdraw";
	const FIELD_WD_OPERATION_FROM_ID = "wd_operation_from_id";
	const FIELD_REQUEST_ID = "request_id";
	const FIELD_ABUSE_POINTS = "abuse_points";
	const FIELD_ABUSE_POINTS_TOTAL = "abuse_points_total";
	const FIELD_CURRENCY_ID = "currency_id";
	const FIELD_CURRENCY_RATE = "currency_rate";
	const FIELD_CONTEST_ID = "contest_id";
	const FIELD_TRANSFERRED = "transferred";
	const FIELD_IS_TIPS = "is_tips";
	const FIELD_SUB_TYPE = "sub_type";
	const FIELD_EPAYMENTS_ID = "epayments_id";

	/**
	 * Идентификатор этапа заказа
	 */
	const FIELD_ORDER_STAGE_ID = "order_stage_id";

	const TYPE_WITHDRAW = "withdraw";
	const TYPE_MONEYBACK = "moneyback"; // возврат пополнения
	const TYPE_ADM_REFUND = "adm_refund";
	const TYPE_REFUND = "refund";
	const TYPE_REFILL = "refill";
	const TYPE_ORDER_OUT = "order_out";
	const TYPE_ORDER_IN = "order_in";
	const TYPE_POST_OUT = "post_out";
	const TYPE_ORDER_OUT_BONUS = "order_out_bonus";
	const TYPE_REFUND_BONUS = "refund_bonus";
	const TYPE_REFILL_BONUS = "refill_bonus";
	const TYPE_REFILL_REFERAL = "refill_referal";
	const TYPE_CANCEL_BONUS = "cancel_bonus";
	const TYPE_REFILL_MODER_KWORK = "refill_moder_kwork";
	const TYPE_ORDER_OUT_YANDEX = "order_out_yandex";
	const TYPE_ORDER_IN_YANDEX = "order_in_yandex";
	const TYPE_ORDER_OUT_BILL = "order_out_bill";
	const TYPE_REFUND_BILL = "refund_bill";
	const TYPE_REFILL_BILL = "refill_bill";
	const TYPE_REFILL_MODER_REQUEST = "refill_moder_request";

	const STATUS_NEW = "new";
	const STATUS_INPROGRESS = "inprogress";
	const STATUS_DONE = "done";
	const STATUS_CANCEL = "cancel";

	/**
	 * Подтип операции, проводимые через Solar
	 */
	const SUB_TYPE_SOLAR = "solar";

	/**
	 * Подтип операции, проводимые через Unitpay
	 */
	const SUB_TYPE_UNITPAY = "unitpay";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	/**
	 * Существует ли операция, по первичному ключу.
	 * @param $id
	 * @return bool
	 */
	public static function isExistsByPK($id): bool {
		return self::query()
			->whereKey($id)
			->exists();
	}
	/**
	 * Получить сумму поля operation.amount для массива $userIds
	 * На выходе: массив ключи которого - user_id, значение - сумма
	 * @param $userIds - array
	 * @param string $operation_type - 'earned' или 'spend'
	 * @return array
	 */
	public static function getSumAmount(array $userIds, $operation_type = 'earned')
	{

		$type = ['order_in']; // earned
		if ($operation_type == 'spend') {
			$type = ['order_out', 'order_out_bonus', 'order_out_bill'];
		}

		$rows = \Model\Operation::query()
			->select("user_id", \Core\DB\DB::raw("SUM(amount) AS sum_amount"))
			->whereIn('type', $type)
			->where('status', 'done')
			->whereIn('user_id', $userIds)
			->groupBy('user_id')
			->get()
			->toArray();

		$res = [];
		foreach ($rows as $row) {
			$res[$row['user_id']] = $row['sum_amount'];
		}

		foreach ($userIds as $userId) {
			if (!isset($res[$userId])) {
				$res[$userId] = 0;
			}
		}

		return $res;
	}

	/**
	 * Связь с операцией вывода в солар
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function solarOperation() {
		return $this->hasOne(SolarOperation::class, SolarOperation::FIELD_OPERATION_ID, self::FIELD_ID);
	}

	/**
	 * Связь с операцией пополнения через unitpay
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function unitpayOperation() {
		return $this->hasOne(Unitpay::class, Unitpay::FIELD_OPERATION_ID, self::FIELD_ID);
	}

	/**
	 * Связь с пользователем
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, self::FIELD_USER_ID, User::FIELD_USERID);
	}

	/**
	 * Связь с операцией пополнения через paymore (Касса)
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function paymoreRefill() {
		return $this->hasOne(PaymoreRefill::class);
	}

	/**
	 * Заплаченная сумма за заказ по операциям
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int|null $orderStageId
	 *
	 * @return float
	 */
	public static function getOrderPayedSum(int $orderId, int $orderStageId = null) {
		$query = Operation::where(Operation::FIELD_ORDER_ID, $orderId)
			->where(Operation::FIELD_STATUS, \OperationManager::FIELD_STATUS_DONE);

		if ($orderStageId) {
			$query->where(Operation::FIELD_ORDER_STAGE_ID, $orderStageId);
		}

		// Зачисления по заказу
		$amount = (clone $query)
			->whereIn(Operation::FIELD_TYPE, [
				\OperationManager::TYPE_ORDER_OUT,
				\OperationManager::TYPE_ORDER_OUT_BONUS,
				\OperationManager::TYPE_ORDER_OUT_BILL,
			])
			->sum(Operation::FIELD_AMOUNT);

		// Возвраты по заказу
		$refundAmount = (clone $query)
			->where(Operation::FIELD_TYPE, \OperationManager::TYPE_REFUND)
			->sum(Operation::FIELD_AMOUNT);

		return bcsub($amount, $refundAmount, 2);
	}

	/**
	 * Операция в процессе?
	 *
	 * @return bool
	 */
	public function isInprogress() {
		return $this->status == self::STATUS_INPROGRESS;
	}

	/**
	 * Операция новая?
	 *
	 * @return bool
	 */
	public function isNew() {
		return $this->status == self::STATUS_NEW;
	}

	/**
	 * Операция выполнена?
	 *
	 * @return bool
	 */
	public function isDone() : bool {
		return $this->status == self::STATUS_DONE;
	}

	/**
	 * Операция отменена?
	 *
	 * @return bool
	 */
	public function isCancel() : bool {
		return $this->status == self::STATUS_CANCEL;
	}

	/**
	 * Операция вывода через Solar?
	 *
	 * @return bool
	 */
	public function isSolar() {
		return in_array($this->payment, Withdraw::$_solarStaffTypes);
	}
}