{strip}
    {if $want->getSumOrderCount() > 0}
        {insert name=declension value=a assign=kworkDecl count=$want->getSumOrderCount() form1="кворк" form2="кворка" form3="кворков"}
		<span class="nowrap">
			<a class="link-color" href="{absolute_url route="payer_orders" params=["project" => $want->id]}">
				{$want->getSumOrderCount()|intval} {'заказ'|tn:$want->getSumOrderCount()}
			</a>
			<span class="tooltipster tooltip_circle tooltip_circle--hover ml5 m-hidden"
				  data-tooltip-text="{'Вы заказали %s %s по этому проекту'|t:($want->getSumOrderCount()|intval):$kworkDecl}">
				?
			</span>
		</span>
    {else}
		<span class="m-hidden nowrap">{'Пока нет'|t}</span>
    {/if}
{/strip}