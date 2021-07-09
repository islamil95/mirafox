<?php

/**
 * Модификатор форматирующий сумму с необходимым денежным знаком
 * @param  int|float  $price        Сумма для форматирования
 * @param  string  $userLang        Версия сайта под которой зарегистрировался пользователь
 * @param  boolean $alternativeSign Альтернативный знак (Если true, то вернет "руб." вместо знака рубля)
 * @return string                   Вернет форматированную сумму с необходимым знаком валюты
 */
function smarty_modifier_priceFormatter($price, $userLang, $alternativeSign = false) {
	if (!$userLang) return $price;

	$price = smarty_modifier_zero($price);

	$usd = '$';

	switch (Translations::getLang()) {
		case Translations::DEFAULT_LANG:
			$currencySign = ruSign($alternativeSign);
			$resultPrice = $price . $currencySign;
			break;
		case Translations::EN_LANG:
			if ($userLang == Translations::DEFAULT_LANG) {
				$currencySign = ruSign($alternativeSign);
				$resultPrice = $price . $currencySign;
			} else {
				$currencySign = $usd;
				$resultPrice = $currencySign . $price;
			}
			break;
		default:
			$resultPrice = $price;
	}

	return $resultPrice;
}

/**
 * Вернуть денежный знак российского рубля в зависимости от версии сайта
 * @param  string $alternativeSign Альтернативный знак (Если true, то вернет "руб." вместо знака рубля)
 * @return string                  Знаковая версия рубля
 */
function ruSign($alternativeSign) {
	$ruSign = '<span class="rouble">Р</span>';
	if (Translations::getLang() == Translations::DEFAULT_LANG) {
		$result = $alternativeSign ? 'руб.' : $ruSign;
	} else {
		$result = $alternativeSign ? 'rub.' : $ruSign;
	}

	return '&nbsp;' . $result;
}