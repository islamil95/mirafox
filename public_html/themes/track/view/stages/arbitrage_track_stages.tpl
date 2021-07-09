{strip}
	<div>
		{foreach $track->trackStages as $trackStage}
		<div>
			{'Задача'|t} {$trackStage->stage->number}. {$trackStage->stage->title} -&nbsp;
			{if $trackStage->isArbitragePayer()}
				{'оплата возвращена покупателю'|t}
			{elseif $trackStage->isArbitrageWorker()}
				{'оплата переведена продавцу'|t}
			{elseif $trackStage->isArbitrageHalf()}
				{if isAllowToUser($track->order->worker_id)}
					{assign var=fromPrice value=$trackStage->arbitrage_half_initial_worker_price}
					{assign var=toPrice value=$trackStage->stage->worker_price}
				{else}
					{assign var=fromPrice value=$trackStage->arbitrage_half_initial_payer_price}
					{assign var=toPrice value=$trackStage->stage->payer_price}
				{/if}
				{assign var=fromPrice value=Translations::getPriceWithCurrencySign($fromPrice, $trackStage->stage->currency_id, "₽")}
				{assign var=toPrice value=Translations::getPriceWithCurrencySign($toPrice, $trackStage->stage->currency_id, "₽")}
				{'стоимость задачи изменена с %s на %s'|t:$fromPrice:$toPrice}
			{/if}
		</div>
		{/foreach}
	</div>
{/strip}
