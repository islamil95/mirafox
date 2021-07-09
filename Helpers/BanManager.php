<?php

class BanManager {

	public static function getIp() {
		$return = $_SERVER['REMOTE_ADDR'];
		if (!empty($return) && !Helper::isValidIp($return)) {
			exit; // никогда, ни в коем случае не убирать!
		}
		return $return;
	}

}
