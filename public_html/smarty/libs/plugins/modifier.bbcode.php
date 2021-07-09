<?php
/**
* заменяет теги bbcode на html
*/
function smarty_modifier_bbcode($text)
{
	return Helper::replaceBBCode($text);
}
?>