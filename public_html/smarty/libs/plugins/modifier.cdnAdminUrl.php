<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "cdn.admin_url"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnAdminUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("cdn.admin_url");
	}
	return $url . $filePath;
}
