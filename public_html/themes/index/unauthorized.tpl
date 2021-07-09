{strip}

{* поиск на главной странице *}
{Helper::registerFooterJsFile("/js/dist/index.js"|cdnBaseUrl)}

<div id="app">
	<div class="banner">
		<div class="centerwrap relative">
			<div class="headertext">
				<h1 class="f34 fw600">{'Kwork – магазин и биржа фриланс-услуг'|t}</h1>
				<span class="headertext_subtitle">
						{'87%% пользователей считают Kwork гораздо более удобным,<br class="m-hidden">надежным и быстрым, чем любые биржи фриланса.'|t}
				</span>
				<div class="find-service m-hidden">
					<general-search-index></general-search-index>
					{if $popular_cat}
						<div class="font-OpenSans mt5 example-search-link m-text-center">
							{'Например:'|t} <a href="{$baseurl}/{$catalog}/{$popular_cat.seo}">{$popular_cat.name|t}</a>
						</div>
					{/if}
					<div class="clear"></div>
				</div>
				<div class="color-white f16 font-OpenSans t-align-c mt20 index-advantage-block index-advantages">
					<div class="dib v-align-m t-align-l banner-icon outline-none">
						<i class="icon v-align-m ico-about-price"></i>
						<span class="dib v-align-m ml10">{'Десятки тысяч<br> услуг'|t}</span>
					</div>
					<div class="dib v-align-m t-align-l banner-icon outline-none">
						<i class="icon v-align-m ico-about-term"></i>
						<span class="dib v-align-m ml10">{'Быстрый заказ без<br>долгих обсуждений'|t}</span>
					</div>
					<div class="dib v-align-m t-align-l banner-icon outline-none">
						<i class="icon v-align-m ico-about-warranty "></i>
						<span class="dib v-align-m ml10">{'Оплата без риска<br>с гарантией возврата'|t}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{* Выбирайте услуги *}
{include file="index_top_categories.tpl"}

{* Биржа *}
<div class="index-market bgLightGray{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}">
	<div class="lg-centerwrap centerwrap position-r">
		<div class="index-market__content">
			<h2 class="f26 index-market__title">{"Биржа фриланса Kwork"|t}</h2>
			<p class="f18 index-market__text">{"Не нашли в магазине Kwork нужной услуги?<br> Разместите задание на бирже - множество<br> фрилансеров сделают свои предложения. Вам<br> останется только выбрать лучшее и начать работу."|t}</p>
			<div class="btnWrap t-align-c m-wMax">
				<a class="hugeGreenBtn hoverMe GreenBtnStyle h50 pull-reset index-market__button" href="{$baseurl}/new_project">{"Создать задание"|t}</a>
				<a class="db mt5" href="{$baseurl}/projects">{'Смотреть проекты на бирже'|t}</a>
			</div>
		</div>
		{assign var="index_market" value="/index_market@x1_gray_bg.jpg"}
		{assign var="index_market2x" value="/index_market@x2_gray_bg.jpg"}
		{if Translations::getLang() == Translations::EN_LANG}
			{$index_market = "/index_market_en@x1_gray_bg.jpg"}
			{$index_market2x = "/index_market_en@x2_gray_bg.jpg"}
		{/if}
		{if $pageSpeedMobile}
			<img src="{"/blank.png"|cdnImageUrl}" class="index-market__image lazy-load_scroll" data-src="{$index_market|cdnImageUrl}" data-srcset="{$index_market2x|cdnImageUrl} 2x" width="350" height="356" alt="">
		{else}
			<img class="index-market__image" src="{$index_market|cdnImageUrl}" srcset="{$index_market2x|cdnImageUrl} 2x" alt="">
		{/if}
	</div>
</div>

{* УТП *}
<div class="bgWhite{if $pageSpeedDesktop} lazy-load_scroll-wrapper{/if}">
	<div class="lg-centerwrap centerwrap pb10 clearfix">
		<div class="pt20"></div>
		<h2 class="mt10 f26 t-align-c m-m0">{'Kwork — новый подход к фрилансу'|t}</h2>
		<div class="pt20"></div>
		<div class="pt20 m-hidden"></div>
		<div class="landing-how-it-works">
			{include file='how_it_works_content.tpl'}
		</div>
		<div class="pt20"></div>
		<div class="pt20 m-hidden"></div>
	</div>
