<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Отзывы для отображения на странице профиля пользователя
 *
 * Class RatingForDisplay
 * @package Model
 * @property int id Id записи
 * @property int user_id Id пользователя
 * @property int rating_id Id отзыва
 * @property int portfolio_id Id портфолио
 * @property bool is_good Является ли отзыв положительным
 * @property int currency_id Код валюты
 * @property string date_created Дата создания
 *
 * Связанные модели
 *
 * @property-read \Model\Rating $rating Модель связанного отзыва
 *
 * @mixin \EloquentTypeHinting
 */
class RatingForDisplay extends Model {
	/**
	 * Таблица
	 */
	const TABLE_NAME = "ratings_for_display";

	/**
	 * Id записи
	 */
	const FIELD_ID = "id";

	/**
	 * Id пользователя
	 */
	const FIELD_USER_ID = "user_id";

	/**
	 * Id отзыва
	 */
	const FIELD_RATING_ID = "rating_id";

	/**
	 * Id портфолио
	 */
	const FIELD_PORTFOLIO_ID = "portfolio_id";

	/**
	 * Является ли отзыв положительным
	 */
	const FIELD_IS_GOOD = "is_good";

	/**
	 * Код валюты
	 */
	const FIELD_CURRENCY_ID = "currency_id";

	/**
	 * Дата создания
	 */
	const FIELD_DATE_CREATED = "date_created";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	/**
	 * Отзыв
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function rating() {
		return $this->hasOne(Rating::class, Rating::FIELD_ID, self::FIELD_RATING_ID);
	}
}
