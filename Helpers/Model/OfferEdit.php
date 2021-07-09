<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Хранит последнее время изменения предложений
 * Записи старше {OfferEditManager::STORAGE_PERIOD} удаляются
 *
 * http://wikicode.kwork.ru/offer_edit/
 *
 * @property int $offer_id - ID предложения
 * @property string $last_edit_time - Время последнего изменения предложения
 *
 * @mixin \EloquentTypeHinting
 */
class OfferEdit extends Model {
	/**
	 * Имя таблицы
	 */
	const TABLE_NAME = "offer_edit";

	/**
	 * ID предложения
	 */
	const FIELD_OFFER_ID = "offer_id";

	/**
	 * Время последнего изменения предложения
	 */
	const FIELD_LAST_EDIT_TIME = "last_edit_time";

	/**
	 * Первичный ключ
	 */
	protected $primaryKey = self::FIELD_OFFER_ID;

	/**
	 * Имя таблицы для модели
	 */
	protected $table = self::TABLE_NAME;

	/**
	 * Отключаем встроенную обработку created_at, updated_at
	 */
	public $timestamps = false;
}