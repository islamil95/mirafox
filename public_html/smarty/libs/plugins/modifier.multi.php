<?php
/**
* умножает параметры
*/
function smarty_modifier_multi($number1, $number2)
{
	if(!$number1 || !$number2)
		return 0;
	
	return $number1 * $number2;
}
?>