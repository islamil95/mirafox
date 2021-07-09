{strip}
	{if $actor}
		<a class="m-wMax m-h50 {if $canAddOfferStatus !== true}js-link-popup-warning-profile{elseif $ifPenaltyOrders}js-link-penalty-orders{/if} green-btn projects-offer-btn {if $actor->kwork_allow_status == "deny" || $waitPenaltyMessage}denied{/if}"
		   href="{absolute_url route="new_offer" params=$urlParameters}">
			{'Предложить услугу'|t}
		</a>
	{else}
		<a class="m-wMax m-h50 green-btn projects-offer-btn offer-signup-js" href="javascript: void(0);">
			{'Предложить услугу'|t}
		</a>
	{/if}
{/strip}