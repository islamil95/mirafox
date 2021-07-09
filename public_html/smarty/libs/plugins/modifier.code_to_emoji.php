<?php
/**
 * Заменить в тексте [:код emoji] на изображение
 *
 * @param string $text текст
 * @return string
 */
function smarty_modifier_code_to_emoji($text) {
	preg_match_all("/\[:([^\]]*)\]/im", $text, $matches, PREG_SET_ORDER);
	foreach ($matches as $row) {
		$code = "&#x" . str_replace("-", ";&#x", $row[1]) . ";";
		// Заменяем шорткод тегом и отделяем одним пробелом
		$text = str_replace($row[0], "<span class=\"message-emoji-icon message-emoji-icon_$row[1]\"><img src=\"" . Helper::cdnImageUrl("/emoji/emoji-blank.png") . "\" alt=\"$code\"></span>", $text);
	}
	return $text;
}

?>