<?php

namespace Session;


class MysqlSessionHandler implements \SessionHandlerInterface {

	/**
	 * Максимальное время жизни неактивной сессии
	 */
	const MAX_LIVETIME = \Helper::ONE_DAY;

	/**
	 * Максимальное время жизни неактивной, пустой сессии
	 */
	const MAX_EMPTY_LIVETIME = \Helper::ONE_HOUR;

	const TABLE_NAME = 'session_handler_table';

	/**
	 * @var \DataBasePDO
	 */
	protected $dbConnection;

	private $cacheData = [];

	public function __construct($dbHost, $dbUser, $dbPassword, $dbDatabase) {
		$pdo = new \DataBasePDO();
		$pdo->setConnectionParams($dbHost, $dbUser, $dbPassword, $dbDatabase);
		$pdo->reConnect();
		$this->dbConnection = $pdo;
	}

	/**
	 * Открыть новую сессию
	 * @param string $path
	 * @param string $name
	 * @return bool
	 */
	public function open($path, $name) {
		return true;
	}

	/**
	 * Прочитать значение сессии
	 * @param string $sessionId
	 * @return bool|mixed|string
	 */
	public function read($sessionId) {
		$sql = "SELECT `data`, `timestamp`
			FROM " . self::TABLE_NAME . ' 
			WHERE id = :id 
			LIMIT 1';

		$return = $this->dbConnection->fetch($sql, [
			'id' => $sessionId,
		]);
		$this->setCache($sessionId, $return);
		return $return['data'];
	}

	/**
	 * Сохранить кеш
	 * @param string $sessionId ID сессии
	 * @param array $sessionData Данные сессииы
	 */
	protected function setCache($sessionId, array $sessionData) {
		$this->cacheData[$sessionId] = $sessionData;
	}

	/**
	 * Обновилась ли сессия?
	 * @param string $sessionId
	 * @param string $sessionData
	 * @return bool true - обновилось, false - не изменилось
	 */
	protected function isSessionUpdated($sessionId, $sessionData) {
		if (empty($this->cacheData[$sessionId])) {
			return true;
		}
		$cacheData = $this->cacheData[$sessionId];
		if ($cacheData['data'] == $sessionData) {
			if (time() - $cacheData['timestamp'] > 4 * \Helper::ONE_HOUR) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Удалить сессию
	 * @param string $sessionId
	 * @return bool|mixed|string
	 */
	public function destroy($sessionId) {
		$sql = "DELETE FROM " . self::TABLE_NAME . " 
			WHERE id = :id
			LIMIT 1";
		return $this->dbConnection->fetchScalar($sql, [
			'id' => $sessionId,
		]);
	}

	/**
	 * Очистка старых сессий
	 * @param int $_maxTime
	 * @return bool
	 */
	public function gc($_maxTime) {
		$sqls = [
			"SELECT id FROM " . self::TABLE_NAME . " 
			WHERE `timestamp`<" . (time() - self::MAX_LIVETIME),

			"SELECT id FROM " . self::TABLE_NAME . " 
			WHERE 
				`data` = '' AND
				`timestamp`<" . (time() - self::MAX_EMPTY_LIVETIME)
		];
		foreach ($sqls as $sql) {
			$ids = $this->dbConnection->fetchAllByColumn($sql);
			if (!empty($ids)) {
				$this->removeSessions($ids);
			}
		}
		return true;
	}

	/**
	 * Удаление списка сессий
	 * @param array $sessionNames
	 */
	protected function removeSessions(array $sessionNames) {
		$parts = array_chunk($sessionNames, 1000);
		foreach ($parts as $part) {
			$params = [];
			$strParams = $this->dbConnection->arrayToStrParams($part, $params);
			$sql = "DELETE FROM " . self::TABLE_NAME . "
				WHERE id IN ({$strParams})";
			$this->dbConnection->execute($sql, $params);
		}
	}

	/**
	 * Закрытие сессии
	 * @return bool|void
	 */
	public function close() {
		unset($this->dbConnection);
	}

	/**
	 * Сохранить данные сессии
	 * @param string $sessionId
	 * @param string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData) {
		if (!$this->isSessionUpdated($sessionId, $sessionData)) {
			return true;
		}
		$insert = [
			'id' => $sessionId,
			'data' => $sessionData,
			'timestamp' => time(),
		];
		$return = $this->dbConnection->insert(self::TABLE_NAME, $insert, [
			\DataBasePDO::INSERT_OPTION_DUPLICATE_UPDATE => [
				'data',
				'timestamp'
			]
		]);
		return $return ? true : false;
	}

	/**
	 * @return MysqlSessionHandler
	 */
	public static function getInstance() {
		return new MysqlSessionHandler(
			\App::config("session.mysql.host"),
			\App::config("session.mysql.user"),
			\App::config("session.mysql.password"),
			\App::config("session.mysql.db_name"));
	}

}