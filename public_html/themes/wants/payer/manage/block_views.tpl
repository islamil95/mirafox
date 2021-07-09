{strip}
    {if $want->views_dirty && $want->date_create > WantManager::DATE_START_SHOW_VIEWS}
		<span class="color-gray f12 nowrap">
			<span class="kwork-icon icon-eye mr5"></span>
			{$want->views_dirty} {declension count=$want->views_dirty form1="просмотр" form2="просмотра" form5="просмотров"}
		</span>
    {/if}
{/strip}