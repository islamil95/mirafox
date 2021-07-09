{* Слайд для видео в kwork_slider.tpl, ожидает переменную video*}
<div data-index="{$index++}" data-portfolio-id="{$portfolioItem->id}" class="sliderItem">
	<div class="sliderImage">
		{insert name=youtube_key value=a assign=ykey yt=$video}
		<div class="kwork-slider_videoWrapper">
			<div id="player"></div>
			<div class="kwork-slider_videoWrapper_play-block"
				 style="background: url(//img.youtube.com/vi/{$ykey}/0.jpg) center center no-repeat; background-size: cover;">
				<div
						{if !$kworkPortfolioEmpty && $isShowPortfolio}{* для нового портфолио *}
							class="play-video"
						{else}{* чтобы воспроизводилось видео в старом портфолио *}
							class="play-video-js" data-video-id="{$ykey}"
						{/if}
				>
					<i class="ico-play-css" style="cursor: pointer"></i>
				</div>
			</div>
		</div>
		{if !$kworkPortfolioEmpty && $isShowPortfolio}
			<span class="kwork_slider__zoom">
				<i class="fa fa-arrows-alt kwork_slider__icon"></i> {'Открыть портфолио'|t}
			</span>
		{/if}
	</div>
</div>