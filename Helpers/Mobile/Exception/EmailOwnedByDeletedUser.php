<?php

namespace Mobile\Exception;

use Mobile\Constants;

class EmailOwnedByDeletedUser extends \Exception\JsonException
{
	public function __construct() {
		parent::__construct(
			\Translations::t("Извините, данный адрес почты занесен в стоп-список. Регистрация с ним невозможна."),
			Constants::CODE_EMAIL_OWNED_BY_DELETED_USER);
	}
}