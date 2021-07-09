{strip}
	{foreach $filters as $classification}
		{if $classification->getChildren()}
			{assign var="active" value=false}
			{foreach from=$classification->getChildren() key=index item=attribute}
				{if $attribute->getId()|in_array:$selected}
					{assign var="active" value=$attribute->getId()}
					{assign var="activeIndex" value=$index}
				{/if}
			{/foreach}

		<div class="popup-filter__group expandable">

			<h3 class="popup-filter__group-title m-visible">
				{$classification->getTitle()}: <span></span>
				<div class="kwork-icon icon-down-arrow"></div>
			</h3>

			<div class="card__content-column {if $classification->isAllowMultiple()}custom-select-wrapper_theme_left-filter{/if}">
				<div class="card__content-header">
					<strong>{$classification->getTitle()}</strong>
				</div>

				<div class="card__content-body unembedded-filter" data-id="{$classification->getId()}" data-parent-id="{$classification->getParentId()}">
					{if $classification->isAllowMultiple()}
						{include file="./attributes/multiple_choice.tpl"}
					{else}
						{include file="./attributes/single_choice.tpl"}
					{/if}
				</div>
			</div>

		</div>

		{/if}
	{/foreach}
{/strip}