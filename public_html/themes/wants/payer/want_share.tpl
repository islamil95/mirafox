{strip}
	{if !in_array($want->status, [WantManager::STATUS_CANCEL])}
		<a href="javascript: void(0);" class="kwork-icon icon-share tooltipster wantsClipboard" data-tooltip-text="{'Скопировать ссылку на проект'|t}" data-tooltip-destroy="true" data-clipboard="{absolute_url route="view_offers_all" params=["id" => $want->id]}"></a>
	{/if}
{/strip}