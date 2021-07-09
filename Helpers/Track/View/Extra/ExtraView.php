<?php

namespace Track\View\Extra;

use Track\View\AbstractView;

/**
 * Предложение доп опций
 *
 * Class ExtraView
 * @package Track\View\Extra
 */
class ExtraView extends AbstractView {

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
		return "track/view/extra/extra";
	}
}