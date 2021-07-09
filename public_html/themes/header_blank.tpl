<!DOCTYPE html>
{strip}
<html lang="{if is_null($i18n)}ru{else}{$i18n}{/if}" {if $isMobile}{if $isTablet}class="tablet"{else}class="mobile"{/if}{/if}>
<head>
    {include file="head_relations.tpl"}
	<script>
		var PULL_MODULE_ENABLE = 0;
	</script>
</head>
<body class="{if $isMobile && !$onlyDesktopVersion}is_mobile{/if}
				{if $smarty.server.HTTP_USER_AGENT|strstr:'KworkMobileAppWebView'} kwork-mobile-app{/if}">

<div class="nav-fox" id="foxmobilenav" style="display:none;">
    {include file="mobile_menu_blank.tpl"}
</div>

<div class="header relative">
	<div class="header_top">
		<div class="centerwrap lg-centerwrap relative">
			<div class="headertop">
                {include file="header_top_mobile_blank.tpl"}
				<div class="headertop-desktop m-hidden clearfix">
					<div class="brand-image">
						<a href="{$baseurl}/">
							<i class="icon ico_retina ico-kwork"></i>
							<div class="f9 logo_subtext"
								 style="color:white; position:absolute; bottom: -8px; white-space:nowrap;">{'Супер фриланс'|t}</div>
						</a>
					</div>
					<div style="line-height: 56px;float: right;">
						<a href="{route route="logout"}" class="white" style="margin: auto">Выйти</a>
					</div>
                    {if $canUserWithdraw}
                        {include file="components\header_funds_blank.tpl"}
                    {/if}
				</div>

				<div class="clear"></div>
			</div>

		</div>
	</div>
</div>
<div class="all_page{if $pageModuleName} page-{$pageModuleName}{/if}{if $pageModuleName} page-{$pageModuleName}{/if}{if $pageName == 'index'} is_index{elseif $pageName == 'land'} is_land{elseif $pageName == 'cat'} is_cat{/if}">
{/strip}