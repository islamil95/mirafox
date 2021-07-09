{* Популярные рубрики / Любимые рубрики *}

<div class="lg-centerwrap centerwrap main-wrap m-m0">
	<h1 class="t-align-c pt20 m-fs22">{'Популярные рубрики'|t}</h1>

	{* Список популярных рубрик *}
	<div class="collageSubcategories firstSubcategories-4">
		{assign var='imageDefault_x1' value='/collage/default_category@x1.jpg'}
		{assign var='imageDefault_x2' value='/collage/default_category@x2.jpg'}
		{foreach $popularRubrics as $rubric}

			{assign var='imageUrl' value="/collage/categories_second_level/{Translations::getLang()}/{$rubric.seo|lower|stripslashes}"}
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

			{* Рубрика *}
			<div class="collageSubcategories-col">
				<a href="{$baseurl}/{$catalog}/{$rubric.seo|lower|stripslashes}" class="collageSubcategories-item">
					<img src="{$image_x1|cdnImageUrl}"{if file_exists("$imagedir$image_x2")} srcset="{$image_x2|cdnImageUrl} 2x"{/if} alt="{$rubric.name|t|stripslashes}">
				</a>
			</div>
		{/foreach}
	</div>
</div>
