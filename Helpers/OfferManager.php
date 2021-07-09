<?php

use Model\Offer;
use Model\OrderExtra;
use Core\Exception\SimpleJsonException;
use Core\Exception\JsonValidationException;
use Model\OrderStages\OrderStage;
use Order\Stages\OrderStageOfferManager;
use Order\Stages\OrderStageManager;
use Validator\OrderStageValidator;

class OfferManager {

	const TABLE_NAME = "offer";

	/**
	 * Названия полей таблицы offer
	 */
	const F_ID = "id";
	const F_ORDER_ID = "order_id";
	const F_WANT_ID = "want_id";
	const F_KWORK_ID = "kwork_id";
	const F_STATUS = "status";
	const F_DATE_CREATE = "date_create";
	const F_COMMENT = "comment";

	/**
	 * Константы статусов
	 */
	const STATUS_ACTIVE = "active";
	const STATUS_DELETE = "delete";
	const STATUS_CANCEL = "cancel";
	const STATUS_DONE = "done";
	const STATUS_REJECT = "reject";

	const ERROR_CODE_KWORK_ERRORS = 1;

	/**
	 * Нельзя добавить предложение, не задан аватар
	 */
	const CANNOT_ADD_AVATAR = "no_avatar";

	/**
	 * Нельзя добавить предложение, слишком короткое описание
	 */
	const CANNOT_ADD_DESCRIPTION = "no_description";

	/**
	 * Нельзя добавить предложение, нет ни аватара и короткое описание
	 */
	const CANNOT_ADD_ALL = "poorly_profile_data";

	/**
	 * Количество предложений по которым проверяется уникальность комментария предложения
	 */
	const UNIQUE_CHECK_OFFERS_COUNT = 20;

	/**
	 * Минимальное количество символов в комменте (он же "Как вы будете решать задачу клиента")
	 */
	const COMMENT_MIN_LENGTH = 150;

	/**
	 * Максимальное количество символов в комменте (он же "Как вы будете решать задачу клиента")
	 */
	const COMMENT_MAX_LENGTH = 2000;

	/**
	 * Множитель для получения минимальной цены предложения
	 */
	const MIN_PRICE_MULTIPLIER = 0.2;

	/**
	 * Длина шингла для проверки схожести
	 */
	const SIMILARITY_CHECK_SHINGLE_LENGTH = 6;

	/**
	 * Порог процента схожести текстов
	 */
	const COMMENT_SIMILARITY_THRESHOLD = 70;

	/**
	 * Максимальное кол-во дней на выполнение заказа
	 */
	const CUSTOM_OFFERS_MAX_DAYS = 60;

	public static function api_hideOffer() {
		global $actor;
		return [
			'success' => self::hideOffer($actor->id, post('order_id'))
		];
	}

