<?php


namespace Track\View\Order\Cancel;


/**
 * Class BaseCancelView
 * @package Track\View\Order\Cancel
 */
class BaseCancelView extends AbstractOrderCancelView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/system";
	}

	/**
	 * @inheritdoc
	 */
	protected function getText() {
		return "";
	}
}