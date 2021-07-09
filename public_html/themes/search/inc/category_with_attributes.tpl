{if !$catViewType}
	{assign var=catViewType value=$catalog}
{/if}
{strip}
	<div class="allmusic">
        {if $catViewType == "gallery"}
            {if !$subCatSelectedId} {* Если не выбраны подкатегории *}
				<div class="card__content-header">
					<a style="color: #000; display: block;" href="{$baseurl}/{$catViewType}?rcat=all"><span class="arrow"></span> {"Все"|t}</a>
				</div>
			{else} {* Если выбраны подкатегории*}
				<div class="card__content-header">
					<a style="color: #000; display: block;" href="{$baseurl}/{$catViewType}?rcat={$categoryInfo[$rootSelectedId].seo}"><span class="arrow"></span> {$categoryInfo[$rootSelectedId].name|t}</a>
				</div>
            {/if}
		{else}
			<div class="card__content-header">
				<a style="color: #000; display: block;" href="{$baseurl}/{$catViewType}/{$parentseo|lower|stripslashes}"><span class="arrow"></span> {$parentname|t}</a>
			</div>
        {/if}
		<div class="card__content-body ">
			<ul id="foxdontshowcats">
				<li class="subcats {if !$selectedAttributes}active{/if}">
					<a class="category subId" data-id="{$CATID}" href="javascript: void(0);">
						<span class="arrow"></span> {$cname|stripslashes}
					</a>
					{if $attributesTree}
						{include file="search/inc/attributes_list.tpl" attributesTree=$attributesTree}
					{/if}
				</li>
			</ul>
			{literal}
				<script>
					var attributes = {/literal}{$attributesTree|@json_encode}{literal};
					window.attributesTree = attributes;
					window.isLinksCategory = {/literal}{if $isLinksCategory}{$isLinksCategory}{else}0{/if}{literal};
					var selectedAttributesIds = {/literal}{$selectedAttributesIds|@json_encode}{literal};
					var packageFilters = {/literal}{$packageFilters|@json_encode}{literal};
					var packageItemsConditions = {/literal}{$packageItemsConditions|@json_encode}{literal};
					var activeItemId = {/literal}{if $selectedAttribute}{$selectedAttribute->getId()}{else}null{/if}{literal};
					var topFilters;
					window.addEventListener('DOMContentLoaded', function() {
						topFilters = new TopFilters();
						topFilters.refresh(attributes, selectedAttributesIds, packageFilters, packageItemsConditions);
					});
				</script>
			{/literal}
		</div>
	</div>
{/strip}