	/**
	 * Скрыть предложение у запроса
	 *
	 * @param int $userId  id покупателя (владельца запроса)
	 * @param int $orderId id заказа
	 *
	 * @return bool
	 */
	public static function hideOffer($userId, $orderId) {
		global $conn;
		if (!(int) $userId || !(int) $orderId) {
			return false;
		}

		$sql = 'SELECT w.user_id FROM offer o JOIN want w ON w.id = o.want_id WHERE w.user_id = ' . mres($userId);
		if ($conn->getCell($sql) == $userId) {
			$conn->Execute("UPDATE offer 
								SET status = 'cancel',
									highlighted = 0 
								WHERE order_id = " . mres($orderId));
			return true;
		}

		return false;
	}

	/**
	 * Создать заказ для предложения кворка покупателю
	 * @global object $actor
	 *
	 * @param int $userId			Покупатель предлагаемого кворка
	 * @param string $sourceType - откуда был создан заказ
	 * @param int $kworkId - Идентификатор кворка
	 * @param int $kworkCount - Количество кворков (для непакетных кворков)
	 * @param string $kworkPackageType - Тип пакетного кворка standard|medium|premium
	 * @param array $selectedExtras - Выбранные опции (для непакетного кворка) [extraId => extraCount]
	 * @param array $customExtras - Кастомные опции [["name" => string, "price_id" => int, "duration" => int]]
	 * @param float $priceLimit Лимит цены заказа
	 * @param int $wantId Идентификатор запроса на услуги при косвенной связи (из лички)
	 *
	 * @return int
	 *
	 * @throws \Core\Exception\JsonException
	 */
	public static function createOffersOrder($userId, $sourceType, $kworkId, $kworkCount = null, $kworkPackageType = null, $selectedExtras = [], $customExtras = [], float $priceLimit = 0, int $wantId = 0) {

		$actor = UserManager::getCurrentUser();

		$sql = "SELECT 
					PID as 'id',
					USERID as 'userId',
					gtitle,
					gdesc, 
					price, 
					ctp,
					active,
					lang,
					category
				FROM " . KworkManager::TABLE_KWORKS . "
				WHERE
					PID = :kworkId";
		$kwork = App::pdo()->fetch($sql, ["kworkId" => (int) $kworkId], PDO::FETCH_OBJ);
		if (!$kwork || $kwork->userId != $actor->id) {
			throw new SimpleJsonException(Translations::t("Выберите кворк"));
		}

		$kworkManager = new KworkManager($kwork->id, KworkManager::LOAD_KWORK_DATA | KworkManager::LOAD_PACKAGES);
		if ($kwork->is_package) {
			$packageData = $kworkManager->get($kworkPackageType . 'Package');
		} else {
			$packageData = $kworkManager->getStandardPackage();
		}

		if ($kwork->is_package) {
			$kworkCount = 1;
			$kworkDays = $packageData["duration"];
			$kworkPrice = $packageData["price"];
		} else {
			$kworkDays = $kwork->days;
			$kworkPrice = $kwork->price;
			$kworkCount = intval($kworkCount);
		}

		if ($kworkCount == 0) {
			throw new SimpleJsonException(Translations::t("Введите количество кворков"));
		}
		$duration *=  Helper::ONE_DAY;
		$price = $kworkPrice * $kworkCount;

		$turnover = \OrderManager::getTurnover($actor->id, $userId, $kwork->lang);
		$comission = \OrderManager::calculateCommission($price, $turnover, $kwork->lang);
		$turnover += $price;
		$kworkCtp = $comission->priceKwork / $kworkCount;
		$ctp = $comission->priceKwork;

		$crt = $price - $ctp;
		if ($kwork->lang == Translations::DEFAULT_LANG) {
			$currencyId = \Model\CurrencyModel::RUB;
			$currencyRate = 1.0;
		} else {
			$currencyId = \Model\CurrencyModel::USD;
			$currencyRate = \Currency\CurrencyExchanger::getInstance()->convertToRUB(1.0);
		}

		// Проверка стоимости если установлен лимит стоимости
		if ($priceLimit > 0) {
			$minPrice = OfferManager::getMinCustomOfferPrice($kwork->lang, $priceLimit, $kwork->category);
			$maxPrice = OfferManager::getMaxCustomOfferPrice($kwork->lang, $priceLimit);

			if ($price < $minPrice || $price > $maxPrice) {
				if ($maxPrice == $minPrice) {
					throw new SimpleJsonException(Translations::t(
						"Цена услуги для данного заказа не может быть более %s.",
						Translations::getPriceWithCurrencySign($minPrice, $currencyId, "руб")));
				}
				throw new SimpleJsonException(Translations::t(
					"Допустимая цена услуги для данного заказа от %s до %s",
					Translations::getPriceWithCurrencySign($minPrice, $currencyId),
					Translations::getPriceWithCurrencySign($maxPrice, $currencyId)));
			}
		}

		$bonusText = "";
		$enablePromoAction = \App::config("promo_show_bonus");
		if ($enablePromoAction &&
			$kwork->bonus_moderate_status == KworkManager::BONUS_STATUS_VERIFIED) {
			$bonusText = $kwork->bonus_text;
		}

		$timeCreate = time();
		$fieldsOrder = [
			OrderManager::F_USERID => $userId,
			OrderManager::F_PID => $kwork->id,
			OrderManager::F_WORKER_ID => $kwork->userId,
			OrderManager::F_STATUS => OrderManager::STATUS_NEW,
			OrderManager::F_TIME_ADDED => $timeCreate,
			OrderManager::F_STIME => $timeCreate,
			OrderManager::F_PRICE => $price,
			OrderManager::F_CRT => $crt,
			OrderManager::F_COUNT => $kworkCount,
			OrderManager::F_DURATION => $duration,
			OrderManager::F_KWORK_TITLE => $kwork->gtitle,
			OrderManager::F_KWORK_DAYS => 0,
			OrderManager::F_SOURCE_TYPE => $sourceType,
			OrderManager::F_CURRENCY_ID => $currencyId,
			OrderManager::F_CURRENCY_RATE => $currencyRate,
			OrderManager::F_BONUS_TEXT => $bonusText,
			\Model\Order::FIELD_INITIAL_OFFER_PRICE => $price,
			\Model\Order::FIELD_INITIAL_DURATION => $duration,
		];

		if ($wantId) {
			//Проверяем тип источника заказа, на валидность, если типа источника нет, его нужно добавить в
			// getDirectTypes или getIndirectTypes
			if (!WantManager::checkValidSourceType($sourceType)) {
				throw new SimpleJsonException(Translations::t("Задан неверный тип источника заказа"));
			}
			$fieldsOrder[\Model\Order::FIELD_PROJECT_ID] = $wantId;
		}

		$orderId = App::pdo()->insert(OrderManager::TABLE_NAME, $fieldsOrder);

		if (empty($orderId)) {
			throw new SimpleJsonException(Translations::t("Не удалось создать предложение"));
		}

		$orderData = [
			"order_id" => $orderId,
			"kwork_desc" => $kwork->gdesc,
			"kwork_category" => $kwork->category,
			"kwork_price" => $kworkPrice,
			"kwork_ctp" => $kworkCtp
		];

		$volumeExtraTitle = null;
		if (!empty($volumeType)) {
			$orderData[\Order\OrderDataManager::FIELD_KWORK_VOLUME] = $kwork->volume;
			$orderData[\Order\OrderDataManager::FIELD_VOLUME] = $kwork->volume * $kworkCount;
			$orderData[\Order\OrderDataManager::FIELD_VOLUME_TYPE_ID] = $kwork->volume_type_id;
			$orderData[\Model\OrderData::FIELD_CUSTOM_VOLUME] = $kwork->volume * $kworkCount;
			$orderData[\Model\OrderData::FIELD_CUSTOM_VOLUME_TYPE_ID] = $kwork->volume_type_id;
			$volumeExtraTitle = Translations::translateByLang($kworkManager->getLang(), "Количество %s", $volumeType->getPluralizedName(0));
		}

		\Order\OrderDataManager::add($orderData);

		return $orderId;
	}

	/**
	 * Установить предложение на запрос на услуги в статус "выполнен"
	 * @global DataBase $conn
	 * @param int $wantId
	 * @param int $order_id
	 */
	public static function setDoneByProject($wantId, $order_id) {

		global $conn;

		$conn->Execute("UPDATE want SET order_count = order_count+1 WHERE id = " . mres($wantId));
		$conn->Execute("UPDATE offer SET status = 'done' WHERE want_id = " . mres($wantId) . " AND order_id = " . mres($order_id));
	}

	/**
	 * Обновить предложение по идентификатору заказа
	 * @param int $orderId
	 * @param array $fields - поля для обновления
	 * @return boolean
	 */
	public static function updateOfferByOrderId($orderId, array $fields) {
		if (!$orderId || empty($fields) || !is_array($fields)) {
			return false;
		}
		$condition = self::F_ORDER_ID . " = :" . self::F_ORDER_ID;
		$params = [self::F_ORDER_ID => $orderId];
		return (bool) App::pdo()->update(self::TABLE_NAME, $fields, $condition, $params);
	}

	/**
	 * Отменить предложение по идентификатору заказа
	 * @param int $orderId
	 * @return boolean
	 */
	public static function setCancelByOrderId($orderId) {
		return self::updateOfferByOrderId($orderId, [self::F_STATUS => self::STATUS_CANCEL]);
	}


	/**
	 * Создание индивидуального заказа (с индивидуальным кворком)
	 *
	 * @param int $payer Покупатель
	 * @param string $kworkName Название кворка
	 * @param string $kworkDesc Описание кворка
	 * @param int $kworkDuration Длительность кворка
	 * @param float $kworkPrice Цена кворка для покупателя
	 * @param string $sourceType Тип предложения OrderManager::SOURCE_INBOX_PRIVATE/OrderManager::SOURCE_WANT_PRIVATE
	 * @param string $lang Язык создаваемого кворка и соответственно валюта
	 * @param float $priceLimit Лимит цены запроса предложения
	 * @param int $wantId Идентификатор запроса на услуги при косвенной связи (из лички)
	 * @param int $categoryId Идентификатор категории для создания кворка
	 * @param array $stages Предложения по поэтапной оплате заказа
	 *
	 * @return int
	 *
	 * @throws \Core\Exception\JsonException
	 *
	 * Соответствие ошибок принимаемым полям (старый вариант отображения предложения)
	 * target: title - поле: kwork_name
	 * target: kwork_desc - поле: kwork_desc
	 * target: description - поле: description
	 * target: work_time - поле: kwork_duration
	 *
	 * Соответствие ошибок принимаемым полям (новый вариант отображения предложения)
	 * target: title - поле: stages[position][title] если position не представлен то он считается равным 0
	 * target: description - поле: description
	 * target: payer_price - поле: stages[position][payer_price]
	 * target: work_time - поле: kwork_duration
	 */
	public static function offerCustomKwork($payer, $kworkName, $kworkDesc, $kworkDuration, $kworkPrice, $sourceType, string $lang, float $priceLimit = 0, int $wantId = 0, int $categoryId = 0, $stages = []) {
		global $actor;

		if (!$lang) {
			throw new SimpleJsonException(Translations::t("Не задан язык предложения"));
		}

		OfferManager::checkCustomOfferPrice($kworkPrice, $lang, $priceLimit, $categoryId);

		$kwork = new KworkManager();

		$kworkDesc = self::kworkDescFilter($kworkDesc);

		$turnover = \OrderManager::getTurnover($actor->id, $payer, $lang);
		$comission = \OrderManager::calculateCommission($kworkPrice, $turnover, $lang);

		//Индивидуальный кворк создается в статусе "индивидуальный кворк"
		$kwork
			->setLang($lang)
			->setUserId($actor->id)
			->setTitle($kworkName)
			->setDescription($kworkDesc)
			->setWorkTime($kworkDuration)
			->setCategoryId($categoryId)
			->setCustomOfferPrice($kworkPrice);

		if (is_array($stages) && count($stages) > 1) {
			$kwork->setStagedOffer();
		}

		if ($kwork->save() === false) {
			throw new JsonValidationException(self::customKworkRenameErrorsFromKworkManager($kwork->get('errors')));
		}

		$orderId = OfferManager::createOffersOrder($payer, $sourceType, $kwork->getId(), 1, null, [], [], $priceLimit, $wantId);

		// Добавление предложений этапов заказа
		if (is_array($stages) && count($stages) > 1) {
			OrderStageManager::saveStages($orderId, $stages);
		}

		return $orderId;
	}

	/**
	 * Переименование ошибок приходящих из KworkManager
	 *
	 * @param array $errors Массив ошибок
	 *
	 * @return array
	 */
	public static function customKworkRenameErrorsFromKworkManager($errors) {
		if (!OrderStageOfferManager::isTester()) {
			foreach ($errors as $key => $error) {
				if ($error["target"] == "description") {
					$errors[$key]["target"] = "kwork_desc";
				}
			}
		}
		return $errors;
	}


	/**
	 * Заменить перводы строк на <br>, убрать лишние пробелы
	 * @param string $kworkDesc
	 * @return string
	 */
	public static function kworkDescFilter($kworkDesc)
	{
		$kworkDesc = str_replace("\r\n", "\n", $kworkDesc);
		$kworkDesc = str_replace("\n", "<br>", $kworkDesc);
		$kworkDesc = preg_replace("/ +/", " ", $kworkDesc);
		return $kworkDesc;
	}

	/**
	 * Убрать лишние пробелы
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function commentFilter($value) {
		$value = html_entity_decode($value);
		$value = preg_replace("/(\r\n)+/", "\r\n", $value);
		$value = preg_replace("/(\n)+/", "\n", $value);
		$value = str_replace(": \\", "&#58;&#92;", $value);
		$value = str_replace("\\", "&#92;", $value);
		return preg_replace("/ +/", " ", $value);
	}

	/**
	 * Типы source_type разрешенные для заказа через kwork повсюду
	 *
	 * @return array
	 */
	public static function offerAllowedSourceTypes(): array
	{
		return [
			OrderManager::SOURCE_ANYWHERE,
			OrderManager::SOURCE_ANYWHERE_PRIVATE,
			OrderManager::SOURCE_INBOX,
			OrderManager::SOURCE_INBOX_PRIVATE,
			OrderManager::SOURCE_WANT,
			OrderManager::SOURCE_WANT_PRIVATE
		];
	}

	/**
	 * Есть ли у продавца предложения в конкретной языковой версии
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param string $lang Язык
	 *
	 * @return bool
	 */
	public static function isUserHasOffers(int $userId, string $lang):bool {
		// Exists быстрее чем count
		return \Core\DB\DB::table(\OfferManager::TABLE_NAME)
			->join(\WantManager::TABLE_NAME, "want.id", "=", "offer.want_id")
			->whereIn("want.status", [\Model\Want::STATUS_ACTIVE, \Model\Want::STATUS_STOP, \Model\Want::STATUS_USER_STOP])
			->where("want.lang", $lang)
			->where("offer.user_id", $userId)
			->whereIn("offer.status", [
				\OfferManager::STATUS_ACTIVE,
				\OfferManager::STATUS_CANCEL,
				\OfferManager::STATUS_DONE,
				\OfferManager::STATUS_REJECT,
			])
			->exists();
	}

	/**
	 * Проверка является ли коммент клоном других комментов к предложениям продавца
	 *
	 * @param string $comment Текст коммента
	 * @param int $userId Идентфикатор пользователя
	 * @param wantId, id запроса в котором редактируется преддлжение
	 *
	 * @return bool
	 */
	public static function isCommentClone(string $comment, int $userId, $wantId = null) :bool {
		return false;
	}

	/**
	 * Получение минимальной цены индивидуального предложения
	 *
	 * @param string $lang Язык предложения
	 * @param float $wantPriceLimit Лимит цены запроса
	 * @param int $categoryId Рубрика предложения
	 *
	 * @return float
	 */
	public static function getMinCustomOfferPrice(string $lang, float $wantPriceLimit = 0, int $categoryId = 0) {
		/*
		 * #7947 для рубрики "Ссылки" минимальная цена предложения должна быть
		 * не меньше минимальной цены кворка в рубрике
		 */
		if ($categoryId > 0 && in_array($categoryId, array_keys(\CategoryManager::BASE_PRICE_BY_CATEGORY))) {
			$customMinPrice = \CategoryManager::getCategoryBasePrice($categoryId, $lang);
		} else {
			$customMinPrice = \KworkManager::getCustomMinPrice($lang);
		}

		if ($wantPriceLimit > 0) {
			$limitMinPrice = $wantPriceLimit * OfferManager::MIN_PRICE_MULTIPLIER;
			if ($limitMinPrice > $customMinPrice) {
				$customMinPrice = $limitMinPrice;
			}
		}

		return $customMinPrice;
	}

	/**
	 * Получение максимальной цены индивидуального предложения
	 *
	 * @param string $lang Язык предложения
	 * @param float $wantPriceLimit Лимит цены запроса
	 *
	 * @return float
	 */
	public static function getMaxCustomOfferPrice(string $lang, float $wantPriceLimit = 0) {
		$customMaxPrice = \KworkManager::getCustomMaxPrice($lang);
		if ($wantPriceLimit > 0) {
			$customMaxPrice = $wantPriceLimit;
		}
		return $customMaxPrice;
	}

	/**
	 * Получение предложений пользователя вместе с запросами
	 *
	 * @param int $userId Идентификатор пользователя
	 *
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Model\Offer[]
	 */
	public static function getUserOffers(int $userId) {
		return Offer::where(Offer::FIELD_USER_ID, $userId)
			->whereIn(Offer::FIELD_STATUS, [
				\OfferManager::STATUS_ACTIVE,
				\OfferManager::STATUS_CANCEL,
				\OfferManager::STATUS_DONE,
				\OfferManager::STATUS_REJECT,
			])
			->whereHas("want", function ($query) {
				$currentUser = \UserManager::getCurrentUser();

				// На RU сайте выводим RU и EN предложения (если юзер не отключил), на EN сайте - только EN предложения [тикет #6506]
				if (\Translations::isDefaultLang()) {
					$langs = [\Translations::DEFAULT_LANG];
					if (!$currentUser->disableEn) {
						$langs[] = \Translations::EN_LANG;
					}
				} else {
					$langs = [\Translations::EN_LANG];
				}
				$query->whereIn(\WantManager::F_STATUS, [\Model\Want::STATUS_ACTIVE, \Model\Want::STATUS_STOP, \Model\Want::STATUS_USER_STOP])
					->whereIn(\WantManager::F_LANG, $langs);
			})
			->orderByDesc(Offer::FIELD_ID)
			->with([
				"kwork",
				"user",
				"order",
				"want",
				"want.files",
				"want.user",
				"want.user.data",
				"want.user.badges",
			])
			->paginate(\App::config("per_page_items"));
	}

	/**
	 * Метод проверяет достаточно ли данных в настройках, что бы оставить предложение к запросу
	 * @return string|bool
	 */
	public static function checkProfileInfoToAdd() {
		$user = UserManager::getCurrentUser();
		$errors = [];
		if ($user->profilepicture == \Model\User::PROFILE_PICTURE_DEFAULT) {
			$errors[] = self::CANNOT_ADD_AVATAR;
		}
		// проверяем длину описания для рукворка и en-кворка
		if ((Translations::getLang() == Translations::DEFAULT_LANG && mb_strlen($user->description, "utf-8") < UserManager::MIN_USER_DESCRIPTION_LENGTH) ||
			(Translations::getLang() == Translations::EN_LANG && strlen($user->descriptionen) < UserManager::MIN_USER_DESCRIPTION_LENGTH)
		) {
			$errors[] = self::CANNOT_ADD_DESCRIPTION;
		}

		// временно отключено
		if (false) {
			if (count($errors) > 1) {
				return self::CANNOT_ADD_ALL;
			} elseif (count($errors) == 1) {
				return $errors[0];
			}
		}

		return true;
	}

	/**
	 * Проверяет видим ли заказ для пользователя
	 * @param int offerId
	 * @return bool
	 */
	public static function isOfferAccessableByWantId($offerId) {
		$actor = \UserManager::getCurrentUser();
		return Offer::where(Offer::FIELD_WANT_ID, $offerId)->where(Offer::FIELD_USER_ID, $actor->id)->where(Offer::FIELD_STATUS, '!=', OfferManager::STATUS_DELETE)->exists();
	}

	/**
	 * Проверка валидности комментария (он же "Как вы будете решать задачу клиента")
	 *
	 * @param string $comment Комментариий
	 * @param int $categoryId Идентификатор категории для создания кворка
	 * @param string $lang Язык создаваемого кворка и соответственно валюта
	 * @param bool $isCustomOffer
	 * @return array
	 * @throws Exception
	 */
	public static function checkComment($comment, $categoryId, $lang, $isCustomOffer): array {
		$errors = [];
		if ($comment == "") {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Введите комментарий"),
			];
			return $errors;
		}
		if (!UserManager::isModer() && KworkManager::getDesciptionLength($comment) > OfferManager::COMMENT_MAX_LENGTH) {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Максимальная длина описания %s символов", OfferManager::COMMENT_MAX_LENGTH),
			];
		} elseif (!UserManager::isModer() && KworkManager::getDesciptionLength($comment) < OfferManager::COMMENT_MIN_LENGTH) {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Минимальная длина описания %s символов", OfferManager::COMMENT_MIN_LENGTH),
			];
		}
		if (KworkManager::checkWithExcludes($comment) || preg_match(KworkManager::REGEXP_SYMBOL_DUPLICATE, $comment)) {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Текст не соответствует нормам русского языка"),
				"mistakes" => KworkManager::wrapDuplicateSymbols($comment)
			];
		}
		if (preg_match(KworkManager::REGEXP_BIG_WORD, $comment)) {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Превышена максимальная длина слов"),
				"mistakes" => KworkManager::wrapBigWord($comment)
			];
		}
		if (preg_match(KworkManager::REGEXP_SMALL_WORD, $comment)) {
			$errors[] = [
				"target" => "description",
				"text" => Translations::t("Текст не соответствует нормам русского языка"),
				"mistakes" => KworkManager::wrapSmallWord($comment)
			];
		}
		if (!$isCustomOffer) {

			// Проверка языка
			// Для русского языка есть минимальный порог русских букв в процентах при котором текст валиден
			if ($lang == Translations::DEFAULT_LANG) {
				if (Helper::getRuSymbolsPercentInString(Helper::removeLinks($comment)) < KworkManager::MIN_LANG_PERCENT) {
					$errors[] = [
						"target" => "description",
						"text" => Translations::t("Не менее %s%% текста должно быть написано на русском языке.", KworkManager::MIN_LANG_PERCENT),
					];
				}
			}
			// Для английского языка валидна только латиница
			if ($lang == Translations::EN_LANG) {
				if (Helper::countNotEnSymbols(Helper::removeLinks($comment)) > 0) {
					$errors[] = [
						"target" => "description",
						"text" => Translations::t("Текст должен быть только на английском языке."),
					];
				}
			}
		}

		return $errors;
	}

	/**
	 * Проверка цены предложения с выбрасыванием эксепшена
	 *
	 * @param float $kworkPrice Цена кворка
	 * @param string $lang Языке кворка
	 * @param float $priceLimit Лимит цены предложения
	 * @param int $categoryId Рубрика предложения
	 *
	 * @throws \Core\Exception\SimpleJsonException
	 */
	public static function checkCustomOfferPrice(float $kworkPrice, string $lang, float $priceLimit = 0, int $categoryId = 0) {
		$minPrice = OfferManager::getMinCustomOfferPrice($lang, $priceLimit, $categoryId);
		$maxPrice = OfferManager::getMaxCustomOfferPrice($lang, $priceLimit);

		if ($kworkPrice < $minPrice || $kworkPrice > $maxPrice) {
			if ($maxPrice == $minPrice) {
				throw new SimpleJsonException(Translations::t("Стоимость должна быть равна %s", $minPrice));
			}
			throw new SimpleJsonException(Translations::t("Стоимость может быть от %s до %s", $minPrice, $maxPrice));
		}
	}

	/**
	 * Округляет сумму для показа суммы заработка продавца на бирже
	 *
	 * @param $amount
	 * @return string
	 */
	public static function roundPayerIncome($amount) : string {
		if ($amount < 1) {
			return $amount;
		}

		if ($amount < 10000) {
			$amount = "до 10к";
		} elseif ($amount >= 1000000) {
			$amount = floor(($amount / 1000000) * 10) / 10;
			$amount = str_replace(".", ",", $amount) . "М+";
		} else {
			$amount = floor($amount / 1000) . "к+";
		}

		return $amount;
	}

}
