<?php

use Core\Routing\UrlGeneratorSingleton;

function smarty_function_route($params) {
	$url = UrlGeneratorSingleton::getInstance();
	return $url->generate($params['route'], $params['params'] ?: []);
}
