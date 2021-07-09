<?php

use Core\DB\DB;
use Core\Routing\UrlGeneratorSingleton;
use Currency\CurrencyExchanger;
use Model\Kwork;
use Model\Offer;
use Model\Order;
use Model\Rating;
use Model\User;
use Model\Want;
use \Illuminate\Database\Eloquent;

class UserManager {

	/*
	 * Основная таблица пользователей
	 */
	const TABLE_NAME = 'members';

	/**
	 * Поля таблицы members
	 */
	const FIELD_USERID = "USERID"; //Идентификатор пользователя
	const FIELD_FUNDS = "funds"; //Средства пользователя доступные для вывода
	const FIELD_BFUNDS = "bfunds"; // Бонусные средства
	const FIELD_BILL_FUNDS = "bill_funds"; //Средства по безналичному расчету
	const FIELD_CARD_FUNDS = "card_funds";//Средства заведенные с карты
	const FIELD_CARD_FUNDS_TRANSFER_AMOUNT = "card_funds_transfer_amount";

	const FIELD_PASSWORD = "password"; //Пароль в хешированном виде
	const FIELD_USERNAME = "username"; //Логин пользователя в системе
	const FIELD_EMAIL = "email"; //Закодированная строка email
	const F_VERIFIED = "verified"; //Верифицирован ли пользователь по email
	const F_PHONE = "phone"; //номер телефона
	const F_PHONE_VERIFIED = "phone_verified"; //верифицирован ли телефон
	const NOTIFICATION_PERIOD_DEFAULT = 3; //Параметр отправки уведомлений по умолчанию
	const UD_FIELD_USER_ID = "user_id"; //Идентификатор пользователя в дополнительной таблице
	const UD_FIELD_PROJECT_LETTER_DATE = "project_letter_date"; //Время последней отсылки уведомлений
	const UD_FIELD_SHOW_RECOMMENDATIONS_IN_TRACK_PAGE = "show_recommendations_in_track_page"; // опция показывать ли блок рекомендаций на странице трекинга
	const FIELD_QIWIID = "qiwiId";//Идентификатор киви
	const FIELD_NOTIFY_UNREAD_COUNT = "notify_unread_count";//Количество непрочитанных уведомлений
	const FIELD_HIDE_GUARANTEE_NOTIFICATION = "hide_guarantee"; // Нужно ли скрывать блок о гарантии возврата

	/**
	 * Статус пользователя в системе. members.status
	 */
	const FIELD_STATUS = "status";

	/**
	 * Роль пользователя в системе. members.role
	 */
	const FIELD_ROLE = "role";

	/**
	 * Продавец
	 */
	const TYPE_WORKER = 'worker';

	/**
	 * Покупатель
	 */
	const TYPE_PAYER = 'payer';

	/**
	 * Название типов пользователя
	 */
	const TYPE_TITLES = [
		self::TYPE_PAYER => "Покупатель",
		self::TYPE_WORKER => "Продавец"
	];

	/**
	 * Поле, обозначающее, заблокированы кворки администратором или нет
	 * <p>members.kwork_allow_status ENUM</p>
	 * <p>DEFAULT allow</p>
	 */
	const FIELD_KWORK_ALLOW_STATUS = "kwork_allow_status";
	const FIELD_KWORK_ALLOW_STATUS_ALLOW = "allow";
	const FIELD_KWORK_ALLOW_STATUS_DENY = "deny";

	const PAYMENT_QIWI = "qiwi";
	const PAYMENT_WM = "webmoney";
	const PAYMENT_CARD = "card";
	const PAYMENT_SOLARCARD = "solarcard";

	/**
	 * Время последней разблокировки кворков
	 * <p>members.last_abuse_date TIMESTAMP</p>
	 * <p>DEFAULT NULL</p>
	 */
	const FIELD_LAST_ABUSE_DATE = "last_abuse_date";

	/**
	 * Поле означающее есть ли уведомление для пользователя
	 * <p>members.isEvent TINYINT</p>
	 * <p>DEFAULT 0</p>
	 */
	const FIELD_IS_EVENT = "isEvent";

	/**
	 * Язык пользователя
	 */
	const FIELD_LANG = 'lang';

	/**
	 * Уровень блокировки для подсчета кол-ва дней для разблокировки кворков
	 * <p>members.abuse_block_level int</p>
	 * <p>DEFAULT 1</p>
	 */
	const FIELD_ABUSE_BLOCK_LEVEL = "abuse_block_level";

	/**
	 * Идентифкатор страны
	 */
	const FIELD_COUNTRY_ID = "country_id";

	const USER_STATUS_NOT_CONFIRMED = 'new';

	const USER_STATUS_ACTIVE = 'active';

	const USER_STATUS_BLOCKED = 'blocked';

	const USER_STATUS_DELETED = 'delete';

	const USER_NOT_VERIFIED = 0;

	const USER_VERIFIED = 1;

	const MAX_CATEGORY_COUNT = 4;

	//Константы при создании пользователя и отправки письма с подтверждением регистрации
	const REG_NOT_SIMPLE = 0; //при обычной регистрации

	const REG_SIMPLE_REQUEST = 1; //при регистрации по email при формировании запроса

	const REG_SIMPLE_SOCIAL_FB = 2; //при регистрации через facebook
	const REG_SIMPLE_SOCIAL_VK = 3; // при регистрации через vkontakte

	// время онлайн (таймаут онлайна пользователя)
	const LIVE_TIME = 120;

	//Дефолтная картинка профиля
	const PROFILE_PICTURE_DEFAULT = "noprofilepicture.gif";

	// Тип аватара
	const FIELD_AVATAR_TYPE = "avatar_type";
	const AVATAR_TYPE_NONE = "none";            // отсутствует (noprofilepicture.gif)
	const AVATAR_TYPE_BIG = "big";                // маленький (100х100)
	const AVATAR_TYPE_CHUNK = "chunk";            // маленький (100х100), на некоторых страницах (в профиле, редактировании профиля) пользователю показываем дефолтный аватар
	const AVATAR_TYPE_LARGE = "large";            // большой (200х200)

	/**
	 * Период рассылки "Интересные кворки" и "Хорошие новости"
	 */
	const LETTER_INTEREST_NEW_PERIOD = 28;

	const CATEGORY_SMM_ID = 46;

	// Пользвоатель просмотрел видео ролик о возможностях оформления кворка в 3 ценовых вариантах
	const LOOKED_LESSON = '1';

	private static $preLoadUsers = array();
	private static $usersData = array();

	const SESSION_AUTH_HASH = 'user_auth_hash';

	/**
	 * Ответы функции удаления карты Solar Staff
	 */
	const SOLAR_CARD_REMOVE_SUCCESS = 1;    // успешно удалена
	const SOLAR_CARD_REMOVE_ERROR = 2;      // ошибка
	const SOLAR_CARD_REMOVE_NOT_FOUND = 3;    // у юзера не привязана карта

	/**
	 * Статус продавца
	 */
	const FIELD_WORKER_STATUS = "worker_status";

	/**
	 * Константы статусов продавцов
	 */
	const WORKER_STATUS_NONE = "none";
	const WORKER_STATUS_FREE = "free";
	const WORKER_STATUS_BUSY = "busy";

	/**
	 * При включении статуса «Принимаю заказы» активировать все кворки
	 * или только те, которые были активны до включения статуса «Занят»
	 */
	const FIELD_WORKER_STATUS_SWITCH_ALL = "worker_status_switch_all";

	/**
	 * Тип портфолио
	 */
	const FIELD_PORTFOLIO_TYPE = "portfolio_type";

	const USER_LOGIN_LENGTH = 20;

	/**
	 * Минимальная длина описания в профиле
	 */
	const MIN_USER_DESCRIPTION_LENGTH = 100;

	/**
	 * Максимальная длина описания в профиле
	 */
	const MAX_USER_DESCRIPTION_LENGTH = 1200;

	const NOTIFICATION_PERIOD_UNSUBSCRIBE = -1;

	/*
	 * Поля с кол-вом отзывов по языкам
	 */
	const ALIAS_RATING_COUNT = [
		Translations::DEFAULT_LANG => "userRatingCount",
		Translations::EN_LANG => "cache_rating_count_en",
	];

	/**
	 * Алиас для хранения в redis признака is_dirty
	 */
	const REDIS_IS_DIRTY = "is_dirty";

	/**
	 * Типы пользователей
	 */
	const USER_TYPES = [
		\UserManager::TYPE_PAYER,
		\UserManager::TYPE_WORKER
	];

	const HIRED_PERCENT_LIMIT = 5;
	const WANTS_COUNT_LIMIT = 5;
	const CHATS_PER_DAY_LIMIT = 20;

	public static $profileCovers = [
		'default.jpg', 'header1.jpg', 'header2.jpg', 'header3.jpg',
		'header4.jpg', 'header5.jpg', 'header6.jpg', 'header7.jpg',
		'header8.jpg', 'header9.jpg', 'header10.jpg', 'header11.jpg'
	];

	/**
	 * Подключения трейта с api функциями пользователя
	 */
	use \User\Traits\Api;

	/**
	 * API метод
	 * Проверяет email на наличие в базе
	 *
	 * @param $email
	 *
	 * @return mixed
	 */
	public static function checkEmail($email) {
		$arReturn['success'] = self::checkEmailExists($email);
		if ($arReturn['success']) {
			$arReturn['in_stop_list'] = self::checkEmailOwnedByDeletedUser($email);
		}
		return $arReturn;
	}

	/**
	 * API метод
	 * Проверяет логин на наличие в базе
	 *
	 * @param $login
	 *
	 * @return mixed
	 */
	public static function checkLogin() {
		$arReturn['success'] = self::checkLoginExists(post('login'));
		return $arReturn;
	}

	/**
	 * Проверка пароля на простоту
	 * @param string $password
	 * @param string $username
	 * @return boolean
	 */
	public static function checkSimplePassword($password, $username) {
		$result = false;
		$filePath = App::config('libDir') . DIRECTORY_SEPARATOR . 'simple_passwords.list';
		if (!file_exists($filePath)) {
			if (self::checkPassUsername($password, $username)) {
				$result = true;
			}
		} else {
			$arrayPasswords = array_filter(array_map('trim', file($filePath)));
			if (in_array($password, $arrayPasswords)) {
				$result = true;
			} else {
				if (self::checkPassUsername($password, $username)) {
					$result = true;
				}
			}
		}
		return $result;
	}

	/**
	 * Сгенерировать login из email пользователя
	 * @param string $email
	 * @return string
	 */
	public static function generateUserName($email) {
		$arr = explode("@", $email);
		$userName = $arr[0];
		$userName = preg_replace("/[^a-zA-Z0-9_-]/i", '', $userName);

		if (mb_strlen($userName) < 4) {
			$userName .= str_repeat("0", 4 - mb_strlen($userName));
		}

		if (mb_strlen($userName) > UserManager::USER_LOGIN_LENGTH) {
			$userName = mb_substr($userName, 0, UserManager::USER_LOGIN_LENGTH);
		}

		while (!self::checkLoginExists($userName)) {
			if (mb_strlen($userName) == UserManager::USER_LOGIN_LENGTH) {
				$userName = mb_substr($userName, 0, 10);
			}

			$userName .= rand(0, 9);
		}

		return $userName;
	}

	/**
	 * Проверить что пароль не одинаков с именем пользователя или именем пользователя + 1|2 символа
	 * @param string $password - пароль
	 * @param string $username - имя пользователя
	 * @return boolean
	 */
	private static function checkPassUsername($password, $username) {
		$strlen = strlen($username);
		if (substr($password, 0, $strlen) == $username && strlen($password) <= $strlen + 2) {
			return true;
		}
		return false;
	}

