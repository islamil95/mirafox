<?php


namespace Model;


use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderNames
 * @package Model
 * @mixin \EloquentTypeHinting
 * @property int id идентификатор
 * @property int order_id ид заказа
 * @property int user_id ид пользователя
 * @property string order_name новое название
 * @property Order order
 */
class OrderNames extends Model {
	const TABLE_NAME = "order_names";
	const FIELD_ID = "id";
	const FIELD_ORDER_ID = "order_id";
	const FIELD_USER_ID = "user_id";
	const FIELD_ORDER_NAME = "order_name";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	public function order() {
		return $this->belongsTo(Order::class, self::FIELD_ORDER_ID, Order::FIELD_OID);
	}
}