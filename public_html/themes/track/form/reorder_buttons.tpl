<div class="btn-group track-page-group-btn dib" style="vertical-align: top;">
	{if $canSendPortfolio}
		<div class="orange-btn js-toggle-message-button sx-wMax mr20 pull-left inactive {if $order->portfolio}hide-track-button{/if}" data-form="send-portfolio-js">{'Загрузить портфолио'|t}</div>
	{/if}
	{if !$order->has_stages || ($order->has_stages && $customMaxPriceAdd >= $customMinPriceAdd && $order->isCanStagesReserved())}
		{include './reorder_button.tpl'}
	{/if}
	{if $canWriteToSeller === true}
		<a class="js-individual-message__popup-link white-btn sx-wMax pull-left sx-m0" style="border: 1px solid #ebebeb;" href="javascript:;">
			{'Связаться с продавцом'|t}
		</a>
	{/if}
</div>