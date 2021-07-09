{strip}
<div id="track-id-{$track->MID}"
	 class="{if $config.track.isFocusGroupMember} track--item tr-track {/if} step-block-order_item checkWork-js{if $isUnread} unread{/if}{if $track->getHide()} hide{/if} {$direction}"
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
				<h3 class="track-{$color} pt10 font-OpenSansSemi">
                    {$title}
				</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
                {if $hasMultipleStages}
					<div class="f15 {if !$config.track.isFocusGroupMember} mt15 {/if}">
                        {if $isPayerSee}
                            {'Вы вернули задачи на доработку:'|t}
                        {else}
                            {'Покупатель вернул сданную работу по задачам на доработку:'|t}
                        {/if}
					</div>
                    {include file="track/view/stages/track_stages_table.tpl" progress=\Model\OrderStages\OrderStage::PROGRESS_REWORK}
					<div class="f15  {if !$config.track.isFocusGroupMember} mt15 {/if}">
                        {if $isPayerSee}
                            {'Продавец внесет исправления и снова отправит работу на проверку.'|t}
                        {else}
                            {'Пожалуйста, исправьте недочеты и сдайте работу вновь.'|t}
                        {/if}
					</div>
                {else}
					<div class="f15  {if !$config.track.isFocusGroupMember} mt15 {/if}">
                        {if $isPayerSee}
                            {'Вы вернули задачу №%s «%s» на доработку.'|t:$firstStage->number:$firstStage->title}
							<br>
                            {'Продавец внесет исправления и снова отправит работу на проверку.'|t}
                        {else}
                            {'Покупатель вернул сданную работу по задаче №%s «%s» на доработку.'|t:$firstStage->number:$firstStage->title}
							<br>
                            {'Пожалуйста, исправьте недочеты и сдайте работу по задаче снова.'|t}
                        {/if}
					</div>
                {/if}
                {if !empty($track->message)}
					<div class="f15 mt15 mb15 breakwords pre-wrap m-ml0">
						<i>{"Комментарий:"|t} {$track->message|bbcode|stripslashes|code_to_emoji}</i>
					</div>
                {/if}
                {if $track->files->isNotEmpty()}
					<div class="track-files pl80 mb20">
                        {include file="track/view/files.tpl" files=$track->files}
					</div>
                {/if}
                {if !$isPayerSee && $track->isFirstRework()}
                    {include file="track/view/loyality/loyality_rework.tpl" loyalityVisible=true}
                {/if}
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
		</div>
	</div>
    {/strip}

