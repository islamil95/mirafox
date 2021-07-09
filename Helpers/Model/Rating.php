<?php


namespace Model;


use Illuminate\Database\Eloquent\Model;

/**
 * Отзывы
 *
 * Class Rating
 * @package Model
 * @property int RID id записи
 * @property int USERID id продавца
 * @property int PID id кворка
 * @property int OID id заказа
 * @property int RATER id покупателя
 * @property int time_added дата создания
 * @property bool good положительный ли
 * @property bool bad отрицательный ли
 * @property string comment текст отзыва
 * @property string auto_mode тип отзыва
 * @property int time_vote_changed
 * @property int unread Прочитал ли отзыв продавец
 *
 * Связанные модели
 * @property-read \Model\Order $order Модель связанного кворка
 * @property-read RatingComment $answer Ответ на отзыв
 *
 * @mixin \EloquentTypeHinting
 */
class Rating extends Model {

	const TABLE_REVIEW = "ratings";
	const FIELD_ID = "RID";
	const FIELD_USERID = "USERID";
	const FIELD_KWORK_ID = "PID";
	const FIELD_ORDER_ID = "OID";
	const FIELD_RATER_ID = "RATER";
	const FIELD_TIME_ADDED = "time_added";
	const FIELD_GOOD = "good";
	const FIELD_BAD = "bad";
	const FIELD_COMMENT = "comment";
	const FIELD_AUTO_MODE = "auto_mode";
	const FIELD_TIME_VOTE_CHANGED = "time_vote_changed";
	const FIELD_UNREAD = "unread";

	/**
	 * кроном при просрочке заказа
	 */
	const AUTO_REVIEW_TYPE_INWORK_TIME_OVER = "inwork_time_over";

	/**
	 * покупателем при просрочке заказа
	 */
	const AUTO_REVIEW_TYPE_TIME_OVER = "time_over";

	/**
	 * кроном или продавцом по запросу покупателя на отмену по причине что заказ выполнен некорректно
	 */
	const AUTO_REVIEW_TYPE_INCORRECT_EXECUTION = "incorrect_execute";

	protected $table = self::TABLE_REVIEW;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	/**
	 * HTML комментария для вывода на сайте
	 *
	 * @return string
	 */
	public function commentHtml():string {
		return replace_full_urls($this->comment);
	}

	/**
	 * Продавец
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user() {
		return $this->hasOne(User::class, User::FIELD_USERID, self::FIELD_USERID);
	}

	/**
	 * Кворк
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function kwork() {
		return $this->hasOne(Kwork::class, Kwork::FIELD_PID, self::FIELD_KWORK_ID);
	}

	/**
	 * Заказ
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function order() {
		return $this->hasOne(Order::class, Order::FIELD_OID, self::FIELD_ORDER_ID);
	}

	/**
	 * Покупатель
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function rater() {
		return $this->hasOne(User::class, User::FIELD_USERID, self::FIELD_RATER_ID);
	}

	public function answer() {
		return $this->hasOne(RatingComment::class,RatingComment::FIELD_REVIEW_ID, self::FIELD_ID);
	}

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	public static function boot() {
		parent::boot();

		// При добавлении отзыва
		self::created(function (Rating $model) {
			// Нужно добавить соответствующую запись в таблицу ratings_for_display
			// или обновить её, если у заказа уже есть портфолио
			if ($model->order->portfolio) {
				$ratingForDisplay = RatingForDisplay::query()
					->where(RatingForDisplay::FIELD_PORTFOLIO_ID, $model->order->portfolio->id)
					->first();

				if (!$ratingForDisplay) {
					throw new \Exception("В таблице ratings_for_display отсутствует запись для портфолио с ID = "
						. $model->order->portfolio->id);
				}

			} else {
				$ratingForDisplay = new RatingForDisplay();
				$ratingForDisplay->user_id = $model->USERID;
				$ratingForDisplay->portfolio_id = null;
				$ratingForDisplay->currency_id = $model->order->currency_id;
			}

			$ratingForDisplay->rating_id = $model->RID;
			$ratingForDisplay->is_good = $model->good;
			$ratingForDisplay->date_created = date("Y-m-d H:i:s", $model->time_added);
			$ratingForDisplay->save();
		});

		// При обновлении отзыва
		self::updated(function (Rating $model) {
			// Если изменился "знак" отзыва (положительный/отрицательный)
			if ($model->good !== $model->getOriginal(self::FIELD_GOOD)) {
				// Нужно обновить соответствующую запись в таблице ratings_for_display
				/** @var RatingForDisplay $ratingForDisplay */
				$ratingForDisplay = RatingForDisplay::query()
					->where(RatingForDisplay::FIELD_RATING_ID, $model->RID)
					->first();
				if ($ratingForDisplay) {
					$ratingForDisplay->is_good = $model->good;
					$ratingForDisplay->save();
				}
			}
		});

		// При удалении отзыва
		self::deleted(function (Rating $model) {
			// Нужно удалить соответствующую запись из таблицы ratings_for_display
			// или обновить её, если у заказа есть портфолио
			/** @var RatingForDisplay $ratingForDisplay */
			$ratingForDisplay = RatingForDisplay::query()
				->where(RatingForDisplay::FIELD_RATING_ID, $model->{ self::FIELD_ID })
				->first();
			if ($ratingForDisplay) {
				if (!is_null($ratingForDisplay->portfolio_id)) {
					$ratingForDisplay->rating_id = null;
					$ratingForDisplay->is_good = null;
					$ratingForDisplay->save();
				} else {
					$ratingForDisplay->delete();
				}
			}
		});
	}

	/**
	 * Сфомировать массив, который надо подавать в reviews_answer_new.tpl
	 * @return array
	 */
	public function toRenderReviewsAnswerNew() {
		$renderReview = $this->toArray();
		if ($this->answer) {
			$renderReview["answer"] = $this->answer->toArray();
			$renderReview["answer"]["worker_id"] = $this->answer->user->USERID;
			$renderReview["answer"]["worker_username"] = $this->answer->user->username;
			$renderReview["answer"]["worker_profilepicture"] = $this->answer->user->profilepicture;
			$renderReview["answer"]["message"] = replace_full_urls($this->answer->message);
			$renderReview["answer"]["canEdit"] = \RatingManager::canEditAnswer($renderReview["answer"]);
		}
		return $renderReview;
	}
}
