<?php


namespace Track\View\Order\Cancel;


/**
 * Представление для покупателя автоотмены заказа при невзятии в работу
 */
class PayerCronRestartedInprogressCancelView extends OrderCancelView {

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/cancel/payer_cron_restarted_inprogress_cancel";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		return \Translations::t("Продавец не взял заказ в работу");
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		$projectSourceTypes = [
			\OrderManager::SOURCE_WANT,
			\OrderManager::SOURCE_WANT_PRIVATE
		];
		return [
			"isNeedProjectLink" => in_array($this->track->order->source_type, $projectSourceTypes)
		];
	}
}