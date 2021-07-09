<?php

namespace Controllers\Track\Strategy;


use Model\Order;

/**
 * Стратегия получения заказа
 *
 * Interface GetOrderStrategy
 * @package Controllers\Track\Strategy
 */
interface IGetOrderStrategy {

	/**
	 * Получить заказ
	 *
	 * @return Order
	 */
	public function getOrder(): Order;
}