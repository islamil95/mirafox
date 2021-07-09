<?php


namespace Model;


use Illuminate\Database\Eloquent\Model;

/**
 * Class RatingComment
 * @package Model
 * @property int id
 * @property int user_id
 * @property string message
 * @property int review_id
 * @property string status
 * @property int time_added
 * @property User user
 * @property Rating review
 * @property int unread Прочитал ли ответ на отзыв покупатель
 * @mixin \EloquentTypeHinting
 */
class RatingComment extends Model {

	const TABLE_NAME = "rating_comment";
	const FIELD_ID = "id";
	const FIELD_USER_ID = "user_id";
	const FIELD_MESSAGE = "message";
	const FIELD_REVIEW_ID = "review_id";
	const FIELD_STATUS = "status";
	const FIELD_TIME_ADDED = "time_added";
	const FIELD_UNREAD = "unread";

	const STATUS_NEW = "new";
	const STATUS_ACTIVE = "active";
	const STATUS_REJECT = "reject";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	public function getMessageAttribute($value) {
		return replace_full_urls($value);
	}

	public function user() {
		return $this->hasOne(User::class, User::FIELD_USERID, self::FIELD_USER_ID);
	}

	public function review() {
		return $this->belongsTo(Rating::class, self::FIELD_REVIEW_ID, Rating::FIELD_ID);
	}

	/**
	 * Может ли текущий пользователь видеть ответ на отзыв
	 *
	 * @return bool
	 */
	public function showToCurrentUser() {
		return $this->status == self::STATUS_ACTIVE || $this->user_id == \UserManager::getCurrentUserId();
	}
}