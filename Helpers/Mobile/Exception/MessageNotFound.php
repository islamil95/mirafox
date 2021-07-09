<?php

namespace Mobile\Exception;

use Mobile\Constants;

class MessageNotFound extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Сообщение не найдено"), Constants::CODE_MESSAGE_NOT_FOUND);
	}
}