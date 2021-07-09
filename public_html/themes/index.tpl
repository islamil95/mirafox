{strip}
    {include file="header.tpl"}

	{if $actor}
		{* выводим только для авторизованных *}
		{Helper::registerFooterJsFile("/js/pages/index.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/jquery.kworkcarousel.min.js"|cdnBaseUrl)}
	{/if}

	{if $actor}
		{include file="index/authorized.tpl"}
	{else}
		{include file="index/unauthorized.tpl"}
	{/if}

    {include file="footer.tpl"}
{/strip}
