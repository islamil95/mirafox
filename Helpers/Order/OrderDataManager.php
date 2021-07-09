<?php

namespace Order;

use Core\DB\DB;

class OrderDataManager
{
    const TABLE_NAME = 'orders_data';
    const F_ORDER_ID = "order_id";
    const F_KWORK_DESC = "kwork_desc";
    const F_KWORK_INST = "kwork_inst";
    const F_KWORK_WORK = "kwork_work";
    const F_KWORK_LINK_TYPE = "kwork_link_type";
    const F_KWORK_CART_POSITION = "kwork_cart_position";
    const F_KWORK_CART_COUNT = "kwork_cart_count";
    const F_KWORK_CATEGORY = "kwork_category";
    const F_KWORK_PRICE = "kwork_price";
    const F_KWORK_CTP = "kwork_ctp";

	/**
	 * Заказанный объем кворка
	 */
    const FIELD_VOLUME = "volume";

	/**
	 * Идентификатор типа объема кворка на момент заказа
	 */
    const FIELD_VOLUME_TYPE_ID = "volume_type_id";

	/**
	 * Числовой объем кворка на момент заказа
	 */
    const FIELD_KWORK_VOLUME = "kwork_volume";

    public static function add($data)
    {
        global $conn;

	    if (array_key_exists(self::FIELD_VOLUME_TYPE_ID, $data) && empty($data[self::FIELD_VOLUME_TYPE_ID])) {
		    unset($data[self::FIELD_VOLUME_TYPE_ID]); // foreign key - нельзя вставлять пустую строку
	    }

        $fields = array_map('mres', array_keys($data));
        $values = array_map('mres', array_values($data));

        $sql = "INSERT INTO " . self::TABLE_NAME . " 
                    (`" . implode('`,`', $fields) . "`) 
                VALUES 
                    ('" . implode("','", $values) . "')";

        return $conn->Execute($sql);
    }

    public static function get($id, $fields)
    {
        global $conn;
        $fields = array_map('mres', $fields);

        $sql = "SELECT 
                    " . implode(', ', $fields) . "
                FROM
                    " . self::TABLE_NAME . "
                WHERE order_id = " . intval($id);

        return $conn->getEntity($sql);
    }
}