{strip}
	{$kwork.userRatingCount = UserManager::getUserRatingCount($kwork)}
	{include file='functions.tpl'}
	<div class="cusongsblock js-kwork-card cards-layout-item" data-id="{$kwork.PID}">
		{if $show_birthday_badges[$kwork.USERID]}
			<div class="kwork_birthday_badge tooltipster"
				data-tooltip-side="top"
				data-tooltip-text="{'Я поздравил Kwork с днем рождения. А вы? <nobr>%sПосмотреть поздравления%s</nobr>'|t:'<a href=\'/birthday\'>':'</a>'}"
				data-tooltip-theme="dark"
			><img src="{"/badge/{Birthday\CardManager::BADGE_ID}s.png"|cdnImageUrl}" alt="" /></div>
		{/if}
		{if $kwork.bonus_text && $kwork.bonus_moderate_status == 1 && \App::config('promo_show_badges') == 1}
		<div class="kwork_birthday_badge tooltipster"
			data-tooltip-side="top"
			data-tooltip-text="Купите этот кворк и участвуйте в<br> <a href='/newyear'>розыгрыше iPhone 8</a>"
			data-tooltip-theme="dark"
			><img src="{"/promo/newyear_2018/badges_01.png"|cdnImageUrl}" alt="" /></div>
		{/if}
		{if $kwork.bonus_text && $kwork.bonus_moderate_status == 1 && \App::config('promo_show_bonus') == 1}
			<div class="cusongsblock_promo">+ {$kwork.bonus_text}</div>
		{/if}
		<div class="songperson cusongsblock__content">
			<a href="{$baseurl}{$kwork.url}" class="ispinner-container">
				{include file="_blocks/thumbnail_img_load.tpl" spinnerMode="lite"}
				{if $kwork.is_resizing == 0}
					{assign var="imageSize" value="t4"}
				{else}
					{assign var="imageSize" value="t0"}
					{$sizeImage = \CImage::getSizeImage("{$purl}/{$imageSize}/{$kwork.photo}")}
					{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
						{assign var="imgClass" value="isHorizontalImg"}
					{/if}
				{/if}
				{assign var="photoSrcset" value="{photoSrcset($imageSize, $kwork.photo)}"}
				{if $pageSpeedMobile || ($pageSpeedDesktop && ($pageName == "land" || $pageName == "catalog"))}
					<img src="{"/kwork-carousel-blank{if $pageName == "catalog"}_transparent{/if}.png"|cdnImageUrl}"
						 class="lazy-load_scroll {$imgClass}"
						 data-src="{$purl}/{$imageSize}/{$kwork.photo}"
							{if $photoSrcset} data-{$photoSrcset}{/if}
						 alt=""
						 width="230" height="153" onload="removeISpinner(event)">
				{else}
					<img src="{$purl}/{$imageSize}/{$kwork.photo}"
							{$photoSrcset}
						 alt=""
						 width="230" height="153" class="{$imgClass}" onload="removeISpinner(event)">
				{/if}
			</a>
		</div>
		<div class="ta-left padding-content{if $showStoppedKworks} narrower{/if}">
			<p>
				<a class="multiline-faded" href="{$baseurl}{$kwork.url}"{if strlen($kwork.gtitle) > 65} title="{$kwork.gtitle|stripslashes|upstring}"{/if}>
					<span class="first-letter breakwords dib">{$kwork.gtitle|stripslashes}</span>
				</a>
			</p>
			{if $kwork.is_popular >= KworkManager::BEST_RATING}
				<div class="cusongsblock-toprated m-hidden clearfix">
					<div class="toprated-inner-white">
						<span class="fox-express">{'Популярный'|t}</span>
					</div>
				</div>
			{/if}
			<div class="cusongsblock__panel">
				<div class="pull-left cusongsblock-panel__user-name m-hidden oneline-faded w120">
					{if $is_online[$kwork.USERID]}
						<i class="dot-user-status dot-user-online"></i>
					{else}
						<i class="dot-user-status dot-user-offline"></i>
					{/if} <a class="dark-link" href="{$baseurl}/{insert name=get_seo_profile value=a username=$kwork.username|stripslashes}" title="{$kwork.username|stripslashes}">
							{$kwork.username|stripslashes}
					</a>
				</div>
				<div class="pull-right cusongsblock-panel__rating m-pull-reset">
					<ul class="rating-block cusongsblock-panel__rating-list dib">
						{if $kwork.userRating > 0}
							<li class="mr2 v-align-m"><i class="fa fa-star gold" aria-hidden="true"></i> </li>
							<li class="rating-block__rating-item--number fw600 v-align-m">{number_format(round($kwork.userRating/20,1), 1,".","")}</li>
						{elseif $kwork.userRatingCount > 0}
							<li class="rating-block__rating-item--new">{'Новый'|t}</li>
						{/if}
					</ul>
					{if $kwork.userRatingCount > 0}
						<span class="rating-block__count">({$kwork.userRatingCount|shortDigit})</span>
					{/if}
				</div>

				<div class="clear"></div>
			</div>
			<div class="userdata clearfix">
				{if $showStoppedKworks}
					<div class="ta-center mt2">
						<form method="post"
							  name="sendNotice{$kwork.PID}"
							  action="{route route="notify_activate_kwork"}">
							<input type="hidden" name="kworkId" value="{$kwork.PID}">
						</form>
						<button class="white-btn green-btn lh24 mt-1"
										onclick="document.sendNotice{$kwork.PID}.submit();" style="width:100%">{'Отправить уведомление'|t}
						</button>
					</div>
				{else}
					<div class="otherdetails pull-left m-hidden w50">
						<div class="price pull-left m-pull-right">
							{call name=kwork_price kwork=$kwork actor=$actor filterPrice=$filterPrice}
						</div>
					</div>
					<div class="pull-left cusongsblock-panel__user-name m-visible {if $kwork.rating < KworkManager::BEST_RATING} w50p {/if}">
						{if $is_online[$kwork.USERID]}
							<i class="dot-user-status dot-user-online"></i>
						{else}
							<i class="dot-user-status dot-user-offline"></i>
						{/if}
						&nbsp;
						<a class="dark-link dib v-align-m oneline-faded {if $kwork.rating >= KworkManager::BEST_RATING} w100{else} w90p {/if}" href="{$baseurl}/{insert name=get_seo_profile value=a username=$kwork.username|stripslashes}" title="{$kwork.username|stripslashes}">
							{$kwork.username|stripslashes}
						</a>
					</div>
					{/if}
				</div>

		</div>
	</div>
{/strip}
