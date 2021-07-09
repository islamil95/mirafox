{strip}
<div class="top-categories d-flex flex-wrap justify-content-center{if $cardsLayout} cards-layout{/if}">
	{assign var='subcategoryMaxShow' value=3}
	{assign var='imageDefault_x1' value='/collage/default_category@x1.jpg'}
	{assign var='imageDefault_x2' value='/collage/default_category@x2.jpg'}
	{foreach from=(array_slice([], 0, 8)) item=category}
		{assign var='subcategoryCount' value=count($category->cats)}

		{assign var='categoryUrl' value="/categories/{$category->seo}"}

		{assign var='image' value="/collage/categories_first_level/{Translations::getLang()}/{$category->seo}"}
		{assign var='image_x1' value="{$image}@x1.jpg"}
		{assign var='image_x2' value="{$image}@x2.jpg"}
		{assign var='image_x1_webp' value="{$image}@x1.webp"}
		{assign var='image_x2_webp' value="{$image}@x2.webp"}

		{* Заглушка *}
		{if !file_exists("$imagedir$image_x1")}
			{$image_x1 = $imageDefault_x1}
		{/if}
		{if !file_exists("$imagedir$image_x2")}
			{$image_x2 = $imageDefault_x2}
		{/if}

		<div class="top-categories-col{if $cardsLayout} cards-layout-item{/if}">
			<div class="top-category">
				<a href="{$categoryUrl}" class="top-category-link"></a>
				<div class="top-category-content">
					<ul>
						{foreach from=(array_slice($category->cats, 0, $subcategoryMaxShow)) item=subcategory}
							<li>
								<a href="/categories/{$subcategory->seo}">{$subcategory->short_name}</a>
							</li>
						{/foreach}
						{if $subcategoryCount > $subcategoryMaxShow}
							<li>
								<a href="{$categoryUrl}">
									{'И еще %s %s'|t:($subcategoryCount - $subcategoryMaxShow):declension($subcategoryCount - $subcategoryMaxShow, [Translations::t('рубрика'), Translations::t('рубрики'), Translations::t('рубрик')])}
								</a>
							</li>
						{/if}
					</ul>
				</div>

				{assign var="image_webp_exist" value=(file_exists("$imagedir$image_x1_webp") && file_exists("$imagedir$image_x2_webp"))}

				{if $pageSpeedMobile}
					<picture class="lazy-load lazy-load_webp">
						{if $image_webp_exist}
							<source data-srcset="{$image_x1_webp|cdnImageUrl} 1x, {$image_x2_webp|cdnImageUrl} 2x" type="image/webp">
						{/if}
						<source data-srcset="{$image_x1|cdnImageUrl} 1x, {$image_x2|cdnImageUrl} 2x" type="image/jpeg">
						<img src="{"/collage/categories_first_level/blank.png"|cdnImageUrl}" data-src="{$image_x1|cdnImageUrl}" data-srcset="{$image_x2|cdnImageUrl} 2x" alt="{$category->name}">
					</picture>
				{else}
					<picture>
						{if $image_webp_exist}
							<source srcset="{$image_x1_webp|cdnImageUrl} 1x, {$image_x2_webp|cdnImageUrl} 2x" type="image/webp">
						{/if}
						<source srcset="{$image_x1|cdnImageUrl} 1x, {$image_x2|cdnImageUrl} 2x" type="image/jpeg">
						<img src="{$image_x1|cdnImageUrl}" srcset="{$image_x2|cdnImageUrl} 2x" alt="{$category->name}">
					</picture>
				{/if}
			</div>
		</div>
	{/foreach}
</div>
{/strip}