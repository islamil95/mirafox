<?php
/**
* выводит число в строковой записи
*/
function smarty_modifier_sumAsString($sum)
{
	if(!$sum)
		return "";

	return number2string($sum);
}