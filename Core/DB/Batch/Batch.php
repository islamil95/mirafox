<?php declare(strict_types=1);

namespace Core\DB\Batch;

use Carbon\Carbon;
use Core\DB\DB;
use Core\Exception\DifferentClassesException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Массовые изменения данных в БД
 *
 * Class Batch
 * @package Core\DB\Batch
 */
final class Batch {
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
	public static function update(Collection $changedModels, bool $updateTimestamps): ?int {
		if ($changedModels->isEmpty()) {
			return null;
		}

		$firstModel = $changedModels->first();

		$table = $firstModel->getTable();
		$primaryKey = $firstModel->getKeyName();

		$ids = [];
		$fieldToStatementsMap = [];

		foreach ($changedModels as $model) {
			if (get_class($model) !== get_class($firstModel)) {
				throw new DifferentClassesException("В коллекции не могут быть модели разных классов");
			}

			if (!$model->isDirty()) {
				continue;
			}

			if ($updateTimestamps && $model->usesTimestamps() && !$model->isDirty(Model::UPDATED_AT)) {
				$model->setUpdatedAt(Carbon::now());
			}

			$isAnyStatementAdded = false;

			foreach ($model->getAttributes() as $field => $value) {
				if ($field === $primaryKey || !$model->isDirty($field)) {
					continue;
				}

				$fieldToStatementsMap[$field][] = new CaseStatement(
					$model->getKey(),
					$value
				);

				$isAnyStatementAdded = true;
			}

			if ($isAnyStatementAdded) {
				// Добавляем в список id только модели с добавленными изменениями (CaseStatement)
				$ids[] = $model->getKey();
			}
		}

		// Внутреняя переменная цикла, больше не нужна
		unset($isAnyStatementAdded);

		if (!$fieldToStatementsMap) {
			return null;
		}

		$params = [];
		$cases = [];

		foreach ($fieldToStatementsMap as $field => $statements) {
			$case = "`{$field}` = CASE {$primaryKey} \n ";

			/** @var CaseStatement $statement */
			foreach ($statements as $statement) {
				$case .= $statement->getStatement() . "\n ";

				$params[] = $statement->getValue();
			}

			$case .= "ELSE `{$field}` END\n";

			$cases[] = $case;
		}

		if (!$cases) {
			return null;
		}

		$idsString = implode(", ", $ids);
		$casesString = implode(", ", $cases);

		$query = "UPDATE `{$table}` SET {$casesString} WHERE `{$primaryKey}` in ({$idsString})";

		return DB::update($query, $params);
	}
}