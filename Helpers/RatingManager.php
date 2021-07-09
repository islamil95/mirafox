<?php

use Core\DB\DB;
use Helpers\Rating\AutoRating\GetAutoRatingInfoService;
use Model\CurrencyModel;
use Model\Kwork;
use Model\Order;
use Model\Rating;
use Model\RatingComment;
use Model\RatingForDisplay;
use \Track\Type;

class RatingManager {

	const REVIEW_ENTITY_USER = 'user';
	const REVIEW_ENTITY_KWORK = 'kwork';
	const CAN_ADD_REVIEW_ALL = 'all';
	const CAN_ADD_REVIEW_BAD = 'bad';
	const AUTO_MODE_INWORK_TIME_OVER = "inwork_time_over";
	const AUTO_MODE_TIME_OVER = "time_over";
	const AUTO_MODE_INCORRECT_EXECUTE = "incorrect_execute";
	const AUTO_MODE_ARBITRAGE_PAYER = "arbitrage_payer";
	const COMMENT_MAX_SYMBOLS_COUNT = 300;
	const COMMENT_MAX_CHARACTERS_COUNT = 500;
	/**
	 * Таблица с отзывами по заказу
	 * ratings
	 */
	const TABLE_REVIEW = "ratings";

	/**
	 * Поля таблицы
	 * Описание полей: http://wikicode.kwork.ru/ratings/
	 */
	const F_ID = "RID";
	const F_USER_ID = "USERID";
	const F_KWORK_ID = "PID";
	const F_ORDER_ID = "OID";
	const F_PAYER_ID = "RATER";
	const F_TIME_ADDED = "time_added";
	const F_GOOD = "good";
	const F_BAD = "bad";
	const F_COMMENT = "comment";
	const F_AUTO_MODE = "auto_mode";
	const F_TIME_VOTE_CHANGED = "time_vote_changed";
	const F_UNREAD = "unread";

	/**
	 * Таблица с ответами на отзыв
	 * rating_comment
	 */
	const TABLE_REVIEW_ANSVER = "rating_comment";

	// Кол-во дней, в течение которых отрицательный отзыв доступен для редактирования
	// после создания или изменения с положительного на отрицательный
	const EDIT_NEGATIVE_REVIEW_PERIOD_DAYS = 2;
	// Кол-во месяцев, в течение которых продавец может оставить отзыв по заказу,
	// если максимальное время на выполнение кворка < 30 дней
	const CREATE_REVIEW_PERIOD_MONTH_SHORT = 1;
	// Кол-во месяцев, в течение которых продавец может оставить отзыв по заказу,
	// если максимальное время на выполнение кворка >= 30 дней
	const CREATE_REVIEW_PERIOD_MONTH_LONG = 2;
	/**
	 * Кол-во месяцев, в течение которых после выполнения заказа покупатель может голосовать за отзыв
	 */
	const CREATE_RATE_PERIOD_MONTH_LONG = 6;

	/**
	 * Кол-во отзывов объемом более 200 символов, которые требуются для получения плашки Суперкомментатор
	 */
	const BADGE_SUPERCOMMENTATOR_REVIEW_THRESHOLD = 7;

	/**
	 * Создание отзыва для заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $vote Положительный 1, Отрицательный 0
	 * @param string $comment Текст отзыва
	 * @return bool Создан ли отзыв
	 * @throws Exception
	 */
	public static function create($orderId, $vote, $comment): bool {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$comment) {
			return false;
		}

		// заказ
		$order = Order::where(Order::FIELD_OID, $orderId)
			->select([
				Order::FIELD_OID,
				Order::FIELD_PID,
				Order::FIELD_USERID,
				Order::FIELD_STATUS,
				Order::FIELD_DATE_DONE,
				Order::FIELD_DATE_CANCEL,
			])
			->first();

		if (!$order) {
			return false;
		}

		$canAddReview = TrackManager::canAddReview($order->OID);

		if (!$canAddReview) {
			return false;
		}

		// кворк
		$kwork = Kwork::where(Kwork::FIELD_PID, $order->PID)
			->select([
				Kwork::FIELD_PID,
				Kwork::FIELD_USERID,
				Kwork::FIELD_LANG,
			])
			->first();

		// отзыв
		$rating = Rating::where(Rating::FIELD_ORDER_ID, $order->OID)
			->select([
				Rating::FIELD_ID,
				Rating::FIELD_AUTO_MODE,
			])
			->first();

		if ($rating) {
			if ($rating->auto_mode) {
				return self::update($order->OID, $vote, $comment);
			}

				return false;
			}

		$isGood = (bool)$vote;
		$isBad = !$isGood;

		if ($isGood && $canAddReview === self::CAN_ADD_REVIEW_BAD) {
			return false;
		}

		$comment = self::prepareCommentToSave($comment);

		// Если отзыв добавляет модер, то действует от лица покупателя
		if (UserManager::isModer()) {
			$raterId = $order->USERID;
		} else {
			$raterId = $actor->id;
		}

		$rating = new Rating();
		$rating->USERID = $kwork->USERID;
		$rating->PID = $kwork->PID;
		$rating->OID = $order->OID;
		$rating->RATER = $raterId;
		$rating->comment = $comment;
		$rating->time_added = time();
		$rating->good = $isGood;
		$rating->bad = $isBad;
		$rating->time_vote_changed = time();
		$rating->save();
		$rid = $rating->RID;

		KworkManager::setReviewCount($kwork->PID);

		// уведомления
		$data = OrderManager::getOrderData($order->OID);

		if ($isGood) {
			KworkManager::updateDateModify($order->PID);
		}

