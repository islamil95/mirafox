<?php

/**
 * возвращает часть строки
 */
function smarty_modifier_substr($string, $start, $length = null) {
    $string = (string) $string;
    return mb_substr($string, $start, $length);
}