{Helper::registerFooterJsFile("/js/jquery.kworkcarousel.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/slick.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/pages/index.js"|cdnBaseUrl)}

{strip}
<!DOCTYPE html>
<html lang="{if is_null($i18n)}ru{else}{$i18n}{/if}"{if $isMobile}{if $isTablet} class="tablet"{else} class="mobile"{/if}{/if}>
<head>
	{include file="head_relations.tpl"}
	<style>
		.is_stopper {
			background-color: #fff;
		}
		.stopper__header {
			height: 100px;
			padding-top: 50px;
			background-color: #f6f6f6;
		}
		.stopper__logo {
			display: block;
			width: 260px;
			height: 49px;
			margin: 0 auto;
			overflow: hidden;
			background: url(/images/kwork_logo_big.png) no-repeat 0 0;
		}
		.all_page.is_stopper {
			min-height: auto !important;
		}
	</style>
</head>
<div id="loadme"></div>
<body class="is_stopper">
<div class="stopper">
	<header class="stopper__header">
		<span class="stopper__logo"></span>
	</header>
	<div class="banner">
		<div class="centerwrap relative">
			<div class="headertext">
				<h1 class="f34 fw600">{'Kwork - удобный магазин фриланс-услуг'|t}</h1>
				<p class="stopper__text f34 text-orange ta-center mt50 mb50 pt0 uppercase">{$pagetitle}</p>
				<div class="color-white f16 font-OpenSans t-align-c mt20 index-advantage-block">
					<div class="dib v-align-m t-align-l banner-icon outline-none">
						<i class="icon v-align-m ico-about-price"></i>
						<span class="dib v-align-m ml10">Tens of thousands<br> services</span>
					</div>
					<div class="dib ml28 v-align-m t-align-l banner-icon outline-none">
						<i class="icon v-align-m ico-about-term"></i>
						<span class="dib v-align-m ml10">Quick order without<br> long discussions</span>
					</div>
					<div class="dib v-align-m t-align-l  ml28  banner-icon outline-none">
						<i class="icon v-align-m ico-about-warranty "></i>
						<span class="dib v-align-m ml10">Money back<br> guarantee</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="all_page is_index is_stopper"></div> {* этот блок нужен для адаптивности слайдера при ресайзе *}
{/strip}
{include file="footer_metrics.tpl"}
{include file="footer_base.tpl"}