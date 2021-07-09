<?php

namespace Exception;


class FactoryIllegalTypeException extends \Exception{
	public function __construct() {
		parent::__construct('illegal type passed to factory');
	}
}