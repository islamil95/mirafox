<?php

function smarty_modifier_t() {
	$funcParams = func_get_args();
	return call_user_func_array('Translations::t', $funcParams);
}
