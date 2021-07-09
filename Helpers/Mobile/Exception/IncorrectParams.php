<?php

namespace Mobile\Exception;

use Mobile\Constants;

class IncorrectParams extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Некорректные значения параметров"), Constants::CODE_INCORRECT_PARAMS_VALUES);
	}
}