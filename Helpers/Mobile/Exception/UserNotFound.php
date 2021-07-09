<?php

namespace Mobile\Exception;

use Mobile\Constants;

class UserNotFound extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Пользователь не найден"), Constants::CODE_USER_NOT_FOUND);
	}
}