{strip}
	{if $wants->total()}
		{foreach from=$wants item=want}
			{include file="wants/worker/wants_list_item_projects.tpl" needToAddView=true date_view=$wantViews[$want->id]}
		{/foreach}
	{else}
		<div class="f15">Нет ни одного доступного проекта</div>
	{/if}
{/strip}