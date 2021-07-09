<?php

namespace Controllers\Track\Handler\Worker;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Core\DB\DB;
use Core\Exception\JsonException;
use Core\Exception\JsonValidationException;
use DbLock\DbLock;
use DbLock\LockEnum;
use Helpers\MailEvents\Events\NewOrderReport;
use Model\KworkReport;
use Model\Notification\NotificationType;
use Model\OrderStages\OrderStage;
use Order\Stages\OrderStageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Track\Factory\TrackViewFactory;
use Track\Type;
use \TrackManager;
use \Translations;
use \KworkReportManager;
use Validator\OrderStageProgressValidator;

/**
 * Новый отчет по заказу
 *
 * Принимает параметры POST
 * isExecutedOn общий прогресс для заказов без этапов
 * track_id Идентификатор трека промежуточного отчета при редактировании отчета
 * stages Массив этапов в формате [[id => 1, progress => 30], [id => 2, progress => 100],...]
 */
class NewReportHandlerController extends AbstractTrackHandlerController {

	/**
	 * @var \Model\OrderStages\OrderStage[]|null
	 */
	private $editedStages;

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}


	/**
	 * Получить общий процент выполнения
	 *
	 * @return int
	 */
	private function getExecutedOn():int {
		$order = $this->getOrder();
		if ($order->has_stages) {
			$stagesProgress = $this->getStagesProgress();
			$orderStages = $order->stages->keyBy(OrderStage::FIELD_ID)->all();
			foreach ($stagesProgress as $stageProgress) {
				$stageId = $stageProgress[OrderStage::FIELD_ID];
				if ($stageId && !empty($orderStages[$stageId])) {
					$orderStages[$stageId]->progress = $stageProgress[OrderStage::FIELD_PROGRESS];
				}
			}
			return OrderStageManager::getOverallStagesProgress($orderStages);
		} else {
			return $this->getRequest()->request->getInt("isExecutedOn");
		}
	}

	/**
	 * Получение этапов из запроса
	 *
	 * @return array
	 */
	private function getStagesProgress(): array {
		$jsonString = $this->getRequest()->request->get("stages");
		$stages = json_decode($jsonString, true);
		if (!is_array($stages)) {
			return [];
		}
		return $stages;
	}

	/**
	 * Получить предыдущий отчет
	 *
	 * @return KworkReport|null
	 */
	private function getPreviousReport() {
		$order = $this->getOrder();
		$trackId = $this->getRequest()->request->getInt("track_id");
		return $order->reports
			->whereNotIn(KworkReport::FIELD_TRACK_ID, [$trackId])
			->sortByDesc(KworkReport::FIELD_ID)
			->first();
	}

	/**
	 * Выбросить исключение
	 *
	 * @param string $errorText
	 * @return array
	 */
	private function throwError(string $errorText): array {
		$exception = new JsonException();
		$exception->setData([
			"status" => "error",
			"response" => $errorText,
		]);
		throw $exception;
	}

	/**
	 * Сохраняет прогресс по этапам и устанавливает значение phases
	 *
	 * @param \Model\KworkReport $report
	 * @param array $phases Фазы которые сохраняем
	 *
	 * @throws \Throwable
	 */
	private function setPhases($report, $phases) {
		$order = $this->getOrder();
		if ($order->has_stages) {
			$report->phases = json_encode($phases);
		}
	}

	/**
	 * Обновить отчет
	 *
	 * @param KworkReport $report отчет
	 * @param int $executedOn процент выполнения
	 * @return NewReportHandlerController
	 */
	private function updateReport(KworkReport $report, int $executedOn):self {
		$mergedPhases = $this->mergePreviousPhasesWithEditedStagesProgress($report);
		$this->setPhases($report, $mergedPhases);
		$report->status = KworkReport::STATUS_SENT;
		$report->updated = time();
		$report->is_executed_on = $executedOn;
		$reportUpdateStatus = $report->save();

		if ($reportUpdateStatus) {
			$report->track->message = strip_tags($this->getMessage());
			$report->track->save();
		}
		return $this;
	}

	/**
	 * Объединение существующих сохраненных этапов отчета с новыми отредактированными
	 *
	 * @param \Model\KworkReport $report Редактируемый отчет
	 *
	 * @return array
	 */
	private function mergePreviousPhasesWithEditedStagesProgress(KworkReport $report) {
		$previousPhases = json_decode($report->phases, true);
		$editedStages = $this->getEditedStages();
		$mergedPhases = [];
		if (is_array($previousPhases)) {
			foreach ($previousPhases as $previousPhase) {
				foreach ($editedStages as $stage) {
					if ($previousPhase[OrderStage::FIELD_ID] == $stage->id) {
						$previousPhase[OrderStage::FIELD_PROGRESS] = $stage->progress;
					}
				}
				$mergedPhases[] = $previousPhase;
			}
		}
		$mergedPhasesIds = array_filter(array_column($mergedPhases, OrderStage::FIELD_ID));
		foreach ($editedStages as $stage) {
			if (!in_array($stage->id, $mergedPhasesIds)) {
				$mergedPhases[] = $stage->getAsPhase();
			}
		}

		return $mergedPhases;
	}

	/**
	 * Создать отчет для отправки
	 *
	 * @param int $isExecutedOn процент выполения
	 * @return KworkReport|null
	 */
	private function makeReportForSend(int $isExecutedOn) {
		$order = $this->getOrder();
		$trackId = TrackManager::create($order->OID, Type::WORKER_REPORT_NEW, $this->getMessage());
		/**
		 * @var KworkReport $report
		 */
		$report = $order->reports
			->where(KworkReport::FIELD_STATUS, KworkReport::STATUS_NEW)
			->first();

		if (is_null($report)) {
			$report = new KworkReport();
			$report->order_id = $order->OID;
			$report->created = time();
			$report->payer_notify = KworkReportManager::PAYER_NOT_NOTIFY;
			$report->type = KworkReport::TYPE_INDEFINITE;
		}
		$report->track_id = $trackId;
		$report->status = KworkReport::STATUS_SENT;
		$report->updated = time();
		$report->time_delivery = time();
		$report->is_executed_on = $isExecutedOn;
		$this->setPhases($report, $this->getEditedStagesAsPhases());
		if (!$report->save()) {
			return null;
		}

		return $report;
	}

	/**
	 * Получение измененных этапов как фаз отчета
	 *
	 * @return array
	 */
	private function getEditedStagesAsPhases():array {
		$phases = [];
		foreach ($this->getEditedStages() as $stage) {
			$phases[] = $stage->getAsPhase();
		}
		return $phases;
	}

	/**
	 * Получить отчет для редактирования
	 *
	 * @return KworkReport|null
	 */
	private function getReportForEdit() {
		$trackId = $this->getRequest()->request->getInt("track_id");
		if (!$trackId) {
			return null;
		}
		$order = $this->getOrder();
		/**
		 * @var KworkReport $report
		 */
		return KworkReport::where(KworkReport::FIELD_ORDER_ID, $order->OID)
			->where(KworkReport::FIELD_TRACK_ID, $trackId)
			->first();
	}

	/**
	 * Получение массива моделей этапов отредактированных данными из запроса
	 *
	 * @return \Model\OrderStages\OrderStage[]
	 */
	private function getEditedStages() {
		// Сохранение editedStages для того чтобы после save, тоже метод их отдавал
		if (is_null($this->editedStages)) {
			$stagesProgress = $this->getStagesProgress();
			$orderStages = $this->getOrder()->stages->keyBy(OrderStage::FIELD_ID)->all();
			$editedStages = [];
			foreach ($stagesProgress as $stageProgress) {
				$stageId = $stageProgress[OrderStage::FIELD_ID];
				$orderStage = $orderStages[$stageId];
				if ($orderStage instanceof OrderStage) {
					$orderStage->progress = $stageProgress[OrderStage::FIELD_PROGRESS];
					if ($orderStage->isDirty()) {
						$editedStages[$stageId] = $orderStage;
					}
				}
			}
			$this->editedStages = $editedStages;
		}

		return $this->editedStages;
	}

	/**
	 * Получение измененных этапов с прогрессом менее 100
	 *
	 * @return OrderStage[]
	 */
	private function getNotFullEditedStages() {
		return array_filter($this->getEditedStages(), function (OrderStage $stage) {
			return !$stage->isFullProgress();
		});
	}

	/**
	 * Получение измененных этапов с прогрессом 100
	 *
	 * @return OrderStage[]
	 */
	private function getFullEditedStages() {
		return array_filter($this->getEditedStages(), function (OrderStage $stage) {
			return $stage->isFullProgress();
		});
	}

	/**
	 * Продавец устанавливает прогресс этапов заказа
	 */
	private function saveStagesProgress() {
		$fullStages = $this->getFullEditedStages();
		$notFullStages = $this->getNotFullEditedStages();

		if ($notFullStages) {
			DB::transaction(function () use ($notFullStages) {
				foreach ($notFullStages as $stage) {
					$stage->save();
				}
			});
		}

		if ($fullStages) {
			\OrderManager::worker_inprogress_check($this->getOrderId(), null, array_keys($fullStages));
			\Pull\PushManager::sendNewOrderTrack($this->getOrder()->worker_id, $this->getOrderId(), Type::WORKER_INPROGRESS_CHECK);
		}

		\Pull\PushManager::sendOrderUpdated($this->getOrder()->worker_id, $this->getOrderId());
		\Pull\PushManager::sendOrderUpdated($this->getOrder()->USERID, $this->getOrderId());
	}


	/**
	 * Данные отчета
	 *
	 * @return array
	 */
	private function getReportData($report): array {
		return [
			[
				"order_id" => $report->order->OID,
				"kworkTitle" => $report->order->getPayerOrderName(),
				"is_executed_on" => $report->is_executed_on,
				"reportMessage" => $this->getMessage(),
				"phases" => json_decode($report->phases, true),
				"price" => $report->order->price,
				"currency" => $report->order->currency_id,
				"userRating" => $report->order->worker->cache_rating,
				"userRatingCount" => $report->order->worker->cache_rating_count,
				"cache_rating_count_en" => $report->order->worker->cache_rating_count_en,
				"lang" => $report->order->kwork->lang,
				"image" => $report->order->kwork->photo,
			],
		];
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	protected function processAction(): Response {
		try {
			$order = $this->getOrder();
			if (!$order->isWorker($this->getCurrentUserId())) {
				$this->throwError(Translations::t("Доступ разрешен только продавцу"));
			}

			$isExecutedOn = $this->getExecutedOn();
			if (!$isExecutedOn) {
				$this->throwError(Translations::t("Процент выполненной работы не может быть равен 0"));
			}
			if (mb_strlen($this->getMessage()) > 350) {
				$this->throwError(Translations::t("Максимальная длинна сообщения 350 символов"));
			}
			if (mb_strlen($this->getMessage()) < 2) {
				$this->throwError(Translations::t("Укажите комментарий"));
			}

			// защитимся от дублей и повторной отправки
			$lock = new DbLock(LockEnum::getWithId(LockEnum::SAVE_ORDER_REPORT, $this->getUserId()));

			$report = $this->getReportForEdit();

			if ($order->has_stages) {
				$stagesProgress = $this->getStagesProgress();
				$validator = new OrderStageProgressValidator($order->OID);
				if (!$validator->validate($stagesProgress, !is_null($report))) {
					throw new JsonValidationException($validator->errors());
				}
			} else {
				// Проверка процента работы только для заказов без этапов
				$lastReport = $this->getPreviousReport();
				if ($lastReport && $lastReport->is_executed_on >= $isExecutedOn) {
					$this->throwError(Translations::t("Процент выполненной работы не может быть меньше или таким-же в сравнении с последним отправленным отчетом") . " (" . $lastReport->is_executed_on . "%)");
				}
			}

			if (is_null($report)) {
				// Создаем новый отчет
				$report = $this->makeReportForSend($isExecutedOn);
				if (is_null($report)) {
					$this->throwError(Translations::t("Ошибка при добавлении отчета"));
				}

				// Для поэтапных заказов другая логика - отправляем с задержкой 5 минут
				if (!$order->has_stages) {
					$emailHandler = new NewOrderReport([
						"userEmail" => $report->order->payer->getUnencriptedEmail(),
						"reports" => $this->getReportData($report),
						"orderId" => $report->order->OID,
					]);
					$emailHandler->sendEmail();
				}

				$view = TrackViewFactory::getInstance()->getView($report->track);
				$trackAsHtml = $view->render();
				// Уведомление о созданном отчете
				TrackManager::sendNewTrackPush($order->OID, Type::WORKER_REPORT_NEW, TrackManager::STATUS_NEW);

				NotityManager::create(NotificationType::PAYER_NEW_ORDER_REPORT, $report->order->USERID, $report->order->OID);

				$this->saveStagesProgress();

				return new Response($trackAsHtml);
			} else {
				if ($report->canNotEdit()) {
					$this->throwError(Translations::t("Прошло более 10 минут с момента отправки отчета, поэтому изменить его невозможно"));
				}
				$this->updateReport($report, $isExecutedOn);
				$this->saveStagesProgress();

				return new JsonResponse([
					"status" => "success",
					"type" => "report_update",
					"trackId" => $report->track_id,
					"message" => $this->getMessage(),
				]);
			}
		} catch(JsonException $e) {
			return new JsonResponse($e->getData());
		}
	}
}