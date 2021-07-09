{extends file="track/view/text/text.tpl"}

{block name="additional"}
	{if ! $track->getSkipAdditional()}
		{if $track->order->isPayer($actor->id)}
			{include file="track/view/text/first_text_payer.tpl"}
		{else}
			{include file="track/view/text/first_text_worker.tpl"}
		{/if}
	{/if}
{/block}