<?php

function smarty_modifier_translateByLang() {
	$funcParams = func_get_args();

	// Меняем местами первый и второй элементы массива, чтобы в Smarty все выглядело понятнее
	$temp = $funcParams[0];
	$funcParams[0] = $funcParams[1];
	$funcParams[1] = $temp;

	return call_user_func_array('Translations::translateByLang', $funcParams);
}