<?php


namespace Converter;

use InboxManager;
use Model\File;
use Model\Inbox\Inbox;
use Model\User;

/**
 * Конвертирует модель inbox в массив поля которого ожидается на фронте
 */
class InboxModelToFrontendArrayConverter {

	/**
	 * Кешированный список сотрудников техподдержки
	 * @var array
	 */
	private static $supports;

	/**
	 * Конвертация модели
	 *
	 * @param \Model\Inbox\Inbox $model
	 *
	 * @return array
	 */
	public static function convert(Inbox $model): array {
		$quote = $model->quote()
			->with(["author" => function ($selectQuery) {
				$selectQuery->select(User::FIELD_USERNAME, User::FIELD_USERID);
			}, "message", "files"
			])->select(Inbox::FIELD_MESSAGE_ID,
				Inbox::FIELD_MESSAGE_FROM,
				Inbox::FIELD_MESSAGE_TO,
				Inbox::FIELD_INBOX_MESSAGE_ID,
				Inbox::FIELD_TIME,
				Inbox::FIELD_UNREAD,
				Inbox::FIELD_QUOTE_ID)
			->first();
			
		$array = [
			"MID" => $model->MID,
			"MSGTO" => $model->MSGTO,
			"MSGFROM" => $model->MSGFROM,
			"PID" => $model->PID,
			"time" => $model->time,
			"type" => $model->type,
			"unread" => $model->unread,
			"created_order_id" => $model->created_order_id,
			"inbox_message_id" => $model->inbox_message_id,
			"mto" => $model->recipient->username,
			"mfrom" => $model->author->username,
			"profilepicture" => $model->author->profilepicture,
			"rawMessage" => $model->message->message,
			"message" => replace_full_urls($model->message->message),
			"typeTitle" => InboxManager::getStatusDesc($model->type, $model->MSGFROM, $model->MSGTO),
			"support_id" => $model->support->support_id,
			"files" => $model->files->keyBy(File::FIELD_ID)->all(),
			"updated_at" => (int)$model->updated_at,
			"quote" => $quote,
		];

		if ($array["support_id"]) {
			$supportNames = self::getSupports();
			$array["supportName"] = $supportNames[$array["support_id"]]["support_name"];
			$array["supportTitle"] = $supportNames[$array["support_id"]]["support_title"];
			$supportScore = $model->support->score;
			if ($supportScore) {
				$array["support_score"] = $supportScore->toArray();
			}
		}

		if ($model->data) {
			foreach (["budget", "duration", "currency_id"] as $key) {
				$array[$key] = $model->data->$key;
			}
		}

		return $array;
	}

	/**
	 * Получение массива саппортов с кешированием
	 * @return array
	 */
	private static function getSupports() {
		if (is_null(self::$supports)) {
			self::$supports = \Support\SupportManager::getModeratorsArray(false, false, true);
		}
		return self::$supports;
	}

}