<!DOCTYPE html>
{strip}
<html lang="{if is_null($i18n)}ru{else}{$i18n}{/if}" {if $isMobile}{if $isTablet}class="tablet"{else}class="mobile"{/if}{/if}>
	<head>
		{include file="head_relations.tpl"}
	</head>
	<body id="body" class="{if $isMobile && !$onlyDesktopVersion}is_mobile{/if}{if $smarty.server.HTTP_USER_AGENT|strstr:'KworkMobileAppWebView'} kwork-mobile-app{/if}{if $pageSpeedMobile} page-speed-loading{/if}{if $pageModuleName == "chat"} is_chat{/if}">

		{include file="header_google_tag_manager.tpl"}

		{if $actor && $actor->show_poll_notify && $canonicalUrl != $baseurl|cat:'/poll'}
			<div class="abandoned-basket m-hidden">
				<div class="abandoned-basket__layout">
					<span>{'Оставьте свое мнение о Kwork и получите награду в профиль.'|t}</span>
					<div onclick="location.href = '/poll?hash={$actor->pollHash}&type=notify'" class="green-btn ml20">
						{'Перейти к опросу'|t}
					</div>
				</div>
				<div class="js-poll-notify__close abandoned-basket__close popup-close cur" style="top:25px;"></div>
			</div>
		{/if}

		<script type="application/ld+json">
		{
			"@context" : "http://schema.org",
			"@type" : "Organization",
			"name" : "{'kwork.ru - магазин фриланс-услуг'|t}",
			{if Translations::isDefaultLang()}
			"url" : "https://kwork.ru/",
			{else}
			"url" : "https://kwork.com/",
			{/if}
			"sameAs" : [
			{if Translations::isDefaultLang()}
			"https://www.facebook.com/kworkru",
			"https://twitter.com/kworkru",
			"https://vk.com/kwork_kwork"
			{else}
			"https://www.facebook.com/KworkProject",
			"https://twitter.com/KworkProject"
			{/if}
			]
		}
		</script>

		<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "Organization",
			{if Translations::isDefaultLang()}
			"url": "https://kwork.ru/",
			"logo": "https://kwork.ru/images/logo-winter.png"
			{else}
			"url": "https://kwork.com/",
			"logo": "https://kwork.com/images/logo-winter.png"
			{/if}
		}
		</script>

		<div id="loadme"></div>

		{if $actor}
			{literal}
				<script>
					function loadContent(elementSelector, sourceURL) {
						$("" + elementSelector + "").load("" + sourceURL + "");
					}

					$(document).on('click', '.change-type-user-js', function () {
						$('.change-type-user-js').removeClass('active');
						$(this).addClass('active');
						changeUserType($(this).attr('data-type'))
					});

					function changeUserType(userType, reload) {
						$.get('/change_usertype?usertype=' + userType, function (answer) {
							if (typeof (yaCounter32983614) !== 'undefined') {
								if (userType == 1) {
									yaCounter32983614.reachGoal('CHANGE-TYPE-ZAKAZCHIK');
								} else {
									yaCounter32983614.reachGoal('CHANGE-TYPE-ISPOLNITEL');
								}
							}
							if (reload) {
								if (userType == 1) {
									location.href = '/';
								} else if (userType == 2) {
									location.href = '/';
								} else {
									location.reload();
								}
							}
						});
					}
					{/literal}
					$(window).load(function () {
						{insert name=session_get_and_clean value=a param='api_after_action' assign=sessvalue}
						{if $sessvalue == 'cart'}
							CartModule.makeOrder();
						{/if}
					});
					{literal}
				</script>
			{/literal}
		{/if}

		<div class="nav-fox" id="foxmobilenav" style="display:none">
			{include file="mobile_menu.tpl"}
		</div>

		<div class="header relative{if $pageName == 'index'} is_index{/if}">
			<div class="header_top">
				<div class="centerwrap lg-centerwrap relative">
					<div class="headertop">
						{include file="header_top_mobile.tpl"}
						<div class="headertop-desktop m-hidden clearfix">
							<div class="brand-image">
								<a href="{$baseurl}/">
									<i class="icon ico_retina ico-kwork{if Translations::getLang() == Translations::EN_LANG} beta{/if}"></i>
									<span class="f9 logo_subtext {if Translations::getLang() == Translations::EN_LANG}logo_subtext_en{/if}">{'Супер фриланс'|t}</span>
								</a>
							</div>
							<div class="search{if Translations::getLang() == Translations::EN_LANG} beta{/if}">
								<div id="general-search">
									<general-search default-search-encoded='{rawurlencode($searchValue)}'></general-search>
								</div>
							</div>

							{insert name=onlineUserCount assign=onlineUserCount}
							{insert name=last_order_time assign=live_date}
							{if Translations::isDefaultLang() || $onlineUserCount >= 200}
								<div class="online-count" style="{if $live_date >= 2760}margin-top:18px;{else}margin-top:9px;{/if}">
									<div class="font-OpenSans f14 ml10" style="color: #ABABAB;">
										<i class="circle-online"></i>{'Пользователей онлайн:'|t} {$onlineUserCount}
									</div>
									{if $live_date < 2760}
										<div class="font-OpenSans f14 ml10" style="color: #ABABAB;">
											<i class="circle-ordercount"></i>{'Последний заказ:'|t} {$live_date|timeLeft:false:'назад':1:true}
										</div>
									{/if}
								</div>
							{/if}

							<input type="hidden" class="needCheckNotify" value="{if $actor}1{else}0{/if}"/>

							{if $actor}
								{if false}
									<a class="win-iphone8 promo_top_banner border-box{if $actor->type eq 'payer'} win-iphone8-payer{else} win-iphone8-buyer{/if}{if $actor->totalFunds|floor > 0} win-iphone8-balance{/if}" href="{$baseurl}/prize">
										<img src="{"/iphone8.png"|cdnImageUrl}" width="137" height="50" alt="Выиграй iPhone 8">
									</a>
								{/if}
								<div class="logoutheader">
									{if App::config("module.lang.en_site_enable") && $actor->lang == Translations::DEFAULT_LANG && !$actor->disableEn}
										{include file="lang_selector.tpl"}
									{/if}
									<div class="usernamebox droparrow" style="background: none;">
										<a href="{$baseurl}/user/{$actor->username|lower}">
											<span class="userimage">
												{include file="user_avatar.tpl" profilepicture=$actor->profilepicture username=$actor->username size="medium" class="s28"}
											</span>
											<span class="dib userdata">
												<span class="menu usernamebox_user_name db" {if $actor && $actor->isVirtual}style="color:yellow"{/if}>{$actor->username}</span>
												{if $actor->type eq 'payer'}
													<span id="usertypelabel" class="uppercase f9 font-OpenSans db">{'Покупатель'|t}</span>
												{elseif $actor->type eq 'worker'}
													<span id="usertypelabel" class="uppercase f9 font-OpenSans db">{'Продавец'|t}</span>
												{/if}
											</span>
										</a>
										<div class="clear"></div>
										<div class="dropdownbox dropdownbox-profile" style="display: none">
											{include file="header_profile.tpl"}
										</div>
									</div>

									{include file="components\header_funds.tpl"}

									{include file="components\header_notification.tpl"}
									{include file="components\header_inbox.tpl"}

									{if $basketEnable}
										{include file="cart.tpl"}
									{/if}
									{if $actor->type=="payer"}
										<a href="{$baseurl}/new_project" class="orange_button button_create_task place_order js-sms-verification-action">{'Создать задание'|t}</a>
									{/if}
									<a onclick="if (typeof (yaCounter32983614) !== 'undefined') {
											yaCounter32983614.reachGoal('ADD-KWORK');
											return true;
										}" href="{$baseurl}/new" id="newkworkbutton" {if $actor->type eq 'payer'}style="display:none"{/if} class="js-blocked-kworks pull-right GreenBtnStyle h24 mt15">
										<i class="fa fa-plus v-align-m"></i>
										<span class="dib v-align-m ml5 uppercase">{'Кворк'|t}</span>
									</a>
									<div class="clear"></div>
								</div>

								{* центровка баннера в шапке *}
								{if Translations::getLang() == Translations::DEFAULT_LANG}
									{literal}
										<script>
											function promoTopBannerCenter() {
												var margin, leftSideWidth, rightSideWidth, fullWidth;
												var headertop = $('.headertop');
												var brandImage = $('.headertop .brand-image:visible');
												var search = $('.headertop .search:visible');
												var onlineCount = $('.headertop .online-count:visible');
												var logoutHeader = $('.headertop .logoutheader:visible');
												var promoTopBanner = $('.promo_top_banner ');
												var minMargin = 10; // минимальная длина отступов от баннера

												leftSideWidth = 0;
												if (brandImage.length) {
													leftSideWidth += brandImage.width();
												}
												if (search.length) {
													leftSideWidth += search.width() + parseInt(search.css('margin-left'));
												}
												if (onlineCount.length) {
													leftSideWidth += onlineCount.width() + parseInt(onlineCount.css('margin-left'));
												}

												rightSideWidth = 0;
												if (logoutHeader.length) {
													rightSideWidth += logoutHeader.width();
													{/literal}{if $actor->type eq 'payer'}rightSideWidth -= 10;{literal}{/literal}{/if}{literal}
												}

												fullWidth = $('.headertop').width();
												margin = Math.round((fullWidth - leftSideWidth - rightSideWidth - promoTopBanner.width()) / 2);

												if (headertop.width() - (leftSideWidth + rightSideWidth + promoTopBanner.width() + minMargin) > 0) {
													if (margin !== undefined) {
														promoTopBanner.css({'margin-left': margin + 'px'}).show();
													} else {
														promoTopBanner.show();
													}
												} else {
													promoTopBanner.hide();
												}
											}

											promoTopBannerCenter();
											window.onresize = promoTopBannerCenter;
										</script>
									{/literal}
								{/if}
							{else}
								{* Если регистрацию для англокворка включат то нужно будет сделать получение списка стран *}
								<script>
									var userLoginLength = {$userLoginLength};
									var countryList = [];
									var countriesWithoutWithdrawal = [];
								</script>
								<div class="headeright">
										<ul class="clearfix">
										<li><a href="javascript:;" class="login-js">{'Вход'|t}</a></li>
										{if Translations::isDefaultLang()}
											<li><a href="javascript:;" class="signup-js register-link">{'Регистрация'|t}</a></li>
										{/if}
										{if !$actor}
											<li>
												<a href="{$baseurl}/new_project" class="orange_button button_create_task place_order">{'Создать задание'|t}</a>
											</li>
											<li><a href="{$baseurl}/for-sellers" class="pr0">{'Фрилансер?'|t}</a></li>
										{/if}
										</ul>
								</div>
							{/if}
						</div>
						<div class="clear"></div>
					</div>
				</div>
				{control name="components\header_categories"}
			</div>
		</div>

		{if !$pageSpeedMobile && !$pageSpeedDesktop && ($pageName != 'cat' || $actor)}
			{Helper::printJsFile("/js/dist/general-search.js"|cdnBaseUrl)}
		{/if}

		<input type="hidden" value="{$userRefillType}" class="user-payment-method" />
		<input type="hidden" value="{$userRefillTypeSaved}" class="user-payment-saved" />
		<div class="all_page{if $pageModuleName} page-{if $pageModuleName == "chat"}conversation{else}{$pageModuleName}{/if}{/if}{if $pageName == 'index'} is_index{elseif $pageName == 'land'} is_land{elseif $pageName == 'cat'} is_cat{/if}">
		{if App::config("category.color_view")}
			<div class="overlay_menu"></div>
		{/if}
{/strip}