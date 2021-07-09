<?php

/**
 * преобразовывает строку для лендигна
 */
function smarty_modifier_userType($string) {
    switch ($string){
        case 'worker':
            return Translations::t('Продавец');
        case 'payer':
            return Translations::t('Покупатель');
        case 'guest':
            return Translations::t('Гость');
        default:
            return ''; 
    }
}