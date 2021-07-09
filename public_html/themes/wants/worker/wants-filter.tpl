{* Фильтр продавца "Биржа проектов" *}
{strip}
	<div class="projects-filter wants-filter wants-filter-sidebar">
		{if $isUserHasOffers}
			<a href="{absolute_url route="projects_worker"}" class="wants-filter__link active">
				{'Проекты'|t}
			</a>
			<a href="{absolute_url route="offers"}" class="wants-filter__link">
				{'Мои предложения'|t}
			</a>
		{/if}

		{if !$showWantsForMyKworks && $selectedFavouriteCategoryId}
			{$showWantsForMyKworks = true}
		{/if}

		{insert name=get_categories2 assign=categories type=3}
		<div class="projects-filter__item projects-filter__item--select">
			<div class="js-category-container projects-filter__select {if $showWantsForMyKworks && !$selectedParentCategoryId}projects-filter__select--my{/if}">
				<select class="js-category projects-filter__select-styled" data-show-popup-my="{if $favouriteCategories}0{else}1{/if}">
					<option value="all" class="fw600" {if !$showWantsForMyKworks && !$selectedParentCategoryId} selected="selected"{/if}>
						{'Все рубрики'|t}
					</option>
					{if $actor}
						<option value="mine" class="fw600" {if $showWantsForMyKworks && !$selectedParentCategoryId} selected="selected"{/if}>
							{'Мои любимые рубрики'|t}
						</option>
					{/if}
					{foreach from=$categories key=parentId item=parentCategory}
						<option value="{$parentId}" {if !$showWantsForMyKworks && $parentId == $selectedParentCategoryId} selected="selected""{/if}>
							{$parentCategory->name|t}
						</option>
					{/foreach}
				</select>

				{if $actor}
					<div class="js-favourite-category-filter {if !$showWantsForMyKworks}hidden{/if}">
						<a href="javascript:;" class="js-favourite-category-all projects-filter__clear {if !$selectedParentCategoryId}hidden{/if}" data-name="favourite_category">{'Сбросить'|t}</a>
							{$totalCount = 0}
						<ul class="js-favourite-category-list projects-filter__list projects-filter__favourite-category-list">
						{foreach $favouriteCategories as $category}
								{if $totalCount > 6}
									{break}
								{/if}
								{if $favouriteCategoriesCount[$category->category_id] > 0}
									{$totalCount = $totalCount + 1}
								{/if}
								<li class="favourite-category-item" data-filter="category_id_{$category->category_id}" data-count="{$favouriteCategoriesCount[$category->category_id]}">
									<label class="projects-filter__radio">
										<input name="favourite_category" class="js-favourite-category-input styled-radio" type="radio" value="{$category->category_id}">
										<div class="radio_style">
											{$category->name|t}&nbsp;<span class="filter-counter">({$favouriteCategoriesCount[$category->category_id]})</span>
										</div>
									</label>
								</li>
							{/foreach}
						</ul>
						<ul class="js-favourite-category-list-hide projects-filter__list projects-filter__favourite-category-list projects-filter__favourite-category-list--hidden">
							{$totalCount = 0}
							{foreach $favouriteCategories as $category}
								{if $favouriteCategoriesCount[$category->category_id] > 0}
									{$totalCount = $totalCount + 1}
								{/if}
								{if $totalCount > 7}
									<li class="favourite-category-item" data-filter="category_id_{$category->category_id}" data-count="{$favouriteCategoriesCount[$category->category_id]}">
										<label class="projects-filter__radio">
											<input name="favourite_category" class="js-favourite-category-input styled-radio" type="radio" value="{$category->category_id}">
											<div class="radio_style">
												{$category->name|t}&nbsp;<span class="filter-counter">({$favouriteCategoriesCount[$category->category_id]})</span>
											</div>
										</label>
									</li>
								{/if}
							{/foreach}
						</ul>

						<div class="js-favourite-category-filter-more projects-filter__favourite-category-more {if $totalCount <= 7}hidden{/if}">
							{'Показать все'|t}
						</div>
					</div>


					<div class="js-link-change-my-categories projects-filter__my-link {if !$showWantsForMyKworks}hidden{/if}">
						<a href="javascript:;">{'Изменить любимые рубрики'|t}</a>
					</div>
				{/if}
			</div>

			<div class="js-sub-category-container projects-filter__select {if $showWantsForMyKworks || !$selectedParentCategoryId}hidden{/if}">
				{foreach from=$categories key=parentId item=parentCategory}
					{if $parentCategory->cats}
						{$selected = false}
						{foreach from=$parentCategory->cats key=id item=subCategory}
							{if in_array($subCategory->id, $filterCategories)}
								{$selected = true}
							{/if}
						{/foreach}
						<div data-category-id="{$parentCategory->id}" class="js-sub-category-wrap {if !$selected}hidden{/if}">
							<select class="js-sub-category long-touch-js projects-filter__select-styled">
								<option value="subAll" {if $selectedCategoryId == $selectedParentCategoryId}selected="selected"{/if}>
									{'Все подкатегории'|t}
								</option>
								{foreach from=$parentCategory->cats key=id item=subCategory}
									<option value="{$subCategory->id}" {if $selectedCategoryId == $subCategory->id}selected="selected"{/if}>
										{$subCategory->name|t}
									</option>
								{/foreach}
							</select>
						</div>
					{/if}
				{/foreach}
			</div>
		</div>

		{$_cntTotalKworks = 0}
		{foreach from=$filters['by_kworks'] key=id item=byKworks}
			{$_cntTotalKworks = $_cntTotalKworks + $counts['kworks_filter_id_'|cat:$byKworks['id']]}
		{/foreach}

		{$_cntTotalPrices = 0}
		{foreach from=$filters['by_budget'] key=id item=byBudget}
			{$_cntTotalPrices = $_cntTotalPrices + $counts['prices_filter_id_'|cat:$byBudget['id']]}
		{/foreach}
		<div class="projects-filter__item">
			<div>
				<div class="projects-filter__title">
					{'Бюджет'|t}
				</div>
				<div class="mb10" data-total-count="{$_cntTotalPrices}">
					<a href="javascript:;" class="js-input-checkbox-clear projects-filter__clear hidden" data-name="prices-filters[]">{'Сбросить'|t}</a>
					<ul class="projects-filter__list">
						{foreach from=$filters['by_budget'] key=id item=byBudget}
							{$_cntPrices = $counts['prices_filter_id_'|cat:$byBudget['id']]}

							<li data-filter="prices_filter_id_{$byBudget['id']}" data-count="{$_cntPrices}">
								<input id="prices-filters-{$byBudget['id']}" name="prices-filters[]" class="js-input-checkbox" type="checkbox" value="{$byBudget['id']}">
								<label for="prices-filters-{$byBudget['id']}">
									{$byBudget['name']}&nbsp;<span class="filter-counter">({$_cntPrices})</span>
								</label>
							</li>
						{/foreach}
					</ul>
				</div>
			</div>
			<div class="d-flex">
				<div class="projects-filter__input">
					<input type="text" name="price-from" id="price-from" value="{$filter.price_from}" placeholder="{if !Translations::isDefaultLang()}$ min{else}От руб.{/if}" maxlength="6" autocomplete="off" class="js-filter-input filter-input">
					<div class="js-filter-clear projects-filter__input-clear {if !$filter.price_from}hidden{/if}"></div>
				</div>
				<div class="projects-filter__input">
					<input type="text" name="price-to" id="price-to" value="{$filter.price_to}" placeholder="{if !Translations::isDefaultLang()}$ max{else}До руб.{/if}" maxlength="6" autocomplete="off" class="js-filter-input filter-input">
					<div class="js-filter-clear projects-filter__input-clear {if !$filter.price_to}hidden{/if}"></div>
				</div>
			</div>
			<div class="js-filter-error projects-filter__error hidden"></div>
		</div>

		<div class="projects-filter__item">
			<div class="projects-filter__title">
				{'%% найма'|t}
			</div>
			<div class="projects-filter__input">
				<input type="text" name="hiring-from" id="hiring-from" value="{$filter.hiring_from}" placeholder="{'От'|t}" maxlength="2" autocomplete="off" class="js-filter-input filter-input">
				<div class="js-filter-clear projects-filter__input-clear {if !$filter.hiring_from}hidden{/if}"></div>
			</div>
			<div class="js-filter-error projects-filter__error hidden"></div>
		</div>

		<div class="projects-filter__item" data-total-count="{$_cntTotalKworks}">
			<div class="projects-filter__title">
				{'Количество предложений'|t}
			</div>
			<div class="">
				<a href="javascript:;" class="js-input-checkbox-clear projects-filter__clear hidden" data-name="kworks-filters[]">{'Сбросить'|t}</a>
				<ul class="projects-filter__list">
					{foreach from=$filters['by_kworks'] key=id item=byKworks}
						{$_cntKworks = $counts['kworks_filter_id_'|cat:$byKworks['id']]}

						<li data-filter="kworks_filter_id_{$byKworks['id']}" data-count="{$_cntKworks}">
							<input id="kworks-filters-{$byKworks['id']}" name="kworks-filters[]" class="js-input-checkbox" type="checkbox" value="{$byKworks['id']}">
							<label for="kworks-filters-{$byKworks['id']}">
								{$byKworks['name']}&nbsp;<span class="filter-counter">({$_cntKworks})</span>
							</label>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/strip}