<?php

function addAllowFrameDomain() {
	$allowUrls = ["/api/widget/get", "/setcookie?"];
	$isAllowFrameUrl = false;
	foreach ($allowUrls as $url) {
		if (strpos($_SERVER['REQUEST_URI'], $url) === 0) {
			$isAllowFrameUrl = true;
			break;
		}
	}
	if ($isAllowFrameUrl) {
		header("Content-Security-Policy: frame-ancestors *");
		header("X-Frame-Options: ALLOWALL");
		return true;
	}

	$allowHosts = ["http://webvisor.com", "http://awards.ratingruneta.ru"];
	header("Content-Security-Policy: frame-ancestors 'self' " . implode(' ', $allowHosts));
}

header("Access-Control-Allow-Origin: *");
addAllowFrameDomain();
header('Vary: Accept-Encoding, User-Agent');


setlocale(LC_TIME, 'ru_RU.UTF-8');
ini_set('session.gc_maxlifetime', 60 * 60 * 24);
ini_set('session.cookie_lifetime', 60 * 60 * 24);
ini_set('upload_max_filesize', '4M');


if (!function_exists('autoloader')) {

	function autoloader($className) {
		$directories = array("Helpers", 'lib', '');
		if (preg_match('!^[a-z_\\\0-9]+$!i', $className)) {
			$className = str_replace('\\', '/', $className);
			foreach ($directories as $directory) {
				if (empty($directory)) {
					$fileName = APP_ROOT . "/$className.php";
				} else {
					$fileName = APP_ROOT . "/$directory/$className.php";
				}
				if (file_exists($fileName)) {
					require_once $fileName;
					break;
				}
			}
		}
	}

	spl_autoload_register('autoloader');
}

require APP_ROOT . "/vendor/autoload.php";

// заглушка для техработ
if (App::config('technical_works.redirect')) {
	header("Location: /technical");
	exit();
}

{ // логирование ошибок

	function catchErrorReporting($errno, $errmsg, $filename, $linenum) {
		$errorInfo = compact('errno', 'errmsg', 'filename', 'linenum');
		$uri = $_SERVER['REQUEST_URI'];
		if (!in_array($errno, array(E_NOTICE, E_WARNING, E_STRICT))) {
			Log::daily("URL: {$uri}\n" . print_r($errorInfo, true) . Log::getStackCall(), 'error');
		}
	}

	set_error_handler("catchErrorReporting", E_ALL & ~E_NOTICE);

	function systemShutdownWork() {
		$e = error_get_last();
		if (is_array($e)) {
			$code = isset($e['type']) ? $e['type'] : 0;
			$msg = isset($e['message']) ? $e['message'] : '';
			$file = isset($e['file']) ? $e['file'] : '';
			$line = isset($e['line']) ? $e['line'] : '';
			if ($code > 0)
				catchErrorReporting($code, $msg, $file, $line, '');
		}
	}

	register_shutdown_function('systemShutdownWork');
}

// Eloquent
\Core\DB\Eloquent::boot();

require_once(App::config('basedir') . '/libraries/db/db.php');
require_once(App::config('basedir') . '/libraries/db/DataBasePDO.php');
require_once(App::config('basedir') . '/include/main.php');
require_once(DOCUMENT_ROOT . "/../vendor/autoload.php");

// PDO
{
	$conn = new DataBase();
	$conn->connect();
}

$uri = trim($_SERVER['REQUEST_URI'], '/');

if (Helper::needStrictFieldsis()) {
	// @todo: max_field_length есть только в базе, в config.
	// Но неоткуда не запршивается, как и fox_proxy_block.
	strict_fields($_REQUEST, App::config('max_field_length'));
	strict_fields($_POST, App::config('max_field_length'), getSoftStrictModeFields($_SERVER["REQUEST_URI"]));
	strict_fields($_GET, App::config('max_field_length'));
}
if (!Helper::isConsoleRequest()) {
	if (App::config('fox_proxy_block') == "1") {
		if ($_SERVER['HTTP_X_FORWARDED_FOR'] || $_SERVER['HTTP_X_FORWARDED'] || $_SERVER['HTTP_FORWARDED_FOR'] || $_SERVER['HTTP_VIA'] || in_array($_SERVER['REMOTE_PORT'], array(8080, 80, 6588, 8000, 3128, 553, 554)) || @fsockopen($_SERVER['REMOTE_ADDR'], 80, $errno, $errstr, 1)) {
			exit('Proxy detected.');
		}
	}
}


// Проверка кто исполняет файл
{
	if ((Helper::isConsoleRequest() && rand(0, 100) == 0) || rand(0, 10000) == 0) {
		Helper::checkProcessOwner();
	}
}

// Роутинг
\Core\Routing\Router::boot();
