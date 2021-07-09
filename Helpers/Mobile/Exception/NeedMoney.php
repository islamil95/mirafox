<?php

namespace Mobile\Exception;

use Mobile\Constants;

class NeedMoney extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Недостаточно средств на счете"), Constants::CODE_NEED_MORE_MONEY);
	}
}