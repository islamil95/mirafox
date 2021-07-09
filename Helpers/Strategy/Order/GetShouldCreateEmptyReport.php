<?php


namespace Strategy\Order;

use Model\KworkReport;

/**
 * Проверяет заказ на необходимость создания нового отчета
 *
 * Class GetShouldCreateEmptyReport
 * @package Strategy\Order
 */
class GetShouldCreateEmptyReport extends AbstractOrderStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$reportNotificationEnable = !$this->order->payer->data->disable_report_notification;
		$pausedOrNewReportsCount = $this->order->reports->sum(function ($report) {
			/**
			 * @var KworkReport $report
			 */
			return ($report->isNew() || $report->isPause()) ? 1 : 0;
		});
		return $reportNotificationEnable && $pausedOrNewReportsCount == 0;
	}
}