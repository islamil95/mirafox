<?php

namespace Track;

use Model\Order;
use OrderManager;
use Translations;
use TrackManager;

/**
 * Класс Status для работы с виртуальным статусом заказа (показываемым)
 * @package Track
 */
class Status
{
	/**
	 * Запрос отмены
	 */
	const IN_CANCEL_REQUEST = -1;

	/**
	 * Данные заказа предоставлены
	 */
	const DATA_PROVIDED = -2;

	/**
	 * В работе
	 */
	const INWORK = -3;

	/**
	 * Заказ создан
	 */
	const CREATED = -4;

	/**
	 * Доработка
	 */
	const REWORK = -5;

	/**
	 * @var int Виртуальный статус
	 */
	private $virtualStatus;

	/**
	 * @var array Типы треков по виртуальным статусам
	 */
	private $statusTypes;

	/**
	 * @var array Типы треков по виртуальным статусам
	 */
	private $titles;

	/**
	 * @var \Model\Track[] Массив треков в обратной сортировке
	 */
	private $reverseTracks;

	/**
	 * @var string Тип пользователя payer|worker
	 */
	private $userType;

	/**
	 * Конструктор
	 *
	 * @param \Model\Order $order Модель заказа
	 * @param int $userId Идентификатор пользователя которому показываем
	 */
	public function __construct(Order $order, int $userId)
	{
		if ($order->USERID == $userId) {
			$this->userType = "payer";
		} else {
			$this->userType = "worker";
		}

		$this->reverseTracks = array_reverse($order->tracks->all(), true);
		$this->initStatusTypes();
		$this->initTitles();

		$isInProgress = $order->isInProgress();
		if ($order->has_stages && $this->userType == \UserManager::TYPE_WORKER) {
			$isInProgress = $order->isInprogressForWorker();
		}

		if ($isInProgress) {
			if ($order->isInCancelRequest()) {
				$this->virtualStatus = self::IN_CANCEL_REQUEST;
			} elseif ($this->isRework()) {
				$this->virtualStatus = self::REWORK;
			} elseif ($order->in_work) {
				$this->virtualStatus = self::INWORK;
			} elseif ($order->data_provided) {
				$this->virtualStatus = self::DATA_PROVIDED;
			} else {
				$this->virtualStatus = self::CREATED;
			}
		} else {
			$this->virtualStatus = $order->status;
		}
	}


	/**
	 * Инициализирует массив названий для всех статусов и реальных и виртуальных
	 */
	private function initTitles()
	{
		$this->titles = [
			self::REWORK => Translations::t("Доработка"),
			self::IN_CANCEL_REQUEST => Translations::t("Запрос отмены"),
			self::DATA_PROVIDED => Translations::t("Данные по заказу предоставлены"),
			self::INWORK => Translations::t("В работе"),
			self::CREATED => Translations::t("Заказ создан"),
			OrderManager::STATUS_NEW => Translations::t("Заявка создана"),
			OrderManager::STATUS_INPROGRESS => Translations::t("В работе"), //fallback
			OrderManager::STATUS_ARBITRAGE => Translations::t("Арбитраж"),
			OrderManager::STATUS_CANCEL => Translations::t("Отменен"),
			OrderManager::STATUS_CHECK => Translations::t("На проверке"),
			OrderManager::STATUS_DONE => Translations::t("Выполнен"),
			OrderManager::STATUS_UNPAID => Translations::t("Приостановлен"),
		];
	}

	/**
	 * Получение названия статуса
	 * @return string
	 */
	public function title(): string
	{
		if (array_key_exists($this->virtualStatus, $this->titles)) {
			return $this->titles[$this->virtualStatus];
		}

		return "";
	}

	/**
	 * Определение является ли доработкой
	 * @return bool
	 */
	private function isRework(): bool
	{
		$reworkTypes = [
			Type::PAYER_CHECK_INPROGRESS,
			Type::ADMIN_ARBITRAGE_INPROGRESS,
			Type::ADMIN_DONE_INPROGRESS,
			Type::ADMIN_CANCEL_INPROGRESS,
		];

		if ($this->lastTrackType($reworkTypes)) {
			return true;
		}

		return false;
	}

