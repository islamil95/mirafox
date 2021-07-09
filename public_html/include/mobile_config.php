<?php

use Mobile\Token;
use Pull\PullChannel;

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : dirname(dirname(__FILE__)));
define('APP_ROOT', dirname(DOCUMENT_ROOT));

require_once(DOCUMENT_ROOT . '/include/common_config/upper.php');

if (App::config("ban_ip.enable")) {
	BanManager::registerMobileRequest();
}

$actor = null;
$mobileApi = true;

$token = request('token');

if ($token && App::config('redis.enable')) {

	$tokenData = Token::getTokenData($token);

	if ($tokenData) {
		//для совместимости с getActor
		Session\SessionContainer::getSession()->set(UserManager::FIELD_USERID, $tokenData["userId"]);
		//для совместимости с UserManager::compareAuthHash
		Session\SessionContainer::getSession()->set(UserManager::SESSION_AUTH_HASH, $tokenData["authHash"]);

		$actor = getActor();

		if (!$actor) {
			Token::delete($token);
		} else {
			UserManager::setCurrentMobileToken($token);

			//продлеваем socket-канал пользователя
			if (Helper::isModuleChatEnable()) {
				PullChannel::getChannel($actor->id, true);
			}
		}
	}
}

require_once(DOCUMENT_ROOT . '/include/common_config/after_actor.php');
