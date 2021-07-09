<?php


namespace Track\View\Order\Cancel;

/**
 * Заказа в процессе отмены
 *
 * Class InProgressCancelView
 * @package Track\View\Order\Cancel
 */
class InProgressCancelView extends AbstractOrderCancelView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/cancel/inprogress_cancel";
	}
}