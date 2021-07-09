{strip}
	{if $apps}
	<label for="appId">Проект&nbsp;
		<select name="appId" id="appId" class="input__h18">
			<option value="" {if !$appId}selected="selected"{/if}>Все</option>
			{foreach from=$apps key=index item=app}
				<option value="{$app["id"]}" {if $appId == $app["id"]}selected="selected"{/if}>{$app["name"]|ucfirst}</option>
			{/foreach}
		</select>
	</label>
	{/if}
{/strip}