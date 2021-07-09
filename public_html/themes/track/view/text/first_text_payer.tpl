{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{* подсказка покупателю *}
	{include file="track/view/advice/payer.tpl"}
	<hr class="gray  {if $config.track.isFocusGroupMember}mb10 mt10{/if}">
	<div class="db f12 color-gray v-align-m ml10  {if $config.track.isFocusGroupMember}mb10 {else} mt15{/if}">
		{"Если продавец не начнет работу над заказом в течение %s, заказ будет отменен автоматически."|t:\Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN)}
	</div>
{/block}