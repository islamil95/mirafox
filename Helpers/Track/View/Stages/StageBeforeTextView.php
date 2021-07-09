<?php


namespace Track\View\Stages;


use Track\View\AbstractView;

class StageBeforeTextView extends AbstractView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/base_stage_before_text";
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		return [];
	}

}