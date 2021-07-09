{strip}
	{if $subCatSelectedId > 0}
		{$currentCategory = $subCatSelectedId}
	{else}
		{$currentCategory = $rootSelectedId}
	{/if}

	<div class="js-top-filters top-filters">
		<div class="popup-filter__header m-visible">
			<div class="popup-filter__header-name">{'Фильтры'|t}</div>
			<span class="popup-filter__close js-top-filters-mobile-close">{'Отмена'|t}</span>
			<span class="popup-filter__apply-btn js-top-filters-mobile-close">{'Применить'|t}</span>
		</div>
		<div class="js-top-filters-container top-filters__container">
			<div class="js-top-filter-item js-top-filter-item-subcategory top-filters__item top-custom-select">
				<div class="js-top-filter-title-link top-custom-select__title">
					<span class="m-visible">{'Рубрика'|t}:</span>
					<span class="js-top-filter-title">
						{if $rootSelectedId}
							{$subCategoryInfo[$currentCategory].name}
						{else}
							{"Все творческие рубрики"|t}
						{/if}
					</span>
				</div>
				{* Категории *}
				<div class="top-custom-select__wrap">
					<div class="js-top-select-scroll top-custom-select__list">
						{* Разбиваем список на две колонки *}
						{$subCategoryInfo = $subCategoryInfo|array_prepend:["CATID" => 0, "name" => "Все творческие рубрики"|t]}

						<div class="top-custom-select__list-content">
							{foreach array_chunk($subCategoryInfo, ceil($subCategoryInfo|count / 2)) as $categoryArray}
								<div>
									{foreach $categoryArray as $category}
										<div class="js-top-filter-list-item top-custom-select__list-item{if $currentCategory == $category.CATID} top-custom-select__list-item_active{/if}" data-id="{$category.CATID}">
											{$category.name}
										</div>
									{/foreach}
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="top-controls">
			{include file="catalog/inc/sort-by-block.tpl" selectName="time" values=[
				"all" => "За всё время"|t,
				"year" => "За год"|t,
				"month" => "За месяц"|t,
				"week" => "За неделю"|t,
				"day" => "За сутки"|t
			] activeValue=($time)?$time:"week" hide=($time)?false:true}
			{include file="catalog/inc/sort-by-block.tpl" selectName='s' values=[
				"popular" => "Лучшее"|t,
				"new" => "Свежее"|t
			] activeValue=$s}
		</div>
	</div>

	<a href="javascript: void(0);" class="js-top-filters-mobile-link top-filters-mobile">
		<i class="fa fa-sliders"></i>
		<span class="filter-name">{'Фильтры'|t}</span>
	</a>
	<div class="js-top-filters-selected top-filters-selected"></div>
{/strip}