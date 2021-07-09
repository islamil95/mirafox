<?php

namespace DbLock;

use App;
use Core\DB\DB;

class DbLock {

	const TABLE_NAME = 'dblock';

	const FIELD_ID = 'id';
	const FIELD_NAME = 'name';
	const FIELD_CREATED = 'created';
	const FIELD_LIFETIME = 'lifetime';

	private $id;
	private $name;
	private $lifetime;
	private $created;

	const MAX_WAIT_TIME = 15;

	/**
	 * DbLock constructor.
	 * @param $name
	 * @param int $lifetime
	 * @throws \Exception
	 */
	public function __construct($name, $lifetime = 600) {
		if (empty($name)) {
			throw new \Exception('Name cannot be empty');
		}
		$lifetime = intval($lifetime);
		if (empty($lifetime) || $lifetime < 0) {
			throw new \Exception('Lifetime must be positive.');
		}
		$this->name = $name;
		$this->lifetime = $lifetime;
		$this->created = \Helper::now();
		$this->waitUntil();
	}

	public function __destruct() {
		$this->delete();
	}

	/**
	 * Удалить элемент
	 * @return bool pdo()->execute
	 */
	private function delete() {
		$sql = "DELETE FROM 
				" . self::TABLE_NAME . " 
			WHERE 
			" . self::FIELD_ID . "=:id";
		return App::pdo()->execute($sql, ['id' => $this->id]);
	}

	/**
	 * Заблокировать запись
	 * @return mixed pdo()->insert
	 */
	private function insert() {
		$insert = [
			self::FIELD_NAME => $this->name,
			self::FIELD_LIFETIME => $this->lifetime,
			self::FIELD_CREATED => $this->created
		];
		$this->id = App::pdo()->insert(self::TABLE_NAME, $insert, [
			App::pdo()::INSERT_OPTION_IGNORE => true,
		]);
		return $this->id;
	}

	/**
	 * Заблокировать по возможности
	 */
	private function waitUntil() {
		$start = time();
		do {
			$this->insert();
			if (is_numeric($this->id)) {
				break;
			}
			sleep(1);
		} while (time() - $start < self::MAX_WAIT_TIME);
		if ($this->id === false) {
			throw new \Exception();
		}
	}

	/**
	 * Переинициализировать таблицу.
	 */
	public static function reInit() {
		\Helper::safeTruncate(self::TABLE_NAME);

		$insert = [
			self::FIELD_NAME => 'locked',
			self::FIELD_LIFETIME => 1,
			self::FIELD_CREATED => \Helper::now(),
		];
		App::pdo()->insert(self::TABLE_NAME, $insert);
	}

	/**
	 * Удалить устаревшие блокировки. Вызывать кроном
	 */
	public static function removeExpired() {
		$query = DB::table(self::TABLE_NAME)
			->where(self::FIELD_CREATED, "<", DB::raw("NOW() - INTERVAL " . self::FIELD_LIFETIME . " SECOND"));

		$locks = (clone $query)->get();

		if ($locks->count() > 0) {
			$log = "Removing expired DbLocks: " . PHP_EOL;
			$locks->each(function($lock) use (&$log) {
				$log .= $lock->{self::FIELD_NAME} . " - " . $lock->{self::FIELD_CREATED} . " (" . $lock->{self::FIELD_LIFETIME} . ")" . PHP_EOL;
			});
			\Log::daily($log, "error");
			$query->delete();
		}
	}

}