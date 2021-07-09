<?php

namespace Track\View\Order\Cancel;


use Model\OrderStages\OrderStage;
use Model\Track;
use Track\Type;
use Track\View\AbstractView;

/**
 * Абстрактный класс для отображения случаем отказа или отмены заказа
 *
 * Class AbstractOrderCancelView
 * @package Track\View\Order\Cancel
 */
abstract class AbstractOrderCancelView extends AbstractView {

	/**
	 * @var bool отмена со стороны продавца
	 */
	private $isWorkersReason;

	/**
	 * @inheritdoc
	 */
	public function __construct(Track $track) {
		parent::__construct($track);
		$workerReasons = [
			"worker_bad_payer_requirements",
			"worker_payer_ordered_by_mistake",
			"worker_no_payer_requirements",
		];
		$this->isWorkersReason = in_array($this->track->reason_type, $workerReasons);
	}

	/**
	 * Получить отмену со стороны продавца
	 *
	 * @return bool
	 */
	protected function getIsWorkersReason():bool {
		return $this->isWorkersReason;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		$viewData = $this->getViewData($this->getType());
		$accessKey = $this->getAccessKey();
		if ($this->isNeedReplaceCancelByStop()) {
			$accessKeyHasPaidStages = Type::getAccessKeysWithSuffix($accessKey, $this->isNeedReplaceCancelByStop());
			if (!empty($viewData[$accessKeyHasPaidStages])) {
				return $viewData[$accessKeyHasPaidStages];
			}
		}
		return $viewData[$accessKey];
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"reason" => $this->getCancelReason(),
			"comment" => $this->getComment(),
			"isWorkersReason" => $this->getIsWorkersReason(),
			"showActionButtons" => $this->isShowActionButtons(),
		];
	}

	/**
	 * Показывать кнопки действий?
	 *
	 * @return bool
	 */
	private function isShowActionButtons():bool {
		return in_array($this->track->type, [Type::WORKER_INPROGRESS_CANCEL_REQUEST, Type::PAYER_INPROGRESS_CANCEL_REQUEST]) &&
			$this->track->isNew() &&
			($this->track->order->isInProgress() || $this->track->order->isUnpaid());
	}

	/**
	 * Получить коментарий
	 *
	 * @return null|string коментарий
	 */
	protected function getComment() {
		if (empty($this->track->message)) {
			return null;
		}
		return nl2br(stripslashes($this->track->message));
	}

	/**
	 * Получить причины отмены
	 *
	 * @return null|string причина отмены
	 */
	protected function getCancelReason() {
		$cancelReason = $this->track->getCancelReason();
		if (empty($cancelReason["name"])) {
			return null;
		}
		return mb_strtolower($cancelReason["name"]);
	}

	/**
	 * Нужно ли заменять в текстах "отменен" на "остановлен"
	 *
	 * @return bool
	 */
	protected function isNeedReplaceCancelByStop():bool {
		if (!$this->track->order->hasPaidStages()) {
			// Если нет оплаченных продавцу этапов то не нужно
			return false;
		}

		$earliestPaidStageDate = $this->getEarliestPaidStageDate();
		if (!$earliestPaidStageDate) {
			return false;
		}

		// Если дата создания трека позже даты самой ранней оплаты продавцу то нужно
		return strtotime($earliestPaidStageDate) <= strtotime($this->track->date_create);
	}

	/**
	 * Получение самой ранней даты оплаты этапа продавцу
	 * @return string|null
	 */
	protected function getEarliestPaidStageDate() {
		$paidStages = $this->track->order->getPaidStages();
		$earliestStage = $paidStages->sortBy(OrderStage::FIELD_PAID_DATE)->first();
		if ($earliestStage instanceof OrderStage) {
			return $earliestStage->paid_date;
		}
		return null;
	}
}