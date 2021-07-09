<?php

use Core\Routing\UrlGeneratorSingleton;

function smarty_function_absolute_original_url($params) {
	$generator = UrlGeneratorSingleton::getInstance();
	$url = $generator->generate($params["route"], $params["params"] ?: []);
	$baseUrl = \App::config("originurl");
	return $baseUrl.$url;
}
