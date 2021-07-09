{extends file="page_with_user_header.tpl"}

{* content *}
{block name="content"}
	{Helper::registerFooterJsFile("/js/pages/orders/orders.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/manage_orders.js"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/manage_orders.css"|cdnBaseUrl)}

	{strip}
		<div class="centerwrap clearfix pt20 block-response" style="width: 681px;    left: 260px;">
			<h1 class="f32 orders-title mb5 bold">{'Заказы'|t}</h1>
			{if $orders|@count eq "0" && $searchQuery eq null}
				<div class="mt25 font-OpenSans t-align-c">
					{'Здесь будут отображаться Ваши заказы'|t}
				</div>
			{else}
				<div class="m-hidden pull-right">
					{if $includeLimitField}
						{include file="manage_orders/count_switcher.tpl"}
					{/if}
				</div>

				<div  >
					{if $searchQuery neq null}
						{if $orders|@count > 0}
							<div class="orders-search-result mb20">{"<b>Результаты поиска</b> по запросу:"|t} "{$searchQuery}"</div>
						{else}
							<div class="orders-search-result mb20">{"<b>К сожалению, поиск не дал результатов. Измените запрос.</b> Вы можете искать по названию заказа и/или логину покупателя"|t}</div>
						{/if}
					{/if}

					{if $orders|@count > 0}
						{foreach from=$orders item=order}
							{control name="manage_orders/table_row" order=$order}
						{/foreach}
					{/if}
				</div>
				<div class="t-align-c mb10">
					{insert name=paging_block assign=pages value=a data=$pagingdata}
					{$pages}
				</div>
			{/if}
			<div class="clear"></div>
		</div>
	{/strip}

	{include file="popup/order_change_name.tpl"}
{/block}
{* /content *}
