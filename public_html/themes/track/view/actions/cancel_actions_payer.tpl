{strip}
	{if $isWorkersReason}
		<div>
			<div class="cancel-track__block m-wMax">
				<form action="{absolute_url route="track_payer_inprogress_cancel_reject"}"
					  name="reject{$track->MID}"
					  method="post">
					<input type="hidden" name="action"
						   value="payer_inprogress_cancel_reject">
					<input type="hidden" name="orderId" value="{$order->OID}">
					<a class="green-btn v-align-m"
					   onclick="if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('DONT-CANCEL-ORDER-TWO'); document.reject{$track->MID}.submit()">
						{'Вернуть в работу'|t}
					</a>
				</form>
			</div>
			<div class="cancel-track__block m-wMax">
				<button type="button"
						class="js-cancel-track__button orange-btn v-align-m ml10">
					{'Подтвердить отмену'|t}
				</button>
			</div>
			<div class="clear"></div>
		</div>
		<hr class="gray">
		{if $track->reason_type == "worker_no_payer_requirements"}
			<div class="f12 color-gray v-align-m t-align-l mb10">
				{'Вы можете вернуть заказ в работу и предоставить все данные, необходимые для начала работы. Пожалуйста, предоставьте данные как можно скорее после возврата заказа в работу, чтобы он не отменился автоматически из-за того что продавец не успел начать работу.'|t}
			</div>
			<div class="f12 color-gray v-align-m t-align-l">
				{'Если Вы не примете решение в течение 2 дней, заказ будет отменен автоматически'|t}
			</div>
			{include file='track/popup/agree_cancel_reason.tpl'}
		{/if}
	{else}
		<form action="{absolute_url route="track_payer_inprogress_cancel_reject"}" name="reject{$track->MID}" method="post">
			<input type="hidden" name="action" value="payer_inprogress_cancel_reject">
			<input type="hidden" name="orderId" value="{$order->OID}">
		</form>
		<form action="{route route="track_payer_inprogress_cancel_confirm"}" name="confirm{$track->MID}" method="post">
			<input type="hidden" name="action" value="payer_inprogress_cancel_confirm">
			<input type="hidden" name="orderId" value="{$order->OID}">
		</form>
		<a class="green-btn v-align-m mt15"
		   onclick="if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('DONT-CANCEL-ORDER-TWO'); document.reject{$track->MID}.submit()">
			{'Вернуть в работу'|t}
		</a>
		<a class="orange-btn v-align-m ml10 mt15"
		   onclick="if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('CANCEL-ORDER-TWO'); document.confirm{$track->MID}.submit()">
			{'Подтвердить отмену'|t}
		</a>
		<hr class="gray">
		<div class="f12 color-gray v-align-m ml10 t-align-l">
			{'Если Вы не примете решение в течение 2 дней, заказ будет отменен автоматически'|t}
		</div>
	{/if}
{/strip}
