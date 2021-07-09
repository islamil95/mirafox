{extends file="wants/common/offer_item.tpl"}
{block name="offer_item_class"}{if $offer->status == 'cancel' && ($offer->isActual() || ($offer->isNotActual() && $want->status == 'archived'))}hidden offer-item_cancel{elseif $offer->highlighted}offer-item-highlighted{/if}{/block}
{block name="bottom"}
	{strip}
	<div class="offer-item__bottom clearfix">
        {if $config.chat.isFocusGroupMember}
            {assign var="hasConversation" value=(InboxManager::getConversationCount($offer->user_id) > 0)}
			<a class="white-btn offer-item__bottom-btn js-sms-verification-action" href="javascript:void(0)" onclick="firstConversationMessage({$hasConversation|boolval|@json_encode}, '/inbox/{$offer->user->username|lower}?offerId={$offer->id}', {$offer->user_id|intval}, '');">
                {'Связаться c продавцом'|t}
			</a>
		{else}
			<a class="white-btn offer-item__bottom-btn js-sms-verification-action" href="/conversations/{$offer->user->username|lower}?offerId={$offer->id}&goToLastUnread=1">
                {'Связаться c продавцом'|t}
			</a>
		{/if}
		{if $wantWorkersOrders[$offer->user_id]}
			<a class="offer-item__bottom-link" href="/track?id={$wantWorkersOrders[$offer->user_id]}">{'Заказ создан'|t}</a>
		{elseif $offer->kwork->isCustom() && $isStageTester}
			<div class="offer-item__bottom-btn">
				{if $want->isArchive() || $offer->isNotActual()}
					<button class="green-btn tooltipster disabled" type="button" data-tooltip-text="{'Предложение могло потерять актуальность. Свяжитесь с продавцом, чтобы сделать заказ.'|t}">{'Заказать'|t}</button>
				{else}
					<button class="js-send-offer green-btn"
							data-offer-action="{route route="order_by_offer"}"
							data-offer-id="{$offer->id}"
							data-offer-want-id="{$want->id}"
							data-offer-order-id="{$offer->order_id}"
							data-offer-lang="{$offerLang}"
							data-offer-price="{$offer->order->price}"
							data-offer-stages='{$offer->order->stages|@json_encode|htmlspecialchars}'
							data-offer-duration='{$offer->order->duration}'
							data-offer-initial-duration='{if $offer->order->initial_duration}{$offer->order->initial_duration}{else}{$offer->order->duration}{/if}'
							data-offer-stages-max-increase-days="{$want->category->max_days}"
							data-offer-stages-max-decrease-days="{$offer->order->getMaxDecreaseDays()}"
							data-offer-stages-price-threshold="{Order\Stages\OrderStageManager::getPriceThreshold($offerLang)}"
							data-offer-max-stages="{$maxKworkCount}"
							data-offer-stages-price-threshold="{$maxKworkCount}"
							data-offer-custom-min-price="{if $offer->order->initial_offer_price|round:0}{$offer->order->initial_offer_price|round:0}{else}{$offer->order->price|round:0}{/if}"
							data-offer-custom-max-price="{$customMaxPrice}"
							data-offer-stage-min-price="{$stageMinPrice}"
							data-count-stages="{count($offer->order->stages)}"
							data-control-en-lang="{$controlEnLang}">
                        {if $offer->order->price < Order\Stages\OrderStageManager::getPriceThreshold($offerLang)}
                            {'Заказать за'|t} {include file="utils/currency.tpl" total=$offer->order->price currencyId=$offer->order->currency_id}
                        {else}
                            {'Заказать'|t}
                        {/if}
					</button>
				{/if}
			</div>
		{else}
			<div class="offer-item__bottom-btn">
				{if $want->isArchive() || $offer->isNotActual()}
					<button class="green-btn tooltipster disabled" type="button" data-tooltip-text="{'Предложение могло потерять актуальность. Свяжитесь с продавцом, чтобы сделать заказ.'|t}">
						{'Заказать за'|t} {include file="utils/currency.tpl" total=$offer->order->price currencyId=$offer->order->currency_id}
					</button>
				{else}
					<form action="{route route="order_by_offer"}" method="post" name="order" class="js-form-project-order">
						<input type="hidden" name="order_id" value="{$offer->order_id}">
						<input type="hidden" name="id" value="{$want->id}">
						<button class="green-btn" type="submit">
							{'Заказать за'|t} {include file="utils/currency.tpl" total=$offer->order->price currencyId=$offer->order->currency_id}
						</button>
					</form>
				{/if}
			</div>
		{/if}
	</div>
	{/strip}
{/block}
{block name="thumb_buttons"}
	{strip}
	<div class="d-flex justify-content-between align-items-center">
		<div>
			<div class="js-highlight-offer offer-thumb-button {if $offer->highlighted}choosed{/if}" title="{'Выделить отклик'|t}">
				<div class="relative kwork-icon icon-thumbs-up"></div>
			</div>
			<div class="js-hide-offer-btn offer-thumb-button {if $offer->status == 'cancel'}choosed{/if}" title="{'Скрыть отклик'|t}">
				<div class="relative kwork-icon icon-thumbs-down"></div>
			</div>
		</div>
	</div>
	{/strip}
{/block}
