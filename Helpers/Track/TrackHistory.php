<?php
namespace Track;

use Illuminate\Database\Eloquent\Collection;
use Model\Track;
use Model\Track\TrackHistoryItem;

class TrackHistory {

	public static function getMustHaveTypes() {
		return [
			"Заказ создан",
			"Взят в работу",
			"Сдан на проверку",
			"Принят",
			"Опубликован в портфолио",
		];
	}

	/**
	 * TrackHistory constructor.
	 * @param Collection|Track[] $tracks
	 * @param string $userType
	 */
	public function __construct($tracks, $userType) {
		$this->tracks = $tracks;
		$this->userType = $userType;
	}

	/**
	 * @var Collection|Track[]
	 */
	private $tracks;
	private $userType;
	private $result = [];

	/**
	 * Получить список типов, которые игнорируются в истории
	 * @return array
	 */
	private static function getIgnoreTrackTypeArray(): array {
		return [
			Type::TEXT,
			Type::TEXT_FIRST,
			Type::FROM_DIALOG,
			Type::PAYER_ADVICE,
			Type::INSTRUCTION,
			Type::CREATE,
		];
	}

	/**
	 * Получить список типов, относящихся к автоотмене
	 * @return array
	 */
	private static function getAutocancelArray(): array {
		return [
			Type::CRON_INPROGRESS_CANCEL,
			Type::CRON_INPROGRESS_INWORK_CANCEL,
			Type::CRON_PAYER_INPROGRESS_CANCEL,
			Type::CRON_WORKER_INPROGRESS_CANCEL,
			Type::CRON_UNPAID_CANCEL,
		];
	}

	/**
	 * Получить список типов, относящихся к автоотмене администрацией
	 * @return array
	 */
	private static function getAdminCancelArray(): array {
		return [Type::ADMIN_INPROGRESS_CANCEL, Type::ADMIN_CHECK_CANCEL];
	}

	private static function getCancelRequestArray(): array {
		return [Type::WORKER_INPROGRESS_CANCEL_REQUEST, Type::PAYER_INPROGRESS_CANCEL_REQUEST];
	}

	private static function getCancelRequestRejectArray(): array {
		return [Type::WORKER_INPROGRESS_CANCEL_REJECT, Type::PAYER_INPROGRESS_CANCEL_REJECT];
	}

	/**
	 * Получить список типов, относящихся к принятию заказа покупателем
	 * @return array
	 */
	private static function getArrayPayerDone(): array {
		return [
			Type::PAYER_INPROGRESS_DONE,
			Type::PAYER_CHECK_DONE,
			Type::CRON_WORKER_CHECK_DONE,
		];
	}

	/**
	 * Получить список типов, относящихся к принятию заказа покупателем
	 * @return array
	 */
	private static function getCancelRequestDeleteArray(): array {
		return [Type::WORKER_INPROGRESS_CANCEL_DELETE, Type::PAYER_INPROGRESS_CANCEL_DELETE];
	}

	/**
	 * Типы принятия этапов
	 *
	 * @return array
	 */
	private static function getStageApprove():array {
		return [
			Type::PAYER_APPROVE_STAGES,
			Type::CRON_CHECK_APPROVE_STAGE,
			Type::PAYER_APPROVE_STAGES_INPROGRESS,
		];
	}

	/**
	 * Получить список типов, относящихся к принятию заказа покупателем
	 * @return array
	 */
	private static function getCancelRequestConfirmArray(): array {
		return [Type::WORKER_INPROGRESS_CANCEL_CONFIRM, Type::PAYER_INPROGRESS_CANCEL_CONFIRM];
	}

	private static function getSendArbitrageArray(): array {
		return [Type::ADMIN_CANCEL_ARBITRAGE,
			Type::ADMIN_DONE_ARBITRAGE,
			Type::WORKER_INPROGRESS_ARBITRAGE,
			Type::WORKER_CHECK_ARBITRAGE,
			Type::PAYER_INPROGRESS_ARBITRAGE,
			Type::PAYER_CHECK_ARBITRAGE];
	}

