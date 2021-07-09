{strip}
<table class="wMax">
	<tbody>
	{if $wants && count($wants) > 0}
		{foreach $wants as $want}
			{include file="wants/payer/manage/want_item.tpl"}
		{/foreach}
		{include file="wants/payer/manage/create_want_small.tpl"}
	{else}
		{include file="wants/payer/manage/create_want_block.tpl"}
	{/if}
	</tbody>
</table>
{/strip}
