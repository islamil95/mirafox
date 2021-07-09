<?php

namespace Mobile\Exception;

use Mobile\Constants;

class IncorrectEmail extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Адрес электронной почты указан некорректно."), Constants::CODE_INCORRECT_EMAIL);
	}
}