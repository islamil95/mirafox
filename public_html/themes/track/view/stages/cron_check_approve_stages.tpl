{strip}
<div id="track-id-{$track->MID}"
        {if $config.track.isFocusGroupMember}
			data-type="{$track->type}"
        {/if}
	 class="{if $config.track.isFocusGroupMember}track--item{/if} step-block-order_item checkWork-js{if $isUnread} unread{/if}{if $track->getHide()} hide{/if}  {$direction}"
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
                        {if $hasMultipleStages}
                            {'Задачи выполнены'|t}
                        {else}
                            {'Задача выполнена'|t}
                        {/if}
					</h3>
					<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>
				</div>
            {else}
				<i class="{$icon}"></i>
				<h3 class="track-{$color} pt10 font-OpenSansSemi">
                    {if $hasMultipleStages}
                        {'Задачи выполнены'|t}
                    {else}
                        {'Задача выполнена'|t}
                    {/if}
				</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
                {if $hasMultipleStages}
					<div class="f15 mt15">
                        {if $isPayerSee}
							{'По задачам, перечисленным ниже, продавец сдал вам выполненную работу на проверку, а вы не приняли или не отклонили ее в течение %s. Задачи приняты автоматически.'|t:$checkPeriod}
                        {else}
                            {'Задачи приняты автоматически:'|t}
                        {/if}

					</div>
                    {include file="track/view/stages/track_stages_table.tpl" progress=100}
					<div class="f15 mt15">
                        {if $isPayerSee}
                            {'Оплата по задачам переведена продавцу.'|t}
                        {else}
                            {'Оплата по задачам переведена на ваш баланс.'|t}
                        {/if}
					</div>
                {else}
					<div class="f15 mt15">
                        {if $isPayerSee}
							{'Задача №%s «%s» принята автоматически, т.к. продавец сдал вам выполненную работу на проверку, а вы не приняли или не отклонили ее в течение %s.'|t:$firstStage->number:$firstStage->title:$checkPeriod}
							<br>
                            {'Оплата по задаче переведена продавцу.'|t}
                        {else}
                            {'Задача №%s «%s» принята автоматически.'|t:$firstStage->number:$firstStage->title}
							<br>
                            {'Оплата по задаче переведена на ваш баланс.'|t}
                        {/if}

					</div>
                {/if}
				<div class="f15 {if $config.track.isFocusGroupMember} mt10 {else} mt15 mb15 {/if}breakwords pre-wrap m-ml0">
					<i>{$track->message|bbcode|stripslashes}</i>
				</div>
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
		</div>
	</div>
    {/strip}
