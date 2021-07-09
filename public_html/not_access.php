<?php
include($_SERVER["DOCUMENT_ROOT"] . "/include/config.php");

//Получить IP пользователя
$userIp = BanManager::getIp();

if (BanManager::isBanNow($userIp)) {
	//Если IP сейчас забаннен - покажем страницу not_access
	header("HTTP/1.0 403 Forbidden");
	$pagetitle = Translations::getLang() == Translations::EN_LANG ? 'Access denied' : 'Доступ заблокирован';
	$smarty->assign("pagetitle", $pagetitle)
		->assign("ip", $userIp)
		->display("not_access.tpl");
} else {
	//Если IP не забаннен - редирект на главную
	$baseUrl = \App::config('baseurl');
	Controllers\PageController::redirect($baseUrl);
}

exit;

