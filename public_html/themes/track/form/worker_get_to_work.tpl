<div class="step-block-order_item">
	<form action="{route route="track_worker_inwork"}" name="inprogress{$track->MID}" method="post" id="sendInWork_js">
		<input type="hidden" name="action" value="worker_inwork">
		<input type="hidden" name="orderId" value="{$order->OID}">
	</form>
	<a class="track-write-message js-toggle-message-button green-btn inactive v-align-m sx-wMax mr15" onclick="MessageFormModule.openMessageForm(this);">{'Написать сообщение'|t}</a>
	<div class="clear m-visible mt10"></div>
	<a class="js-toggle-message-button green-btn v-align-m sx-wMax" onclick="if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('I-DO-WORK'); document.inprogress{$track->MID}.submit()">
		{'Приступаю к работе'|t}
	</a>
	<div class="clear mt10"></div>
	{if $order->inWorkAttention()}
		<div class="block-inwork-attention">
			{'<strong>Внимание!</strong> Осталось менее %s для того, чтобы начать работу над заказом. Если вы не приступите к работе в течение %s, заказ будет отменен. Автоотмена заказа снижает ваш рейтинг ответственности и негативно сказывается на продажах.'|t:$order->inWorkTimeCancelString():$order->timeToTake()}
		</div>
		<span class="f14 ml10 mt5 sm-margin-reset font-OpenSans link-color cur break-word js-link-show-inwork-info" onclick="showInworkInfo($(this));">{"Если покупатель не предоставил информацию"|t}<img class="ml5" src="{"/arrow_right_blue.png"|cdnImageUrl}" width="9" alt=""/></span>
		<div class="js-block-inwork-info block-inwork-info-spoiler hidden">
			{"Если покупатель не предоставил все нужные для работы данные, вы можете запросить отмену заказа с причиной \"Покупатель не предоставил всю нужную информацию по заказу\". В комментарии обязательно напишите покупателю, какие именно данные вы от него ждете. Во время запроса отмены время, оставшееся до отмены заказа, не уменьшается."|t}
			<div onclick="hideInworkInfo();">
				<a class="fs14 cur dib">{"Свернуть"|t}</a>
				<img class="ml5 rotate180" src="{"/arrow_right_blue.png"|cdnImageUrl}" width="9" alt="">
			</div>
		</div>
	{else}
		<div class="block-inwork-info">
			{'Если вы не приступите к работе в течение %s, заказ будет отменен. Автоотмена заказа снижает ваш рейтинг ответственности и негативно сказывается на продажах.'|t:$order->timeToTake()}
		</div>
	{/if}
</div>