{strip}
	<ul class="category-menu__list cat-menu-thin sub-menu-parent">
		{if ($c|@count-1) <= $thinMaxParent}
			{assign var="maxParent" value=$thinMaxParent}
		{else}
			{assign var="maxParent" value=($thinMaxParent-1)}
		{/if}
		{foreach item=category from=$c name=list}
			{if $smarty.foreach.list.index <= $maxParent}
				{include file="components\header_menu\_item.tpl"}
			{/if}
		{/foreach}
		{if App::config("promo_page_in_menu")}
			{include file="components\header_categories_promo_item.tpl"}
		{/if}
		{if ($c|@count-1) > $thinMaxParent}
			{include file="components\header_categories_more.tpl" morePosition=$thinMaxParent}
		{/if}
	</ul>
{/strip}