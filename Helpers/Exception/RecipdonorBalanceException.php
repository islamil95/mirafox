<?php


namespace Exception;

class RecipdonorBalanceException extends \Exception
{
	public function __construct() {
		parent::__construct("Недостаточно денег на балансе recipdonor.com");
	}
}