	/**
	 * API метод
	 * Проверяет наличие новых уведомлений пользователя
	 * @param bool $tracks Проверять ли треки
	 * @param bool $messages Description
	 * @return mixed
	 */
	public static function checkNotify($tracks = true, $messages = true) {
		global $actor;

		$userNotifyInfo = [
			"success" => true,
			"data" =>
				[
					"new_message" => 0,
					"notify_unread_count" => 0,
					"last_tracks" => []
				]
		];
		if (!$actor || !$actor->id) {
			return $userNotifyInfo;
		}
		// есть ли непрочитанные сообщения
		if ($messages) {
			$userNotifyInfo["data"]["new_message"] = $actor->unread_dialog_count;
			$userNotifyInfo["data"]["dialog_data"] = null;
			$userNotifyInfo["data"]["warning_inbox_count"] = $actor->warning_inbox_count;
		}
		$userNotifyInfo["data"]["notify_unread_count"] = $actor->notify_unread_count;

		return $userNotifyInfo;
	}

	/**
	 * Проверяет email на наличие в базе
	 *
	 * @param string $email
	 *
	 * @param int|null $userId
	 *
	 * @return bool
	 */
	public static function checkEmailExists($email, $userId = null) {
		$condition = "";
		$params["email"] = Crypto::encodeString($email);

		if ($userId) {
			$condition = "email = :email AND USERID <> :userId";
			$params["userId"] = $userId;
		} else {
			$condition = "email = :email";
		}

		return App::pdo()->exist(self::TABLE_NAME, $condition, $params);
	}

	/**
	 * Проверяет занятость email удаленным пользователем
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public static function checkEmailOwnedByDeletedUser($email) {
		$condition = "email = :email AND status = :status";
		$params = [
			"email" => Crypto::encodeString($email),
			"status" => self::USER_STATUS_DELETED
		];
		return App::pdo()->exist(self::TABLE_NAME, $condition, $params);
	}

	public static function get($userId, $fields = []) {
		global $conn;

		$fields = empty($fields) ? ["USERID", "email", "lang"] : $fields;

		$query = "SELECT ";
		foreach ($fields as $field) {
			$query .= ($field == "USERID" ? "USERID as 'id'" : $field) . ",";
		}
		$query = trim($query, ",");
		$query .= " FROM members WHERE USERID = '" . mres($userId) . "'";

		$user = $conn->getEntity($query);
		if (isset($user->email)) {
			$user->email = Crypto::decodeString($user->email);
		}

		return $user;
	}

	/**
	 * Обновление полей пользователя
	 * @param int $userId Идентификатор пользователя
	 * @param array $fields Массив полей [fieldName => value]
	 * @return int|false Количество измененных записей
	 */
	public static function update($userId, $fields) {
		return App::pdo()->update(self::TABLE_NAME, $fields, "USERID = :userId", ["userId" => $userId]);
	}

	/**
	 * Регистрация попытки логина
	 *
	 * @param string $userName Логин или емейл пользователя
	 * @param array $types Массив с типами ошибок
	 *
	 * @return void
	 */
	public static function registerTryLogin($userName, array $types) {
		return;
	}

	public static function login() {
		global $conn;
		$session = Session\SessionContainer::getSession();

		$userName = post("l_username");
		$password = post("l_password");
		$rememberMe = post("l_remember_me");
		$r = post("r");

		$error = '';
		$authErrorType = array();
		$arResult = [
			'success' => false,
			'error' => '',
			'redirect' => ''
		];

		if ($userName == "") {
			$error = Translations::t("Нужно ввести логин.");
			$authErrorType[] = 'no_login';
		} elseif ($password == "") {
			$error = Translations::t("Нужно ввести пароль.");
			$authErrorType[] = 'no_password';
		}

		if ($error != "") {
			$arResult['error'] = $error;
			$arResult['recaptcha_show'] = false;
			return $arResult;
		}

		$encryptedPassword = Crypto::hashUserPassword($password);
		$encodedLogin = Crypto::encodeString($userName);
		$sql = "SELECT
					status, USERID, email, username, verified, addtime, password, lang
				FROM
					members
				WHERE
					(username='" . mres($userName) . "' OR email='" . mres($encodedLogin) . "')
					AND password='" . mres($encryptedPassword) . "'";

		$userEntity = $conn->getEntity($sql);

		if (!$userEntity) {
			$error = Translations::t('Логин или пароль указаны неверно.');

			# нельзя использовать для изменения текста ошибки
			# don't use for change error message
			$authErrorType[] = UserManager::hasUser($userName) ? 'wrong_password' : 'wrong_login';
		} elseif ($userEntity->status == UserManager::USER_STATUS_NOT_CONFIRMED) {
			$error = Translations::t('Ваша учётная запись ещё не подтверждена.');
			$authErrorType[] = 'user_inactive';
		} elseif ($userEntity->status == UserManager::USER_STATUS_DELETED) {
			$error = Translations::t('Ваша учётная запись заблокирована.');
			$authErrorType[] = 'user_inactive_2';
		}

		if ($error != "") {
			$arResult['error'] = $error;
			return $arResult;
		}

		UserManager::afterAuthActions($userEntity->USERID);

		$arResult['success'] = true;

		$arResult['action_after'] = post('action_after');
		$session->set('api_after_action', post('action_after'));

		$urlFrom = str_replace(App::config("baseurl"), "", explode("?", $_SERVER["HTTP_REFERER"])[0]);

		foreach (Helper::DO_NOT_REDIRECT_BACK as $item) {
			if (strpos($urlFrom, $item) === 0) {
				$arResult["action_after"] = "index_redirect";
				$session->set('api_after_action', "index_redirect");
				break;
			}
		}

		self::updateOnlineStatus($userEntity->USERID, true);

		return $arResult;
	}

	/**
	 * Проверка надо ли редиректить англичанина
	 *
	 * @param string $lang Язык юзера
	 * @return bool
	 */
	public static function needEnRedirect($lang) {
		return Translations::getLang() == Translations::DEFAULT_LANG && $lang == Translations::EN_LANG;
	}

	/**
	 * Проверка существует ли пользователь в базе
	 *
	 * @param string $login Имя пользователя или email
	 *
	 * @return bool
	 */
	public static function hasUser($login) {
		return (boolean)App::pdo()->fetchScalar("SELECT 1 FROM members WHERE (username=:login OR email=:email)", [
			"login" => $login,
			"email" => Crypto::encodeString($login)
		]);
	}

	/**
	 * Обновление последнего входа в таблице members
	 *
	 * @param int $userId Идентификатор пользователя
	 *
	 * @return void
	 */
	public static function updateLastLogin($userId) {
		App::pdo()->execute("UPDATE members SET lastlogin=:lastlogin, lip=:lip WHERE USERID=:userid", [
			"lastlogin" => time(),
			"lip" => $_SERVER['REMOTE_ADDR'],
			"userid" => $userId
		]);
	}

	/**
	 * Регистрация пользователя (через ajax, всплывающая форма)
	 * входящие данные через post()
	 *
	 * @return array
	 */
	public static function signup() {
		$redirect = '';
		$session = Session\SessionContainer::getSession();

		$arResult = [
			'success' => false,
			'errors' => [],
			'redirect' => ''
		];

		$userEmail = post("user_email");
		$userUsername = post("user_username");
		$userPassword = post("user_password");
		$r = stripslashes(post("r"));
		$promoCode = post("user_promo");
		$countryId = post("country");

		if (post('userType') == 1) {
			$userType = self::TYPE_PAYER;
		} else {
			$userType = self::TYPE_WORKER;
		}

		$error = UserManager::signupValidate($userEmail, $userUsername, $userPassword, $promoCode, $userType, $countryId);

		if (!empty($error)) {
			foreach ($error as $k => $v) {
				$arResult['errors'][$k] = $v['error'];
				if (isset($v["recaptcha_show"])) {
					$arResult["recaptcha_show"] = $v["recaptcha_show"];
				}
			}
			return $arResult;
		}

		$userFields = [
			'email' => Crypto::encodeString($userEmail),
			'username' => ['value' => $userUsername, 'PDOType' => PDO::PARAM_STR],
			'password' => Crypto::hashUserPassword($userPassword),
			'type' => $userType
		];

		if (!\Translations::isDefaultLang()) {
			$userFields[\UserManager::FIELD_COUNTRY_ID] = $countryId;
		}
		$addtime = time();
		$userId = UserManager::create(
			$userFields,
			false,
			!App::isMirror(),
			UserManager::REG_NOT_SIMPLE,
			null,
			$promoCode
		);

		if ($userId != "" && is_numeric($userId) && $userId > 0) {
			$redirect = base64_decode($r);
			if ($redirect == "" && $userType == self::TYPE_WORKER) {
				$redirect = 'for-sellers';
			}
		}

		$arResult['success'] = true;

		$arResult['redirect'] = '/' . $redirect;
		$arResult['action_after'] = post('action_after');
		$session->set('api_after_action', post('action_after'));

		return $arResult;
	}

	/**
	 *  Метод для Api - обертка self::simpleSignUp
	 *
	 * @return array
	 */
	public static function apiSimpleSignup() {
		$userEmail = post('user_email');
		$userType = self::TYPE_WORKER;
		if (post('userType') == 1) {
			$userType = self::TYPE_PAYER;
		}
		$res = self::simpleSignUp($userEmail, $userType);
		if (!empty($res["user_id"])) {
			if (App::isMirror()) {
				$token = \App::genMirrorAuthData($res["user_id"], $_SERVER["HTTP_REFERER"]);
				$redirect = UrlGeneratorSingleton::getInstance()->generate("new_project", ["success_registration" => 1]);
				$tokenData = [
					"user_id" => $res["user_id"],
					"redirect" => $redirect,
					"pushGtmSource" => 1,
					"addTime" => $res["addTime"]
				];
				$res["action_after"] = "mirror_redirect";
				$res["redirect"] = UrlGeneratorSingleton::getInstance()->generate("new_project", ["success_registration" => 1]);
				$res["token"] = $token;
			} else {
				$res["action_after"] = post("action_after");
			}
		}

		return $res;
	}


	/**
	 * Упрощённая регистрация пользователя только по email
	 *
	 * @param $userEmail
	 * @param $userType
	 *
	 * @return array
	 */
	public static function simpleSignUp($userEmail, $userType) {
		$error = '';

		if ($userEmail == "") {
			$error = Translations::t("Нужно ввести адрес электронной почты");
		} elseif (!verify_valid_email($userEmail)) {
			$error = Translations::t("Адрес электронной почты указан некорректно");
		} elseif (self::checkEmailExists($userEmail)) {
			$error = Translations::t("Адрес электронной почты, который Вы ввели, уже используется");
		}

		if ($error) {
			return ["success" => false, "message" => $error];
		}

		$userPassword = Helper::randomString(8);

		$userFields = [
			'email' => Crypto::encodeString($userEmail),
			'username' => self::generateUserName($userEmail),
			'password' => Crypto::hashUserPassword($userPassword),
			'type' => $userType
		];
		$addtime = time();
		$userId = UserManager::create(
			$userFields,
			false,
			!App::isMirror(),
			self::REG_SIMPLE_REQUEST,
			$userPassword
		);

		return ["success" => true, "user_id" => $userId, "addtime" => $addtime];
	}

