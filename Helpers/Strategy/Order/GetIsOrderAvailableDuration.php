<?php


namespace Strategy\Order;
use \Helper;
use \KworkReportManager;

/**
 * Проверка на длительность выполнения заказа
 *
 * Class GetIsOrderAvailableDuration
 * @package Strategy\Order
 */
class GetIsOrderAvailableDuration extends AbstractOrderStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		$deadline = $this->order->deadline;
		if ($deadline > 0) {
			return (($deadline - time()) / Helper::ONE_HOUR) > KworkReportManager::MIN_ORDER_DURATION;
		}
		return ($this->order->duration / Helper::ONE_HOUR) > KworkReportManager::MIN_ORDER_DURATION;
	}
}