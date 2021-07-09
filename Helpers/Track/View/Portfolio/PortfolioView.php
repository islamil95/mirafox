<?php

namespace Track\View\Portfolio;

use Track\View\AbstractView;

/**
 * Портфолио
 *
 * Class PortfolioView
 * @package Track\View\Portfolio
 */
class PortfolioView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {

		return [
			"portfolioItem" => $this->track->order->portfolio,
			"canSendPortfolio" => \PortfolioManager::canSendPortfolio(
				$this->track->order->portfolio_type,
				$this->track->order->kwork->getPortfolioType(),
				$this->track->order->worker_id,
				$this->track->order->status),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/portfolio/portfolio";
	}
}