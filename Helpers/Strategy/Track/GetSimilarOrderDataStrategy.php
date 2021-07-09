<?php


namespace Strategy\Track;

/**
 * Получить похожие данные заказа
 *
 * Class GetSimilarOrderDataStrategy
 * @package Strategy\Track
 */
class GetSimilarOrderDataStrategy extends AbstractTrackStrategy {

	/**
	 * Проверка
	 * @return bool
	 */
	private function isValidationFail():bool {
		return $this->order->isNotInProgress() ||
			$this->order->isWorker($this->getUserId()) ||
			$this->order->data_provided != 0;
	}

	/**
	 * @inheritdoc
	 */
	public function get() {
		if ($this->isValidationFail()) {
			return [];
		}

		$useOrderInfo = \OrderManager::getKworkSimilarData([$this->order->kwork->category], 3);
		$similarDataKworks = \KworkManager::getField(array_values($useOrderInfo), "gtitle");
		$similarDataOrders = \OrderManager::getField(array_keys($useOrderInfo), "time_added");
		$ordersProvidedData = [];
		foreach ($useOrderInfo as $orderId => $pid) {
			$ordersProvidedData[$orderId] = \OrderManager::getOrderProvidedData($orderId);
		}

		return [
			"similarOrderInfo" => $useOrderInfo,
			"similarDataKworks" => $similarDataKworks,
			"similarDataOrders" => $similarDataOrders,
			"ordersProvidedData" => $ordersProvidedData,
		];
	}
}