	/**
	 * Инициализирует типы треков по виртуальным статусам
	 */
	private function initStatusTypes()
	{
		$this->statusTypes = [
			self::REWORK => [
				Type::PAYER_CHECK_INPROGRESS,
				Type::ADMIN_ARBITRAGE_INPROGRESS,
				Type::ADMIN_DONE_INPROGRESS,
				Type::ADMIN_CANCEL_INPROGRESS,
				Type::WORKER_INPROGRESS_CANCEL_DELETE,
				Type::WORKER_INPROGRESS_CANCEL_REJECT,
				Type::PAYER_INPROGRESS_CANCEL_DELETE,
				Type::PAYER_INPROGRESS_CANCEL_REJECT,
			],
			self::IN_CANCEL_REQUEST => [
				Type::WORKER_INPROGRESS_CANCEL_REQUEST,
				Type::PAYER_INPROGRESS_CANCEL_REQUEST,
			],
			self::CREATED => [
				Type::CREATE,
				Type::WORKER_INPROGRESS_CANCEL_DELETE,
				Type::WORKER_INPROGRESS_CANCEL_REJECT,
				Type::PAYER_INPROGRESS_CANCEL_DELETE,
				Type::PAYER_INPROGRESS_CANCEL_REJECT,
			],
			self::DATA_PROVIDED => [
				Type::TEXT_FIRST,
				Type::WORKER_INPROGRESS_CANCEL_DELETE,
				Type::WORKER_INPROGRESS_CANCEL_REJECT,
				Type::PAYER_INPROGRESS_CANCEL_DELETE,
				Type::PAYER_INPROGRESS_CANCEL_REJECT,
			],
			self::INWORK => [
				Type::WORKER_INWORK,
				Type::PAYER_CHECK_INPROGRESS,
				Type::ADMIN_ARBITRAGE_INPROGRESS,
				Type::ADMIN_DONE_INPROGRESS,
				Type::ADMIN_CANCEL_INPROGRESS,
				Type::WORKER_INPROGRESS_CANCEL_DELETE,
				Type::WORKER_INPROGRESS_CANCEL_REJECT,
				Type::PAYER_INPROGRESS_CANCEL_DELETE,
				Type::PAYER_INPROGRESS_CANCEL_REJECT,
			],
			OrderManager::STATUS_INPROGRESS => [
				Type::CREATE,
				Type::TEXT_FIRST,
				Type::WORKER_INWORK,
				Type::PAYER_CHECK_INPROGRESS,
				Type::ADMIN_ARBITRAGE_INPROGRESS,
				Type::ADMIN_DONE_INPROGRESS,
				Type::ADMIN_CANCEL_INPROGRESS,
				Type::WORKER_INPROGRESS_CANCEL_DELETE,
				Type::WORKER_INPROGRESS_CANCEL_REJECT,
				Type::PAYER_INPROGRESS_CANCEL_DELETE,
				Type::PAYER_INPROGRESS_CANCEL_REJECT,
				Type::WORKER_INPROGRESS_CANCEL_REQUEST,
				Type::PAYER_INPROGRESS_CANCEL_REQUEST,
			],
			OrderManager::STATUS_ARBITRAGE => [
				Type::ADMIN_CANCEL_ARBITRAGE,
				Type::ADMIN_DONE_ARBITRAGE,
				Type::PAYER_CHECK_ARBITRAGE,
				Type::PAYER_INPROGRESS_ARBITRAGE,
				Type::WORKER_CHECK_ARBITRAGE,
				Type::WORKER_INPROGRESS_ARBITRAGE,
			],
			OrderManager::STATUS_CANCEL => [
				Type::ADMIN_ARBITRAGE_CANCEL,
				Type::ADMIN_CHECK_CANCEL,
				Type::ADMIN_INPROGRESS_CANCEL,
				Type::CRON_INPROGRESS_CANCEL,
				Type::CRON_INPROGRESS_INWORK_CANCEL,
				Type::PAYER_INPROGRESS_CANCEL,
				Type::PAYER_INPROGRESS_CANCEL_CONFIRM,
				Type::WORKER_INPROGRESS_CANCEL,
				Type::WORKER_INPROGRESS_CANCEL_CONFIRM,
			],
			OrderManager::STATUS_CHECK => [
				Type::WORKER_INPROGRESS_CHECK,
				Type::ADMIN_ARBITRAGE_CHECK,
			],
			OrderManager::STATUS_DONE => [
				Type::ADMIN_ARBITRAGE_DONE,
				Type::ADMIN_ARBITRAGE_DONE_HALF,
				Type::CRON_WORKER_CHECK_DONE,
				Type::PAYER_CHECK_DONE,
				Type::PAYER_INPROGRESS_DONE
			],
			OrderManager::STATUS_UNPAID => [Type::STAGE_UNPAID],
		];
	}

	/**
	 * Описание из трека по виртуальному статусу
	 *
	 * @return string Описание
	 */
	public function description(): string
	{
		if (array_key_exists($this->virtualStatus, $this->statusTypes)) {
			$lastTrackType = $this->lastTrackType($this->statusTypes[$this->virtualStatus]);
			if ($lastTrackType) {
				return TrackManager::getStatusDesc($lastTrackType, $this->userType);
			}
		}

		return "";
	}

	/**
	 * Поиск типа последнего трека, одно из представленных
	 *
	 * @param array $types Массив типов треков
	 * @return string Тип последнего трека
	 */
	private function lastTrackType(array $types): string
	{
		foreach ($this->reverseTracks as $track) {
			if (in_array($track->type, $types)) {
				return $track->type;
			}
		}

		return "";
	}

}