<?php

namespace Mobile\Exception;

use Mobile\Constants;

/**
 * Class DialogNotFound
 * @package Mobile\Exception
 */
class DialogNotFound extends \Exception\JsonException
{
	public function __construct()
	{
		parent::__construct(\Translations::t("Диалог не найден."), Constants::CODE_DIALOG_NOT_FOUND);
	}
}