{strip}
	<tr class="m-hidden m-clearfix">
		<td>
			<a class="link-color" href="{absolute_url route="view_offers_all" params=["id" => $want->id]}">{$want->name|stripslashes}</a><br>
            {include file="wants/payer/manage/block_date.tpl" isShowViews=true}
		</td>
		<td class="nowrap">
            {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
		</td>
		<td>
            {include file="wants/payer/manage/block_offers.tpl"}
		</td>
		<td>
            {include file="wants/payer/manage/block_orders.tpl"}
		</td>
        {if $status != "archived"}
			<td width="185" class="projects-list__table-td-mobile">
                {include file="wants/payer/manage/block_statuses.tpl"}
			</td>
        {/if}
		<td width="85" class="projects-list__table-td-mobile">
			<div class="projects-list__icons mt5">
            	{include file="wants/payer/manage/block_control.tpl"}
			</div>
		</td>
	</tr>
	<tr class="m-visible">
		<td>
			<div class="d-flex flex-column justify-content-between">
				<div>
                    {include file="wants/payer/manage/block_date.tpl"}
					<a class="projects-list__m-title" href="{absolute_url route="view_offers_all" params=["id" => $want->id]}">{$want->name|stripslashes}</a>
				</div>
				<div>
					<div class="mt6">
                        {include file="wants/payer/manage/block_views.tpl"}
					</div>
					<span{if $want->getSumOrderCount() > 0} class="mr15"{/if}>
						{include file="wants/payer/manage/block_offers.tpl"}
					</span>
                    {include file="wants/payer/manage/block_orders.tpl"}
				</div>
			</div>
		</td>
		<td>
            {if $status != "archived"}
                {include file="wants/payer/manage/block_statuses.tpl"}
            {/if}
			<div class="price mt15">
                {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
			</div>
			<div class="t-align-c mt15">
				<div class="projects-list__icons mt5">
                	{include file="wants/payer/manage/block_control.tpl"}
				</div>
			</div>
		</td>
	</tr>
{/strip}