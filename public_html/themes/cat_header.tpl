{include file="fox_error7.tpl"}
{if $parent == 0}
	{* Эта часть шаблона отображется только при переходе по ссылке categories/all *}
	<div class="new_cat_header tagcloud cat-fon new-cat-fon-{$CATID} cat-fon-repeat">
		<div class="centerwrap cat_header_container">
			<div style="width:365px;min-width:365px;display:inline-block;float:left;padding-right:30px;padding-left:30px;">
				<div class="mt70 dib"></div>
				<h1>{$cname|stripslashes}</h1>
				<div class="tagcloud_delimiter"></div>
				<div class="tagcloud_tagline">{$tagline|t}</div>
			</div>
		</div>
	</div>
{/if}
	<div class="tagcloud standart_cat_header cat-fon-repeat {if $parent == 0}hidden{/if}">
		<div class="lg-centerwrap centerwrap">
			{if $parent}
				{* Хлебные крошки *}
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
									{include file="search/inc/breadcrumb_item.tpl" level=$attributeLevel++ type="attribute" name=$parentAttribute->getTitle() isLast=false url=($cseo|cat:"/"|cat:$parentAttribute->getAlias()) position=$breadcrumbPosition++}
								{/if}
							{/foreach}
						{/if}

						{* Выбранный атрибут *}
						{if $selectedAttributes|@count == 1}
							{assign var=firstSelectedAttribute value=$selectedAttributes.0}
							{include file="search/inc/breadcrumb_item.tpl" level=$attributeLevel type="attribute" name=$firstSelectedAttribute->getTitle() isLast=true url=($cseo|cat:"/"|cat:$firstSelectedAttribute->getAlias()) position=$breadcrumbPosition++}
						{/if}
					</ol>
				</div>
			{/if}
			<h1 class="f32 mt0">{$cname|stripslashes}{foreach $selectedAttributeParents as $parentAttribute}{if !$parentAttribute->isClassification()} - {$parentAttribute->getTitle()}{/if}{/foreach}{if $selectedAttributes|@count == 1} - {$firstSelectedAttribute->getTitle()}{/if}{foreach $unembeddedFilters as $classification}{foreach $classification->getChildren() as $subAttribute}{if $subAttribute->getId()|in_array:$selectedSubattributesIds} - {$subAttribute->getTitle()}{/if}{/foreach}{/foreach}</h1>
		</div>
	</div>