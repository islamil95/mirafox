<?php
namespace User;
class UserDataModel {
	/**
	 * id пользователя
	 */
	const FIELD_USER_ID = 'user_id';
	/**
	 * дата первого подтверждения покупателем заказа
	 */
	const FIELD_PAYER_FIRST_DATE_DONE = 'payer_first_date_done';
	/**
	 * список 4-х наиболее популярных категорий кворков
	 */
	const FIELD_CATEGORIES = 'categories';
	/**
	 * был ли изменен логин
	 */
	const FIELD_IS_LOGIN_CHANGE = 'is_login_change';
	/**
	 * тип указанный при регистрации (покупатель или продавец)
	 */
	const FIELD_REG_TYPE = 'reg_type';
	/**
	 * сумма потраченных на заказы денег
	 */
	const FIELD_USED = 'used';
	/**
	 * количество выставленных счетов для оплаты по безналу
	 */
	const FIELD_BILL_COUNT = 'bill_count';
	/**
	 * токен для виджета, по нему виджет определяет пользователя (чтобы не указывать user_id)
	 */
	const FIELD_WIDGET_TOKEN = 'widget_token';
	/**
	 * среднее время за которое пользователь выполняет свои кворки (без учета исключенных из рейтинга заказов)
	 */
	const FIELD_AVG_WORK_TIME = 'avgWorkTime';
	/**
	 * количество созданных пользоватлем кворков
	 */
	const FIELD_KWORK_COUNT = 'kwork_count';
	/**
	 * Дата последней смены пароля
	 */
	const FIELD_PASSWORD_CHANGE_DATE = 'password_change_date';
	/**
	 * Дата последней смены логина
	 */
	const FIELD_LOGIN_CHANGE_DATE = 'login_change_date';
	/**
	 * Дата последней смены email
	 */
	const FIELD_EMAIL_CHANGE_DATE = 'email_change_date';
	/**
	 * Дата последней смены реквизитов QIWI
	 */
	const FIELD_QIWI_CHANGE_DATE = 'qiwi_change_date';
	/**
	 * Дата последней смены реквизитов WebMoney
	 */
	const FIELD_WEBMONEY_CHANGE_DATE = 'webmoney_change_date';
	/**
	 * Дата последней смены реквизитов Карт
	 */
	const FIELD_SOLARCARD_CHANGE_DATE = 'solarcard_change_date';
	/**
	 * дата отправки последнего письма о новых запросах
	 */
	const FIELD_PROJECT_LETTER_DATE = 'project_letter_date';
	/**
	 * Был ли изменен раннее добавленный кошелек. Если кошелёк просто добавляется то 0
	 */
	const FIELD_QIWI_IS_ADDED_CHANGED = 'qiwi_is_added_changed';
	const FIELD_WEBMONEY_IS_ADDED_CHANGED = 'webmoney_is_added_changed';
	const FIELD_CARD_IS_ADDED_CHANGED = 'card_is_added_changed';
	const FIELD_SOLARCARD_IS_ADDED_CHANGED = 'solarcard_is_added_changed';
	/**
	 * была ли оформлена подписка на возвратную рассылку
	 */
	const FIELD_WAS_SUBSCRIBED = 'was_subscribed_comeback';
	/**
	 * было ли отправлено пользователю письмо что он стал Продвинутым
	 */
	const FIELD_BECAME_ADVANCED = 'became_advanced';
	/**
	 * количество успешно сданных заказов (в процентах)
	 */
	const FIELD_ORDER_DONE_PERCENT = 'order_done_persent';
	/**
	 * количество успешно сданных вовремя заказов (в процентах)
	 */
	const FIELD_ORDER_DONE_IN_TIME_PERCENT = 'order_done_intime_persent';
	/**
	 * количество успешно сданных повторных заказов (в процентах)
	 */
	const FIELD_DONE_REPEAT_PERCENT = 'order_done_repeat_persent';
	/**
	 * счетчик версии аватара пользователя (чтобы при заливке нового аватара задать ему следующее имя)
	 */
	const FIELD_AVATAR_LAST_NUMBER = 'avatar_last_number';
	/**
	 * Для админов и модераторов: показывать статистику в интерфейсе
	 */
	const FIELD_SHOW_STAT = 'show_stat';
	/**
	 * Отключить получение промежуточных отчетов в настройках
	 */
	const FIELD_DISABLE_REPORT_NOTIFICATION = 'disable_report_notification';

	/**
	 * Процент неотвеченных важных сообщений из последних 30 важных сообщений
	 */
	const FIELD_WARNING_MESSAGES_IGNORED_PERCENT = "warning_messages_ignored_percent";

	/**
	 * Уровень покупателя
	 */
	const FIELD_PAYER_LEVEL = "payer_level";

	/**
	 * Количество заказов по которым подсчитан рейтинг качества
	 */
	const FIELD_SERVICE_ORDERS_TOTAL = "service_orders_total";

    /**
     * Очередь заказов для продавца
     */
    const FIELD_QUEUE_ORDERS_COUNT = "queue_orders_count";

	const DISABLE_REPORT_NOTIFICATION = 1;
	const ENABLE_REPORT_NOTIFICATION = 0;
}