<?php


namespace Model;
use Illuminate\Database\Eloquent\Model;


/**
 * Просмотр запроса на услуги
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int id Идентификатор
 * @property int want_id Идентификатор запроса на услуги
 * @property int user_id Идентификатор пользователя
 * @property string date_create Дата создания
 */
class WantView extends Model {

	const TABLE_NAME = "want_view";

	/**
	 * Идентификатор записи
	 */
	const FIELD_ID = "id";

	/**
	 * Идентификатор запроса на услуги
	 */
	const FIELD_WANT_ID = "want_id";

	/**
	 * Идентификатор пользователя просмотревшего запрос
	 */
	const FIELD_USER_ID = "user_id";

	/**
	 * Дата создания просмотра
	 */
	const FIELD_DATE_CREATE = "date_create";

	protected $table = self::TABLE_NAME;

	protected $primaryKey = self::FIELD_ID;

	public $timestamps = false;

	/**
	 * Получение дат просмотров запросов пользователем
	 *
	 * @param array $wantIds Массив идентификаторов запросов
	 * @param int $userId Идентификатор пользователя просмотры которого нужно получить
	 *
	 * @return array [want_id => date_create]
	 */
	public static function getUserWantsViewsDates(array $wantIds, int $userId) {
		if (empty($wantIds) || empty($userId)) {
			return [];
		}
		return WantView::whereIn(WantView::FIELD_WANT_ID, $wantIds)
			->where(WantView::FIELD_USER_ID, $userId)
			->pluck(WantView::FIELD_DATE_CREATE, WantView::FIELD_WANT_ID)
			->toArray();
	}

	/**
	 * Id просмотренных запросов
	 *
	 * @param $wantId
	 * @param $userId
	 * @return array|bool
	 */
	public static function getUserWantViews($wantIds, $userId) {
		if (empty($wantIds) || empty($userId)) {
			return [];
		}
		$wantIds = WantView::select([WantView::FIELD_WANT_ID])
			->where(WantView::FIELD_USER_ID, "=", $userId)
			->whereIn(WantView::FIELD_WANT_ID, $wantIds)
			->get()
			->keyBy(WantView::FIELD_WANT_ID)
			->toArray();
		return array_keys($wantIds);
	}

	/**
	 * Добавление просмотра пользователем
	 *
	 * @param $wantIds
	 * @param int|null $userId
	 * @return bool
	 */
	public static function addUserWantView($wantIds, $userId) {
		$values = [];
		foreach ($wantIds as $id) {
			$v = [WantView::FIELD_WANT_ID => $id];
			if (!empty($userId)) {
				$v[WantView::FIELD_USER_ID] = $userId;
			}
			$values[] = $v;
		}
		return WantView::insert($values);
	}

}