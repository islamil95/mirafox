<?php


namespace Exception;

class ChecktrustBalanceException extends \Exception
{
	public function __construct() {
		parent::__construct("Недостаточно денег на балансе checktrust.ru");
	}
}