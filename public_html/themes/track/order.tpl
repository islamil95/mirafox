{strip}
	{assign var="showSpoiler" value=(isset($showDescription) && $showDescription) }
	{assign var="hideSpoiler" value=(!isset($showDescription) || !$showDescription) }
	{assign var="showFullInfo" value=(!isset($noFullInfo) || !$noFullInfo) }
	{assign var="hideFullInfo" value=(isset($noFullInfo) && $noFullInfo) }

	{if $order->status != 0}
	<table class="order-info {if $hideSpoiler && $showFullInfo || ($hideFullInfo && $order->orderPackage)}order-info--hide{/if} {if $hideFullInfo && !$order->orderPackage}order-info--hide-description{/if}">
		<thead>
		<tr>
			<td class="order-info__head order-info__head--title">{'Услуги'|t}</td>
			<td class="order-info__head order-info__head--quantity {if !$order->data->volumeType || !($order->data->volume > 0)}nowrap{/if}">
				<span>
                {if $order->getOrderVolumeType() && $order->data->volume > 0}
									{'Кол-во'|t} {$order->getOrderVolumeType()->name_plural_11_19}
								{else}
									{'Кол-во'|t}
								{/if}
				</span>
			</td>
			<td class="order-info__head order-info__head--time">{'Срок'|t}</td>
			<td class="order-info__head order-info__head--price">{'Стоимость'|t}</td>
		</tr>
		</thead>
		<tbody>
		<tr class="kwork">
			<td class="order-info__title">
				{if $hideFullInfo}
					<span class="break-word">
						{$order->kwork_title|mb_ucfirst}
					</span>
				{else}
					<span class="js-link-description-order order-info__title-spoiler link-color break-word">
						{$order->kwork_title|mb_ucfirst}&nbsp;<img src="{"/arrow_right_blue.png"|cdnImageUrl}" width="9" alt=""/>
					</span>
				{/if}
			</td>
			<td class="order-info__quantity">
				{include file="track/order_quantity.tpl"}
			</td>
			<td class="order-info__time">
				<div class="order-info__time--total">
					{if $order->orderPackage}
						{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration" only_days=1}
					{else}
						{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration"}
					{/if}
					{$timeLeft}
				</div>
			</td>
			<td class="order-info__price">
				<div class="js-order-total-price order-info__price--current">
					{if $order->currency_id == \Model\CurrencyModel::USD}
						<span class="usd">$</span>
					{/if}
					{if $order->has_stages && $order->isNotNew()}
						{*Для задачный заказов используем stages_price*}
						{if isAllowToUser($order->USERID) || $priceFor == "payer"}
							{$order->stages_price - $order->getExtrasPricesForBuyer()|zero}
						{else}
							{$order->stages_crt - $order->getExtrasPricesForWorker()|zero}
						{/if}
					{else}
						{if isAllowToUser($order->USERID) || $priceFor == "payer"}
							{$order->price-$order->getExtrasPricesForBuyer()|zero}
						{else}
							{$order->crt-$order->getExtrasPricesForWorker()|zero}
						{/if}
					{/if}
					{if $order->currency_id == \Model\CurrencyModel::RUB}
						&nbsp;
						<span class="rouble">Р</span>
					{/if}
				</div>
				<div class="order-info__price--total">
					{include file="track/order_total_price.tpl"}
				</div>
			</td>
		</tr>
		{if $showFullInfo}
			<tr class="order-info__description">
				<td colspan="4">
					<div class="shortened-url-track">
						<p class="bold db">
							{if $order->kwork->isCustom()}
								{'Условия заказа'|t}
							{else}
								{'Описание кворка'|t}
							{/if}
						</p>
						{$order->data->kwork_desc|stripslashes|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|replace_full_urls}
					</div>
					{if $order->data->kwork_link_type}
						<br>
						<div>
							<span class="bold">{'Источник ссылок:'|t} </span>{$order->data->kwork_link_type|stripslashes}
						</div>
					{/if}
					{if !$order->orderPackage}
						{if  $order->data->kwork_work || ($order->data->volumeType && $order->data->kwork_volume)}
							<br>
							<div>
								<span class="bold">{'Объем услуги в кворке:'|t}&nbsp;</span>{if $order->volumeType && $order->kwork_volume}
									{$order->data->kwork_volume|zero} {$order->data->volumeType->getPluralizedName($order->data->kwork_volume)}
								{else}
									{$order->data->kwork_work|stripslashes}
								{/if}
								{if $order->bonus_text}
									<span class="order_promo_text"> + {$order->bonus_text}</span>
								{/if}
							</div>
						{/if}
					{/if}
				</td>
			</tr>
		{/if}
		<tr class="order-info__link-hide">
			<td>
				<a class="js-link-description-order dib m-hidden track-link-show-hide" data-type="hide">
					{'Свернуть'|t}<img src="{"/arrow_right_blue.png"|cdnImageUrl}" class="rotate180" width="9" alt=""/>
				</a>
			</td>
		</tr>
		{if !$order->has_stages}
			<tr class="order-info__line m-hidden">
				<td colspan="4">
					<div></div>
				</td>
			</tr>
		{/if}
		<tr class="order-info__total">
			<td></td>
			<td class="order-info__total-title">
				{'Итого:'|t}
			</td>
			<td class="js-order-duration order-info__total-time">
				{if $order->orderPackage}
					{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration" only_days=1}
				{else}
					{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration"}
				{/if}
				{$timeLeft}
			</td>
			<td class="js-order-total-price order-info__total-price">
				{include file="track/order_total_price.tpl"}
			</td>
		</tr>
		<tr class="order-info__total order-info__total--time">
			<td></td>
			<td class='order-info__total-title' style="padding-top:0;">
				{'Срок:'|t}
			</td>
			<td class="hidden"></td>
			<td class='order-info__total-time' style="padding-top:0;">
				{if $order->orderPackage}
					{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration" only_days=1}
				{else}
					{insert name=countdown_short value=a assign=timeLeft time=$order->duration type="duration"}
				{/if}
				{$timeLeft}
			</td>
		</tr>
		</tbody>
	</table>
	{/if}
{/strip}
