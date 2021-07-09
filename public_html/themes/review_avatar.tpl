{strip}
<div class="review-avatar kwork-review-avatar{if $badgeId} tooltipster" data-tooltip-content=".level-tooltip-{$reviewId}" data-tooltip-destroy="true" data-tooltip-side="bottom{/if}">
	{if $badgeId}
		{include file="user/small_tooltip.tpl" class="level-tooltip-{$reviewId}" title=$badgeName badge=$badgeId super=$badgeSuper}
		<div class="level">
			{if $pageSpeedDesktop && $pageName == "view" && $reviewsBlock == "kwork_reviews"}
				<img class="lazy-load_scroll" src="{"/blank.png"|cdnImageUrl}" data-src="{"/badge/{$badgeId}s.png"|cdnImageUrl}" alt="">
			{else}
				<img src="{"/badge/{$badgeId}s.png"|cdnImageUrl}" alt="">
			{/if}
		</div>
	{/if}
	{include file="user_avatar.tpl" profilepicture=$userAvatar username=$userName size="medium"}
</div>
{/strip}