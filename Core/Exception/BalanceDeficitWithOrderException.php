<?php

namespace Core\Exception;
use Mobile\Constants;

/**
 * Недостаточность баланса пользователя с идентификатором заказа
 */
class BalanceDeficitWithOrderException extends JsonException implements BalanceDeficitInterface {

	/**
	 * @var float Сумма которую нужно доплатить
	 */
	protected $needMoney;

	/**
	 * @var int Идентификатор заказа
	 */
	protected $orderId;

	/**
	 * BalanceDeficitException constructor.
	 *
	 * @param float $needMoney Необходимое количество доплаты
	 * @param int $orderId Идентификатор заказа
	 * @param string $message Текст ошибки
	 * @param int $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(float $needMoney = 0.0, int $orderId = null, string $message = "", int $code = Constants::CODE_NEED_MORE_MONEY, \Throwable $previous = null) {
		if ($message == "") {
			$message = \Translations::t("Недостаточно средств на балансе");
		}
		$this->orderId = $orderId;
		$this->needMoney = $needMoney;
		parent::__construct($message, $code, $previous);
		$this->setData([
			"success" => false,
			"needMoney" => $needMoney,
			"orderId" => $orderId,
			"code" => $code,
		]);
	}

	/**
	 * @return float
	 */
	public function getNeedMoney(): float {
		return $this->needMoney;
	}

	/**
	 * @param float $money
	 */
	public function setNeedMoney(float $money): void {
		$this->needMoney = $money;
	}

	/**
	 * @return int
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @param int $orderId
	 */
	public function setOrderId($orderId) {
		$this->orderId = $orderId;
	}


}