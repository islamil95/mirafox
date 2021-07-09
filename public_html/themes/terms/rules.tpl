{include file="header.tpl"}
{strip}
	<div class="static-page__block  {if UserManager::isModer() || UserManager::isAdmin() || isVirtual() } anchor-show {/if}">
		<div class="f15 white-bg-block centerwrap ta-justify">
			<div class="pt20 m-visible"></div>
			{$page.content}
		</div>
	</div>
{/strip}
{include file="footer.tpl"}