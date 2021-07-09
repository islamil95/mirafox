<?php
/**
* статус заказа
*/
function smarty_modifier_orderStatus($status)
{
	switch($status)
	{
		case 0:
			return Translations::t("Заявка создана");
		case 1:
			return Translations::t("В работе");
		case 2:
			return Translations::t("Арбитраж");
		case 3:
			return Translations::t("Отменен");
		case 4:
			return Translations::t("На проверке");
		case 5:
			return Translations::t("Выполнен");
		case 6:
			return Translations::t("Неоплачен");
		case 8:
			return Translations::t("Заказ создан");
		default:
			return "";
	}
}
