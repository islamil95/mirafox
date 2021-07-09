{extends file="page_with_user_header.tpl"}

{* content *}
{block name="content"}
	{Helper::registerFooterJsFile("/js/pages/orders/orders.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/manage_orders.js"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/manage_orders.css"|cdnBaseUrl)}

	{strip}
		<div class="centerwrap clearfix pt20 block-response" style="width: 681px;    left: 260px;">
			<h1 class="f32 orders-title mb5">{'Заказы'|t}</h1>
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

				<div class="centerwrap clearfix  pb50 block-response m-p0 m-m0">
					{if $searchQuery neq null}
						{if $orders|@count > 0}
							<div class="orders-search-result mb20">{"<b>Результаты поиска</b> по запросу:"|t} "{$searchQuery}"</div>
						{else}
							<div class="orders-search-result mb20">{"<b>К сожалению, поиск не дал результатов. Измените запрос.</b> Вы можете искать по названию заказа и/или логину покупателя"|t}</div>
						{/if}
					{/if}

					{if $orders|@count > 0}
						<table class="table-style m-table-manage-orders m-order-table">
{*							<thead>*}
{*								<tr>*}
{*									<td class="w52p">*}
{*										<a class="pl20 {if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'title'}active{/if}" *}
{*											href="#" data-params="s={$s}&b=title&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*											onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*											{'Название'|t}*}
{*										</a>*}
{*									</td>*}
{*									<td class="{if $s eq 'active'}w5p{else}w19p{/if}">*}
{*										<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'price'}active{/if}" *}
{*											href="#" data-params="s={$s}&b=price&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*											onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*											{'Стоимость'|t}*}
{*										</a>*}
{*									</td>*}
{*									<td class="{if $s eq 'active'}w5p{else}w19p{/if}">*}
{*										<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'user'}active{/if}" *}
{*											href="#" data-params="s={$s}&b=user&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*											onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*											{'Покупатель'|t}*}
{*										</a>*}
{*									</td>*}
{*									<td class="w19p">*}
{*										<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'date'}active{/if}" *}
{*											href="#" data-params="s={$s}&b=date&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*											onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*											{if $s eq 'completed'}*}
{*												{'Оплачено'|t}*}
{*											{elseif $s eq 'cancelled'}*}
{*												{'Отменен'|t}*}
{*											{else}*}
{*												{'Заказан'|t}*}
{*											{/if}*}
{*										</a>*}
{*									</td>*}
{*									{if $s eq 'active'}*}
{*										<td class="w14p">*}
{*											<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'deadline'}active{/if}" *}
{*												href="#" data-params="s={$s}&b=deadline&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*												onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*												{'Осталось'|t}*}
{*											</a>*}
{*										</td>*}
{*									{/if}*}
{*									<td class="w19p  ta-center">*}
{*										<a class="{if $a eq 'asc'}table-style_sort-up{else}table-style_sort-down{/if} {if $b == 'status'}active{/if}" *}
{*											href="#" data-params="s={$s}&b=status&a={if $a eq 'asc'}desc{else}asc{/if}" *}
{*											onclick="location.href = '?' + getUpdatedUrlParamsString(this.getAttribute('data-params'))">*}
{*											{'Статус'|t}*}
{*										</a>*}
{*									</td>*}
{*									<td class="w19p  ta-center">*}
{*										{'Прогресс'}*}
{*									</td>*}
{*								</tr>*}
{*							</thead>*}
							<tbody>
								{foreach from=$orders item=order}
									{control name="manage_orders/table_row" order=$order}
								{/foreach}
							</tbody>
						</table>
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
