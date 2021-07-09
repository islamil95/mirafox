{strip}
<div {if $setTrackId} id="track-id-{$track->MID}"{/if}
		class="{if $config.track.isFocusGroupMember} track--item {/if}tr-track step-block-order_item{if $isUnread} unread{/if}{if $track->getHide()} hide{/if}  {$direction}"
		data-track-id="{$track->MID}">
    {block name="upperContent"}{/block}
    {if !$config.track.isFocusGroupMember}
        {if !$hideDate}
			<div class="f14 color-gray mt3 t-align-r">{$date|date}</div>
        {/if}
    {/if}

    {if $config.track.isFocusGroupMember}
		<div class="track--item__sidebar">
			<div class="track--item__sidebar-image {$color}">
				<svg width="25" height="25" viewBox="0 0 25 25">
					<use xlink:href="#{$icon}"></use>
				</svg>
			</div>
		</div>
    {/if}
    {if $config.track.isFocusGroupMember}
	<div class="track--item__main">
        {else}
		<div class="t-align-c">
            {/if}
            {if $config.track.isFocusGroupMember}
				<div class="track--item__title {if (mb_strlen($title) > 70)}two-lines{/if}">
					<h3 class="f15 bold">
                        {$title}
					</h3>
                    {if !$hideDate}
						<div class="track--item__date color-gray">{$date|date:"H:i"}</div>
                    {/if}
				</div>
            {else}
				<i class="{$icon}"></i>
				<h3 class="track-{$color} pt10 fw600 mb10">
                    {$title}
				</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
                {block name="mainContent"}{/block}
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
		</div>
	</div>
    {block name="lowContent"}{/block}
{/strip}