<?php

class Translations {

	const DEFAULT_LANG = 'ru';
	const EN_LANG = 'en';

	/**
	 * Строковые представления валют
	 */
	const RUB = "RUB";
	const USD = "USD";

	/**
	 * Сокращенные представления валют для вывода
	 */
	const CURRENCY_SHORT = [
		self::RUB => "руб.",
		self::USD => "$"
	];

	private static $langs = [
		self::DEFAULT_LANG,
		self::EN_LANG
	];

	/**
	 * @var string текущий хост (rudomain/endomain)
	 */
	private static $host;

	public static function getLangArray() {
		return self::$langs;
	}

	public static function isDefaultLang() {
		return self::getLang() == self::DEFAULT_LANG;
	}

	public static function getLang() {
		return self::DEFAULT_LANG;
	}

	/**
	 * Получить идентификатор валюты по текущему языку
	 * @return int
	 */
	public static function getLangCurrency() : int {
		if (self::isDefaultLang()) {
			return \Model\CurrencyModel::RUB;
		}
		return \Model\CurrencyModel::USD;
	}

	/**
	 * Получить идентификатор валюты для представленного языка
	 * @param string $lang Язык ru|en
	 * @return int
	 */
	public static function getCurrencyIdByLang(string $lang) : int {
		if ($lang == self::DEFAULT_LANG) {
			return \Model\CurrencyModel::RUB;
		}
		return \Model\CurrencyModel::USD;
	}

	/**
	 * Получить язык по идентификатору валюты
	 *
	 * @param int $currencyId Идентификатор валюты
	 *
	 * @return string
	 */
	public static function getLangByCurrencyId(int $currencyId) : string {
		if ($currencyId == \Model\CurrencyModel::USD) {
			return self::EN_LANG;
		}
		return self::DEFAULT_LANG;
	}

	/**
	 * Возвращает строковое представление валюты по языку
	 * @param string $lang
	 * @return string
	 */
	public static function getCurrencyByLang(string $lang) : string {
		$currency = self::RUB;
		if ($lang == Translations::EN_LANG) {
			$currency = self::USD;
		}

		return $currency;
	}

	/**
	 * Возвращает сокращенное представление валюты по языку
	 * @param string $lang
	 * @return string
	 */
	public static function getShortCurrencyByLang(string $lang) : string {
		$currency = self::CURRENCY_SHORT[self::getCurrencyByLang($lang)];
		if (!$currency) {
			$currency = "";
		}

		return $currency;
	}

	/**
	 * Получение цены вместе со знаком валюты
	 *
	 * @param float $price Цена
	 * @param int $currencyId Идентификатор валюты
	 * @param string $ruRub русское обозначение валюты (по умолчанию руб.)
	 *
	 * @return string
	 */
	public static function getPriceWithCurrencySign(float $price, int $currencyId, string $ruRub = "руб.") {
		if ($currencyId == \Model\CurrencyModel::RUB) {
			if (Translations::isDefaultLang()) {
				return Helper::zero($price) . " " . $ruRub;
			}
			return Helper::zero($price) . " RUR";
		} else {
			return "$" . Helper::zero($price);
		}
	}

	/**
	 * Возвращает имя хоста в зависимости от текущего языка
	 * @return string
	 */
	public static function getCurrentHost() {
		if (self::$host) {
			return self::$host;
		}

		if (self::getLang() === self::DEFAULT_LANG) {
			self::$host = App::config("rudomain");
		} else {
			self::$host = App::config("endomain");
		}

		return self::$host;
	}

	/**
	 * Переводит имя хоста в зависимости от текущего языка
	 * @return string
	 */
	public static function translateCurrentHost() {
		if (self::getLang() == self::EN_LANG) {
			$host = App::config("rudomain");
		} else {
			$host = App::config("endomain");
		}
		return $host;
	}

	/**
	 * Возвращает имя домена по языку
	 * @param string $lang
	 * @return string
	 */
	public static function getDomainByLang(string $lang) : string {
		if ($lang == self::EN_LANG) {
			$domain = App::config('endomain');
		} else {
			$domain = App::config('rudomain');
		}

		return $domain;
	}

	/**
	 * Возвращает список языковых доменов
	 * @return array [lang => domain]
	 */
	public static function getDomains() : array {
		$domains = array_flip(self::getLangArray());
		foreach ($domains as $lang => &$domain) {
			$domain = self::getDomainByLang($lang);
		}

		return $domains;
	}

	public static function t() {
		$args = func_get_args();
		if (empty($args)) {
			return "";
		}
		$translated = @call_user_func_array("sprintf", $args);
		return $translated;
	}

	/**
	 * Перевод зависимый от множественных чисел
	 * первый аргумент - строка которую переводим msgid,
	 * второй аргумент - число по склоняются формы msgstr
	 * остальные аргументы - подстановки в строку для printf
	 *
	 * @return string
	 */
	public static function tn() {
		$args = func_get_args();
		if (empty($args)) {
			return "";
		}
		$singular = array_shift($args);
		$count = array_shift($args); // Извлекаем число из массива подстановок
		array_unshift($args, $singular);
		return forward_static_call_array("self::t", $args);
	}

	/**
	 * Перевод строки с параметрами в виде массива.
	 *
	 * @param string $string Строка для перевода
	 * @param array $params Параметры
	 * @return string
	 */
	public static function ta(string $string, ?array $params = []): string {
		$params = $params ?? [];
		array_unshift($params, $string);
		return forward_static_call_array("self::t", $params);
	}

	/**
	 * Перевод по заданному языку (если русский то не переводит, просто вставляет замены)
	 *
	 * Первый аргумент - язык ru|en
	 * Второй строка для перевода
	 * Последующие - замены
	 *
	 * @return string
	 */
	public static function translateByLang() {
		$args = func_get_args();
		if (empty($args)) {
			return "";
		}
		$lang = array_shift($args); // Извлекаем язык из массива аргументов
		return forward_static_call_array("self::t", $args);
	}
}
