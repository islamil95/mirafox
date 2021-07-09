<?php
/**
* возвращает сумму для продавца
*/
function smarty_modifier_crt($price)
{
	return $price - intval(KworkManager::getCtp($price));
}