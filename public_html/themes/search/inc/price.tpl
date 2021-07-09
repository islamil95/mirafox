{strip}
<div class="popup-filter__group">
<h3 class="popup-filter__group-title m-visible">{'Цена'|t}:</h3>
<div class="card__content-column">
	<div class="card__content-header">
		<strong>{'Цена'|t}</strong>
	</div>
	<div class="card__content-body">
		<div class="m-hidden">
		{assign var=priceParts value=explode('_', $filterPrice)}
		<a href="javascript: void(0);" class="filter-clear" data-name="price" {if ($priceParts[0] && $priceParts[1]) || ($filterPrice === implode('_', $priceFilterBounds))} data-hidden-at-start="1"{/if}>
			{'Сбросить'|t}
		</a>
		{foreach item=bound key=priceKey from=$priceFilterBounds}
			<div>
				<input name="price" class="js-kwork-filter-input styled-radio" id="price_{$priceKey + 1}" type="radio" value="{$bound.value}" {if $filterPrice eq $bound.value}checked="checked"{/if}>
				<label for="price_{$priceKey + 1}">{$bound.title}</label>
			</div>
		{/foreach}
		</div>
		<div>
			{$priceLimitsMin = $priceLimits->min|default:500}
			{$priceLimitsMax = $priceLimits->max|default:5000}
			{$priceStep = 100}
			{$priceMinPlaceholder = 'От руб.'}
			{$priceMaxPlaceholder = 'До руб.'}

			{if Translations::getLang() != Translations::DEFAULT_LANG}
				{$priceLimitsMin = $priceLimits->min|default:10}
				{$priceLimitsMax = $priceLimits->max|default:100}
				{$priceStep = 10}
				{$priceMinPlaceholder = '$ min'}
				{$priceMaxPlaceholder = '$ max'}
			{/if}

			<div class="price-filter-inputs-block">
				<div class="price-filter-input__box">
					<input type="number" id="priceFrom" title="" class="price-filter-input" placeholder="{$priceMinPlaceholder}" value="{if $priceParts[0] > 0}{$priceParts[0]}{/if}" autocomplete="off" data-default="{$priceLimitsMin}">
					<div class="price-filter-input__clear {if !$priceParts[0]}hidden{/if}"></div>
				</div>
				<div class="price-filter-input__box ml10">
					<input type="number" id="priceTo" title="" class="price-filter-input" placeholder="{$priceMaxPlaceholder}" value="{if $priceParts[1] > 0}{$priceParts[1]}{/if}" autocomplete="off" data-default="{$priceLimitsMax}">
					<div class="price-filter-input__clear {if !$priceParts[1]}hidden{/if}"></div>
				</div>
			</div>

			<div class="popup-filter__price-range-slider m-visible" data-min="{$priceLimitsMin}" data-max="{$priceLimitsMax}" data-step="{$priceStep}"></div>
			<input type="hidden" name="price" id="volumePrice" value="">
		</div>
	</div>
</div>

{if ($priceParts[0] || $priceParts[1])}
	<script>
		window.addEventListener('DOMContentLoaded', function() {
			jQuery('.filter-clear').attr('data-hidden-at-start', 1);
		});
	</script>
{/if}

</div>
{/strip}