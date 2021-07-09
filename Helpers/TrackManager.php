<?php

use Core\Exception\SimpleJsonException;
use Model\File;
use Model\Order;
use Model\TrackExtra;
use Order\OrderDataManager as ODM;
use Model\UpgradePackageExtra;
use Core\DB\DB;
use Model\OrderExtra;
use Model\Track;
use Order\Stages\OrderStageManager;
use Track\Type;
use Model\Track\TrackCreateData;

class TrackManager
{
	const TABLE_NAME = 'track';

	const FIELD_ID = 'MID';
	const FIELD_TYPE = 'type';
	const FIELD_ORDER_ID = 'OID';
	const FIELD_STATUS = 'status';
	const FIELD_MESSAGE = 'message';
	const FIELD_USER_ID = 'user_id';
	const FIELD_REASON_TYPE = "reason_type";
	const FIELD_PREV_REASON_TYPE = "prev_reason_type";
	const FIELD_SUPPORT_ID = 'support_id';
	const FIELD_DATE_CREATE = 'date_create';
	const FIELD_DATE_UPDATE = 'date_update';
	const FIELD_REPLY_TYPE = "reply_type";
	const FIELD_UNREAD = 'unread';
	const FIELD_KB_ARTICLE_ID = 'kb_article_id';
	const FIELD_KB_ROLE_ID = 'kb_role_id';

	/**
	 * Согласен с причиной отклонения
	 */
	const REPLY_TYPE_AGREE = 'agree';

	/**
	 * Не согласен с причиной отклонения
	 */
	const REPLY_TYPE_DISAGREE = 'disagree';

	const TRACK_TYPE_WORKER_INPROGRESS_CANCEL_REQUEST = "worker_inprogress_cancel_request";
	const TRACK_TYPE_WORKER_PORTFOLIO = "worker_portfolio";
	const TRACK_TYPE_PAYER_INPROGRESS_CANCEL_REQUEST = "payer_inprogress_cancel_request";
	const TRACK_TYPE_TEXT = "text";

	/**
	 * Отклонен по причине завышения цены
	 */
	const REASON_TYPE_INFLATED_PRICE = "payer_inflated_price";

	/**
	 * Причина отмены "Не выполнено вовремя"
	 */
	const REASON_TYPE_PAYER_TIME_OVER = "payer_time_over";

	/**
	 * Причина отмены "Нет связи с продавцом"
	 */
	const REASON_TYPE_PAYER_NO_COMMUNICATION_WITH_WORKER = "payer_no_communication_with_worker";

	/**
	 * Причина отмены "Админ отменил заказ и присудил средтва покупателю"
	 */
	const REASON_TYPE_ADMIN_ARBITRAGE_CANCEL = "admin_arbitrage_cancel";


	/**
	 * Продавец несогласен с этапами которые безакцептно добави/изменил покупатель
	 */
	const REASON_TYPE_WORKER_DISAGREE_STAGES = "worker_disagree_stages";

	/**
	 * Причина отмены "Продавец не может корректно выполнить заказ"
	 */
	const REASON_PAYER_WORKER_CANNOT_EXECUTE_CORRECT = "payer_worker_cannot_execute_correct";

	/**
	 * Маскимальное количество отклонений админом причины отмена заказа "завышение цены"
	 */
	const MAX_COUNT_INFLATED_PRICE = 2;

	const CANCEL_REASON_NORMAL = 1;
	const CANCEL_REASON_BAD_SERVICE = 2;
	const CANCEL_REASON_BAD_SERVICE_REVIEW = 3;
	const CANCEL_REASON_BAD_REVIEW = 4;

	const CONDITION_TIME_OVER = 1;
	const CONDITION_CRON_INPROGRESS = 2;
	const CONDITION_INPROGRESS_CANCEL = 3;
	const CONDITION_PAYER_DISAGREE = 4;
	const CONDITION_CONFIRM = 5;
	const CONDITION_INWORK_LATE_INCORRECT = 6;
	const CONDITION_INCORRECT = 7;
	const CONDITION_INCORRECT_AND_LATE = 8;
	const CONDITION_NO_COMMUNICATION_AND_LATE = 9;

	const MAX_FILE_SIZE = 12 * 1024 * 1024;

	const STATUS_NEW = 'new';
	const STATUS_CLOSE = 'close';
	const STATUS_DONE = 'done';
	const STATUS_CANCEL = 'cancel';

	const CHANGE_FILE_REMOVED = 'file_removed';
	const CHANGE_TEXT_MESSAGE = 'text_message_changed';

	const UNREAD = 1;
	const READ = 0;

	/**
	 * Срок с момента заказа когда скрыть докупку опций
	 */
	const HIDE_EXTRA_LIST_TRESHOLD = Helper::ONE_DAY * 2;

	//Идентификатор поля отзывов в таблице kwork_text_fields
	const CHECK_FIELD_REVIEW = 10;

	/**
	 * Количество показываемых прочитанных треков
	 * Внимание - нельзя делать менее 3, иначе могут быть проблемы
	 */
	const SHOW_READ_TRACKS = 10;

	private static $orderTracks = array();

	/**
	 * Кэшированные данные о файлах из Request
	 *
	 * @var array
	 */
	private static $requestFiles = null;

	/**
	 * Создание трека в заказе
	 * Минимальный рефакторинг чтобы можно было использовать в транзакции
	 * (использовать в транзакциях можно только для треков не запускающих время заказа, и без указаний payerId n kworkId)
	 *
	 * @param int $orderId Идентификатор трека
	 * @param string $type Тип трека
	 * @param string|null $message Сообщение трека
	 * @param string|null $reasonType Причина отмены заказа
	 * @param string|null $replyType Согласен ли с причиной отмены заказа agree|disagree
	 * @param string|null $status Статус трека new|close|done|cancel
	 * @param int|null $payerId Идентификатор покупателя (для UserKwork::set())
	 * @param int|null $kworkId Идентификатор кворка (для UserKwork::set())
	 * @param int|null $userId Идентификатор пользователя - автора трека
	 * @param int|null $supportId Идентификатор саппорта
	 * @param int|null $articleId Идентификатор статьи по арбитражам в базе KB
	 * @param int|null $roleId Идентификатор роли пользователя в статьях по арбитражам KB
	 * @param int|null $quoteId Идентификатор цитируемого сообщения
	 *
	 * @return int Идентификатор созданного трека
	 */
	public static function create($orderId, $type, $message = null, $reasonType = null, $replyType = null, $status = null, $payerId = null, $kworkId = null, $userId = null, $supportId = null, $articleId = null, $roleId = null, $quoteId = null) {
		$actor = UserManager::getCurrentUser();

		if (!$orderId || !$type) {
			return false;
		}

		OrderManager::updateDeadline($orderId, $type);

		$fieldsTrack = [
			self::FIELD_ORDER_ID => $orderId,
			self::FIELD_TYPE => $type
		];
		if ($userId) {
			$fieldsTrack[self::FIELD_USER_ID] = $userId;
		} else {
			if ($actor) {
				$fieldsTrack[self::FIELD_USER_ID] = $actor->id;
			}
		}

		if ($supportId) {
			$fieldsTrack[self::FIELD_SUPPORT_ID] = $supportId;
		}
		if ($message) {
			$fieldsTrack[self::FIELD_MESSAGE] = htmlspecialchars($message);
		}
		if ($reasonType) {
			$fieldsTrack[self::FIELD_REASON_TYPE] = $reasonType;
		}
		if ($replyType) {
			$fieldsTrack[self::FIELD_REPLY_TYPE] = $replyType;
		}
		if ($articleId) {
			$fieldsTrack[self::FIELD_KB_ARTICLE_ID] = $articleId;
		}
		if ($roleId) {
			$fieldsTrack[self::FIELD_KB_ROLE_ID] = $roleId;
		}
		if ($quoteId && self::checkQuoteTrack($type, $orderId, $quoteId)) {
			$fieldsTrack[Track::FIELD_QUOTE_ID] = $quoteId;
		}

		$status = $status ?: self::STATUS_NEW;
		$fieldsTrack[self::FIELD_STATUS] = $status;

		$fieldsTrack[self::FIELD_UNREAD] = \Track\Type::isCreatedImmediatelyRead($type) ? self::READ : self::UNREAD;

		if (Type::isCronType($type)) {
			$fieldsTrack[Track::FIELD_CRON_WORKER_UNREAD] = $fieldsTrack[self::FIELD_UNREAD];
		}

		$trackId = DB::table(self::TABLE_NAME)->insertGetId($fieldsTrack);
		if (!in_array($type, Type::unimportantTypes())) {
			\Model\Order::whereKey($orderId)
				->update([\Model\Order::FIELD_LAST_TRACK_ID => $trackId]);
		}
		$payerId = (int) $payerId;
		$kworkId = (int) $kworkId;

		// прикрепление файлов, в т.ч. из черновика, если есть
		self::attachFilesToTrack($trackId, $draft ? $draft->id : null);

		// удаление черновика вместе с неиспользованными файлами
		if ($draft) {
			$draft->removeWithFiles();
		}

		$similarFileIds = post("similar_files");
		if ($trackId && !empty($similarFileIds)) {
			$similarFiles = File::whereIn(File::FIELD_ID, $similarFileIds)
				->get();

			if (!empty($similarFiles)) {
				foreach ($similarFiles as $file) {
					if ($file->USERID == $actor->id) {
						$values = [
							FileManager::FIELD_USER_ID => $actor->id,
							FileManager::FIELD_FNAME => $file->fname,
							FileManager::FIELD_S => $file->s,
							FileManager::FIELD_TIME => time(),
							FileManager::FIELD_IP => $_SERVER["REMOTE_ADDR"],
							FileManager::FIELD_ENTITY_ID => $trackId,
							FileManager::FIELD_ENTITY_TYPE => FileManager::ENTITY_TYPE_TRACK,
							FileManager::FIELD_SIZE => $file->size,
							FileManager::FIELD_LANG => $file->lang,
						];
						FileManager::createNewFile($values, $file);
					}
				}
			}
		}

		// Об этих уведомлять никого не надо.
		$notPushedTypes = [
			\Track\Type::WORKER_PORTFOLIO,
			\Track\Type::PAYER_ADVICE,
			// А эти надо обработать пушем вне, после создания всего.
			\Track\Type::EXTRA,
			\Track\Type::ADMIN_ARBITRAGE_DONE,
			\Track\Type::WORKER_REPORT_NEW,
		];
		if (!in_array($type, $notPushedTypes)) {
			self::sendNewTrackPush($orderId, $type, $status, $trackId, (string) $message);
		}

		if ($type == \Track\Type::TEXT) {
			self::calcUnreadTracksCount($orderId);
		}

		if ($reasonType === self::REASON_TYPE_INFLATED_PRICE) {
			/** @var \Model\Order $order */
			$order = \Model\Order::find($orderId);
			\Support\AssignManager::addInflatedAssign($trackId, $order->worker_id);
		}

		if (in_array($type, \Track\Type::getAdminArbitrageTypes())) {
			\Model\OrdersWasInArbitrage::addToList($trackId, $orderId);
		}

		return $trackId;
	}

	/**
	 * Отправляет пуш о новом треке участнику или обоим.
	 *
	 * @param int $orderId
	 * @param string $trackType
	 * @param string $trackStatus
	 * @param int $trackId добавляет в данные пуша информацию по идентификатору сообщения
	 * @param string $text добавляет в данные пуша текст сообщения
	 */
	public static function sendNewTrackPush(int $orderId, string $trackType, string $trackStatus, int $trackId = 0, string $text = "") {
		return;
	}

	/**
	 * Отправляет пуш об изменении трека нужному человеку.
	 *
	 * @param int $trackId
	 * @param array $params
	 */
	public static function sendTrackChangedPush(int $trackId, array $params = []) {
		return;
	}

	/**
	 * Метод определяет получателя трека (либо его изменения),
	 * который особенно неочевиден для треков предложений,
	 * когда они уже существуют и их принимают/отклоняют.
	 *
	 * @param \stdClass $track
	 *
	 * @return int
	 * 	 id пользователя, который должен получить этот трек (либо его изменение).
	 */
	public static function getTrackRecipientId(\stdClass $track): int {
		$creatorId = $track->user_id;
		$orderData = OrderManager::getOrderData($track->OID);

		$trackOpponentId = ($creatorId == $orderData->payerId) ? $orderData->workerId : $orderData->payerId;

		// Трек предложения может меняться прямо по месту, при этом создатель его остается неизменным.
		// По текущему состоянию пытаемся определить текущего же получателя.
		if (\Track\Type::isExtra($track->type)) {
			switch ($track->status) {
				// Если предложение создано, то получатель - противная создателю сторона.
				// Отменить преложение может только его создатель, следовательно получатель - тоже противная создателю сторона.
				case self::STATUS_NEW:
				case self::STATUS_CANCEL:
					return $trackOpponentId;

				// Покупатель отказался от предложения, получатель - создатель этого самого предложения.
				case self::STATUS_CLOSE:
					return $creatorId;

				// Покупатель принял предложение, получатель - создатель этого самого предложения.
				case self::STATUS_DONE:
					// Если создатель этого трека покупатель (заказал доп. опции),
					// то получатель - противная создателю сторона.
					// В противном случае это принятие предложения продавца, который его создатель,
					// а теперь и получатель.
					return ($creatorId == $orderData->payerId) ? $trackOpponentId : $creatorId;

				// Сюда никогда не попадем.
				default:
					break;
			}
		}

		// Для обычных треков получатель - противная создателю трека сторона.
		return $trackOpponentId;
	}

	/**
	 * Создать текстовое сообщение в заказе
	 * @param int $senderId - ID отправителя (ID покупателя или ID продавца)
	 * @param int $orderId - ID заказа
	 * @param array $message - ["text"=>Текст сообщения, "uploadedFiles"=>Для файлов, которые загружаются через POST]
	 * @return int|false - ID трека или false в случае ошибки
	 */
	public static function createFromInbox($senderId, $orderId, $message) {
		if(!isset($message['text']) && !$message['text'] && !isset($message['uploadedFiles'])) {
			return false;
		}
		if(!$senderId || !$orderId) {
			return false;
		}
		$sql = "SELECT 
			OID as id, 
			USERID as payerId, 
			worker_id as workerId,
			status
		FROM orders 
		WHERE OID = :orderId";
		$order = App::pdo()->fetch($sql, ["orderId" => $orderId]);
		if(!$order || ($order["payerId"] != $senderId && $order["workerId"] != $senderId) || OrderManager::STATUS_DONE == $order['status']) {
			return false;
		}
		//Получатель
		$recipient = $senderId == $order["payerId"] ? $order["workerId"] : $order["payerId"];

		$trackId = App::pdo()->insert(self::TABLE_NAME, [
			"OID" => $orderId,
			"user_id" => $senderId,
			"type" => "text",
			"status" => "new",
			"message" => $message["text"],
			"date_create" => date("Y-m-d H:i:s")
		]);

		if (!empty($message["uploadedFiles"])) {
			TrackManager::attachUploadedFiles($trackId, $message["uploadedFiles"]);
		}

		if ($draft = \InboxDraftManager::getDraftByRecipientId($recipient)) {
			$draft->removeWithFiles();
		}

		return $trackId;
	}

