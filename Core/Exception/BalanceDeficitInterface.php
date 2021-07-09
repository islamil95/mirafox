<?php


namespace Core\Exception;

/**
 * Интерфейс для унификации эксепшенов по недостаточности баланса
 */
interface BalanceDeficitInterface {

	/**
	 * @return float
	 */
	public function getNeedMoney(): float;

	/**
	 * @param float $money
	 */
	public function setNeedMoney(float $money);
}