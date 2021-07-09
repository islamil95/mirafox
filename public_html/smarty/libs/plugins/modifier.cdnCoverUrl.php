<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "coverurl"
 * если путь к ресурсу содержит "/default.jpg" в самом конце, то путь меняется на CDN /files/cover/default.jpg
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnCoverUrl(string $filePath): string {
	static $urlLocal, $urlCdn;
	if (!$urlLocal) {
		$urlLocal = App::config("coverurl");
	}
	return $urlLocal . $filePath;
}
