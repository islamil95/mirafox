{Helper::printCssFile("/css/dist/parent-category.css"|cdnBaseUrl)}
{if $actor}
	{Helper::registerFooterJsFile("/js/jquery.kworkcarousel.min.js"|cdnBaseUrl)}
{else}
	{* для неавторизованных пользователей вынес скрипт в head_script.tpl т.к срабатывает отложенная загрузка скриптов(defer) *}
{/if}
{if !$pageSpeedMobile}
	{Helper::registerFooterCssFile("/css/jquery.kworkcarousel.css"|cdnBaseUrl)}
{/if}
{$popularCatsQty = CategoryManager::POPULAR_SUB_CATS_QTY}

{strip}
	<div class="page-parent-category">
		{* Шапка категории *}
		<div class="categoriesHeader">
			<h1>{$cname|t|stripslashes}</h1>
			<div class="categoriesDescriptionc">{$tagline|t|strip_tags}</div>
		</div>

		{assign var=countSubcategories value=count($sub_cats)}
		{if $countSubcategories}
			{* Логика построения коллажа *}
			{assign var=remainderSubcategories value=$countSubcategories % 4}
			{assign var=firstSubcategories value='firstSubcategories-4'}

			{if $countSubcategories > 6}
				{if $remainderSubcategories > 0}
					{if $remainderSubcategories == 1}
						{$firstSubcategories = 'lastSubcategories-'|cat:5}
					{elseif $remainderSubcategories == 2}
						{$firstSubcategories = 'subcategories-'|cat:$countSubcategories}
					{else}
						{$firstSubcategories = 'firstSubcategories-'|cat:$remainderSubcategories}
					{/if}
				{/if}
			{else}
				{$firstSubcategories = 'firstSubcategories-'|cat:$countSubcategories}
			{/if}

			{* Коллаж *}
			<div class="lg-centerwrap clearfix centerwrap">
				{include './collage_of_subactegories_cards.tpl'}
			</div>
		{/if}

		{for $i=0 to ($popularCatsQty - 1)}
			<div class="lg-centerwrap centerwrap main-wrap m-m0 authorized-kwork-block clearfix categoriesSlider{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}">
				<h2 class="t-align-c m-text-left m-f18">{$sub_cats[$i].name|t}</h2>
				{include file="_blocks/kwork/carousel_kwork.tpl" kworks=$popularCatsPosts[$i] carouselName="popularKworks"}
				{if $popularCatsPosts[$i]|@count > 5}
					<div class="kwork-carousel-more">
						<div class="pull-right semibold fs16">
							<a href="{$baseurl}/{$catalog}/{$sub_cats[$i].seo|lower|stripslashes}">{'Смотреть все'|t}</a>
						</div>
					</div>
				{/if}
			</div>
		{/for}
		<br><br>
	</div>
{/strip}