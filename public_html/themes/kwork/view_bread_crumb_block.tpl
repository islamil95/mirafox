{strip}
<div class="bread-crump block-response m-hidden" data-parent="{$parent_cat.id}" data-cat="{$kwork.category}">
	<ol itemscope itemtype="http://schema.org/BreadcrumbList" class="mb0 {if $nowrap}nowrap{/if}">
		{if $parent_cat|count gt 0}
			<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a itemprop="item"
				   href="{categoryUrl($parent_cat.alias)}">
					<span itemprop="name"
						{if $patchData && $patchData.parentCatId != $parent_cat.id}
							class="patch-outline__yellow"
						{/if}>
						{$parent_cat.name|t}
					</span>
				</a>
				<meta itemprop="position" content="1"/>
				<span class="bread-crump-delimiter">&nbsp;&nbsp;</span>
			</li>
		{/if}
		<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
			<a itemprop="item"
			   href="{categoryUrl($kwork.seo)}">
				<span itemprop="name"
				      {if $patchData && $patchData.category != $kwork.category}
						class="patch-outline__yellow"{/if}>
					{$kwork.name|t|stripslashes}
				</span>
			</a>
			<meta itemprop="position" content="2"/>
			{assign var=needDelimiter value=false}
			{foreach from=$classifications item=classification}
				{if !$classification->getParentId()}
					{$needDelimiter = true}
				{/if}
			{/foreach}
			{if $needDelimiter}
				<span class="bread-crump-delimiter">&nbsp;&nbsp;</span>
			{/if}
		</li>
		{* Показываем в хлебных крошках только один выбранный первоуровневый атрибут *}
		{foreach from=$classifications item=classification}
			{if !$classification->getParentId()}
				{foreach from=$classification->getChildren() item=attribute}
					{assign var=isChangedFirstLevelAttribute value=$patchData && !in_array($attribute->getId(),$patchData.attributesIds) }
					<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
						<a itemprop="item" href="{$baseurl}/{$catalog}/{$kwork.seo|lower|stripslashes}/{$attribute->getAlias()}">
							<span itemprop="name" class="color-gray {if $isChangedFirstLevelAttribute}patch-outline__yellow{/if}">{$attribute->getTitle()}</span>
						</a>
						<meta itemprop="position" content="3"/>
					</li>
					{break}
				{/foreach}
				{break}
			{/if}
		{/foreach}
	</ol>
</div>
{/strip}