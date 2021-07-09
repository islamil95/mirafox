{strip}
	{capture assign=priceWithSign}
		{if $kwork.lang == Translations::DEFAULT_LANG}
			{$price|zero}&nbsp;{'руб.'|t}
		{else}
			${$price|zero}
		{/if}
	{/capture}
	{capture assign=tooltipText}
		{'За %s вы получите %s %s. Если требуется больший объем, впишите его в поле. Цена заказа пересчитается. Минимальная цена заказа %s'|t:$priceWithSign:$minVolume:$volumeType->getPluralizedNameGenetive($minVolume):$priceWithSign}
	{/capture}
	<span class="kwork-icon icon-custom-help tooltipster tooltip_circle--size14 tooltip_circle--hover" data-tooltip-side="right" data-tooltip-text="{$tooltipText}" data-tooltip-theme="light"></span>
{/strip}