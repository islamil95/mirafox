<?php


namespace Track\View\Order\Cancel;

/**
 * Запрос на отмену заказа
 *
 * Class InProgressCancelRequestView
 * @package Track\View\Order\Cancel
 */
class InProgressCancelRequestView extends AbstractOrderCancelView {

	/**
	 * Причина отмены со стороны покупателя "иная"
	 * @return bool
	 */
	private function isPayerOtherType():bool {
		return $this->track->reason_type == "payer_other";
	}

	/**
	 * @inheritdoc
	 */
	protected function getCancelReason() {
		if ($this->isPayerOtherType()) {
			return nl2br(stripslashes($this->track->message)) . ".";
		}
		return parent::getCancelReason();
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/cancel/inprogress_cancel_request";
	}
}