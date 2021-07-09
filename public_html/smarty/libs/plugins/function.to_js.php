<?php

function smarty_function_to_js($params) {
	$options = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
	if ($params["encodeUnicode"]) {
		$options = 0;
	}
	if (!empty($params["var"])) {
		$data = json_encode($params["var"], $options);
	} else {
		$data = 'null';
	}
	return "window." . $params["name"] . "=" . $data . ';';
}

?>