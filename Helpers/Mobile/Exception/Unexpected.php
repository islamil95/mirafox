<?php

namespace Mobile\Exception;

use Mobile\Constants;

class Unexpected extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Неожиданная ошибка"), Constants::CODE_INTERNAL_ERROR);
	}
}