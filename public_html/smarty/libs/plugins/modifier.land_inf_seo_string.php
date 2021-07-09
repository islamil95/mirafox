<?php

/**
 * преобразовывает строку для лендигна
 */
function smarty_modifier_land_inf_seo_string($string) {
    return Helper::getLandInfSeoString($string);
}