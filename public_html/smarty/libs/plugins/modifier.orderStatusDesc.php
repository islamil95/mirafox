<?php
/**
* Описание статуса заказа
*/
function smarty_modifier_orderStatusDesc($trackType, $user_type)
{
	switch($trackType)
	{
		case "worker_inprogress_cancel":
		case "payer_inprogress_cancel_confirm":
			return $user_type == "worker" ? Translations::t("Вы отменили заказ") : Translations::t("Заказ отменен продавцом");

		case "payer_inprogress_cancel":
		case "worker_inprogress_cancel_confirm":
			return $user_type == "worker" ? Translations::t("Заказ отменен покупателем") : Translations::t("Вы отменили заказ");

		case "admin_check_cancel":
		case "admin_inprogress_cancel":
		case "admin_arbitrage_cancel":
			return $user_type == "worker" ? Translations::t("Заказ отменен модератором") : Translations::t("Заказ отменен модератором");

		case "cron_payer_inprogress_cancel":
			return $user_type == "worker" ? Translations::t("Заказ отменен автоматически, т.к. покупатель запросил отмену, а вы не отказались") : Translations::t("Заказ отменен автоматически, т.к. вы запросили отмену, а продавец не отказался");
		case "cron_worker_inprogress_cancel":
			return $user_type == "worker" ? Translations::t("Заказ отменен автоматически, т.к. вы запросили отмену, а покупатель не отказался") : Translations::t("Заказ отменен автоматически, т.к. продавец запросил отмену, а вы не отказались");
		case "cron_restarted_inprogress_cancel":
		case "cron_inprogress_cancel":
			return $user_type == "worker" ? Translations::t("Заказ отменен автоматически, т.к. вы не приступили к заказу") : Translations::t("Заказ отменен автоматически, т.к. продавец не приступил к заказу");
        case "cron_inprogress_inwork_cancel":
            return Translations::t('Сдача заказа просрочена. Заказ отменен автоматически');
		default:
			return "";
	}
}