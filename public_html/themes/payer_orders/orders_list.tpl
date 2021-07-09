{strip}

	{Helper::registerFooterJsFile("/js/pages/orders/orders.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}

	{if $o|@count > 0}
		<table class="table-style m-table-manage-orders m-order-table m-m0">
			<thead>
				<tr>
					<td class="w52p">
						<div class="ml20">
							<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'title'}active{/if}"
								href="javascript:void(0);"
								data-params="s={$s}&b=title&a={if $a eq 'asc'}desc{else}asc{/if}{if $searchQuery != ''}&search={$searchQuery}{/if}"
								onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
								{'Название'|t}
							</a>
						</div>
					</td>
					<td class="w19p">
						<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'price'}active{/if}"
							href="javascript:void(0);"
							data-params="s={$s}&b=price&a={if $a eq 'asc'}desc{else}asc{/if}{if $searchQuery != ''}&search={$searchQuery}{/if}"
							onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
							{'Стоимость'|t}
						</a>
					</td>
					<td class="w21p">
						<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'user'}active{/if}"
							href="javascript:void(0);"
							data-params="s={$s}&b=user&a={if $a eq 'asc'}desc{else}asc{/if}{if $searchQuery != ''}&search={$searchQuery}{/if}"
							onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
							{'Продавец'|t}
						</a>
					</td>
					{if $s eq 'delivered'}
						<td class="w21p ta-center">
							<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'deadline'}active{/if}"
								href="javascript:void(0);"
								data-params="s={$s}&b=deadline&a={if $a eq 'asc'}desc{else}asc{/if}"
								onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
								{'Осталось на&nbsp;проверку'|t}
							</a>
						</td>
					{else}
						<td class="w21p ta-center">
							<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'date'}active{/if}"
							href="javascript:void(0);"
							data-params="s={$s}&b=date&a={if $a eq 'asc'}desc{else}asc{/if}{if $searchQuery != ''}&search={$searchQuery}{/if}"
							onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
								{$dateHead}
							</a>
						</td>
					{/if}
					<td class="w19p ta-center">
						<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'status'}active{/if}"
							href="javascript:void(0);"
							data-params="s={$s}&b=status&a={if $a eq 'asc'}desc{else}asc{/if}{if $searchQuery != ''}&search={$searchQuery}{/if}"
							onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">
							{'Статус'|t}
						</a>
					</td>
				</tr>
			</thead>
			<tbody>

			{section name=i loop=$o}
				<tr class="m-hidden m-clearfix">
					<td class="ellipsis-wrap">
						<div class="ellipsis ml10" data-id="{$o[i].OID}">
							{include file="payer_orders/block_order_name.tpl" orderName=$o[i].displayTitle orderUrl=$baseurl|cat:'/track?id='|cat:$o[i].OID}

							{if $o[i].canEditName eq 1}
								<i class="change-order-name-js fa fa-pencil tooltipster" data-tooltip-text="{'Изменить название заказа'|t}" rel="{$o[i].OID}"></i>
							{/if}
							{include file="components/orders_row_inbox_ico.tpl" order=$o[i]}
							{if $o[i].project}
								<img style="height:1em;" class="pl10 v-align-m tooltipster"
									alt=""
									src="{"/orderfromrequest_icon.png"|cdnImageUrl}"
									data-tooltip-text='{'Заказ по заданию на бирже'|t} "{$o[i].project|truncate:80:"..."}"'>
							{/if}
							{include file="components/orders_row_promo_ico.tpl" order=$o[i]}
							{include file="components/orders_row_stages_done_text.tpl" order=$o[i]}
						</div>
					</td>
					<td class="pr0">
						{include file="utils/currency.tpl" currencyId=$o[i].currencyId total=$o[i].displayPrice}
						{if $o[i].secondStagesPrice && $o[i].status !== "3" && $o[i].status !== "5"}
							{if $o[i].status eq "6"}
								{$tooltip_text={'Указана стоимость задачи, под который требуется зарезервировать средства. Ниже написана суммарная стоимость заказа.'|t}}
							{else}
								{$count=$o[i]['stagesCount']}
								{$tooltip_text={'Указана стоимость задачи, под который зарезервированы средства. Ниже написана суммарная стоимость заказа.'|tn:$count}}
							{/if}
							<div class="dib ml5 tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover"
								 data-tooltip-text="{$tooltip_text}">?</div>
						<br><span class="nowrap color-gray f13">{'из'|t} {include file="utils/currency.tpl" lang=$actor->lang total=$o[i].secondStagesPrice}</span>
						{/if}
					</td>
					<td class="nowrap">
						{include file="payer_orders/block_user.tpl" username=$o[i].username isOnline=$o[i].is_online}
					</td>

					<td class="ta-center nowrap">
                        {if $o[i].isCancelRequest && $o[i].status eq "4"} {* Запрос на отмену + На проверке *}
							&mdash;
						{elseif $s eq 'delivered'}
							{$o[i].time_until_autoaccept}
						{else}
							{insert name=get_time_to_days_ago value=a time=$o[i].$dateColumn|date}
						{/if}
					</td>
					<td class="pr10">
						{include file="payer_orders/block_status_and_action.tpl"}
					</td>
				</tr>


				{* mobile version *}
				<tr class="m-visible">
					<td>
						<div class="data">
							{if $s eq 'delivered'}
								{$o[i].time_until_autoaccept}
							{else}
								{insert name=get_time_to_days_ago value=a time=$o[i].$dateColumn|date}
							{/if}
						</div>
						<div class="order-name">
							{include file="payer_orders/block_order_name.tpl" orderName=$o[i].gtitle orderUrl=$baseurl|cat:'/track?id='|cat:$o[i].OID}
							{include file="components/orders_row_stages_done_text.tpl" order=$o[i]}
						</div> 
						<div>
							{include file="payer_orders/block_user.tpl" username=$o[i].username isOnline=$o[i].is_online}
						</div>
					</td>
					<td>
						<div>
							{include file="payer_orders/block_status_and_action.tpl"}
						</div>
						<div class="price">
							{include file="utils/currency.tpl" lang=$actor->lang total=$o[i].displayPrice}
							{if $o[i].secondStagesPrice && $o[i].status !== "3" && $o[i].status !== "5"}
								{if $o[i].status eq "6"}
									{$tooltip_text={'Указана стоимость задачи, под которую требуется зарезервировать средства. Ниже написана суммарная стоимость заказа.'|t}}
								{else}
									{$count=$o[i]['stagesCount']}
									{$tooltip_text={'Указана стоимость задачи, под которую зарезервированы средства. Ниже написана суммарная стоимость заказа.'|tn:$count}}
								{/if}
								<div class="dibi ml5 tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover"
									 data-tooltip-text="{$tooltip_text}">?</div>
								<br><span class="nowrap color-gray f13">{'из'|t} {include file="utils/currency.tpl" lang=$actor->lang total=$o[i].secondStagesPrice}</span>
							{/if}
						</div>
					</td>
				</tr>
			{/section}
			</tbody>
		</table>
	{/if}

	<div style="text-align:center;" class="mb10">
		{insert name=paging_block assign=pages value=a data=$pagingdata}
		{$pages}
	</div>

	{include 'order-more-modal.tpl'}

{/strip}
