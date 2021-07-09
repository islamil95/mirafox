{strip}
<div class="pull-right droparrow pricebox ml15" style="background: none; margin-right: 15px;">
	<a href="{$baseurl}/balance" class="color-white font-OpenSans f20 underline-hover js-user-purse_link">
		{include file="utils/currency.tpl" lang=$actor->lang total=$actor->totalFunds|floor span=true}
	</a>
</div>
{/strip}