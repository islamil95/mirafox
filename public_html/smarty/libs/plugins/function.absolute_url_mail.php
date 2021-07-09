<?php

use Core\Routing\UrlGeneratorSingleton;

function smarty_function_absolute_url_mail($params) {
	$route = $params["route"];
	$parameters = $params["params"] ?? [];
	$lang = $params["lang"] ?? Translations::getLang();

	$generator = UrlGeneratorSingleton::getInstance();
	$url = $generator->generate($route,$parameters);
	$baseUrl = \App::config("baseurl");
	if($lang == \Translations::EN_LANG && \Translations::isDefaultLang()) {
		$endomain = \App::config("endomain");
		$rudomain = \App::config("rudomain");
		$baseUrl = str_replace($rudomain, $endomain, $baseUrl);
	}
	return $baseUrl . $url;
}
