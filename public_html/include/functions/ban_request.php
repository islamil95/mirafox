<?php

function __is_valid_ip($ip) {
	return preg_match('!^([0-9]{1,3}\.){3}[0-9]{1,3}$!', $ip);
}

function __ban_curr_ip($time = 3600) {
	global $conn;
	$ip = $_SERVER['REMOTE_ADDR'];

	if (__is_valid_ip($ip)) {
		$ips = explode('.', $ip);

		$ips = implode('.', array_slice($ips, 0, 3));
		$sql = 'insert into bans_ips set ip="' . mres($ips) . '", time="' . date('Y-m-d H:i:s', time() + $time) . '", at_time=1, request_count=1';
		$conn->execute($sql);
	}
}

$list = array(
	'--', '@'
);
$list = array_map('preg_quote', $list);
$request = $_SERVER['REQUEST_URI'];
if (preg_match('!' . implode('|', $list) . '!', $request)) {
    
	$is_ok_request = false;
	# exclusion
	# ....
	if (!$is_ok_request) {
		__ban_curr_ip();
		exit;
	}
}