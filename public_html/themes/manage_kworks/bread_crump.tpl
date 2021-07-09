{strip}
	{if $post.parent_category_name}
		<div class="bread-crump query-card__breadcrumbs">
			<span class="query-card__breadcrumb-item">
					{$post.parent_category_name}
			</span>
			<span class="kwork-icon icon-next"></span>
			<span class="query-card__breadcrumb-item">
					{$post.category_name}
			</span>
			{if $post.classifications}
				<span class="kwork-icon icon-next"></span>
				<span class="query-card__breadcrumb-item">{$post.classifications}</span>
			{/if}
		</div>
	{/if}
{/strip}