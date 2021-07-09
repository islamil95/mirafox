{strip}
	<form action="{route route="track_worker_inprogress_cancel_delete"}" name="delete{$track->MID}" method="post">
		<input type="hidden" name="action" value="worker_inprogress_cancel_delete">
		<input type="hidden" name="orderId" value="{$track->order->OID}">
	</form>
	<a class="green-btn v-align-m mt15"
		 onclick="document.delete{$track->MID}.submit()">
		{'Вернуть в работу'|t}
	</a>
	<hr class="gray">
	<div class="f12 color-gray v-align-m ml10 mt15 t-align-l">
		{'Если покупатель не примет решение в течение 2 дней, заказ будет отменен автоматически'|t}
	</div>
{/strip}
