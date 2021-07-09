<?php

/**
 * возвращает строку, переводя в вверхний регистр в заданном режиме
 * 1 - первый символ
 * 2 - всю строку
 * 3 - каждое слово
 */
function smarty_modifier_upstring($string, $mode = 1) {
    $string = (string) $string;
    if ($mode == 1) {
        $result = mb_strtoupper(mb_substr($string, 0, 1));
        $result .= mb_substr($string, 1);
        return $result;
    }else if ($mode == 2) {
        return mb_strtoupper($string);
    }else if ($mode == 3) {
        $result = '';
        $array = explode(" ", $string);
        foreach ($array as $item) {
            $result .= smarty_modifier_upstring(trim($item)) . ' ';
        }
        return trim($result);
    }
    return $string;
}