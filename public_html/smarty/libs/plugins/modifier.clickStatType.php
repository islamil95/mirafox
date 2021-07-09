<?php

/**
 * тип click_stat
 */
function smarty_modifier_clickStatType($type) {
	switch ($type) {
		case "kwork_get_more":
			return Translations::t("Запрос дополнительных отзывов на странице кворка");
			
		case "user_get_more":
			return Translations::t("Запрос дополнительных отзывов на странице профиля");

		case "kwork_get_negative":
			return Translations::t("Просмотр списка отрицательных отзывов");
			
		case "user_get_negative":
			return Translations::t("Просмотр списка отрицательных отзывов на странице профиля");
			
		default:
			return "";
	}
}