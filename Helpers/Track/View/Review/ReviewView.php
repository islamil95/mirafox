<?php

namespace Track\View\Review;

use Track\View\AbstractView;

/**
 * Отзыв
 *
 * Class ReviewView
 * @package Track\View\Review
 */
class ReviewView extends AbstractView {

	/**
	 * Параметры, передаваемые в шаблон для рендера
	 * @var array
	 */
	private $parameters = [];

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return $this->parameters;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/review/review";
	}

	/**
	 * Установить параметры, передаваемые в шаблон для рендера
	 * @param $parameters
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
}