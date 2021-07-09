<?php


namespace Exception;

class RecipdonorCompletedException extends \Exception
{
	/**
	 * RecipdonorCompletedException constructor.
	 *
	 * @param string $body Ответ api
	 */
	public function __construct($body) {
		parent::__construct("Recipdonor отказал в проверке сайта, ответ: $body");
	}
}