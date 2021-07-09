<li class="cat-menu_item_more">
	<span class="f14 cat-menu-thin_more pt5" >{'Еще'|t}</span>
	<div class="menubox">
		<div class="menulist last">
			<ul>
				{foreach item=category from=$c name=list}
					{if $smarty.foreach.list.index >= $morePosition}
						{if $cat->id!=UserManager::CATEGORY_SMM_ID || !UserManager::isHideSocialSeo()}
							<li><a href="{$baseurl}/{$catalog}/{$category->seo|lower|stripslashes}">{$category->name|t|stripslashes}</a></li>
						{/if}
					{/if}
				{/foreach}
			</ul>
		</div>
	</div>
</li>