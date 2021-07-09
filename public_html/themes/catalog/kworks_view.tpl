{assign var="cname" value=$name}
{assign var="cseo" value=$seo}

{include file="header.tpl"}

{if $CATID == "all"}
	{include file="cat_header.tpl"}
{elseif !$actor && $land}
	{include file="land_header.tpl"}
{elseif $parent}
	{include file="cat_header.tpl"}
{/if}

{include file="cat.tpl"}

{include file="footer.tpl"}