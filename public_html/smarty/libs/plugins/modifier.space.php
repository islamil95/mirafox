<?php
/**
* рисует пробелы в строке
*/
function smarty_modifier_space($string, $digit)
{
	if(!$string && !$digit)
		return $string;
	
	$string = strrev($string);
	$string = str_split($string, $digit);
	$string = implode(" ", $string);
	$string = strrev($string);
	
	return $string;
}
?>