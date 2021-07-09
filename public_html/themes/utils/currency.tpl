{strip}
	{if $currencyId == \Model\CurrencyModel::USD || $lang == Translations::EN_LANG}
		<span>$</span>
	{/if}
	{if isset($total)}
		{if $span}<span class="funds">{/if}
			{$total|zero}
		{if $span}</span>{/if}
	{/if}
	{if $currencyId == \Model\CurrencyModel::RUB || $lang == Translations::DEFAULT_LANG}
		&nbsp;<span class="rouble">Р</span>
	{/if}
{/strip}