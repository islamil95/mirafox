{if $actor->isVirtual && !empty($post.statistics[$stat]["orders"][$metric])}
	<div class="donut-block__value tooltipster-nested" data-tooltip-content="#{$stat}-{$post.PID}-{$metric}">
		<div style="display: none;">
			<div id="{$stat}-{$post.PID}-{$metric}">
				{foreach from=$post.statistics[$stat]["orders"][$metric] item=orderId name=orders}
					<a href="/track?id={$orderId}">{$orderId}</a>{if !$smarty.foreach.orders.last},{/if}
				{/foreach}
			</div>
		</div>
		<span class="js-metric__value--{$metric}">{$post.statistics[$stat][$metric]}</span>
	</div>
{else}
	<div class="donut-block__value">
		<span class="js-metric__value--{$metric}">{$post.statistics[$stat][$metric]}</span>
	</div>
{/if}