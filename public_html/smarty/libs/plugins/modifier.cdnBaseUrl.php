<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "cdn.base_url"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnBaseUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("cdn.base_url");
	}
	return $url . $filePath;
}
