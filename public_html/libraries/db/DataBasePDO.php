<?php

class DataBasePDO {

	private static $_instance;
	private $_rowCount;
	protected $_config;

	/**
	 * @var PDO
	 */
	protected $_pdoObj;
	protected $_lastError;
	private $conneсtionParams;

	private $database;

	/**
	 * Количество обращений к PDO
	 * @var int
	 */
	public $countUsing = 0;
	// $enableSqlLogInPage

	public $logQueryTime = false;
	public $querysTimme = array();

	const codeServerError = 1000;
	const codePDOException = 1001;
	const codeRowNotFound = 1004;

	private $_tryReconnect = true;
	public $isSlave = false;

	/**
	 * @var bool Использовать PDO не из \Core\DB\DB
	 * Для sphinx это нужно т.к. он не поддерживает стандартные инициализационные команды
	 */
	private $usePDONotFromDB = false;

	/**
	 * Выбрасывать исключение вместо exit при неудачной попытке подключиться к базе
	 * @var bool
	 */
	private $throwExceptionNotExit = false;

	/**
	 * Параметры запроса
	 * @var array
	 */
	private $_params = [];

	/**
	 * Пауза перед перезапуска запроса при дедлоке, микросекунды
	 */
	const RETRY_PAUSE = 100000;

	/**
	 * Количество попыток для перезапуска запросов при дедлоке
	 */
	const MAX_TRY_COUNT = 5;

	/**
	 * Количество попыток переподключиться
	 */
	const MAX_CONNECTION_TRY_COUNT = 3;

	/**
	 * Код ошибки дедлока
	 */
	const DEADLOCK_ERROR = 40001;

	/**
	 * Потеря соединения ошибка
	 */
	const LOST_CONNECTION_ERROR = "HY000";

	/**
	 * Уровни изоляции транзакций
	 */
	const ISOLATION_LEVELS = [
		"READ COMMITTED",
		"READ UNCOMMITTED",
		"SERIALIZABLE",
		"REPEATABLE READ"
	];
	const SET_DELIMITER = ", ";
	const INSERT_OPTION_IGNORE = 'ignore';
	const INSERT_OPTION_DUPLICATE_UPDATE = 'on_duplicate_update';

	/**
	 * @var string Уровень изоляции который определен по умолчанию
	 */
	private $_defaultIsolationLevel;

	/**
	 * Начата ли сейчас транзакция
	 *
	 * @var bool
	 */
	private $isTransactionStarted = false;

	/**
	 * Получить экземпляр класса c коннектом к заданной базе
	 * @param string $database база данных
	 * @return DataBasePDO Объект DataBasePDO
	 */
	public static function getInstance($database = App::DB_MASTER) {
		if (self::$_instance[$database] == null) {
			self::$_instance[$database] = new DataBasePDO(["database" => $database]);
		}
		return self::$_instance[$database];
	}

	private function setIsTransactionStarted(bool $isTransactionStarted): self {
		$this->isTransactionStarted = $isTransactionStarted;
		return $this;
	}

	private function isTransactionStarted(): bool {
		return $this->isTransactionStarted;
	}

	/**
	 * DataBasePDO constructor.
	 * @param array $options
	 * @throws Exception
	 */
	public function __construct(array $options = array()) {
		if ($options["database"] == App::DB_SLAVE) {
			$this->isSlave = true;
			$this->usePDONotFromDB = true;
		} elseif ($options["database"] == App::DB_SPHINX) {
			// Использовать PDO не из \Core\DB\DB
			// Для sphinx это нужно т.к. он не поддерживает стандартные инициализационные команды
			$this->usePDONotFromDB = true;
			$this->throwExceptionNotExit = true;
		}

		$this->database = $options["database"];

		if (empty($options['custom'])) {
			$this->connect();
		}
	}

	public function lastInsertId() {
		return $this->_pdoObj->lastInsertId();
	}

	private static function serverError($msg, $sql, $params) {
		Log::daily('SQL: ' . $sql . " (params: " . http_build_query($params) . ")\nError: " . implode(":", $msg) . "\nFrom call: " . Log::getStackCall(), 'error');
		$setMsg = implode(":", $msg);
		self::processLastError($setMsg);
		return array(self::codeServerError, $setMsg);
	}

	private static function pdoException($msg) {
		$msg = is_array($msg) ? implode(":", $msg) : $msg;
		Log::daily("pdoException: $msg\nFrom call: " . Log::getStackCall(), 'error');
		self::processLastError($msg);
		return array(self::codePDOException, $msg);
	}

	public function affectedRows() {
		return $this->_rowCount;
	}

