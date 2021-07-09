<?php
/**
 * Заменить в тексте bbcode ссылки с доменами kwork.ru, kwork.com на html теги
 *
 * @param string $text текст
 * @return string
 */
function smarty_modifier_bbcode_kwork_url($text)
{
	return Helper::replaceBBCodeKworkUrl($text);
}
?>