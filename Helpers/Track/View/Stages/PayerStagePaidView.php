<?php

namespace Track\View\Stages;

class PayerStagePaidView extends AbstractStagesView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/payer_stage_paid";
	}

}