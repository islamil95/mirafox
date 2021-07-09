<?php

namespace Strategy\Track;

use Model\Order;

/**
 * Получить цены опций
 *
 * Class getOptionPricesStrategy
 * @package Strategy\Track
 */
class GetOptionPricesStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 *
	 * @param float|null $turnover Оборот между продавцом и покупателем
	 * @param bool $withPrice Добавить цену в результат
	 */
	public function get($turnover = null, $withPrice = false) {
		return [];
	}
}