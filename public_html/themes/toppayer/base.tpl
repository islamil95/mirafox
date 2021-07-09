{include file="header.tpl"}
{strip}
	{Helper::registerFooterJsFile("/js/toppayer.js"|cdnBaseUrl)}
	<div class="bodybg foxpaddingtop15 pb10 mt20">
		<div class="whitebody foxpaddingtop30 foxwidth842">
			<div class="inner-wrapper foxwidth842">
				{block name="topPayerContent"}{/block}
				<div class="clear"></div>
			</div>
		</div>
	</div>
{/strip}
{include file="footer.tpl"}