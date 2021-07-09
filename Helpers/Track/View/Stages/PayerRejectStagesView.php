<?php


namespace Track\View\Stages;


class PayerRejectStagesView extends AbstractStagesView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/payer_reject_stages";
	}

}