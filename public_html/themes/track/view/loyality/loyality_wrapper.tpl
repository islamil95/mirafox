{strip}
	<div class="track-loyality-wrapper {if !$loyalityVisible} hidden {/if} {block name=loyality_class}{/block}">
		<div class="track-loyality-title relative fw700 fs13">
			<div class="loyality-image dib mr10">
				<i class="kwork-icon icon-smile fs22 mr5 v-align-m color-green"></i>
				<i class="kwork-icon icon-right-arrow fs16 mr5 v-align-m"></i>
				<i class="kwork-icon icon-neutral-smile fs22 mr5 v-align-m color-orange"></i>
				<i class="kwork-icon icon-right-arrow fs16 mr5 v-align-m"></i>
				<i class="kwork-icon icon-angry-smile fs22 v-align-m color-red"></i>
			</div>
			<div class="dib">{'Возможно снижение лояльности покупателя'|t}</div>
			<i class="ico-arrow-down"></i>
		</div>
		<div class="track-loyality-more fs13" style="display: none;">
			{block name=loyality_text}{/block}
		</div>
	</div>
{/strip}