{strip}
    {* Подгрузка стилей CSS *}
	{assign var="pagesCss" value=array()}
	{if $pageSpeedMobile && $pageName != "view"}
		{$pagesCss[]="/css/minified_basic.css"|cdnBaseUrl|fileWithTime}
	{else}
		{$pagesCss[]="/css/minified.css"|cdnBaseUrl|fileWithTime}
	{/if}
	{if $pageName}
		{if $pageName == "land"}
			{$pagesCss[]="/css/land.css"|cdnBaseUrl|fileWithTime}
		{elseif $pageName == "view"}
			{$pagesCss[]="/css/bootstrap.modal.css"|cdnBaseUrl|fileWithTime}
			{$pagesCss[]="/css/dist/kwork-view.css"|cdnBaseUrl|fileWithTime}
		{elseif $pageName == "catalog"}
			{$pagesCss[]="/css/jquery-ui.css"|cdnBaseUrl|fileWithTime}
			{$pagesCss[]="/css/filter.css"|cdnBaseUrl|fileWithTime}
		{/if}
	{/if}
{/strip}

{* отложенная подгрузка стилей *}
<script>
	window.addEventListener('DOMContentLoaded', function() {
{foreach from=$pagesCss item=pageCss}
		loadStyleSheet('{$pageCss}');
{/foreach}

{* отложенная подгрузка изображений при загрузке страницы *}
		setTimeout(function () {
			lazyLoadImg('.lazy-load', false);
		}, 250);
	});
</script>
<noscript>
{foreach from=$pagesCss item=pageCss}
	{Helper::printCssFile($pageCss, "screen")}
{/foreach}
</noscript>

{* страница кворка *}
{if $pageName == "view"}
	<script>
{* отложенная загрузка контента подгрузка контента после загрузки всех стилей *}
		window.addEventListener('DOMContentLoaded', function() {
			lazyLoadContent('.page-more-kwork_user');
		});
	</script>
{/if}