<?php

use Core\DB\DB;
use Model\CurrencyModel;
use Model\Notification\Notification;

class Tips {
	/**
	 * Вспомогательные константы для диапазона сумм
	 */
	const FIRST_SUM_MIN = "first_sum_min";
	const FIRST_SUM_MAX = "first_sum_max";
	const SECOND_SUM_MIN = "second_sum_min";
	const SECOND_SUM_MAX = "second_sum_max";

	/**
	 * Диапазоны с минимальной и максимальной суммой для первой и второй суммы, для русского и английского языка
	 */
	const SUM_RANGE = [
		CurrencyModel::RUB => [
			self::FIRST_SUM_MIN => 100,
			self::FIRST_SUM_MAX => 3000,
			self::SECOND_SUM_MIN => 500,
			self::SECOND_SUM_MAX => 10000,
		],
		CurrencyModel::USD => [
			self::FIRST_SUM_MIN => 2,
			self::FIRST_SUM_MAX => 60,
			self::SECOND_SUM_MIN => 10,
			self::SECOND_SUM_MAX => 200,
		]
	];

	/**
	 * Процены рассчёта для первой и второй сумм
	 */
	const FIRST_SUM_PERCENT = 10;
	const SECOND_SUM_PERCENT = 50;

	/**
	 * ID заказа
	 */
	protected $orderId;

	/**
	 * Флаг, можно оставить бонус по заказу или нет
	 */
	protected $isAvailable;

	/**
	 * Id валюты заказа
	 */
	protected $currencyId;

	/**
	 * Первая заготовленная сумма на выбор
	 */
	protected $firstSum;

	/**
	 * Вторая заготовленная сумма на выбор
	 */
	protected $secondSum;

	/**
	 * @var \Model\Order - заказ
	 */
	private $order;

	/**
	 * Tips constructor.
	 * @param \Model\Order $order
	 */
	public function __construct(\Model\Order $order) {
		$this->order = $order;
		$this->orderId = $order->OID;
		$this->isAvailable = $this->checkAvailable();
		$this->currencyId = $this->getCurrencyId();
		$this->fillSum();
	}

	/**
	 * Возвращает Id валюты в заказе
	 * @return mixed
	 */
	protected function getCurrencyId() {
		$currencyId = $this->order->{OrderManager::F_CURRENCY_ID};

		return $currencyId;
	}

	/**
	 * Заполняет свойства суммами
	 */
	protected function fillSum() {
		$orderPrice = (int) $this->order->{OrderManager::F_PRICE};
		$this->firstSum = $this->calculateFirstSum($orderPrice);
		$this->secondSum = $this->calculateSecondSum($orderPrice);
	}

	/**
	 * Проверяет возможно ли заплатить бонус
	 * @return bool
	 */
	protected function checkAvailable(): bool {
		$isAvailable = true;
		// проверяем является ли текущий пользователь заказчиком по данном заказу
		if($this->order->payer->USERID != UserManager::getCurrentUserId()) {
			return false;
		}

		// проверяем что заказ находится в статусе DONE
		if($this->order->{OrderManager::F_STATUS} != OrderManager::STATUS_DONE) {
			return false;
		}


		// проверяем оставлен ли отрицательный отзыв
		$hasBadReview = DB::table(RatingManager::TABLE_REVIEW)
			->where(RatingManager::F_ORDER_ID, $this->orderId)
			->where(RatingManager::F_AUTO_MODE, NULL)
			->where(RatingManager::F_BAD, 1)
			->exists();

		if($hasBadReview) {
			return false;
		}

		// проверям что бонус не был ранее отправлен

		if (!empty($this->order->tracks->where(TrackManager::FIELD_TYPE, \Track\Type::PAYER_SEND_TIPS)->first())) {
			$hasBonus = true;
		} else {
			$hasBonus = false;
		}

		if($hasBonus) {
			return false;
		}

		// не истекло ли время, возможность оставить бонус аналогична возможности написать отзыв
		$canReview = RatingManager::inCreateTime($this->orderId);
		if(!$canReview) {
			return false;
		}

		return $isAvailable;
	}

	/**
	 * Рассчитывает первую сумму
	 * @param $orderPrice
	 * @return float|int
	 */
	protected function calculateFirstSum($orderPrice) {
		$sum = $orderPrice * (self::FIRST_SUM_PERCENT / 100);
		if($sum < self::SUM_RANGE[$this->currencyId][self::FIRST_SUM_MIN]) {
			$sum = self::SUM_RANGE[$this->currencyId][self::FIRST_SUM_MIN];
		} elseif($sum > self::SUM_RANGE[$this->currencyId][self::FIRST_SUM_MAX]) {
			$sum = self::SUM_RANGE[$this->currencyId][self::FIRST_SUM_MAX];
		}

		return $sum;
	}

