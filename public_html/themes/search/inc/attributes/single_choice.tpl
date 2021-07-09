<a class="clear-button" style="display: {if $active}block{else}none{/if};">
	{'Сбросить'|t}
</a>
<div>
	{foreach from=$classification->getChildren() key=k item=attribute}
	{if $k == 4}
</div>
<div class="js-unembedded-filter__list" {if !$activeIndex || $activeIndex < 4}style="display: none"{/if}>
	{/if}
	<div class="unembedded-item">
		<a href="javascript: void(0);" id="unembedded_attribute_{$attribute->getId()}" data-id="{$attribute->getId()}" {if $active == $attribute->getId()}class="active"{/if}>{$attribute->getTitle()}</a>
	</div>
	{/foreach}
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