<?php
/*
* показывает вместо чисел больше 1000 - 1К, 2К...
* для числел больше 1 000 000 - 1M, 2M
*/
function smarty_modifier_shortDigit($digit) {
	$digit = (int)$digit;

	if ($digit >= 1000000) {
		return floor($digit / 1000000) . "M";
	} elseif ($digit >= 1000) {
		return floor($digit / 1000) . "K";
	}

	return $digit;
}