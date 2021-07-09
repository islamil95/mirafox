<?php
/**
 * @param $queryString
 * @param $add
 * @param $remove
 *
 * @return string
 */
function smarty_modifier_build_query($queryString, $add = false, $remove = false) {
	if ($queryString == false) {
		$queryString = $_SERVER['QUERY_STRING'];
	}

	return Helper::buildQuery($queryString, $add, $remove);
}