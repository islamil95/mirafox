<?php

class App {

	/**
	 * Режим работы - продакшен
	 */
	const APP_MODE_STAGE = 'stage';

	/**
	 * Режим работы - локальная разработка
	 */
	const APP_MODE_LOCAL = 'local';

	/*
	 * Константы баз данных
	 */
	const DB_MASTER = "master";
	const DB_SLAVE = "slave";
	const DB_WORK = "work";
	const DB_SPHINX = "sphinx";

	/*
	 * Валидные базы данных
	 */
	const VALID_DATABASES = [
		self::DB_MASTER,
	];

	/*
	 * Таблицы в БД self::DB_WORK
	 */
	const DB_WORK_TABLES = [];

	/**
	 * Получить название соединения БД по названию таблицы
	 *
	 * @param string $table название таблицы
	 * @return string
	 */
	public static function getConnectionName($table) {
		if (in_array($table, self::DB_WORK_TABLES)) {
			return App::DB_WORK;
		}

		return self::DB_MASTER;
	}

	/**
	 * Включен ли режим отладки
	 *
	 * @return bool
	 */
	public static function isDebugEnable(): bool {
		return self::config("app.mode") != self::APP_MODE_STAGE;
	}

	/**
	 * Режим работы: локальная разработка или тестовые поддомен
	 * @return bool
	 */
	public static function isLocalMode(): bool {
		return self::config("app.mode") == self::APP_MODE_LOCAL;
	}

	/**
	 * Режим работы: рабочий сайт, продакшен
	 * @return bool
	 */
	public static function isProductionMode(): bool {
		return self::config("app.mode") == self::APP_MODE_STAGE;
	}

	/**
	 * Получить значение из конфига
	 *
	 * @param $param
	 *
	 * @return mixed
	 */
	public static function config($param) {
		return Configurator::getInstance()->get($param);
	}

	public static function getHost() {
		if (!$_SERVER["HTTP_HOST"])
			return App::config("originurl");

		// если зеркало
		if (stripos(App::config("mirrorurl"), "//" . $_SERVER["HTTP_HOST"]) > 0)
			return App::config("mirrorurl");

		return App::config("originurl");
	}

	public static function getDomen() {
		return str_replace(["http://", "https://"], "", App::getHost());
	}

	public static function isMirror() {
		return App::getHost() == App::config("mirrorurl");
	}

	/**
	 * Не зеркало
	 *
	 * @return bool результат
	 */
	public static function isNotMirror(): bool {
		return !self::isMirror();
	}

	public static function genMirrorAuthData($userId, $redirectUrl = '', $customData = []) {
		$token = md5(Crypto::genPassword() . time() . rand(1, 1000000));
		$cart = [];
		if (App::config("redis.enable")) {
			$redis = RedisManager::getInstance();
			$cart = $redis->getActor('cart');
		}
		$data = [
			'user_id' => $userId,
			'redirect' => str_replace(App::config('mirrorurl'), App::config('originurl'), $redirectUrl) ?: '/',
			'cart' => $cart
		];
		$data = array_merge($data, $customData);
		if (App::config("redis.enable")) {
			$redis->set($token, $data, Helper::ONE_MINUTE);
		}
		return $token;
	}

	// отправить
	public static function send404() {
		global $smarty;
		header("HTTP/1.0 404 Not Found");
		$cacheTime = 60 * 60 * 24;
		$cacheFile = App::config("basedir") . "/temporary/" . str_replace(["..","/","\\"], "", App::getDomen()) . "_not_found.html";
		if (file_exists($cacheFile)  && time() - $cacheTime < filemtime($cacheFile) && filesize($cacheFile)) {
			include($cacheFile);
		} else {
			$pagetitle = Translations::getLang() == Translations::EN_LANG ? "Page not found" : "Страница не найдена";
			$smarty->assign("pagetitle", $pagetitle);
			$cacheСontent = $smarty->fetch("not_found.tpl");

			$cached = fopen($cacheFile, "w");
			fwrite($cached, $cacheСontent);
			fclose($cached);

			print $cacheСontent;
		}
		exit;
	}

