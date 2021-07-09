<?php

namespace Currency;

/**
 * Обмен валютами
 *
 * @author jsosnovsky
 */
class CurrencyExchanger {

	private static $instance = null;

	/**
	 *
	 * @return \Currency\CurrencyExchanger
	 */
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new CurrencyExchanger();
		}
		return self::$instance;
	}

	private $currencyRate;

	private function __construct() {
		$this->currencyRate = \App::config("currency_rate");
		if (!$this->currencyRate) {
			throw new \Exception("Could not load current currency rate");
		}
	}

	public function convertToRUB($money, $rate = NULL) {
		if (is_null($rate)) {
			$rate = $this->currencyRate;
		}
		return $this->convert($rate, $money);
	}

	public function convertToUSD($money, $rate = NULL) {
		if (is_null($rate)) {
			$rate = 1 / $this->currencyRate;
		}
		return $this->convert($rate, $money);
	}

	/**
	 * Конвертировать сумму по заданному курсу
	 * @param $rate
	 * @param $money
	 * @return float|int
	 */
	public function convert($rate, $money) {
		$result = $money * $rate;
		return round($result * 100) / 100;
	}

	/**
	 * Конвертирует валюту по меткам языка ru/en
	 * @param $amount - денежная сумма
	 * @param string $langFrom - из какой валюты ковертируетм (ru/en)
	 * @param string $langTo - в какую валюту ковертируем (ru/en)
	 * @param null $rate Курс
	 * @return float|int|bool
	 */
	public function convertByLang($amount, string $langFrom, string $langTo, $rate = NULL) {
		$result = false;

		// на случай, если будет передан одинаковый язык
		if($langTo == $langFrom) {
			return $amount;
		}

		// Ковертируем в USD
		if($langFrom == \Translations::DEFAULT_LANG) {
			if($langTo == \Translations::EN_LANG) {
				$result = $this->convertToUSD($amount, $rate);
			}
		}

		// Конвертируем в RUB
		if($langFrom == \Translations::EN_LANG) {
			if($langTo == \Translations::DEFAULT_LANG) {
				$result = $this->convertToRUB($amount, $rate);
			}
		}

		return $result;
	}

	/**
	 * Конвертация валют по id валюты
	 * @param int|float $amount Сумма для конвертации
	 * @param int $currentCurrencyId Исходная валюта
	 * @param int $targetCurrencyId Целевая валюта
	 * @param null $rate Курс
	 * @return bool|float|int
	 */
	public function convertByCurrencyId($amount, int $currentCurrencyId, int $targetCurrencyId, $rate = NULL) {
		$result = false;

		if ($currentCurrencyId == $targetCurrencyId) {
			return $amount;
		}

		if ($targetCurrencyId == \Model\CurrencyModel::RUB) {
			$result = $this->convertToRUB($amount, $rate);
		} elseif ($targetCurrencyId == \Model\CurrencyModel::USD) {
			$result = $this->convertToUSD($amount, $rate);
		}

		return $result;
	}


	/**
	 * Возвращает текущий курс обмена по языку по языку
	 * @param string $lang
	 * @return float|int
	 */
	public function getCurrencyRateByLang(string $lang) {
		if($lang == \Translations::DEFAULT_LANG) {
			$currencyRate = 1.0;
		} else {
			$currencyRate = $this->convertToRUB(1.0);
		}

		return $currencyRate;
	}

	/**
	 * Возвращает текущий курс обмена по трехбуквенному ISO-коду валюты
	 * @param string $currency
	 * @return float|int
	 */
	public function getCurrencyRateByCurrencyCode(string $currency) {
		if ($currency == \Translations::RUB) {
			$currencyRate = 1.0;
		} else {
			$currencyRate = $this->convertToRUB(1.0);
		}

		return $currencyRate;
	}

	/**
	 * Возвращает текущий курс обмена по идентификатору валюты
	 * @param int $currencyId
	 * @return float|int
	 */
	public function getCurrencyRateByCurrencyId(int $currencyId) {
		return CurrencyExchanger::getInstance()->getCurrencyRateByLang(
			\Translations::getLangByCurrencyId($currencyId)
		);
	}

}
