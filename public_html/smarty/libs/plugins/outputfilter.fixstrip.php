<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFilter
 */
/**
 * Исправление багов после работы smarty тега {strip}.
 * Плагин устраняет отсутствие пробелов между соседними атрибутами в тегах html.
 *
 * @param string $source input string
 * @return string filtered output
 *
 */
function smarty_outputfilter_fixstrip(string $source):string {

	// Некоторые атрибуты сожержат html теги, что ломает регулярное выражение.
	// Чтобы избежать этого экранируем такие атрибуты перед началом работы, а в конце переименовываем обратно.
	$bracket_alias = [ 'open' => "@#@_open_@#@", 'close' => "@#@_close_@#@" ];
	list($source, $count_attr_with_html_tags) = clear_broken_attr($source, $bracket_alias);

	// Очистим код от js, перед началом поиска
	$clean_source = preg_replace("|<script.*?</script>|s", "", $source);

	// Выбираем все теги с атрибутами
	$tags=[];
	if (preg_match_all('|<[^>]+[=]+[^>]+>|sU', $clean_source, $patt)) {
		$tags = $patt[0];
	}

	$pattern_single_quote = "|([\w]+\s{0,}=\s{0,}'[^']{0,}')([^\s>]{1})|sU";
	$pattern_double_quote = '|([\w]+\s{0,}=\s{0,}"[^"]{0,}")([^\s>]{1})|sU';

	foreach ($tags as $tag_old) {
		$tag_new = preg_replace($pattern_double_quote, "$1 $2", $tag_old, -1, $count1);
		$tag_new = preg_replace($pattern_single_quote, "$1 $2", $tag_new, -1, $count2);

		if (($count1 || $count2) && $tag_new !== $tag_old) {
			$source = str_replace($tag_old, $tag_new, $source);
		}
	}

	// Восстановление экранированных тегов
	if ($count_attr_with_html_tags) {
		$source = revert_broken_attr($source, $bracket_alias);
	}

	return $source;
}

/**
 * Экранирование невалидных атрибутов
 * @param string $source
 * @param array $bracket_alias
 * @return array
 */
function clear_broken_attr(string $source, array $bracket_alias):array {

	$attr_with_html_tags = ['data-tooltip-text']; // атрибуты, содержащие в себе html теги
	$count_attr_with_html_tags = 0;

	$callback_replace_brackets = function ($matches) use ($bracket_alias) {
		$attr = $matches[1];
		$attr = str_replace("<", $bracket_alias['open'], $attr);
		$attr = str_replace(">", $bracket_alias['close'], $attr);
		return $attr;
	};

	while ($attr = array_pop($attr_with_html_tags)) {
		$source = preg_replace_callback_array(
			[
				'|('.$attr.'\s{0,}=\s{0,}"[^"]+")|sU' => $callback_replace_brackets,
				"|(".$attr."\s{0,}=\s{0,}'[^']+')|sU" => $callback_replace_brackets,
			]
			,$source,-1, $count_attr_with_html_tags);
	}
	return [$source, $count_attr_with_html_tags];
}

/**
 * Откат экранированных атрибутов к начальному состоянию
 * @param string $source
 * @param array $bracket_alias
 * @return string
 */
function revert_broken_attr(string $source, array $bracket_alias):string {
	$source = str_replace("{$bracket_alias['open']}", "<", $source);
	$source = str_replace("{$bracket_alias['close']}", ">", $source);
	return $source;
}
