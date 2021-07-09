<?php

namespace Mobile\Exception;

use Mobile\Constants;

class IncorrectCaptcha extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Подтвердите что вы не робот"), Constants::CODE_INCORRECT_CAPTCHA);
	}
}