{strip}
<h3 class="popup-filter__group-title m-visible">{'Положительных отзывов'|t}: <span>
		{if $sMinReview gt "0"}{'От'|t} {$sMinReview}{/if}
	</span>
	<div class="kwork-icon icon-down-arrow"></div>
</h3>
<div class="card__content-column">
	<div class="card__content-header">
		<strong>{'Положительных отзывов'|t}</strong>
		<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
			<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16" data-tooltip-side="right" data-tooltip-text="{'Количество положительных отзывов у продавца'|t}" data-tooltip-theme="dark">?</span>
		</span>
	</div>
	<div class="card__content-body">
		<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
		<div>
			<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_1" type="radio" value="1" {if $sMinReview eq "1"} checked="checked" {/if}>
			<label for="sminreview_1">{'От'|t} 1</label>
		</div>
		{if $attributeReviews[5] > 0}
			<div class="m-mt10">
				<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_5" type="radio" value="5" {if $sMinReview eq "5"} checked="checked" {/if}>
				<label for="sminreview_5">{'От'|t} 5</label>
			</div>
		{/if}
		{if $attributeReviews[20] > 0}
			<div class="m-mt10">
				<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_20" type="radio" value="20" {if $sMinReview eq "20"} checked="checked" {/if}>
				<label for="sminreview_20">{'От'|t} 20</label>
			</div>
		{elseif $attributeReviews[10] > 0}
			<div class="m-mt10">
				<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_10" type="radio" value="10" {if $sMinReview eq "10"} checked="checked" {/if}>
				<label for="sminreview_10">{'От'|t} 10</label>
			</div>
		{/if}
		{if $attributeReviews[100] > 0}
			<div class="m-mt10">
				<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_100" type="radio" value="100" {if $sMinReview eq "100"} checked="checked" {/if}>
				<label for="sminreview_100">{'От'|t} 100</label>
			</div>
		{elseif $attributeReviews[50] > 0}
			<div class="m-mt10">
				<input name="sminreview" class="js-kwork-filter-input styled-radio" id="sminreview_50" type="radio" value="50" {if $sMinReview eq "50"} checked="checked" {/if}>
				<label for="sminreview_50">{'От'|t} 50</label>
			</div>
		{/if}
	</div>
</div>
{if $sMinReview && !$attributeReviews[$sMinReview]}
	<script>
		$(".attibute-review-filter .filter-clear").attr("data-name", "sminreview").trigger("click");
	</script>
{/if}
{/strip}