{* Покупатель запросил обоюдную отмену заказа *}
{extends file="track/view/base.tpl"}

{block name="mainContent"}
    {if $reason}
		<div class="f15 mt5 t-align-l {if !$config.track.isFocusGroupMember} ml80 m-ml0 {/if}">
            {'Причина:'|t} {$reason}
		</div>
    {/if}

    {if $comment && $comment != "&nbsp;"}
		<div class="f15 mt5 breakwords t-align-l {if !$config.track.isFocusGroupMember} ml80 m-ml0 {/if}">
            {'Комментарий:'|t} {$comment}
		</div>
    {/if}

    {$inDays = $track->getInprogressCancelAutoDays()}
    {if !$order}
        {$order = $track->order}
    {/if}

    {if isAllowToUser($order->USERID) && $track->isNew() && $inDays}
		<form action="{route route="track_payer_inprogress_cancel_delete"}" name="delete{$track->MID}" method="post">
			<input type="hidden" name="action" value="payer_inprogress_cancel_delete">
			<input type="hidden" name="orderId" value="{$order->OID}">
		</form>
        {if $config.track.isFocusGroupMember}
			<div class="t-align-c">
        {/if}
		<a class="green-btn v-align-m mt15"
		   onclick="document.delete{$track->MID}.submit()">
            {'Вернуть в работу'|t}
		</a>
        {if $config.track.isFocusGroupMember}
			</div>
        {/if}
        {if $order->status != OrderManager::STATUS_ARBITRAGE}
			<hr class="gray">
			<div class="f12 color-gray v-align-m ml10 t-align-l">
                {'Если продавец не примет решение в течение %s, заказ будет отменен автоматически'|t:$inDays}
			</div>
        {/if}
    {/if}

    {if isAllowToUser($order->worker_id) && $track->isNew() && $inDays}
		<form action="{route route="track_worker_inprogress_cancel_reject"}" name="reject{$track->MID}" method="post">
			<input type="hidden" name="action" value="worker_inprogress_cancel_reject">
			<input type="hidden" name="orderId" value="{$order->OID}">
		</form>
		<form action="{route route="track_worker_inprogress_cancel_confirm"}" name="confirm{$track->MID}" method="post">
			<input type="hidden" name="action" value="worker_inprogress_cancel_confirm">
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
        {if $order->status != OrderManager::STATUS_ARBITRAGE}
			<hr class="gray">
			<div class="f12 color-gray v-align-m ml10 t-align-l">
                {'Если Вы не примете решение в течение %s, заказ будет отменен автоматически'|t:$inDays}
			</div>
        {/if}
    {/if}
{/block}
