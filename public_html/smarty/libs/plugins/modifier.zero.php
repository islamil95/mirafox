<?php
/**
* рисует нули в копейках если нужно
*/
function smarty_modifier_zero($price, $digit = 2, $space = true) {
	return Helper::zero($price, $digit, $space);
}