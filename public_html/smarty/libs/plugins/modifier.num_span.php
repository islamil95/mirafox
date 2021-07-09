<?php
/**
 * оборачивает числа в span
 */
function smarty_modifier_num_span($string, $type)
{
	if($type == 1)
		return preg_replace('/\d+/', "<span class='f34' style='line-height:30px;'>$0</span>", $string);
	
	if($type == 2)
		return preg_replace('/\d+/', "<span class='f26'>$0</span>", $string);
	
	return $string;
}