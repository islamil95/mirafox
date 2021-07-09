{strip}
	<div class="js-popup-confirm-cancel-reason__container hidden">
		<form action="{absolute_url route="track_payer_inprogress_cancel_confirm"}"
			  id="confirm-cancel-reason-form" method="post">
			<h2>{'Отмена заказа'|t}</h2>
			<input type="hidden" name="action" value="payer_inprogress_cancel_confirm">
			<input type="hidden" name="orderId" value="{$order->OID}">
			<input type="hidden" name="agree_reason">
			<hr class="gray">
			{assign var="cancelReason" value=$track->getCancelReason()}
			<p class="dib bold pb20">{'Причина отказа: "%s".'|t:$cancelReason.name}</p>
			<p class="mb10">{'Вы согласны, что отмена происходит именно по этой причине?'|t}</p>
			<div class="popup__buttons">
				<button type="button" class="popup__button green-btn button-confirm-cancel-reason"
						onclick="confirmCancelReasonOrder(this);" data-agree="0">
					{'Нет'|t}
				</button>
				<button type="button" class="popup__button green-btn button-confirm-cancel-reason pull-right"
						onclick="confirmCancelReasonOrder(this);" data-agree="1">
					{'Да'|t}
				</button>
				<div class="clearfix"></div>
			</div>
		</form>
	</div>
{/strip}