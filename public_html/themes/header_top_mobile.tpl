<div class="headertop-mobile justify-content-between m-visible">
	{if $hasBackButton}
		<div class="header_back-button pull-left" onclick="window.history.length > 1 ? window.history.back() : window.location.href = '/'">
			<i class="fa fa-angle-left color-white"></i>
		</div>
	{/if}

	{if $mobileReferer}
		<a href="{$mobileReferer}" class="back-link-mobile">
			<i class="fa fa-angle-left"></i>
		</a>
	{/if}
	<div class="foxmenubutton">
		<div class="{if $actor} {if $actor->notify_unread_count > 0}active{/if} {if $cart|count gt 0}active{/if} {if $actor->notify_unread_count > 0}active{/if}{/if} " onclick="mobile_menu_toggle();">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>

	<div class="header_search m-visible">
		<div id="general-search-mobile">
			<general-search class="mobile" default-search-encoded='{rawurlencode($searchValue)}'></general-search>
		</div>
	</div>

	{if $actor}
		<a class="menu-category" href="{$baseurl}/categories">
			<span></span><span></span><span></span><span></span>
		</a>
	{else}
		<a class="menu-category menu-category_login login-js">
			<i class="icon ico-signIn"></i>
		</a>
	{/if}
</div>