{strip}
{Helper::registerFooterJsFile("https://yastatic.net/es5-shims/0.0.2/es5-shims.min.js")}
{Helper::registerFooterJsFile("https://yastatic.net/share2/share.js")}
<div class="clear"></div>
<div class="mt10 ya-share2" id="ya-share2"
	 data-services="facebook,vkontakte,twitter,odnoklassniki,gplus" 
	 data-bare="true"
	 data-url="{$canonicalUrl}{if $actor}?ref={$actor->id}{/if}"
></div>
{/strip}