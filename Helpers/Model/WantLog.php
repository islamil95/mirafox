<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;


/**
 * Логирование изменения статуса у запросов
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int id - ID Записи
 * @property int user_id - ID пользователя
 * @property int want_id - ID запроса
 * @property int want_moder_id - ID записи в таблице модерации
 * @property string status - Статус запроса
 * @property string moder_type - Тип модерации запроса
 * @property string date_create - Дата создания записи

 */
class WantLog extends Model {

	/**
	 * Имя таблицы
	 */
	const TABLE_NAME = "want_log";

	/**
	 * ID записи
	 */
	const F_ID = "id";

	/**
	 * ID пользователя
	 */
	const F_USER_ID = "user_id";

	/**
	 * ID запроса
	 */
	const F_WANT_ID = "want_id";

	/**
	 * ID записи в таблице модерации
	 */
	const F_WANT_MODER_ID = "want_moder_id";

	/**
	 * Статус запроса
	 */
	const F_STATUS = "status";

	/**
	 * Тип модерации запроса
	 */
	const F_MODER_TYPE = "moder_type";

	/**
	 * Дата создания записи
	 */
	const F_DATE_CREATE = "date_create";


	/**
	 * Имя таблицы для модели
	 */
	protected $table = self::TABLE_NAME;

	/**
	 * Первичный ключ
	 */
	protected $primaryKey = self::F_ID;

	/**
	 * Отключаем встроенную обработку created_at, updated_at
	 */
	public $timestamps = false;

	/**
	 * Тип "Встал на модерацию"
	 */
	const MODER_TYPE_STAY_ON_PREMODER = "stay_on_premoder";

	/**
	 * Тип "Встал на проверку автомодерации"
	 */
	const MODER_TYPE_STAY_ON_AUTOMODER = "stay_on_automoder";

	/**
	 * Тип "Встал на постмодерацию"
	 */
	const MODER_TYPE_STAY_ON_POSTMODER = "stay_on_postmoder";

	/**
	 * Тип "Встал на перемодерацию"
	 */
	const MODER_TYPE_STAY_ON_REMODER = "stay_on_remoder";
}