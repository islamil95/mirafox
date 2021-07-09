<?php

/**
 * вывод абсолютного пути к ресурсу с доменом из конфига "portfoliourl"
 *
 * @param $filePath string относительный путь к ресурсу
 * @return string абсолютный путь к ресурсу
 */
function smarty_modifier_cdnPortfolioUrl(string $filePath): string {
	static $url;
	if (!$url) {
		$url = App::config("cdn.portfolio_url");
	}
	return $url . $filePath;
}
