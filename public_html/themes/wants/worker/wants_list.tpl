{strip}
	{if $wants->isNotEmpty()}
		{foreach from=$wants item=want}
			{include file="wants/worker/wants_list_item_projects.tpl" needToAddView=true date_view=$wantViews[$want->id]}
		{/foreach}
	{else}
		<div class="f15">
			{'Нет ни одного доступного проекта в выбранных категориях.'|t}<br>
			<a class="js-blocked-kworks {if !$actor}{if Translations::isDefaultLang()}offer-signup-js{else}short-login-js{/if}{/if}" href="{if $actor}/new{else}javascript: void(0);{/if}">{'Создавайте'|t}</a> {'больше кворков в разных категориях, чтобы получать больше предложений об участии в проектах.'|t}
		</div>
	{/if}
	<div class="mt20">
	{include file="wants/worker/exchange_stats.tpl"}
	</div>
	<div style="text-align:center;" class="mb10">
        {$wants->links()}
	</div>
{/strip}