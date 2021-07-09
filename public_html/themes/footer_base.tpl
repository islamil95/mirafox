{strip}

{Helper::printJsFiles()}
{Helper::printCssFiles()}
{if $controlEnLang}
<script>
	window.addEventListener('DOMContentLoaded', function() {
        $('.control-en').on('input', (e) => e.target.value = e.target.value.replace(/[А-Яа-яЁё]/g, ''));
    });
</script>
{/if}
{if $refIframe}
	<iframe id="iframe_ref" src="{$refIframe}" width="0" height="0" align="left"></iframe>
{/if}

{if $authHash && $authBaseurls}
	{foreach $authBaseurls as $authBaseurl}
		<iframe id="iframe_auth" src="{$authBaseurl}/setcookie?auth_hash={$authHash}" width="0" height="0" align="left"></iframe>
	{/foreach}
{/if}

</body>
</html>
{/strip}