	/**
	 * @param stdClass $orderData
	 * @param string $advicePlace
	 *
	 * @return bool
	 */
	public static function createAdviceTrack($orderData, $advicePlace)
	{
		global $conn;

		if (in_array($orderData->kworkCategory, [38, 46, CategoryManager::customOfferCategoryId(Translations::DEFAULT_LANG), CategoryManager::customOfferCategoryId(Translations::EN_LANG)])) {
			return false;
		}

		$sql = 'SELECT 1 FROM
                    track 
                WHERE 
                    OID = ' . mres($orderData->orderId) . '
                    AND type = "' . \Track\Type::PAYER_ADVICE . '"
                LIMIT 1';

		if($conn->getCell($sql)) {
			return false;
		}

		if($advicePlace == 'instruction') {
			$statuses = [OrderManager::STATUS_INPROGRESS];
		} else {
			$statuses = [OrderManager::STATUS_INPROGRESS, OrderManager::STATUS_CHECK];
		}

		$sql = 'SELECT 
                    count(*)
                FROM
                    orders o
                JOIN 
                    posts p ON o.PID = p.PID
                WHERE
                    p.category = ' . mres($orderData->kworkCategory) . '
                    AND o.status IN(' . mres(implode(', ', $statuses)) . ')
                    AND o.OID <> ' . mres($orderData->orderId) . '
                    AND o.USERID = ' . mres($orderData->payerId) . '
                ';

		if($conn->getCell($sql)) {
			return false;
		}

		return self::create($orderData->orderId, \Track\Type::PAYER_ADVICE, $advicePlace);
	}

	// закрыть трэк (для треков с кнопками действия)
	public static function close($trackId)
	{
		global $conn;

		if(!$trackId)
			return false;

		$conn->execute("UPDATE track SET status = 'close' WHERE MID = '" . mres($trackId) . "'");
	}

	/**
	 * Получение описания статуса трека.
	 *
	 * @param string $type Тип трека
	 * @param string $info Кому будет показано описание
	 * @param array $params Параметры, которые будут подставлены в описание
	 * @return string
	 */
	public static function getStatusDesc($type, $info, $params = []) {
		// @todo: на самом деле и текущий метод должен должен быть там,
		// оставил, чтобы не делать слишком много замен (он во многих местах) пока.
		$data = !empty($params) ? [$type => [$info => $params]] : [];
		$tracks = \Track\Type::getTracksDesc($data);
		return isset($tracks[$type]) && isset($tracks[$type][$info]) ? $tracks[$type][$info] : "";
	}

	public static function getDescByType($type, $isExtra, $isTips)
	{
		$desc = '';
		if($type) {
			switch ($type) {
				case "refill":
					$desc = "Пополнение счета";
					break;
				case "refill_bonus":
					$desc = "Пополнение бонусного счета по промокоду";
					break;
				case "refill_bill":
					$desc = "Пополнение безналичного счета";
					break;
				case "cancel_bonus":
					$desc = "Списание с бонусного счета неиспользованных бонусов по промокоду";
					break;
				case "refill_referal":
					$desc = "Пополнение основного счета за реферала";
					break;
				case "order_out":
					if(!$isTips) {
						$desc = $isExtra ? "Оплата дополнительных опций" : "Оплата кворка";
					}
					break;
				case "order_out_bonus":
					$desc = $isExtra ? "Оплата дополнительных опций с бонусного счета" : "Оплата кворка с бонусного счета";
					break;
				case "order_out_bill":
					$desc = $isExtra ? "Оплата дополнительных опций с безналичного счета" : "Оплата кворка с безналичного счета";
					break;
				case "refund":
					$desc = "Возврат оплаты";
					break;
				case "order_in":
					$desc = "Получение оплаты за сделанный кворк";
					break;
				case "withdraw":
					$desc = "Вывод с основного счета";
					break;
				case OperationManager::FIELD_TYPE_MONEYBACK:
					$desc = "Возврат пополнения баланса";
					break;
			}
		}
		return $desc;
	}

