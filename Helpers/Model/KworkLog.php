<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;


/**
 * Модель лог-записи для таблицы логирования состояния кворков
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int id - Идентификатор лог-записи
 * @property int user_id - Идентификатор автора лог-записи
 * @property int kwork_id - Идентификатор кворка лог-записи
 * @property int status - Статус кворка
 * @property int feat - Активность кворка
 * @property string log_type - Тип записи
 * @property string date_create - Дата создания лог-записи
 * @property int kwork_moder_id - Идентификатор модерации

 */
class KworkLog extends Model {

	/**
	 * Имя таблицы
	 */
	const TABLE_NAME = "kwork_log";

	/**
	 * Идентификатор лог-записи
	 */
	const FIELD_ID = "id";

	/**
	 * Идентификатор автора лог-записи
	 */
	const FIELD_USER_ID = "user_id";

	/**
	 * Идентификатор кворка лог-записи
	 */
	const FIELD_KWORK_ID = "kwork_id";

	/**
	 * Статус кворка
	 */
	const FIELD_STATUS = "status";

	/**
	 * Активность кворка
	 */
	const FIELD_FEAT = "feat";

	/**
	 * Тип записи
	 */
	const FIELD_LOG_TYPE = "log_type";

	/**
	 * Дата создания лог-записи
	 */
	const FIELD_DATE_CREATE = "date_create";

	/**
	 * Идентификатор модерации
	 */
	const FIELD_KWORK_MODER_ID = "kwork_moder_id";

	/**
	 * Идентификатор админа
	 */
	const FIELD_ADMIN_ID = "admin_id";

	/**
	 * Был ли виртуальный логин
	 */
	const FIELD_IS_VIRTUAL = "is_virtual";

	/**
	 * Текстовые данные об изменении кворка.
	 */
	const FIELD_DATA = "data";
	/**
	 * Кол-во записей для выборки для удаления мусора из таблицы kwork_log
	 */
	const WASTE_RECORDS_LIMIT = 1000;

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_ID;
	public $timestamps = false;

	/**
	 * Определить статус активности кворка по лог-записи
	 *
	 * @param array $log Параметры записи
	 * @return bool Кворк Активен
	 */
	public static function isActive($log) {
		$onStatusInactive = (!is_null($log[self::FIELD_STATUS]) && $log[self::FIELD_STATUS] != \KworkManager::STATUS_ACTIVE);
		$onFeatInactive = (!is_null($log[self::FIELD_FEAT]) && $log[self::FIELD_FEAT] != \KworkManager::FEAT_ACTIVE);
		return !$onStatusInactive && !$onFeatInactive;
	}

	/**
	 * Добавить запись в лог-таблицу
	 *
	 * @param array $log Параметры записи для добавления лог-записи
	 * @return bool
	 */
	public static function insertLog($log) {
		if(empty($log[self::FIELD_KWORK_ID])) {
			return false;
		}
		return self::insert($log);
	}

	/**
	 * Удалить ненужные записи из таблицы kwork_log
	 * удаляем все записи старше 2 месяцев
	 * кроме последней для каждого кворка status = STATUS_SUSPEND и STATUS_PAUSE
	 * и последней записи для кворка log_type = 'autoactive' and status = STATUS_ACTIVE
	 *
	 * @return int Кол-во удалённых записей
	 */
	public static function deleteWasteRecords() {
		echo "START KworkLog::deleteWasteRecords\r\n";
		$wastePeriod = \Helper::dateTimeToMysqlString(date_create("now - 2 month"));
		$deletedCount = $processedCount = 0;
		$query = self::where(self::FIELD_DATE_CREATE, "<=", $wastePeriod)->orderBy(self::FIELD_ID);
		$totalCount = $query->count();

		if (empty($totalCount)) {
			echo "FINISH KworkLog::deleteWasteRecords: no waste\r\n";
			return false;
		}

		$lastRecordId = 0;
		do {
			$wasteRecords = $query->where(self::FIELD_ID, ">", $lastRecordId)
				->limit(self::WASTE_RECORDS_LIMIT)
				->get();

			foreach ($wasteRecords as $record) {
				if (
					in_array($record->{self::FIELD_STATUS}, [\KworkManager::STATUS_SUSPEND, \KworkManager::STATUS_PAUSE]) ||
					$record->{self::FIELD_STATUS} == \KworkManager::STATUS_ACTIVE && $record->{self::FIELD_LOG_TYPE}== \KworkManager::KWORK_LOG_TYPE_AUTOACTIVE
				) {
					$otherRecord = self::where(self::FIELD_ID, ">", $record->{self::FIELD_ID})
						->where(self::FIELD_KWORK_ID, $record->{self::FIELD_KWORK_ID})
						->where(self::FIELD_STATUS, $record->{self::FIELD_STATUS});

					if ($record->status == \KworkManager::STATUS_ACTIVE) {
						$otherRecord->where(self::FIELD_LOG_TYPE, \KworkManager::KWORK_LOG_TYPE_AUTOACTIVE);
					}

					if($otherRecord->exists()) {
						if($record->delete()) {
							$deletedCount++;
						}
					}
				} else {
					if($record->delete()) {
						$deletedCount++;
					}
				}
				$lastRecordId = $record->{self::FIELD_ID};
				$processedCount++;
			}
			echo "\r" . number_format(round($processedCount / $totalCount * 100, 2), 2, '.', '') . "%";
		} while (count($wasteRecords) == self::WASTE_RECORDS_LIMIT);

		echo "\r\nFINISH KworkLog::deleteWasteRecords: DELETED {$deletedCount} records\r\n";

		return $deletedCount;
	}
}