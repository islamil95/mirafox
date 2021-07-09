<?php
// Кешируем вывод в буфер, чтобы можно было начать сессию из
// любого места кода, если не из консоли запущено
if (php_sapi_name() !== "cli") {
	ob_start();
}
// TODO мониторить
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && preg_match('!ru\.upnbapp\.!i', $_SERVER['HTTP_X_REQUESTED_WITH'])) {
	exit('<html><body><p>Данное приложение не является официальным и никак не связано с сайтом Kwork.ru или Kwork.com, разработчики незаконно используют торговую марку Kwork в названии приложения.</p><p>Возможно приложение создано с нечистоплотными целями, об этом в том числе говорит то, что приложение запрашивает неадекватные разрешения, например, следить за вашим местонахождением.</p><p>Настоятельно рекомендуем как можно скорее удалить это приложение.</p><p>Вскоре у Kwork появится собственное официальное приложение, о чем будет сообщено на сайте и в рассылке. Не используйте сомнительные приложения, на которые нет ссылок с сайта Kwork.</p></body></html>');
}

define('DOCUMENT_ROOT', dirname(dirname(__FILE__)));
define('APP_ROOT', dirname(DOCUMENT_ROOT));

$_SERVER['REMOTE_ADDR'] = "127.0.0.1";

require_once DOCUMENT_ROOT . "/include/profile/init.php";

// Вся общая(и для мобильной версии) логика до актора тут
require_once(DOCUMENT_ROOT . '/include/common_config/upper.php');

$session = Session\SessionContainer::getSession();
// smarty
{
	$smarty = \Smarty\SmartyBridge::getMainInstance();
	if (App::isLocalMode()) {
		$smarty->compile_check = Smarty::COMPILECHECK_ON;
	} elseif (App::isProductionMode()) {
		$smarty->compile_check = Smarty::COMPILECHECK_OFF;
	}

	$smarty->caching =false;
	$smarty->force_compile =true;
}

// инициирую в smarty частые дефолтные значения, чтобы не было нотисов
{
	$smarty->assign("message", null);
	$smarty->assign("error", null);
	$smarty->assign("snotice", null);
	$smarty->assign("searchValue", null);
	$smarty->assign("mtitle", null);
	$smarty->assign("mdesc", null);
	$smarty->assign("canonicalUrl", null);
	$smarty->assign("pageName", null);
}
if (Translations::isDefaultLang()) {
	setlocale(LC_TIME, 'ru_RU.UTF-8');
} else {
	setlocale(LC_TIME, 'en_US.UTF-8');
}
// actor
$actor = getActor();

