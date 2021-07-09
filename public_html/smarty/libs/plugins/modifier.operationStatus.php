<?php
/**
* статусы операций
*/
function smarty_modifier_operationStatus($status)
{
	if(!$status)
		return "";
	
	switch($status)
	{
		case "new":
			return Translations::t("В процессе");
		case "inprogress":
			return Translations::t("В процессе");
		case "done":
			return Translations::t("Выполнено");
		case "cancel":
			return Translations::t("Выполнено, но отменено другой операцией");
	}
	
	return "";
}
?>