<div class="btn-group track-page-group-btn" style="vertical-align: top;">
	{if $showWriteMessageButton}
		<div class="green-btn js-toggle-message-button sx-wMax mr20 pull-left hidden" data-form="send-message-js" {if $hideForm}style="display:none;"{/if}>{'Написать сообщение'|t}</div>
		{if $type_form == "pass-work"}
			<div class="orange-btn js-toggle-message-button inactive sx-wMax hidden" data-form="pass-work-js" {if $hideForm}style="display:none;"{/if}>{'Сдать выполненную работу'|t}</div>
		{/if}
	{elseif $type_form == "status-done"}
		{if $canReOrder && (!$order->has_stages || ($order->has_stages && $customMaxPriceAdd >= $customMinPriceAdd && $order->isCanStagesReserved()))}
			{include './reorder_button.tpl' addMargin=true}
		{/if}
	{/if}
</div>