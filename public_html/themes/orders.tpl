{extends file="page_with_user_header.tpl"}

{block name="content"}

	{Helper::registerFooterJsFile("/js/dist/orders.js"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/orders.css"|cdnBaseUrl)}

	{strip}
		<div>
			<div class="orders-page centerwrap clearfix pt20 block-response">
				<h1 class="f32 orders-title">{'Мои заказы'|t}</h1>
			</div>
			<div class="centerwrap clearfix mt20 pb50 block-response m-p0 m-m0">
				{if $searchQuery neq null}
					<div class="orders-search-result mb20">
						{if $o|@count > 0}
							{'<b>Результаты поиска</b> по запросу: "%s"'|t:$searchQuery}
						{else}
							{'<b>К сожалению, поиск не дал результатов. Измените запрос.</b> Вы можете искать по названию заказа и/или логину продавца'|t}
						{/if}
					</div>
				{/if}

				{if $orders|@count eq "0" && $searchQuery eq null}
					{include file="wants/payer/manage/create_want_block.tpl"}
				{elseif $orders|@count}
					{include file="payer_orders/orders_list.tpl"}
				{/if}
			</div>
		</div>
	{/strip}

	{include file="popup/order_change_name.tpl"}
{/block}