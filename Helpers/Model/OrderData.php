<?php


namespace Model;


use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderData
 * @package Model
 * @mixin \EloquentTypeHinting
 * @property int order_id
 * @property string kwork_desc
 * @property string kwork_inst
 * @property string kwork_work
 * @property string kwork_link_type
 * @property int kwork_cart_position
 * @property int kwork_cart_count
 * @property int kwork_category
 * @property float kwork_price
 * @property float kwork_ctp
 * @property int kwork_volume Числовой объем кворка на момент заказа
 * @property float volume Заказанный числовой объем кворка
 * @property int volume_type_id Числовой объем кворка на момент заказа
 * @property float custom_volume Объём в выбранном покупателем типе
 * @property int custom_volume_type_id Тип объёма, который выбран покупателем для заказа
 * @property Category category
 */
class OrderData extends Model {
	const TABLE_NAME = 'orders_data';

	const FIELD_ORDER_ID = "order_id";
	const FIELD_KWORK_DESC = "kwork_desc";
	const FIELD_KWORK_INST = "kwork_inst";
	const FIELD_KWORK_WORK = "kwork_work";
	const FIELD_KWORK_LINK_TYPE = "kwork_link_type";
	const FIELD_KWORK_CART_POSITION = "kwork_cart_position";
	const FIELD_KWORK_CART_COUNT = "kwork_cart_count";
	const FIELD_KWORK_CATEGORY = "kwork_category";
	const FIELD_KWORK_PRICE = "kwork_price";
	const FIELD_KWORK_CTP = "kwork_ctp";
	const FIELD_ = "kwork_volume";
	const FIELD_VOLUME = "volume";
	const FIELD_VOLUME_TYPE_ID = "volume_type_id";

	/**
	 * Объём в выбранном покупателем типе
	 */
	const FIELD_CUSTOM_VOLUME = "custom_volume";

	/**
	 * Тип объёма, который выбран покупателем для заказа
	 */
	const FIELD_CUSTOM_VOLUME_TYPE_ID = "custom_volume_type_id";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ORDER_ID;
	public $timestamps = false;


	public function order() {
		return $this->belongsTo(Order::class, self::FIELD_ORDER_ID, Order::FIELD_OID);
	}

	public function category() {
		return $this->hasOne(Category::class, \CategoryManager::F_CATEGORY_ID, self::FIELD_KWORK_CATEGORY);
	}
}