	public static function changeemail() {
		$actor = self::getCurrentUser();

		$arResult = [
			"success" => false,
			"error" => "",
			"redirect" => "",
		];

		if (!$actor) {
			$arResult["error"] = Translations::t("Необходима авторизация");
			return $arResult;
		}

		$userEmail = post("user_email");
		$error = "";

		if ($userEmail == "") {
			$error = Translations::t("Нужно ввести адрес электронной почты.");
		} elseif (!verify_valid_email($userEmail)) {
			$error = Translations::t("Адрес электронной почты указан некорректно.");
		} elseif (UserManager::checkEmailExists($userEmail, $actor->id)) {
			$error = Translations::t("Адрес электронной почты, который Вы ввели, уже используется");
		}

		if ($error != "") {
			$arResult['error'] = $error;
			return $arResult;
		}

		$redirect = 'email_confirm_wait';
		$arResult['success'] = true;
		$arResult['redirect'] = '/' . $redirect;
		return $arResult;
	}

	public static function getOnlineUsers() {
		global $conn;
		$onlineTime = time() - (60 * 15);
		$sql = 'SELECT
				username as name
			FROM
				members
			WHERE
				live_date > ' . mres($onlineTime) . '
			ORDER BY name';

		$lastUsers = $conn->Execute($sql);
		return $lastUsers->getrows();
	}

	public static function checkLoginExists($login, $userId = null) {
		global $conn;

		$add = $userId ? " and USERID != '" . mres($userId) . "'" : "";
		$addHis = $userId ? " and user_id != '" . mres($userId) . "'" : "";

		$userExist = $conn->getCell("SELECT USERID FROM members WHERE username = '" . mres($login) . "' {$add} LIMIT 1");
		if ($userExist)
			return false;

		$loginExist = $conn->getCell("SELECT id FROM login_history WHERE login = '" . mres($login) . "' {$addHis} LIMIT 1");
		if ($loginExist)
			return false;

		return true;
	}

	public static function checkLoginHistoryExists($login, $userId) {
		global $conn;

		$loginExist = $conn->getCell("SELECT id FROM login_history WHERE login = '" . mres($login) . "' and user_id = '" . mres($userId) . "' LIMIT 1");
		if ($loginExist)
			return false;

		return true;
	}

	public static function checkPhoneExists($phone) {
		global $conn;
		global $actor;

		$exist = $conn->getCell("SELECT USERID FROM members WHERE phone = '" . mres($phone) . "' AND USERID <> '" . mres($actor->id) . "'");
		if ($exist)
			return false;

		return true;
	}

	public static function isPhoneNumberValid($phone) {
		$valid = true;
		$clearPhone = preg_replace("/[^0-9]/", '', $phone);
		if (strlen($clearPhone) < 10 || strlen($clearPhone) > 20) {
			$valid = false;
		}
		return $valid;
	}

	public static function changeUserType() {
		global $actor, $conn;

		$newType = $actor->type == self::TYPE_PAYER ? self::TYPE_WORKER : self::TYPE_PAYER;

		$sql = "UPDATE
					members
				SET
					type = '" . mres($newType) . "'
				WHERE USERID='" . mres($actor->id) . "'";

		$conn->Execute($sql);
		$actor->type = $newType;
		return true;
	}

	/**
	 * Возвращает метку времени для следующего письма интересной рассылки (send_interest)
	 * Если передать метку времени регистрации пользователя - будет выбран день недели
	 * при регистрации пользователя, иначе текущий день недели.
	 *
	 * @param int|null $signupTimestamp метка времени регистрации пользователя
	 * @param int|null $fromTimestamp текущая метка времени
	 * @param bool $sameTime сохранять время рассылки или использовать 9:05
	 *
	 * @return int метка времени для следующего письма
	 */
	public static function getNextLetterInterestDate($signupTimestamp = null, $fromTimestamp = null, $sameTime = false) {
		$timestamp = ($fromTimestamp !== null) ? $fromTimestamp : time();

		if (!$sameTime) {
			// текущий день, время 09:05
			$timestamp = strtotime(date("Y-m-d 09:05:00", $timestamp));
		}

		// плюс 2 недели
		$timestamp += 2 * Helper::ONE_WEEK;

		// если знаем дату регистрации пользователя - ставим день регистрации
		if ($signupTimestamp !== null) {
			$timestamp = self::setSameWeekDay($timestamp, $signupTimestamp);
		}

		// корректировка с учётом новогодних праздников и выходных
		return self::correctLetterDate($timestamp);
	}

	/**
	 * Установить день недели в timestamp1 как у timestamp2
	 *
	 * @param int $timestamp1 метка времени, которая требует изменения
	 * @param int $timestamp2 метка времени с желаемым днём
	 * @param bool $onlyForward устанавливать день недели только в будущее
	 *
	 * @return int скорректированная метка времени
	 */
	public static function setSameWeekDay($timestamp1, $timestamp2, $onlyForward = false) {
		$timestamp1Day = date('N', $timestamp1);
		$timestamp2Day = date('N', $timestamp2);
		$difference = $timestamp2Day - $timestamp1Day;

		if ($onlyForward && $timestamp2Day < $timestamp1Day) {
			$timestamp1 += Helper::ONE_WEEK;
		}

		$timestamp1 += $difference * \Helper::ONE_DAY;
		return $timestamp1;
	}

	/**
	 * Скорректировать дату с учетом выходных и праздников
	 *
	 * @param int $timestamp метка времени
	 * @param bool $correctByHolidays корректировать ли дату на официальные праздники РФ (переносить на след. рабочий день)
	 *
	 * @return int скорректированная метка времени
	 */
	public static function correctLetterDate($timestamp, $correctByHolidays = true) {
		// перенесем дату с выходных
		if (date("N", $timestamp) == 6) {
			// с субботы переносим на пятницу
			$timestamp -= \Helper::ONE_DAY;
		} elseif (date("N", $timestamp) == 7) {
			// с воскресенья переносим на понедельник
			$timestamp += \Helper::ONE_DAY;
		}

		// если не надо корректировать на праздники РФ, то выйдем
		if (!$correctByHolidays) {
			return $timestamp;
		}

		$date = date("Y-m-d", $timestamp);
		$time = date(" H:i:s", $timestamp);

		return strtotime($date . $time);
	}


	/**
	 * Создание пользователя
	 *
	 * @param array $fields - поля пользователя
	 * @param bool $isVerified - Верифицировать ли пользователя
	 * @param bool $signIn - логинить ли пользователя при регистрации
	 * @param int $typeReg - тип регистрации
	 * @param string $noHashPassword - пароль в незахешированном виде. используется при регистрации через соц сети и запросы
	 * @param string $promoCode Промокод при регистрации
	 *
	 * @return int|false
	 */
	public static function create($fields, $isVerified, $signIn = true, $typeReg = self::REG_NOT_SIMPLE, $noHashPassword = null, $promoCode = "") {

		$fields["addtime"] = time();
		$fields["lastlogin"] = time();
		$fields["profilepicture"] = self::PROFILE_PICTURE_DEFAULT;
		$fields["live_date"] = time();
		$fields["verified"] = 1;
		$fields["lang"] = Translations::getLang();
		$fields["cover"] = self::$profileCovers[mt_rand(0, count(self::$profileCovers) - 1)];
		$fields["ip"] = \BanManager::getIp();
		$fields["lip"] = \BanManager::getIp();

		$userId = App::pdo()->insert(self::TABLE_NAME, $fields, [DataBasePDO::INSERT_OPTION_IGNORE => true]);
		$fields["USERID"] = $userId;

		if (empty($userId)) {
			Log::daily('UserManager->create() error. ' . App::pdo()->lastError() . PHP_EOL . print_r($fields, true), 'error');
			return false;
		}


		if (intval($userId) < 2) {
			// костыль из-за insert ignore. лог писать не нужно, т.к. пользователь зареган
			return false;
		}

		$userDataRegType = in_array($fields["type"], [self::TYPE_PAYER, self::TYPE_WORKER]) ? $fields["type"] : "unknown";

		if ($userId != "" && is_numeric($userId) && $userId > 0) {

			$session = Session\SessionContainer::getSession();

			if ($signIn) {
				$session->set("USERID", $userId);
				CookieManager::set("userId", $userId, false, false);

				self::registerLogon($userId);
				self::resetAuthHash($userId);
				$userFieldValues = DataBasePDO::getBindValues($fields);
			}
		}
		return $userId;
	}

	public static function getOrderCount($userId) {
		global $conn;
		$query = "SELECT count(*) FROM orders WHERE USERID = '" . mres($userId) . "' LIMIT 1";
		return $conn->getCell($query) ? true : false;
	}

	public static function isModer() {
		return self::isModerOnly() || self::isAdmin();
	}

	public static function isAdmin() {
		return self::checkRole("admin");
	}

	/**
	 * Определяет по ID является ли пользователь админом, кэширует список админов
	 * @param $userId
	 * @return bool
	 */
	public static function isUserAdmin($userId) {
		return User::where(User::FIELD_ROLE, "=", User::ROLE_ADMIN)
			->where(User::FIELD_USERID, "=", $userId)
			->exists();

	}

	public static function isModerOnly() {
		return self::checkRole("moder");
	}

	private static function checkRole($role) {
		global $actor;
		return $actor->role == $role;
	}

	private static function checkRoleWithLocale($role, $lang = \Translations::DEFAULT_LANG) {
		global $actor;
		if (!$actor) {
			return false;
		}
		return $actor->role == $role && $lang == \Translations::getLang();
	}

	/**
	 * Возвращает $actor->isVirtual
	 * @return bool
	 */
	public static function isVirtual(): bool {
		global $actor;
		return is_null($actor) ? false : $actor->isVirtual;
	}

	/**
	 * Проверяет является ли actor покупателем
	 * @return bool
	 */
	public static function isPayer() {
		global $actor;
		return $actor->type == self::TYPE_PAYER;
	}

	/**
	 * Возвращает последние заказы пользователя влияющие рейтинг ответственности
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param int $limit Ограничение по количеству последних заказов
	 * @return array|bool Массив объектов заказов
	 */
	public static function getServiceOrders($userId, $limit = 30) {
		$whenSql = "";
		$conditionsSql = [];
		foreach (TrackManager::getBadCancelConditions() as $conditionName => $condition) {
			$whenSql .= " WHEN " . TrackManager::getBadCancelNameSql($conditionName) . " THEN " . $conditionName;
			$conditionsSql[] = TrackManager::getBadCancelNameSql($conditionName);
		}
		$conditionSql = "(" . implode(") OR (", $conditionsSql) . ")";
		$sql = "SELECT
					o.OID, o.status, t.type, t.reason_type, t.reply_type,
					CASE 
						$whenSql
						ELSE 'good_reason'
					END as 'cancel_reason'
				FROM
				  orders o
				JOIN track t ON o.last_track_id = t.MID
				WHERE 
					(
						o.status = :status_done 
						OR o.status = :status_cancel AND ($conditionSql)
					)
					AND o.worker_id = :user_id
					AND o." . OrderManager::F_RATING_IGNORE . " = 0
				ORDER BY IF(o.status = :status_done, o.date_done, o.date_cancel) DESC
				LIMIT $limit";

		return App::pdo()->fetchAll($sql, [
			"status_done" => OrderManager::STATUS_DONE,
			"status_cancel" => OrderManager::STATUS_CANCEL,
			"user_id" => $userId
		], PDO::FETCH_OBJ);
	}

