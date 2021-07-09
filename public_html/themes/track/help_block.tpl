{strip}
	<div id="track--questions__item-{$id}" class="track--questions__item">
		{if $block["title"]}
			<div class="track--questions__item-title">
				{$block["title"]}
			</div>
		{/if}
		<div class="{if $isLast} color-gray {/if} track--questions__item-content ">
			{$block["content"]}
		</div>
	</div>
{/strip}
