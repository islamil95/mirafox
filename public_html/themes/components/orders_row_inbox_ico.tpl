{strip}
	{if $order.payer_unread_tracks || $order.worker_unread_tracks}
		{if $order.payer_unread_tracks}
			{$order.unreadCount = $order.payer_unread_tracks}
			{$order.orderInboxTooltipText = declension($order.unreadCount, [
				"Получено %s новое<br> сообщение от продавца"|t:$order.unreadCount,
				"Получено %s новых<br> сообщения от продавца"|t:$order.unreadCount,
				"Получено %s новых<br> сообщений от продавца"|t:$order.unreadCount
			])}
		{elseif $order.worker_unread_tracks}
			{$order.unreadCount = $order.worker_unread_tracks}
			{$order.orderInboxTooltipText = declension($order.unreadCount, [
				"Получено %s новое<br> сообщение от покупателя"|t:$order.unreadCount,
				"Получено %s новых<br> сообщения от покупателя"|t:$order.unreadCount,
				"Получено %s новых<br> сообщений от покупателя"|t:$order.unreadCount
			])}
		{/if}
		{if $order.unreadCount}
			<div class="tooltipster icon ico-order-row-inbox"
				data-tooltip-theme="dark"
				data-tooltip-around-side="right"
				data-tooltip-text="{$order.orderInboxTooltipText}">
				{if $order.unreadCount > 9}
					<div class="order-row-inbox-message-counter f10">+</div>
				{else}
					<div class="order-row-inbox-message-counter">{$order.unreadCount}</div>
				{/if}
			</div>
		{/if}
	{/if}
{/strip}