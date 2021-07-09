{strip}
	{if App::config("promo_page_in_menu")}
		{assign var="wideMaxParent" value=7}
		{assign var="thinMaxParent" value=6}
	{else}
		{assign var="wideMaxParent" value=8}
		{assign var="thinMaxParent" value=7}
	{/if}
	{insert name=get_header_menu assign=c}

	{if !App::config("category.color_view")}
		<div class="subnav">
			<div class="centerwrap lg-centerwrap category-menu">
				{include file="components\header_menu\_thin_items.tpl"}
				{include file="components\header_menu\_wide_items.tpl"}
			</div>
		</div>
	{else}
		<div class="subnav">
			<div class="centerwrap lg-centerwrap category-menu category-menu__bg">
				<ul class="category-menu__list cat-menu-thin sub-menu-parent">
					{include file="components\header_categories_colored_navs.tpl" currentMaxParent=$thinMaxParent}
				</ul>
				<ul class="category-menu__list cat-menu-wide sub-menu-parent category-menu__bg">
					{include file="components\header_categories_colored_navs.tpl" currentMaxParent=$wideMaxParent}
				</ul>
			</div>
		</div>
	{/if}

{/strip}