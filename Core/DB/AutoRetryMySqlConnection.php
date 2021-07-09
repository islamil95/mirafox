<?php


namespace Core\DB;

use Closure;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\QueryException;
use Log;
use PDO;

/**
 * Подключение к mysql с автоматическим повторением запросов при дедлоке
 */
class AutoRetryMySqlConnection extends MySqlConnection {

	/**
	 * Запуск запроса
	 *
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException|\Exception
	 */
	protected function runQueryCallback($query, $bindings, Closure $callback) {
		for ($attempt = 1; $attempt <= \DataBasePDO::MAX_TRY_COUNT; $attempt++) {
			try {
				$result =  parent::runQueryCallback($query, $bindings, $callback);
				if ($attempt > 1) {
					Log::write("{$query}\nResolved at $attempt try", "deadlock_error");
				}
				return $result;
			} catch (QueryException $e) {
				$sql = str_replace_array('\?', $this->prepareBindings($bindings), $query);
				if ($attempt > \DataBasePDO::MAX_TRY_COUNT) {
					Log::write("{$sql}\nCannot resolver at $attempt tries", "deadlock_error");
					throw $e;
				}

				if (!$this->shouldRetry($errorCode = $e->getCode())) {
					Log::daily("{$sql}\nShouldn't Retry: {$sql}\nStack trace: " . Log::getStackCall(), Log::ERROR_LOG);
					throw $e;
				}

				if ($this->isLostConnectionError($errorCode)) {
					$this->restoreConnection();
				}


				Log::daily($_SERVER['REQUEST_URI'] . "\n" . $sql . "\nStack trace: " . Log::getStackCall(), Log::ERROR_LOG);
				// задержка при перезапуске запроса
				usleep(\DataBasePDO::RETRY_PAUSE);
			}
		}
	}

	/**
	 * Переподключение к базе
	 *
	 * @throws \Exception
	 */
	private function restoreConnection() {
		for ($attempt = 1; $attempt <= \DataBasePDO::MAX_CONNECTION_TRY_COUNT; $attempt++) {
			try {
				$this->reconnect();
				\App::pdo()->reConnect();
				break;
			} catch (\Exception $exception) {
				if ($attempt > \DataBasePDO::MAX_CONNECTION_TRY_COUNT) {
					throw $exception;
				}
				usleep(\DataBasePDO::RETRY_PAUSE);
			}
		}

	}

	/**
	 * Ошибка вызванна блокировкой
	 *
	 * @param int $errorCode
	 * @return bool
	 */
	private function isDeadLockError(int $errorCode): bool {
		return $errorCode === \DataBasePDO::DEADLOCK_ERROR;
	}

	/**
	 * Ошибка вызвана потерей соединения
	 *
	 * @param string $errorCode
	 * @return bool
	 */
	private function isLostConnectionError(string $errorCode): bool {
		return $errorCode === \DataBasePDO::LOST_CONNECTION_ERROR;
	}


	/**
	 * Проверка по ошибке нужно ли перезапускать запрос, перезапуск только запросов без транзакции
	 *
	 * @param string|integer $errorCode
	 *
	 * @return boolean
	 */
	protected function shouldRetry($errorCode) {
		return ($this->isDeadLockError((int) $errorCode) || $this->isLostConnectionError((string) $errorCode))
			&& $this->transactions === 0;
	}

	/**
	 * Переопределение для установки другого значения количества попыток по умолчанию
	 *
	 * @param \Closure $callback Замыкание содержащее запросы транзакиции
	 * @param int $attempts Количество попыток перезапуска транзации при дедлоке
	 *
	 * @return mixed
	 * @throws \Throwable
	 */
	public function transaction(Closure $callback, $attempts = \DataBasePDO::MAX_TRY_COUNT) {
		return parent::transaction($callback, $attempts);
	}

	/**
	 * Bind values to their parameters in the given statement.
	 * #7757 hotfix
	 *
	 * @param  \PDOStatement $statement
	 * @param  array  $bindings
	 */
	public function bindValues($statement, $bindings) {
		foreach ($bindings as $key => $value) {
			$statement->bindValue(
				is_string($key) ? $key : $key + 1, $value,
				is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
			);
		}
	}
}