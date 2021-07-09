<?php

namespace Core\Exception;
use Mobile\Constants;

/**
 * Недостаточность баланса пользователя с идентификатором заказа
 */
class BalanceDeficitWithOperationException extends JsonException implements BalanceDeficitInterface {

	/**
	 * @var float Сумма которую нужно доплатить
	 */
	protected $needMoney;

	/**
	 * @var int Идентификатор заказа
	 */
	protected $operationId;

	/**
	 * BalanceDeficitException constructor.
	 *
	 * @param float $needMoney Необходимое количество доплаты
	 * @param int $operationId Идентификатор операции
	 * @param string $message Текст ошибки
	 * @param int $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(float $needMoney = 0.0, int $operationId = null, string $message = "", int $code = Constants::CODE_NEED_MORE_MONEY, \Throwable $previous = null) {
		if ($message == "") {
			$message = \Translations::t("Недостаточно средств на балансе");
		}
		$this->needMoney = $needMoney;
		$this->operationId = $operationId;
		parent::__construct($message, $code, $previous);
		$this->setData([
			"success" => false,
			"needMoney" => $needMoney,
			"operationId" => $operationId,
			"code" => $code,
			// Совместимость с текущей функцией пополнения баланса и повторного сабмита
			"difference" => $needMoney,
			"payment_id" => $operationId,
			"error" => "funds",
		]);
	}

	/**
	 * @return float
	 */
	public function getNeedMoney(): float {
		return $this->needMoney;
	}

	/**
	 * @param $money
	 */
	public function setNeedMoney(float $money): void {
		$this->needMoney = $money;
	}

	/**
	 * @return int
	 */
	public function getOperationId() {
		return $this->operationId;
	}

	/**
	 * @param int $operationId
	 */
	public function setOperationId($operationId) {
		$this->operationId = $operationId;
	}


}