{include file="header.tpl"}
{include file="portfolio/upload/modal.tpl" page="my-portfolios"}
{Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/libs/withinviewport.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/libs/jquery.withinviewport.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/components/youtube-thumbnail.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}

{Helper::printCssFile("/css/filter.css"|cdnBaseUrl)}

{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/modules/filter.js"|cdnBaseUrl)}

{Helper::printCssFile("/css/dist/portfolio-categories.css"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/dist/portfolio-categories.js"|cdnBaseUrl)}

<script>
	{to_js name="categoryInfo" var=$categoryInfo}
	{to_js name="categoryChilds" var=$categoryChilds}
	{to_js name="attributeTree" var=$attributesTree}
	{if $subCatSelectedId}
	{to_js name="categorySelected" var=$categoryInfo[$subCatSelectedId].seo}
	{else}
	{to_js name="categorySelected" var=$categoryInfo[$rootSelectedId].seo}
	{/if}
	{to_js name="selectedAttributes" var=$attributesIds}
	{to_js name="sortByType" var=$s}
	{to_js name="sortByTime" var=$time}
</script>

{strip}
	{* комментарии *}
	{if $actor}
		{Helper::printJsFile("/js/caret.js"|cdnBaseUrl)}
		{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
		{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
	{/if}
	{include file="fox_error7.tpl"}
	<div class="centerwrap lg-centerwrap catalog-portfolio portfolio-view-data" data-nextpage="{$currentpage}" data-itemsperpage="{$items_per_page}" data-total="{$total}" data-catid="{$CATID}">
		<div class="catalog-portfolio__header">
			<h1>{'Показывайте и находите<br> творческие работы'|t}</h1>

			{if $actor->type == UserManager::TYPE_WORKER}
			<div class="catalog-portfolio__button-load-portfolio m-wMax">
				<a href="javascript:;" class="green-btn m-wMax js-new-portfolio">{'Загрузить работу'|t}</a>
			</div>
			{/if}
		</div>
		{include file="catalog/inc/top-filter.tpl"}
		<div class="portfolio-view-list-wrapper portfolio-card-data-wrap" data-portfolio-per-page="{$items_per_page}">
			{* Список работ портфолио *}
			<div class="portfolio-list portfoilo-card-list portfolio-view cusongslist">
				{foreach $portfolio as $item}
					{include file="portfolio/card_collage.tpl" item=$item}
				{/foreach}
			</div>

			{* Кнопка "Показать еще" *}
			<div class="ta-center">
				<button onclick="loadPortfolios();" class="loadPortfolios loadKworks btn_show-more mb0">{"Показать еще"|t}</button>
			</div>

			{* По запросу не найдено работ *}
			<div class="kworks-filter ta-center no-results {if $total}hidden{/if}">
				{'По выбранным фильтрам, к сожалению, ничего не найдено.'|t}<br>
				{'В каталоге Kwork %s работ. Попробуйте найти подходящую работу, изменив немного фильтры поиска.'|t:$statActDesignCount}
			</div>
		</div>
	</div>
{/strip}
{include file="footer.tpl"}