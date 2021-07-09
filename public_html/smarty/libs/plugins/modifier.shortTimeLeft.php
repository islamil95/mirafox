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
 * @return bool|string
 */
function smarty_modifier_shortTimeLeft($seconds, $textBefore = false, $textAfter = false, $significantDigit = 1, $short = true)
{
    return Helper::timeLeft($seconds, $textBefore, $textAfter, $significantDigit, $short);
}
