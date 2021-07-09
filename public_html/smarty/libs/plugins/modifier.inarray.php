<?php

/**
 * возвращает true если $string есть в массиве $array, false если нет
 */
function smarty_modifier_inArray($array, $string) {
    if (!is_array($array)) {
        return false;
    }
    return in_array($string, $array);
}