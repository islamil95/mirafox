<?php


namespace Track\View\Stages;


class PayerApproveStagesView extends AbstractStagesView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/payer_approve_stages";
	}

}