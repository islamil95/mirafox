<div class="card__content-body search-category allmusic">
	<ul class="popup-filter__category-list">
		{foreach from=$searchCategories key=k item=cat}
		{if $k == 3}
	</ul>
	<ul class="popup-filter__category-list js-search-category__list mt10" style="display: none">
		{/if}
		<li class="search-category__item popup-filter__category-item">
			<div class="popup-filter__category-item m-visible">
				<input name="c" class="js-kwork-filter-input styled-radio" id="cat_{$cat.category}" type="radio" value="{$cat.category}"
						{if $cname eq $cat.name || (!$cname && $cat.category eq 0)} checked="checked" {/if}>
				<label for="cat_{$cat.category}">{$cat.name|t|stripslashes}</label>
			</div>
			<div class="m-hidden">
				<span style="float: left">
				{if $cname eq $cat.name || (!$cname && $cat.category eq 0)}
					<span class="search-category__text link-color">{$cat.name|t|stripslashes}</span>
				{else}
					<a class="search-category__link"
					   href="{$baseurl}/search?{$filterQuery|build_query:['c' => {$cat.category}]:['c', 'page']}">
						{$cat.name|t|stripslashes}
					</a>
				{/if}
				</span>
                {if !($cname && $cat.category == 0)}
                    {* Количество во "все категории" не показываем в случае если выбрана категория *}
                    <span class="search-category__count" style="position: relative">({$cat.count})</span>
                {/if}
				<div style="clear: both"></div>
				<div class="search-category-attributes">
					{if $attributesTree[$cat.category]}
						{include file="search/inc/attributes_list.tpl" attributesTree=$attributesTree[$cat.category]}
					{/if}
				</div>
			</div>
		</li>
		{foreachelse}
		<li class="search-category__item">
			<a href="{$baseurl}/search?{$filterQuery|build_query:[]:['c', 'page']}">
				{if $cname eq $c[i].name}
					<strong>{'Все категории'|t}</strong>
				{else}{'Все категории'|t}{/if}
			</a>
		</li>
		{/foreach}
	</ul>

	{if $searchCategories|@count > 2}
	<div class="js-search-category__more search-category__more" id="toggleSearchCats">
		<a href="javascript:void(0)" data-show-text="{'Показать еще'|t}" data-hide-text="{'Свернуть'|t}">
			{'Показать еще'|t}
		</a>
	</div>
	{/if}
	<input type="hidden" name="attribute_id" value="">
</div>

<script>
	var tree = {$attributesTree|@reset|@json_encode};
	var selectedAttributesIds = {$selectedAttributesIds|@json_encode};
	var packageFilters = {$packageFilters|@json_encode};
	var packageItemsConditions = {$packageItemsConditions|@json_encode};
	var topFilters;

	window.addEventListener('DOMContentLoaded', function() {
		topFilters = new TopFilters();
		topFilters.refresh(tree, selectedAttributesIds, packageFilters, packageItemsConditions);
	});
</script>