<?php

/**
 * Класс, который определяет к какому языку относится операция
 * Class OperationLanguageManager
 */

use \Model\Kwork;
use \Model\Order;

class OperationLanguageManager {

	/**
	 * Определить язык операции
	 *
	 * @param string $type Тип операции
	 * @param int $operationCurrencyId Идентификатор валюты операции
	 * @param int $orderCurrencyId Идентификатор валюты из заказа
	 * @param int $isTips Флаг "бонус за заказ"
	 * @return string
	 */
	public static function detectLang($type, $operationCurrencyId, $orderCurrencyId = 0) {
		switch ($type) {
			case OperationManager::TYPE_ORDER_IN:
			case OperationManager::TYPE_ORDER_OUT:
			case OperationManager::TYPE_REFUND:
			case OperationManager::TYPE_REFILL_REFERAL:
				if (empty($orderCurrencyId)) {
					$lang = Translations::getLangByCurrencyId($operationCurrencyId);
				} else {
					$lang = Translations::getLangByCurrencyId($orderCurrencyId);
				}
				break;
			default:
				$lang = Translations::getLangByCurrencyId($operationCurrencyId);
		}
		return $lang;
	}

	/**
	 * Перевести сумму в валюту языка операции $lang
	 *
	 * @param string $type Тип операции
	 * @param float $amount Исходная сумма операции
	 * @param int $currencyId Идентификатор валюты операции
	 * @param string $lang Язык операции
	 * @param float $rate Курс валюты языка $lang к валюте $currencyId
	 * @return bool|float|int
	 */
	public static function getAmountByType($type, $amount, $currencyId, $lang, $rate = 0.0) {
		switch ($type) {
			case OperationManager::TYPE_ORDER_IN:
			case OperationManager::TYPE_ORDER_OUT:
			case OperationManager::TYPE_REFUND:
			case OperationManager::TYPE_REFILL_REFERAL:
				$currencyLang = Translations::getLangByCurrencyId($currencyId);
				if ($currencyLang != $lang) {
					if(empty($rate)) {
						$rate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByLang($lang);
					}
					if($lang == Translations::EN_LANG) {
						$rate = 1 / $rate;
					}
					$langAmount = \Currency\CurrencyExchanger::getInstance()->convert($rate, $amount);
				} else {
					$langAmount = $amount;
				}
				break;
			default:
				$langAmount = $amount;
		}
		return $langAmount;
	}

}