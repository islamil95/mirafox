<?php


namespace Track\View\Order;


use Track\View\AbstractView;

/**
 * Покупатель принял работу
 *
 * Class PayerDoneView
 * @package Track\View\Order
 */
class PayerDoneView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/payer_order_done";
	}
}