<?php
/**
* Добавляет текстом валюту
*/
function smarty_modifier_textCurrency($price, $lang) {
	$text = $currencyId;
	if ($lang == Translations::EN_LANG) {
		$text .= '$';
	}
	$text .= $price;
	if ($lang == Translations::DEFAULT_LANG) {
		$text .= Translations::t(' руб.');
	}
	return $text;
}