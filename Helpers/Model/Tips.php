<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Класс для хранения информации о перечисленных бонусах продавцу, за выполненный кворк от покупателя.
 *
 * @mixin \EloquentTypeHinting
 * @property int $track_id - ID track-записи о переводе бонуса
 * @property float $amount - сумма бонуса
 * @property int order_id - ID заказа
 * @property float crt Сумма бонуса с учётом комиссии
 * @property float currency_rate Курс на момент оплаты
 * @property string comment Комментарий для исполнителя
 * @property string date Время отправки бонуса
 */
class Tips extends Model {

	/**
	 * Название таблицы
	 */
	const TABLE_NAME = "tips";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::F_TRACK_ID;
	public $timestamps = false;

	/**
	 * ID track-записи о переводе бонуса
	 */
	const F_TRACK_ID = "track_id";

	/**
	 * ID заказа
	 */
	const F_ORDER_ID ="order_id";

	/**
	 * Сумма бонуса
	 */
	const F_AMOUNT ="amount";

	/**
	 * Сумма бонуса с учётом комиссии
	 */
	const F_CRT ="crt";

	/**
	 * Комментарий для исполнителя
	 */
	const F_COMMENT ="comment";

	/**
	 * Курс на момент оплаты
	 */
	const FIELD_CURRENCY_RATE ="currency_rate";

	/**
	 * Время отправки бонуса
	 */
	const F_DATE ="date";

}