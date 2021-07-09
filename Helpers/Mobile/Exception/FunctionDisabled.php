<?php

namespace Mobile\Exception;

use Mobile\Constants;

class FunctionDisabled extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Функция системы отключена"), Constants::CODE_FUNCTION_DISABLED);
	}
}