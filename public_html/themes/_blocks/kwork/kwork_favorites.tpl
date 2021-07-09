{strip}
	{if $actor && $actor->id != $kwork.USERID}
		<div data-id="{$kwork.PID}" class="cusongsblock__favorites js-heart-block pull-left {if $kwork.isBookmark}active{/if}">
			<div class="tooltipster dib" data-tooltip-content=".kwork-control-{$kwork.PID}">
				{if $kwork.isHidden}<span class="kwork-icon icon-eye-slash icon-eye-slash-card active"></span>{/if}
				<span class="js-icon-heart-card cur kwork-icon icon-heart icon-heart_hover icon-heart-card {if $kwork.isHidden}hidden{/if}"></span>
				<div style="display: none;">
					<div class="js-kwork-control kwork-controls kwork-control-{$kwork.PID}" data-id="{$kwork.PID}">
						<div class="kwork-control">
							<span class="js-icon-heart-card kwork-icon icon-heart{if !$isMobile} tooltipster{/if}{if $kwork.isBookmark} hidden{/if}" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Добавить в избранное'|t}"></span>
							<span class="js-icon-heart-card kwork-icon icon-heart{if !$isMobile} tooltipster{/if} active{if !$kwork.isBookmark} hidden{/if}" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Удалить из избранного'|t}"></span>
						</div>
						<div class="kwork-control">
							<span class="js-kwork-hidden-card kwork-icon icon-eye-slash{if !$isMobile} tooltipster{/if}{if $kwork.isHidden} hidden{/if}" data-id="{$kwork.PID}" data-action="add" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Скрыть кворк'|t}"></span>
							<span class="js-kwork-hidden-card kwork-icon icon-eye-slash{if !$isMobile} tooltipster{/if} active{if !$kwork.isHidden} hidden{/if}" data-id="{$kwork.PID}" data-action="del" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Вернуть из скрытых'|t}"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	{elseif $actor && $actor->id == $kwork.USERID}
		<div class="Favorites-block cusongsblock__favorites pull-left">
			<div class="signout-fav-div">
				<span class="kwork-icon icon-heart icon-heart-card icon-heart_hover tooltipster" data-tooltip-text="{'Вы не можете заносить свои кворки в Избранное'|t}"></span>
			</div>
		</div>
	{else}
		<div class="Favorites-block cusongsblock__favorites pull-left signup-js">
			<div class="signout-fav-div">
				<span class="kwork-icon icon-heart icon-heart-card icon-heart_hover{if !$isMobile} tooltipster{/if}" data-tooltip-text="{'Вы сможете заносить кворки в Избранное, когда <a class=\'login-js cur\'>авторизуетесь</a>'|t}"></span>
			</div>
		</div>
	{/if}
{/strip}
