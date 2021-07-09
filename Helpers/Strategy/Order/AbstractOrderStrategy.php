<?php


namespace Strategy\Order;


use Model\Order;

/**
 * Абстрактный класс для стратегий на заказами
 *
 * Class AbstractOrderStrategy
 * @package Strategy\Order
 */
abstract class AbstractOrderStrategy {

	/**
	 * @var Order
	 */
	protected $order;

	/**
	 * AbstractOrderStrategy constructor.
	 * @param Order $order заказ
	 */
	public function __construct(Order $order) {
		$this->order = $order;
	}

	/**
	 * Получить значение стратегии
	 *
	 * @return mixed
	 */
	abstract public function get();
}