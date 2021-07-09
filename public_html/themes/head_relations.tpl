{strip}
{if $alternateUrls}
	{foreach from=$alternateUrls key=hreflang item=href}
	<link rel="alternate" href="https://{$href}" hreflang="{$hreflang}" />
	{/foreach}
{/if}

<meta name="format-detection" content="telephone=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>{if $mtitle ne ""}{$mtitle|t}{else}{if $pagetitle ne ""}{$pagetitle|t} - Kwork{/if}{/if}</title>
<meta name="description" content="{if $mdesc}{$mdesc}{else}{if $pagetitle}{$pagetitle|t}{/if}{if $pagetitle && $metadescription} - {/if}{if $metadescription}{$metadescription}{/if}{/if}">
<meta name="keywords" content="{if $metakeywords ne ''}{$metakeywords|t}{else}{if $mtags ne ""}{$mtags|t}{else}{if $pagetitle ne ""}{$pagetitle|t}{/if}{$site_name}{/if}{/if}">

{if $isMobile && !$isTablet && !$onlyDesktopVersion}
	<meta name="viewport" id="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
{else}
	<meta name="viewport" id="viewport" content="width=1024"/>
{/if}

{if $isMultiApp}
	{if $canonicalKworkUrl}
		<link rel="canonical" href="{$canonicalKworkUrl}" />
	{/if}
{else}
	{if $canonicalUrl}
		<link rel="canonical" href="{$canonicalUrl}" />
	{/if}
{/if}

{if App::isMirror()}
	<meta name="robots" content="noindex, nofollow"/>
{else if $isNoIndex eq 1}
	<meta name="robots" content="noindex, follow"/>
{/if}

{Helper::printCssFile("/css/fonts.css"|cdnBaseUrl, "screen")}
{if $isWorkbayApp}
	{Helper::printCssFile("/css/fontgothampro.css"|cdnBaseUrl, "screen")}
	{Helper::printCssFile("/css/dist/workbay.css"|cdnBaseUrl, "screen")}
{/if}
{if !$pageSpeedMobile}
	{**
	 * на ключевых страницах стили подгружаем динамически после полной загрузки страницы
	 * подробнее: http://wikicode.kwork.ru/optimizaciya-skorosti-zagruzki-klyuchevyx-stranic-po-google-pagespeed/
	 *}
	{if $pageSpeedDesktop && $pageName != "view"}
		{* на странице кворка отдаем полный вариант файла стилей *}
		{Helper::printCssFile("/css/minified_basic.css"|cdnBaseUrl, "screen")}
	{else}
		{Helper::printCssFile("/css/minified.css"|cdnBaseUrl, "screen")}
	{/if}
	{if $pageName == "land"}
		{Helper::printCssFile("/css/land.css"|cdnBaseUrl, "screen")}
	{/if}
{else}
	{* критические стили для первого экрана мобильного девайса на посадочных страницах *}
	<style>{$criticalStyles}</style>
{/if}

{include file="head_favicon.tpl"}

<meta http-equiv="X-UA-Compatible" content="IE=edge" />
{if $pageName == "index"}
	<meta name="yandex-verification" content="b9ed2660bbff1e2f" />
{/if}

{* js-файл с переводами *}
{if Translations::isDefaultLang()}
	<script>
		{literal}var translates = {};{/literal}
	</script>
{else}
    {Helper::printJsFile("/js/{Translations::getLang()}.js"|cdnBaseUrl, $pageSpeedMobile)}
{/if}

{include file="config/header.tpl"}

<script>
	var ORIGIN_URL = '{App::config('originurl')}';
	var KWORK_BASE_URL = '{App::config('kwork_baseurl')}';
	var CANONICAL_ORIGIN_URL = '{$canonicalOriginUrl}';
	var IS_MIRROR = {if App::isMirror()}1{else}0{/if};
	var IS_BILL_ENABLE = 0;
	var USER_ID = "{$actor->id}";
	var PULL_MODULE_ENABLE = false;
	var MESSAGE_SOUND_ENABLE = {if $actor && $actor->message_sound}1{else}0{/if};
	var MIN_LANG_PERCENT = "{KworkManager::MIN_LANG_PERCENT}";
	var USER_REFILL_SYSTEM = 1;
	var BILL_COMISSION = 0;
	{if $actor->kwork_allow_status === "deny"}
		var USER_KWORK_BLOCKED = {
			blockType: "{$actor->kworkBlock.blockType}",
			reason: "{str_replace(array("\r\n", "\r", "\n"), "<br />", $actor->kworkBlock.reason)}",
			dueDate: "{$actor->kworkBlock.dueDate}"
		};
	{/if}
	var lang = "{Translations::getLang()}";
	{if $actor}
		var actor_lang = "{$actor->lang}";
	{else}
		var actor_lang = "{Translations::getLang()}";
	{/if}
	{if $actor && $actor->disableEn}
		var disable_actor_en = true;
	{else}
		var disable_actor_en = false;
	{/if}
	{if App::config("module.lang.enable") || $actor->isLanguageTester}
		var disable_en = false;
	{else}
		var disable_en = true;
	{/if}
	{if Translations::isDefaultLang()}
		var MIN_PRICE = 500;
	{else}
		var MIN_PRICE = 10;
	{/if}
	var CURRENCY_RATE = {App::config("currency_rate")};
	var PAYPAL_ENABLED = false;
	{to_js name="serverTime" var=time()}
	var base_url = '{App::config('baseurl')}';
	var CURRENT_APP_DESCRIPTION = "Kwork";
</script>

{* js-файлы подключать сюда *}
{include file="head_scripts.tpl"}

{/strip}