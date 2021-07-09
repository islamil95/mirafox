<div class="bread-crump block-response">
	<ol itemscope itemtype="http://schema.org/BreadcrumbList" class="mb0">
		{assign var=breadcrumbPosition value=1}

		{* Родительская категория *}
		{include file="search/inc/breadcrumb_item.tpl" level=1 type="category" name=$parentname isLast=false url=$parentseo position=$breadcrumbPosition++}
		{* Подкатегория *}
		{include file="search/inc/breadcrumb_item.tpl" level=2 type="category" name=$cname isLast=(!$selectedAttributes) url=$cseo position=$breadcrumbPosition++}

		{assign var=attributeLevel value=1}

		{* Родительские атрибуты *}
		{if $selectedAttributeParents}
			{foreach $selectedAttributeParents as $parentAttribute}
				{if !$parentAttribute->isClassification()}
					{include file="search/inc/breadcrumb_item.tpl" level=$attributeLevel++ type="attribute" name=$parentAttribute->getTitle() isLast=false url=($cseo|cat:"?attribute_id="|cat:$parentAttribute->getId()) position=$breadcrumbPosition++}
				{/if}
			{/foreach}
		{/if}

		{* Выбранный атрибут *}
		{if $selectedAttributes|is_array && $selectedAttributes|@count == 1}
			{assign var=firstSelectedAttribute value=$selectedAttributes.0}
			{include file="search/inc/breadcrumb_item.tpl" level=$attributeLevel type="attribute" name=$firstSelectedAttribute->getTitle() isLast=true url=($cseo|cat:"?attribute_id="|cat:$firstSelectedAttribute->getId()) position=$breadcrumbPosition++}
		{/if}
	</ol>
</div>