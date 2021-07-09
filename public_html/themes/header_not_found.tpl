<!DOCTYPE html>
{strip}
<html lang="{if is_null($i18n)}ru{else}{$i18n}{/if}" {if $isMobile}{if $isTablet}class="tablet"{else}class="mobile"{/if}{/if}>
<head>
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta property="og:image" content="{"/large_logo.jpg"|cdnImageUrl}">
	<meta property="og:image:width" content="250">
	<meta property="og:image:height" content="250">
	<title>{'Страница не найдена'|t}</title>
	<meta name="description" content="{'Страница не найдена'|t} - {'Больше чем биржа фриланса'|t}">
	<meta name="keywords" content="{'фриланс, услуги онлайн, заказать услуги'|t}">
	{if $isMobile && !$isTablet && !$onlyDesktopVersion}
		<meta name="viewport" id="viewport" content="width=device-width,height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	{else}
		<meta name="viewport" id="viewport" content="width=1024"/>
	{/if}
	<link rel="canonical" href="{App::config('originurl')}/not_found">
	{Helper::printCssFile("/css/fonts.css"|cdnBaseUrl, "screen")}
	{Helper::printCssFile("/css/minified.css"|cdnBaseUrl, "screen")}
	{include file="head_favicon.tpl"}
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	{* js-файл с переводами *}
	{if Translations::isDefaultLang()}
		<script>
			{literal}var translates = {};{/literal}
		</script>
	{else}
		{Helper::printJsFile("/js/{Translations::getLang()}.js"|cdnBaseUrl, $pageSpeedMobile)}
	{/if}
	<script>
		var ORIGIN_URL = '{App::config('originurl')}';
		var CANONICAL_ORIGIN_URL = '{$canonicalOriginUrl}';
		var IS_MIRROR = {if App::isMirror()}1{else}0{/if};
		var IS_BILL_ENABLE = {if App::config('module.refill_bill.enable') === true || in_array($actor->id, explode(',',App::config('module.refill_bill.enable')))}1{else}0{/if};
		var USER_ID = "{$actor->id}";
		var PULL_MODULE_ENABLE = {if $pullModuleEnable}1{else}0{/if};
		var MESSAGE_SOUND_ENABLE = {if $actor && $actor->message_sound}1{else}0{/if};
		var BILL_COMISSION = '{BillManager::COMISSION_PERCENT}';
		var MIN_LANG_PERCENT = "{KworkManager::MIN_LANG_PERCENT}";
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
		var config = {};
		var CURRENCY_RATE = {$cex->convertToRUB(1)};
		var PAYPAL_ENABLED = {if PaypalManager::isLangAvailable() && PaypalManager::isAvailableForUser($actor->id)}true{else}false{/if};
	</script>
	{Helper::printJsFile("/js/jquery.min.1.9.1.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/jquery.mb.browser.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/jquery.jscrollpane.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/fox.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/formDataFilter.js"|cdnBaseUrl)}
</head>
<body>
<div class="header relative">
	<div class="header_top">
		<div class="centerwrap lg-centerwrap relative">
			<div class="headertop">
				<div class="brand-image">
					<a href="{$baseurl}/">
						<i class="icon ico_retina ico-kwork"></i>
						<div class="f9 logo_subtext"
						     style="color:white; position:absolute; bottom: -8px; white-space:nowrap;">{'Супер фриланс'|t}</div>
					</a>
				</div>
				<div class="m-visible pt10 float-left">
						<a href="{$baseurl}/">
							<i class="icon ico_retina ico-kwork"></i>
							<div class="f9 logo_subtext {if Translations::getLang() == Translations::EN_LANG}logo_subtext_en{/if}"
							     style="color:white; white-space:nowrap;">{'Супер фриланс'|t}</div>
						</a>
				</div>
				<div class="clear"></div>
			</div>

		</div>
		{control name="components\header_categories"}
	</div>
</div>
<div class="all_page{if $pageModuleName} page-{$pageModuleName}{/if}{if $pageModuleName} page-{$pageModuleName}{/if}{if $pageName == 'index'} is_index{elseif $pageName == 'land'} is_land{elseif $pageName == 'cat'} is_cat{/if}">
{/strip}