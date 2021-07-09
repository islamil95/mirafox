<?php
/**
 * Модификатор для замены повторяющихся  переносов строк на один перенос
 *
 * @param string $text
 *
 * @return string
 */
function smarty_modifier_strip_nl($text)
{
	$text = preg_replace("/(\r\n)+/", "\r\n", $text);
	$text = preg_replace("/(\n)+/", "\n", $text);
	return $text;
}