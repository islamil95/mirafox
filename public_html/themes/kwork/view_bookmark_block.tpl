{strip}
<div class="pull-right Favorites-block ">
	{if $actor && isNotAllowUser($kwork.USERID)}
		<div class="foxbookmark cur pull-right signout-fav-div">
			<div data-pid="{$kwork.PID}" class="js-bookmark signout-fav-div_right {if $kwork.isBookmark}active{/if}">

				<div class="tooltipster" data-tooltip-content=".kwork-control-{$kwork.PID}">

					<span class="js-icon-heart kwork-icon js-trig-status icon-heart icon-heart_hover {if $kwork.isHidden}hidden{/if}"></span>
					<span class="kwork-icon js-trig-status icon-eye-slash icon-heart_hover active {if !$kwork.isHidden}hidden{/if}"></span>
					<div style="display: none;">
						<div class="js-kwork-control kwork-controls kwork-control-{$kwork.PID}" data-id="{$kwork.PID}">
							<div class="kwork-control">
								<span class="js-icon-heart kwork-icon icon-heart tooltipster notactive {if $kwork.isBookmark}hidden{/if}" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Добавить в избранное'|t}"></span>
								<span class="js-icon-heart kwork-icon icon-heart tooltipster active {if !$kwork.isBookmark}hidden{/if}" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Удалить из избранного'|t}"></span>
							</div>
							<div class="kwork-control">
								<span class="js-kwork-hidden kwork-icon icon-eye-slash tooltipster notactive {if $kwork.isHidden}hidden{/if}" data-tooltip-mhidden="true" data-id="{$kwork.PID}" data-action="add" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Скрыть кворк'|t}"></span>
								<span class="js-kwork-hidden kwork-icon icon-eye-slash tooltipster active {if !$kwork.isHidden}hidden{/if}" data-tooltip-mhidden="true" data-id="{$kwork.PID}" data-action="del" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Вернуть из скрытых'|t}"></span>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
	{elseif $actor && isAllowToUser($kwork.USERID)}
		<div class="signout-fav-div cur pull-right">
			<span class="kwork-icon icon-heart icon-heart_hover tooltipster" data-tooltip-text="{'Вы не можете заносить свои кворки в Избранное'|t}"></span>
		</div>
	{else}
		<div class="signout-fav-div signup-js cur pull-right">
			<span class="kwork-icon icon-heart icon-heart_hover tooltipster" data-tooltip-text="{'Вы сможете заносить кворки в Избранное, когда <a class=\'login-js cur\'>авторизуетесь</a>'|t}" data-tooltip-mhidden="true"></span>
		</div>
	{/if}
	<div class="js-fav-count p0 t-align-c f14 color-gray total-like-count mr10 pull-left {if $kwork.isHidden}hidden{/if} m-hidden">
		{$kwork.bookmark_count}
	</div>
</div>
{/strip}