	/**
	 *
	 * @return type
	 */
	private static function getAdminReturnInworkOrderArray(): array {
		return [Type::ADMIN_DONE_INPROGRESS,
			Type::ADMIN_CANCEL_INPROGRESS];
	}

	/**
	 * Получить историю заказа с короткими описаниями
	 * @param bool $revert инвертировать ли историю
	 * @param int $length - количество записей
	 * @return \Model\Track\TrackHistoryItem []
	 */
	public function getHistory($revert = false, $length = 0): array {
		if ($revert) {
			$this->tracks = $this->tracks->reverse();
		}
		if ($length > 0) {
			$this->tracks = $this->tracks->slice(0, $length);
		}
		foreach ($this->tracks as $track) {
			$desc = $this->getShortDesc($track->type);
			if (!empty($desc)) {
				$this->addUniqueInResult(new TrackHistoryItem($track->date_create, $desc));
			}
		}
		return $this->result;
	}

	/**
	 * Получить описание для трека типа extra
	 *
	 * @param Track $track Модель трека
	 *
	 * @return string
	 */
	private function getExtraTrackDescription($track) {
		if ($track->status == \TrackManager::STATUS_DONE) {
			return \Translations::t("Докуплены опции");
		}
		return \Translations::t("Предложены опции");
	}

	/**
	 * Добавить в результат элементы истории
	 * @param TrackHistoryItem $thi
	 */
	private function addInResult(TrackHistoryItem $thi) {
		$this->result[] = $thi;
	}

	/**
	 * Добавить в результат элементы истории c проверкной никальности
	 * @param TrackHistoryItem $thi
	 */
	private function addUniqueInResult(TrackHistoryItem $thi) {
		$unique = true;
		if (!empty($this->result)) {
			foreach ($this->result as $item) {
				if ($item->getShortDescription() == $thi->getShortDescription()) {
					$unique = false;
					break;
				}
			}
		}
		if ($unique) {
			$this->addInResult($thi);
		}
	}

	/**
	 * Является ли пользователь покупателем
	 *
	 * @return bool
	 */
	private function isPayer() {
		return $this->userType == \UserManager::TYPE_PAYER;
	}

	/**
	 * Решение по арбитражу поэтапного заказа
	 *
	 * @return array
	 */
	private function getStageArbitrage() {
		return [
			Type::ADMIN_ARBITRAGE_STAGE_CANCEL,
			Type::ADMIN_ARBITRAGE_STAGE_DONE,
			Type::ADMIN_ARBITRAGE_STAGE_CONTINUE,
		];
	}

	/**
	 * Продолжение заказа
	 *
	 * @return array
	 */
	private function getStageInprogress():array {
		return [
			Type::PAYER_UNPAID_INPROGRESS,
			Type::PAYER_DONE_INPROGRESS,
			Type::PAYER_CANCEL_INPROGRESS,
			Type::PAYER_DONE_INPROGRESS_UNPAID,
		];
	}

	/**
	 * Получить короткое описание для истории по типу трека
	 * @param string $type
	 * @return string
	 */
	private function getShortDesc(string $type) {
		if ($type == Type::PAYER_NEW_INPROGRESS) {
			return \Translations::t("Заказ создан");
		} elseif ($type == Type::WORKER_INWORK) {
			return \Translations::t("Взят в работу");
		} elseif ($type == Type::WORKER_INPROGRESS_CHECK) {
			return \Translations::t("Сдан на проверку");
		} elseif ($type == Type::WORKER_INPROGRESS_CANCEL) {
			return \Translations::t("Заказ отменен");
		} elseif ($type == Type::PAYER_INPROGRESS_CANCEL) {
			return \Translations::t("Заказ отменен");
		} elseif (in_array($type, self::getArrayPayerDone())) {
			return \Translations::t("Принят");
		} elseif (in_array($type, self::getCancelRequestConfirmArray())) {
			return \Translations::t("Заказ отменен");
		}

		return "";
	}
}
