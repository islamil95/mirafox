<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "membersprofilepicurl"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnMembersProfilePicUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("membersprofilepicurl");
	}
	return $url . $filePath;
}
