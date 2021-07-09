<?php


namespace Core\Traits;


trait SingletonTrait {

	/**
	 * @var static $instance
	 */
	private static $instance = null;

	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	private function __construct() {
	}
}