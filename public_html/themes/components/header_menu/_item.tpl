{strip}
	<li>
		<a href="{$baseurl}/categories/{$category->seo|lower|stripslashes}"
		   class="category-menu__list_item">{$category->name|t|stripslashes}</a>
		{if $category->cats|@count > 0}
			<div class="menubox">
				<div class="menulist {if $thinMaxParent && $smarty.foreach.list.index > ($thinMaxParent/2)}last{/if}">
					<div class="menutitle">{$category->name|t|stripslashes}</div>
					{include file="components\header_categories_subnav_block.tpl" catArr=$category->cats leftCatArray=$category->wideLeftCatArray rightCatArray=$category->wideRightCatArray}
				</div>
			</div>
		{/if}
	</li>
{/strip}