	/**
	 * @throws Exception
	 */
	protected function connect() {
		try {
			$this->reConnect();
		} catch (PDOException $e) {
			Log::daily($e->getMessage() . "\nFrom call: " . Log::getStackCall(), 'error');
			Log::write(print_r($e, true) . "\n" . print_R(debug_backtrace(), true), 'PDOException');
			Log::write("config file is readable=" . is_readable(Configurator::BASE_DIR . "/" . Configurator::FILE_TYPE_PUBLIC . "/config.cnf"), 'PDOException');
			Log::write("Config content: " . file_get_contents(Configurator::BASE_DIR . "/" . Configurator::FILE_TYPE_PUBLIC . "/config.cnf"), 'PDOException');
			if ($this->throwExceptionNotExit) {
				throw new Exception("Failed to connect to database");
			} else {
				exit;
			}
		}
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function checkReconnect() {
		$pingStatement = null;
		try {
			$pingStatement = $this->_pdoObj->query("SELECT 1");
			$fetchedData = $pingStatement->fetchAll();
		} catch (Exception $exception) {
			$errorCode = is_null($pingStatement) ? null : $pingStatement->errorCode();
			if ($errorCode == self::LOST_CONNECTION_ERROR || is_null($errorCode)) {
				if (!$this->restoreConnection()) {
					throw $exception;
				}
			} else {
				throw $exception;
			}
		}
		return true;
	}

	/**
	 * Задать параметры подключения
	 * @param string $dbHost Хост
	 * @param string $dbUser Имя пользователя
	 * @param string $dbPassword Пароль
	 * @param string $dbName Имя базы
	 * @param string $dbSocket Unix сокет для подключения
	 */
	public function setConnectionParams($dbHost = '', $dbUser = '', $dbPassword = '', $dbName = '', $dbSocket = '') {
		$this->conneсtionParams = [
			'dbHost' => $dbHost,
			'dbUser' => $dbUser,
			'dbPassword' => $dbPassword,
			'dbName' => $dbName,
			'dbSocket' => $dbSocket,
		];
	}

	/**
	 * Получить параметры подключения
	 * @return array Массив с параметрами подключения setConnectionParams
	 */
	private function getConnectionParams() {
		return $this->conneсtionParams;
	}

	private function getDatabase() {
		return $this->database;
	}

	public function reConnect() {
		$database = $this->getDatabase();

		if (!in_array($database, App::VALID_DATABASES)) {
			throw new PDOException("Invalid database: ".$database);
		}

		if (empty($this->conneсtionParams)) {
			$this->setConnectionParams(
				App::config("db.".$database.".host"),
				App::config("db.".$database.".user"),
				App::config("db.".$database.".password"),
				App::config("db.".$database.".name"),
				App::config("db.".$database.".socket")
			);
		}

		if ($this->usePDONotFromDB) {
			$params = $this->getConnectionParams();
			$options = [
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
				PDO::ATTR_TIMEOUT => 5,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];
			$this->_pdoObj = new PDO("mysql:host={$params['dbHost']};dbname={$params['dbName']};charset=utf8", $params['dbUser'], $params['dbPassword'], $options);
			return true;
		}

		$this->_pdoObj = \Core\DB\DB::getPdo($database);
		return true;
	}

	public function lastError() {
		return $this->_lastError[1];
	}

	public function lastFullError() {
		return $this->_lastError;
	}

	public function quote($string) {
		return $this->_pdoObj->quote($string);
	}

	/**
	 * Обработать последную ошибку. Если было принулительно прерывание - завершить работу
	 * @param string $errorMsg Текст ошибки, из lastError()
	 */
	public static function processLastError($errorMsg) {
		list(, $code,) = explode(':', $errorMsg);
	}

	/**
	 * Создает значение типа PDO::PARAM_NULL если is_null
	 *
	 * @param mixed $value Подаваемое значение
	 *
	 * @return mixed
	 */
	public function pdoNullValue($value) {
		if (is_null($value)) {
			return ["value" => $value, "PDOType" => PDO::PARAM_NULL];
		}
		return $value;
	}

	protected function bindValues($sql, array $params = array(), $fetch_mode = false) {
		if ($this->_tryReconnect) {
			$this->checkReconnect();
		}
		$this->countUsing++;
		$stmt = $this->_pdoObj->prepare($sql);

		if (!empty($fetch_mode)) {
			$stmt->setFetchMode($fetch_mode);
		}
		$this->clearParams();
		foreach ($params as $key => $value) {
			if (is_array($value) && isset($value['PDOType'])) {
				$param = $value['PDOType'];
				switch ($param) {
					case PDO::PARAM_STR:
						$setValue = strval($value['value']);
						break;

					case PDO::PARAM_INT:
						$setValue = intval($value['value']);
						break;

					case PDO::PARAM_NULL:
						$setValue = null;
						break;

					default:
						$setValue = $value['value'];
						break;
				}
			} else {
				$param = PDO::PARAM_STR;
				if (is_numeric($value) && intval($value) == $value) {
					$param = PDO::PARAM_INT;
					$setValue = intval($value);
				} else {
					$setValue = strval($value);
				}
			}
			$this->setParam($key, $setValue);
			$stmt->bindValue($key, $setValue, $param);
		}
		return $stmt;
	}

	/**
	 * Рестарт запроса
	 *
	 * @param \PDOStatement $stmt
	 * @param string $logFile
	 *
	 * @return bool
	 */
	private function retryExecute(PDOStatement &$stmt, $logFile) {
		if ($this->isTransactionStarted()) {
			// Не рестартим запрос если он в транзакции
			return false;
		}

		$res = false;
		for ($i = 1; $i <= self::MAX_TRY_COUNT; $i++) {
			usleep(self::RETRY_PAUSE);
			try {
				$res = $stmt->execute();
			} catch (Exception $exception) {
				Log::write("Cannot resolve in $i try because error:". $exception->getMessage() . "\n", $logFile);
			}
			if ($res == true) {
				Log::write("Resolved at " . $i . " try", $logFile);
				break;
			} elseif ($i == self::MAX_TRY_COUNT) {
				Log::write("Cannot resolve in " . self::MAX_TRY_COUNT . " tries", $logFile);
			}
		}
		return $res;
	}

	private function restoreConnection(): bool {
		for ($attempt = 1; $attempt <= self::MAX_CONNECTION_TRY_COUNT; $attempt++) {
			try {
				\Core\DB\DB::reconnect();
				$this->connect();
				return true;
			} catch (Exception $exception) {
				usleep(self::RETRY_PAUSE);
			}
		}
		return false;
	}

	/**
	 * Исполнить PDOStatement
	 * @param PDOStatement $stmt Запрос
	 * @return mixed PDOStatement::execute
	 */
	private function executePDOStatment(PDOStatement &$stmt) {
		global $enableSqlLogInPage;
		$isSqllogEnable = App::config("sqllog.enable");
		if ($this->logQueryTime || $isSqllogEnable) {
			$startTime = microtime(true);
		}

		try {
			$res = $stmt->execute();
		} catch (Exception $exception) {
			if ($stmt->errorCode() == self::DEADLOCK_ERROR) {
				$this->logInnodbStatus();
				Log::write($_SERVER['REQUEST_URI'] . "\n" . $stmt->queryString, "deadlock_error");
				$res = $this->retryExecute($stmt, "deadlock_error");
			}
		}
		//Log::logWorkSqlSourse($stmt->queryString, $stmt->rowCount());
		if ($this->logQueryTime || $isSqllogEnable) {
			$workTime = round(microtime(true) - $startTime, 4);
		}
		if ($this->logQueryTime) {
			$this->querysTimme[] = array('sql' => $stmt->queryString, 'work_time' => $workTime);
		}
		if ($isSqllogEnable && $enableSqlLogInPage) {
			Log::write($_SERVER['REQUEST_URI'] . "\n[" . round($workTime * 1000) . "] " . $stmt->queryString, "sql");
		}

		return $res;
	}

	/**
	 * Копим запросы к базе и по выходу из скрипта пишем одним запросом
	 *
	 * @param string $query запрос к базе
	 * @param array $params параметры запроса
	 * @param int $dbTime время выполнения запроса
	 * @param bool $err флаг ошибки
	 */
	public function sqlLog($query, $params, $dbTime, $err) {
		global $logSessionTime, $logSqlTime, $logSqlQuerys;
		if (empty($logSqlQuerys)) {
			$logSessionTime = microtime(true);
			$logSqlQuerys = [];
			$logSqlTime = 0;

			// периодически очищаем таблицу
			if (mt_rand(0, 1000) === 0) {
					Helper::safeTruncate("sql_redis_log");
				}

			register_shutdown_function(function() {
				global $logSessionTime, $logSqlTime, $logRedisTime, $logRedisQuerys, $logSqlQuerys;
				$sql = "INSERT INTO sql_redis_log
							(request, memory, script_time, sql_time, redis_time, sql_count, redis_count, sql_querys, redis_querys)
						VALUES
							(:request, :memory, :script_time, :sql_time, :redis_time, :sql_count, :redis_count, :sql_querys, :redis_querys)";

				$this->bindValues($sql, [
						'request' => mres($_SERVER['REQUEST_URI']),
						'memory' => memory_get_usage(true),
						'script_time' => ceil(1000 * (microtime(true) - $logSessionTime)),
						'sql_time' => $logSqlTime, 'redis_time' => $logRedisTime,
						'sql_count' => count($logSqlQuerys),
						'redis_count' => count($logRedisQuerys),
						'sql_querys' => \Encoder\Crypt::encodeObj($logSqlQuerys),
						'redis_querys' => \Encoder\Crypt::encodeObj($logRedisQuerys)
					]
				)->execute();
			});
		}

		$stack = debug_backtrace();
		while ($item = array_shift($stack)) {
			if ($item["function"] === "sqlLog") {
				break;
			}
		}
		$stack = array_map(function($item) {
			unset($item["args"], $item["object"], $item["type"]);
			return $item;
		}, $stack);

		$query = str_replace(["\r", "\n", "\t", "  "], " ", $query);
		$logSqlQuerys[] = [
			$query,
			$params,
			$dbTime,
			$err,
			$stack,
		];
		$logSqlTime += $dbTime;
	}

	/**
	 * @throws Exception
	 */
	public function execute($sql, $params = array()) {
		if ($this->isSlave) {
			Log::daily("Use execute in SLAVE\n" . Log::getStackCall(), 'error');
			exit('DB Slave is not supported yet.');
		}
		try {
			$stmt = $this->bindValues($sql, $params);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				return $this->affectedRows();
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($sql . "\n" . $e->getMessage());
			return false;
		}
	}

	/**
	 * @throws Exception
	 * @return array|false
	 */
	public function fetchAll($sql, array $params = array(), $fetch_mode = PDO::FETCH_ASSOC) {
		try {
			$stmt = $this->bindValues($sql, $params, $fetch_mode);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$data = $stmt->fetchAll();
				if ($data === false) {
					return array();
				} else {
					return $data;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * @throws Exception
	 */
	public function fetch($sql, $params = array(), $fetch_mode = PDO::FETCH_ASSOC) {
		try {
			$stmt = $this->bindValues($sql, $params, $fetch_mode);

			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$row = $stmt->fetch();
				if ($row === false) {
					return array();
				} else {
					return $row;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * @throws Exception
	 */
	public function fetchScalar($sql, $params = array()) {
		try {
			$stmt = $this->bindValues($sql, $params);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$row = $stmt->fetchColumn();
				if ($row === false) {
					return '';
				} else {
					return $row;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Извлечь результаты N-го столбца sql запроса в массив.
	 * @param string $sql - sql запрос
	 * @param int $column - номер стоблца
	 * @param array $params - параметры
	 * @return boolean|array
	 * @throws Exception
	 */
	public function fetchAllByColumn($sql, $column = 0, $params = array()) {
		try {
			$stmt = $this->bindValues($sql, $params);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$data = $stmt->fetchAll(PDO::FETCH_COLUMN, $column);
				if ($data === false) {
					return array();
				} else {
					return $data;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Извлечь все результаты sql запросы в ассициативный массив.
	 * @param string $sql sql-запрос
	 * @param int|string $column Какой стоблец использовать в качестве ключей возвращаемого массива. <br/>
	 * int - порядковый норме столбца<br/>
	 * string - имя ассоциативного столбца
	 * @param array $params Массив параметров в sql запросе
	 * @param boolean $asObjects Отдать элементы массива как анонимные объекты
	 * @return array|false Возвращает ассоциативный массив, где в качестве индекса
	 * значение поля $column, а в качестве значения столбцы sql-выборки. False - в случае ошибки
	 * @throws Exception
	 */
	public function fetchAllNameByColumn($sql, $column = 0, $params = array(), $asObjects = false) {
		try {
			$stmt = $this->bindValues($sql, $params, PDO::FETCH_ASSOC);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$data = $stmt->fetchAll();
				if ($data === false) {
					return array();
				} else {
					$retArray = array();
					foreach ($data as $i => $array) {
						$indx = -1;
						$temp = array();
						$key = -1;
						foreach ($array as $j => $value) {
							$indx++;
							if ($indx == $column || (!is_numeric($column) && $j == $column)) {
								$key = $value; // если найдена позиция запонимаем ключ
							}
							$temp[$j] = $value;
						}
						if ($asObjects) {
							$retArray[$key] = (object)$temp;
						} else {
							$retArray[$key] = $temp;
						}
						if ($key == -1) {
							$this->_lastError = "Указан неправильный номер столбца";
							return false;
						}
						unset($data[$i]);
					}
					return $retArray;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	public function foundRows() {
		$this->_tryReconnect = false;
		$return = $this->fetchScalar("SELECT FOUND_ROWS()");
		$this->_tryReconnect = true;
		return $return;
	}

	/**
	 * Функция проверки наличия записи в базе данных.
	 * @param string $tableName
	 * @param string $condition
	 * @param array $params
	 * @return boolean
	 * @throws Exception
	 */
	public function exist($tableName, $condition, $params = array()) {
		$sql = "SELECT 1 FROM $tableName WHERE $condition LIMIT 1";
		$res = $this->fetchScalar($sql, $params);
		if ($res === false) {
			return $this->lastError();
		}
		return ($res == 1);
	}

	/**
	 * Проверка наличия записи в базе данных по полям
	 *
	 * @param string $tableName Название таблицы
	 * @param array $fields Массив полей и значений [id => 1]
	 * @return boolean
	 * @throws Exception
	 */
	public function existByFields(string $tableName, array $fields) {
		$this->makeSet($fields, $sql_set, $sql_params);
		if (empty($sql_set)) {
			$this->_lastError = 'Fields empty';
			return false;
		}

		$conditions = explode(self::SET_DELIMITER, $sql_set);
		$condition = implode(" AND ", $conditions);

		return $this->exist($tableName, $condition, $sql_params);
	}

	/**
	 * @throws Exception
	 */
	public function fetchAllSpecial($sql, $params = array(), $fetch_mode = PDO::FETCH_ASSOC, $o = array()) {
		try {
			$stmt = $this->bindValues($sql, $params, $fetch_mode);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$data = call_user_func_array(array($stmt, 'fetchAll'), $o);
				if ($data === false) {
					return array();
				} else {
					return $data;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Подготовить строку с плейсхолдерами и массив с параметрами по полям
	 *
	 * @param array $fields Массив полей и значений [id => 1]
	 * @param string $sql_set Выходной параметр (ссылка) строка с плейсхолдерами
	 * @param array $sql_params Выходной параметр (ссылка) массив параметров
	 */
	protected static function makeSet($fields, &$sql_set, &$sql_params) {
		$sql_fields = array();
		$sql_params = array();
		$i = 0;
		foreach ($fields as $n => $v) {
			if (!Helper::isFieldDb($n)) {
				continue;
			}
			$sql_fields[] = "`$n`" . '=:v' . $i;
			$sql_params['v' . $i] = $v;
			$i++;
		}
		$sql_set = implode(self::SET_DELIMITER, $sql_fields);
	}

	/**
	 *
	 * @param string $table Имя таблицы
	 * @param array $fields Масссив данных. array( <field> => <value> )
	 * @param array $o Параметры записи.
	 * ignore - игнорировать дубли (запись добавлена не будет, но и ошибки не будет)
	 * on_duplicate_update - при дублировании по ключу, обновить поля на вставляемые
	 * @return mixed Возвращает id вставленной строки (только при auto_increment),
	 * true в случае успеха (PK не auto_increment)
	 * false в случае ошибки
	 * @throws Exception
	 */
	public function insert($table, $fields, $o = array()) {
		if ($this->isSlave) {
			Log::daily("Use insert in SLAVE\n" . Log::getStackCall(), 'error');
			exit('DB Slave is not supported yet.');
		}
		try {
			$this->countUsing++;
			self::makeSet($fields, $sql_set, $sql_params);
			if (empty($sql_set)) {
				$this->_lastError = 'Fields empty';
				return false;
			}
			$ignore = '';
			if (isset($o[self::INSERT_OPTION_IGNORE]) and $o[self::INSERT_OPTION_IGNORE]) {
				$ignore = ' ignore';
			}
			$onDuplicateUpdate = '';
			if (isset($o[self::INSERT_OPTION_DUPLICATE_UPDATE]) && $o[self::INSERT_OPTION_DUPLICATE_UPDATE]) {
				$onDuplicateUpdate = ' ON DUPLICATE KEY UPDATE ' . $sql_set;
			}
			$sql = 'insert' . $ignore . ' into ' . $table . ' set ' . $sql_set . $onDuplicateUpdate;
			if ($this->execute($sql, $sql_params) !== false) {
				$id = $this->lastInsertId();
				if (empty($id)) {
					return true;
				}
				return $id;
			}
			return false;
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Замена записи в таблице
	 *
	 * @param string $table Имя таблицы
	 * @param array $fields Масссив данных. array( <field> => <value> )
	 *
	 * @return int Возвращает id вставленной строки (только при auto_increment),
	 * true в случае успеха (PK не auto_increment)
	 * false в случае ошибки
	 * @throws Exception
	 */
	public function replace(string $table, array $fields) {
		if ($this->isSlave) {
			Log::daily("Use replace in SLAVE\n" . Log::getStackCall(), 'error');
			return false;
		}
		try {
			if (empty($fields)) {
				$this->_lastError = 'Fields empty';
				return false;
			}

			$sqlParams = [];
			$i = 0;
			foreach ($fields as $fieldName => $fieldValue) {
				if (!Helper::isFieldDb($fieldName)) {
					$this->_lastError = "$fieldName is not field name";
					return false;
				}
				$sqlParams[":f".$i++] = $fieldValue;
			}
			$fieldNamesString = implode(",", array_keys($fields));
			$placeHoldersString = implode(",", array_keys($sqlParams));

			$sql = "REPLACE INTO {$table} ({$fieldNamesString}) VALUES ({$placeHoldersString})";
			if ($this->execute($sql, $sqlParams) !== false) {
				$id = $this->lastInsertId();
				if (empty($id)) {
					return true;
				}
				return $id;
			}
			return false;
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}


	/**
	 * Обновление
	 * @param string $table Имя таблицы
	 * @param array $fields Масссив данных. array( <field> => <value> )
	 * @param string $condition where часть sql.
	 * @param array $params Параметры $contidion
	 * @return mixed Возвращает количество изменных строк. false в случае ошибки.
	 * @throws Exception
	 */
	public function update($table, $fields, $condition, $params = array()) {
		if ($this->isSlave) {
			Log::daily("Use update in SLAVE\n" . Log::getStackCall(), 'error');
			exit('DB Slave is not supported yet.');
		}
		try {
			$sql_params = $params;
			self::makeSet($fields, $sql_set, $sql_params);
			if (empty($sql_set)) {
				$this->_lastError = 'Fields empty';
				return false;
			}
			$sql = 'update ' . $table . '  set ' . $sql_set . ' where ' . $condition;
			return $this->execute($sql, array_merge($sql_params, $params));
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Получение объекта PDOStatement по запросу для фетча по одной строке
	 * @param string $sql SQL запрос
	 * @param array $params Массив параметров
	 * @param int $fetch_mode Режим PDO::FETCH
	 * @return PDOStatement|false
	 * @throws Exception
	 */
	public function getStmt($sql, $params = array(), $fetch_mode = PDO::FETCH_ASSOC) {
		try {
			$stmt = $this->bindValues($sql, $params, $fetch_mode);
			$res = $this->executePDOStatment($stmt);
			if ($res == true) {
				return $stmt;
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Использовать нельзя! Получить следующие auto_increment в указанной таблице
	 * @param string $tableName Имя таблицы
	 * @return int Следующее значение auto_increment
	 * @throws Exception
	 */
	public function getNextAutoIncrement($tableName) {
		# forbidden to use (by galera)
		throw new Exception('PDO method forbidden');
	}

	/**
	 * Извлечь все результаты sql запроса в ассициативный массив ( $keyColumn => $valueColumn )
	 * @param string $sql sql-запрос
	 * @param int|string $keyColumn Какой стоблец использовать в качестве ключей возвращаемого массива. Можно использовать как порядковый номер столбца, так и имя столбца
	 * @param int|string $valueColumn Какой стоблец использовать в качестве значений возвращаемого массива. Можно использовать как порядковый номер столбца, так и имя столбца
	 * @param array $params Параметры sql-запроса
	 * @return array|false Возвращает array ( $keyColumn => $valueColumn )
	 * @throws Exception
	 */
	public function fetchAllAssocPair($sql, $keyColumn = 0, $valueColumn = 1, array $params = array()) {
		try {
			$stmt = $this->bindValues($sql, $params, PDO::FETCH_BOTH);
			$res = $this->executePDOStatment($stmt);
			$this->_rowCount = $stmt->rowCount();
			if ($res == true) {
				$data = $stmt->fetchAll();
				if ($data === false) {
					return array();
				} else {
					$retArray = array();
					foreach ($data as $i => $row) {
						if (!array_key_exists($keyColumn, $row)) {
							$this->_lastError = "Указан неправильный индексный столбц";
							return false;
						}
						if (!array_key_exists($valueColumn, $row)) {
							$this->_lastError = "Указан неправильный столбц значений";
							return false;
						}
						$retArray[$row[$keyColumn]] = $row[$valueColumn];
					}
					return $retArray;
				}
			} else {
				$this->_lastError = self::serverError($stmt->errorInfo(), $sql, $params);
				return false;
			}
		} catch (Exception $ex) {
			if ($this->isTransactionStarted()) {
				throw $ex;
			}
			$this->_lastError = self::pdoException($ex->getMessage());
			return false;
		}
	}

	/**
	 * Множественный insert одной командой
	 * @param string $table Имя таблицы
	 * @param array $values Масссив данных. array ( array( <field> => <value> ) )
	 * @param array $o Параметры записи.
	 * ignore - игнорировать дубли (запись добавлена не будет, но и ошибки не будет)
	 * duplicate - заменять дубли (только указанные поля) duplicate => array()
	 * replace - вместо insert использовать replace
	 * @return mixed Возвращает pdo()->execute.
	 * false в случае ошибки
	 * @throws Exception
	 */
	public function insertRows($table, array $values, $o = array()) {
		if ($this->isSlave) {
			Log::daily("Use insertRows in SLAVE\n" . Log::getStackCall(), 'error');
			exit('DB Slave is not supported yet.');
		}
		try {
			$fields = array();
			$insertParams = array();
			$setValues = array();
			foreach ($values as $rowNum => $setRow) {
				if (empty($fields)) {
					$fields = array_keys($setRow);
				}
				$_value = array();
				foreach ($setRow as $field => $value) {
					$_k = $rowNum . '_' . $field;
					$_value[] = ":f{$_k}";
					$insertParams[":f{$_k}"] = $value;
				}
				$setValues[] = '(' . implode(',', $_value) . ')';
			}

			if (empty($fields)) {
				$this->_lastError = 'Fields empty';
				return false;
			}
			$ignore = '';
			if (isset($o['ignore']) and $o['ignore']) {
				$ignore = ' ignore';
			}

			$duplicate = '';
			if (isset($o['duplicate']) and is_array($o['duplicate'])) {
				$updateFields = $o['duplicate'];
				if (empty($updateFields)) {
					$this->_lastError = 'Updated Fields empty';
					return false;
				}

				$updated = [];
				foreach ($updateFields as $field) {
					if (!in_array($field, $fields)) {
						$this->_lastError = 'Поле не найдено';
						return false;
					}

					$updated[] = $field . " = VALUES(" . $field . ")";
				}
				$duplicate = ' on duplicate key update ' . implode(", ", $updated);
			}

			$statement = "insert";
			if (isset($o["replace"])) {
				$statement = "replace";
			}
			$sql = $statement . $ignore . ' into ' . $table . '(' . implode(',', $fields) . ') values' . implode(',', $setValues) . $duplicate;
			return $this->execute($sql, $insertParams);
		} catch (Exception $e) {
			if ($this->isTransactionStarted()) {
				throw $e;
			}
			$this->_lastError = self::pdoException($e->getMessage());
			return false;
		}
	}

	/**
	 * Множественный insert одной командой с разбивкой на чанки
	 *
	 * @param string $table таблица
	 * @param array $values массив данных
	 * @param array $options параметры записи
	 * @param int $iterationCount размер чанка
	 * @return void
	 * @throws Exception
	 */
	public function insertByIteration($table, $values, $options = [], $iterationCount = 1000) {
		$chunks = array_chunk($values, $iterationCount, true);

		foreach ($chunks as $chunk) {
			$this->insertRows($table, $chunk, $options);
		}
	}

	/**
	 * Формирование из массива строки, которую можно передавать в sql. Рассмотрим пример использования. Допустим у нас есть массив
	 * $a = array(0 => "a", "x" => "b", 2 => "c"); Функция вернет строку ":p0,:px,:p2", при этом в массив $params будут добавлены значения
	 * $params = array("p0" => "a", "px" => "b", "p2" => "c"). Если в массиве $params ключ уже будет существовать, то ключ будет модифицирован
	 * по принципу $key = $prefix . $key столько раз, пока такого ключа не будет в массиве
	 *
	 * @param array $values Массив, который нужно собрать в строку
	 * @param array $params Массив, в который будут добавлены параметры и соответствующие им значения
	 * @param int $valueType Тип значения. Константа класса PDO с префиксом PARAM_. По умолчанию все
	 * @param string $prefix Префикс параметров
	 * @param string $separator Разделитель элементов
	 *
	 * @return string Строка, которую передавать в sql
	 */
	public function arrayToStrParams(array &$values, array &$params, $valueType = PDO::PARAM_STR, $prefix = "p", $separator = ",") {

		$valueType = intval($valueType);

		$result = "";
		$isFirst = true;

		foreach ($values as $key => $value) {

			$paramKey = $prefix . $key;

			while (array_key_exists($paramKey, $params)) {
				$paramKey = $prefix . $paramKey;
			}

			if ($isFirst) {
				$result = ":$paramKey";
				$isFirst = false;
			} else {
				$result .= "$separator:$paramKey";
			}

			$params[$paramKey] = array('PDOType' => $valueType, 'value' => $value);
		}

		return $result;
	}

	/**
	 * Извлечь все результаты sql запроса в ассициативный массив в качестве ключа первый столбец.
	 * @param string $sql sql-запрос
	 * @param array $params Массив параметров в sql запросе
	 * @return array
	 */
	public function getList($sql, $params = []) {
		$list = [];

		$rows = $this->fetchAllNameByColumn($sql, 0, $params);

		if (!empty($rows)) {
			foreach ($rows as $key => $row) {
				$list[$key] = (object)$row;
			}
		}

		return $list;
	}

	/**
	 * Залогировать innodb status (содержит причину deadlock)
	 */
	protected function logInnodbStatus() {
		$data = $this->fetchAll("show engine innodb status");
		Log::daily(print_r($data, true), 'deadlock_reason');
	}

	/**
	 * Начало транзации
	 * @return mixed PDO::beginTransaction
	 */
	public function beginTransaction() {
		$setTransactionResult = $this->_pdoObj->beginTransaction();
		$this->setIsTransactionStarted($setTransactionResult);
		return $setTransactionResult;
	}

	/**
	 * Фиксация транзации
	 * @return mixed PDO::commit
	 */
	public function commit() {
		$commitResult = $this->_pdoObj->commit();
		$this->setIsTransactionStarted(false);
		return $commitResult;
	}

	/**
	 * Откат транзации
	 * @return mixed PDO::rollBack
	 */
	public function rollBack() {
		$rollbackResult = $this->_pdoObj->rollBack();
		$this->setIsTransactionStarted(false);
		return $rollbackResult;
	}

	public function fieldExist(string $table, string $field): bool {
		$sql = "SHOW COLUMNS FROM $table WHERE Field = '$field'";
		$result = $this->fetchAll($sql);
		return !empty($result);
	}

	/**
	 * Получение текущего уровня изоляции транзакций
	 * @return string
	 */
	private function currentIsolationLevel(): string {
		$setting = $this->fetch("SHOW VARIABLES LIKE 'tx_isolation'");
		if (!empty($setting["Value"])) {
			//MYSQL возвращает READ-UNCOMMITTED
			return str_replace('-', ' ', $setting["Value"]);
		}
		return "";
	}

	/**
	 * Сохраняет уровень изоляции транзакций по умолчанию
	 */
	private function saveDefaultIsolationLevel() {
		if (empty($this->_defaultIsolationLevel)) {
			$this->_defaultIsolationLevel = $this->currentIsolationLevel();
		}
	}

	/**
	 * Устанавливает уровень изоляции транзакций сессии SERIALIZABLE
	 */
	public function setIsolationLevelSerializable() {
		$this->setIsolationLevel("SERIALIZABLE");
	}

	/**
	 * Устанавливает уровень изоляции транзакций сессии READ UNCOMMITTED
	 */
	public function setIsolationLevelUncommited() {
		$this->setIsolationLevel("READ UNCOMMITTED");
	}

	/**
	 * Устанавливает уровень изоляции транзакций сессии
	 * @param string $level
	 */
	private function setIsolationLevel(string $level) {
		$this->saveDefaultIsolationLevel();
		if (!empty($this->_defaultIsolationLevel) && in_array($level, self::ISOLATION_LEVELS)) {
			$this->execute("SET SESSION TRANSACTION ISOLATION LEVEL $level");
		}
	}

	/**
	 * Устанавливает уровень транзакций сессии по умолчанию
	 */
	public function setDefaultIsolationLevel() {
		if (!empty($this->_defaultIsolationLevel)) {
			$this->setIsolationLevel($this->_defaultIsolationLevel);
		}
	}

	/**
	 * Конкатенация в строку условий для выборки из бд
	 * @param array $condition массив значений
	 * @param string $alias алиас для таблицы
	 * @param string $sign оператор условия
	 * @return string
	 */
	public function buildCondition(array $condition, string $alias = '', $sign = "="): string {
		$string = "";
		foreach ($condition as $key => $value) {
			$string .= (empty($string)) ? "" : " AND ";
			$string .= (empty($alias) ? "" : $alias . '.') . $key . " " . $sign . ":" . $value;
		}

		return $string;
	}

	/**
	 * Подготавливает условие для безопасной выборки через IN из массива
	 * Возвращает массив содержащий строку SQL "condition" и параметры "params"
	 *
	 * @param string $column - колонка БД для выборки с префиксом
	 * @param array $array - массив значений
	 * @return array
	 */
	public function buildInCondition(string $column, array $array): array {
		$condition = "";
		$params = [];

		if (!empty($array)) {
			$in = "";
			foreach ($array as $i => $item) {
				$inKey = ":{$column}_value_" . $i;
				$in .= "$inKey,";
				$params[$inKey] = $item;
			}
			$in = rtrim($in, ",");
			$condition = "({$in})";
		}

		return [
			"condition" => $condition,
			"params" => $params
		];
	}

	/**
	 * Подготавливает условие для безопасной выборки через IN из массива
	 * Возвращает строку SQL "condition" вида (:param1, :param2 ... , :paramN)
	 *
	 * @param string $column - колонка БД для выборки с префиксом
	 * @param array $array - массив значений
	 * @return string
	 */
	public function buildInQuery(string $column, array $array): string {
		return $this->buildInCondition($column, $array)["condition"];
	}

	/**
	 * Подготавливает условие для безопасной выборки через IN из массива
	 * Возвращает массив параметров
	 *
	 * @param string $column - колонка БД для выборки с префиксом
	 * @param array $array - массив значений
	 * @return array
	 */
	public function buildInParams(string $column, array $array): array {
		return $this->buildInCondition($column, $array)["params"];
	}

	/**
	 * Очищает ранее сохранённый массив параметров запроса
	 */
	public function clearParams() {
		$this->_params = [];
	}

	/**
	 * Возвращает все параметры запроса
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}

	/**
	 * Получить параметр запроса по ключу
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getParam(string $key) {
		return $this->_params[$key];
	}

	/**
	 * Установить параметр запроса
	 *
	 * @param string $key
	 * @param $value
	 */
	public function setParam(string $key, $value) {
		$this->_params[$key] = $value;
	}

	/**
	 * Удаление итерациями по $iterationCount
	 * @param string $sql
	 * @param int $iterationCount
	 */
	public function removeByIteration(string $sql, int $iterationCount) {
		$sql = "{$sql} LIMIT {$iterationCount}";
		do {
			$removed = App::pdo($this->getDatabase())->execute($sql);
		} while ($removed > 0);
	}

	/**
	 * Получить значения парарметров, без указания типов PDO
	 * @param array $params
	 * @return array
	 */
	public static function getBindValues(array $params) {
		foreach ($params as $name => $value) {
			if (is_array($value) && isset($value['PDOType'])) {
				$params[$name] = $value['value'];
			}
		}
		return $params;
	}

	/**
	 * Массовое обновление таблицы
	 *
	 * @param string $table Название таблицы
	 * @param array $data Массив данных из которых обновлениям
	 * @param string $keyField Поле PrimaryKey из $data
	 * @param array $updateFields Массив полей которые обновляем из $data
	 *
	 * @return int|false Количество обновленных строк или false в случае ошибки
	 * @throws \Exception
	 */
	public function massUpdate(string $table, array $data, string $keyField, array $updateFields) {
		if (empty($data)) {
			Log::dailyErrorException(new RuntimeException("empty data"));
			return false;
		}

		if (!Helper::isFieldDb($keyField)) {
			Log::dailyErrorException(new RuntimeException("keyField \"$keyField\" is not DB field"));
			return false;
		}
		if (empty($updateFields)) {
			Log::dailyErrorException(new RuntimeException("empty update Fields"));
			return false;
		}
		$updateFields = array_values($updateFields);

		$keys = array_column($data, $keyField, $keyField);
		if (empty($keys)) {
			Log::dailyErrorException(new RuntimeException("empty keys"));
			return false;
		}

		$setStatements = [];
		$params = [];

		foreach ($updateFields as $fieldIndex => $updateField) {
			if (!Helper::isFieldDb($updateField)) {
				Log::dailyErrorException(new RuntimeException("update field \"$updateField\" is not DB field"));
				return false;
			}
			$updateValues = array_column($data, $updateField, $keyField);
			if (empty($updateValues)) {
				Log::dailyErrorException(new RuntimeException("empty updateValues"));
				return false;
			}
			$setStatements[$updateField] = "\n$updateField = CASE ";
			foreach ($updateValues as $key => $value) {
				$keyPlaceholder = "k{$key}";
				$valuePlaceholder = "v{$fieldIndex}_{$key}";
				$setStatements[$updateField] .= "\nWHEN {$keyField} = :$keyPlaceholder THEN :$valuePlaceholder";
				$params[$keyPlaceholder] = $key;
				$params[$valuePlaceholder] = $value;
			}
			$setStatements[$updateField] .= "\nEND ";
		}

		$keysPlacheHolders = $this->arrayToStrParams($keys, $params);
		$setsString = implode(",", $setStatements);
		$sql = "UPDATE $table SET $setsString \nWHERE $keyField IN ($keysPlacheHolders)";

		return $this->execute($sql, $params);
	}

}