	/**
	 * Рассчитывает вторую сумму
	 * @param $orderPrice
	 * @return float|int
	 */
	protected function calculateSecondSum($orderPrice) {
		$sum = $orderPrice * (self::SECOND_SUM_PERCENT / 100);
		if($sum < self::SUM_RANGE[$this->currencyId][self::SECOND_SUM_MIN]) {
			$sum = self::SUM_RANGE[$this->currencyId][self::SECOND_SUM_MIN];
		} elseif($sum > self::SUM_RANGE[$this->currencyId][self::SECOND_SUM_MAX]) {
			$sum = self::SUM_RANGE[$this->currencyId][self::SECOND_SUM_MAX];
		}

		return $sum;
	}


	/**
	 * Можно ли перевести бонус продавцу
	 * @return bool
	 */
	public function isAvailable(): bool {
		return $this->isAvailable;
	}

	/**
	 * Возвращает первую сумму
	 * @return float|int
	 */
	public function getFirstSum() {
		return $this->firstSum;
	}

	/**
	 * Возвращает первую сумму ввиде строки с обозначением валюты
	 * @return string
	 */
	public function getFirstSumText(): string {
		$result = $this->firstSum . " ₽";
		if($this->currencyId == CurrencyModel::USD) {
			$result = "Tip $" . $this->firstSum;
		}

		return $result;
	}

	/**
	 * Возвращает вторую сумму
	 * @return float|int
	 */
	public function getSecondSum() {
		return $this->secondSum;
	}

	/**
	 * Возвращает вторую сумму ввиде строки с обозначением валюты
	 * @return string
	 */
	public function getSecondSumText(): string {
		$result = $this->secondSum . " ₽";
		if($this->currencyId == CurrencyModel::USD) {
			$result = "Tip $" . $this->secondSum;
		}

		return $result;
	}

	/**
	 * Возвращает тип валюты для суммы
	 * @return string
	 */
	public function getSumCurrency(): string {
		$currency = "₽";
		if($this->currencyId == CurrencyModel::USD) {
			$currency = "$";
		}

		return $currency;
}

	/**
	 * Возвращает минимальную сумму бонуса
	 * @return int
	 */
	public function getMinSum(): int {
		return self::SUM_RANGE[$this->currencyId][self::FIRST_SUM_MIN];
	}

	/**
	 * Возвращает максимальную сумму бонуса
	 * @return int
	 */
	public function getMaxSum(): int {
		return self::SUM_RANGE[$this->currencyId][self::SECOND_SUM_MAX];
	}

	/**
	 * Отправляем бонус исполнителю
	 *
	 * @param int $orderId -ID заказа
	 * @param int $amount - сумма бонуса
	 * @param string $message - комментарий исполнителю
	 * @return bool
	 */
	public static function sendTips(int $orderId, int $amount, string $message = "") {
		// защитимся от дублей и повторной отправки
		$lock = new \DbLock\DbLock(\DbLock\LockEnum::getWithId(\DbLock\LockEnum::ORDER, $orderId));
		$exist = \Model\Tips::query()
			->where(\Model\Tips::F_ORDER_ID, $orderId)
			->exists();

		if ($exist) {
			return false;
		}

		$order = \Model\Order::query()
			->find($orderId, [
				OrderManager::F_ORDER_ID,
				OrderManager::F_WORKER_ID,
				OrderManager::F_USERID,
				OrderManager::F_KWORK_ID,
				OrderManager::F_RATING_TYPE,
				OrderManager::F_PROJECT_ID,
				OrderManager::F_CURRENCY_ID,
			]);

		// #6318 Комиссии считаются по прогрессивной шкале
		$lang = \Translations::getLangByCurrencyId($order->{OrderManager::F_CURRENCY_ID});
		$turnover = \OrderManager::getTurnover($order->{OrderManager::F_WORKER_ID}, $order->{OrderManager::F_USERID}, $lang);
		$commission = \OrderManager::calculateCommission($amount, $turnover, $lang);
		$ctp = $commission->priceKwork;

		// производим списание/начисление средств
		try {
			$result = OperationManager::sendTips($orderId, $order->{OrderManager::F_USERID}, $order->{OrderManager::F_WORKER_ID}, $amount, $ctp);
		} catch (Exception $exception) {
			Log::daily($exception->getMessage() . "\n" . $exception->getTraceAsString(), "error");
			$result = false;
		}

		if ($result) {
			// добавляем запись на страницу трекинга заказа
			$trackId = TrackManager::create($orderId, Track\Type::PAYER_SEND_TIPS);
			if ($trackId) {
				// записываем данные о бонусе
				\Model\Tips::query()
					->insert([
						\Model\Tips::F_TRACK_ID => $trackId,
						\Model\Tips::F_ORDER_ID => $orderId,
						\Model\Tips::F_AMOUNT => $amount,
						\Model\Tips::F_CRT => $amount - $ctp,
						\Model\Tips::F_COMMENT => $message,
						\Model\Tips::FIELD_CURRENCY_RATE => \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByLang($lang),
					]);

				// если у заказа нет отзыва то обновляем поле rating_type
				if (in_array($order->{OrderManager::F_RATING_TYPE}, [OrderManager::RATING_TYPE_FIRST, OrderManager::RATING_TYPE_SECOND])) {
					$order->{OrderManager::F_RATING_TYPE} = OrderManager::RATING_TYPE_TIPS;
					$order->save();
				}

				return true;
			}
		}

		return false;
	}
}