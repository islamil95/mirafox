<?php
/**
* вставляет темплат в контрол. темплат не кэшируется
*/
function smarty_function_control($params)
{
	global $smarty;
	
	extract($params);
	
	foreach($params as $key => $value)
		$smarty->assign($key, ${$key});
	
	$smarty->display("$name.tpl");

}
?>