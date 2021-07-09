<?php


namespace Track\View\Order\Cancel;


use Track\Type;

/**
 * Заказа отменен
 *
 * Class OrderCancelView
 * @package Track\View\Order\Cancel
 */
class OrderCancelView extends AbstractOrderCancelView {

	/**
	 * @inheritdoc
	 */
	protected function getCancelReason() {
		return null;
	}

	/**
	 * @inheritdoc
	 */
	protected function getComment(): string {
		return "";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/system";
	}

	/**
	 * Покупатель отказался от отмены заказа
	 *
	 * @return bool
	 */
	private function isPayerCancelConfirmAndDisagree():bool {
		return Type::PAYER_INPROGRESS_CANCEL_CONFIRM == $this->track->type &&
			\TrackManager::REPLY_TYPE_DISAGREE == $this->track->reply_type;
	}

	/**
	 * Получить дополнительное текстовое сообщение
	 *
	 * @return string дополнительное сообщение
	 */
	private function getAdditionalText():string {
		if ($this->track->order->isPayer($this->getUserId())) {
			return \Translations::t('Вы не согласились с причиной, рейтинг продавца понижен.');
		}
		return \Translations::t('Покупатель не согласился с причиной.');
	}

	/**
	 * @inheritdoc
	 */
	protected function getText() {
		$text = parent::getText();
		$isNeedReplaceCancelByStop = $this->isNeedReplaceCancelByStop();
		if ($isNeedReplaceCancelByStop) {
			$viewData = $this->getViewData($this->getType());
			$accessKey = Type::getAccessKeysWithSuffix($this->getAccessKey(), $isNeedReplaceCancelByStop);
			if (!empty($viewData[$accessKey])) {
				$text = $viewData[$accessKey];
			}
		}

		if ($this->isPayerCancelConfirmAndDisagree()) {
			return $text . ". " . $this->getAdditionalText();
		}
		return $text;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		$viewData = $this->getViewData($this->getType());
		$accessKey = "title";
		$isNeedReplaceCancelByStop = $this->isNeedReplaceCancelByStop();
		if ($isNeedReplaceCancelByStop) {
			$key = Type::getAccessKeysWithSuffix($accessKey, $isNeedReplaceCancelByStop);
			if (!empty($viewData[$key])) {
				return $viewData[$key];
			}
		}

		return $viewData[$accessKey] ?: "";
	}
}