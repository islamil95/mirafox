<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "imageurl"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnImageUrl(string $filePath): string {
	return Helper::cdnImageUrl($filePath);
}
