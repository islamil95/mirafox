<?php


namespace Track\View\Stages;


class CronCheckApproveStagesView extends AbstractStagesView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/cron_check_approve_stages";
	}

}