<?php

use Helpers\Log\Formatter\JsonFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log {
	/**
	 * Алиас для редиса для логирования действий пользователя
	 */
	public const REDIS_ALIAS_USER_ACTION = "LogUserAction";
	/**
	 * Алиас для редиса для логирования действий администратора
	 */
	public const REDIS_ALIAS_ADMIN_ACTION = "LogAdminAction";


	/**
	 * Префикс для имени лога с ошибоками
	 */
	public const ERROR_LOG = "error";

	/**
	 * Префикс для файла действий пользователя
	 */
	public const FILE_PREFIX_USER_ACTION = "user_action";

	/**
	 * Префикс для файла действий администратора
	 */
	public const FILE_PREFIX_ADMIN_ACTION = "admin_action";

	/**
	 * Флаги для активации хэндлелов Monolog
	 */
	public const FLAG_HANDLER_STREAM = 1 << 0; // 1
	public const FLAG_HANDLER_REDIS = 1 << 1; // 2

	/**
	 * Названия хэндлеров Monolog
	 */
	public const HANDLER_NAME_STREAM = 'stream';
	public const HANDLER_NAME_REDIS = 'redis';

	/**
	 * @var string
	 */
	static public $streamFormat = "%datetime%\n%message%\n\n";

	/**
	 * Сообщение об ошибке
	 * @var string
	 */
	static public $exceptionMessage = "";

	/**
	 * Массив созданных логгеров
	 * @var array
	 */
	private static $loggers = [];

	/**
	 * @var RedisHandler|null
	 */
	private static $redisHandler;


	/**
	 * Получение логгера со статическим кешированием
	 *
	 * @param string $filename
	 * @param int|string $flags
	 *
	 * @return Logger
	 * @throws \Exception
	 */
	private static function getLogger($filename, $flags = self::FLAG_HANDLER_REDIS): Logger {
		if (!isset(self::$loggers[$flags][$filename])) {
			if ($flags === self::HANDLER_NAME_STREAM) {
				$flags = self::FLAG_HANDLER_STREAM;
			}
			if ($flags === self::HANDLER_NAME_REDIS) {
				$flags = self::FLAG_HANDLER_REDIS;
			}
			$logger = new Logger($filename);

			if (self::FLAG_HANDLER_STREAM & $flags) {
				self::addStreamHandler($logger);
			}
			if (self::FLAG_HANDLER_REDIS & $flags) {
				self::addRedisHandler($logger);
			}
			self::$loggers[$flags][$filename] = $logger;
		}
		return self::$loggers[$flags][$filename];
	}

	/**
	 * Передает данные для логирования в текущий обработчик redis|stream
	 *
	 * @param string $key ключ записи
	 * @param mixed $data данные для записи
	 * @param int|string $flags побитовые флаги хендлеров записи логов или названия хендрелов
	 * @param string $logExt равширение для файлов лога
	 *
	 * @return bool
	 */
	public static function push(string $key, $data, $flags = self::FLAG_HANDLER_STREAM, string $logExt = "log"): bool {
		$filename = $key . "." . $logExt;
		$context = ["key" => $key, "ext" => $logExt, "date" => date("Y-m-d H:i:s")];
		try {
			$data = self::doSafeData($data);
			$logger = self::getLogger($filename, $flags);
			$logger->debug($data, $context);
		} catch (Throwable  $throwable) {
			self::$exceptionMessage = $throwable->getMessage() . "\n";
			try {
				//пишем в любом случае в файл
				$logger = self::getLogger($filename, self::FLAG_HANDLER_STREAM);
				$logger->debug($data, $context);
			} catch (Throwable $throwable) {
				self::$exceptionMessage .= $throwable->getMessage() . "\n";
				return false;
			}
		}
		//если нужно принудительно завершить
        if (App::config("monolog.compulsory_closing")) {
            $logger->close();
        }
		return true;
	}

	/**
	 * Записать данные в установленный канал
	 * @param mixed $data данные для записи
	 * @param string $fileName
	 * @param string $extension
	 */
	public static function write($data, $fileName = "log", $extension = "log"): void {
		self::push($fileName, $data, App::config("monolog.engine"), $extension);
	}

	/**
	 * Подневное логирование
	 * @param mixed $data данные для записи в хендлер
	 * @param string $fileName название файла|ключа
	 * @param int|string|null $flags побитовые флаги хендлеров записи логов или названия хендрелов
	 */
	public static function daily($data, $fileName = "log", $flags = null): void {
		if (null === $flags) {
			$flags = App::config("monolog.engine");
		}
		$fileName .= "_" . date("Y_m_d");
		self::push("daily/" . $fileName, $data, $flags);
	}

	/**
	 * Логирует данные по тикетам
	 * @param $ticketId
	 * @param $data
	 * @param bool $addStackCall
	 */
	public static function ticketLog($ticketId, $data, $addStackCall = false): void {
		if ($addStackCall) {
			$data = print_r($data, true) . "\r\n" . self::getStackCall();
		}
		self::push("ticket_" . intval($ticketId), $data, App::config("monolog.engine"));
	}

	/**
	 * Выводит стек вызова функций
	 * @return string
	 */
	public static function getStackCall(): string {
		$data = debug_backtrace();
		$return = array();
		foreach ($data as $row) {
			$return[] = $row["file"] . " (" . $row["line"] . ")";
		}
		return implode("\n", $return);
	}

	/**
	 * Залоггировать эксепшен в daily/error
	 *
	 * @param \Exception $exception
	 */
	public static function dailyErrorException(Exception $exception): void {
		self::daily($exception->getMessage() . "\n" . $exception->getTraceAsString(), "error");
	}

	/**
	 * Логирование почты
	 * @param string $email - адрес получателя
	 * @param string $subject - заголовок письма
	 * @param string $login - логин, если есть
	 * @param string $dateStart
	 * @throws Exception
	 */
	public static function logMail($email, $subject, $login = "", $dateStart = ""): void {
		$dateFile = date("Y-m-d");
		$logDir = FileManager::getDirAnyway(App::config("logDir") . "/mail");
		$data = $dateStart . " / " . date("Y-m-d H:i:s") . PHP_EOL . $email . " " . ($login ? "[" . $login . "]" : "") . PHP_EOL . $subject . PHP_EOL . PHP_EOL;
		Helper::filePutContents($logDir . DIRECTORY_SEPARATOR . $dateFile . "." . "log", print_r($data, true), FILE_APPEND);
	}

	public static function logWorkSqlSourse($sql, $rowCount): void {
		if (preg_match('!\s+work\s+!', $sql)) {
			global $argv;
			$sourse = Helper::isConsoleRequest() ? $argv[0] : $_SERVER['SCRIPT_NAME'];
			self::daily($sql . " (results: {$rowCount})\n" . $sourse, 'work_sql');
		}
	}

	/**
	 * Логирование в редис (временное хранилище)
	 * @param mixed $data
	 * @param string $redisAlias - название ключа в редисе
	 */
	public static function logIntoRedis($data, $redisAlias): void {
		if (App::config("redis.enable")) {
			RedisManager::getInstance()->rPush($redisAlias, $data);
		}
	}

	/**
	 * Получить данные из редиса в виде строки
	 * @param string $redisAlias - название ключа в редисе
	 * @return array
	 */
	public static function getFromRedis($redisAlias): array {
		$res = [];
		if (App::config("redis.enable")) {
			$res = RedisManager::getInstance()->getAllFromList($redisAlias);
		}
		return $res;
	}

	/**
	 * Вставка dailyLog из редиса
	 * @param string $redisAlias - алиас по которому получить записанный лог
	 * @param string $fileName - префикс файла
	 */
	public static function dailyFromRedis($redisAlias, $fileName): void {
		if (App::config("redis.enable")) {
			$actions = self::getFromRedis($redisAlias);
			$actionsHours = [];
			foreach ($actions as $action) {
				$actionParts = explode("\n", $action);
				$actionDate = strtotime($actionParts[0]);
				$actionHour = intval(date('H', $actionDate));
				$actionsHours[$actionHour] .= print_r($action, true) . "\n\n";
			}
			foreach ($actionsHours as $actionHour => $action) {
				if ($actionHour >= 0 && $actionHour <= 23) {
					$actionHour = ($actionHour < 10) ? 0 . $actionHour : $actionHour;
					$logKey = "daily/user_action/$fileName" . "_" . date("Y_m_d") . "_" . $actionHour;
					self::push($logKey, $action, App::config("monolog.engine"));
				}
			}

			$logRotateDir = App::config(\Enum\Config::ROTATE_LOGS_PATH);
			if (!empty($logRotateDir)) {
				$logPath = "{$logRotateDir}/{$fileName}.log";
				Helper::filePutContents($logPath, implode("\n\n", $actions), FILE_APPEND);
			}
		}
	}

	/**
	 * Обработчик исключений
	 * @param Exception $exception
	 */
	public static function routingError(Exception $exception): void {
		// ExceptionHandler::handler
		switch (true) {
			case $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException:
			case $exception instanceof \Core\Exception\PageNotFoundException:
			case $exception instanceof \Core\Exception\RedirectException:
			case $exception instanceof \Core\Exception\UnauthorizedException:
			case $exception instanceof \Symfony\Component\Routing\Exception\MethodNotAllowedException:
			case $exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException:
			case $exception instanceof \Core\Exception\JsonException:
			case $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException:
				break;
			default:
				self::daily("Routing error\n" .
					"URL: " . $_SERVER['REQUEST_URI'] . "\n" .
					$exception->getMessage() . "\n" .
					$exception->getFile() . "(" . $exception->getLine() . ")\n" .
					$exception->getTraceAsString(), "error");
		}
	}

	/**
	 * Экранирование данных
	 * @param string|array|object $data
	 * @return string
	 */
	public static function doSafeData($data): string {
		$data = print_r($data, true);
		$passwords = Configurator::getInstance()->seekValidPasswords();
		foreach ($passwords as $password) {
			$data = str_replace($password, "********", $data);
		}
		return $data;
	}


	/**
	 * Добавление обработчика stream
	 *
	 * @param Logger $logger Логгер куда добавлять
	 *
	 * @throws \Exception
	 */
	private static function addStreamHandler(Logger $logger): void {
		$handler = new StreamHandler(App::config("logDir") . "/".$logger->getName(), Logger::DEBUG, true, 0664);
		$handler->setFormatter(new LineFormatter(self::$streamFormat, "Y-m-d H:i:s", true));
		$logger->pushHandler($handler);
	}

	/**
	 * Добавление обработчика redis
	 *
	 * @param Logger $logger
	 *
	 * @throws \Exception
	 */
	private static function addRedisHandler(Logger $logger): void {
		$logger->pushHandler(self::getRedisHandler());
	}

	/**
	 * Получение redis обработчика
	 *
	 * @return RedisHandler|null
	 * @throws \Exception
	 */
	private static function getRedisHandler(): ?RedisHandler {
		if (App::config("redis.enable")) {
			if (!isset(self::$redisHandler)) {
				$key = App::config("redis.logs.key");

				self::$redisHandler = new RedisHandler(
					RedisManager::getInstance()->getKeyInstance($key),
					$key
				);

				self::$redisHandler->setFormatter(new JsonFormatter());
			}

			return self::$redisHandler;
		}

		throw new Exception("Redis is disabled");
	}

	/**
	 * @param Throwable|Exception $error Экземпляр исключения
	 * @return string Строка для логирования
	 */
	public static function getLogInfo($error): string {
		return 'Error message: "' . $error->getMessage() . '" in ' . $error->getFile() . ' (' . $error->getLine() . ')';
	}
}