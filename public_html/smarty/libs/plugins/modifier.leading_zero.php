<?php

/**
* Округляет числа он начального количества цифр
* 1 346 341 -> 1 300 000
* 142 -> 140
*
* @param float $number Число которое округляем
* @param int $digit Количество знаков в начале которые остаются нетронутыми
* @param bool $space Добавлять ли пробелы
*
* @return int
*/
function smarty_modifier_leading_zero($number, $digit = 2, $space = true) {

	if(!$number || !$digit)
		return $number;

	$number = round($number);

	if (strlen($number) > $digit) {
		$number = round($number, (strlen($number)-$digit) * -1, PHP_ROUND_HALF_DOWN);
	}

	$space = $space ? " " : "";
	return number_format((double)$number, 0, ".", $space);
}