<?php

function smarty_modifier_tn() {
	$funcParams = func_get_args();
	return call_user_func_array('Translations::tn', $funcParams);
}
