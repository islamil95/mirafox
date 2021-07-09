<?php


namespace Track\View\Order\Cancel;

use Translations;

/**
 * Подтвержденная отмена заказа покупателем
 *
 * Class PayerInProgressCancelConfirmView
 * @package Track\View\Order\Cancel
 */
class PayerInProgressCancelConfirmView extends OrderCancelView {

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		if ($this->track->reason_type == \TrackManager::REASON_TYPE_WORKER_DISAGREE_STAGES) {
			$isStopped = $this->isNeedReplaceCancelByStop();
			if ($this->track->order->isPayer($this->getUserId())) {
				return $isStopped ?
					Translations::t("Продавец остановил заказ") :
					Translations::t("Продавец отменил заказ");
			} elseif ($this->track->order->isWorker($this->getUserId())) {
				return $isStopped ?
					Translations::t("Вы остановили заказ") :
					Translations::t("Вы отменили заказ");
			}
		}
		return parent::getTitle();
	}

	/**
	 * @inheritdoc
	 */
	protected function getText() {
		if ($this->track->reason_type == \TrackManager::REASON_TYPE_WORKER_DISAGREE_STAGES) {
			$isStopped = $this->isNeedReplaceCancelByStop();
			if ($this->track->order->isPayer($this->getUserId())) {
				return $isStopped ?
					Translations::t("Продавец остановил заказ по причине несогласия с этапами") :
					Translations::t("Продавец отменил заказ по причине несогласия с этапами");
			} elseif ($this->track->order->isWorker($this->getUserId())) {
				return $isStopped ?
					Translations::t("Вы остановили заказ по причине несогласия с этапами") :
					Translations::t("Вы отменили заказ по причине несогласия с этапами");
			}
		}
		return parent::getText();
	}
}