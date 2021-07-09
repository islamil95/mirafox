{strip}
	{if isset($kwork.id) && $wasRejected && $rejectReasons}
		{assign var=rejectReasonsList value=array()}
		{foreach from=$rejectReasons item=$reason}
			{if !empty($reason.description) && in_array($reason.type, $type)}
				{if (!empty($reason.name_user))}
					{$rejectReasonsList[] = $reason.name_user|cat:"<br>"|cat:$reason.description}
				{else}
					{$rejectReasonsList[] = $reason.description}
				{/if}
			{else}
				{if (!empty($reason.name_user)) && in_array($reason.type, $type)}
					{$rejectReasonsList[] = $reason.name_user}
				{/if}
			{/if}
		{/foreach}
		{if $rejectReasonsList|@count > 0}
			<div class="moder-reasons-container">
				<div class="moder-reasons-sticky">
					<div class="f15 bold mb10">{'Что исправить?'|t}</div>
					<ul class="moder-reasons-list mt0">
						{foreach from=$rejectReasonsList item=$reasonStr}
							<li class="moder-reasons-list__item">
								{$reasonStr}
							</li>
						{/foreach}
					</ul>
				</div>
			</div>
		{/if}
	{/if}
{/strip}