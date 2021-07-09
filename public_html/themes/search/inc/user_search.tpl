{if \UserManager::validateUsernameChars($originalValue)}
	{strip}
	{if $addOrBlock}
	<div style="margin:20px auto;max-width:400px;">
		<div class="form-entry-middle-popup font-OpenSans t-align-c">
			<span style="background:#F6F6F6;">или</span>
		</div>
	</div>
		{$hrefStyle="style=\"font-size: 14px;\""}
	{elseif $addOrText}
		или
	{/if} <a href="{getAbsoluteURL("/user_search?query={$originalValue}")}" {$hrefStyle}>
		{if $addOrText}искать
		{else}Искать
		{/if} «{$originalValue|stripslashes}» среди пользователей
	</a>
	{/strip}
{/if}