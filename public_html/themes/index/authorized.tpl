{Helper::printJsFile("/js/authorized.js"|cdnBaseUrl)}
{Helper::printCssFile("/css/dist/index.css"|cdnBaseUrl, "all")}


<div id="app">
	<div class="banner">
		<div class="centerwrap relative">
			<div class="headertext">
				<h1 class="f34 fw600">{'Kwork – магазин и биржа фриланс-услуг'|t}</h1>
				<span class="headertext_subtitle headertext_subtitle_authorized">
					{'Десятки тысяч услуг, скорость и качество исполнения'|t}
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
			</div>
		</div>
	</div>
</div>	
{Helper::printJsFile("/js/dist/index.js"|cdnBaseUrl)}

{if $wantsCount > 0}
<div class="lg-centerwrap centerwrap main-wrap m-m0 ovf-h">
	<h1 class="t-align-c m-fs22">{'Интересное на бирже'|t}</h1>
	<p class="t-align-c mb25">{'Изучите проекты на бирже, оставьте свое предложение и получите заказ.'|t}</p>
	<table class="table-style w750 m-wMax">
		<thead>
		<tr>
			<td class="w40p fw700">
				<span class="pl10 color-text">{'Проект'|t}</span>
			</td>
			<td class="w18p fw700">
				<span class="color-text">{'Покупатель'|t}</span>
			</td>
			<td class="w5p fw700 ta-center">
				<span class="color-text">{'Цена'|t}</span>
			</td>
		</tr>
		</thead>
		{foreach $wants as $want}
			<tr>
				<td class="pt-pb-8">
					<div class="ml10">
						<a class="link-color dib first-letter" href="{$baseurl}/new_offer?project={$want->id}">
							{$want->name|stripslashes}
						</a>
						<div class="f12 mt5">{$want->category->parentCategory->name} > {$want->category->name}</div>
					</div>
				</td>
				<td class="pt-pb-8">
					<div class="d-flex align-items-center">
						{include file="user_avatar.tpl" profilepicture=$want->user->profilepicture username=$want->user->username size="small" class="user-avatar-image s22 flex-1-0-100 m-hidden mr5"}
						<a class="link-color word-break-all" href="{$baseurl}/{insert name=get_seo_profile value=a username=$want->user->username|stripslashes}">
							{$want->user->username|stripslashes}
						</a>
					</div>
				</td>
				<td class="ta-center pt-pb-8 fw600 nowrap">
					{if $want->price_limit > 0}
					<a class="colorGreen f16" href="{$baseurl}/new_offer?project={$want->id}">
						{include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
					</a>
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
	<div class="mb20">
		<a href="{$baseurl}/projects" class="mt20 hugeGreenBtn GreenBtnStyle h50 pull-reset mw300px mAuto m-wMax">
			{'Перейти на биржу'|t}
		</a>
	</div>
</div>
{/if}

{if $kworks|@count >= 1}
	{if $isMobile && !$isTablet && !$onlyDesktopVersion}
		<div class="lg-centerwrap centerwrap m-m0 authorized-kwork-block authorized-kwork-block__popular clearfix">
			<h1 class="t-align-c pt15 m-fs22">{'Популярные кворки'|t}</h1>
			<div class="cusongs">
				<div class="cusongslist cusongslist_5_column pb0 cusongslist-authorized-popular authorized-popular-rest-hidden">
					{include file="fox_bit.tpl" posts=$kworks}
				</div>
			</div>
			<div class="pull-right semibold fs16">
				<div class="preloader_rest_popular">
					<div class="preloader__ico prealoder__ico_rest_popular pull-left"></div>
				</div>
				<a href="javascript:void(0);" class="js-authorized-load-popular" data-loaded="0" data-weight-minute="{$weightMinute}">{'Смотреть все'|t}</a>
			</div>
		</div>
	{else}
		<div class="lg-centerwrap centerwrap main-wrap m-m0 authorized-kwork-block clearfix">
			<h1 class="t-align-c m-fs22">{'Популярные кворки'|t}</h1>
			{control name="_blocks/kwork/carousel_kwork" kworks=$kworks carouselName="popular" kworkLoadCategory=3}
		</div>
	{/if}
{/if}

<div class="bgWhite pt20 m-hidden">
	{include file="index/statistics.tpl"}
</div>

<div class="m-hidden bgWhite pt20">
	<div class="lg-centerwrap centerwrap">
		<hr class="m0 gray">
	</div>
</div>
