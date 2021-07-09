<?php


namespace Exception;

class RecipdonorInprogressException extends \Exception
{
	public function __construct() {
		parent::__construct("Нужно повторить запрос позже, recipdonor обрабатывает данные сайта.");
	}
}