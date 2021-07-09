<?php

/**
 * преобразовывает строку для лендигна
 */
function smarty_modifier_land_seo_string($string) {
    return Helper::getLandSeoString($string);
}