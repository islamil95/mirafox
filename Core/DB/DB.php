<?php

namespace Core\DB;

use Core\DB\Batch\Batch;
use Core\Exception\DifferentClassesException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * По сути - короткий алиас для \Illuminate\Database\Capsule\Manager
 * т.к. именно DB используется во всей документации, то про базовый класс
 * лучше даже не знать
 * @mixin \QueryBuliderTypeHinting
 */
class DB extends \Illuminate\Database\Capsule\Manager {

	/**
	 * Build the database manager instance.
	 *
	 * @return void
	 */
	protected function setupManager()
	{
		$factory = new DatabaseConnectionFactory($this->container);

		$this->manager = new DatabaseManager($this->container, $factory);
	}

	/**
	 * Get a fluent query builder instance.
	 *
	 * @param  string  $table
	 * @param  string  $connection
	 * @return \Illuminate\Database\Query\Builder
	 */
	public static function table($table, $connection = null)
	{
		if ($connection === null && in_array($table, \App::DB_WORK_TABLES)) {
			$connection = \App::DB_WORK;
		}
		return parent::table($table, $connection);
	}

	/**
	 * Получить PDO
	 * @param string $database
	 * @return \PDO
	 */
	public static function getPdo($database = \App::DB_MASTER) {
		return parent::connection($database)->getPdo();
	}

	/**
	 * Массовое обновление моделей
	 * Создает запрос вида SET `field` = CASE `id` WHEN ... THEN ...
	 * Поддерживает изменение нескольких разных полей (у разных моделей) сразу
	 * Обновляет только измененные (dirty) поля моделей
	 *
	 * @param Collection|Model[] $changedModels Коллекция измененных моделей одного класса
	 * @param bool $updateTimestamps Обновлять ли updated_at
	 * @return int|null Количество обновленных записей в БД или null в случае, если для запроса нет изменяемых данных
	 * @throws DifferentClassesException
	 * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::isDirty
	 *
	 */
	public static function batchUpdate(Collection $changedModels, bool $updateTimestamps):? int {
		return Batch::update($changedModels, $updateTimestamps);
	}
}
