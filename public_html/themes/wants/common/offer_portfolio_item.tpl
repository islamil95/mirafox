<div class="portfolio-card-collage ispinner-container" data-id="{$item->id}">

	{* thumbnail *}
	{include file="_blocks/thumbnail_img_load.tpl" spinnerMode="lite"}
	<a href="javascript: portfolioCard.getPortfolio({$item->id});" class="thumbnail-img-link">
		{assign var="imgClass" value=""}
		{if $item->is_resizing == 0}
			{assign var="imageSize" value="t1"}
            {assign var="imageRetinaSize" value="t1_r"}
		{else}
			{assign var="imageSize" value="t0"}
            {assign var="imageRetinaSize" value="t0"}
			{$sizeImage = \CImage::getSizeImage($item->cover[$imageSize])}
			{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
				{assign var="imgClass" value="isHorizontalImg"}
			{/if}
		{/if}
		<img src="{$item->cover[$imageSize]}" srcset="{$item->cover[$imageRetinaSize]} 2x" alt="" class="{$imgClass}" onload="removeISpinner(event)">
		{if $item->videos|@count > 0}
			<i class="ico-play-css ico-play-css_sm" style="cursor:pointer"></i>
		{/if}
	</a>
	<div class="extended-info" onclick="portfolioCard.getPortfolio({$item->id});">
	
		{* name *}
		<div class="extended-info-row trim-text">
			<a href="javascript: portfolioCard.getPortfolio({$item->id});" class="portfolio-name">
				{$item->title|mb_ucfirst|stripslashes}
			</a>
		</div>
		<div class="extended-info-row">
		
			{* category *}
			<div class="portfolio-category trim-text">{$item->category}</div>
			
			{* details *}
			<div class="portfolio-details">				
				<div class="detail detail_comments {if $item->comments_count|zero == 0}hidden{/if}">
					<span class="kwork-icon icon-comment"></span>
					<strong class="count">{$item->comments_count|zero}</strong>
				</div>				
				<div class="detail detail_likes {if $item->likes_dirty|zero == 0}hidden{/if}">
					<span class="kwork-icon icon-thumbs-up"></span>
					<strong class="count">{$item->likes_dirty|zero}</strong>
				</div>	
				<div class="detail detail_views {if $item->views_dirty|zero == 0}hidden{/if}">
					<span class="kwork-icon icon-eye"></span>
					<strong class="count">{$item->views_dirty|zero}</strong>
				</div>				
			</div>
		</div>
	</div>
</div>