<?php

namespace Core\DB;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Connection;

/**
 * Фабрика для того чтобы переопределить подключение к mysql подключением с автоматическим повторением запросов при дедлоках
 */
class DatabaseConnectionFactory extends ConnectionFactory
{
	/**
	 * Create a new connection instance.
	 *
	 * @param  string   $driver
	 * @param  \PDO|\Closure     $connection
	 * @param  string   $database
	 * @param  string   $prefix
	 * @param  array    $config
	 * @return \Illuminate\Database\Connection
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
	{
		if ($driver !== 'mysql') {
			return parent::createConnection($driver, $connection, $database, $prefix, $config);
		}

		if ($resolver = Connection::getResolver($driver)) {
			return $resolver($connection, $database, $prefix, $config);
		}

		return new AutoRetryMySqlConnection($connection, $database, $prefix, $config);
	}
}