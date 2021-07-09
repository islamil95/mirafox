<?php
/**
* возвращает число и слово в нужном падеже, в зависимости от числа 
* пример вызова: {declension count=$count form1=ответ form2=ответа form5=ответов}
*/
function smarty_function_declension($params)
{
	$count = $params["count"] ?? 0;
	$form1 = $params["form1"] ?? '';
	$form2 = $params["form2"] ?? '';
	$form5 = $params["form5"] ?? '';
	$lang = $params["lang"] ?? null;

	return declension($count, [$form1, $form2, $form5], $lang);
}
?>