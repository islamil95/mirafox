{strip}
<div id="track-id-{$track->MID}"
	 class="{if $config.track.isFocusGroupMember} track--item{/if} tr-track step-block-order_item{if $isUnread} unread{/if} {$direction}"
	 data-track-id="{$track->MID}">
    {if !$config.track.isFocusGroupMember}
	<div class="step-block-order_item_body font-OpenSans">
        {/if}
        {if $config.track.isFocusGroupMember}
			<div class="track--item__sidebar">
				<div class="track--item__sidebar-image {$color}">
					<svg width="25" height="25" viewBox="0 0 25 25">
						<use xlink:href="#ico-red-extras"></use>
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
                            {'Покупка дополнительной опции отменена'|t}
						</h3>
						<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>
					</div>
                {else}
					<i class="ico-red-extras"></i>
					<h3 class="pt10 font-OpenSansSemi track-red">
                        {'Покупка дополнительной опции отменена'|t}
					</h3>
                    {if isAllowToUser($track->order->USERID)}
						<h3 class="pt10 font-OpenSansSemi fs15"
							style="font-weight: 400;">
                            {'Деньги возвращены на ваш баланс'|t}
						</h3>
                    {else}
						<h3 class="pt10 font-OpenSansSemi fs15"
							style="font-weight: 400;">
                            {'Деньги возвращены на баланс покупателя'|t}
						</h3>
                    {/if}
                {/if}
                {if !$config.track.isFocusGroupMember}
			</div>
            {else}
			<div class="track--item__content">
                {if isAllowToUser($track->order->USERID)}
					<p>
                        {'Деньги возвращены на ваш баланс'|t}
					</p>
                {else}
					<p>
                        {'Деньги возвращены на баланс покупателя'|t}
					</p>
                {/if}

                {/if}
				<table style="width:100%;border-spacing:0px;border:1px solid #f0f0f0" class="mt10">
					<thead style="background-color:#f0f0f0;" class="font-OpenSansSemi">
					<td style='padding:10px 20px;'>{'Опция'|t}</td>
					<td class="ta-center nowrap">{'Кол-во'|t}</td>
					<td class="ta-center nowrap">{'Срок'|t}</td>
					<td class="ta-center">{'Стоимость'|t}</td>
					</thead>
					<tbody>
                    {foreach $track->extras as $extra}
						<tr class="option font-OpenSans">
							<td style="padding:10px 20px;">
                                {$extra->extra_title|mb_ucfirst}
							</td>
							<td class="ta-center">
                                {if $extra->volume}
                                    {$extra->custom_volume|zero:1}
                                {else}
                                    {$extra->count}
                                {/if}
							</td>
							<td class="ta-center">
                                {$extra->extra_duration|zero} {declension count=$extra->extra_duration form1="день" form2="дня" form5="дней"}
							</td>
							<td class="ta-center">
                                {if isAllowToUser($track->order->USERID)}
                                    {include file="utils/currency.tpl" lang=$order->getLang() total=$extra->buyerPrice()}
                                {else}
                                    {include file="utils/currency.tpl" lang=$order->getLang() total=$extra->workerPrice()}
                                {/if}
							</td>
						</tr>
                    {/foreach}
					</tbody>
				</table>
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
            {if !$config.track.isFocusGroupMember}
		</div>
        {/if}
	</div>
    {/strip}
