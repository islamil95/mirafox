<?php

namespace Track\View\Arbitrage;

use Track\View\AbstractView;

/**
 * Арбитраж
 *
 * Class ArbitrageView
 * @package Track\View\Arbitrage
 */
class ArbitrageView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"article" => $this->track->article(),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/arbitrage/arbitrage";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		$viewData = $this->getViewData($this->getType());
		return $viewData[$this->getAccessKey()];
	}
}