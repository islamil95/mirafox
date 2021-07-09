{strip}
	<div class="track--progress p20-15  toggler">

		<div class="mb20 track--progress__toggle toggler--link {if count($trackHistory) <= 5} hide {/if}">
			<span class="open__all">{'Показать все'|t}</span>
			<span class="hide__all">{'Свернуть'|t}</span>
		</div>

		<div class="progress-line">
            {foreach item=histItem from=$trackHistory name=history}
				<div class="progress-line--item
					{if $smarty.foreach.history.last && $order->isDone()} done
					{elseif $smarty.foreach.history.last && $order->isCancel()} canceled
					{elseif $smarty.foreach.history.last} line-white-gray checked
					{else} checked	{/if} ">
					<div class="progress-line--item__icon "></div>
					<div class="progress-line--item__content tooltipster tooltipstered" data-tooltip-side="top"
						 data-tooltip-text="{$histItem->getDate()|date:"j M, H:i"}">{$histItem->getShortDescription()}</div>
				</div>
            {/foreach}
            {if $order->isDone()}
				<div class="progress-line--item">
					<div class="progress-line--item__icon "></div>
					<div class="progress-line--item__content">Опубликован в портфолио</div>
				</div>
            {/if}
            {if !in_array((int)$order->status,[\OrderManager::STATUS_CANCEL,\OrderManager::STATUS_DONE])}
                {foreach \Track\TrackHistory::getMustHaveTypes() as $type}
                    {if !in_array($type,$trackHistoryDescriptions) }
						<div class="progress-line--item">
							<div class="progress-line--item__icon "></div>
							<div class="progress-line--item__content">{$type}</div>
						</div>
                    {/if}
                {/foreach}
            {/if}
		</div>
	</div>
{/strip}