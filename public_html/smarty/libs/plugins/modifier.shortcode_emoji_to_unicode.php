<?php
/**
 * Заменить в тексте [:код emoji] на unicode emoji
 *
 * @param string $text текст
 * @return string
 */
function smarty_modifier_shortcode_emoji_to_unicode($text)
{
	preg_match_all("/\[:([^\]]*)\]/im", $text, $matches, PREG_SET_ORDER);
	foreach($matches as $row) {
		$code = "&#x".str_replace("-", ";&#x", $row[1]).";";
		// Заменяем шорткод тегом и отделяем одним пробелом
		$text = str_replace($row[0], "$code", $text);
	}
	return $text;
}
?>