<?php


namespace Strategy\Track;


use Model\Order;

/**
 * Можно ли написать продавцу
 * @TODO: сделано так, т.к. идет запрос в базу, это все нужно переписать на объекты
 *
 * Class CanWriteToSellerStrategy
 * @package Strategy\Track
 */
class CanWriteToSellerStrategy extends AbstractTrackStrategy {

	private static $objectPool = [];

	private $atFirstTime = true;
	private $strategyResult;

	/**
	 * Получение стратегии
	 *
	 * @param Order $order
	 * @return CanWriteToSellerStrategy
	 */
	public static function getInstance(Order $order) {
		if (! isset(static::$objectPool[$order->OID])) {
			static::$objectPool[$order->OID] = new CanWriteToSellerStrategy($order);
		}
		return static::$objectPool[$order->OID];
	}

	/**
	 * @return bool
	 */
	public function get() {

		if ($this->atFirstTime) {
			$this->strategyResult = (bool) \TrackManager::currentPayerCanWriteToWorker($this->order->status, $this->order->USERID, $this->order->worker_id);
			$this->atFirstTime = false;
		}

		return $this->strategyResult;
	}
}