{strip}
	<div class="popup-filter__category-list">
		{foreach from=$searchCategories key=k item=cat}
			{if $k == 3}
				</div>
				<div class="popup-filter__category-list--hidden js-search-category__list mt10">
			{/if}
			<div class="popup-filter__category-item">
				<input name="c" class="js-kwork-filter-input styled-radio" id="cat_{$cat.category}" type="radio" value="{$cat.category}" {if $cname eq $cat.name || (!$cname && $cat.category eq 0)} checked="checked" {/if}>
					<label for="cat_{$cat.category}">{$cat.name|t|stripslashes}</label>
			</div>
		{/foreach}
	</div>
	<input type="hidden" name="attribute_id" value="">
	{if $searchCategories|@count > 2}
		<div class="js-search-category__more search-category__more" id="toggleSearchCats">
			<a href="javascript:void(0)" data-show-text="{'Показать еще'|t}" data-hide-text="{'Свернуть'|t}">
				{'Показать еще'|t}
			</a>
		</div>
	{/if}
{/strip}