<?php


namespace Track\View\Report;

use Model\OrderStages\OrderStage;
use Track\View\AbstractView;


/**
 * Вид отчета для продавца
 *
 * Class WorkerReportView
 * @package Track\View\Report
 */
class WorkerReportView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/report/for_worker";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		if ($this->track->report && $this->track->report->isSent()) {
			return \Translations::t("Промежуточный отчет отправлен");
		}
		if ($this->getPhases()) {
			return \Translations::tn('Промежуточный отчет по этапам', count($this->getPhases()));
		}

		return \Translations::t('Промежуточный отчет');
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"phases" => $this->getPhases(),
			"phasesCanEdit" => $this->phasesCanEdit(),
		];
	}

	/**
	 * Получение этапов отчета
	 *
	 * @return array|mixed
	 */
	protected function getPhases() {
		if ($this->track->report && $this->track->report->isNew()) {
			$phases = [];
			if ($this->track->order->stages && $this->track->order->stages->count()) {
				foreach ($this->track->order->stages as $stage) {
					if ($stage->isReserved() && !$stage->isFullProgress()) {
						$phases[] = $stage->getAsPhase();
					}
				}
			}
		} else {
			$phasesTemp = json_decode($this->track->report->phases, true);
			$phases = $phasesTemp ? $phasesTemp : [];
		}
		return $phases;
	}

	/**
	 * Может ли продавец редактировать прогресс этапов в отчете
	 *
	 * @return bool
	 */
	protected function phasesCanEdit() {
		if (!$this->track->report) {
			return false;
		}

		if ($this->track->report->isNew()) {
			return true;
		}

		if ($this->track->report->canNotEdit()) {
			return false;
		}

		$phases = json_decode($this->track->report->phases, true);
		if (is_array($phases)) {
			foreach ($phases as $phase) {
				if ($phase[OrderStage::FIELD_PROGRESS] == OrderStage::PROGRESS_FULL) {
					return false;
				}
			}
		}

		return true;
	}
}