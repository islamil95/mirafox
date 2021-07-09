{strip}
	<span class="color-gray f12{if $want->views_dirty && $want->date_create > WantManager::DATE_START_SHOW_VIEWS} mr15{/if}">

		{assign var="dateCreate" value=$want->date_create|date}
        {assign var="dateActive" value=$want->date_active|date}
		{assign var="dateExpire" value=$want->date_expire|date}
		{assign var="dateReject" value=$want->date_reject|date}
        {if $smarty.now > $want->date_create|strtotime+3*24*60*60}
            {$dateCreate = $want->date_create|date:"j F Y"}
            {$dateActive = $want->date_active|date:"j F Y"}
            {$dateExpire = $want->date_expire|date:"j F Y"}
            {$dateReject = $want->date_reject|date:"j F Y"}
        {/if}

		{if $want->status == WantManager::STATUS_NEW}
			{'создан'|t} {$dateCreate}
		{elseif $want->status == WantManager::STATUS_CANCEL}
			{'на исправлении с'|t} {$dateReject}
		{elseif $want->status == WantManager::STATUS_STOP && $want->date_active}
			{'опубликован'|t} {$dateActive}
		{elseif $want->date_expire}
			{if $status != "archived"}
				{'активен до'|t}
			{else}
				{'опубликован'|t}
			{/if}
			&nbsp;
			{$dateExpire}
        {elseif $want->date_active}
            {'опубликован'|t} {$dateActive}
        {elseif $want->date_create}
            {'создан'|t} {$dateCreate}
        {/if}
	</span>
	{if $isShowViews}
        {include file="wants/payer/manage/block_views.tpl"}
	{/if}
{/strip}