</div>

{include 'promo/_blocks/banner.tpl'}

{if Translations::isDefaultLang()}
	<div class="bgLightGray{if $pageSpeedDesktop} lazy-load_scroll-wrapper{/if}">
		<div class="lg-centerwrap centerwrap pt20">
			<h2 class="mt10 f26 t-align-c">{'Реальные фриланс кейсы'|t}</h2>
			<div class="f18 t-align-c mt10">{'Или как  Kwork прокачивает бизнес'|t}</div>
			<div class="clearfix real-case">
				{foreach from=$mainCases item=case}
					<div class="real-case_item">
						{if $pageSpeedDesktop}
							<img src="{"/blank.png"|cdnImageUrl}" class="lazy-load_scroll" data-src="{"/cases/avatars/{$case.user_login}.jpg"|cdnImageUrl}" width="144" height="144" alt="{$case.title}">
						{else}
							<img src="{"/cases/avatars/{$case.user_login}.jpg"|cdnImageUrl}" width="144" height="144" alt="{$case.title}">
						{/if}
						<div class="real-case_name">{$case.user_name}</div>
						<div class="js-multi-elipsis real-case_title" title="{$case.title}">
							<div>{$case.title}</div>
						</div>
						<div class="clearfix">
							<a href="/cases/{$case.id}" class="real-case_link">{'Читать полностью'|t}</a>
						</div>
					</div>
				{/foreach}
			</div>
			<div class="mb20 m-hidden"></div>
			<div class="t-align-c">
				<a href="/cases" class="green-btn inactive real-case_btn">{'Смотреть все кейсы'|t}</a>
				<div class="pb40 m-hidden"></div>
				<div class="pb20 m-visible"></div>
			</div>
		</div>
	</div>
{/if}

<div class="dark-gray-wrap m-hidden">
	<div class="centerwrap index-footer-centerwrap">
		<div class="fontf-pnl m-text-center pb25">
			<div class="pb30">
				{include file="index/statistics.tpl"}
			</div>
			<div class="t-align-c">
				<i class="icon ico_retina ico-kwork footer_logo"></i>
				{if Translations::getLang() == Translations::EN_LANG}
					<span class="fs22" style="margin-right: 8px;">:</span>
				{else}
					<span class="fs22" style="margin: 0 8px;">–</span>
				{/if}
				<span class="fs22 after-logo-text fontf-pnr">{'супер фриланс'|t}</span>
			</div>
			<div class="index-footer pt20">
				<p class="mb10 t-align-c">{'Kwork – это потрясающе удобная фриланс платформа, которая включает:'|t}</p>
				<ol class="index-footer-columns">
					<li>{'<strong>Магазин фриланс услуг.</strong> Фрилансеры оформляют свои услуги в виде кворков, то есть в виде товаров, которые можно купить в один клик. Идеально подходит для типовых услуг: логотипы, баннеры, SEO и др... То есть услуги и работа исполнителей продается как товар, а это экономит массу времени, денег и нервов. Нет опыта во фрилансе – начните именно с магазина, в нем разберется даже ребенок.'|t}</li>
					<li>{'<strong>Биржа фриланса.</strong> Вы создаете задание, и оно становится доступно тысячам фрилансеров, которые могут отправить вам предложения. Остается только выбрать лучшее и начать работу. Такой фриланс – это идеальный вариант для решения больших и уникальных задач. Если не удалось найти подходящей услуги в магазине, то биржа фриланса – ваш выбор. '|t}</li>
				</ol>
				<p>{'Фриланс сервис Kwork – это 100% гарантия возврата средств в случае срыва заказа. Уникальная система рейтингов фрилансеров способствует тому, что во фриланс сервисе Kwork остаются только лучшие исполнители, а плохие постоянно вытесняются. Для хороших специалистов, кто ищет удаленную работу, Kwork – идеальное место. Здесь нет клонов, демпинга и конкуренции с плохими фрилансерами, зато есть тысячи хороших заказчиков, готовых платить за ваши услуги.'}</p>
			</div>
		</div>
	</div>
</div>
{/strip}