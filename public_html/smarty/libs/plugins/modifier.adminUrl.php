<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "adminurl"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_adminUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("adminurl");
	}
	return $url . $filePath;
}
