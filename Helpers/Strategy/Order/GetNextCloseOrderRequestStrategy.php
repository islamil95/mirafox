<?php


namespace Strategy\Order;


use Model\Order;
use Model\Track;

/**
 * Вернет трек который является отменой запроса на отмену
 *
 * Class GetNextCloseOrderRequestStrategy
 * @package Strategy\Order
 */
class GetNextCloseOrderRequestStrategy extends AbstractOrderStrategy {

	private $key;

	/**
	 * GetNextCloseOrderRequestStrategy constructor.
	 *
	 * @param Order $order заказ
	 * @param int $key ключ в маассиве треков который соответствует треку запрос на отмену
	 */
	public function __construct(Order $order, $key) {
		parent::__construct($order);
		$this->key = $key;
	}

	/**
	 * @return Track|null
	 */
	public function get() {
		$returnRequestArray = [
			"worker_inprogress_cancel_delete",
			"worker_inprogress_cancel_reject",
			"payer_inprogress_cancel_reject",
			"payer_inprogress_cancel_delete",
		];

		$trackCount = $this->order->tracks->count();
		for ($i = ($this->key + 1); $i < $trackCount; $i++) {
			$track = $this->order->tracks->get($i);
			if (isset($track)) {
				if (in_array($track->type, $returnRequestArray)) {
					return $track;
				}
			}
		}

		return null;
	}
}