<?php
/**
* добавляет пробелы после разрядов
*/
function smarty_modifier_digit_view($number, $countAfter = 0)
{
	if(Translations::isDefaultLang()) {
		return number_format($number, $countAfter, '.', ' ');
	}
	if($countAfter == 0) {
		$countAfter = 2;
	}
	return number_format($number, $countAfter, '.', ' ');
}