	/**
	 * Возвращает статистику по рейтингу ответственности пользователя
	 *
	 * @param int $userId Идентификатор пользователя
	 *
	 * @return array
	 * ['service','done_count','expired_count','autocancel_count','worker_cancel_count',
	 * 'wrong_reason_cancel_count','payer_request_cancel_count','no_service_cancels', 'total']
	 */
	public static function getService($userId) {
		$orders = self::getServiceOrders($userId);

		$doneCount = 0;
		$expiredCount = 0;
		$autoCancelCount = 0;
		$workerCancelCount = 0;
		$wrongReasonCancelCount = 0;
		$noServiceCancelCount = 0;
		$payerRequestCancelCount = 0;

		foreach ($orders as $order) {
			if ($order->status == OrderManager::STATUS_DONE) {
				$doneCount++;
			} elseif ($order->cancel_reason == TrackManager::CONDITION_TIME_OVER) {
				$expiredCount++;
			} elseif ($order->cancel_reason == TrackManager::CONDITION_CRON_INPROGRESS) {
				$autoCancelCount++;
			} elseif ($order->cancel_reason == TrackManager::CONDITION_INPROGRESS_CANCEL) {
				$workerCancelCount++;
			} elseif (in_array($order->cancel_reason, [TrackManager::CONDITION_CONFIRM, TrackManager::CONDITION_INWORK_LATE_INCORRECT, TrackManager::CONDITION_INCORRECT, TrackManager::CONDITION_INCORRECT_AND_LATE, TrackManager::CONDITION_NO_COMMUNICATION_AND_LATE])) {
				$payerRequestCancelCount++;
			} elseif ($order->cancel_reason == TrackManager::CONDITION_PAYER_DISAGREE) {
				$wrongReasonCancelCount++;
			} else {
				$noServiceCancelCount++;    //не влияет на сервис и правильно делает
			}
		}

		$total = $doneCount + $expiredCount + $autoCancelCount + $workerCancelCount + $wrongReasonCancelCount + $payerRequestCancelCount;

		$data = [
			"total" => $total,
			'service' => $total == 0 ? 0 : $doneCount / $total,
			'done_count' => $doneCount,
			'expired_count' => $expiredCount,
			'autocancel_count' => $autoCancelCount,
			'worker_cancel_count' => $workerCancelCount,
			'wrong_reason_cancel_count' => $wrongReasonCancelCount,
			'payer_request_cancel_count' => $payerRequestCancelCount,
			'no_service_cancels' => $noServiceCancelCount // Теперь тут всегда ноль, если чтото другое то искать ошибку
		];
		return $data;
	}

	public static function logout() {
		$session = Session\SessionContainer::getSession();

		$userId = $session->get("USERID");
		self::updateOnlineStatus($userId, false, null, true);

		$session->delete('USERID');
		$session->delete('FB');
		$session->delete('foxtoken');
		$session->delete('isVirtual');

		CookieManager::clear("userId");
	}

	public static function registerUserAuth() {
		global $actor;

		if (!$actor)
			return false;

		return true;
	}

	public static function clearCurrentUserAuthStat() {
		global $conn;

		$sql = 'DELETE FROM day_users_auth WHERE created<="' . date('Y-m-d H:i:s', time() - Helper::ONE_MONTH) . '"';
		$conn->execute($sql);
		return true;
	}

	public static function search($filters, $pageData) {
		global $conn;

		$query = "SELECT SQL_CALC_FOUND_ROWS
						*
					FROM members m
					WHERE m.username LIKE '%" . mres($filters['username']) . "%'
					ORDER BY m.username
					LIMIT " . mres($pageData['pagingstart']) . ", " . mres($pageData['items_per_page']);
		$data = $conn->getList($query);

		$result = [];
		$result['data'] = $data;
		$result['total'] = $conn->getRowsCount();
		return $result;
	}

	public static function getUserData($id) {
		self::loadInfo($id);
		return self::$usersData[$id];
	}

	/**
	 * Удалить из кеша закешированное значение пользователя
	 * @param int $id - идентификатор пользователя
	 */
	public static function vipeCacheUserData($id) {
		if (isset(self::$usersData[$id])) {
			unset(self::$usersData[$id]);
		}
	}

	public static function checkOnlineTime($user_id) {
		if (!App::config("redis.enable"))
			return false;

		return self::checkUserOnlineStatus((int)$user_id);
	}

	public static function getAvgWorkTime($userId) {
		return rand(0, 5);
	}

	public static function api_isPaymentPayed() {
		$result = [];
		if (self::isPaymentPayed(post('payment_id'))) {
			$result['result'] = 'success';
		} else {
			$result['result'] = 'false';
		}
		return $result;
	}

	public static function isPaymentPayed($paymentId) {
		global $conn, $actor;

		if (!$actor) {
			return false;
		}

		$query = "SELECT id, status FROM operation WHERE id = '" . mres($paymentId) . "' AND user_id = '" . mres($actor->id) . "'";
		$operation = $conn->getEntity($query);
		if ($operation->status == 'done') {
			return true;
		}
		return false;
	}

	/**
	 * Валидация регистрации пользователя
	 *
	 * @param string $userEmail Email пользователя
	 * @param string $userUsername Username пользователяь
	 * @param string $userPassword Пароль пользователя
	 * @param string $promoCode Промокод
	 * @param string $userType Тип пользователя worker|payer
	 * @param string|bool $countryId Идентификатор страны (если false то проверять не надо)
	 *
	 * @return array
	 */
	public static function signupValidate($userEmail, $userUsername, $userPassword, $promoCode = "", $userType = "", $countryId = false) {
		$error = [];

		$no_whitespace_password = str_replace(" ", "", $userPassword);
		$emailError = self::validateEmail($userEmail);
		if ($emailError) {
			$error["email"] = $emailError;
		}

		if ($userUsername == "") {
			$error["login"] = ["error" => \Translations::t("Нужно ввести логин."),
				"error_code" => \Mobile\Constants::CODE_EMPTY_LOGIN];
		} elseif (!preg_match("/^[a-zA-Z0-9-_]*$/i", $userUsername)) {
			$error["login"] = ["error" => \Translations::t("Логин может содержать только латинские буквы, цифры и знаки - и _"),
				"error_code" => \Mobile\Constants::CODE_INCORRECT_LOGIN];
		} elseif (mb_strlen($userUsername) < 4) {
			$error["login"] = ["error" => \Translations::t("Логин должен быть не короче 4-х символов"),
				"error_code" => \Mobile\Constants::CODE_SHORT_LOGIN];
		} elseif (mb_strlen($userUsername) > self::USER_LOGIN_LENGTH) {
			$error["login"] = ["error" => \Translations::t("Логин не может быть длиннее %s символов.", self::USER_LOGIN_LENGTH),
				"error_code" => \Mobile\Constants::CODE_LONG_LOGIN];
		} elseif (!self::checkLoginExists($userUsername)) {
			$error["login"] = ["error" => \Translations::t("Этот логин уже используется."),
				"error_code" => \Mobile\Constants::CODE_EXISTED_LOGIN];
		}

		if ($userPassword == "" || $no_whitespace_password == "") {
			$error["password"] = ["error" => \Translations::t("Нужно ввести пароль."),
				"error_code" => \Mobile\Constants::CODE_EMPTY_PASSWORD];
		} elseif (mb_strlen($no_whitespace_password) < 5) {
			$error["password"] = ["error" => \Translations::t("Длина пароля должна быть не менее 5 символов."),
				"error_code" => \Mobile\Constants::CODE_SHORT_PASSWORD];
		} elseif ($userUsername === $no_whitespace_password) {
			$error["password"] = ["error" => \Translations::t("Пароль совпадает с логином. Укажите, пожалуйста другой."),
				"error_code" => \Mobile\Constants::CODE_PASSWORD_EQUALS_LOGIN];
		} elseif (self::checkSimplePassword($no_whitespace_password, $userUsername)) {
			$error["password"] = ["error" => \Translations::t("Такой пароль легко взломать. Укажите, пожалуйста, другой."),
				"error_code" => \Mobile\Constants::CODE_SIMPLE_PASSWORD];
		}

		if ($promoCode != "") {
			$promoCodeError = self::validatePromocode($promoCode);
			if ($promoCodeError) {
				$error["promo"] = $promoCodeError;
			}
		}

		if (!empty($error)) {
			foreach ($error as $v) {
				Log::daily($v["error"], 'register_problem');
			}
		}

		return $error;
	}

	/**
	 * Валидация Email пользователя
	 *
	 * @param string $userEmail Email пользователя
	 * @param bool $isOauth Для OAuth аунтефикации
	 * @return array
	 */
	public static function validateEmail($userEmail, $isOauth = false) {
		$error = [];
		if ($userEmail == "") {
			$error = ["error" => \Translations::t("Нужно ввести адрес электронной почты."),
				"error_code" => \Mobile\Constants::CODE_EMPTY_EMAIL];
		} elseif (!verify_valid_email($userEmail)) {
			$error = ["error" => \Translations::t("Адрес электронной почты указан некорректно."),
				"error_code" => \Mobile\Constants::CODE_INCORRECT_EMAIL];
		} elseif ($user = self::getDataByEmail($userEmail, ["status",])) {
			if ($user->status == self::USER_STATUS_DELETED) {
				$error = ["error" => \Translations::t("Извините, данный адрес почты занесен в стоп-список. Регистрация с ним невозможна."),
					"error_code" => \Mobile\Constants::CODE_EMAIL_OWNED_BY_DELETED_USER];
			} elseif (!$isOauth || $user->verified) {
				$error = ["error" => \Translations::t("Адрес электронной почты, который Вы ввели, уже используется."),
					"error_code" => \Mobile\Constants::CODE_EXISTED_EMAIL];
			}
		}
		return $error;
	}

	public static function getData($userId) {
		global $conn;

		$query = "SELECT * FROM user_data WHERE user_id = '" . mres($userId) . "'";
		return $conn->getEntity($query);
	}

	/**
	 * Были ли заблокированы кворки по причине неактивности пользователя долгое время
	 * @return bool
	 */
	public static function isKworksDisabled(): bool {
		return false;
	}

	//Изменялся ли логин пользователем
	public static function isLoginChange() {
		global $conn;
		global $actor;

		return $conn->getCell("SELECT is_login_change FROM user_data WHERE user_id = " . $actor->id);
	}

	public static function api_closePollNotify() {
		global $actor;

		return ['result' => 'success'];
	}

	public static function api_cancelPollRefuse() {
		global $actor;

		return ['result' => 'success'];
	}

	public static function api_addReview() {
		global $actor, $conn;

		if (!$actor) {
			return ['result' => false];
		}

		$text = post('review_text');
		if ($text == '') {
			return ['result' => false];
		}

		$query = "INSERT INTO review SET user_id = '" . mres($actor->id) . "', text = '" . mres($text) . "', type = 'worker', status = 'new'";
		$conn->Execute($query);
		return ['result' => true];
	}

	public static function setCustomParam($param, $value, $userId = false) {
		return false;
	}

	public static function getCustomParam($param, $userId = false) {
		return false;
	}

	/**
	 * Зарегистрировать вход пользователя в систему
	 * @param int $userId Код пользователя
	 * @return mixed pdo()->execute
	 */
	public static function registerLogon($userId) {
		return true;
	}

	/**
	 * Загрузить информацию о пользователях в кеш
	 * @param int|array $usersId Код пользователя, или массив кодов
	 * @return boolean true - данные загружены
	 */
	public static function loadInfo($usersId) {
		$usersId = (array)$usersId;
		$usersId = Helper::intArrayNoEmpty($usersId);
		$usersId = array_merge($usersId, array_keys(self::$preLoadUsers));
		$hasKeys = array_keys(self::$usersData);
		$loadUsersId = array_diff($usersId, $hasKeys);
		if (empty($loadUsersId)) {
			return true;
		}
		$loadUsersId = array_unique($loadUsersId);
		$sql = 'SELECT * 
			FROM members 
			WHERE USERID IN (' . implode(',', $loadUsersId) . ')';
		$rows = App::pdo()->fetchAllNameByColumn($sql, 'USERID');
		foreach ($rows as $userId => $row) {
			self::$usersData[$userId] = $row;
		}
		self::$preLoadUsers = array();
		return true;
	}