		return true;
	}

	/**
	 * Создание автоматического отзыва для заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $comment Текст отзыва
	 * @param string $mode Тип автоматического отзыва
	 * @return bool Создан ли отзыв
	 */
	public static function createAuto($orderId, $comment, $mode) {
		// заказ
		{
			$order = App::pdo()->fetch("SELECT OID as 'id', USERID as 'userId', PID as 'kworkId', worker_id as 'workerId', status, date_cancel, date_done, last_track_id FROM orders WHERE OID = :order_id", [
				"order_id" => $orderId
					], PDO::FETCH_OBJ);

			if (!$order)
				return false;
		}

		// отзыв
		{
			$rating = App::pdo()->fetch("SELECT RID as 'id' FROM ratings WHERE OID = :order_id", [
				"order_id" => $order->id
					], PDO::FETCH_OBJ);

			if ($rating)
				return false;
		}

		$rating = new Rating();
		$rating->USERID = $order->workerId;
		$rating->PID = $order->kworkId;
		$rating->OID = $order->id;
		$rating->RATER = App::config("kwork.user_id");
		$rating->comment = (string)$comment;
		$rating->time_added = time();
		$rating->good = 0;
		$rating->bad = 1;
		$rating->time_vote_changed = time();
		$rating->auto_mode = $mode;
		$rating->save();
		$rid = $rating->RID;

		return true;
	}

	/**
	 * Изменение отзыва для заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $vote Положительный 1, Отрицательный 0
	 * @param string $comment Текст отзыва
	 * @return bool Изменен ли отзыв
	 * @throws \PHLAK\Config\Exceptions\InvalidContextException
	 */
	public static function update($orderId, $vote, $comment): bool {
		$actor = UserManager::getCurrentUser();

		if (!$actor || !$comment) {
			return false;
		}

		// заказ
		{
			$order = Order::find($orderId);
			if (!$order)
				return false;

			$updateReviewType = self::getAvailReviewType($order->OID);
			if (!$updateReviewType)
				return false;
		}

		// кворк
		{
			$kwork = $order->kwork;
		}

		if (\UserManager::isModer()) {
			// Если редактирует модер, то действует от лица покупателя
			$raterId = $order->USERID;
		} else {
			$raterId = $actor->id;
		}

		// отзыв
		{
			$rating = Rating::where(Rating::FIELD_ORDER_ID, $order->OID)
				->whereIn(Rating::FIELD_RATER_ID, [$raterId, App::config("kwork.user_id")])
				->first();
			if (!$rating)
				return false;
		}

		$good = $vote == 1 ? 1 : 0;
		$bad = $good == 1 ? 0 : 1;

		if ($good && $updateReviewType == self::CAN_ADD_REVIEW_BAD) {
			return false;
		}

		$comment = self::prepareCommentToSave($comment);
		$orderData = OrderManager::getOrderData($orderId);

		// Сохраним изменения
		$rating->auto_mode = null;
		$rating->RATER = $raterId;
		$rating->comment = $comment;
		$rating->good = $good;
		$rating->bad = $bad;
		$rating->time_vote_changed = time();
		$rating->save();

		KworkManager::setReviewCount($kwork->PID);

		return true;
	}

	/**
	 * Создать ответ на отзыв
	 *
	 * @param int $RID Идентификатор отзыва
	 * @param string $message Текст отзыва
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function createAnswer($RID, $message) {
		$actor = UserManager::getCurrentUser();

		if (!$actor || $RID <= 0 || empty($message)) {
			return false;
		}

		$rating = Rating::find($RID);
		if (!$rating || $rating->USERID != $actor->id) {
			// Если пользователь не продавец из отзыва, то оставить отзыв не разрешаем
			return false;
		}

		if ($rating->answer()->exists()) {
			// Если ответ уже существует, то не даём оставить новый
			return false;
		}

		// если у комментария был положительный рейтинг, то ответ сразу делаем активным
		$answerStatus = $rating->good == 1 ? RatingComment::STATUS_ACTIVE : RatingComment::STATUS_NEW;

		$message = self::prepareCommentToSave($message);

		$ratingComment = new \Model\RatingComment();
		$ratingComment->review_id = (int) $RID;
		$ratingComment->message = $message;
		$ratingComment->time_added = time();
		$ratingComment->status = $answerStatus;
		$ratingComment->user_id = $actor->id;
		$ratingComment->save();

		$answerId = $ratingComment->id;

		if ($answerId && $rating->good == 1) {
			KworkManager::updateDateModify($rating->PID);
		}

		return (boolean) $answerId;
	}

	/**
	 * Редактировать ответ на отзыв
	 *
	 * @param int	$answerId Идентификатор ответа
	 * @param string $message Текст ответа
	 *
	 * @return bool
	 */
	public static function editAnswer($answerId, $message) {
		global $actor;

		if (!$actor || (int) $answerId <= 0 || empty($message)) {
			return false;
		}

		$answer = App::pdo()->fetch("SELECT id, user_id, review_id, time_added FROM rating_comment WHERE id = :answerId", ["answerId" => $answerId], PDO::FETCH_OBJ);

		if (empty($answer)) {
			return false;
		}

		if ($answer->user_id != $actor->id) {
			return false;
		}

		if ($answer->time_added < time() - 2 * Helper::ONE_WEEK) {
			return false;
		}

		$rating = App::pdo()->fetch("SELECT RID as 'id', good FROM ratings WHERE RID = :reviewId", ["reviewId" => $answer->review_id], PDO::FETCH_OBJ);

		$answerStatus = $rating->good == 1 ? "active" : "new";


		$message = self::prepareCommentToSave($message);

		$ratingComment = \Model\RatingComment::find($answerId);
		$ratingComment->message = $message;
		$ratingComment->time_added = time();
		$ratingComment->status = $answerStatus;

		return (boolean) $ratingComment->save();
	}

	/**
	 * Удалить отзыв
	 * @param int $ratingId - идентификатор отзыва
	 * @param bool $forceDelete Принудительное удаление автоотзыва
	 * @return bool
	 * @throws Exception
	 */
	public static function delete($ratingId, $forceDelete = false): bool {
		$rating = Rating::find($ratingId);
		$sql = "SELECT USERID as userId FROM posts WHERE PID = :kworkId";

		$kwork = App::pdo()->fetch($sql, ['kworkId' => $rating->PID]);

		if (!$rating || !$kwork) {
			return false;
		}

		$orderId = $rating->OID;
		$kworkId = $rating->PID;

		if (!$forceDelete) {
			$autoRatingInfo = (new GetAutoRatingInfoService())
				->byOrderId($orderId);

			if ($autoMode = $autoRatingInfo->getAutoMode()) {
				$rating->RATER = App::config("kwork.user_id");
				$rating->auto_mode = $autoMode;
				$rating->comment = $autoRatingInfo->getComment();
				$rating->good = 0;
				$rating->bad = 1;

				$rating->save();

				return false;
			}
		}

		RatingComment::where(RatingComment::FIELD_REVIEW_ID, $rating->RID)->delete();

		$rating->delete();

		KworkManager::setReviewCount($kworkId);
		
		return true;
	}

	/**
	 * Получить общее кол-во отзывов для заданного пользователя на заданных языках
	 *
	 * @param int $userId идентификатор пользователя
	 * @param array|string $langs язык или массив искомых языков
	 * @param array|string $types тип или массив искомых типов ("positive", "negative")
	 * @return int общее кол-во отзывов
	 */
	public static function getRatingCount($userId, $langs = [], $types = []) {
		if (empty($langs)) {
			$langs = [Translations::getLang()];
		}
		if (!is_array($langs)) {
			$langs = [$langs];
		}

		if (empty($types)) {
			$types = ["positive", "negative"];
		}
		if (!is_array($types)) {
			$types = [$types];
		}

		$query = Rating::where(Rating::FIELD_USERID, $userId);

		if (count($types) == 1) {
			if (in_array("positive", $types)) {
				$query->where(Rating::FIELD_GOOD, 1);
			}
			if (in_array("negative", $types)) {
				$query->where(Rating::FIELD_BAD, 1);
			}
		}

		return $query->count();
	}

	/**
	 * Получить отзывы пользователя
	 * @param int $userId - ID пользователя
	 * @param array $options - Массив опций с полями type, offset и limit
	 * @param null $currencies - Список валют
	 * @return false|array
	 */
	public static function getByUserId($userId, $options, $currencies = null) {
		$actor = UserManager::getCurrentUser();

		if (!isset($options["type"]))
			return false;
		if (!in_array($options["type"], ["positive", "negative"]))
			return false;
		$type = $options["type"];

		if (!isset($options["offset"]))
			$offset = 0;
		else
			$offset = intval($options["offset"]);

		if (!isset($options["limit"]))
			$limit = 5;
		else
			$limit = $options["limit"];

		$userId = intval($userId);

		if(!$userId) {
			return false;
		}
		if (!$currencies) {
			$currencies = Translations::isDefaultLang() ? [CurrencyModel::RUB] : [CurrencyModel::USD];
		}

		$reviewCollection = RatingForDisplay::query()
			->with([
				"rating",
				"rating.order",
			])
			->where(RatingForDisplay::FIELD_USER_ID, $userId)
			->where(function ($query) use ($type) {
				$isGood = (int) ($type === "positive");
				$query->where(RatingForDisplay::FIELD_IS_GOOD, $isGood);
				if ($isGood) {
					$query->orWhere(function ($query) {
						$query->whereNull(RatingForDisplay::FIELD_RATING_ID)
							->whereNotNull(RatingForDisplay::FIELD_PORTFOLIO_ID);
					});
				}
			})
			->whereIn(RatingForDisplay::FIELD_CURRENCY_ID, $currencies)
			->orderBy(RatingForDisplay::FIELD_DATE_CREATED, "desc")
			->offset($offset)
			->limit($limit)
			->get();

		$langs = [];
		foreach ($currencies as $currency) {
			$langs[] = Translations::getLangByCurrencyId($currency);
		}
		$total = self::getRatingCount($userId, $langs, $type);

		$reviews = [];

		if ($reviewCollection->isNotEmpty()) {
			$reviewCollection->each(function (RatingForDisplay $item) use (&$reviews) {
				$review = [];

				if (!is_null($item->rating)) {
					$review = array_merge($review, [
						"PID" => $item->rating->order->PID,
						"worker_id" => $item->rating->USERID,
						"comment" => $item->rating->comment,
						"good" => $item->rating->good,
						"bad" => $item->rating->bad,
						"RID" => $item->rating->RID,
						"auto_mode" => $item->rating->auto_mode,
						"time_added" => $item->rating->time_added,
						"hasPaidStages" => $item->rating->order->hasPaidStages(),
					]);

					if (!is_null($item->rating->RATER)) {
						$review["rater_id"] = $item->rating->RATER;
					} else {
						$review["rater_id"] = $item->rating->order->USERID;
					}
				}

				if (!is_null($item->portfolio)) {
					$review = array_merge($review, [
						"PID" => $item->portfolio->order->PID,
						"id" => $item->portfolio->id,
						"order_id" => $item->portfolio->order_id,
						"cover" => $item->portfolio->cover,
						"time_added" => strtotime($item->portfolio->date_create),
						"is_resizing" => $item->portfolio->is_resizing,
					]);

					if (is_null($review["rater_id"])) {
						$review["rater_id"] = $item->portfolio->order->USERID;
					}

					if ($item->portfolio->images->isNotEmpty()) {
						$review["photo"] = $item->portfolio->images->first()->path;
					}

					if ($item->portfolio->videos->isNotEmpty()) {
						$review["video"] = $item->portfolio->videos->first()->url;
					}
				}

				$reviews[] = $review;
			});

			$workerId = $reviews[0]["worker_id"];
			$workerName = UserManager::get($workerId, [UserManager::FIELD_USERNAME])
					->{UserManager::FIELD_USERNAME};
			$reviewIds = [];
			$userIds = [];
			$kworkIds = [];
			$portfolioItemsIds = [];
			foreach ($reviews as $key => $review) {
				$kworkIds[$review["PID"]] = $review["PID"];
				if ($review["rater_id"]) {
					$userIds[$review["rater_id"]] = $review["rater_id"];
				}


				$review["comment_source"] = $review["comment"];
				$review["comment"] = replace_full_urls($review["comment_source"]);

				if ($review["RID"]) {
					$reviewIds[] = $review["RID"];
				}
				$reviews[$key]["worker_username"] = $workerName;

				// Собираем идентификаторы портфолио
				if ($review["id"] > 0) {
					$portfolioItemsIds[] = (int)$review["id"];
				}
			}

			// Получаем изображения для собранных идентификаторов портфолио
			if ($portfolioItemsIds) {
				// Заполняем данные портфолио
				foreach ($reviews as $key => $review) {
					if ($review["id"] > 0) {
						$reviews[$key]["portfolio"] = new StdClass();
						$reviews[$key]["portfolio"]->cover = $review["cover"];
						$reviews[$key]["portfolio"]->video = $review["video"];
						$reviews[$key]["portfolio"]->photo = $review["photo"];
						$reviews[$key]["portfolio"]->is_resizing = $review["is_resizing"];
					}
				}
			}

			$rcStatus = ($actor && $actor->id == $userId) ? "" : "AND rc.status = 'active'";

			$reviewAnswers = [];
			if (count($reviewIds)) {
				$params = [];
				$reviewAnswers = App::pdo()->fetchAllNameByColumn(
						"SELECT 
						 rc.id, rc.review_id, rc.message, rc.user_id, rc.status, rc.time_added,
						m.USERID as worker_id, m.username as worker_username, m.profilepicture as worker_profilepicture
						FROM rating_comment rc
							JOIN members m ON m.USERID = rc.user_id
						WHERE rc.review_id IN (" . App::pdo()->arrayToStrParams($reviewIds, $params, PDO::PARAM_INT) . ") " . $rcStatus, 1, $params);
			}

			$raters = [];
			if (count($userIds)) {
				$params = [];
				$raters = App::pdo()->fetchAllNameByColumn("SELECT USERID, username, profilepicture, lang FROM members WHERE USERID IN (" . App::pdo()->arrayToStrParams($userIds, $params, PDO::PARAM_INT) . ")", 0, $params);

				//Ищем самый большой рейтинг юзера
				$badges  = self::getUsersBadges($userIds);

				//Получаем супер-покупателей
				$super = self::getSuperUsers($userIds);
			}

			$params = [];
			$kworks = App::pdo()->fetchAllNameByColumn(
					"SELECT p.PID, p.gtitle, p.url, p.active, p.feat
					FROM posts p
					WHERE PID IN (" . App::pdo()->arrayToStrParams($kworkIds, $params, PDO::PARAM_INT) . ")", 0, $params);

			foreach ($reviews as $key => $review) {
				if (isset($reviewAnswers[$review["RID"]])) {
					$reviewAnswers[$review["RID"]]["message"] = replace_full_urls($reviewAnswers[$review["RID"]]["message"]);
					$reviewAnswers[$review["RID"]]["canEdit"] = self::canEditAnswer($reviewAnswers[$review["RID"]]);
					$reviews[$key]["answer"] = $reviewAnswers[$review["RID"]];
				}

				if ($review["rater_id"]) {
					$reviews[$key]["username"] = $raters[$review["rater_id"]]["username"];
					$reviews[$key]["profilepicture"] = $raters[$review["rater_id"]]["profilepicture"];
					$reviews[$key]["lang"] = $raters[$review["rater_id"]]["lang"];
				}

				if (isset($badges[$review["rater_id"]])) {
					$reviews[$key]["badge"] = $badges[$review["rater_id"]];
				}
				if (isset($super[$review["rater_id"]])) {
					$reviews[$key]["super"] = true;
				}


				$reviews[$key]["kwork"] = (object) $kworks[$review["PID"]];
			}
		}

		return [
			"total" => $total,
			"reviews" => $reviews
		];
	}

	/**
	 * Выполнены ли условия для получения бейджа Суперкомментатор
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isSuperCommentatorBadgeAvailable(int $userId): bool {
		return Rating::where(Rating::FIELD_RATER_ID, $userId)
			->whereRaw("char_length(" . Rating::FIELD_COMMENT . ") > 200")
			->count() >= self::BADGE_SUPERCOMMENTATOR_REVIEW_THRESHOLD;
	}
	
	/**
	 * Проверка, может ли редактировать пользователь ответ на отзыв
	 * @global stdClass $actor
	 * @param [] $reviewAnsver
	 * @return boolean
	 */
	public static function canEditAnswer($reviewAnswer) {
		$actor = UserManager::getCurrentUser();
		return $reviewAnswer["time_added"] > time() - 2 * Helper::ONE_WEEK && $reviewAnswer["user_id"] == $actor->id;
	}

	/**
	 * Получить отзывы кворка
	 * @param int $kworkId - ID кворка
	 * @param array $options - Массив опций с полями type, offset и limit
	 * @return false|array
	 */
	public static function getByKworkId($kworkId, $options) {
		global $actor;
		if (!isset($options["type"]))
			return false;
		if (!in_array($options["type"], ["positive", "negative"]))
			return false;
		$type = $options["type"];

		if (!isset($options["offset"]))
			$offset = 0;
		else
			$offset = intval($options["offset"]);

		if (!isset($options["limit"]))
			$limit = 5;
		else
			$limit = $options["limit"];
		
		$orderByFields[] = self::F_TIME_ADDED . ' DESC';
		$orderBy = implode(',', $orderByFields);
		
		$kworkId = intval($kworkId);
		if (!$kworkId)
			return false;

		$sql = "SELECT SQL_CALC_FOUND_ROWS
					r.RID, 
					r.comment, 
					r.comment comment_source, 
					r.good, 
					r.bad,
					r.time_added time_added,
					r.PID, r.OID as order_id, 
					r.auto_mode,
					u.username, 
					u.USERID, 
					u.profilepicture
				FROM " . self::TABLE_REVIEW . " r
					JOIN " .
						Model\User::TABLE_NAME . " u ON u.USERID = r.RATER
				WHERE 
					r.good = :isGood
					AND r.bad = :isBad
					ANd r.PID = :kworkId
				GROUP BY r.RID
				ORDER BY $orderBy
				LIMIT :offset, :limit";
		$params = [
			"isGood" => (int) ($type == "positive"),
			"isBad" => (int) ($type == "negative"),
			"kworkId" => $kworkId,
			"offset" => $offset,
			"limit" => $limit
		];

		$reviews = App::pdo()->fetchAllNameByColumn($sql, 0, $params);
		$total = App::pdo()->foundRows();


		if ($reviews !== false && count($reviews)) {
			$reviewIds = array_keys($reviews);

			$params = [];
			$reviewAnswers = App::pdo()->fetchAllNameByColumn(
					"SELECT 
					  rc.id, rc.review_id, rc.message, rc.user_id, rc.status, rc.time_added,
					  m.userid worker_id, m.username worker_username, m.profilepicture worker_profilepicture
				  FROM rating_comment rc
					JOIN members m ON m.USERID = rc.user_id
					WHERE rc.review_id IN (" . App::pdo()->arrayToStrParams($reviewIds, $params, PDO::PARAM_INT) . ") AND rc.status = 'active'", 1, $params);

			foreach ($reviews as $key => $review) {
				if (isset($reviewAnswers[$review["RID"]])) {
					$reviewAnswers[$review["RID"]]["message"] = replace_full_urls($reviewAnswers[$review["RID"]]["message"]);
					$reviewAnswers[$review["RID"]]["canEdit"] = self::canEditAnswer($reviewAnswers[$review["RID"]]);
					$reviews[$key]["answer"] = $reviewAnswers[$review["RID"]];
				}		
			}

			$userList = array_column($reviews, 'USERID');
			//Ищем самый большой рейтинг юзера
			$badges  = self::getUsersBadges($userList);

			//Получаем супер-покупателей
			$super = self::getSuperUsers($userList);

			foreach ($reviews as $key => $review) {
				if (isset($badges[$review["USERID"]])) {
					$reviews[$key]["badge"] = $badges[$review["USERID"]];
				}
				if (isset($super[$review["USERID"]])) {
					$reviews[$key]["super"] = true;
				}
				$reviews[$key]["hasPaidStages"] = false;
			}

			// Добавим поле hasPaidStages
			$reviews = self::injectHasPaidStagesToArray($reviews, "order_id");
		}

		return [
			"reviews" => $reviews === false ? [] : array_values($reviews),
			"total" => $total
		];
	}

	/**
	 * Получить список суперюзеров
	 * @param array $userlist
	 * @return array
	 */
	public static function getSuperUsers(array $userlist) {
		return [];
	}

	/**
	 * Метод поиска бейджей комментаторов
	 * @param array $userlist - массив пользователей
	 * @return array
	 */
	public static function getUsersBadges(array $userlist) {
		return [];
	}

	/**
	 * API подгрузки отзывов к кворку или пользователю
	 *
	 * @param string $entity - kwork или user
	 * @param int $id - ID кворка или пользователя
	 * @param string $type - positive или negative
	 * @param int $offset - Смещение
	 * @param int $limit - Количество
	 * @param string $orderBy По чему сортировать отзывы rating
	 * @param int $hideRate Скрывать кнопки лайков
	 *
	 * @return false|array
	 */
	public static function api_loadReviews($entity, $id, $type, $offset, $limit = 12, $orderBy = '', $hideRate = 0) {
		if ($offset < 0 || $limit < 0) {
			return false;
		}
		if ($entity == self::REVIEW_ENTITY_USER) {
			$reviews = self::getByUserId($id, ["type" => $type, "offset" => $offset, "limit" => $limit]);
			return self::getUserReviewsHTML($id, $reviews, $type, "list");
		} elseif ($entity == self::REVIEW_ENTITY_KWORK) {
			$reviews = self::getByKworkId($id, ["type" => $type, "offset" => $offset, "limit" => $limit, 'orderBy' => $orderBy]);
			return self::getKworkReviewsHTML($id, $reviews, $type, "list", $hideRate);
		}
		return [];
	}

	/**
	 * @param $reviews - Массив полученный из метода RatingManager::getByUserId
	 * @param $listType - block или list
	 * @return array
	 */
	public static function getUserReviewsHTML($id, $reviews, $type, $listType) {
		global $actor, $smarty;

		$arReturn = [];
		$arReturn["html"] = "";

		$grat = Rating::where(Rating::FIELD_USERID, $id)
			->where(Rating::FIELD_GOOD, 1)
			->count();

		$brat = Rating::where(Rating::FIELD_USERID, $id)
			->where(Rating::FIELD_BAD, 1)
			->count();

		$smarty->assign("grat", $grat);
		$smarty->assign("brat", $brat);
		$smarty->assign("count", count($reviews["reviews"]));
		$smarty->assign("type", $type);
		$smarty->assign("change", false);
		$smarty->assign("isWorker", $id == $actor->id);

		$smarty->assign("revs", $reviews["reviews"]);

		$withPortfolio = true;
		$smarty->assign("withPortfolio", $withPortfolio);

		if ($listType == "block") {
			$arReturn["html"] = $smarty->fetch("reviews_new.tpl");
		} else if ($listType == "list") {
			$arReturn["html"] = $smarty->fetch("reviews_list_new.tpl");
		}
		return $arReturn;
	}

	/**
	 * Получение отрендеренного html для отзывов
	 *
	 * @param int $id Идентификатор кворка
	 * @param array $reviews - Массив полученный из метода RatingManager::getByKworkId
	 * @param string $type Тип отзыва positive|negative
	 * @param string $listType - block или list
	 * @param bool $hideReviewsRate Скрывать кнопки лайков отзывов
	 *
	 * @return array
	 */
	public static function getKworkReviewsHTML($id, $reviews, $type, $listType, bool $hideReviewsRate = false) {
		global $smarty, $actor;

		$arReturn = [];
		$arReturn["html"] = "";

		$kworkData = App::pdo()->fetch("SELECT PID, USERID, good_comments_count, bad_comments_count FROM posts WHERE PID = :kworkId", ["kworkId" => $id]);
		$grat = $kworkData["good_comments_count"];
		$brat = $kworkData["bad_comments_count"];

		$smarty->assign("grat", $grat);
		$smarty->assign("brat", $brat);
		$smarty->assign("count", count($reviews["reviews"]));
		$smarty->assign("type", $type);
		$smarty->assign("change", false);
		$smarty->assign("isWorker", $actor->id == $kworkData["USERID"]);
		$smarty->assign("hideReviewsRate", $hideReviewsRate);

		$smarty->assign("revs", $reviews["reviews"]);

		$smarty->assign("withPortfolio", false);

		if ($listType == "block") {
			$arReturn["html"] = $smarty->fetch("reviews.tpl");
		} else if ($listType == "list") {
			$arReturn["html"] = $smarty->fetch("reviews_list.tpl");
		}
		return $arReturn;
	}

	/**
	 * Возвращает false, если по заказу нельзя оставить отзыв,
	 * RatingManager::CAN_ADD_REVIEW_BAD если по заказу можно оставить только отрицательный отзыв,
	 * RatingManager::CAN_ADD_REVIEW_ALL если по заказу можно оставить любой отзыв
	 * @param integer $orderId Ид заказа
	 * @return (false|RatingManager::CAN_ADD_REVIEW_BAD|RatingManager::CAN_ADD_REVIEW_ALL)
	 */
	public static function getAvailReviewType($orderId) {
		$actor = UserManager::getCurrentUser();

		$order = Order::find($orderId);

		$allow = ($order->isPayer($actor->id) || \UserManager::isModer()) && in_array($order->status, [OrderManager::STATUS_DONE, OrderManager::STATUS_CANCEL]);
		if (!$allow) {
			return false;
		}

		$track = $order->lastTrack;

		// Если истек срок для оставления отзыва
		if (!self::inCreateTime($order->OID)) {
			// Отзыв оставить нельзя
			return false;
		}

		// у отмененного заказа при некоторых типах можно оставить только отрицательный отзыв
		if ($order->status == OrderManager::STATUS_CANCEL && !$positiveArbitrage) {
			$reasonsForReview = self::getBadReviewReasons();
			$canAuto = false;
			if ($track->type == Type::CRON_INPROGRESS_INWORK_CANCEL) {
				$canAuto = true;
			} elseif ($track->type == Type::PAYER_INPROGRESS_CANCEL && $track->reason_type == "payer_time_over") {
				$canAuto = true;
			} elseif (in_array($track->type, [Type::WORKER_INPROGRESS_CANCEL_CONFIRM, Type::CRON_PAYER_INPROGRESS_CANCEL]) && $track->reason_type == "payer_worker_cannot_execute_correct") {
				$canAuto = true;
			}
			if (!in_array($track->reason_type, $reasonsForReview) && !$canAuto) {
				return false;
			}

			return RatingManager::CAN_ADD_REVIEW_BAD;
		}

		return RatingManager::CAN_ADD_REVIEW_ALL;
	}

	/**
	 * Возвращает список причин отмены заказа, для которых доступно написание плохого отзыва
	 */
	public static function getBadReviewReasons() {
		return [
			'payer_time_over',
			'payer_no_communication_with_worker',
			'worker_force_cancel',
			'worker_no_time',
			'payer_worker_cannot_execute_correct'
		];
	}

	/**
	 * Отформатировать текст комментария: удалить абзатцы, добавить пробелы
	 * используется в шаблнах smarty
	 *
	 * @param string $text Текст
	 * @param string $autoMode Режим автоотзыва
	 * @param bool $hasPaidStages Есть ли оплаченные этапы в заказе
	 *
	 * @return string Отформатированный $text
	 */
	public static function formatText($text, $autoMode = null, $hasPaidStages = false) {
		// переведём автоответ после арбитража
		if ($autoMode == self::AUTO_MODE_ARBITRAGE_PAYER) {
			$text = Translations::t($text);
		}

		if ($text != "") {
			$rows = explode("<br>", $text);
			$rows = array_filter(array_map('trim', $rows));
			$text = implode(" ", $rows);
			$text = preg_replace(RegexpPatternManager::REGEX_URL, '{{{$0}}}', $text); // Обернуть ссылки в {{{url}}}

			/*
			 * Ставим пробелы после знаков препинания (только если знак не в конце строки),
			 * кроме ";" чтобы не ставить пробелы после html-entities
			 */
			$text = preg_replace('/([.,!?:])([^\s]+)/', '$1 $2', $text);
			$text = preg_replace_callback('!({{{(.*)}}})!U', // Удалить пробелы у ссылок
				function ($matches) {
					return replace_full_urls(str_replace(' ', '', $matches[2]));
				}, $text);
		}
		if ($autoMode) {
			$addText = self::getAutoModeText($autoMode, $hasPaidStages);
			if ($text != "") {
				if ($autoMode == self::AUTO_MODE_INCORRECT_EXECUTE) {
					$addText .= Translations::t(" Комментарий покупателя: ");
				} elseif ($autoMode == self::AUTO_MODE_TIME_OVER) {
					$addText .= Translations::t(" с комментарием: ");
				}
			}
			$text = $addText . $text;
		}

		return $text;
	}

	/**
	 * Return auto generating status for review
	 *
	 * @param string $mode Mode from RatingManager constants
	 * @param bool $hasPaidStages Есть ли оплаченные этапы в заказе
	 *
	 * @return string
	 */
	public static function getAutoModeText($mode, $hasPaidStages = false) {
		if ($mode == self::AUTO_MODE_INWORK_TIME_OVER) {
			if ($hasPaidStages) {
				return Translations::t("Продавец надолго затянул сдачу заказа, из-за чего он был автоматически остановлен.");
			}
			return Translations::t("Продавец надолго затянул сдачу заказа, из-за чего он был автоматически отменен.");
		} elseif ($mode == self::AUTO_MODE_TIME_OVER) {
			if ($hasPaidStages) {
				return Translations::t("Покупателю пришлось остановить заказ после его просрочки");
			}
			return Translations::t("Покупателю пришлось отменить заказ после его просрочки");
		} elseif ($mode == self::AUTO_MODE_INCORRECT_EXECUTE) {
			if ($hasPaidStages) {
				return Translations::t("Покупателю пришлось остановить заказ, так как продавец не мог его корректно выполнить.");
			}
			return Translations::t("Покупателю пришлось отменить заказ, так как продавец не мог его корректно выполнить.");
		} elseif ($mode == self::AUTO_MODE_ARBITRAGE_PAYER) {
			if ($hasPaidStages) {
				return Translations::t("Покупателя не устроил результат работы, и заказ был отправлен в арбитраж. Спор решен в пользу покупателя, заказ остановлен.");
			}
			return Translations::t("Покупателя не устроил результат работы, и заказ был отправлен в арбитраж. Спор решен в пользу покупателя, заказ отменен.");
		}

		return "";
	}

	/**
	 * Проверка, не истекло ли время для изменения/удаления отзыва по заказу
	 * @param int $reviewId ИД отзыва
	 * @return boolean true, если отзыв еще можно изменить/удалить
	 */
	public static function inEditTime($reviewId) {
		global $conn;
		global $actor;

		if ($actor->isVirtual) {
			return true;
		}

		// Получить отзыв по ИД
		$review = $conn->getEntity("
			SELECT r.OID, r.bad, r.time_vote_changed
			FROM ratings r
			WHERE 
				r.RID = '" . mres($reviewId) . "' LIMIT 1");

		// Если оставлен отрицательный отзыв
		if ($review->bad) {
			// Если прошло более RatingManager::EDIT_NEGATIVE_REVIEW_PERIOD_DAYS дней с момента создания отзыва
			// или смены отзыва с положительного на отрицательный
			if ($review->time_vote_changed < time() -
				RatingManager::EDIT_NEGATIVE_REVIEW_PERIOD_DAYS * Helper::ONE_DAY) {
				// Отзыв изменить нельзя
				return false;
			}
		}

		// Если истекло время для написания отзыва - истекло время и для его изменения/удаления
		if (!self::inCreateTime($review->OID)) {
			return false;
		}

		return true;
	}

	/**
	 * Проверка, не истекло ли время для написания отзыва по заказу
	 * @param int $orderId ИД заказа
	 * @return boolean true, если отзыв еще можно оставить
	 */
	public static function inCreateTime($orderId) {
		global $conn;
		global $actor;

		if ($actor->isVirtual) {
			return true;
		}

		// Получить заказ по ИД
		$order = $conn->getEntity("
			SELECT o.status, o.date_done, o.date_cancel
			FROM 
				orders as o 
			JOIN
				posts as p on o.PID = p.PID
			JOIN
				categories as c on p.category = c.CATID
			WHERE 
				OID = '" . mres($orderId) . "'");

		// Определить дату выполения/отмены заказа
		if ($order->status == OrderManager::STATUS_DONE) {
			$orderClosedDate = strtotime($order->date_done);
		} else {
			$orderClosedDate = strtotime($order->date_cancel);
		}

		// Если максимальное время на выполнение кворка >= 30 дней
		if ((int)$order->max_days >= 30) {
			// Если прошло более 2 месяцев с момента принятия/отмены заказа
			if ($orderClosedDate < time() -
				RatingManager::CREATE_REVIEW_PERIOD_MONTH_LONG * Helper::ONE_MONTH) {
				// Отзыв оставить нельзя
				return false;
			}
		} else {
			// Если максимальное время на выполнение кворка < 30 дней
			// Если прошло больше месяца с момента принятия/отмены заказа
			if ($orderClosedDate < time() -
				RatingManager::CREATE_REVIEW_PERIOD_MONTH_SHORT * Helper::ONE_MONTH) {
				// Отзыв оставить нельзя
				return false;
			}
		}

		return true;
	}

	/**
	 * Количество отзывов по пользователю
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param string $lang Языковая версия отзывы по которой получаем, если не указана то текущая
	 *
	 * @return array|false ["good" => int, "bad" => int]
	 */
	public static function userCounts(int $userId, $lang = null) {

		$sql = "SELECT
					SUM(if(o.rating_type = 'good' || (o.rating_type != 'bad' && pf.order_id IS NOT NULL), 1, 0)) as good,
					SUM(if(o.rating_type = 'bad', 1, 0)) as bad
				FROM orders o
				LEFT JOIN portfolio pf ON pf.order_id = o.OID
				WHERE worker_id = :userId AND o.currency_id = :currencyId";

		if (!$lang) {
			$lang = Translations::getLang();
		}
		$params = [
			"userId" => $userId,
			"currencyId" => Translations::getCurrencyIdByLang($lang),
		];

		$counts = App::pdo()->fetch($sql, $params);

		return [
			"good" => (int)$counts["good"],
			"bad" => (int)$counts["bad"],
		];
	}

	/**
	 * Удалить отзыв по заказу и ответ на отзыв
	 * а также портфолио
	 *
	 * @param int $orderId
	 *
	 * @return bool
	 */
	public static function removeReviewByOrder($orderId) {
		$review = Rating::where(Rating::FIELD_ORDER_ID, $orderId)->first();

		if (!$review) {
			return false;
		}
		//Удаление ответа на отзыв и отзыва
		$review->answer()->delete(); // Поидее это должно делаться foreign key cascade
		$review->delete();

		KworkManager::setReviewCount($review->PID);

		return true;
	}

	/**
	 * Получение количества негативных отзывов среди последних 30
	 *
	 * @param int $kworkId Идентификатор кворка
	 *
	 * @return int
	 */
	public static function getKworkBadReviewsCount(int $kworkId): int {
		$sql = "SELECT bad
				FROM ratings
				WHERE PID = :kworkId
				ORDER BY RID DESC
				LIMIT " . KworkPostsDataManager::LAST_REVIEWS_LIMIT;

		$reviews = App::pdo()->fetchAllByColumn($sql, 0, ["kworkId" => $kworkId]);

		return array_sum($reviews);
	}

	/**
	 * Обработка содержимого отзыва перед сохранением в БД
	 *
	 * @param string $comment Содержимое комментария
	 *
	 * @return string
	 */
	public static function prepareCommentToSave($comment): string {
		//Преобразуем <p> в переводы строки, декодируем сущности
		$comment = \Helper::p2nl($comment);
		// Обрезаем строку по максимальной длине
		$comment = \Helper::trimTextWidth($comment, self::COMMENT_MAX_CHARACTERS_COUNT);
		// Вырезаем написанные пользователем html тэги, превращаем другие спецсимволы в сущности
		$comment = htmlspecialchars(strip_tags($comment));
		// Удаляет лишние проблемы и спецсимволы из начала и конца строки
		$comment = trim($comment, " \t\n\r\0\x0B\xC2\xA0");
		
		return $comment;
	}

	/**
	 * Получение количеств отзывов
	 *
	 * @param array $userIds Массив идентификаторов пользователей
	 * @param string $lang Язык отзывов
	 *
	 * @return array
	 */
	public static function getUsersReviewsCount(array $userIds, string $lang) {
		$userReviewsCounts = [];
		if (count($userIds)) {
			foreach ($userIds as $userId) {
				$userReviewsCounts[$userId] = \RatingManager::userCounts($userId, $lang);
			}
		}

		return $userReviewsCounts;
	}

	/**
	 * Проверить комментарий на непустой
	 *
	 * @param string $string
	 * @return array
	 */
	public static function checkCommentNotEmpty($string) {
		$check = ['success' => true];
		$clearString = self::prepareCommentToSave($string);
		if(empty($clearString)) {
			$check['success'] = false;
			$check['result'] = [
				'emptyString' => true
			];
		}

		return $check;
	}

	/**
	 * Получить отзыв покупателя по заказу, в котором привязана работа
	 *
	 * @param $orderId
	 * @return array|bool
	 */
	public static function getByOrderId($orderId) {
		$orderId = intval($orderId);
		if (!$orderId) {
			return false;
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS
					r.comment,
					r.time_added,
					r.PID,
					r.auto_mode,
					u.username,
					u.USERID as user_id,
					u.profilepicture,
					r.unread
				FROM " . self::TABLE_REVIEW . " r
					JOIN " .
			Model\User::TABLE_NAME . " u ON u.USERID = r.RATER
				WHERE
					r.OID = :orderId";
		$params = [
			"orderId" => $orderId,
		];
		$review = App::pdo()->fetchAll($sql, $params);

		if ($review !== false && count($review)) {
			$review[0]["is_review"] = 1;
		}

		return $review;
	}

	/**
	 * Добавить в массив отзывов поле $hasPaidStages
	 *
	 * @param array $reviews Массив отзывов
	 * @param string $orderIdField Название поля идентификатора заказа в отзыве по умолчанию OID
	 *
	 * @return array
	 */
	public static function injectHasPaidStagesToArray($reviews, $orderIdField = self::F_ORDER_ID) {
		// Получим идентификаторы заказов в отрицательных автоотзывах
		$badAutoReviewsOrderIds = array_column(array_filter($reviews, function ($review) {
			return $review[self::F_BAD] && $review[self::F_AUTO_MODE];
		}), "order_id");
		if ($badAutoReviewsOrderIds) {
			// Получим список идентификаторов заказов в которых есть оплаченные этапы
			$orderIdsWithPaidStages = OrderStage::whereIn(OrderStage::FIELD_ORDER_ID, $badAutoReviewsOrderIds)
				->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_PAID)
				->distinct()
				->pluck(OrderStage::FIELD_ORDER_ID, OrderStage::FIELD_ORDER_ID)
				->toArray();
		}
		foreach ($reviews as $key => $review) {
			if (isset($orderIdsWithPaidStages[$review[$orderIdField]])) {
				$reviews[$key]["hasPaidStages"] = true;
			} else {
				$reviews[$key]["hasPaidStages"] = true;
			}
		}
		return $reviews;
	}

}
