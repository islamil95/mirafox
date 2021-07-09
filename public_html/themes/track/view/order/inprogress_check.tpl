{strip}
{* Продавец отправил работу на проверку *}
<div id="track-id-{$track->MID}"
	 class="{if $config.track.isFocusGroupMember} track--item {/if} tr-track step-block-order_item inprogress-check checkWork-js{if $isUnread} unread{/if}{if $track->getHide()} hide{/if} {$direction}"
	 data-user-id="{$track->order->worker_id}"
	 data-track-id="{$track->MID}">
    {if $config.track.isFocusGroupMember}
		<div class="track--item__sidebar">
			<div class="track--item__sidebar-image {$color}">
				<svg width="25" height="25" viewBox="0 0 25 25">
					<use xlink:href="#{$icon}"></use>
				</svg>
			</div>
		</div>
    {else}
		<div class="f14 color-gray mt3 t-align-r">{$date|date}</div>
    {/if}
    {if $config.track.isFocusGroupMember}
	<div class="track--item__main">
        {else}
		<div class="t-align-c">
            {/if}
            {if $config.track.isFocusGroupMember}
				<div class="track--item__title">
					<h3 class="f15 bold">
                        {$title}
					</h3>
                    {if !$hideDate}
						<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>
                    {/if}
				</div>
            {else}
				<i class="{$icon}"></i>
				<h3 class="track-{$color} pt10 fw600">
                    {$title}
				</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
                {if $hasStages}
                    {if $hasMultipleStages}
                        {include file="track/view/stages/track_stages_table.tpl" checked=true progress=100 showCheckbox=isAllowToUser($track->order->USERID)}
                    {else}
						<div class="f15 mt15 fw700">
                            {'Этап'|t} №{$firstStage->number}. {$firstStage->title}
						</div>
                    {/if}
                {/if}
				<div class="{if !$config.track.isFocusGroupMember} f15 mt15 {$messageClasses} mb15  m-ml0 {/if} breakwords pre-wrap">
					<i>{$track->message|bbcode|stripslashes|code_to_emoji}</i>
				</div>
                {if $config.track.isFocusGroupMember}
				<div class="t-align-c mt10">
                    {/if}
                {if $haveActions}
                    {if $hasStages}
                        {include file="track/view/actions/check_stages_actions_payer.tpl" enabled=true}
                    {else}
                        {include file="track/view/actions/check_actions_payer.tpl"}
                    {/if}
                {/if}
                {if $config.track.isFocusGroupMember}
				</div>
                    {/if}
                {if $infoForWorker}
                    {if $hasStages}
                        {include file="track/view/actions/check_stages_actions_worker.tpl"}
                    {else}
                        {include file="track/view/actions/check_actions_worker.tpl"}
                    {/if}
                {/if}
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}

		</div>
	</div>
    {/strip}
