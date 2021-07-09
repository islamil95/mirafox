<?php

namespace Core\Traits\Price;

trait TotalAmountTrait {

	protected function getTotalAmount($price, $count = 1) {
		return \Helper::moneyRound($price * $count);
	}
}