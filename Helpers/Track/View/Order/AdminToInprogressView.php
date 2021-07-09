<?php


namespace Track\View\Order;


use Track\View\AbstractView;

/**
 * Администратор возвращает завершенный заказ в работу
 */
class AdminToInprogressView extends AbstractView {

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		return [
			"showStages" => $this->track->trackStages->count() > 0,
		];
	}

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/order/admin_to_inprogress";
	}
}