	/**
	 * Добавить пользователя в очередь загрузки информации
	 * @param int|array $usersId Код пользователя, или массив кодов
	 */
	public static function preLoadInfo($usersId) {
		$usersId = (array)$usersId;
		$usersId = Helper::intArrayNoEmpty($usersId);
		foreach ($usersId as $userId) {
			if (!isset(self::$usersData[$userId]) && empty(self::$preLoadUsers[$userId])) {
				self::$preLoadUsers[$userId] = true;
			}
		}
	}

	/**
	 * Получить информацию пользователей по логин. Кешируется
	 * @param string $username Логин пользователя
	 * @return array Информация о пользователей
	 */
	public static function getInfoByLogin($username) {
		foreach (self::$usersData as $userInfo) {
			if ($userInfo['username'] == $username) {
				return $userInfo;
			}
		}

		$sql = 'SELECT * 
			FROM members 
			WHERE username = :username
			LIMIT 1';
		$userInfo = App::pdo()->fetch($sql, [
			"username" => ["value" => $username,
				"PDOType" => PDO::PARAM_STR]
		]);
		self::$usersData[$userInfo['USERID']] = $userInfo;
		return $userInfo;
	}


	public static function api_sendPhoneVerifyCode() {
		$actor = UserManager::getCurrentUser();

		$countryCode = post('phone_country_code');
		$phoneNumber = post('popup_phone');
		$phoneNumber = "+" . preg_replace('/[^0-9]/', '', $countryCode . $phoneNumber);

		if (!self::isPhoneNumberValid($phoneNumber)) {
			return [
				'result' => 'false',
				'error' => 'not_valid_number',
				'error_text' => \Translations::t('Введите корректный номер телефона')
			];
		}

		if (!self::checkPhoneExists($phoneNumber)) {
			return [
				'result' => 'false',
				'error' => 'wrong_number',
				'error_text' => \Translations::t('Этот номер телефона уже подтвержден в другом аккаунте. Пожалуйста, укажите другой номер.')
			];
		}

		$previousCodeList = [];

		if (count($previousCodeList) > 4) {
			return [
				'result' => 'false',
				'error' => 'sms_apptemps_exceed',
				'error_text' => \Translations::t('К сожалению, вы исчерпали лимит в 5 отправок SMS. Во избежание SMS-спама повторную отправку SMS необходимо запросить через %sСлужбу поддержки%s.',
					'<a href="' . App::config('baseurl') . '/conversations/support?goToLastUnread=1">', '</a>')
			];
		}
		return [
			'result' => 'success'
		];
	}

	/**
	 * Разрешен ли доступ к разделу Продвижения в соц сетях
	 * @return boolean true - запрещен, false - разрешен
	 */
	public static function isHideSocialSeo() {
		global $actor;
		return App::config("category.hide_smm") && (empty($actor) || empty($actor->id));
	}

	/**
	 * Получи авторизационный хеш
	 * @param array $actor Информация о пользователе
	 * @return str|boolean md5-хеш|false - если не передана информация о пользователе
	 */
	public static function getUserAuthHash(array $actor) {
		if (empty($actor)) {
			return false;
		}

		return md5("valid");
	}

	/**
	 * Сравнить auth-хеш
	 * @return bool true - хеши совпадают|false - хеши не совпадают
	 */
	public static function compareAuthHash(array $actor) {
		if ($actor['isVirtual']) {
			return true;
		}

		$mustHash = self::getUserAuthHash($actor);
		$currentHash = Session\SessionContainer::getSession()->get(self::SESSION_AUTH_HASH);
		return $mustHash === $currentHash;
	}

	/**
	 * Перезаписать auth-хеш
	 * @param int $userId ID пользователя
	 * @return boolean false в случае ошибки
	 */
	public static function resetAuthHash($userId) {
		if (empty($userId)) {
			return false;
		}
		$userId = intval($userId);

		$newAuthHash = UserManager::getUserAuthHash([$userId]);
		Session\SessionContainer::getSession()->set(self::SESSION_AUTH_HASH, $newAuthHash);
	}

	public static function api_hideTechnicalWorksNotification() {
		return;
	}

	/**
	 * Api функция для проверки введенных паролей
	 */
	public static function api_checkSettingsPasswords() {
		global $actor;
		$result = ["success" => false, "message" => ""];
		if ($actor->isVirtual) {
			return $result;
		}
		$pass1 = post("pass1");
		$pass2 = post("pass2");
		if ($pass1 !== $pass2) {
			$result["success"] = false;
			$result["message"] = "Пароль и подтверждение пароля не совпадают.";
			return $result;
		}
		if (self::checkSimplePassword($pass2, $actor->username) == true) {
			$result["success"] = false;
			$result["message"] = "Такой пароль легко взломать. Укажите, пожалуйста, другой.";
			return $result;
		}
		$result["success"] = true;
		return $result;
	}

	/**
	 * Активные заказы между пользователями
	 * @param $userId1 - ID текущего пользователя
	 * @param $userId2 - ID пользователя
	 * @return array
	 */
	public static function ordersBetweenUsers(?int $userId1, int $userId2): array {
		if (!$userId1 || !App::config("module.inbox_to_track.enable")) {
			return [];
		}

		$statuses = [
			OrderManager::STATUS_CANCEL,
			OrderManager::STATUS_DONE,
			OrderManager::STATUS_NEW,
		];

		/** @var Order[] $orders */
		$orders = Order::query()
			->with("orderNames")
			->select([
				Order::FIELD_OID,
				Order::FIELD_KWORK_TITLE,
				Order::FIELD_WORKER_ID,
			])
			->where(function(Eloquent\Builder $query) use ($userId1, $userId2) {
				$query->where(function(Eloquent\Builder $query2) use ($userId1, $userId2) {
					$query2->where(Order::FIELD_USERID, $userId1);
					$query2->where(Order::FIELD_WORKER_ID, $userId2);
				});
				$query->orWhere(function(Eloquent\Builder $query2) use ($userId1, $userId2) {
					$query2->where(Order::FIELD_USERID, $userId2);
					$query2->where(Order::FIELD_WORKER_ID, $userId1);
				});
			})
			->whereNotIn(Order::FIELD_STATUS, $statuses)
			->get();

		$result = [];
		foreach ($orders as $order) {
			$result[] = [
				"id" => $order->OID,
				Order::FIELD_KWORK_TITLE => $order->getOrderTitleForUser($userId1),
				Order::FIELD_WORKER_ID => $order->worker_id,
			];
		}

		return $result;
	}

	/**
	 * Возвращает объект текущего пользователя (который пока $actor).
	 * Смысл метода в инкапсуляции возможных будущих изменений работы (получения) с текущим пользователем.
	 *
	 * @return null|object
	 */
	public static function getCurrentUser() {
		global $actor;
		return $actor;
	}

	/**
	 * Возвращает ID текущего пользователя
	 *
	 * @return mixed
	 */
	public static function getCurrentUserId() {
		return self::getCurrentUser()->id;
	}

	/**
	 * Возвращает модель User для текущего пользователя
	 * @return User|null
	 */
	public static function getCurrentUserModel() {
		$user = self::getCurrentUser();
		return User::find($user->id);
	}

	/**
	 * Обновить данные пользователя
	 * @return null|object
	 */
	public static function reloadActor() {
		global $actor;
		$actor = getActor();
		return $actor;
	}

	/**
	 * Верифицировать пользователя
	 * @param int $userId - Идентификатор пользователя
	 * @return bool
	 */
	public static function verifyUser($userId) {
		global $actor;
		$userId = (int)$userId;
		if (!$userId) {
			return false;
		}
		App::pdo()->update(UserManager::TABLE_NAME, ["verified" => UserManager::USER_VERIFIED], "USERID = :userId", ["userId" => $userId]);
		if ($actor) {
			$actor = getActor();
		}
		return true;
	}

	/**
	 * Api функция для проверки email в настройках пользователя
	 * @param string post(email) - email для проверки
	 * @global $actor
	 */
	public static function api_checkSettingsUsername() {
		global $actor;
		$result = ["success" => false, "message" => ""];
		$username = post("username");
		if (!$actor) {
			$result["message"] = "Ошибка. Пользователь не найден";
			return $result;
		}
		if (!$username) {
			$result["message"] = "Введите логин";
			return $result;
		}
		if (!preg_match("/^[a-zA-Z0-9-_]*$/i", $username)) {
			$result["message"] = "Логин может содержать только латинские буквы, цифры и знаки - и _";
			return $result;
		}
		if (mb_strlen($username) < 4) {
			$result["message"] = "Логин должен быть не короче 4-х символов";
			return $result;
		}
		if (mb_strlen($username) > self::USER_LOGIN_LENGTH) {
			$result["message"] = "Логин не может быть длиннее " . self::USER_LOGIN_LENGTH . " символов";

			return $result;
		}
		if (mb_strtolower($username) != mb_strtolower($actor->username) && self::isLoginChange()) {
			$result["message"] = "Вы уже изменяли логин";
			return $result;
		}
		if (mb_strtolower($username) != mb_strtolower($actor->username) && !self::checkLoginExists($username, $actor->id)) {
			$result["message"] = "Этот логин уже используется";
			return $result;
		}
		$result["success"] = true;
		return $result;
	}


	/**
	 * Получить идентификатор пользователя по логину
	 * @param string $username
	 * @return int|bool
	 */
	public static function getIdByLogin($username): int {
		if (!$username) {
			return 0;
		}
		$sql = "SELECT " . self::FIELD_USERID . "
					FROM " . self::TABLE_NAME . "
				WHERE
					" . self::FIELD_USERNAME . " = :" . self::FIELD_USERNAME;
		return (int)App::pdo()->fetchScalar($sql, [self::FIELD_USERNAME => ['value' => (string)$username, 'PDOType' => \PDO::PARAM_STR]]);
	}

	/**
	 * Получить данные пользователя по email
	 * @param string $email
	 * @param array $fields
	 * @return object|bool
	 */
	public static function getDataByEmail($email, $fields = [self::FIELD_USERID]) {
		if (!$email) {
			return 0;
		}
		$sql = "SELECT " . mres(implode(", ", $fields)) . "
					FROM " . self::TABLE_NAME . "
				WHERE
					" . self::FIELD_EMAIL . " = :email 
				LIMIT 1";
		return App::pdo()->fetch($sql, ["email" => Crypto::encodeString($email)], PDO::FETCH_OBJ);
	}

	/**
	 * Получить данные пользователей по списку идентификаторов
	 * @param array $userIds
	 * @return array
	 */
	public static function getByIds(array $userIds): array {
		if (empty($userIds) || !is_array($userIds)) {
			return [];
		}
		$params = [];
		$userIds = App::pdo()->arrayToStrParams($userIds, $params, PDO::PARAM_INT);
		$sql = "SELECT
					*
				FROM
					" . self::TABLE_NAME . "
				WHERE
					" . self::FIELD_USERID . " IN ({$userIds})";
		return App::pdo()->fetchAllNameByColumn($sql, 0, $params);
	}

	/**
	 * Возвращает absolute url к изображению аватара пользователя
	 * @param string $profileImage название файла
	 * @param string $size размер возвращаемого изображения \UserManager::AVATAR_SIZE_*
	 * @return string
	 */
	public static function getUserAvatar(string $profileImage, string $size): string {
		$filePath = implode("/", [\App::config('membersprofilepicdir'), $size, $profileImage]);
		if (file_exists($filePath)) {
			$profileImage = implode("/", [\App::config('membersprofilepicurl'), $size, $profileImage]);
		} else {
			$profileImage = implode("/", [\App::config('membersprofilepicurl'), $size, self::PROFILE_PICTURE_DEFAULT]);
		}

		return $profileImage;
	}

