{assign var="reorderModal" value=(!$order->has_stages && $isStageTester && $order->isDone())}
<div data-kwork-id="{$order->PID}"
		{if $order->orderPackage}
			{if $order->orderPackage->type}
				data-package="{$order->orderPackage->type}"
				{else}
					data-package="standard"
				{/if}
			{/if}
		data-order-id="{$order->OID}"
		data-same-page="true"
		data-stages-type="{if $order->has_stages}1{else}0{/if}"
		data-quick="{$order->is_quick}"
		class="green-btn sx-wMax sx-mb5 pull-left{if $addMargin} mr20{/if}{if ($order->kwork->lang != Translations::getLang()) || ($order->kwork->active != 1 && $order->kwork->active != 5)} tooltipster disabled{else}{if $reorderModal} js-order-more-link{else} js-reorder{/if}{/if}"
		{if $order->kwork->lang != Translations::getLang()}
				data-tooltip-text="{'Данный кворк можно заказать только на &quot;%s&quot;'|t:Translations::translateCurrentHost()}"
		{elseif $order->kwork->active != 1 && $order->kwork->active != 5}
				data-tooltip-text="{'Данный кворк временно не продается'|t}"
			{else}
				{if !$reorderModal}data-form="new-order-js"{/if}
			{/if}
>
	{if $order->has_stages}
		{'Добавить и активировать задачу'|t}
	{else}
		{'Заказать еще'|t}
	{/if}
</div>
