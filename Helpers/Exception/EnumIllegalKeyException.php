<?php

namespace Exception;


class EnumIllegalKeyException extends \Exception{
	public function __construct() {
		parent::__construct('illegal key passed to enum');
	}
}