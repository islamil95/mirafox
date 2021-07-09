<?php

namespace Strategy\Track;

use Core\Traits\AuthTrait;
use Core\Traits\ConfigurationTrait;
use Model\Order;

/**
 * Абстрактная стратегия для трека
 *
 * Class AbstractTrackStrategy
 * @package Strategy\Track
 */
abstract class AbstractTrackStrategy implements TrackStrategyInterface {

	use AuthTrait, ConfigurationTrait;

	/**
	 * @var Order $order
	 */
	protected $order;

	public function __construct(Order $order) {
		$this->order = $order;
	}
}