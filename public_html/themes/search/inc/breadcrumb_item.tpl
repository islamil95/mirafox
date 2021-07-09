{strip}
	{if !$catViewType}
		{assign var=catViewType value=$catalog}
	{/if}
	<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" data-item-type="{$type}" data-level="{$level}">
		<a itemprop="item" {if !$isLast}href="{$baseurl}/{$catViewType}/{$url}"{/if} data-href="{$baseurl}/{$catViewType}/{$url}">
			<span class="bread-crumb-title" itemprop="name">{$name}</span>
		</a>
		{if !$isLast}
			<span class="bread-crump-delimiter">&nbsp;&nbsp;</span>
		{/if}
		<meta itemprop="position" content="{$position}"/>
	</li>
{/strip}