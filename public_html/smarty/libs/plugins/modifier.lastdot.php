<?php

/**
 * Добавить в конец строки точку если строка не заканчивается на точку
 *
 * @param string $string Входящая строка
 *
 * @return string
 */
function smarty_modifier_lastdot($string) {
	if (strlen($string) && mb_substr($string, -1) !== ".") {
		return $string . ".";
	}
	return $string;
}
