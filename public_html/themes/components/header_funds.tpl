{strip}
<div class="pull-right droparrow pricebox ml15" style="background: none;">
	<a href="{$baseurl}/balance" class="color-white font-OpenSans f20 underline-hover js-user-purse_link">
		{include file="utils/currency.tpl" lang=$actor->lang total=$actor->totalFunds|floor span=true}
	</a>
	<div class="dropdownbox dropdownbox-balance" style="right:{if $actor->totalFunds < 10}-30px
		{elseif $actor->totalFunds >= 10 && $actor->totalFunds < 100}-25px
		{elseif $actor->totalFunds >= 100 && $actor->totalFunds < 1000}-20px
		{elseif $actor->totalFunds >= 1000 && $actor->totalFunds < 10000}-15px
		{elseif $actor->totalFunds >= 10000 && $actor->totalFunds < 100000}-10px
		{elseif $actor->totalFunds >= 100000 && $actor->totalFunds < 1000000}-5px
		{else}0px
		{/if};">
		<div style="text-align: center; margin-top: 10px;">
			<div class="green-btn" style="width: 90%;" onclick="toggleBalanceRefillPopup();
				show_balance_popup('', 'balance');">{'Пополнить баланс'|t}</div>
		</div>
	</div>
</div>
{/strip}