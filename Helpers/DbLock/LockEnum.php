<?php

namespace DbLock;

class LockEnum {
	/**
	 * Блокировка вывода средств для пользователя
	 */
	const WITHDRAW_USER = 'withdraw_{id}';

	/**
	 * Создание пользователем заказа
	 */
	const CREATE_ORDER = 'create_order_{id}';

	/**
	 * Генерация таблиц групп кворков для сортировки рекомендуемые
	 */
	const GENERATE_GROUPS_DATA = "generatingGroupsData";

	/**
	 * Принятие пользователем заказа
	 */
	const APPROVE_ORDER = 'approve_order_{id}';

	/**
	 * Сохранение отчёта в заказе
	 */
	const SAVE_ORDER_REPORT = "save_order_report_{id}";

	/**
	 * Блокировка действий по заказу
	 */
	const ORDER = 'order_{id}';

	/**
	 * Блокировка при списании средств пользователя
	 */
	const REFILL_USER = "refill_user_{id}";

	/**
	 * Подставить ID в параетр
	 * @param string $name константна из LockEnum
	 * @param integer $id Id для замены
	 * @return string
	 */
	public static function getWithId($name, $id) {
		$replace = [
			'{id}' => $id
		];
		return strtr($name, $replace);
	}

}