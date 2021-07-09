<?php


namespace Order\Stages;


use Helper;
use Model\OrderStages\ChangeLog;
use Model\OrderStages\OrderStage;

class ChangeLogManager {

	const ORDER_STAGE_FIELDS = [
		OrderStage::FIELD_NUMBER => ChangeLog::FIELD_IS_UPDATED_NUMBER,
		OrderStage::FIELD_TITLE => ChangeLog::FIELD_IS_UPDATED_TITLE,
		OrderStage::FIELD_PAYER_AMOUNT => ChangeLog::FIELD_IS_UPDATED_PAYER_AMOUNT,
	];

	/**
	 * Сохранить запись в лог
	 * @param OrderStage $stage - этап заказа
	 * @param int $action - действие: сохранить(1), редактировать(3) или удалить(2)
	 * @param array $fields - измененые поля
	 * @return bool
	 */
	public static function save(OrderStage $stage, int $action = ChangeLog::ACTION_ADD, array $fields = []) {
		//Если этап был добавлен покупателем и еще не просмотрен продавцом,
		//смысла логировать изменения нет.
		if ($action == ChangeLog::ACTION_EDIT && self::isUnreadLogRecordExist($stage, ChangeLog::ACTION_ADD)) {
			return false;
		}

		$attributes = [];

		//Проверяем измененные поля, есть ли среди них такие, изменения в которых требуют логирования
		foreach ($fields as $field) {
			if (isset(self::ORDER_STAGE_FIELDS[$field])) {
				$attributes[self::ORDER_STAGE_FIELDS[$field]] = true;
			}
		}

		//Если нет, а это еще и запрос редактирования, то можно не логировать.
		if ($action == ChangeLog::ACTION_EDIT && empty($attributes)) {
			return false;
		}

		$attributes[ChangeLog::FIELD_ACTION] = $action;
		$attributes[ChangeLog::FIELD_STAGE_ID] = $stage->id;
		$attributes[ChangeLog::FIELD_UNREAD] = true;

		return self::saveOrUpdateRecord($stage, $action, $attributes);
	}

	/**
	 * Получить коллекцию записей из лога
	 *
	 * @param int $orderId - ID заказа
	 * @param bool $unreadOnly - выбирать только непрочитанные
	 * @param bool $isUpdatedFromAjax - список запрошен через ajax
	 * @return array
	 */
	public static function getLogRecordArray(int $orderId, bool $unreadOnly = false, bool $isUpdatedFromAjax = false) {
		/** @var ChangeLog[] $records */
		$records = self::getLogRecords($orderId, $unreadOnly);
		$resultRecords = [];
		foreach ($records as $record) {
			if (!isset($resultRecords[$record->stage_id])) {
				$resultRecords[$record->stage_id] = [
					"action" => $record->action,
					"updated" => [],
					"date" => Helper::dateFormat($record->updated_at, "%e %m, %H:%M", null, "rus", "short"),
					"unread" => $record->unread,
				];
			}

			if ($resultRecords[$record->stage_id]["action"] != ChangeLog::ACTION_DELETE) {
				self::getChangedFields($record, $resultRecords[$record->stage_id]["updated"]);
			} elseif ($record->action == ChangeLog::ACTION_ADD && $record->unread == 1) {
				$deletedBeforeReadingStageIds[] = $record->stage_id;
				if (!$isUpdatedFromAjax) {
					$resultRecords[$record->stage_id]["unread"] = 0;
				}
			}
		}
		// Если запрос записей лога не из аякса,
		// то все записи об этапах которые были добавлены и сразу удалены, до прочтения продавцом,
		// отмечаем как прочитанные
		if (!$isUpdatedFromAjax && !empty($deletedBeforeReadingStageIds)) {
			self::setReadedByStageId($deletedBeforeReadingStageIds);
		}
		return $resultRecords;
	}

	/**
	 * Отметить записи прочитанными
	 * @param array $stageIds
	 */
	public static function setReadedByStageId(array $stageIds) {
		ChangeLog::whereIn(ChangeLog::FIELD_STAGE_ID, $stageIds)
			->where(ChangeLog::FIELD_UNREAD, true)
			->update([
				ChangeLog::FIELD_UNREAD => false,
			]);
	}

	/**
	 * Получить массив записей из лога
	 *
	 * @param int $orderId - id заказа
	 * @param bool $unreadOnly - выбирать только непрочитанные
	 * @param string|null $action
	 * @return array|ChangeLog[]
	 */
	public static function getLogRecords(int $orderId, bool $unreadOnly = false, string $action = null) {
		$stageIds = OrderStage::withTrashed()
			->where(OrderStage::FIELD_ORDER_ID, $orderId)
			->pluck(OrderStage::FIELD_ID)
			->toArray();

		if (empty($stageIds)) {
			return [];
		}

		$query = ChangeLog::whereIn(ChangeLog::FIELD_STAGE_ID, $stageIds);
		if ($unreadOnly) {
			$query->where(ChangeLog::FIELD_UNREAD, 1);
		}
		if (!is_null($action)) {
			$query->where(ChangeLog::FIELD_ACTION, $action);
		}
		$records = $query->orderByDesc(ChangeLog::FIELD_CREATED_AT)
			->get();

		return $records;
	}

	/**
	 * Массив измененных полей
	 * @param ChangeLog $record - запись лога
	 * @param array $fields - измененные поля
	 */
	private static function getChangedFields(ChangeLog $record, array &$fields) {
		foreach (self::ORDER_STAGE_FIELDS as $stageFieldName => $logFieldName) {
			if (isset($record->{$logFieldName}) && ($record->{$logFieldName} > 0) && !in_array($stageFieldName, $fields)) {
				$fields[] = $stageFieldName;
			}
		}
	}

	/**
	 * Есть ли непрочитанный лог определенного типа
	 * @param OrderStage $stage - этап
	 * @param int|null $action - действие: сохранить(1), редактировать(3) или удалить(2)
	 * @return bool
	 */
	private static function isUnreadLogRecordExist(OrderStage $stage, int $action = null) {
		$query = ChangeLog::where(ChangeLog::FIELD_STAGE_ID, $stage->id);
		if (!is_null($action)) {
			$query->where(ChangeLog::FIELD_ACTION, $action);
		}
		return $query->where(ChangeLog::FIELD_UNREAD, 1)
			->exists();
	}

	/**
	 * Обновить или добавить запись в лог
	 *
	 * @param OrderStage $stage - этап
	 * @param int $action - действие: сохранить(1), редактировать(3) или удалить(2)
	 * @param array $attributes - поля и занчения для записи/обновления
	 * @return bool|int
	 */
	private static function saveOrUpdateRecord(OrderStage $stage, int $action, array $attributes) {
		//Если есть непрочитанная запись о действии над этим этапом - обновляем ее,
		//если нет - создаем новую запись в лог.
		if (self::isUnreadLogRecordExist($stage, ChangeLog::ACTION_EDIT)) {
			return ChangeLog::where(ChangeLog::FIELD_STAGE_ID, $stage->id)
				->where(ChangeLog::FIELD_UNREAD, 1)
				->update($attributes);
		} else {
			return ChangeLog::insert($attributes);
		}
	}
}