	/**
	 * Проверка и смена пароля текущего пользователя
	 *
	 * @param string $password Новый пароль
	 * @throws \Exception\FactoryIllegalParameterException
	 * @throws \Exception\FactoryIllegalTypeException
	 * @throws \Exception\JsonException
	 * @throws \Exception\MailSendEmptyEngineException
	 */
	public static function changePassword($password) {
		$user = self::getCurrentUser();

		if (UserManager::checkSimplePassword($password, $user->username) == true) {
			throw new \Exception\JsonException(
				Translations::t("Такой пароль легко взломать. Укажите, пожалуйста, другой"),
				\Mobile\Constants::CODE_SIMPLE_PASSWORD);
		}

		self::updatePassword($password, $user);
	}

	/**
	 * Смена пароля пользователя
	 *
	 * @param string $password Пароль
	 * @param object $user Пользователь - объект со свойствами:
	 *   - int id Id пользователя
	 *   - string email Email пользователя
	 *   - string lang Язык пользователя
	 */
	public static function updatePassword($password, $user) {
		User::whereKey($user->id)
			->update([
				User::FIELD_PASSWORD => $password,
			]);
	}

	/**
	 * Пользователь просмотрел ролик о возможностях создания кворков в 3 ценовых категориях
	 *
	 * @return bool
	 */
	public static function api_setLookedLesson($user_id = null) {
		global $conn, $actor;

		if (is_null($user_id)) {
			if (!empty($actor)) {
				$user_id = $actor->USERID;
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Расчет и сохранение статуса продавца
	 * @param int $userId Id пользователя
	 * @return mixed Возвращает рассчитанный статус или false в случае ошибки
	 */
	public static function updateWorkerStatus($userId) {
		$status = self::calcWorkerStatus($userId);

		$result = self::setWorkerStatus($userId, $status);

		if ($result === false) {
			return false;
		}

		return $status;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Расчет статуса продавца
	 * @param int $userId Id пользователя
	 * @return string
	 */
	public static function calcWorkerStatus($userId) {
		{ // Если есть активные кворки
			$sql = "SELECT COUNT(*)
					FROM " . KworkManager::TABLE_KWORKS . "
					WHERE " . KworkManager::FIELD_USERID . " = :userId
						AND " . KworkManager::FIELD_ACTIVE . " = :active
						AND " . KworkManager::FIELD_FEAT . " = :feat";

			$params = [
				"userId" => $userId,
				"active" => KworkManager::STATUS_ACTIVE,
				"feat" => KworkManager::FEAT_ACTIVE,
			];

			$activeCount = App::pdo()->fetchScalar($sql, $params);

			if ($activeCount > 0) {
				return self::WORKER_STATUS_FREE;
			}
		}

		{ // Если нет активных кворков, но есть остановленные
			$sql = "SELECT COUNT(*)
					FROM " . KworkManager::TABLE_KWORKS . "
					WHERE " . KworkManager::FIELD_USERID . " = :userId
						AND " . KworkManager::FIELD_ACTIVE . " = :active
						AND " . KworkManager::FIELD_FEAT . " = :feat";

			$params = [
				"userId" => $userId,
				"active" => KworkManager::STATUS_ACTIVE,
				"feat" => KworkManager::FEAT_INACTIVE,
			];

			$featedCount = App::pdo()->fetchScalar($sql, $params);

			if ($featedCount > 0) {
				return self::WORKER_STATUS_BUSY;
			}
		}

		return self::WORKER_STATUS_NONE;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Установка статуса продавца
	 * @param int $userId Id пользователя
	 * @param string $status Статус
	 * @return bool
	 */
	public static function setWorkerStatus($userId, $status) {
		if (!in_array($status, [self::WORKER_STATUS_NONE, self::WORKER_STATUS_FREE, self::WORKER_STATUS_BUSY])) {
			return false;
		}

		$fields = [self::FIELD_WORKER_STATUS => $status];

		$result = App::pdo()->update(self::TABLE_NAME, $fields, self::FIELD_USERID . " = :userId", ["userId" => $userId]);

		return $result !== false;
	}

	/**
	 * Установить продавцу статус "Занят" за превышение штрафных баллов
	 *
	 * @param int $userId идентификатор продавца
	 * @return void
	 */
	public static function setWorkerBusy($userId) {
		$user = User::find($userId);
		if (!$user) {
			return;
		}

		try {
			// если кворки юзера не заблокированы администратором
			if ($user->kwork_allow_status != UserManager::FIELD_KWORK_ALLOW_STATUS_DENY) {

				// если статус продавца отличен от "Занят"
				if ($user->worker_status != self::WORKER_STATUS_BUSY) {

					// остановим все кворки продавца
					self::suspendAllKworks($userId);

					// установим продавцу статус "Занят"
					self::setWorkerStatus($userId, self::WORKER_STATUS_BUSY);
				}
			}

		} catch (Exception $exception) {
			// залогируем в daily/error лог
			Log::daily("Ошибка при установке продавцу (userId=$userId) статуса 'Занят' за превышение штрафных баллов: " . $exception->getMessage() . "\n" . $exception->getTraceAsString(), "error");
		}
	}

	/**
	 * Остановка всех кворков пользователя
	 *
	 * @param int $userId Идентификатор пользователя
	 *
	 * @throws \Exception
	 */
	private static function suspendAllKworks(int $userId) {
		$kworks = \Model\Kwork::where(KworkManager::FIELD_USERID, $userId)
			->where(KworkManager::FIELD_FEAT, KworkManager::FEAT_ACTIVE)
			->whereIn(KworkManager::FIELD_ACTIVE, KworkManager::READY_FOR_BULK_SWITCH_STATUSES)
			->get([KworkManager::FIELD_PID, KworkManager::FIELD_ACTIVE, KworkManager::FIELD_FEAT, KworkManager::FIELD_LANG])
			->toArray();

		$kworkIds = array_column($kworks, KworkManager::FIELD_PID);

		if (empty($kworkIds)) {
			return;
		}

		\Model\Kwork::where(KworkManager::FIELD_USERID, $userId)
			->update([KworkManager::FIELD_WORKER_STATUS_FEAT => DB::raw(KworkManager::FIELD_FEAT)]);

		\Model\Kwork::whereIn(KworkManager::FIELD_PID, $kworkIds)
			->update([
				KworkManager::FIELD_FEAT => KworkManager::FEAT_INACTIVE,
				KworkManager::FIELD_DATE_FEAT => Helper::now(),
			]);

		foreach ($kworkIds as $kworkId) {
			KworkManager::removeOffers($kworkId);
		}

		// Добавляем в лог записи об обновлении кворков
		foreach ($kworkIds as $kworkId) {
			KworkManager::logStatus($kworkId, null, KworkManager::FEAT_INACTIVE);
		}
	}

	/**
	 * Активация всех кворков пользователя
	 *
	 * @param int $userId Идентфикатор пользователя
	 * @param bool $switchAll Запускать все или только те, которые были активны до включения статуса «Занят»
	 *
	 * @throws \Exception
	 */
	private static function activateAllKworks(int $userId, $switchAll = true) {
		$query = \Model\Kwork::where(KworkManager::FIELD_USERID, $userId)
			->where(KworkManager::FIELD_FEAT, KworkManager::FEAT_INACTIVE)
			->whereIn(KworkManager::FIELD_ACTIVE, KworkManager::READY_FOR_BULK_SWITCH_STATUSES);

		if (!$switchAll) {
			$query = $query->where(KworkManager::FIELD_WORKER_STATUS_FEAT, KworkManager::FEAT_ACTIVE);
		}

		$kworks = $query->get();

		if ($kworks->isEmpty()) {
			return;
		}

		$wasActivated = 0;
		$lastError = null;
		// Сейчас логика следующая если есть кворки которые активируются - то просто молча активируем если даже есть неактивирующиеся.
		// Если же есть только неактивирующиеся то ошибку последнего выдаем пользователю
		// Если нужно будет переделать логику чтобы при первой ошибке выполнение прерывалось и откатывались предыдущие изменения
		// то нужно будет перевести на Core\DB все что вносит изменения в базу и поставить транзакцию.
		foreach ($kworks as $kwork) {
			try {
				KworkManager::activateLogic($kwork);
				$wasActivated++;
			} catch (Exception $exception) {
				$lastError = $exception->getMessage();
			}
		}

		if ($wasActivated == 0 && $lastError) {
			// Чтобы отличать юзерские эксепшены от системных
			throw new \Exception\JsonException($lastError, 500);
		}
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Api функция для смены статуса продавца
	 * @param string post(status) Новый статус
	 * @param string post(switch_all) Активировать все кворки или только те,
	 *     которые были активны до включения статуса «Занят»
	 * @return array
	 * @global $actor
	 */
	public static function api_switchWorkerStatus() {
		global $actor;

		$result = [
			"success" => false,
			"data" => [],
		];

		$status = post("status");
		$switchAll = post("switch_all");

		{ // Валидация параметров
			if (!$actor) {
				$result["data"]["error"] = Translations::t("Пользователь не найден");
				return $result;
			}

			if (empty($status)) {
				$result["data"]["error"] = Translations::t("Не указан статус");
				return $result;
			}

			if (!in_array($status, [self::WORKER_STATUS_NONE, self::WORKER_STATUS_FREE, self::WORKER_STATUS_BUSY])) {
				$result["data"]["error"] = Translations::t("Неизвестный статус: '%s'", $status);
				return $result;
			}

			if ($status === self::WORKER_STATUS_NONE) {
				$result["data"]["error"] = Translations::t("Невозможно установить статус '%s'", $status);
				return $result;
			}
		}

		// Если новый статус - «Принимаю заказы», то определяем, нужно ли активировать
		// все кворки или только те, которые были активны до включения статуса «Занят».
		// Если передано корректное значение, то используем его, иначе берем
		// значение из настроек пользователя
		if ($status === self::WORKER_STATUS_FREE) {
			if (in_array($switchAll, ["0", "1"])) {
				$switchAll = (bool)$switchAll;
			} else {
				$switchAll = (bool)$actor->worker_status_switch_all;
			}
		}

		{ // Запуск или остановка кворков
			try {
				if ($status === self::WORKER_STATUS_FREE) {
					UserManager::activateAllKworks($actor->id, $switchAll);
				} else {
					UserManager::suspendAllKworks($actor->id);
				}
			} catch (Exception $exception) {
				if ($exception instanceof \Exception\JsonException) {
					$result["data"]["error"] = $exception->getMessage();
				} else {
					$result["data"]["error"] = Translations::t("Ошибка при смене статуса");
				}
				return $result;
			}
		}

		// Получение актуального статуса
		$actualStatus = self::calcWorkerStatus($actor->id);

		// Сохранение актуального статуса
		if (!self::setWorkerStatus($actor->id, $actualStatus)) {
			$result["data"]["error"] = Translations::t("Ошибка при сохранении статуса");
			return $result;
		}

		$result["success"] = true;
		$result["data"]["status"] = $actualStatus;

		if ($result["data"]["status"] !== $status) {
			if ($result["data"]["status"] === self::WORKER_STATUS_NONE) {
				if ($status === self::WORKER_STATUS_FREE) {
					$result["data"]["message"] = Translations::t("У вас нет активных кворков");
				}
				if ($status === self::WORKER_STATUS_BUSY) {
					$result["data"]["message"] = Translations::t("У вас нет остановленных кворков");
				}
			}
		}

		return $result;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Api функция для получения подсказки о статусах
	 * @return array
	 */
	public static function api_getWorkerStatusHelp() {
		$smarty = \Smarty\SmartyBridge::getInstance();
		$html = $smarty->fetch("help/worker_status.tpl");
		return [
			"success" => true,
			"data" => ["html" => $html],
		];
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Api функция для сохранения настройки активации
	 * @param string post(value) Активировать все кворки или только те,
	 *     которые были активны до включения статуса «Занят»
	 * @return array
	 * @global $actor
	 */
	public static function api_setWorkerStatusSwitchAll() {
		global $actor;

		$result = [
			"success" => false,
			"data" => [],
		];

		$switchAll = post("value");

		if (!in_array($switchAll, ["0", "1"])) {
			$result["data"]["error"] = Translations::t("Некорректные значения параметров");
			return $result;
		}

		$fields = [self::FIELD_WORKER_STATUS_SWITCH_ALL => $switchAll];

		$update = App::pdo()->update(self::TABLE_NAME, $fields, self::FIELD_USERID . " = :userId", ["userId" => $actor->id]);

		if ($update === false) {
			$result["data"]["error"] = Translations::t("Ошибка при сохранении");
			return $result;
		}

		$result["success"] = true;

		return $result;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Получение количества остановленных кворков до включения статуса «Занят»
	 * @param int $userId Id пользователя
	 * @return int
	 */
	public static function getWorkerStatusFeatInactiveCount($userId) {
		$sql = "SELECT COUNT(*)
				FROM " . KworkManager::TABLE_KWORKS . "
				WHERE " . KworkManager::FIELD_USERID . " = :userId
					AND " . KworkManager::FIELD_WORKER_STATUS_FEAT . " = :feat";

		$inactiveCount = App::pdo()->fetchScalar($sql, ["userId" => $userId, "feat" => KworkManager::FEAT_INACTIVE]);

		return (int)$inactiveCount;
	}

	/**
	 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
	 * Api функция для проверки наличия предложений по запросам при остановке кворков
	 * @return array
	 */
	public static function api_checkWorkerOffers() {
		global $actor;

		$restrictedStatuses = [KworkManager::STATUS_DELETED, KworkManager::STATUS_CUSTOM];
		$sql = "SELECT PID FROM posts WHERE USERID = :userId AND active NOT IN (" . implode(", ", $restrictedStatuses) . ")";
		$kworkIds = App::pdo()->fetchAllByColumn($sql, 0, ["userId" => $actor->id]);

		$kworksWithOffer = KworkManager::getWithOffers($kworkIds);

		return [
			"success" => true,
			"data" => [
				"has_offers" => !empty($kworksWithOffer),
			],
		];
	}

	/**
	 * Возвращает валюту пользователя по Логину
	 * @param $userLogin
	 * @return array
	 */
	public static function api_getUserCurrencyByLogin($userLogin): array {
		$success = true;
		$result = self::getUserCurrencyByLogin((string)$userLogin);
		if (!$result) {
			$success = false;
			$result = \Translations::t("Пользователь не найден");
		}

		return [
			"success" => $success,
			"result" => $result,
		];
	}

	/**
	 * Возвращает валюту пользователя по Логину
	 * @param string $userLogin
	 * @return string
	 */
	public static function getUserCurrencyByLogin(string $userLogin): string {
		$sql = "SELECT " . UserManager::FIELD_LANG . "
				FROM " . UserManager::TABLE_NAME . "
				WHERE " . UserManager::FIELD_USERNAME . " = :" . UserManager::FIELD_USERNAME;
		$params = [
			UserManager::FIELD_USERNAME => $userLogin
		];
		$lang = \App::pdo()->fetchScalar($sql, $params);

		if ($lang) {
			$result = \Translations::getCurrencyByLang($lang);
		} else {
			$result = "";
		}

		return $result;
	}

	/**
	 * Проверка пользователя на тестера для английской версии сайта
	 * при выключенном module.lang.testers.enable проверка не выполняется
	 * @param $userId
	 * @return bool
	 */
	public static function isLanguageTester($userId): bool {
		return true;

		$testersEnable = App::config("module.lang.testers.enable");
		$testers = App::config("module.lang.testers");

		if ($testersEnable && in_array($userId, $testers))
			return true;

		return false;
	}

	/**
	 * Проверка онлайн статуса юзера
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function checkUserOnlineStatus($userId): bool {
		return false;
	}

	/**
	 * Отмечаем что пользователь в сети, отправляем пуш и обновляем время активности пользователя (опционально)
	 *
	 * @param int $userId
	 * @param bool $isLogin
	 * @param int|null $loginTime - время входа юзера, к нему прибавляется таймаут (null - time())
	 * @param bool $forceOffline Принудительная установка статуса оффлайн
	 */
	public static function updateOnlineStatus(int $userId, bool $isLogin, int $loginTime = null, bool $forceOffline = false): void {

		if (!App::config("redis.enable")) {
			return;
		}

		if ($isLogin) {
			// Если был запуск из крона, то не нужно обновлять в базе, иначе будет бесконечный онлайн
			if (!$loginTime) {
				self::updateUserLiveDate($userId);
			}
		}
	}

	/**
	 * Проверяет, если юзер - тестер
	 *
	 * @param int $userId идентификатор юзера
	 * @return bool
	 */
	public static function isTester($userId) {
		return in_array($userId, [1]);
	}

	/**
	 * Получить кол-во отзывов, взяв их из нужного поля входящего обьекта (cache_rating_count или cache_rating_count_en) в зависимости от языка
	 * @param mixed $kwork входящий обьект в виде ассоц. массива
	 * @return int
	 */
	public static function getUserRatingCount($kwork) {
		$lang = $kwork["lang"];
		if (!isset($lang)) {
			$lang = Translations::getLang();
		}
		return $kwork[self::ALIAS_RATING_COUNT[$lang]];
	}

	/**
	 * Метод проверяет имеет ли пользователь хотя бы 1 выполненный заказ в течении 6 месяцев, если нет, просмотр/лайк
	 * будет "грязным" (не будет учитыватья в статистике). Значение кешируется в Redis
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function isDirty(int $userId): bool {
		return self::getIsDirtyValue($userId);
	}

	/**
	 * Метод проверяет имеет ли пользователь хотя бы 1 выполненный заказ в течении 6 месяцев, если нет, просмотр/лайк
	 * будет "грязным" (не будет учитыватья в статистике)
	 * @param int $userId
	 * @return bool
	 */
	protected static function getIsDirtyValue(int $userId): bool {
		$sixMonthAgo = time() - 6 * \Helper::ONE_MONTH;
		$haveOrder = Order::where(Order::FIELD_STATUS, "=", \OrderManager::STATUS_DONE)
			->where(Order::FIELD_STIME, ">", $sixMonthAgo)
			->where(function($query) use ($userId) {
				$query->where(Order::FIELD_USERID, "=", $userId)
					->orWhere(Order::FIELD_WORKER_ID, "=", $userId);
			})
			->exists();

		return !$haveOrder;
	}

	/**
	 * Обновить пользовательские настройки отображения каталога
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param int $viewType Тип отображения. Определяется константами User::CATALOG_VIEW_*
	 * @return false|int
	 */
	public static function saveCatalogViewType(int $userId, int $viewType) {
		$fields = [\Model\User::FIELD_CATALOG_VIEW_TYPE => $viewType];
		return self::update($userId, $fields);
	}

	/**
	 * @param stdClass $userStd Информация о пользовате из getActor
	 * @return User Модель
	 */
	public static function getModelByStdClass(stdClass $userStd): User {
		$model = new User();
		return $model->newFromBuilder($userStd);
	}

	/**
	 * Проверяет, является ли пользователь заблокированным
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function isBlockedUser(int $userId): bool {
		return User::find($userId)->status == self::USER_STATUS_BLOCKED;
	}

	/**
	 * Определяет является ли текущий пользователь (actor) заблокированным
	 *
	 * @return bool
	 */
	public static function isActorBlocked(): bool {
		global $actor;
		if (!$actor) {
			return false;
		}
		return $actor->status == self::USER_STATUS_BLOCKED;
	}

	/**
	 * Определяет НЕ является ли текущий пользователь (actor) заблокированным
	 *
	 * @return bool
	 */
	public static function isActorNotBlocked(): bool {
		return !self::isActorBlocked();
	}

	/**
	 * Возвращает название типа пользователя
	 *
	 * @param string $type - тип пользователя
	 * @return string
	 * @throws Exception
	 */
	public static function getTypeTitle(string $type): string {
		if (isset(self::TYPE_TITLES[$type])) {
			$result = self::TYPE_TITLES[$type];
		} else {
			throw new Exception("Не верный тип пользователя");
		}

		return $result;
	}

	/**
	 * Получение ссылки последней посещенной пользователем рубрики
	 *
	 * @return string|null
	 */
	public static function getLastViewedCategoryLink() {
		$user = self::getCurrentUser();
		if ($user) {
			$categoryId = $user->{User::FIELD_LAST_VIEWED_CATEGORY_ID};
			if ($categoryId) {
				$category = CategoryManager::getCategoryFromCasheById($categoryId, Translations::getLang());
				if (!empty($category[CategoryManager::FIELD_SEO])) {
					return "/categories/{$category[CategoryManager::FIELD_SEO]}";
				}
			}
		}
		return null;
	}

	/**
	 * Получить фон для аватарки без картинки
	 * @param string $letter
	 * @return string
	 */
	public static function getAvatarColor($letter) {
		if (($letter >= 'a' && $letter <= 'd') || ($letter >= 'A' && $letter <= 'D') || $letter == 1) {
			$background = '#e17076';
		} elseif (($letter >= 'e' && $letter <= 'h') || ($letter >= 'E' && $letter <= 'H') || $letter == 2 || $letter == 3) {
			$background = '#faa774';
		} elseif (($letter >= 'i' && $letter <= 'l') || ($letter >= 'I' && $letter <= 'L') || $letter == 4 || $letter == 5) {
			$background = '#7bc862';
		} elseif (($letter >= 'm' && $letter <= 'p') || ($letter >= 'M' && $letter <= 'P') || $letter == 6 || $letter == 7) {
			$background = '#6ec9cb';
		} elseif (($letter >= 'q' && $letter <= 't') || ($letter >= 'Q' && $letter <= 'T') || $letter == 8 || $letter == '-') {
			$background = '#65aadd';
		} elseif (($letter >= 'u' && $letter <= 'w') || ($letter >= 'U' && $letter <= 'W') || $letter == 9 || $letter == '_') {
			$background = '#a695e7';
		} else {
			$background = '#ee7aae';
		}

		return $background;
	}

	/**
	 * Получить домен из e-mail адреса
	 * @param string $email E-mail адрес
	 * @return bool|string Домен, false если email не валидный
	 */
	public static function getEmailDomain($email) {
		$pattern = '!@(?<domain>[a-z0-9.\-_]+)$!i';
		if (preg_match($pattern, $email, $match)) {
			return $match['domain'];
		}
		return false;
	}

	/**
	 * Обслуживается ли email через mail.ru?
	 * @param string $email E-mail адрес
	 * @return bool true - да, false - нет
	 */
	public static function isMailRuDomain($email) {
		$mailRuDomains = [
			"mail.ru",
			"list.ru",
			"bk.ru",
			"inbox.ru",
		];
		$emailDomain = self::getEmailDomain($email);
		return in_array($emailDomain, $mailRuDomains);
	}

	/**
	 * Получение идентификатора валюты пользователя по его идентификатору (со статическим кешированием)
	 *
	 * @param int $userId
	 *
	 * @return int
	 */
	public static function getUserCurrencyId(int $userId) {
		return Translations::getCurrencyIdByLang(self::getUserLang($userId));
	}

	/**
	 * Получение языка пользователя по идентификатору со статическим кешированием
	 *
	 * @param int $userId
	 *
	 * @return string
	 */
	public static function getUserLang(int $userId) {
		$lang = &kwork_static(__CLASS__ . __FUNCTION__ . $userId);
		if (is_null($lang)) {
			$lang = User::whereKey($userId)->value(User::FIELD_LANG);
			if (is_null($lang)) {
				throw new RuntimeException("User not found");
			}
		}

		return $lang;
	}

	/**
	 * Генерация ссылки для виртуальной авторизации
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param string $userLang Язык пользователя (если не будет представлен то будет получен из базы по userId
	 * @param string $redirect Необязательный, страница редиректа (сейчас в контроллере поддерживаются только user/{username} и track?id={orderId})
	 *
	 * @return string
	 */
	public static function getVirtualAuthLink(int $userId, string $userLang = "", string $redirect = ""): string {
		if ($userId < 1) {
			throw new RuntimeException("Incorrect userId: $userId");
		}

		if (!empty($userLang) && !in_array($userLang, Translations::getLangArray())) {
			throw new RuntimeException("Incorrect user lang: $userLang");
		}

		if (empty($userLang)) {
			$userLang = self::getUserLang($userId);
		}

		$schema = (empty($_SERVER["HTTPS"]) ? "http" : "https") . "://";
		// Английских пользователей нужно редиректить через английскую админку иначе потом на русский сайт не зайдешь из-за куки
		if (\Translations::isDefaultLang()) {
			if ($userLang == \Translations::DEFAULT_LANG) {
				$host = \Translations::getDomainByLang(\Translations::DEFAULT_LANG);
			} else {
				$host = \Translations::getDomainByLang(\Translations::EN_LANG);
			}
		} else {
			$host = \Translations::getDomainByLang(\Translations::EN_LANG);
		}

		// Волшебные костыли для некорректных значений endomain rudomain на тестовых серверах
		preg_match("/dev|dev2|[d0-9]+/i", $_SERVER["HTTP_HOST"], $matches);
		$devPrefix = $matches[0];
		if ($devPrefix) {
			// для dev сервера добавим поддомен если там он еще не присутствует
			if (strpos($host, $devPrefix) !== 0) {
				$host = "{$devPrefix}.{$host}";
			}
		}

		$url = $schema . $host . "/administrator/members_login?USERID=" . $userId;
		// Если был адрес редиректа
		if ($redirect) {
			$url .= "&redirect=" . urlencode(urldecode($redirect));
		}

		return $url;
	}

	/**
	 * Есть ли у продавца кворки нужного языка в статусах
	 * 'активный', 'заблокирован за штрафные баллы', 'на паузе за большую очередь или нет портфолио'
	 * для проверки возможности послать ему индивидуальный заказ
	 *
	 * @param int $userId Идентификатор продавца
	 * @param string $lang Язык кворков
	 *
	 * @return bool
	 */
	public static function hasLangKworks(int $userId, string $lang): bool {
		return Kwork::where(\KworkManager::FIELD_USERID, $userId)
			->where(\KworkManager::FIELD_LANG, $lang)
			->whereIn(\KworkManager::FIELD_ACTIVE, [\KworkManager::STATUS_ACTIVE, \KworkManager::STATUS_SUSPEND, \KworkManager::STATUS_PAUSE])
			->exists();
	}

	/**
	 * Получить количество оплаченных проектов покупателя за два месяца
	 *
	 * @param User $user
	 *
	 * @return int
	 */
	public static function getUserDoneProjectsCount($user) {
		$count = 0;
		$wantIds = Want::where(Want::FIELD_USER_ID, $user->USERID)->pluck(Want::FIELD_ID);
		if (!$wantIds->isEmpty()) {
			$kworkIds = Offer::whereIn(Offer::FIELD_WANT_ID, $wantIds)->pluck(Offer::FIELD_KWORK_ID);
			if (!$kworkIds->isEmpty()) {
				$count = Order::whereIn(Order::FIELD_PID, $kworkIds)
					->where(Order::FIELD_STATUS, OrderManager::STATUS_DONE)
					->where(Order::FIELD_DATE_DONE, ">=", \Carbon\Carbon::now()->subMonth(2)->toDateTimeString())
					->count();
			}
		}
		return $count;
	}

	/**
	 * Количество новых чатов за сутки
	 *
	 * @param int $userId
	 *
	 * @return int
	 */
	public static function getCountChatsPerDay($userId) {
		return 0;
	}

	/**
	 * Нужна ли верификация телефона покупателю
	 *
	 * @param int $userId - Идентификатор пользователя
	 *
	 * @return bool
	 */
	public static function isNeedPayerPhoneVerification($userId) {
		if (empty($userId)) {
			return false;
		}
		$user = User::find($userId);
		if ($user && !$user->phone_verified && self::getUserDoneProjectsCount($user) == 0
			&& (($user->data->wants_hired_percent < self::HIRED_PERCENT_LIMIT && $user->data->wants_count >= self::WANTS_COUNT_LIMIT)
				|| (self::getCountChatsPerDay($userId) >= self::CHATS_PER_DAY_LIMIT))) {
			return true;
		}
		return false;
	}

	/**
	 * Обновить дату последней активности пользователя
	 *
	 * @param int $userId
	 * @see UsersLiveDateCron
	 */
	public static function updateUserLiveDate(int $userId): void {
		User::whereKey($userId)
			->update([
				User::FIELD_LIVE_DATE => time(),
			]);
	}

	/**
	 * Нужна ли верификация телефона продавцу
	 *
	 * @param stdClass|\Model\User $user - Объект $actor или \Model\User
	 *
	 * @return bool
	 */
	public static function isNeedWorkerPhoneVerification($user) {
		if ($user && !$user->phone_verified) {
			if ($user instanceof User) {
				$userModel = $user;
			} else {
				$userModel = User::find($user->USERID);
			}
			if ($userModel instanceof User) {
				// Продавцу не нужно подтверждать телефон, если у него
				// не менее 8 продаж, из которых не менее 90% успешных (не отмененных
				// по неуважительной причине и без отрицательных отзывов)
				return $userModel->data->order_done_persent < 90 ||
					$userModel->workerOrders()
						->whereIn(Order::FIELD_STATUS, [OrderManager::STATUS_DONE, OrderManager::STATUS_CANCEL])
						->count() < 8;
			}
		}
		return false;
	}

	/**
	 * Списание с баланса по полям
	 *
	 * @param int $userId Идентификатор пользователя
	 * @param array $fieldsChanges Массив полей для списания в формате [["field" => string, "amount" => float],...] из OperationManager::descFields
	 *
	 * @return int affected rows
	 */
	public static function balanceChargeOff(int $userId, array $fieldsChanges) {
		$userUpdateFields = [];
		foreach ($fieldsChanges as $item) {
			$userUpdateFields[$item["field"]] = DB::raw($item["field"] . " - " . (float)$item["amount"]);
		}
		return User::whereKey($userId)->update($userUpdateFields);
	}

	/**
	 * Общие действия выполняемые при авторизации
	 *
	 * @param int $userId Идентификатор пользователя
	 */
	public static function afterAuthActions(int $userId) {
		// Обновляем статус "Онлайн"
		UserManager::updateOnlineStatus($userId, true);

		// Добавляем идентификаторы в сесиии и куки
		\Session\SessionContainer::getSession()->set(UserManager::FIELD_USERID, $userId);
		\Session\SessionContainer::getSession()->set("foxtoken", NewfoxToken());
		CookieManager::set("userId", $userId, false, false);

		// Обновляем хеш авторизации в сесиии
		UserManager::resetAuthHash($userId);

		$sessionId = \Session\SessionContainer::getSession()->getSessionId();
		$authHash = \Session\SessionContainer::getSession()->get(self::SESSION_AUTH_HASH);

		$authData = [
			"userId" => $userId,
			"sessionId" => $sessionId,
		];
	}

	/**
	 * Установить в actor текущий мобильный токен авторизации
	 *
	 * @param string|null $token
	 */
	public static function setCurrentMobileToken(?string $token): void {
		global $actor;

		$actor->mobileToken = $token;
	}

	/**
	 * Текущий мобильный токен авторизации
	 *
	 * @return string|null
	 */
	public static function getCurrentMobileToken(): ?string {
		global $actor;

		return $actor->mobileToken;
	}

	/**
	 * Ручное пополнение средств на основной счёт пользователя
	 *
	 * @param int $adminId
	 * @param int $userId
	 * @param float $amount
	 * @return int|bool oprationId в случае успеха, false в случае ошибки
	 */
	public static function refillByAdmin($adminId, $userId, $amount) {
		$user = User::find($userId);
		$currencyId = \Translations::getCurrencyIdByLang($user->lang);
		$currencyRate = CurrencyExchanger::getInstance()->getCurrencyRateByLang($user->lang);
		$operationLang = \OperationLanguageManager::detectLang(\OperationManager::FIELD_TYPE_REFILL, $currencyId);
		$langAmount = \OperationLanguageManager::getAmountByType(\OperationManager::FIELD_TYPE_REFILL, $amount, $currencyId, $operationLang);

		$fieldsOperation = [
			\OperationManager::FIELD_USER_ID => $userId,
			\OperationManager::FIELD_TYPE => \OperationManager::FIELD_TYPE_REFILL,
			\OperationManager::FIELD_AMOUNT => $amount,
			\OperationManager::FIELD_STATUS => \OperationManager::FIELD_STATUS_DONE,
			\OperationManager::FIELD_PAYMENT => \OperationManager::FIELD_PAYMENT_ADMIN,
			\OperationManager::FIELD_DATE_DONE => \Helper::now(),
			\OperationManager::FIELD_SUB_TYPE => \OperationManager::FIELD_ST_REFILL_ADMIN,
			\OperationManager::FIELD_CURRENCY_ID => $currencyId,
			\OperationManager::FIELD_CURRENCY_RATE => $currencyRate,
			\OperationManager::FIELD_LANG => $operationLang,
			\OperationManager::FIELD_LANG_AMOUNT => $langAmount,
		];

		try {
			\App::pdo()->beginTransaction();
			$operationId = \OperationManager::create($fieldsOperation);
			$isFundsAdded = self::addBaseFunds($userId, $amount);
			if (!$operationId || !$isFundsAdded) {
				throw new \Exception('Ошибка ручного пополнения баланса');
			}
			\App::pdo()->commit();
		} catch (\Exception $e) {
			\App::pdo()->rollBack();
			\Log::daily('Ошибка транзакции' . print_r($e->getTrace(), true));
			return false;
		}
		return $operationId;
	}

	/**
	 * Добавить пользователю средства на основной счёт
	 *
	 * @param int $userId id пользователя
	 * @param float $amount сумма к зачислению
	 * @return bool true в случае успеха, false в случае ошибки
	 */
	public static function addBaseFunds($userId, $amount) {
		$sql = 'UPDATE ' . self::TABLE_NAME . '
                SET ' . self::FIELD_FUNDS . ' = ' . self::FIELD_FUNDS . ' + :funds
                WHERE ' . self::FIELD_USERID . ' = :userId';
		$affectedRows = App::pdo()->execute($sql, ['funds' => (float)$amount, 'userId' => (int)$userId]);
		return ($affectedRows == 1);
	}

	// актуализирует сумму в кошельке актора
	public static function refreshActorTotalFunds() {
		global $actor;

		if (!$actor) {
			return false;
		}

		$user = User::find($actor->id, [
			UserManager::FIELD_FUNDS,
		]);
		if (!$user) {
			return false;
		}

		$actor->totalFunds = $user->{UserManager::FIELD_FUNDS};
	}
}
