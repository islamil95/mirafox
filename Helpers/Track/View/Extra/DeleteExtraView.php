<?php


namespace Track\View\Extra;


use Track\View\AbstractView;

/**
 * Отказ от доп опций
 *
 * Class DeleteExtraView
 * @package Track\View\Extra
 */
class DeleteExtraView extends AbstractView {

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
		return "track/view/extra/delete_extra";
	}
}