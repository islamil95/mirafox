{strip}
	{if $s == "completed" && $order.status == OrderManager::STATUS_CANCEL && $order.stagesCount && $order.notPaidStagesCount}
		<div class="fs12">
			{'Выполнено %s задач из %s'|tn:$order.paidStagesCount:$order.paidStagesCount:$order.stagesCount}
		</div>
	{elseif $s == "cancelled" && $order.stagesCount && $order.paidStagesCount}
		<div class="fs12">
			{'Отменено %s задач из %s'|tn:$order.notPaidStagesCount:$order.notPaidStagesCount:$order.stagesCount}
		</div>
	{/if}
{/strip}