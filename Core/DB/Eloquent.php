<?php

namespace Core\DB;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use \App;
use \PDO;

class Eloquent {

	protected static $capsule;

	/**
	 * Загрузчик Eloquent
	 * @return DB
	 * @throws
	 */
	public static function boot() {
		// Query Builder
		$capsule = new DB;

		// основное подключение
		$capsule->addConnection(self::getConnectionParams(App::DB_MASTER), App::DB_MASTER);

		// подключение к Work
		$capsule->addConnection(self::getConnectionParams(App::DB_WORK), App::DB_WORK);

		$capsule->getDatabaseManager()->setDefaultConnection(App::DB_MASTER);

		$capsule->setEventDispatcher(new Dispatcher(new Container));
		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		self::$capsule = $capsule;

		self::enableQueryLog(App::DB_MASTER);
		self::enableQueryLog(App::DB_WORK);

		return $capsule;
	}

	/**
	 * Получить параметры подключения по имени
	 *
	 * @param string $connectionName
	 * @return array
	 */
	private static function getConnectionParams($connectionName) {
		return [
			"driver" => "mysql",
			"host" => empty(App::config("db.$connectionName.socket")) ? App::config("db.$connectionName.host") : null,
			"unix_socket" => !empty(App::config("db.$connectionName.socket")) ? App::config("db.$connectionName.socket") : null,
			"database" => App::config("db.$connectionName.name"),
			"username" => App::config("db.$connectionName.user"),
			"password" => App::config("db.$connectionName.password"),
			"charset" => "utf8",
			"collation" => "utf8_unicode_ci",
			"prefix" => "",
			"options" => self::getConnectionOptions($connectionName),
		];
	}

	/**
	 * Параметры подключения по-умолчанию
	 * @return array
	 */
	private static function defaultConnectionOptions() {
		return [
			PDO::ATTR_EMULATE_PREPARES => true,
			PDO::ATTR_TIMEOUT => 5,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_CASE => PDO::CASE_NATURAL,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false,
		];
	}

	/**
	 * Получить параметры подключения
	 * @param string $connectionName Имя подключения App::DB_*
	 * @return array
	 */
	private static function getConnectionOptions($connectionName) {
		$return = self::defaultConnectionOptions();
		switch ($connectionName) {
			case App::DB_WORK:
				$return[PDO::ATTR_ERRMODE] = PDO::ERRMODE_WARNING;
				break;
		}
		return $return;
	}

	/**
	 * Включить логирование запросов на подключении
	 *
	 * @param string $connection название подключения
	 */
	private static function enableQueryLog($connection = "default") {
		if (App::config("sqllog.enable") && App::config("app.mode") == "local") {
			$dispatcher = new Dispatcher();

			$connection = self::$capsule->getConnection($connection);
			$connection->enableQueryLog();
			$connection->setEventDispatcher($dispatcher);
			$connection->listen(function(QueryExecuted $query) {
				global $logSqlTime, $logSqlQuerys;

				$stack = debug_backtrace();
				while ($item = array_shift($stack)) {
					if (mb_stripos($item["file"], "illuminate/database/Query/Builder") !== false) {
						break;
					}
				}
				array_unshift($stack, $item);
				$stack = array_map(function($item) {
					unset($item["args"], $item["object"], $item["type"]);
					return $item;
				}, $stack);

				$logSqlQuerys[] = [
					$query->sql,
					$query->bindings,
					$query->time,
					false,
					$stack,
				];
				$logSqlTime += $query->time;
			});

		}
	}

	/**
	 * Получить capsule с текущим подключением к БД
	 * @return DB
	 */
	public static function getCapsule() {
		return self::$capsule;
	}

}
