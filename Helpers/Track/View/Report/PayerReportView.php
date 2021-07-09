<?php


namespace Track\View\Report;

use Track\View\AbstractView;

/**
 * Отчет для покупателя
 *
 * Class PayerReportView
 * @package Track\View\Report
 */
class PayerReportView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/report/for_payer";
	}


	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		return \Translations::t("Промежуточный отчет продавца");
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"phases" => json_decode($this->track->report->phases, true)
		];
	}
}