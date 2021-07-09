{strip}
<div class="collageSubcategories {$firstSubcategories}">
	{assign var='imageDefault_x1' value='/collage/default_category@x1.jpg'}
	{assign var='imageDefault_x2' value='/collage/default_category@x2.jpg'}
	{section name=i loop=$sub_cats}

		{assign var='imageUrl' value="/collage/categories_second_level/{Translations::getLang()}/{$sub_cats[i].seo|lower|stripslashes}"}
		{assign var='image_x1' value="{$imageUrl}@x1.jpg"}
		{assign var='image_x2' value="{$imageUrl}@x2.jpg"}
		{assign var='image_x1_webp' value="{$imageUrl}@x1.webp"}
		{assign var='image_x2_webp' value="{$imageUrl}@x2.webp"}

		{* Заглушка *}
		{if !file_exists("$imagedir$image_x1")}
			{$image_x1 = $imageDefault_x1}
		{/if}
		{if !file_exists("$imagedir$image_x2")}
			{$image_x2 = $imageDefault_x2}
		{/if}

		{assign var="image_webp_exist" value=(file_exists("$imagedir$image_x1_webp") && file_exists("$imagedir$image_x2_webp"))}

		<div class="collageSubcategories-col cards-layout-item">
			<a href="{$baseurl}/categories/{$sub_cats[i].seo|lower|stripslashes}" class="collageSubcategories-item">
				{if $pageSpeedMobile}
					<picture class="lazy-load lazy-load_webp">
						{if $image_webp_exist}
							<source data-srcset="{$image_x1_webp|cdnImageUrl} 1x, {$image_x2_webp|cdnImageUrl} 2x" type="image/webp">
						{/if}
						<source data-srcset="{$image_x1|cdnImageUrl} 1x, {$image_x2|cdnImageUrl} 2x" type="image/jpeg">
						<img src="{"/collage/categories_second_level/blank.png"|cdnImageUrl}" data-src="{$image_x1|cdnImageUrl}" data-srcset="{$image_x2|cdnImageUrl} 2x" alt="{$sub_cats[i].name|t}">
					</picture>
				{else}
					<picture>
						{if $image_webp_exist}
							<source srcset="{$image_x1_webp|cdnImageUrl} 1x, {$image_x2_webp|cdnImageUrl} 2x" type="image/webp">
						{/if}
						<source srcset="{$image_x1|cdnImageUrl} 1x, {$image_x2|cdnImageUrl} 2x" type="image/jpeg">
						<img src="{$image_x1|cdnImageUrl}" srcset="{$image_x2|cdnImageUrl} 2x" alt="{$sub_cats[i].name|t}">
					</picture>
				{/if}
			</a>
		</div>
	{/section}
</div>
{/strip}