//Если англичанин вошёл в ru, перекидываем в com с сохранением сессии
if (UserManager::needEnRedirect($actor->lang)) {
	$token = App::genMirrorAuthData($actor->id, "", ["addtime" => $actor->addtime]);
	redirect((empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . App::config('endomain') . '/?mirror=' . $token);
}

// Вся общая(и для мобильной версии) логика после получения актора тут
require_once(DOCUMENT_ROOT . '/include/common_config/after_actor.php');

$smarty->assign("actor", $actor);

// После каждого захода на сайт
if ($actor) {
	$smarty->assign('login_user_id', $actor->id);
}

// promo
$smarty->assign("show_promo_header", !isset($_COOKIE["show_promo_header"]) || $_COOKIE["show_promo_header"] == 1);

$smarty->assign('appMode', App::config('app.mode'));

$smarty->assign('baseurl', App::config('baseurl'));
$smarty->assign("catalog", Controllers\Catalog\AbstractViewController::DEFAULT_VIEW);
$smarty->assign('basedir', App::config('basedir'));
$smarty->assign('adminurl', App::config('adminurl'));
$smarty->assign('imagedir', App::config('imagedir'));
$smarty->assign('imageurl', App::config('imageurl'));
$smarty->assign('portfoliourl', App::config('portfoliourl'));
$smarty->assign('coverurl', App::config('coverurl'));
$smarty->assign('pdir', App::config('pdir'));
$smarty->assign('purl', App::config('purl'));
$smarty->assign('membersprofilepicdir', App::config('membersprofilepicdir'));
$smarty->assign('membersprofilepicurl', App::config('membersprofilepicurl'));
$smarty->assign('maxSmtpError', App::config("mail.max_smtp_error"));
$smarty->assign('metadescription', App::config('metadescription'));
$smarty->assign('metakeywords', App::config('metakeywords'));
$smarty->assign('site_name', App::config('site_name'));

try {
	// thekwork.local часто падает на этом месте
	$smarty->assign("currencyHtml", $smarty->fetch(App::config('basedir') . "/themes/utils/currency.tpl"));
} catch (Exception $e) {
	Log::daily(__FILE__ . ", line " . __LINE__ . "\n" . $e->getMessage(), "error");
}

$smarty->assign('metricEnable', App::config('metric.enable'));
if ($actor) {
	$smarty->assign('pullModuleEnable', Helper::isModuleChatEnable());
} else {
	$smarty->assign('pullModuleEnable', false);
}

// корзина
$smarty->assign("basketEnable", App::config("basket.enable"));
$cart = [];
$smarty->assign('cart', $cart);
$cartTotalPrice = 0;
foreach ($cart as $cartItem) {
	$cartTotalPrice += $cartItem['price'];
}

$smarty->assign('cartTotalPrice', $cartTotalPrice);
$smarty->assign('cartCount', count($cart));

// статистика посещений по дням
UserManager::registerUserAuth();

$canonUrl = get_canonical_url();
$smarty->assign('canonicalUrl', trim(App::config('baseurl') . $canonUrl, "/"));
$smarty->assign('canonicalOriginUrl', trim(App::config('originurl') . $canonUrl, "/"));

if ($session->isExist('track_client_id')) {
	$smarty->assign('track_client_id', $session->get('track_client_id'));
	$session->delete('track_client_id');
}

$utmFields = [
	'gclid',
	'utm_medium',
	'utm_source',
	'utm_campaign',
	'utm_content',
	'utm_term',
	'_openstat'
];
foreach ($utmFields as $field) {
	if (isset($_GET[$field]) && $_GET[$field] != '') {
		header("HTTP/1.1 200 OK");
		break;
	}
}
$smarty->assign("imgAltTitle", Translations::t("Сервис фриланс-услуг"));
$smarty->assign('imgNumber', 1);

AssetManager::devCheckAndMinify();
AssetManager::devCheckAndMinify("basic");

$smarty->assign('cex', Currency\CurrencyExchanger::getInstance());

$isMobile = $isTablet = false;
$onlyDesktopVersion = isset($_COOKIE['only_desktop_version']);
$smarty->assign("isMobile", $isMobile);
$smarty->assign("isTablet", $isTablet);
$smarty->assign("onlyDesktopVersion", $onlyDesktopVersion);
$cookieTime = time() + (360 * 24 * 60 * 60);

if ($isMobile && !$onlyDesktopVersion) {
	CookieManager::set("site_version", "mobile", $cookieTime);
} else {
	CookieManager::set("site_version", "desktop", $cookieTime);
}

/*
 * Проверка для включения на посадочных страницах жесткой оптимизации по рекомендациям Google PageSpeed:
 * только для мобильной версии;
 * только для неаторизованных пользователей.
 */
if ($isMobile && !$isTablet && !$onlyDesktopVersion && !$actor) {
	$isNeedPageSpeed = true;
} else {
	$isNeedPageSpeed = false;
}
$smarty->assign("isNeedPageSpeed", $isNeedPageSpeed);

// Добавить в параметры текущий язык
$smarty->assign("i18n", \Translations::getLang());

// Данные для регистрации
$smarty->assign('userLoginLength', UserManager::USER_LOGIN_LENGTH);

// Фильтр вывода исправляет баг в html коде оставленный smarty тегом {strip}
$smarty->loadFilter('output', 'fixstrip');
$smarty->assign('curYear', date("Y", time()));

$smarty->assign("canUserWithdraw", false);

// если пользователь заблокирован редиректим на страницу диалога с поддержкой
if (UserManager::isActorBlocked() && !isAdminPage()) {
	redirect("/conversations/Support");
}
