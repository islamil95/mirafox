<?php declare(strict_types=1);

namespace Core\Exception;

use Exception;
use Throwable;
use Translations;

final class BalanceDeficitException extends Exception implements BalanceDeficitInterface {
	/**
	 * @var int
	 */
	private $userId;

	/**
	 * @var float
	 */
	private $needMoney;

	/**
	 * @param int $userId
	 * @param float $needMoney
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct(int $userId, float $needMoney, string $message = "", int $code = 0, Throwable $previous = null) {
		$message = $message ?? Translations::t("Недостаточно средств на балансе");

		parent::__construct($message, $code, $previous);

		$this->userId = $userId;
		$this->needMoney = $needMoney;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->userId;
	}

	/**
	 * @return float
	 */
	public function getNeedMoney(): float {
		return $this->needMoney;
	}

	/**
	 * @param float $needMoney
	 */
	public function setNeedMoney(float $needMoney): void {
		$this->needMoney = $needMoney;
	}
}