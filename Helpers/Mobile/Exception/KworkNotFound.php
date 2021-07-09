<?php

namespace Mobile\Exception;

use Mobile\Constants;

class KworkNotFound extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Кворк не найден"), Constants::CODE_KWORK_NOT_FOUND);
	}
}