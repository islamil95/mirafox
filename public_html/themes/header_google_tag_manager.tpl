{if $appMode == "stage"}
	{literal}
		<!-- Google Tag Manager (noscript) -->
		<noscript>
	{/literal}
	{if Translations::isDefaultLang()}
		{literal}
			<iframe src="//www.googletagmanager.com/ns.html?id=GTM-KJSMMH" height="0" width="0"	style="display:none;visibility:hidden"></iframe>
		{/literal}
	{else}
		{literal}
			<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MZXSF2S" height="0" width="0" style="display:none;visibility:hidden"></iframe>
		{/literal}
	{/if}
	{literal}
		</noscript>
		<!-- End Google Tag Manager (noscript) -->
	{/literal}
{/if}