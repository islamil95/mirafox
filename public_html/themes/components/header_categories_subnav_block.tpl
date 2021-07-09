{strip}
	<div class="submenu">
		{if $rightCatArray|count}
			{$max = max($leftCatArray|count, $rightCatArray|count) - 1}
			<div class="submenu-column">
				{for $i=0 to $max}
					{if $leftCatArray[$i]}
						{if $catArr[$leftCatArray[$i]]->id != UserManager::CATEGORY_SMM_ID || !UserManager::isHideSocialSeo()}
							<a class="submenu-item" href="{$baseurl}/{$catalog}/{$catArr[$leftCatArray[$i]]->seo|lower|stripslashes}">{$catArr[$leftCatArray[$i]]->name|stripslashes}</a>
						{/if}
					{else}
						<span class="submenu-item-empty"></span>
					{/if}
				{/for}
			</div>
			<div class="submenu-column">
				{for $i=0 to $max}
					{if $rightCatArray[$i]}
						{if $catArr[$rightCatArray[$i]]->id != UserManager::CATEGORY_SMM_ID || !UserManager::isHideSocialSeo()}
							<a class="submenu-item" href="{$baseurl}/{$catalog}/{$catArr[$rightCatArray[$i]]->seo|lower|stripslashes}">{$catArr[$rightCatArray[$i]]->name|stripslashes}</a>
						{/if}
					{else}
						<span class="submenu-item-empty"></span>
					{/if}
				{/for}
			</div>
		{else}
			{foreach from=$leftCatArray item=catId}
				{if $catArr[$catId]->id != UserManager::CATEGORY_SMM_ID || !UserManager::isHideSocialSeo()}
					<a class="submenu-item" href="{$baseurl}/{$catalog}/{$catArr[$catId]->seo|lower|stripslashes}">{$catArr[$catId]->name|stripslashes}</a>
				{/if}
			{/foreach}
		{/if}
	</div>
{/strip}