{strip}
	<div data-index="{$index++}" data-portfolio-id="{$portfolioItem->id}" class="sliderItem portfolio-slide-js">
		<div class="sliderImage portfolio-kwork_sliderBlock">
			{if $isVideo}
				{insert name=youtube_key value=a assign=ykey yt=$video}
				<div class="kwork-slider_videoWrapper">
					<div class="kwork-slider_videoWrapper_play-block">
						<img width="650" height="433" data-lazy="//img.youtube.com/vi/{$ykey}/0.jpg" alt="" src="{"/empty.png"|cdnImageUrl}"/>
						<div class="play-video">
							<i class="ico-play-css" style="cursor:pointer"></i>							
						</div>
					</div>
				</div>
			{else}
				{assign var="imgClass" value=""}
				{include file="_blocks/thumbnail_img_load.tpl"}
				{if $portfolioImage->is_resizing == 0}
                    {assign var="imageSize" value="t3"}
                {else}
                    {assign var="imageSize" value="t0"}
					{$sizeImage = \CImage::getSizeImage($portfolioImage->getSizeUrl("t0"))}
					{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
						{assign var="imgClass" value="isHorizontalImg"}
					{/if}
                {/if}
				<img itemprop="image" src="{$portfolioImage->getSizeUrl($imageSize)}" width="660" height="440" alt="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}" class="js-sliderImage-picture portfolio-kwork_image {$imgClass}">
			{/if}
			{if $isShowPortfolio}
				<span class="kwork_slider__zoom">
					<i class="fa fa-arrows-alt kwork_slider__icon"></i> {'Открыть портфолио'|t}
				</span>
			{/if}
			<div class="portfolio-kwork_mini_review">
				{if !$isVideo}
					<div class="portfolio-kwork_mini_review_bg lazy-bg" data-lazy="{$portfolioImage->getSizeUrl("t3")}"></div>
				{/if}
				<div class="portfolio-kwork_mini_review_content clearfix {if $isVideo}bgBlack{/if}">
					<img class="rounded" alt="{$portfolioItem->username}" data-lazy="{"/small/{$portfolioItem->profilepicture}"|cdnMembersProfilePicUrl}" width="30" height="30" src="{"/empty.png"|cdnImageUrl}">
					{if $portfolioItem->comment != ''}
						<div class="portfolio-kwork_review_username hover_transition">
							<div class="hover_inner">
								"{$portfolioItem->comment|truncate:500:"..."}"
							</div>
							<div class="hover_oneline">
								"{$portfolioItem->comment|truncate:500:"..."}"
							</div>
						</div>
					{/if}
					<div class="portfolio-kwork_review_small_text hover_transition">
						{if $portfolioItem->comment != ''}
							{'Отзыв от'|t} {$portfolioItem->username}, {if $portfolioItem->gtitle}{'кворк'|t} "{$portfolioItem->gtitle|truncate:50:"..."|mb_ucfirst}", {/if}{(time() - $portfolioItem->time_added)|timeLeft}
						{else}
							{$portfolioItem->username}, {if $portfolioItem->gtitle}{'кворк'|t} "{$portfolioItem->gtitle|truncate:50:"..."|mb_ucfirst}", {/if}{$portfolioItem->time_left|timeLeft}
						{/if}
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}