{strip}
		{if $pageModuleName != "chat"}<div class="substrate-footer"></div>{/if}
	</div>
    {if $pageModuleName != "chat"}
	<div class="clear"></div>
	<div class="footer m-hidden{if $pageName == "index"} is_index{/if}">
		<div class="lg-centerwrap centerwrap footertop">
				<div class="bottomLinks {if Translations::getLang() == Translations::EN_LANG}en{/if}">
					<div class='linksBlock'>
						<span class="linksBlockTitle">{'О Kwork'|t}</span>
						<a href="{$baseurl}/about">{'О проекте'|t}</a>
						<a href="{$baseurl}/terms">{'Пользовательское соглашение'|t}</a>
						<a href="{$baseurl}/privacy">{'Политика конфиденциальности'|t}</a>
					</div>
					<div class='linksBlock'>
						<span class="linksBlockTitle">{'Помощь'|t}</span>
						<a href="{$baseurl}/terms_of_service">{'Правила сервиса'|t}</a>
						<a href="{$baseurl}/faq">{'Вопрос - Ответ'|t}</a>
						{if App::config("module.support_enable")}
							<a href="{$baseurl}/support">{'Служба поддержки'|t}</a>
						{/if}
						<a href="/" class="render_mobile-js" {if !$isMobile || !$onlyDesktopVersion}style="display:none"{/if}>{'Перейти на мобильную версию'|t}</a>
					</div>
					<div class="linksBlock">
						<span class="linksBlockTitle">{'Полезное'|t}</span>
						{if Translations::isDefaultLang()}
							<a href="{$baseurl}/for-sellers">{'Фрилансеру'|t}</a>
						{else}
							<a href="{$baseurl}/for-sellers">{'Фрилансеру'|t}</a>
						{/if}
						<a href="{$baseurl}/{$catalog}">{'Категории'|t}</a>
						{if Translations::isDefaultLang()}
							<a href="{$baseurl}/kwork_book">
								<span class="label-new">Новое</span>
								Как зарабатывать на Kwork
							</a>
						{/if}
					</div>
					<div class="linksBlock">
						<span class="linksBlockTitle">{'Сообщество'|t}</span>
						{if Translations::isDefaultLang()}
							<a href="http://blog.kwork.ru" target="_blank">{'Блог Кворк'|t}</a>
							<a href="{$baseurl}/cases">{'Кейсы'|t}</a>
						{/if}
						<a href="{$baseurl}/partners">{'Партнерская программа'|t}</a>
						{if Translations::getLang() == Translations::EN_LANG}		
							<div class="footer__correct-currency">
								{Translations::getShortCurrencyByLang(Translations::EN_LANG)} {Translations::getCurrencyByLang(Translations::EN_LANG)}
							</div>
						{/if}
					</div>
					<div class="linksBlock">
						{if Translations::isDefaultLang()}
							<a href="http://mirafox.com" class="dib mr18 cur-hover" target="_blank"><i class="icon ico-mirafoxLogo"></i></a>
							<div class="socialIcons">
								<a href="https://vk.com/kwork_kwork" class="icon ico-circle-vk-hover" target="_blank"></a>
								<a href="https://www.facebook.com/kworkru" class="icon ico-circle-fb-hover" target="_blank"></a>
								<a href="https://twitter.com/kworkru" class="icon ico-circle-tw-hover" target="_blank"></a>
							</div>
							<div class="dib f13 copyright">&copy; 2015 - {$curYear} {$site_name}</div>
						{else}
							<a href="http://mirafox.com/en/" class="dib mr18 cur-hover" target="_blank"><i class="icon ico-mirafoxLogo"></i></a>
							<div class="socialIcons">
								<a href="https://twitter.com/KworkProject" class="icon ico-circle-tw-hover" target="_blank"></a>
								<a href="https://www.facebook.com/KworkProject" class="icon ico-circle-fb-hover" target="_blank"></a>
							</div>
							<div class="dib f13 copyright">&copy; 2018 - {$curYear} {$site_name|t}</div>
						{/if}
					</div>
				</div>
			{if Translations::getLang() === Translations::EN_LANG && App::config("module.en_bugreport.enable")}
				<div class="mt20">
					Press CTRL+ENTER to report a bug or a mistake
				</div>
			{/if}
			<div class="clear"></div>
		</div>
		<a href="#" class="scrollup"><i class="fa fa-chevron-up" aria-hidden="true"></i></a>
	</div>
    {/if}

	{if $pageSpeedMobile}
		{include file="footer_pagespeed.tpl"}
	{/if}

	{include file="config/footer.tpl"}
	{include file="footer_base.tpl"}
{/strip}