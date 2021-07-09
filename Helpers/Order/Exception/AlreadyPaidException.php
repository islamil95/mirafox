<?php

namespace Order\Exception;

/**
 * Заказ уже оплачен
 */
class AlreadyPaidException extends \RuntimeException {

	/**
	 * @var int Идентификатор заказа который оплачен
	 */
	protected $orderId;

	/**
	 * AlreadyPaidException constructor.
	 *
	 * @param int $orderId Идентификатор заказа который уже оплачен (нужен для редиректа на заказ)
	 * @param string $message
	 * @param int $code
	 * @param \Throwable|null $previous
	 */
	public function __construct(int $orderId, string $message = "", int $code = 0, \Throwable $previous = null) {
		$this->orderId = $orderId;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return int
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @param int $orderId
	 */
	public function setOrderId(int $orderId) {
		$this->orderId = $orderId;
	}


}