{strip}
	{if $errors}
		{foreach $errors as $error}
			<div>
				<span class="mr3">{$error.caption}</span>
				{if $error.items}
					(
					{foreach from=$error.items key=error_id item=item name=errors}
						<a target="_blank" href="{$item.url}">{$item.title}{if !$smarty.foreach.errors.last}, {/if}</a>
					{/foreach}
					).
				{/if}
			</div>
		{/foreach}
	{/if}
{/strip}
