{strip}
    {assign var="wantStatus" value=$want->getAltStatusHint()}
    {assign var="wantStatusTooltip" value=""}

    {if $want->getAltStatusAttribute() == Model\Want::ALT_STATUS_CHOOSING_CONTRACTOR}
		<a href="{absolute_url route="view_offers_all" params=["id" => $want->id]}#offers-anchor" class="btn-title btn-title_green_link wMax nowrap">
            {$wantStatus.title|t}
		</a>
    {else}
		<div class="btn-title btn-title_{$wantStatus.color} wMax nowrap">
			{if $want->status==WantManager::STATUS_ACTIVE}
                {$wantStatusTooltip=$want->date_expire|date:"j F Y H:i"}
            {elseif $want->status==WantManager::STATUS_CANCEL && $want->rejects && count($want->rejects)}
                {$wantStatusTooltip="<p>"|cat:$want->rejects->last()->comment|cat:"</p>"}
			{elseif $want->status==WantManager::STATUS_NEW}
                {if !$want->date_expire}
					{$wantStatusTooltip=""}
				{else}
                    {$wantStatusTooltip=$want->date_expire|date:"j F Y H:i"}
				{/if}
            {/if}
			<div class="btn-title_right tooltipster" data-tooltip-text="{if $wantStatusTooltip|strlen > 0}{"<p>"|cat:$wantStatus.tooltip|cat:"</p>"|t:$wantStatusTooltip}{else}{"<p>"|cat:$wantStatus.tooltip|cat:"</p>"}{/if}">?</div>
            {$wantStatus.title|t}
		</div>
    {/if}
{/strip}