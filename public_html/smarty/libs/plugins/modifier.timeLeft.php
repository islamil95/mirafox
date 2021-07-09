<?php
/**
 * показывает сколько времени назад наступила дата
 *
 * @param            $seconds
 *
 * @param array|bool $textBefore
 * @param string     $textAfter
 *
 * @param int        $significantDigit максимальное количество значащих чисел
 *
 * @param bool       $short
 *
 * @return bool|string
 */
function smarty_modifier_timeLeft($seconds, $textBefore = false, $textAfter = 'назад', $significantDigit = 1, $short = false)
{
    return Helper::timeLeft($seconds, $textBefore, Translations::t($textAfter), $significantDigit, $short);
}