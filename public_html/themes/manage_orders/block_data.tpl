{strip}
	{if $s eq 'cancelled'}
		{$order.date_cancel|date}
	{else}
		{$order.stime|date}
	{/if}
{/strip}