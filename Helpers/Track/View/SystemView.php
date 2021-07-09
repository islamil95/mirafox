<?php


namespace Track\View;

/**
 * Базовый трек для разных стандартных событий
 *
 * Class SystemView
 * @package Track\View
 */
class SystemView extends AbstractView {

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
		return "track/view/system";
	}
}