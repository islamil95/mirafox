{strip}
	{if $paginator->hasPages() }
		<div class="paging">
			<div class="p1">
				<ul>
					{* Ссылка первая и предыдущая *}
					{if !$paginator->onFirstPage() }
						<li class="prev">
							<a class="prev" href="{$paginator->previousPageUrl()}">{"<"|t}</a>
						</li>
					{/if}
					{* Ссылки с номерами страниц *}
					{foreach item=element from=$elements}
						{* Троеточие, если ссылок слишком много *}
						{if is_string($element)}
							{$element}&nbsp;
						{/if}
						{* Ссылки с номерами страниц *}
						{if is_array($element)}
							{foreach item=url key=page from=$element}
								{if $page == $paginator->currentPage()}
									<li>
										<a class="active" href="{$url}">{$page}</a>
									</li>
								{else}
									<li>
										<a href="{$url}">{$page}</a>
									</li>
								{/if}
							{/foreach}
						{/if}
					{/foreach}
					{* Ссылки следующая и последняя *}
					{if $paginator->hasMorePages()}
						<li class="next">
							<a class="next" href="{$paginator->nextPageUrl()}">{">"|t}</a>
						</li>
					{/if}
				</ul>
			</div>
		</div>
	{/if}
{/strip}
