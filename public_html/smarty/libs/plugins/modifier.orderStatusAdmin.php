<?php
/**
* статус заказа
*/
function smarty_modifier_orderStatusAdmin($status)
{
	switch($status)
	{
		case 0:
			return "Заявка создана";
		case 1:
			return "В работе";
		case 2:
			return "Арбитраж";
		case 3:
			return "Отменен";
		case 4:
			return "На проверке";
		case 5:
			return "Выполнен";
		case 6:
			return "Неоплачен";
		case 8:
			return "Заказ создан";
		default:
			return "";
	}
}
