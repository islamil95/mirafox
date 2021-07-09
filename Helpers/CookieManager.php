<?php

class CookieManager {
	public static function set($name, $value, $time = false, $httpOnly = true, $path = '/') {
		if (Helper::isConsoleRequest()) {
			return false;
		}
		if ($time === false) {
			$time = 60 * 60 * 24 * 365 * 10;
		}

		setcookie($name, $value, time() + $time, $path, null, App::isSSL(), $httpOnly);
	}

	public static function get($name) {
		return array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : false;
	}

	public static function clear($name) {
		setcookie($name, "", time() - 3600, "/", null, App::isSSL(), true);
	}
}