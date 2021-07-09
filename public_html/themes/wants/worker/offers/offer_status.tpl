{strip}
	{if in_array($offer->status, array('active', 'cancel'))  }
		<span class="status-block status-block_green">{'Отправлено'|t}</span>
	{elseif $offer->status == 'delete'}
		<span class="status-block status-block_red">{'Отклонено'|t}</span>
	{elseif $offer->status == 'done'}
		<span class="status-block status-block_orange">{'Заказано'|t}</span>
	{elseif $offer->status == 'stop'}
		<span class="status-block">{'Остановлено'|t}</span>
	{elseif $offer->status == 'reject'}
		<span class="status-block status-block_red tooltipster"
			  data-tooltip-text="{'Причина отклонения в личных сообщениях'|t}">
			{'Отклонено модератором'|t}
		</span>
	{/if}
{/strip}