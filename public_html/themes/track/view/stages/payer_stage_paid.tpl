{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{strip}
		{if $firstStage}
			<div class="f15 fw700 {if !$config.track.isFocusGroupMember} mt15 {/if}">
				<div>
					{'Этап'|t} №{$firstStage->number}. {$firstStage->title} -&nbsp;

					{if isAllowToUser($order->USERID)}
						{$price = $firstStage->payer_price}
					{else}
						{$price = $firstStage->worker_price}
					{/if}
					{include file="utils/currency.tpl" total=$price currencyId=$order->currency_id lang=''}
				</div>
			</div>
			<div class="f15 {if !$config.track.isFocusGroupMember} mt15 {/if}">
				{'Работа может идти параллельно над несколькими задачами, под которые зарезервирована оплата.'|t}
			</div>
		{/if}
	{/strip}
{/block}