{strip}
	<div id="track-id-{$track->MID}"
		 class="{if $config.track.isFocusGroupMember}track--item {else} t-align-c {/if} tr-track step-block-order_item checkWork-js{if $isUnread} unread{/if} {$direction} report-message"
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
						<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>
					</div>
                {else}
					<i class="{$icon}"></i>
					<h3 class="track-{$color} mb15 pt10 fw600">
                        {$title}
					</h3>
                {/if}
                {if $config.track.isFocusGroupMember}
				<div class="track--item__content">
                    {/if}
                    {if $phases}
						<div class="{if $config.track.isFocusGroupMember} pt10 pb10 {else}mb15  m-ml0{/if} breakwords">
                            {include file="track/view/report/phases_block.tpl"}
						</div>
                    {else}
						<div class="{if !$config.track.isFocusGroupMember}mb15  m-ml0{/if} breakwords">
							<div {if !$config.track.isFocusGroupMember} class="mt10" {/if}>
								<b>{"Прогресс"|t} <span
											class="js-progress-value">{$track->report->is_executed_on}</span>%&nbsp;</b>
							</div>
                            {include file="track/view/progress_bar.tpl" trackMID=$track->MID progress=$track->report->is_executed_on}
						</div>
                    {/if}
                    {if $config.track.isFocusGroupMember}
					<div class="f15">
                        {$track->message}
					</div>
				</div>
			</div>
            {else}
		</div>
		<div class="f15 mt15">
            {$track->message}
		</div>
        {/if}
	</div>
{/strip}