	/**
	 * Отдать 403 код ошибки и прервать выполнение запроса
	 */
	public static function send403Forbidden(){
		header('HTTP/1.0 403 Forbidden');
		exit;
	}

	public static function isShowAuthCaptcha() {
		if (self::isLocalMode() || UserManager::getCurrentUserId()) {
			return false;
		}

		if (!App::allowUseGoogle()) {
			return false;
		}

		$hasRegisterIp = reCAPTCHA::hasRegisterIp();
		if ($hasRegisterIp) {
			return true;
		}

		return !empty(App::config(reCAPTCHA::CAPTCHA_ENABLE_CONFIG_OPTION)) || $hasRegisterIp ? true : false;
	}

	public static function isWantEnable($userId = false) {
		global $actor;

		if (!$userId) {
			$userId = $actor->id;
		}
		if ($userId % 2 != 0) {
			return true;
		}

		return false;
	}

	// возвращает guid
	public static function getGuid($length = 32) {
		$guid = md5(uniqid(rand(), 1) . App::config("crypto.xor_string_salt") . time());

		return substr($guid, 0, $length);
	}

	public static function getPhoneVerifyCode() {
		return mt_rand(1000, 9999);
	}

	/**
	 * Получить коннект к заданной базе
	 * @param string $database база данных
	 * @return DataBasePDO
	 */
	public static function pdo($database = self::DB_MASTER) {
		return DataBasePDO::getInstance($database);
	}

	/**
	 * Получить коннект к slave базе
	 * @return DataBasePDO
	 */
	public static function pdoSlave() {
		return DataBasePDO::getInstance(self::DB_SLAVE);
	}

	public static function end() {
		exit;
	}

	/**
	 * Относится ли идентификатор пользователя к сервисным
	 * @param int $userId
	 * @return bool
	 */
	public static function isServiceUser($userId) {
		return in_array($userId, [App::config("kwork.support_id"),
			App::config("kwork.moder_id"),
			App::config("kwork.user_id")
		]);
	}

	/**
	 * Можно ли использоватеть скрипты google?
	 * @return bool
	 */
	public static function allowUseGoogle() {
		$return = App::config('disallow_use_google');
		return $return ? false : true;
	}

	/**
	 * Функция определяет используется ли https протокол.
	 * @return  bool  true если https, в остальных случаях false
	 */
	public static function isSSL() {
		if (isset($_SERVER["HTTPS"])) {
			if ($_SERVER["HTTPS"] == 1) {
				return true;
			} elseif ($_SERVER["HTTPS"] == "on") {
				return true;
			}
		} elseif ($_SERVER["SERVER_PORT"] == 443) {
			return true;
		}
		return false;
	}

	/**
	 * Является ли текущий хост субдоменом Kwork Connect
	 *
	 * @return bool
	 */
	public static function isConnectSubdomain():bool {
		if (Translations::isDefaultLang()) {
			$connectSubdomain = App::config("connect_subdomain_ru");
		} else {
			$connectSubdomain = App::config("connect_subdomain_en");
		}

		return $_SERVER["HTTP_HOST"] && $connectSubdomain && $_SERVER["HTTP_HOST"] == $connectSubdomain;
	}

	/**
	 * Проверка и выброс 404 Not Found в случае, если страница отображается на
	 * проекте, к которому не принадлежит
	 *
	 * @param bool $projectKwork Включена ли страница на Topfreelancer (Kwork.Connect)
	 * @param bool $projectTopfreelancer Включена ли страница в проекте Kwork
	 */
	public static function validateProject(bool $projectKwork = true, bool $projectTopfreelancer = true) {
		global $smarty;

		$isConnectSubdomain = self::isConnectSubdomain();

		if ($isConnectSubdomain && !$projectTopfreelancer) {
			self::send404();
		}

		if (!$isConnectSubdomain && !$projectKwork) {
			self::send404();
		}

		// передаем в шаблон переменную, указывающую, что страница в общем доступе
		if ($projectKwork && $projectTopfreelancer) {
			$smarty->assign("isMultiProject", true);
		}
	}

}