	/**
	 *
	 * @param bool|string $id ID причины
	 *
	 * @param bool $userType
	 *
	 * @return array|bool причина, если не указан ID, возвращает все причины по типу пользователя
	 */
	public static function getCancelReason($id = false, $userType = false)
	{
		if($userType !== false) {
			if(
				($userType == 'worker' && strpos($id, 'worker_') !== 0)
				||
				($userType == 'payer' && strpos($id, 'payer_') !== 0)
			) {
				return false;
			}
		}

		$arReasons = [
			//уважительные причины
			self::REASON_TYPE_WORKER_DISAGREE_STAGES => [
				"name" =>  Translations::t("Несогласие с этапами"),
				"track_status" => "worker_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если не получается прийти к согласию по этапам. Отмена заказа не отразится на завершенных и оплаченных этапах. Оплата по всем незавершенным этапам будет возвращена покупателю."),
			],
			"payer_ordered_by_mistake" => [
				"name" =>  Translations::t("Заказал по ошибке"),
				"is_payer_unrespectful" => false,
				"track_status" => "payer_inprogress_cancel",
				"tooltip" => Translations::t("")
			],
			self::REASON_TYPE_INFLATED_PRICE => [
				"name" =>  Translations::t("Продавец завышает цену"),
				"is_payer_unrespectful" => false,
				"track_status" => "payer_inprogress_cancel",
				"tooltip" => Translations::t("Выбирайте эту причину, если продавец завышает цену.")
			],
			"payer_do_not_like_this_worker" => [
				"name" =>  Translations::t("Сложилось непонимание с продавцом"),
				"is_payer_unrespectful" => false,
				"track_status" => "payer_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если вы не можете договориться об условиях заказа с продавцом.")
			],
			"payer_other_no_guilt" => [
				"name" =>  Translations::t("Другая причина, вины продавца нет"),
				"is_payer_unrespectful" => false,
				"track_status" => "payer_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину если отмена происходит не по вине продавца. Не стоит указывать ее, если отменить заказ хочет сам продавец и просит выбрать именно эту причину.")
			],
			"worker_bad_payer_requirements" => [
				"name" =>  Translations::t("Требования покупателя не соответствуют описанию кворка"),
				"track_status" => "worker_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если покупатель требует выполнить то, что не описано в вашем кворке."),
				"subtypes" => [
					"too_much_work" => [
						"name" => Translations::t("Заказ сделан на услуги, описанные в кворке, но покупатель запрашивает слишком большой объем работы"),
						"track_status" => "worker_inprogress_cancel_request",
						"help" => [
							["type" => "header", "content" => Translations::t("Ваша ситуация")],
							["type" => "paragraph", "content" => Translations::t("Покупатель просит выполнить объем работ, превышающий объем заказанного кворка")],
							["type" => "header", "content" => Translations::t("Что делать?")],
							["type" => "paragraph", "content" => Translations::t('Не стоит отменять заказ. Вы можете предложить покупателю расширить заказ. Для этого вернитесь к заказу и нажмите кнопку "Предложить опции" внизу справа в форме отправки сообщения.')],
						],
						"additional" => Translations::t("* отменить заказ по этой причине можно только если покупатель отказался от покупки дополнительных опций"),
						"allowed" => true,
					],
					"work_doesnt_match_kwork" => [
						"name" => Translations::t("Заказ включает в себя задачи, которые не описаны в кворке и не могут быть выполнены"),
						"track_status" => "worker_inprogress_cancel_request",
						"help" => [
							["type" => "header", "content" => Translations::t("Ваша ситуация")],
							["type" => "paragraph", "content" => Translations::t("Покупатель просит выполнить не то, что указано в кворке")],
							["type" => "header", "content" => Translations::t("Что делать?")],
							["type" => "paragraph", "content" => Translations::t("Если вы можете выполнить данную услугу, рекомендуем вам создать новый кворк с данной услугой и предложить его покупателю.")],
						],
						"allowed" => true,
					],
				],
			],
			"worker_no_payer_requirements" => [
				"name" =>  Translations::t("Покупатель не предоставил всю нужную информацию по заказу"),
				"track_status" => "worker_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если покупатель не предоставил необходимую информацию по заказу, согласно условиям вашего кворка.")
			],
			"worker_no_communication_with_payer" => [
				"name" =>  Translations::t("Нет связи с покупателем"),
				"track_status" => "worker_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если покупатель не отвечает на ваши сообщения долгое время, в связи с чем у вас нет возможности завершить работу над заказом.")
			],

			//неуважительные
			"payer_time_over" => [
				"name" =>  Translations::t("Не выполнено вовремя"),
				"track_status" => "payer_inprogress_cancel",
				"is_payer_unrespectful" => true,
				"tooltip" => Translations::t("Выбирайте эту причину, если продавец не выполнил заказ в отведенный срок.")
			],
			"payer_worker_cannot_execute_correct" => [
				"name" => Translations::t("Продавец не может корректно выполнить заказ"),
				"is_payer_unrespectful" => true,
				"track_status" => "payer_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если продавец не может выполнить заказ качественно и в том объеме, который указан в кворке.")
			],
			"payer_worker_is_busy" => [
				"name" =>  Translations::t("Продавец занят"),
				"is_payer_unrespectful" => true,
				"track_status" => "payer_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, когда продавец не берет заказ в работу, ссылаясь на чрезмерную занятость.")
			],
			"payer_no_communication_with_worker" => [
				"name" =>  Translations::t("Нет связи с продавцом"),
				"is_payer_unrespectful" => true,
				"track_status" => "payer_inprogress_cancel_request",
				"tooltip" => Translations::t("Выбирайте эту причину, если продавец не отвечает на ваши сообщения.")
			],
			"worker_payer_is_dissatisfied" => [
				"name" =>  Translations::t("Сложилось непонимание с покупателем (неуважительная)"),
				"track_status" => "worker_inprogress_cancel",
				"tooltip" => Translations::t("Выбирайте ее, если вы не можете договориться об условиях заказа с покупателем. Ваш рейтинг ответственности будет снижен.")
			],
			"worker_no_time" => [
				"name" =>  Translations::t("Нет времени (неуважительная)"),
				"track_status" => "worker_inprogress_cancel",
				"tooltip" => Translations::t("Выбирайте ее, если не можете выполнить заказ из-за отсутствия времени. Ваш рейтинг ответственности будет снижен.")
			],
			"worker_force_cancel" => [
				"name" =>  Translations::t("Указать другую причину (неуважительную)"),
				"track_status" => "worker_inprogress_cancel",
				"tooltip" => Translations::t("Укажите другую неуважительную причину. Ваш рейтинг ответственности будет снижен.")
			],
		];

		if($id === false) {
			return $arReasons;
		}

		if (strpos($id, "-")) {
			$arr = explode("-", $id);
			return $arReasons[$arr[0]]["subtypes"][$arr[1]] ?: false;
		}
		return $arReasons[$id] ?: false;
	}

	/**
	 * Причины отклонения по ID заказа
	 * @param int $orderId
	 * @deprecated используется лишь в мобильном апи
	 * @return array
	 */
	public static function getCancelReasonsByOrderId(int $orderId): array {
		$order = \Model\Order::find($orderId);
		return self::getCancelReasonsByOrder($order);
	}

	/**
	 * Причины отклонения по заказу
	 * @param \Model\Order $order
	 * @return array
	 */
	public static function getCancelReasonsByOrder(\Model\Order $order): array {
		$arReasons = TrackManager::getCancelReason();

		if ($order->late()) {
			$arReasons["payer_worker_cannot_execute_correct"]["track_status"] = "payer_inprogress_cancel";
			$arReasons["payer_no_communication_with_worker"]["track_status"] = "payer_inprogress_cancel";
		}

		$arAvailReasons = TrackManager::getAvailCancelReasons($order);

		foreach ($arReasons as $key => $reason) {
			if (!in_array($key, $arAvailReasons)) {
				unset($arReasons[$key]);
			}
			if (isset($arReasons[$key]["subtypes"])) {
				foreach ($arReasons[$key]["subtypes"] as $subKey => $subReason) {
					if (!in_array($key . "-" . $subKey, $arAvailReasons)) {
						$arReasons[$key]["subtypes"][$subKey]["allowed"] = false;
					}
				}
			}
		}

		return $arReasons;
	}


	/**
	 * Получить текст сообщения по типу причины для покупателя и продавца
	 *
	 * @param int $mode тип причины
	 * @param bool $isWorker продавец или покупатель
	 * @param int $deadline метка времени, когда истекло время выполнения заказа
	 * @param bool $orderHasPaidStages В заказе есть уже оплаченные этапы
	 * @return string
	 */
	public static function cancelRatingMessage($mode, $isWorker = true, $deadline = 0, bool $orderHasPaidStages = false) {
		if ($mode == self::CANCEL_REASON_NORMAL) {
			if ($orderHasPaidStages) {
				return $isWorker ? Translations::t("Остановка заказа не повлияла на ваш рейтинг") : Translations::t("Остановка заказа не повлияла на рейтинг продавца");
			}
			return $isWorker ? Translations::t("Отмена заказа не повлияла на ваш рейтинг") : Translations::t("Отмена заказа не повлияла на рейтинг продавца");
		} elseif ($mode == self::CANCEL_REASON_BAD_SERVICE) {
			return $isWorker ? Translations::t("Ваш рейтинг ответственности снижен. Отмена заказа приравнена к отрицательному отзыву от покупателя.") : Translations::t("Рейтинг ответственности продавца снижен. Отмена заказа приравнена к отрицательному отзыву от покупателя.");
		} elseif ($mode == self::CANCEL_REASON_BAD_SERVICE_REVIEW) {
			if ($deadline) {
				$orderTimeExpiredDate = \Helper::dateByLang($deadline, "j F", \Translations::getLang());
				$orderTimeExpiredTime = \Helper::dateByLang($deadline, "H:s", \Translations::getLang());
				$orderTimeExpiredString = \Translations::t("Время работы над заказом истекло %s в %s.", $orderTimeExpiredDate, $orderTimeExpiredTime) . " ";
			} else {
				$orderTimeExpiredString = "";
			}
			if ($orderHasPaidStages) {
				if ($isWorker) {
					return $orderTimeExpiredString.Translations::t("Ваш рейтинг ответственности снижен. Остановка заказа приравнена к отрицательному отзыву от покупателя.");
				} else {
					return $orderTimeExpiredString.Translations::t("Рейтинг ответственности продавца снижен. Остановка заказа приравнена к отрицательному отзыву от покупателя.");
				}
			}
			return $isWorker ? $orderTimeExpiredString.Translations::t("Ваш рейтинг ответственности снижен. Отмена заказа приравнена к отрицательному отзыву от покупателя.") : $orderTimeExpiredString.Translations::t("Рейтинг ответственности продавца снижен. Отмена заказа приравнена к отрицательному отзыву от покупателя.");
		} elseif ($mode == self::CANCEL_REASON_BAD_REVIEW) {
			if ($orderHasPaidStages) {
				return Translations::t("Остановка заказа приравнена к отрицательному отзыву от покупателя.");
			}
			return Translations::t("Отмена заказа приравнена к отрицательному отзыву от покупателя.");
		}
		return "";
	}

	public static function getCancelReasonName($track) {
		$track = (array) $track;

		foreach (self::getBadCancelConditions() as $conditionName => $condition) {
			$match = true;
			foreach ($condition as $field => $values) {
				if (in_array($track[$field], $values)) {
					$match = $match && true;
				} else {
					$match = false;
				}
			}
			if ($match) {
				return $conditionName;
			}
		}
		return 0;
	}

	/**
	 * Получение кода отмены заказа по идентификатору заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return int Константа TrackManager::CANCEL_REASON_*
	 */
	public static function getCancelReasonModeByOrderId(int $orderId) {
		$track = self::getLastTrackInfo($orderId);
		return self::getCancelReasonMode($track);
	}

	/**
	 * Является ли исход заказа нормальным по идентификатору
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return bool
	 */
	public static function isCancelReasonModeNormalByOrderId(int $orderId) {
		return self::getCancelReasonModeByOrderId($orderId) == self::CANCEL_REASON_NORMAL;
	}

	/**
	 * Получение типа отмены заказа по массиву данных трека
	 *
	 * @param array $track Массив данных по заказу и последнему треку (можно получить из TrackManager::getLastTrackInfo())
	 * значимые поля type, in_work, late, reason_type
	 *
	 * @return int Константа TrackManager::CANCEL_REASON_*
	 */
	public static function getCancelReasonMode($track) {
		$track = (array) $track;

		$conditionName = self::getCancelReasonName($track);
		if ($conditionName !== 0) {
			if (in_array($conditionName, [self::CONDITION_CRON_INPROGRESS, self::CONDITION_INPROGRESS_CANCEL, self::CONDITION_PAYER_DISAGREE, self::CONDITION_CONFIRM, self::CONDITION_INCORRECT])) {
				return self::CANCEL_REASON_BAD_SERVICE;
			} elseif (in_array($conditionName, [self::CONDITION_TIME_OVER, self::CONDITION_INWORK_LATE_INCORRECT, self::CONDITION_INCORRECT_AND_LATE, self::CONDITION_NO_COMMUNICATION_AND_LATE])) {
				return self::CANCEL_REASON_BAD_SERVICE_REVIEW;
			}
		} elseif ($track["in_work"] && !$track["late"] && in_array($track["type"], ["worker_inprogress_cancel_confirm", "cron_payer_inprogress_cancel"]) && $track["reason_type"] == "payer_worker_cannot_execute_correct") {
			return self::CANCEL_REASON_BAD_REVIEW;
		}
		return self::CANCEL_REASON_NORMAL;
	}

	public static function getLastTrackInfo($orderId) {
		$query = "SELECT 
						t.MID,
						t.type,
						t.reason_type,
						t.reply_type,
						t.message,
						o.in_work,
						case
							when (o.deadline IS NULL OR
									o.deadline > unix_timestamp(case
																	when o.status = :status_done
																		then o.date_done
																	when o.status = :status_cancel
																		then o.date_cancel
																	else NOW()
																end))
									then 0
							else 1
							end as 'late'
					FROM " . OrderManager::TABLE_NAME . " o
					JOIN " . self::TABLE_NAME . " t ON t.MID = o.last_track_id
					WHERE o.OID = :order_id";
		return App::pdo()->fetch($query, [
			"order_id"	=> $orderId,
			"status_cancel"	=> OrderManager::STATUS_CANCEL,
			"status_done"	=> OrderManager::STATUS_DONE
		]);
	}

	/**
	 * Получить плохие причины отказа от заказа для разных статусов
	 *
	 * @param bool|int $name статус заказа
	 * @return array|mixed
	 */
	public static function getBadCancelConditions($name = false) {
		$data = [
			//отмена заказа заказчиком из-за просрочки
			self::CONDITION_TIME_OVER	=> [
				"late"			=> [1],
				"type"			=> ["payer_inprogress_cancel"],
				"reason_type"	=> ["payer_time_over"]
			],
			//отмена заказа кроном из-за невзятия в работу
			self::CONDITION_CRON_INPROGRESS   => [
				"type"			=> ["cron_inprogress_cancel", "admin_arbitrage_cancel", "cron_inprogress_inwork_cancel", Type::CRON_RESTARTED_INPROGRESS_CANCEL],
			],
			//вынужденная отмена заказа продавцом
			self::CONDITION_INPROGRESS_CANCEL => [
				"type"			=> ["worker_inprogress_cancel"]
			],
			//покупатель согласен с отменой заказа, но не согласен с причиной
			self::CONDITION_PAYER_DISAGREE        => [
				"reason_type"	=> ["worker_bad_payer_requirements", "worker_payer_ordered_by_mistake", "worker_no_payer_requirements"],
				"reply_type"	=> [TrackManager::REPLY_TYPE_DISAGREE]
			],
			//покупатель запросил отмену заказа и заказ был отменен
			self::CONDITION_CONFIRM               => [
				"type"			=> ["worker_inprogress_cancel_confirm", "cron_payer_inprogress_cancel"],
				"reason_type"	=> ["payer_worker_is_busy", "payer_no_communication_with_worker", "payer_worker_is_busy"]
			],
			//заказ был взят в работу, просрочен и отменен по причине некорректности
			self::CONDITION_INWORK_LATE_INCORRECT => [
				"in_work"		=> [1],
				"late"			=> [1],
				"type"			=> ["worker_inprogress_cancel_confirm", "cron_payer_inprogress_cancel"],
				"reason_type"	=> ["payer_worker_cannot_execute_correct"]
			],
			//заказ не был взят в работу и отменен по причине некорректности
			self::CONDITION_INCORRECT => [
				"in_work"		=> [0],
				"type"			=> ["worker_inprogress_cancel_confirm", "cron_payer_inprogress_cancel"],
				"reason_type"	=> ["payer_worker_cannot_execute_correct"]
			],
			self::CONDITION_INCORRECT_AND_LATE => [
				"late"			=> [1],
				"type"			=> ["payer_inprogress_cancel"],
				"reason_type"	=> ["payer_worker_cannot_execute_correct"]
			],
			self::CONDITION_NO_COMMUNICATION_AND_LATE => [
				"in_work"		=> [1],
				"late"			=> [1],
				"type"			=> ["payer_inprogress_cancel"],
				"reason_type"	=> ["payer_no_communication_with_worker"]
			],
		];
		if ($name && isset($data[$name])) {
			return $data[$name];
		}
		return $data;
	}

    public static function getBadCancelNameSql($cancelReason = false, $trackAlias = "t", $orderAlias = "o") {
        $sql = "";
        $conditions = self::getBadCancelConditions($cancelReason);
        if ($cancelReason !== false) {
            $conditions = [ $conditions ];
        }
        foreach ($conditions as $condition) {
            if ($sql != "") {
                $sql .= " OR ";
            }
            $subBadReasonQuery = "";
            foreach ($condition as $field => $values) {
                if ($subBadReasonQuery != "") {
                    $subBadReasonQuery .= " AND ";
                }
                if ($field == "in_work") {
                    $whereField = $orderAlias . "." . $field;
                } elseif ($field == "late") {
                    $whereField = "IF(" . $orderAlias . ".deadline IS NULL OR " . $orderAlias . ".deadline > unix_timestamp(), 0, 1)";
                } else {
                    $whereField = $trackAlias . ".". $field;
                }
                $subBadReasonQuery .= $whereField . " IN ('" . implode("','", $values) . "')";
            }
            $sql .= $subBadReasonQuery;
        }
        return $sql;
    }

	/**
     * Получить все причины отмены по типу пользователя
     * @param string $userType - тип пользователя
     * @return array
     */
    public static function getCancelReasonUserType($userType)
    {
        $arReasons = self::getCancelReason();
        $arrayReasonsOnUser = [];
        if (in_array($userType, [UserManager::TYPE_PAYER, UserManager::TYPE_WORKER])) {
            foreach ($arReasons as $key => $reason) {
                if (substr($key, 0, strlen($userType)) == $userType) {
                    $arrayReasonsOnUser[$key] = $reason;
                }
            }
        }
        return $arrayReasonsOnUser;
    }

	/**
	 * Получить доступные причины отмены заказа
	 *
	 * @param \Model\Order $order - модель заказа
	 * @return array
	 */
	public static function getAvailCancelReasons(\Model\Order $order) {

		$userId = UserManager::getCurrentUserId();

		//Найдем последнее сообщение собеседника того, кто хочет отменить заказ
		$lastMessageFrom = $order->tracks->where(Track::FIELD_USER_ID, "=", $userId == $order->USERID ? $order->worker_id : $order->USERID)
			->sortByDesc(Track::FIELD_ID)
			->first();
		if (!empty($lastMessageFrom)){
			$lastMessageFrom = $lastMessageFrom->only([Track::FIELD_ID, Track::FIELD_DATE_CREATE, Track::FIELD_USER_ID, Track::FIELD_TYPE]);
			$lastMessageFrom["date_create"] = strtotime($lastMessageFrom["date_create"]);
		}

		$lastMessageMine = $order->tracks->where(Track::FIELD_USER_ID, "=", $userId)
			->sortByDesc(Track::FIELD_ID)
			->first();
		if (!empty($lastMessageMine)) {
			$lastMessageMine = $lastMessageMine->only([Track::FIELD_ID, Track::FIELD_DATE_CREATE, Track::FIELD_USER_ID, Track::FIELD_TYPE]);
			$lastMessageMine["date_create"] = strtotime($lastMessageMine["date_create"]);
		}
		if (empty($lastMessageFrom)) {
			$lastMessageFrom = [
				"MID" => 0,
				"date_create" => $order->stime,
				"user_id" => $userId == $order->USERID ? $order->worker_id : $order->USERID
			];
		}

		if (empty($lastMessageMine)) {
			$lastMessageMine = [
				"MID" => 0,
				"date_create" => $order->stime,
				"user_id" => $userId
			];
		}

		if ($lastMessageFrom["MID"] < $lastMessageMine["MID"]) {
			//Если последнее сообщение From было до последнего сообщения Mine, найдем первое сообщение Mine после последнего сообщения From
			$lastMessage = $order->tracks->where(Track::FIELD_USER_ID, "=", $lastMessageMine["user_id"])
				->where(Track::FIELD_ID, ">", $lastMessageFrom["MID"])
				->sortBy(Track::FIELD_ID)
				->first();
			if (!empty($lastMessage)) {
				$lastMessage = $lastMessage->only([Track::FIELD_ID, Track::FIELD_DATE_CREATE]);
				$lastMessage["date_create"] = strtotime($lastMessage["date_create"]);
			}

			if (empty($lastMessage)) {
				$lastMessageDiff = time() - $order->stime;
			} else {
				$lastMessageDiff = time() - $lastMessage["date_create"];
			}
		} else {
			$lastMessageDiff = false;
		}
		$orderTimeDiff = time() - $order->stime;

		$lastTrack = $order->tracks
			->sortByDesc(Track::FIELD_ID)
			->first();
		$lastTrackType = $lastTrack->type;

		$arAvailReasons = [];

		if ($userId == $order->worker_id &&
			!$order->isDone() &&
			!$order->isCancel() &&
			$order->has_stages &&
			(
				$order->has_payer_stages ||
				OrderStageManager::isOrderStagesWithFreshUnacceptedPayerChanges($order)
			)
		) {
			$arAvailReasons[] = self::REASON_TYPE_WORKER_DISAGREE_STAGES;
		}

		if ($userId == $order->USERID) {
			$availableOrderStatuses = OrderManager::getCancelAvailableStatusesForPayer();
		} else {
			$availableOrderStatuses = OrderManager::getCancelAvailableStatusesForWorker();
		}
		$arTrackTypes = ['payer_inprogress_cancel_request', 'worker_inprogress_cancel_request'];
		if (!in_array($order->status, $availableOrderStatuses) || in_array($lastTrackType, $arTrackTypes)) {
			return $arAvailReasons;
		}

		if ($userId == $order->USERID) { //buyer
			if ($orderTimeDiff <= 20 * Helper::ONE_MINUTE) {//Прошло менее 20 минут
				$arAvailReasons[] = "payer_ordered_by_mistake";
			} else {
				if ($orderTimeDiff >= 6 * Helper::ONE_HOUR) {   //Прошло более 6 часов
					$arAvailReasons[] = "payer_worker_cannot_execute_correct";
				}
				if ($order->late()) {
					$arAvailReasons [] = "payer_time_over";
				}
				if ($lastMessageDiff !== false && $lastMessageDiff >= 24 * Helper::ONE_HOUR) {
					$arAvailReasons[] = "payer_no_communication_with_worker";
				}
				if (!$order->in_work) {
					$arAvailReasons [] = "payer_worker_is_busy";
				}
				$arAvailReasons[] = "payer_do_not_like_this_worker";
				$arAvailReasons[] = "payer_other_no_guilt";

				if (!empty($lastMessageFrom["MID"]) && $lastMessageFrom["date_create"] > $order->stime && !$order->in_work && !self::getNotInflatedPriceFlag($order->PID)) {
					//Если было нескопированное из лички сообщение от продавца, заказ не взят в работу и админы не отклоняли причину два раза, доступна отмена по завышению цены
					$arAvailReasons[] = self::REASON_TYPE_INFLATED_PRICE;
				}
			}
		} elseif ($userId == $order->worker_id) { //seller
			if ($order->status != OrderManager::STATUS_ARBITRAGE) {
				if (!$order->in_work) {
					$arAvailReasons[] = "worker_bad_payer_requirements";
					$arAvailReasons[] = "worker_no_payer_requirements";
				}
				if ($lastMessageDiff !== false && $lastMessageDiff >= 24 * Helper::ONE_HOUR) {
					$arAvailReasons[] = "worker_no_communication_with_payer";
				}
			}
			$arAvailReasons[] = "worker_payer_is_dissatisfied";
			$arAvailReasons[] = "worker_no_time";
			$arAvailReasons[] = "worker_force_cancel";
		}

		if (in_array("worker_bad_payer_requirements", $arAvailReasons)) {
			//Требования покупателя не соответствуют описанию кворка
			//Проверка возможности указать подпричину "Заказ сделан на услуги, описанные в кворке, но покупатель запрашивает слишком большой объем работы"
			$hasTrack = (bool)$order->tracks
				->where(Track::FIELD_TYPE, "=", Type::EXTRA)
				->filter(function(Track $track) {
					return $track->isClose() ||
						($track->isNew() && strtotime($track->date_create) < strtotime("now - 1 hour"));
				})->count();

			if ($hasTrack) {
				$arAvailReasons[] = "worker_bad_payer_requirements-too_much_work";
			}
			$arAvailReasons[] = "worker_bad_payer_requirements-work_doesnt_match_kwork";
		}

		return $arAvailReasons;
	}

	/**
	 * Получаем статус кворка по причине отмены заказа "Завышение цены"
	 *
	 * @param int $kworkId Идентификатор кворка
	 * @return bool TRUE, если админы отклонили причину отмены заказа "Завышение цены" два раза
	 */
	public static function getNotInflatedPriceFlag($kworkId) {
		return true;
	}

	/**
	 * Доступен ли статус трека для пользователя
	 *
	 * @param string $trackType тип трека
	 * @param \Model\Order $order заказ
	 * @param string $reasonType Тип причины отмены
	 *
	 * @return bool
	 */
	public static function checkCancelTrackStatus($trackType, \Model\Order $order, $reasonType)
	{
		// Причину отмены заказа «Продавец не может корректно выполнить заказ» заставляем работать по новому принципу,
		// а именно, если заказ просрочен, то выбор этой причины приводит к моментальной отмене заказа без согласования с продавцом
		//
		// Причина "Нет связи с продавцом" должна быть выделена жирной и моментально приводить к отмене заказа, ЕСЛИ ЗАКАЗ ПРОСРОЧЕН
		if ($trackType == "payer_inprogress_cancel" && in_array($reasonType, TrackManager::getImmidiateCancelAfterLateReasons())) {
			if ($order->late()) {
				return true;
			}
		}

		$reasons = self::getCancelReason();
		if(!$reasons) {
			return false;
		}

		$arAvailReasons = self::getAvailCancelReasons($order);
		if(!$arAvailReasons) {
			return false;
		}

		foreach ($arAvailReasons as $availReason) {
			if($reasonType == $availReason && $reasons[$availReason]['track_status'] == $trackType) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверяет, может ли пользователь оставить отзыв
	 * @param int $orderId id заказа, по которому надо оставить отзыв
	 *
	 * @return bool
	 */
	public static function canAddReview($orderId)
	{
		global $conn;

		$availType = RatingManager::getAvailReviewType($orderId);

		if(!$availType) {
			return false;
		}

		$hasReview = $conn->getCell("SELECT r.RID as 'id' FROM ratings r WHERE r.OID = '" . mres($orderId) . "' AND r.auto_mode IS NULL LIMIT 1");
		if($hasReview) {
			return false;
		}

		return $availType;
	}

	public static function isDoneConvAllow(\stdClass $order, \stdClass $kwork, bool $allowMessageForTrack): bool {
		global $actor;

		$contractor = $actor->id == $order->userId ? $kwork->userId : $order->userId;
		$allowMessageForConv = PrivateMessageManager::checkAvailableCached($contractor);
		return $allowMessageForTrack || $allowMessageForConv === true;
	}

	public static function currentUserCanWriteMessage($order, $kwork) {
		global $actor;

		$canWriteMessage = false;

		if($actor->id == $order->userId || $actor->id == $kwork->userId){
			$writableStatuses = [
				\OrderManager::STATUS_INPROGRESS,
				\OrderManager::STATUS_CHECK,
				\OrderManager::STATUS_ARBITRAGE,
			];

			$orderConvAllow = \TrackManager::isDoneConversationAllow($order);

			$canWriteMessage = in_array($order->status, $writableStatuses) ||
				($orderConvAllow && $actor->id == $order->userId);
		}

		return $canWriteMessage;
	}

	public static function currentPayerCanWriteToWorker(int $orderStatus, int $orderUserId, int $workerId): bool {
		global $actor;
		if(OrderManager::STATUS_DONE == $orderStatus && $orderUserId == $actor->id) {
			return true;
		}

		return false;
	}

	/**
	 * Можно ли писать в завершенном заказе: если прошло менее 2-х недель с момента завершения заказа или последнего сообщения
	 * @param object $order заказ
	 * @return bool
	 */
	public static function isDoneConversationAllow($order)
	{
		global $conn;

		// если статус не done, то нет
		if($order->status != OrderManager::STATUS_DONE)
			return false;

		$period = 2 * Helper::ONE_WEEK;

		// если заказ принят менее 2-х недель назад, то да
		if(strtotime($order->date_done) > time() - $period)
			return true;

		// если последнее сообщение менее 2-х недель назад, то да
		{
			$lastTextTrack = $conn->getEntity("SELECT MID as 'id', date_create FROM track WHERE OID = '" . mres($order->id) . "' and type = 'text' ORDER BY id desc LIMIT 1");
			if($lastTextTrack && strtotime($lastTextTrack->date_create) > time() - $period)
				return true;
		}

		return false;
	}

	public static function updateOrderExtraDuration($orderId)
	{
		$order = Order::find($orderId);

		global $conn;
		$query = "SELECT oe.extra_id, SUM(oe.count) cnt, SUM(oe.extra_duration) sum_duration 
                        FROM order_extra oe
                        WHERE 
                        oe.order_id = '" . mres($orderId) . "' 
                        AND oe.status = 'done'
                        GROUP BY extra_id";
		$orderedExtras = $conn->getList($query);

		$query = "SELECT oe.id, e.EID, e.duration, oe.count, e.is_volume 
                        FROM order_extra oe
                        JOIN extras e ON e.EID = oe.extra_id
                        WHERE 
                        oe.status = 'new' AND
                        oe.order_id = '" . mres($orderId) . "'";
		$extrasForUpdate = $conn->getList($query);

		foreach ($extrasForUpdate as $updExtra) {
			if($updExtra->is_volume) {
				$oldKworkDays = OrderManager::getDuration($order->kwork_days, $order->count, $order->data->category);
				$newKworkDays = OrderManager::getDuration($order->kwork_days, $updExtra->count + $order->count, $order->data->category);
				$updExtra->duration = $newKworkDays - $oldKworkDays;
			} else {
				$orderedCount = 0;
				$orderedDuration = 0;
				if(!empty($orderedExtras[$updExtra->EID])) {
					$orderedCount = $orderedExtras[$updExtra->EID]->cnt;
					$orderedDuration = $orderedExtras[$updExtra->EID]->sum_duration;
				}
				$updExtra->duration = OrderManager::getDuration($updExtra->duration, $orderedCount + $updExtra->count, $order->data->category);
				$updExtra->duration -= $orderedDuration;
			}
			$query = "UPDATE order_extra SET extra_duration = '" . mres($updExtra->duration) . "' WHERE id = '" . mres($updExtra->id) . "'";
			$conn->execute($query);
		}
		return true;
	}

	//Получить данные заказа для track
	public static function getTrackOrderData($orderId)
	{
		$order = Order::find($orderId);

		$query = "SELECT 
				    USERID as payer, 
					OID, 
					PID, 
					count, 
					duration, 
					deadline, 
					stime, 
					crt, 
					price as totalprice, 
					portfolio_type as order_portfolio_type, 
					status, 
					is_quick,
					kwork_days,
					case when (deadline IS NULL OR deadline > unix_timestamp()) then 0 else 1 end as late, 
					currency_id, 
					currency_rate
				FROM orders WHERE OID = :orderId LIMIT 1";
		$orderData = App::pdo()->fetch($query, ["orderId" => $orderId]);
		if ($orderData) {
			$orderData["totaldays"] = OrderManager::getDuration($orderData["kwork_days"], $orderData["count"], $order->data->category);
			self::fillOrderData($orderData);
			return $orderData;
		} else {
			return FALSE;
		}
	}

    //Заполнение данных заказа из таблицы posts (данные кворка)
    private static function fillOrderPostsData(&$order)
    {

        $kworkId = $order["PID"];
        if ($kworkId > 0) {
            $kworkData = (object) KworkManager::getKworkData($kworkId);
            if ($kworkData) {
                $order['gtitle'] = $kworkData->gtitle;
                $order['price'] = $kworkData->price;
                $order['ctp'] = $kworkData->ctp;
                $order['photo'] = $kworkData->photo;
                $order['owner'] = $kworkData->USERID;
                $order['days'] = $kworkData->days;
                $order['ginst'] = $kworkData->ginst;
                $order['catId'] = $kworkData->category;
	            $order['kwork_time_added'] = $kworkData->time_added;
	            $order['kwork_lang'] = $kworkData->lang;
            }
            unset($kworkData);
        }
    }

	//Заполнение данных заказа из таблицы members (продавец, покупатель)
	private static function fillOrderMembersData(&$order)
	{
		$ownerId = $order['owner'];
		$payerId = $order['payer'];
		if($ownerId > 0 && $payerId > 0) {
			UserManager::preLoadInfo([$ownerId, $payerId]);
			//Продавец
			$owner = UserManager::getUserData($ownerId);
			if(!empty($owner)) {
				$order['username'] = $owner['username'];
				$order['sellername'] = $owner['fullname'];
				$order['sellernameen'] = $owner['fullnameen'];
				$order['owner_live_date'] = $owner['live_date'];
				$order['ownerpp'] = $owner['profilepicture'];
			}
			unset($owner);

			//Покупатель
			$payer = UserManager::getUserData($payerId);
			if($payer) {
				$order['buyer'] = $payer['username'];
				$order['buyername'] = $payer['fullname'];
				$order['buyer_live_date'] = $payer['live_date'];
				$order['buyerpp'] = $payer['profilepicture'];
				$order['portfolio_type'] = $payer['portfolio_type'];
			}
			unset($payer);
		}
	}

	//Заполнение данных заказа из таблицы categories
    private static function fillOrderCatsData(&$order)
    {
        $catId = $order['catId'];
        if ($catId > 0) {
			$catData = CategoryManager::getCategoryFromCasheById($catId);
            if ($catData) {
                $order["seo"] = $catData["seo"];
                $order["category_name"] = $catData["name"];
                $order["is_allow_mirror_category"] = $catData["allow_mirror"];
                $order["cat_portfolio_type"] = $catData["portfolio_type"];
	            $order["cat_custom_offer"] = $catData["custom_offer"];
            }
        }
    }

	//Заполнение данных заказа из таблицы order_package
	private static function fillOrderPackageData(&$order)
	{
		$orderId = $order['OID'];
		if($orderId > 0) {
			$query = "SELECT price, price_ctp FROM order_package WHERE order_id = :orderId LIMIT 1";
			$packageData = App::pdo()->fetch($query, ["orderId" => $orderId], PDO::FETCH_OBJ);

			$sql = "SELECT *
					FROM " . ODM::TABLE_NAME . "
					WHERE " . ODM::F_ORDER_ID . " = :" . ODM::F_ORDER_ID;
			$orderData = App::pdo()->fetch($sql, [ODM::F_ORDER_ID => $orderId], PDO::FETCH_OBJ);

			if($packageData) {
				$order['kworkspayerprice'] = sprintf("%.2f", Helper::moneyRound($order['count'] * $packageData->price));
				$order['kworksworkerprice'] = sprintf("%.2f", Helper::moneyRound($order['count'] * $packageData->price_ctp));
			} else {
				$kworkPrice = ($orderData->kwork_price) ? $orderData->kwork_price : $order['price'];
				$kworkCtp = ($orderData->kwork_ctp) ? $orderData->kwork_ctp : $order['ctp'];
				$order['kworkspayerprice'] = sprintf("%.2f", Helper::moneyRound($order['count'] * $kworkPrice));
				$order['kworksworkerprice'] = sprintf("%.2f", Helper::moneyRound($order['count'] * ($kworkPrice - $kworkCtp)));
			}
		}
	}

	//Заполнение данных заказа, удаление ненужных данных образовавшихся в процессе
	private static function fillOrderData(&$order)
	{
		self::fillOrderPostsData($order);
		self::fillOrderMembersData($order);
		self::fillOrderCatsData($order);
		self::fillOrderPackageData($order);
		unset($order['ctp'], $order['catId'], $order['payer']);
	}

	/**
	 * Получить записи трека
	 * @param int $orderId Код заказа
	 * @return array Записи трека pdo()->fetchAllNameByColumn (key=MID)
	 */
	public static function getOrder($orderId)
	{
		$orderId = intval($orderId);
		if(!isset(self::$orderTracks[$orderId])) {
			$sql = 'SELECT *, 1 is_track
				FROM track 
				WHERE 
					OID=:id
				ORDER BY MID';
			self::$orderTracks[$orderId] = App::pdo()->fetchAllNameByColumn($sql, 'MID', ['id' => $orderId]);
		}

		return self::$orderTracks[$orderId];
	}

	/**
	 * @param $order
	 */
	public static function setOrderTrack($order){
		self::$orderTracks[$order->OID] = $order->tracks->keyBy(Track::FIELD_ID)->toArray();
	}

	/**
	 * Получить ограничение на максимальное количество прикрепленных к треку файлов
	 *
	 * @return int Максимальное количество прикрепленных файлов
	 */
	public static function getFileLimit() {
		return App::config("track.files.max_count") ?: App::config("files.max_count");
	}

	/**
	 * Прикрепляет файлы к сообщению в треке
	 * Обязательный порядок: 1 - обработка удаляемых файлов 2 - обработка добавленных файлов
	 * иначе при добавлении мы можем не уложиться в лимит файлов
	 * @param $trackId - ID трека
	 * @param $files - Массив файлов, полученный из функции post()
	 * @param string $type - Тип сущности, к которой нужно прикрепить файлы (трек либо черновик трека)
	 */
	public static function attachUploadedFiles($trackId, $files, $type = File::ENTITY_TYPE_TRACK)
	{
		if($trackId > 0 && !empty($files)) {
			$deleteMessageFiles = array_key_exists('delete', $files) ? $files['delete'] : [];
			FileManager::deleteEntityFilesByType($trackId, $deleteMessageFiles, [$type]);

			$newMessageFiles = array_key_exists('new', $files) ? $files['new'] : [];
			FileManager::saveFiles($trackId, $newMessageFiles, $type, self::getFileLimit());
		}
	}

	/**
	 * Получить id менежеров, которые последними отписывались в переданных заказах
	 * @param $orderIds
	 * @return array|false
	 */
	public static function getLastSupportMessageByOrders($orderIds)
	{
		$params = [];

		$sql = "SELECT 
                    max(" . self::FIELD_ID . ")
                FROM 
                    " . self::TABLE_NAME . "
                WHERE " . self::FIELD_ORDER_ID . " in (" . App::pdo()->arrayToStrParams($orderIds, $params) . ")
                    AND " . self::FIELD_TYPE . " = '" . \Track\Type::TEXT . "' 
                    AND " . self::FIELD_USER_ID . " = " . App::config('kwork.user_id') . "
                GROUP BY " . self::FIELD_ORDER_ID;

		$mids = App::pdo()->fetchAllByColumn($sql, 0, $params);
		if(!$mids) {
			return [];
		}

		$params = [];
		$sql = "SELECT 
                    " . self::FIELD_ORDER_ID . " as 'orderId',
                    " . self::FIELD_SUPPORT_ID . " as 'supportId'
                FROM 
                    " . self::TABLE_NAME . "
                WHERE " . self::FIELD_ID . " in (" . App::pdo()->arrayToStrParams($mids, $params) . ")";

        return App::pdo()->fetchAllAssocPair($sql, 'orderId', 'supportId', $params);
    }

    public static function getReview($orderId) {
    	global $conn, $actor;

		$review = $conn->getEntity("SELECT r.RID as 'id', r.good, r.bad, r.time_added, r.time_added date_create_unix, r.comment, m.username, r.RATER as rater, 0 is_track, r.auto_mode FROM ratings r JOIN members m ON r.RATER = m.USERID WHERE r.OID = '" . mres($orderId) . "' LIMIT 1");
		if($review) {
			$review->comment = replace_full_urls($review->comment);
		}
		if($review) {
			$review_comment = $conn->getEntity("SELECT * FROM rating_comment WHERE review_id = '" . mres($review->id) . "'");
			if ($actor->id == $review_comment->user_id || $review_comment->status == 'active') {
				if($review_comment) {
					$review_comment->message = replace_full_urls($review_comment->message);
					$review->answer = $review_comment;
				}
			}
		}
		return $review;
	}

    /**
     * Получение заказанных опций для заказа
	 *
     * @param int $orderId Идентификатор заказа
     * @return array ["needHideAddExtrasList" => ..., "orderedExtras" => ..., "extraPrices" => ...]
     */
	public static function getOrderedExtras($orderId) {

		$query = "SELECT oe.id,
						 oe.extra_id as'EID',
						 oe.count,
						 oe.extra_title,
						 oe.extra_price, 
						 oe.extra_price*oe.count as BPrice, 
						 (oe.extra_price - oe.extra_ctp)*oe.count as WPrice, 
						 oe.worker_total_price,
						 oe.extra_duration as totaldays, 
						 o.currency_id, 
						 o.currency_rate 
				FROM order_extra oe 
				LEFT JOIN track_extra te ON te.order_extra_id = oe.id
				LEFT JOIN track t ON t.MID = te.track_id
				LEFT JOIN orders o ON o.OID = oe.order_id
				WHERE (t.MID is NULL OR t.status = 'done') 
				AND oe.extra_is_volume = 0 
				AND oe.order_id = :orderId 
				AND oe.status != 'delete'";

		$orderedExtras = App::pdo()->fetchAll($query, ["orderId" => $orderId]);

		$needHideAddExtrasList = false;
		$extraPrices = ["payer" => 0, "worker" => 0];

		if ($orderedExtras) {
			foreach($orderedExtras as $key=>$extra) {
				foreach($orderedExtras as $key2=>$extra2) {
					if($orderedExtras[$key]['EID'] == $orderedExtras[$key2]['EID'] && $key != $key2) {
						$orderedExtras[$key]['count']+=$orderedExtras[$key2]['count'];
						$orderedExtras[$key]['BPrice']+=$orderedExtras[$key2]['BPrice'];
						$orderedExtras[$key]['WPrice']+=$orderedExtras[$key2]['WPrice'];
						$orderedExtras[$key][\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE]+=$orderedExtras[$key2][\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE];
						$orderedExtras[$key]['totaldays']+=$orderedExtras[$key2]['totaldays'];
						unset($orderedExtras[$key2]);
						$needHideAddExtrasList = true;
					}
				}
				$extraPrices['payer'] += $orderedExtras[$key]['BPrice'];
				$extraPrices['worker'] += $orderedExtras[$key]['WPrice'];
			}
			foreach ($orderedExtras as $key => $extra) {
				if ($extra[\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE]) {
					$orderedExtras[$key]["WPrice"] = $extra[\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE];
				}
			}
		}

		$order = OrderManager::getCached($orderId);

		if ($order->stime < time() - self::HIDE_EXTRA_LIST_TRESHOLD){
			$needHideAddExtrasList = true;
		}

		return [
			"needHideAddExtrasList" => $needHideAddExtrasList,
			"orderedExtras" => array_values($orderedExtras),
			"extraPrices" => $extraPrices,
		];
	}

    /**
     * Получение заказанных опций для заказа
	 *
     * @param int $orderId Идентификатор заказа
     *
     * @return array Опции заказа в треке
     */
	public static function getTrackExtras($orderId) {
		$query = "SELECT 
					oe.extra_id, 
					oe.count, 
					oe.extra_title, 
					oe.extra_price * oe.count as BPrice, 
					(oe.extra_price - oe.extra_ctp) * oe.count as WPrice, 
					oe.extra_duration as totaldays,
					oe.worker_total_price,
					oe.volume, 
					te.track_id
				FROM order_extra oe
				JOIN track_extra te ON te.order_extra_id = oe.id
				JOIN track t ON te.track_id = t.MID
				WHERE t.OID = :orderId";



		$extras = App::pdo()->fetchAll($query, ["orderId" => (int)$orderId], PDO::FETCH_OBJ);
		if (is_array($extras)) {
			foreach ($extras as $key => $extra) {
				if ($extra->{\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE}) {
					$extra->WPrice = $extra->{\Model\OrderExtra::FIELD_WORKER_TOTAL_PRICE};
				}
			}
		}

		return $extras;
	}


    /**
     * Определение есть ли запрос на отмену среди представленных треков
	 *
     * @param array $tracks Треки
     * @return boolean
     */
	public static function tracksCancelDetect($tracks) {
		foreach ($tracks as $track) {
			if (($track->type == 'worker_inprogress_cancel_request' || $track->type == 'payer_inprogress_cancel_request') && $track->status == 'new') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Получение всех файлов, относящихся к заказу.
	 *
	 * @param int $orderId
	 * 	Идентификатор заказа
	 *
	 * @return array
	 * 	Массив объектов файлов
	 */
	public static function getTrackFiles(int $orderId): array {
		$files = &kwork_static(__FUNCTION__ . $orderId);
		if (!isset($files)) {
			$sql = "SELECT 
				f.FID AS 'id', 
				f.fname, 
				f.s, 
				f.USERID AS userid, 
				t.MID AS 'trackId' 
			FROM files f 
			JOIN track t ON t.MID = f.entity_id AND f.entity_type = 'track' 
			WHERE t.OID = :orderId";

			$files = App::pdo()->getList($sql, ["orderId" => $orderId]);
		}

		return $files;
	}

	/**
	 * Заполнение треков файлами
	 *
	 * @param array $tracks Массив для заполнения
	 * @param array $files Массив файлов
	 */
	public static function fillTrackFiles(array &$tracks, array $files) {
		if(!empty($files)){
			foreach ($tracks as $track) {
				$track->files = [];
				foreach ($files as $file) {
					if ($file->trackId == $track->id) {
						$track->files[] = $file;
					}
				}
			}
		}
	}

	/**
	 * Заполнение треков опциями
	 *
	 * @param int $orderId Иднетификатор заказа
	 * @param array $tracks Массив для заполнения
	 */
	public static function fillTrackExtras($orderId, $tracks) {
		if (!empty($tracks)) {
			$extras = TrackManager::getTrackExtras($orderId);

			$trackIds = array_column($tracks, "id");
			$upgradeTracks = UpgradePackageExtra::whereIn(UpgradePackageExtra::FIELD_TRACK_ID, $trackIds)->get();

			foreach ($tracks as $track) {
				$track->upgradePackageExtra = $upgradeTracks->firstWhere(UpgradePackageExtra::FIELD_TRACK_ID, $track->id);
				$track->extras = [];
				if ($extras) {
					foreach ($extras as $extra) {
						if ($extra->track_id == $track->id) {
							$track->extras[] = $extra;
						}
					}
				}
			}
		}
	}

	/**
	 * Заполнение треков причинами отказов
	 *
	 * @param array $tracks Массив для заполнения
	 */
	public static function fillTrackReasons($tracks) {
		foreach($tracks as $track)
		{
			if ($track->{TrackManager::FIELD_PREV_REASON_TYPE}) {
				$trackType = TrackManager::getCancelReason($track->{TrackManager::FIELD_PREV_REASON_TYPE})["track_status"];
				if ($trackType == "payer_inprogress_cancel_request") {
					$track->type = "worker_inprogress_cancel_confirm";
				} elseif ($trackType == "worker_inprogress_cancel_request") {
					$track->type = "payer_inprogress_cancel_confirm";
				} elseif($trackType) {
					$track->type = $trackType;
				}
				$track->reason_type = $track->{TrackManager::FIELD_PREV_REASON_TYPE};
				$track->cancel_reason = TrackManager::getCancelReason($track->{TrackManager::FIELD_PREV_REASON_TYPE});
			} else {
				$track->cancel_reason = TrackManager::getCancelReason($track->reason_type);
			}
		}
	}

	/**
	 * Покупатель принимает предложенные опции
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $trackId Идентификатор трека с предложенными опциями
	 *
     * @return array|false Сообщение об успехе или ошибке
	 */
	public static function payerApproveExtras($orderId, $trackId) {

		$actor = UserManager::getCurrentUser();

		if ($orderId < 1 || $trackId < 1) {
			return false;
		}
		$order = \Model\Order::find($orderId);

		if ($order->has_stages && $order->stages && $order->stages->count() > 1) {
			throw new SimpleJsonException(Translations::t("Покупка опций в заказах с этапами запрещена"));
		}

		if (empty($order) || $actor->id != $order->USERID || $order->isNotInBuyStatus()) {
			return false;
		}

		$track = \Model\Track::with(["extras" => function($query) {
			/**
			 * @var \Illuminate\Database\Eloquent\Builder $query
			 */
			$query->where(\Model\OrderExtra::FIELD_STATUS, \Model\OrderExtra::STATUS_NEW);
		}])->find($trackId);

		if (is_null($track) || $track->OID != $orderId) {
			return false;
		}

		$currencyId = $order->currency_id;
		$currencyRate = \Currency\CurrencyExchanger::getInstance()->getCurrencyRateByCurrencyId($currencyId);
		$workerLang = $order->worker->lang;
		$orderLang = \Translations::getLangByCurrencyId($currencyId);

		$sum = 0;
		$workerSum = 0;
		$plusDays = 0;
		$increaseOrderVolume = false;
		foreach ($track->extras as $extra) {
			$sum += $extra->count * $extra->extra_price;
			$workerSum += $extra->worker_total_price;
			$plusDays += $extra->extra_duration * Helper::ONE_DAY;
			if ($extra->extra_is_volume == 1) {
				$increaseOrderVolume = $extra;
			}

			$workerAmount = $extra->worker_total_price;
			if($workerLang != $orderLang) {
				$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$workerAmount,
					$orderLang,
					$workerLang
				);
			}
			$payerAmount = $extra->count * $extra->extra_price;
			if($actor->lang != $orderLang) {
				$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$payerAmount,
					$orderLang,
					$actor->lang
				);
			}

			//Сохраним суммы на момент покупки
			$extra->worker_amount = $workerAmount;
			$extra->payer_amount = $payerAmount;
			$extra->currency_rate = $currencyRate;
			$extra->save();
		}

		$upgradePackageExtra = \Model\UpgradePackageExtra::find($trackId);

		if (!count($track->extras) && empty($upgradePackageExtra)) {
			return [
				'result' => 'false',
				'error' => 'no_extra'
			];
		}

		// прежде чем списывать деньги проверим все ли ок с пакетами и подготовим данные
		$package = null;
		if ($upgradePackageExtra) {
			if (!self::isPackageUpgradeWithVolumeAllowed($order->data->volume_type_id, $order->kwork->volume_type_id)) {
				return ["result" => "false", "error" => "volume_type_changed"];
			}
			$orderPackage = DB::table("order_package")
				->where("order_id", $orderId)
				->first();
			if (!$orderPackage) {
				return ["result" => "false", "error" => "order_is_not_package"];
			}
			$betterPackagesTypes = PackageManager::getBetterPackagesTypes($orderPackage->type);
			$package = PackageManager::getPackageForUpgrade($order->PID, $upgradePackageExtra->package_type);
			if (empty($package) || !in_array($package["type"], $betterPackagesTypes)) {
				return ["result" => "false", "error" => "incorrect_package_for_upgrade"];
			}
			if ($upgradePackageExtra->count != $order->count) {
				return ["result" => "false", "error" => "incorrect_package_count_for_upgrade"];
			}
			$sum += $upgradePackageExtra->payer_price;
			$workerSum += $upgradePackageExtra->worker_price;
			if ($package["days"] > $orderPackage->duration) {
				if ($order->processLikeVolumedKwork()) {
					// Посчитаем сколько дней надо добавить по исходному и увеличенному числовому объёму
					$oldDays = \OrderVolumeManager::getVolumedDuration($orderPackage->duration, $order->kwork->getVolumeInSelectedType(), $order->data->volume);
					$newDays = \OrderVolumeManager::getVolumedDuration($package["days"], $order->kwork->getVolumeInSelectedType(), $order->data->volume);
					$plusDays += ($newDays - $oldDays) * Helper::ONE_DAY;
				} else {
					// Посчитаем сколько дней надо добавить по исходному и увеличенному пакету
					$newPackageDuration = OrderManager::getDuration($package["days"], $order->count, $order->data->category);
					$oldPackageDuration = OrderManager::getDuration($orderPackage->duration, $order->count, $order->data->category);
					$plusDays += ($newPackageDuration - $oldPackageDuration) * Helper::ONE_DAY;
				}
			}

			$workerAmount = $upgradePackageExtra->payer_price;
			if($workerLang != $orderLang) {
				$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$workerAmount,
					$orderLang,
					$workerLang
				);
			}
			$payerAmount = $upgradePackageExtra->worker_price;
			if($actor->lang != $orderLang) {
				$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$payerAmount,
					$orderLang,
					$actor->lang
				);
			}

			//Сохраним суммы на момент покупки
			$upgradePackageExtra->worker_amount = $workerAmount;
			$upgradePackageExtra->payer_amount = $payerAmount;
			$upgradePackageExtra->save();
		}

		// Проверка на случай если неверные значения будут в extra_ctp, т.к. раньше по другому работало
		$minCommissionWorkerSum = $sum - (CommissionRanges::getMinCommissionRate() * $sum / 100);
		$maxCommissionWorkerSum = $sum - (CommissionRanges::getMaxCommissionRate() * $sum / 100);
		if ($workerSum > $minCommissionWorkerSum || $workerSum < $maxCommissionWorkerSum) {
			return ["result" => "false", "error" => "incorrect_extras_commission"];
		}

		$convertedSum = $sum;
		if ($currencyId == \Model\CurrencyModel::USD && $actor->lang == \Translations::DEFAULT_LANG) {
			$currencyId = \Translations::getCurrencyIdByLang(\Translations::DEFAULT_LANG);
			$convertedSum = \Currency\CurrencyExchanger::getInstance()->convertByLang(
				$sum,
				\Translations::EN_LANG,
				\Translations::DEFAULT_LANG
			);
		}

		UserManager::refreshActorTotalFunds();
		if ($actor->totalFunds >= $convertedSum && $sum > 0) {
			$operation = OperationManager::orderOutOperation($convertedSum, $orderId, 1, $currencyId, $currencyRate, $order->currency_id);
			if (!$operation) {
				return false;
			}
			GTM::setApproveExtrasTransaction($operation, $order, $track->extras);
			$kworkDays = $order->kwork_days;
			if ($upgradePackageExtra) {
				OrderManager::orderPackageUpgradeProcess($order, $package);
				$kworkDays = $package["days"];
			}
			$plusKworkCount = 0;
			$days = $order->duration;
			$days += $plusDays;
			if ($increaseOrderVolume) {
				if ($order->processLikeVolumedKwork()) {
					$newVolume = $order->data->volume + $increaseOrderVolume->volume;
					// На сколько надо увеличить общее кол-во кворков
					$kworkByVolumeCount = ceil($newVolume / $order->kwork->volume) - $order->count;
				} else {
					$kworkByVolumeCount = $increaseOrderVolume->count;
				}
				$plusKworkCount += $kworkByVolumeCount;
			}

			$workerAmount = $workerSum;
			if ($workerLang != $orderLang) {
				$workerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$workerAmount,
					$orderLang,
					$workerLang
				);
			}

			$payerAmount = $sum;
			if ($actor->lang != $orderLang) {
				$payerAmount = \Currency\CurrencyExchanger::getInstance()->convertByLang(
					$payerAmount,
					$orderLang,
					$actor->lang
				);
			}

			App::pdo()->execute("UPDATE orders SET 	price = price+:sum, 
												crt = crt + :crt, 
												count = count + :plusKworkCount,
												duration = :days,
												deadline = IF(deadline IS NOT NULL, deadline + :plusDays, NULL),
												worker_amount = worker_amount + :worker_amount,
												payer_amount = payer_amount + :payer_amount,
												kwork_days = :kwork_days
								WHERE OID = :orderId", [
				"sum" => $sum,
				"crt" => $workerSum,
				"plusKworkCount" => $plusKworkCount,
				"days" => $days,
				"plusDays" => $plusDays,
				"orderId" => $orderId,
				"worker_amount" => $workerAmount,
				"payer_amount" => $payerAmount,
				"kwork_days" => $kworkDays,
			]);

			if ($increaseOrderVolume && $increaseOrderVolume->volume) {
				// Если есть увеличение числового объема заказа - увеличиваем числовой объем заказа
				DB::table(ODM::TABLE_NAME)
					->where(ODM::F_ORDER_ID, $orderId)
					->update([
						ODM::FIELD_VOLUME => DB::raw(ODM::FIELD_VOLUME . " + " . (float)$increaseOrderVolume->volume),
						\Model\OrderData::FIELD_CUSTOM_VOLUME => DB::raw(\Model\OrderData::FIELD_CUSTOM_VOLUME . " + " . (float)$increaseOrderVolume->custom_volume),
					]);
			}

			App::pdo()->execute("UPDATE track SET status = 'done', unread = 1 WHERE MID = :trackId", ["trackId" => $trackId]);
			App::pdo()->execute("UPDATE order_extra oe 
							JOIN track_extra te ON te.order_extra_id = oe.id
							JOIN track t ON t.MID = te.track_id
							SET oe.status = 'done' 
							WHERE t.MID = :trackId", ["trackId" => $trackId]);

			TrackManager::updateOrderExtraDuration($orderId);

			if ($increaseOrderVolume || $upgradePackageExtra) {
				TrackManager::payerDeclineUpgradeTracks($orderId);
			}
			if ($upgradePackageExtra) {
				TrackManager::payerDeclineVolumeTracks($orderId);
			}

			if ($order->status == OrderManager::STATUS_CHECK) {
				OrderManager::setInprogress($orderId, $order->PID, $order->worker_id);
				TrackManager::create($orderId, \Track\Type::PAYER_INPROGRESS_ADD_OPTION);
			}

			$result = [
				'result' => 'success',
				'redirectUrl' => "/track?id=" . $orderId . "&scroll=1"
			];
		} else {
			$needAmount = $convertedSum - $actor->totalFunds;

			$paymentId = App::pdo()->insert("operation", [
				"user_id" => $actor->id,
				"type" => "refill",
				"status" => "new"
			]);

			$result = [
				'result' => 'false',
				'error' => 'funds',
				'difference' => $needAmount,
				'payment_id' => $paymentId
			];
		}

		return $result;
	}

	/**
	 * Покупатель отклоняет предложенные опции
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $trackId Идентификатор трека с предложенными опциями
	 *
     * @return array|false Сообщение об успехе или ошибке
	 */
	public static function payerDeclineExtras($orderId, $trackId) {

		global $actor;

		if ($orderId < 1 || $trackId < 1) {
			return false;
		}

		$order = \Model\Order::find($orderId);

		if (empty($order) || $actor->id != $order->USERID || $order->isNotInBuyStatus()) {
			return false;
		}

		if (!App::pdo()->fetchScalar("SELECT MID FROM track WHERE MID = :trackId AND status = 'new'", ["trackId" => $trackId])) {
			return false;
		}

		App::pdo()->execute("UPDATE track SET status = 'close', unread = 1 WHERE MID = :trackId", ["trackId" => $trackId]);
		App::pdo()->execute("UPDATE order_extra oe 
                                JOIN track_extra te ON te.order_extra_id = oe.id
                                JOIN track t ON t.MID = te.track_id
                                SET oe.status = 'reject' 
                                WHERE t.MID = :trackId", ["trackId" => $trackId]);

		self::sendTrackChangedPush($trackId, ["action" => "delete_extra"]);

		return true;
	}

	/**
	 * Продавец отменяет предложенные опции
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param int $trackId Идентификатор трека с предложенными опциями
	 *
     * @return array|false Сообщение об успехе или ошибке
	 */
	public static function workerDeclineExtras($orderId, $trackId) {

		global $actor;

		if ($orderId < 1 || $trackId < 1) {
			return false;
		}

		$order = \Model\Order::find($orderId);

		if (empty($order) || $actor->id != $order->worker_id || $order->isNotInBuyStatus()) {
			return false;
		}

		if (!App::pdo()->fetchScalar("SELECT MID FROM track WHERE MID = :trackId AND status = 'new'", ["trackId" => $trackId])) {
			return false;
		}

		App::pdo()->execute("UPDATE track SET status = 'cancel', unread = 1 WHERE MID = :trackId", ["trackId" => $trackId]);
		App::pdo()->execute("UPDATE order_extra oe 
							JOIN track_extra te ON te.order_extra_id = oe.id
							JOIN track t ON t.MID = te.track_id
							SET oe.status = 'cancel'
							WHERE t.MID = :trackId", ["trackId" => $trackId]);

		self::sendTrackChangedPush($trackId, ["action" => "delete_extra"]);

		return true;
	}

	/**
	 * Продавец предлагает опции (без сообщения)
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param array $extrasArray Массив предлагамых опций [Идентификатор опции => Количество]
	 * @param array $customExtrasArray Массив кастомных предлагамых опций ["name" => Название опции, "price_id" => Идентификатор цены, "duration" => продолжительность]
	 * @param bool $asVolume Интерпритировать увеличение объема увеличение числового объема в заказе а не как увеличение количества кворков
	 * @param string $upgradePackageType Тип пакета апгрейд до которого предлагается medium|premium
	 *
     * @return array|false Сообщение об успехе или ошибке
	 */
	public static function workerSuggestExtras($orderId, $extrasArray, $customExtrasArray, $asVolume = false, $upgradePackageType = "") {
		if ($orderId < 1 || (empty($extrasArray) && empty($customExtrasArray) && empty($upgradePackageType))) {
			return false;
		}

		$lastInsert = false;
		$trackId = false;

		$order = \Model\Order::find($orderId);
		$upgradePackage = null;

		if ($order->has_stages && $order->stages && $order->stages->count() > 1) {
			throw new SimpleJsonException(Translations::t("Покупка опций в заказах с этапами запрещена"));
		}

		// #6318 Комиссии считаются по прогрессивной шкале
		$lang = \Translations::getLangByCurrencyId($order->currency_id);
		$turnover = \OrderManager::getTurnover($order->worker_id, $order->USERID, $lang);
		$turnover += $order->price;

		// Апгрейд пакета
		if ($upgradePackageType && $order->orderPackage) {
			$betterPackagesTypes = PackageManager::getBetterPackagesTypes($order->orderPackage->type);

			if (in_array($upgradePackageType, $betterPackagesTypes) &&
				self::isPackageUpgradeWithVolumeAllowed($order->data->volume_type_id, $order->kwork->volume_type_id)) {

				$upgradePackage = \Model\Package\Kwork\KworkPackage
					::where(\Model\Package\Kwork\KworkPackage::FIELD_KWORK_ID, $order->PID)
					->where(\Model\Package\Kwork\KworkPackage::FIELD_TYPE, $upgradePackageType)
					->first();

				if ($upgradePackage && $order->orderPackage->price < $upgradePackage->price) {
					$upgradeTotalPrice = $order->upgradePrice($upgradePackage);
					$upgradeAddDays = $order->upgradeDays($upgradePackage);
					if ($upgradeAddDays < 0) {
						$upgradeAddDays = 0;
					}

					if (!$trackId) {
						$trackId = TrackManager::create($orderId, 'extra');
					}

					$commission = OrderManager::calculateCommission($upgradeTotalPrice, $turnover, $order->getLang());

					$upgradePackageExtra = new \Model\UpgradePackageExtra();
					$upgradePackageExtra->track_id = $trackId;
					$upgradePackageExtra->package_type = $upgradePackageType;
					$upgradePackageExtra->payer_price = $upgradeTotalPrice;
					$upgradePackageExtra->worker_price = $commission->priceWorker;
					$upgradePackageExtra->duration = $upgradeAddDays;
					$upgradePackageExtra->count = $order->count;
					$upgradePackageExtra->save();
					$lastInsert = true;

					$turnover += $upgradeTotalPrice;
				} else {
					$upgradePackage = null; // Чтобы далее на количество предлагаемых пакетов не повлияло
				}
			}
		}

		// Заданные в кворки опции (в том числе увеличение объема кворка)
		if (!empty($extrasArray)) {
			/** @var \Model\Extra[] $extras **/
			$extras = \Model\Extra::whereIn(\Model\Extra::FIELD_ID, array_keys($extrasArray))
				->get()
				->keyBy(\Model\Extra::FIELD_ID);

			$orderedExtras = \Model\OrderExtra::where(\Model\OrderExtra::FIELD_ORDER_ID, $orderId)
				->where(\Model\OrderExtra::FIELD_STATUS, \Model\OrderExtra::STATUS_DONE)
				->groupBy(\Model\OrderExtra::FIELD_EXTRA_ID)
				->selectRaw("SUM(" . \Model\OrderExtra::FIELD_COUNT . ") as cnt, SUM(" . \Model\OrderExtra::FIELD_EXTRA_DURATION . ") as sum_duration")
				->addSelect([
					\Model\OrderExtra::FIELD_EXTRA_ID
				])
				->get()
				->keyBy(\Model\OrderExtra::FIELD_EXTRA_ID);

			$sum = 0;
			foreach ($extrasArray as $extra => $count) {
				if ($extras[$extra]->is_volume && $upgradePackage instanceof \Model\Package\Kwork\KworkPackage) {
					// Если было предложенно повышение уровня пакета, возьмём базовую стоимость повышенного пакета
					$extras[$extra]->eprice = $upgradePackage->price;
					$extras[$extra]->ctp = $upgradePackage->price_ctp;
					if (!$order->data->volume || !$order->data->volume_type_id) {
						// Если кворк не куплен как числовой с заданным объемом, поменяем название
						$extras[$extra]->etitle = Translations::translateByLang($order->kwork->lang, 'Количество пакетов "%1$s"', PackageManager::getName($upgradePackage->type, $order->kwork->lang));
					}
				}
				if ($order->data->volume && $order->data->volume_type_id && $extras[$extra]->is_volume) {
					// Если кворк был куплен как числовой с заданным объемом, то обрабатываем докупку объема как увеличение заказанного числового объема
					if ($asVolume) {
						$count = str_replace(" ", "", $count);
						$extras[$extra]->volume = $count;
					} else {
						// Если не указано явно что как числовой объем использовать значение - поддерживаем совместимость, заказываем в кворках и ставим объем равный объему кворка
						$extras[$extra]->count = $count;
						$extras[$extra]->volume = $order->data->kwork_volume * $count;
					}
					if (!is_null($extras[$extra]->volume)) {
						$extras[$extra]->customVolume = $extras[$extra]->volume;
						$extras[$extra]->volume = $order->getVolumeInCustomType($extras[$extra]->volume);
						$extras[$extra]->volumePrice = $order->kwork->getVolumedPrice($extras[$extra]->volume, $extras[$extra]->eprice);
						$sum += $extras[$extra]->volumePrice;
						$extras[$extra]->count = ceil($extras[$extra]->volume / $order->data->kwork_volume);
						// Цена опции по объёму
						$extras[$extra]->eprice = $extras[$extra]->volumePrice / $extras[$extra]->count;
					} else {
						$sum += $extras[$extra]->eprice * $extras[$extra]->count;
					}
				} else {
					$extras[$extra]->count = $count;
					$sum += $extras[$extra]->eprice * $extras[$extra]->count;
				}
			}

			$orderExtraIds = [];

			foreach ($extras as $key => $extra) {
				if ($extra->is_volume == 1) {
					if ($upgradePackage instanceof \Model\Package\Kwork\KworkPackage) {
						// Если был уже предложен апгрейд пакета то возьмём срок выполнения повышенного пакета
						$baseDays = $upgradePackage->duration;
					} else {
						$baseDays = $order->kwork_days;
					}
					if ($order->processLikeVolumedKwork()) {
						// Для заказа с числовым объёмом посчитам добавку по дням от числового объёма
						$oldKworkDays = OrderVolumeManager::getVolumedDuration($baseDays, $order->kwork->getVolumeInSelectedType(), $order->data->volume);
						$newKworkDays = OrderVolumeManager::getVolumedDuration($baseDays, $order->kwork->getVolumeInSelectedType(), ($order->data->volume + $extra->volume));
					} else {
						$oldKworkDays = OrderManager::getDuration($baseDays, $order->count, $order->data->category);
						$newKworkDays = OrderManager::getDuration($baseDays, ($extra->count + $order->count), $order->data->category);
					}
					$extra->duration = $newKworkDays - $oldKworkDays;
				} else {
					$orderedCount = 0;
					$orderedDuration = 0;
					if (!empty($orderedExtras[$extra->EID])) {
						$orderedCount = $orderedExtras[$extra->EID]->cnt;
						$orderedDuration = $orderedExtras[$extra->EID]->sum_duration;
					}

					$extra->duration = OrderManager::getDuration($extra->duration, $orderedCount + $extra->count, $order->data->category);
					$extra->duration -= $orderedDuration;
				}

				$commission = \OrderManager::calculateCommission($extra->eprice * $extra->count, $turnover, $lang);
				$extra->ctp = $commission->priceKwork / $extra->count;
				$turnover += $commission->price;

				$insertData = [
					'order_id' => $orderId,
					'extra_id' => $extra->EID,
					'count' => $extra->count,
					'extra_title' => $extra->etitle,
					'extra_price' => $extra->eprice,
					'extra_duration' => $extra->duration,
					'extra_ctp' => $extra->ctp,
					'extra_is_volume' => $extra->is_volume,
					'status' => OrderExtraManager::STATUS_NEW,
					OrderExtra::FIELD_VOLUME => $extra->volume ? $extra->volume : null,
					OrderExtra::FIELD_CUSTOM_VOLUME => $extra->customVolume ? $extra->customVolume : null,
					"currency_rate" => $order->currency_rate,
					OrderExtra::FIELD_WORKER_TOTAL_PRICE => $commission->priceWorker,
				];
				$orderExtraId = DB::table(OrderExtraManager::TABLE_NAME)->insertGetId($insertData);
				$orderExtraIds[] = $orderExtraId;
			}

			if (!$trackId) {
				$trackId = TrackManager::create($orderId, 'extra');
			}
			foreach ($orderExtraIds as $extraId) {
				$insertData = [
					"track_id" => $trackId,
					"order_extra_id" => $extraId,
				];
				$lastInsert = DB::table(\Model\TrackExtra::TABLE_NAME)->insertGetId($insertData);
			}
		}

		foreach($customExtrasArray as $customExtra) {
			if ($customExtra["price_id"] <= 0) {
				continue;
			}

			$optionPrice = \OptionPriceManager::getById($customExtra["price_id"]);
			if (!$optionPrice) {
				continue;
			}

			$newExtraOption = new \Model\ExtrasModel([
				ExtrasManager::F_CURRENCY_ID => $order->currency_id,
				ExtrasManager::F_EPRICE => $optionPrice->getPrice(),
			]);
			$commission = \OrderManager::calculateCommission($newExtraOption->getPrice(), $turnover, $lang);
			$newExtraOption->setOrderId($orderId)
				->setTitle($customExtra["name"])
				->setDuration($customExtra["duration"])
				->setCommission($commission->priceKwork);
			$turnover += $newExtraOption->getPrice();
			$extraId = ExtrasManager::saveExtra($newExtraOption);
			if (!$trackId) {
				$trackId = TrackManager::create($orderId, 'extra');
			}

			$duration = OrderManager::getDuration($newExtraOption->getDuration(), 1, $order->data->category);

			$insertData = [
				'order_id' => $orderId,
				'extra_id' => $extraId,
				'count' => 1,
				'extra_title' => $newExtraOption->getTitle(),
				'extra_price' => $newExtraOption->getPrice(),
				'extra_duration' => $duration,
				'extra_ctp' => $newExtraOption->getCommission(),
				'extra_is_volume' => 0,
				'status' => 'new',
				"currency_rate" => $order->currency_rate,
				OrderExtra::FIELD_WORKER_TOTAL_PRICE => $commission->priceWorker,
			];
			$orderExtraId = DB::table(OrderExtraManager::TABLE_NAME)->insertGetId($insertData);

			$insertData = [
				"track_id" => $trackId,
				"order_extra_id" => $orderExtraId,
			];
			$lastInsert = DB::table(\Model\TrackExtra::TABLE_NAME)->insertGetId($insertData);
		}

		if ($lastInsert) {
			TrackManager::sendNewTrackPush($orderId, \Track\Type::EXTRA, self::STATUS_NEW);
		}

		return (boolean) $lastInsert;
	}

	/**
	 * Разрешен ли апгрейд пакетного кворка с числовым объемом
	 *
	 * @param int|null $orderVolumeTypeId Тип числового объема кворка в момент заказа
	 * @param int|null $kworkVolumeTypeId Тип числового объема кворка на текущий момент
	 *
	 * @return bool
	 */
	public static function isPackageUpgradeWithVolumeAllowed($orderVolumeTypeId, $kworkVolumeTypeId): bool {
		return !($orderVolumeTypeId && $orderVolumeTypeId != $kworkVolumeTypeId);
	}

	public static function api_readTracks() {
		$orderId = post('orderId');
		$itemIds = post('itemIds');
		$badRequest = false;
		if (empty($orderId) || empty($itemIds)) {
			$badRequest = true;
		}
		$order = \Model\Order::find($orderId);
		if(empty($order) || $badRequest){
			return [
				'success' => false,
			];
		}
		return [
			'success' => self::readTracks($order, $itemIds),
		];
	}


	/**
	 * Проверяет валидность текста отзыва
	 * @param $string
	 * @return array
	 */
	public static function isValidReviewText($string) {
		//проверим на непустой комментарий
		$checkNotEmpty = \RatingManager::checkCommentNotEmpty($string);
		if(!$checkNotEmpty['success']) {
			return $checkNotEmpty;
		}

		$lang = \Translations::getLang();
		$result = \KworkManager::checkTextField($string, '', $lang, true);

		$badWords = [];

		$success = false;
		if ($result['validError'] == null && count($badWords) == 0) {
			$success = true;
		}
		return [
			'success' => $success,
			'result' => $result
		];
	}

	/**
	 * Устанавливает указанные треки в состояние "прочитано".
	 *
	 * @param \Model\Order $order - модель заказа, если есть
	 *   нужен для дополнительных действий после отмечания треков прочитанными (уведомления и проч.)
	 * @param array $trackIds
	 *
	 * @return bool
	 */
	public static function readTracks(\Model\Order $order, array $trackIds): bool {
		$actor = UserManager::getCurrentUser();
		// Если разлогинился или виртуальный (он не может изменять состояние прочитанности чужих сообщений)
		if (!$actor || $actor->isVirtual) {
			return false;
		}

		$orderId = $order->OID;
		$trackIds = self::getPrevTrackIds($order, $trackIds);
		//Если непрочитанных треков нет
		if (empty($trackIds)) {
			return true;
		}

		// Отмечаем сообщение прочитанным получателем (сюда мы заходим только для получателя).
		if ($order->isPayer($actor->id)) {
			// Для покупателя просто помечаем все unread
			$affectedRows = Track::whereIn(Track::FIELD_ID,  $trackIds)
				->update([
					self::FIELD_UNREAD => self::READ,
				]);
		} else {
			// Для продавца отдельно unread для простых треков, cron_worker_unread для треков из крона
			$affectedRows = Track::whereIn(Track::FIELD_ID, $trackIds)
				->whereNotIn(Track::FIELD_TYPE, \Track\Type::getCronTypes())
				->update([
					self::FIELD_UNREAD => self::READ,
				]);

			$affectedRows += Track::whereIn(Track::FIELD_ID, $trackIds)
				->whereIn(Track::FIELD_TYPE, \Track\Type::getCronTypes())
				->update([
					Track::FIELD_CRON_WORKER_UNREAD => self::READ,
				]);
		}

		$result = ($affectedRows > 0);

		if ($result) {
			// помечаем событие прочитанным

			// И чтобы другая сторона знала о прочитанности.
			// @todo: надо только для пользовательских, хотя можно это и в js проверить.
			$toUserId = ($actor->id == $order->USERID) ? $order->worker_id : $order->USERID;

			self::calcUnreadTracksCount($orderId);
		}

		return $result;
	}

	/**
	 * Получить билдер запроса для получения непрочитанных сообщений в заказе
	 * @param \Model\Order $order Заказ
	 * @param int $userId Идентификатор пользователя от чьего лица читаем
	 * @param array $trackIds Идентификаторы треков, которые проверяем. Если не задано - все треки в заказе
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	private static function unreadTracksQuery(\Model\Order $order, $userId, $trackIds = []) {
		$query = Track::where(self::FIELD_ORDER_ID, $order->OID);

		if (!empty($trackIds)) {
			$query->where(self::FIELD_ID,  "<=", max(Helper::intArray($trackIds)));
		};

		$query->where(function (\Illuminate\Database\Eloquent\Builder $query) use ($order, $userId) {
			// Для покупателя просто получаем все непрочитанные треки по полю unread
			// Для продавца получаем отдельно треки из крона по полю cron_worker_unread и остальные по unread
			// Для треков от крона не зависимо от автора, для остальных - не от заданного пользователя
			$cronUnreadField = $order->isPayer($userId) ? Track::FIELD_UNREAD : Track::FIELD_CRON_WORKER_UNREAD;

			return $query->whereIn(Track::FIELD_TYPE, \Track\Type::getCronTypes())
				->where($cronUnreadField, 1)
				->orWhereNotIn(Track::FIELD_TYPE, \Track\Type::getCronTypes())
				->where(self::FIELD_USER_ID, "!=", $userId)
				->where(self::FIELD_UNREAD, 1);
		});

		return $query;
	}

	/**
	 * Получить также предыдущие непрочитанные треки
	 *
	 * @param \Model\Order $order Заказ
	 * @param array $trackIds Массив идентификаторов треков
	 * @return array|bool
	 */
	private static function getPrevTrackIds(\Model\Order $order, array $trackIds)
	{
		$userId = UserManager::getCurrentUserId();
		return self::unreadTracksQuery($order, $userId, $trackIds)
			->pluck(self::FIELD_ID)
			->toArray();
	}

	// @todo: а ещё есть отдельный метод fillTracks...
	/**
	 * Заполнить треки по идентификатору заказа
	 *
	 * @param array $tracks массив треков
	 * @param int $orderId - идентификатора заказа
	 */
	public static function fillTracksByOrderId(array &$tracks, int $orderId) {
		self::fillTracks($tracks);
		$files = self::getTrackFiles($orderId);
		self::fillTrackFiles($tracks, $files);
		self::fillTrackExtras($orderId, $tracks);
		self::fillTrackReasons($tracks);
	}

	public static function getTracksDataByOrder($orderId) {
		$tracks = TrackManager::getOrder($orderId);
		self::fillTracksData($tracks);
		return $tracks;
	}

	/**
	 * Загружает просто трек, без файлов и проч.
	 *
	 * @param int $trackId
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public static function loadTrackById(int $trackId): \stdClass {
		$track = &kwork_static(__CLASS__ . __METHOD__ . $trackId);
		if (!isset($track)) {
			// @todo: возможно стоит организовать это дело иначе и загрузить
			// все одним запросом а потом сформировать нормальный объект. Но не сейчас.
			$sql = 'SELECT * FROM track WHERE MID = :trackId';

			$track = App::pdo()->fetch($sql, ['trackId' => $trackId], PDO::FETCH_OBJ);

			if (empty($track)) {
				throw new Exception(Translations::t('Не удалось загрузить трек.'));
			}
		}

		return $track;
	}

	public static function getTrackIdByFileId(int $fileId): int {
		$trackIdField = FileManager::FIELD_ENTITY_ID;
		$fileIdField = FileManager::FIELD_ID;
		$fileTable = FileManager::TABLE_NAME;

		$sql = "SELECT $trackIdField FROM $fileTable WHERE $fileIdField = :fileId";
		return (int)App::pdo()->fetchScalar($sql, ['fileId' => $fileId]);
	}

	/**
	 * Добавить к массиву треков данные для вывода.
	 *
	 * @param $tracks
	 */
	public static function fillTracks(&$tracks) {
		if(empty($tracks)) {
			return;
		}

		$trackIds = array_column($tracks, self::FIELD_ID);

		$params = [];
		$files = App::pdo()->getList("SELECT f." . FileManager::FIELD_ID . " as 'id', 
															f." . FileManager::FIELD_FNAME . ", 
															f." . FileManager::FIELD_S . ", 
															f." . FileManager::FIELD_USERID . " as userid, 
															t." . self::FIELD_ID . " as 'trackId' 
														FROM " . FileManager::TABLE_NAME . " f 
															JOIN 
															 " . self::TABLE_NAME . " t 
														 	ON t." . self::FIELD_ID . " = f." . FileManager::FIELD_ENTITY_ID . " 
														 		AND f." . FileManager::FIELD_ENTITY_TYPE . " = '" . FileManager::ENTITY_TYPE_TRACK . "' 
														WHERE t." . self::FIELD_ID . " in (" . App::pdo()->arrayToStrParams($trackIds, $params) . ")", $params);
		self::fillTracksData($tracks);
		self::fillTrackFiles($tracks, $files);
	}

	/**
	 * Заполнить данные по трекам
	 * @param $tracks array массив треков
	 */
	public static function fillTracksData(&$tracks) {
		$trackUsers = array();
		foreach ($tracks as &$track) {
			if (is_array($track)) {
				$track = (object)$track;
			}
			if (!empty($track->user_id)) {
				$trackUsers[$track->user_id] = true;
			}
		}
		UserManager::preLoadInfo(array_keys($trackUsers));

		foreach ($tracks as &$track) {
			$trackUserId = $track->user_id;
			$userInfo = UserManager::getUserData($trackUserId);
			// Обзор-то тоже в треки идет, а у него id не MID.
			if (!empty($track->MID)) {
				$track->id = $track->MID;
			}
			$track->date_create_unix = strtotime($track->date_create);
			if (empty($track->username)) {
				$track->username = $userInfo['username'];
			}
			$track->profilepicture = $userInfo['profilepicture'];
			$track->isArbiter = $trackUserId == App::config("kwork.user_id");
			$track->isModer = $trackUserId == App::config("kwork.moder_id");
			$track->message = replace_full_urls($track->message);
		}
	}

	/**
	 * @param \Model\ExtrasModel[] $kworkExtras
	 */
	public static function getExtrasOptions($kworkExtras) {
		$options = [];
		foreach($kworkExtras as $extra) {
			$options[] = [
				'id' => $extra->getId(),
				'priceWithComission' => $extra->getPriceWithCommission(),
				'priceWithComissionStr' => $extra->getPriceWithCommissionString(),
				'duration' => $extra->getDuration(),
			];
		}

		return $options;
	}

	/**
	 * Преобразование цен опций из моделей в массивы
	 *
	 * @param Model\OptionPriceModel[] $priceOptions Массив моделей цен опций
	 * @param string $lang Язык
	 * @param bool $withPrice Добавить цену в результат
	 *
	 * @return array
	 */
	public static function getPricesOptions(array $priceOptions, string $lang, bool $withPrice = false) {
		$options = [];
		foreach ($priceOptions as $priceOption) {
			$option = [
				'id' => $priceOption->getId(),
				'priceWithComission' => $priceOption->getPriceWithCommissionWOFormatting($lang),
				'priceWithComissionStr' => $priceOption->getLocalizedPriceWIthCommissionString(1, $lang),
			];
			if ($withPrice) {
				$option["price"] = $priceOption->getPrice();
			}
			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Отметить все входящие треки прочитанными.
	 *
	 * @param \Model\Order $order
	 */
	public static function setReadInTracks(\Model\Order $order) {
		$userId = UserManager::getCurrentUserId();

		// Выбрать все непрочитанные входящие.
		$trackIds = self::unreadTracksQuery($order, $userId)
			->pluck(self::FIELD_ID)
			->toArray();

		// Отметить их все прочитанными.
		if (!empty($trackIds)) {
			self::readTracks($order, $trackIds);
		}
	}

	/**
	 * Обновление количества непрочтенных треков в заказе
	 * @TODO по хорошему тут нужно принимать модель заказа тогда не нужно будет получать данные по заказу, но это после рефакторинга send_track
	 *
	 * @param int $orderId Идентификатор заказа
	 */
	public static function calcUnreadTracksCount(int $orderId) {
		$order = \Model\Order::find($orderId);
		if (!$order) {
			return;
		}
		$order->payer_unread_tracks = \Model\Track::where(\Model\Track::FIELD_ORDER_ID, $orderId)
			->where(\Model\Track::FIELD_UNREAD, 1)
			->where(\Model\Track::FIELD_USER_ID, "<>", $order->USERID)
			->where(\Model\Track::FIELD_TYPE, self::TRACK_TYPE_TEXT)
			->count();

		$order->worker_unread_tracks = \Model\Track::where(\Model\Track::FIELD_ORDER_ID, $orderId)
			->where(\Model\Track::FIELD_UNREAD, 1)
			->where(\Model\Track::FIELD_USER_ID, "<>", $order->worker_id)
			->where(\Model\Track::FIELD_TYPE, self::TRACK_TYPE_TEXT)
			->count();

		$order->save();
	}

	/**
	 * Покупатель отказывает все треки с предложениями опций включающими увеличение объема кворка
	 *
	 * @param int $orderId Идентификатор заказа
	 */
	public static function payerDeclineVolumeTracks(int $orderId) {
		$trackIds = self::getNewExtrasTracks($orderId, true);

		foreach ($trackIds as $trackId) {
			self::payerDeclineExtras($orderId, $trackId);
		}
	}

	/**
	 * Покупатель отказывает все треки с предложениями опций включающими апгрейд уровная пакетного кворка
	 *
	 * @param int $orderId Идентификатор заказа
	 */
	public static function payerDeclineUpgradeTracks(int $orderId) {
		$trackIds = self::getUpgradePackageNewTracks($orderId);

		foreach ($trackIds as $trackId) {
			self::payerDeclineExtras($orderId, $trackId);
		}
	}

	/**
	 * Получение треков имеющих предложения доп. опций по которым не принято решение
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param bool $onlyVolume Только опции увеличивающие объем заказа
	 *
	 * @return array
	 */
	private static function getNewExtrasTracks(int $orderId, bool $onlyVolume = false): array {
		$query = DB::table(OrderExtra::TABLE_NAME . " as oe")
			->join(TrackExtra::TABLE_NAME . " as te", "oe." . OrderExtra::FIELD_ID, "te." . TrackExtra::FIELD_ORDER_EXTRA_ID)
			->join(Track::TABLE_NAME . " as t", "te." . TrackExtra::FIELD_TRACK_ID, "t." . Track::FIELD_ID)
			->where("t." . Track::FIELD_ORDER_ID, $orderId)
			->where("t." . Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->where("oe." . OrderExtra::FIELD_STATUS, OrderExtra::STATUS_NEW);

		if ($onlyVolume) {
			$query->where("oe." . OrderExtra::FIELD_EXTRA_IS_VOLUME, 1);
		}

		return $query->distinct()
			->pluck("t." . Track::FIELD_ID)
			->toArray();
	}

	/**
	 * Получение идентификаторов треков с предложениями опций включающими апгрейд уровня пакетного кворка
	 * с еще не вынесенным решением
	 *
	 * @param int $orderId Идентификатор заказа
	 *
	 * @return array
	 */
	private static function getUpgradePackageNewTracks(int $orderId): array {
		return DB::table(UpgradePackageExtra::TABLE_NAME . " as upe")
			->join(Track::TABLE_NAME . " as t", "upe." . UpgradePackageExtra::FIELD_TRACK_ID, "t." . Track::FIELD_ID)
			->where("t." . Track::FIELD_ORDER_ID, $orderId)
			->where("t." . Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->distinct()
			->pluck("t." . Track::FIELD_ID)
			->toArray();
	}

	/**
	 * Нужно ли показывать сообщение о возможном снижении лояльности по просрочке
	 *
	 * @param bool $isWorker Является ли текущий пользователь продавцом
	 * @param int $deadline Дедлайн UNIXTIME
	 *
	 * @return bool
	 */
	public static function isLoyalityLateVisible(bool $isWorker, $deadline) {
		if (!$isWorker) {
			return false;
		}
		if (!$deadline) {
			return false;
		}

		if (($deadline - time()) <= 3 * Helper::ONE_HOUR) {
			return true;
		}

		return false;
	}

	/**
	 * Причины отмены которые изменяют свое поведение с запроса отмены
	 * на мгновенную отмену заказа в случае если заказ просрочен
	 *
	 * @return array
	 */
	public static function getImmidiateCancelAfterLateReasons(): array {
		return [
			"payer_worker_cannot_execute_correct",
			"payer_no_communication_with_worker"
		];
	}

	/**
	 * Получить последний закрытый трек по типу
	 *
	 * @param int $orderId ID заказа
	 * @param string $type тип трека
	 * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
	 */
	public static function getClosedTrackByType($orderId, $type) {
		$trackCancelRequest = Track::where(Track::FIELD_TYPE, $type)
			->where(Track::FIELD_STATUS, self::STATUS_CLOSE)
			->where(Track::FIELD_ORDER_ID, $orderId)
			->orderByDesc(Track::FIELD_ID)
			->first();
		if ($trackCancelRequest) {
			return $trackCancelRequest;
		}
		return false;
	}

	/**
	 * Получить последний новый трек по типу
	 *
	 * @param int $orderId ID заказа
	 * @param string $type тип трека
	 * @return Track|null
	 */
	public static function getNewTrackByType(int $orderId, string $type) {
		return Track::where(Track::FIELD_TYPE, $type)
			->where(Track::FIELD_STATUS, self::STATUS_NEW)
			->where(Track::FIELD_ORDER_ID, $orderId)
			->orderByDesc(Track::FIELD_ID)
			->first();
	}

	/**
	 * Получить последний трек в заказе
	 *
	 * @param int $orderId идентификатор заказа
	 * @return Track
	 */
	public static function getLastTrack(int $orderId) : Track {
		return Track::where(Track::FIELD_ORDER_ID, $orderId)
			->orderByDesc(Track::FIELD_ID)
			->first();
	}

	/**
	 * Закрываем все открытые запросы на отмену заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 */
	public static function closeCancelRequestsTracks(int $orderId) {
		Track::where(Track::FIELD_ORDER_ID, $orderId)
			->whereIn(Track::FIELD_TYPE, Type::cancelRequestTypes())
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->update([Track::FIELD_STATUS => TrackManager::STATUS_CLOSE]);
	}

	/**
	 * Получить идентификаторы треков активных запросов на отмену заказа от покупателя
	 *
	 * @param array $orderIds Список идентификаторов заказов по которым поиск
	 *
	 * @return array [orderId => trackId, ...]
	 */
	public static function getPayerCancelRequestTrackOrderIds(array $orderIds): array {
		return Track::whereIn(Track::FIELD_ORDER_ID, $orderIds)
			->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->distinct()
			->pluck(Track::FIELD_ID, Track::FIELD_ORDER_ID)
			->toArray();
	}

	/**
	 * Отклонить все предложения опций без уведомлений
	 *
	 * @param int $orderId Идентификатор заказа
	 */
	public static function declineAllOpenedExtraTracksWithoutNotification(int $orderId) {
		// Апгрейды пакетов отдельно
		$packageUpgradeTrackIds = self::getUpgradePackageNewTracks($orderId);

		// Обычные опции отдельно
		$extrasTrackIds = self::getNewExtrasTracks($orderId);

		$allTrackIds = array_unique(array_merge($packageUpgradeTrackIds, $extrasTrackIds));

		if ($allTrackIds) {
			Track::whereKey($allTrackIds)
				->update([Track::FIELD_STATUS => TrackManager::STATUS_NEW]);
		}

		if ($extrasTrackIds) {
			DB::table(OrderExtra::TABLE_NAME . " as oe")
				->join(TrackExtra::TABLE_NAME . " as te", "oe." . OrderExtra::FIELD_ID, "te." . TrackExtra::FIELD_ORDER_EXTRA_ID)
				->join(Track::TABLE_NAME . " as t", "te." . TrackExtra::FIELD_TRACK_ID, "t." . Track::FIELD_ID)
				->whereIn("t." . Track::FIELD_ID, $extrasTrackIds)
				->update(["oe." . OrderExtra::FIELD_STATUS => OrderExtra::STATUS_REJECT]);
		}
	}

	/**
	 * Получение проигнорированного трека предложения отмены заказа
	 *
	 * @param int $orderId Идентификатор заказа
	 * @param string $type Тип трека worker_inprogress_cancel_request|payer_inprogress_cancel_request
	 *
	 * @return Track|null
	 */
	public static function getExpiredCancelRequestTrack(int $orderId, string $type) {
		if (!in_array($type, Type::cancelRequestTypes())) {
			throw new RuntimeException("Тип $type не является типом трека предложения отмены заказа");
		}

		$thresholdDate = date("Y-m-d H:i:s", time() - Helper::ONE_DAY * 2);

		return Track::where(Track::FIELD_ORDER_ID, $orderId)
			->where(Track::FIELD_TYPE, $type)
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->where(Track::FIELD_DATE_CREATE, "<", $thresholdDate)
			->first();
	}

	/**
	 * Возвращает сообщение последнего трека инициирующего арбитраж
	 *
	 * @param int $orderId
	 * @return string
	 */
	public static function getLastArbitrageInitTrackMessage(int $orderId): string {
		/** @var Track $track */
		$track = Track::query()
			->where(Track::FIELD_ORDER_ID, $orderId)
			->whereIn(Track::FIELD_TYPE, Type::arbitrageInitateTypes())
			->orderByDesc(Track::FIELD_ID)
			->limit(1)
			->first();

		return ($track) ? $track->message : "";
	}

	/**
	 * Записать в статический кэш данные о файлах в запросе
	 *
	 * @param array $data
	 */
	public static function setRequestFiles(array $data) {
		if (!isset(self::$requestFiles)) {
			self::$requestFiles = $data;
		}
	}

	/**
	 * Получить данные о файлах в запросе из статического кэша
	 *
	 * @return array
	 */
	public static function getRequestFiles(): array {
		return self::$requestFiles ?? [];
	}

	/**
	 * Получить список файлов, доступных для прикрепления к треку
	 *
	 * @param int|null $trackId Идентификатор трека для проверки
	 *
	 * @return array
	 */
	public static function getAvailableFilesForTrack(int $trackId = null) {
		if ($requestedFiles = self::getRequestFiles()) {
			return File::getAvailableFilesForEntity($requestedFiles, $trackId)
				->where(File::FIELD_USERID, UserManager::getCurrentUserId())
				->limit(self::getFileLimit())
				->pluck(File::FIELD_ID)
				->toArray();
		}

		return [];
	}

	/**
	 * Прикрепление доступных файлов
	 *
	 * @param int $entityId Идентификатор сущности
	 * @param int|null $draftId Идентификатор черновика
	 * @param string $entity_type Тип сущности для прикрепляемых файлов
	 */
	public static function attachFilesToTrack(int $entityId, int $draftId = null, $entity_type = FileManager::ENTITY_TYPE_TRACK) {
		$attachedFiles = self::getAvailableFilesForTrack($draftId);
		if ($entityId && !empty($attachedFiles)) {
			$files = File::whereIn(File::FIELD_ID, $attachedFiles)
				->get();

			foreach ($files as $file) {
				/*
				 * если локального файла-исходника не существует по какой--либо причине
				 * (например, это дубль сообщения и файл уже был загружен в Амазон и удален локально)
				 * добавлять этот файл не будем даже пытаться
				 */
				if (!file_exists($file->path)) {
					continue;
				}

				$file->entity_type = $entity_type;

				/*
				 * Обновим поле entity_type, чтобы если постится дубль трека с файлами, файл не попадал
				 * в выборку, т.е. загружаем только при первом обращении.
				 * Удаление непрекрепленных файлов кроном по полю entity_id = null
				 */
				$file->save();

				$file->entity_id = $entityId;

				// определимся по entityType где будет храниться файл и сохраним через хендлер
				try {
					$file->setStorageAndStore();
					$file->save();
				} catch (\Exception $exception) {
					// в случае ошибки загрузки файла не будем прерываться, просто не будем его прикреплять,
					// и залогируем ошибку. Файл удалится кроном через несколько дней как никуда не
					// прикрепленный
					Log::dailyErrorException($exception);
				}
			}
		}
	}

	/**
	 * Создать трек с помощью класс с данными для создания
	 *
	 * @param TrackCreateData $data Данные для создания трека
	 * @return int
	 */
	public static function createViaData(TrackCreateData $data) {
		return self::create(
			$data->orderId,
			$data->type,
			$data->message,
			$data->reasonType,
			$data->replyType,
			$data->status,
			$data->payerId,
			$data->kworkId,
			$data->userId,
			$data->supportId,
			$data->articleId,
			$data->roleId,
			$data->quoteId
		);
	}

	/**
	 * Проверить можно ли установить цитируемое сообщение
	 *
	 * @param string $trackType Тип трека
	 * @param int $orderId Идентификатор заказа трека
	 * @param int $quoteId Идентификатор цитаты
	 * @return bool
	 */
	public static function checkQuoteTrack(string $trackType, int $orderId, int $quoteId) {
		if (empty($quoteId)) {
			return false;
		}
		// Цитировать можно в текстовых треках
		if (!in_array($trackType, Type::getTextTypes())) {
			return false;
		}
		// Цитировать можно только текстовые треки
		$quote = Track::whereKey($quoteId)
			->whereIn(Track::FIELD_TYPE, Type::getTextTypes())
			->first();
		if (!$quote) {
			return false;
		}
		// Если пытаемся связать с цитатой из другого трека - ошибка
		if ($quote->OID != $orderId) {
			return false;
		}
		return true;
	}
}