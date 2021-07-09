<?php


namespace Track\View\Stages;


use Order\Stages\OrderStageManager;
use Track\View\AbstractView;

class WorkerStageUnpaidView extends AbstractView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/worker_stage_unpaid";
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		return [
			"showActualStages" => $this->isLastTrack(),
			"cancelDays" => OrderStageManager::getUnpaidCancelDays(),
			"cancelDate" => $this->getCancelDate(),
		];
	}

	/**
	 * @return string
	 */
	protected function getTitle() {
		return \Translations::t("Ожидается оплата этапа");
	}

	protected function getCancelDate() {
		$time = strtotime($this->track->date_create);
		$time += OrderStageManager::getUnpaidCancelDays() * \Helper::ONE_DAY;
		$dateString = \Helper::now($time);
		return \Helper::dateFormat($dateString, "%e %B %Y %H:%M");
	}

	/**
	 * Является ли последним важным треком
	 *
	 * @return bool
	 */
	private function isLastTrack() {
		return $this->track->order->last_track_id == $this->track->MID;
	}
}