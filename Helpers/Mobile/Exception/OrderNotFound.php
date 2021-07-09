<?php

namespace Mobile\Exception;

use Mobile\Constants;

class OrderNotFound extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Заказ не найден"), Constants::CODE_ORDER_NOT_FOUND);
	}
}