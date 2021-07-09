<?php
/**
* выводит дату в формате год-день-месяц час:минута:секунда
*/
function smarty_modifier_timestamp_to_date($timestamp, $mode = 1) {
    if ($mode == 1) {
        return date("Y-d-m H:i:s", $timestamp);
    }else if ($mode == 2){
        return date("Y-d-m", $timestamp);
    }
}