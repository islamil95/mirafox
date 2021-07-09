{if ($c|@count-1) <= $currentMaxParent}
	{assign var="maxParent" value=$currentMaxParent}
{else}
	{assign var="maxParent" value=($currentMaxParent-1)}
{/if}
{foreach item=category from=$c name=list}
	{if $smarty.foreach.list.index <= $maxParent}
		<li class="category-menu__list_{$category->id}">
			<a href="{$baseurl}/{$catalog}/{$category->seo|lower|stripslashes}" class="category-menu__list_item">{$category->name|t|stripslashes} </a>
			{if $category->cats|@count > 0}
				<div class="menubox">
					<div class="menulist {if !App::config("promo_page_in_menu") && $smarty.foreach.list.index == ($currentMaxParent-1)}last{/if}">
						<ul>
							<div class="category-menu__title">{$category->name|t|stripslashes}</div>
							{foreach item=cat from=$category->cats}
								{if $cat->id!=UserManager::CATEGORY_SMM_ID || !UserManager::isHideSocialSeo()}
									<li><a href="{$baseurl}/{$catalog}/{$cat->seo|lower|stripslashes}">{$cat->name|t|stripslashes}</a></li>
								{/if}
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}
		</li>
	{/if}
{/foreach}
{if App::config("promo_page_in_menu")}
	{include file="components\header_categories_promo_item.tpl"}
{/if}
{if ($c|@count-1) > $currentMaxParent}
	{include file="components\header_categories_more.tpl" morePosition=$currentMaxParent}
{/if}