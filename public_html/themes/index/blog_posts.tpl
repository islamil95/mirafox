{if !empty($blogPosts)}
	<div class="lg-centerwrap centerwrap main-wrap m-m0 clearfix{if $pageName == "index"} is_index{/if}">
		<h1 class="t-align-c m-fs22">{'Новости Kwork'|t}</h1>
		<div class="rubric-list">
			{foreach $blogPosts as $postInfo}
				<a class="rubric-list__item rubric-item" href="{$postInfo['url']}" target="_blank">
					<img src="{$postInfo['thumbnail_url']}?t=123" alt="{$postInfo['title_url']}" width="280" class="rubric-item__image">
					<span class="rubric-item__name">{$postInfo['title']}</span>
				</a>
			{/foreach}
		</div>
		<div class="pull-right mb15 semibold fs16">
			<a href="http://blog.kwork.ru/">{'Перейти в блог'|t}</a>
		</div>
	</div>
{/if}