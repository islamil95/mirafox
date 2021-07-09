<?php


namespace Track\View\Stages;

use Track\View\AbstractView;

abstract class AbstractStagesView extends AbstractView {

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		return [
			"hasMultipleStages" => $this->track->trackStages->count() > 1,
			"isPayerSee" => $this->track->order->isPayer($this->getUserId()),
			"firstStage" => $this->getFirstStage(),
		];
	}

	/**
	 * Получение первого этапа
	 *
	 * @return \Model\OrderStages\OrderStage|null
	 */
	private function getFirstStage() {
		if ($this->track->trackStages && $this->track->trackStages->first()) {
			return $this->track->trackStages->first()->stage;
		}

		return null;
	}

}