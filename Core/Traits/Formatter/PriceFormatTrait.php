<?php

namespace Core\Traits\Formatter;

trait PriceFormatTrait {

	protected function formatPrice($price):string {
		return sprintf("%.2f", $price);
	}
}