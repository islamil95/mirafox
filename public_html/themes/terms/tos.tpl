{include file="header.tpl"}
{strip}
	{Helper::printCssFile("/css/info-table.css"|cdnBaseUrl, "all")}
	<div class="static-page__block">
		<div class="white-bg-block-oferta centerwrap m-pt10 m-bt10 ta-justify">
			{if Translations::isDefaultLang()}
				<div class="js-langs info-langs">
					<a href="javascript:;" data-index="1" class="js-lang info-lang {if Translations::isDefaultLang()}info-lang--active{/if}">RU</a>
					<a href="javascript:;" data-index="2" class="js-lang info-lang {if !Translations::isDefaultLang()}info-lang--active{/if}">EN</a>
				</div>
			{/if}
			{include file="terms/tos_content.tpl"}
		</div>
	</div>
	{Helper::printJsFile("/js/info-table.js"|cdnBaseUrl)}
{/strip}
{include file="footer.tpl"}
