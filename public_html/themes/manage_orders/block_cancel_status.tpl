{strip}
	<div class="btn-title {if $s == "completed" && $order.paidStagesCount}btn-title_green{else}btn-title_pink{/if} m-pull-reset nowrap wMax">
		<div>
			<div class="btn-title_right tooltipster" data-tooltip-content="#order-report-table-item-{$order.OID}">?</div>
			<div style="display: none;">
				<div id="order-report-table-item-{$order.OID}">
					{if $s == "completed" && $order.paidStagesCount}
						{if $userType == "worker"}
							{'Выполнение задач подтверждено покупателем'|t}
						{else}
							{'Выполнение задач подтверждено'|t}
						{/if}
					{else}
						{$order.track_type|orderStatusDesc:$userType}
					{/if}
					{if $order.paidStagesCount}
						<p>{include file="manage_orders/block_stages.tpl" order=$order}</p>
					{/if}
				</div>
			</div>
		</div>
		{if $s == "completed" && $order.paidStagesCount}
			{'Выполнен'|t}
		{else}
			{'Отменен'|t}
		{/if}
	</div>
{/strip}