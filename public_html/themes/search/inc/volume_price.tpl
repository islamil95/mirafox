{strip}
		<div class="popup-filter__group-title m-visible">{'Цена'|t}&nbsp;{'за'|t}&nbsp;
			{$baseVolume|zero} {declension($baseVolume, [$volumeType->name_accusative, $volumeType->name_plural_2_4, $volumeType->name_plural_11_19])}:</div>
		<div class="card__content-column">
			<div class="card__content-header">
				<strong>
					{'Цена'|t}&nbsp;{'за'|t}&nbsp;
					{$baseVolume|zero} {declension($baseVolume, [$volumeType->name_accusative, $volumeType->name_plural_2_4, $volumeType->name_plural_11_19])}:
				</strong>
			</div>
			<div class="card__content-body">
				<a href="javascript: void(0);" class="filter-clear" data-hidden-at-start="1">
					{'Сбросить'|t}
				</a>
				<div>
					<div class="price-filter-inputs-block">
						<div class="price-filter-input__box w100pi_mobile">
							<input type="number" id="volumePriceFrom" name="volume_price_from" class="volume-price-filter-input" placeholder="{if Translations::getLang() === 'en'}$ min{else}От руб.{/if}" value="{if $volumePriceFrom}{$volumePriceFrom}{/if}" autocomplete="off">
							<div class="price-filter-input__clear {if !$volumePriceFrom}hidden{/if}"></div>
						</div>
						<div class="price-filter-input__box ml10 w100pi_mobile">
							<input type="number" id="volumePriceTo" name="volume_price_to" class="volume-price-filter-input" placeholder="{if Translations::getLang() === 'en'}$ max{else}До руб.{/if}" value="{if $volumePriceTo}{$volumePriceTo}{/if}" autocomplete="off">
							<div class="price-filter-input__clear {if !$volumePriceTo}hidden{/if}"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
{/strip}