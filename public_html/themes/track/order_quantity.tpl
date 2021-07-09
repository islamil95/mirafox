{if $order->getOrderVolumeType() && $order->data->volume > 0}
	{$order->getDisplayVolume()|zero:1}
	{if $order->data->volume > $order->data->kwork_volume}
		{capture assign=orderedVolumeText}
			{$order->getDisplayVolume()|zero:1} {$order->getOrderVolumeType()->getPluralizedNameGenetive($order->getDisplayVolume())}
		{/capture}

		{capture assign=paidVolumeText}
			{($order->data->kwork_volume * $order->count)|zero:1} {$order->data->volumeType->getPluralizedNameGenetive($order->data->kwork_volume * $order->count)}
		{/capture}
		<span class="f10">
			{if isAllowToUser($order->USERID)}
				<span class="tooltipster tooltip_circle tooltip_circle--size14 tooltip_circle--light tooltip_circle--hover"
					  data-tooltip-side="right"
					  data-tooltip-text="{'Если понадобится более %s, то вы можете добавить их в заказ в дополнительных опциях ниже.'|t:($orderedVolumeText|trim)}"
					  data-tooltip-theme="light">?</span>

			{else}
				<span class="tooltipster tooltip_circle tooltip_circle--size14 tooltip_circle--light tooltip_circle--hover"
					  data-tooltip-side="right"
					  data-tooltip-text="{'Покупатель оплатил %s. Если понадобится больше, покупатель может добавить их в заказ за дополнительную оплату.'|t:($orderedVolumeText|trim)}"
					  data-tooltip-theme="light">?</span>
			{/if}
		</span>
	{/if}
{else}
	{$order->count}
{/if}