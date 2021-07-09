<?php

namespace Controllers\Track\Strategy;

use Core\Traits\AuthTrait;
use Model\Order;

/**
 * Стратегия получения полного заказа
 *
 * Class GetFullOrderStrategy
 * @package Controllers\Track\Strategy
 */
class GetFullOrderStrategy implements IGetOrderStrategy {

	use AuthTrait;

	private $orderId;

	public function __construct($orderId) {
		$this->orderId = $orderId;
	}

	/**
	 * @inheritdoc
	 */
	public function getOrder(): Order {
		/**
		 * @var $order Order
		 */
		$order = Order::with([
				"payer",
				"worker",
				"kwork",
				"data",
				"tracks",
				"review",
				"review.answer",
				"tips",
			])
			->findOrFail($this->orderId);

		// Пометка треков которые нужно скрывать
		$order->initHiddenConversation();

		return $order;
	}
}