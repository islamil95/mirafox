{strip}
	{foreach $track->trackStages as $trackStage}
	<div>
		<b> {'Этап'|t} №{$trackStage->stage->number}. {$trackStage->stage->title} -&nbsp;
			{if isAllowToUser($track->order->worker_id)}
				{include file="utils/currency.tpl" total=$trackStage->stage->worker_price currencyId=$track->order->currency_id lang=''}
			{else}
				{include file="utils/currency.tpl" total=$trackStage->stage->payer_price currencyId=$track->order->currency_id lang=''}
			{/if}
		</b>
	</div>
	{/foreach}
{/strip}