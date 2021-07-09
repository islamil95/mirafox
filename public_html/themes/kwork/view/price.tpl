{strip}
	{if $kwork.lang == Translations::DEFAULT_LANG}
		<span class="newordext">{max($kwork.price, $kwork.min_volume_price)|zero}</span>
		&nbsp;<span class="rouble">Р</span>
	{else}
		<span class="usd">$</span><span class="newordext">{max($kwork.price, $kwork.min_volume_price)|zero}</span>
	{/if}
{/strip}