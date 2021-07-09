<a class="clear-button" style="display: {if $active}block{else}none{/if};">
	{'Сбросить'|t}
</a>
<div class="custom-select custom-select_theme_multiple">
	<ul class="custom-select__list card__content-body">
		{foreach from=$classification->getChildren() key=k item=attribute}
			{if $k == 4}
	</ul>
</div>
<div class="js-unembedded-filter__list custom-select custom-select_theme_multiple" {if !$activeIndex || $activeIndex < 4}style="display: none"{/if}>
	<ul class="custom-select__list card__content-body">
			{/if}
		<li class="unembedded-item">
			<input class="custom-select__list-checkbox m-hidden"
				   type="checkbox"
				   value="{$attribute->getId()}"
				   id="unembedded_attribute_{$attribute->getId()}"
				   data-id="{$attribute->getId()}"
				   {if $attribute->getId()|in_array:$selected}checked{/if}
			/><label for="unembedded_attribute_{$attribute->getId()}"
				   class="custom-select__list-item {if $attribute->getId()|in_array:$selected}custom-select__list-item_active{/if}"
				   data-id="{$attribute->getId()}"
			>{$attribute->getTitle()}</label>
		</li>
		{/foreach}
	</ul>
</div>
{if $classification->getChildren()|@count > 4}
	<div class="js-unembedded-filter__more unembedded-filter__more{if $activeIndex && $activeIndex > 3} show-unembedded-filter{/if}"  data-show-text="{'Показать все'|t}" data-hide-text="{'Свернуть'|t}">
		{if $activeIndex && $activeIndex > 3}
			{'Свернуть'|t}
		{else}
			{'Показать все'|t}
		{/if}
	</div>
{/if}