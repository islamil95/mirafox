{strip}
	{if $isSearchPage || $CATID != 'all'}
		<div class="kworks-filter-header m-hidden">{'Рубрики'|t}</div>
		<h3 class="popup-filter__group-title m-visible">{'Категория'|t}: <span>
											{if $cname}
												{$cname}
											{else}
												{'Все категории'|t}
											{/if}
										</span>
			<div class="kwork-icon icon-down-arrow"></div>
		</h3>
		{if $isSearchPage}
			{include file="search/inc/categories_in_finded.tpl"}
		{else}
			{include file="search/inc/category_with_attributes.tpl"}
		{/if}
		<hr>
	{/if}
{/strip}