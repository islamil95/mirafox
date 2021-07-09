<?php


namespace Exception;

class ChecktrustInprogressException extends \Exception
{
	public function __construct() {
		parent::__construct("Нужно повторить запрос позже, checktrust обрабатывает данные сайта.");
	}
}