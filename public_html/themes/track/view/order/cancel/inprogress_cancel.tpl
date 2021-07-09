{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{if $reason}
		<div class="f15  t-align-l {if !$config.track.isFocusGroupMember} mt5 ml80 m-ml0 {/if}">
			{'Причина:'|t} {$reason}
		</div>
	{/if}

	{if $comment}
		<div class="f15 breakwords t-align-l {if !$config.track.isFocusGroupMember} mt5 ml80 m-ml0 {/if}">
			{'Комментарий:'|t} {$comment}
		</div>
	{/if}

	{if $track->type == "worker_inprogress_cancel" && $track->reason_type == "worker_no_time" && isAllowToUser($order->worker_id)}
		<div class="f15 {if !$config.track.isFocusGroupMember} mt15  {/if}">
			<p>{'В соответствии с правилами Kwork отказ от заказа по причине занятости является неуважительным. В связи с этим рейтинг ответственности продавца снижается. Это приводит к ухудшению ранжирования кворков и падению продаж.'|t}</p>
			<p>{'Пожалуйста, будьте внимательны! Если вы заняты и не можете брать в работу новые заказы, обязательно останавливайте свои кворки. Затем активируйте их, когда вновь готовы принимать заказы.'|t}</p>
		</div>
	{/if}

	{if $track->isCancelRatingMessage()}
		<div class="f15 {if !$config.track.isFocusGroupMember} mt15  {/if} {if !$track->isCancelReasonModeNormal()}track-red{/if}">
			{$track->getCancelRatingMessage(isAllowToUser($track->order->worker_id))}
		</div>
	{/if}

	{if $showActionButtons}
        {if $config.track.isFocusGroupMember}
			<div class="t-align-c">
        {/if}
        {if $track->order->isPayer($actor->id)}
            {include file="track/view/actions/cancel_actions_payer.tpl"}
        {else}
            {include file="track/view/actions/cancel_actions_worker.tpl"}
        {/if}
        {if $config.track.isFocusGroupMember}
			</div>
        {/if}
	{/if}
{/block}