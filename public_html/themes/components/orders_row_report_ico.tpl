{strip}
	{* теперь тут работа с моделью KworkReport а не KworkReportManager - не перепутайте методы *}
	{if $order.status == OrderManager::STATUS_INPROGRESS && $order.report && $order.report->needShowIcon() && $order.report->needReport()}
		&nbsp;
		<a href="{$baseurl}/track?id={$order.OID}&scroll=1" class="tooltipster icon ico-order-row-inbox ico-order-report-row ico-order-report-{$order.report->type}"
			data-tooltip-side="right"
			data-tooltip-theme="dark"
			data-tooltip-text="{$order.report->getManageOrderTooltip()}">
		</a>
	{/if}
{/strip}