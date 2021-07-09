<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "baseurl"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_baseUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("baseurl");
	}
	return $url . $filePath;
}
