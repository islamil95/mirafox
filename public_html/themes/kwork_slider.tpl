{strip}
	{if $kwork.bonus_text && $kwork.bonus_moderate_status == 1 && \App::config('promo_show_bonus') == 1}
	<div class="promo_slider tooltipster"
		data-tooltip-text="Купите этот кворк и участвуйте в<br> <a href='/newyear'>розыгрыше iPhone 8</a>"
		data-tooltip-theme="dark">
		{if \App::config('promo_show_badges') == 1}
			<img src="{"/promo/newyear_2018/badges_01.png"|cdnImageUrl}" class="promo_slider_badges" alt="">
		{/if}
		<div class="promo_slider_header">{'Новогодняя акция:'|t}</div>
		<div class="promo_slider_text">+ {$kwork.bonus_text}</div>
	</div>
	{/if}
	<div id="slider_{$type}" data-kwork-id="{$kwork.PID}" class="kwork-slider {if $portfolioList} kwork-slider_portfolio{/if} {if !$kworkPortfolioEmpty && $isShowPortfolio}isShowPortfolio{/if}">
		{assign var=index value=0}
		<div data-index="{$index++}" class="sliderItem ">
			<div class="sliderImage" itemscope>
				{assign var="imgClass" value=""}
				{include file="_blocks/thumbnail_img_load.tpl"}
                {if $kwork.is_resizing == 0}
                    {assign var="imageSize" value="t3"}
                {else}
                    {assign var="imageSize" value="t0"}
					{$sizeImage = \CImage::getSizeImage("{$purl}/{$imageSize}/{$kwork.photo}")}
					{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
						{assign var="imgClass" value="isHorizontalImg"}
					{/if}
                {/if}
				<img itemprop="image" src="{$purl}/{$imageSize}/{$kwork.photo}" width="660" height="440" alt="" class="{$imgClass}">
				{if !$kworkPortfolioEmpty && $isShowPortfolio}
					<span class="kwork_slider__zoom">
						<i class="fa fa-arrows-alt kwork_slider__icon"></i> {'Открыть портфолио'|t}
					</span>
				{/if}
			</div>
		</div>
		{if $portfolioList}
			{foreach from=$portfolioList item=portfolioItem}
				{*Все изображения элемента портфолио*}
				{foreach from=$portfolioItem->images item=portfolioImage}
					{include file="kwork/view/view_portfolio_slide.tpl" isVideo=false}
				{/foreach}
				{*Все видео элемента портфолио*}
				{foreach from=$portfolioItem->videos item=video}
					{include file="kwork/view/view_portfolio_slide.tpl" isVideo=true}
				{/foreach}
			{/foreach}
		{/if}
		{foreach from=$kworkPortfolio item=portfolioItem}
			{foreach from=$portfolioItem->images item=portfolioImage}
				<div data-index="{$index++}" data-id="{$portfolioImage->id}" data-portfolio-id="{$portfolioItem->id}" class="sliderItem">
					<div class="sliderImage">
						{assign var="imgClass" value=""}
						{include file="_blocks/thumbnail_img_load.tpl"}
						{if $portfolioImage->is_resizing == 0}
							{assign var="imageSize" value="t3"}
						{else}
							{assign var="imageSize" value="t0"}
							{$sizeImage = \CImage::getSizeImage("{$purl}/{$imageSize}/{$kwork.photo}")}
							{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
								{assign var="imgClass" value="isHorizontalImg"}
							{/if}
						{/if}
						{if $lazyLoad}
							<img data-lazy="{$portfolioImage->getSizeUrl($imageSize)}" width="660" height="440" alt="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}" src="{"/empty.png"|cdnImageUrl}" class="{$imgClass}" />
						{else}
							<img src="{$portfolioImage->getSizeUrl($imageSize)}" width="660" height="440" alt="" class="{$imgClass}" />
						{/if}
						{if !$kworkPortfolioEmpty && $isShowPortfolio}
							<span class="kwork_slider__zoom">
								<i class="fa fa-arrows-alt kwork_slider__icon"></i> {'Открыть портфолио'|t}
							</span>
						{/if}
					</div>
				</div>
			{/foreach}
			{foreach from=$portfolioItem->videos item=portfolioVideo}
				{include file="kwork/view/simple_video_slide.tpl" video=$portfolioVideo->url}
			{/foreach}
			{* Это на время миграции пока не создадутся видео, потом можно убрать *}
			{if $portfolioItem->video && $portfolioItem->videos|@count == 0}
				{include file="kwork/view/simple_video_slide.tpl" video=$portfolioItem->video}
			{/if}
		{/foreach}
		{if $portfolioLimit < $portfolioCount}
			<div data-index="{$index++}" class="sliderItem">
				<div class="sliderImage lastSlide">
					<div class="overlay">
						<div class="text">
							{'Посмотрите другие примеры работ<br>в <a href="/user/%s">профиле %s</a>'|t:$kwork.username:$kwork.username}
						</div>
					</div>
					<img data-lazy="{$purl}/t3/{$kwork.photo}" width="660" height="440" alt="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}" src="{"/empty.png"|cdnImageUrl}"/>
				</div>
			</div>
		{/if}
	</div>

	{* Выводим превью карточки кворка при модерации *}
	{if $canModer}
		<div class="kwork-card-moder-preview kwork-card-data-wrap" data-kwork-load-category="1">
			{control name="_blocks/kwork/kwork_card" kwork=$kwork}
		</div>
	{/if}
{/strip}