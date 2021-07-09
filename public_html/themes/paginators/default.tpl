{strip}
	{* Эта пагинация повторяет оригинальную, поэтому в ней столько "особенностей"*}
	{if $paginator->hasPages()}
		<div class="paging">
			<div class="p1">
				<ul>
					{* Ссылка предыдущая *}
					{if !$paginator->onFirstPage()}
						<li class="prev">
							<a class="prev" href="{$paginator->previousPageUrl()}">&nbsp;</a>
						</li>&nbsp;
					{/if}

					{if $paginator->currentPage() > 3}
						<li>
							<a href="{$paginator->url(1)}">1</a>
						</li>&nbsp;
					{/if}
					{* Многоточие, если ссылок слишком много *}
					{if showLeftThreeDots($paginator)}
						<li>
							<a href="{$paginator->url(round(($paginator->currentPage() - 1) / 2))}">...</a>
						</li>&nbsp;
					{/if}

					{* Ссылки с номерами страниц *}
					{foreach item=element from=$elements}
						{* Ссылки с номерами страниц *}
						{if is_array($element)}
							{foreach item=url key=page from=$element}
								{if showPaginationPage($paginator, $page)}
									<li>
										{if $page == $paginator->currentPage()}
											<a href="{$url}" class="active">{$page}</a>
										{else}
											<a href="{$url}">{$page}</a>
										{/if}
									</li>&nbsp;
								{/if}
							{/foreach}
						{/if}
					{/foreach}

					{* Многоточие, если ссылок слишком много *}
					{if showRightThreeDots($paginator)}
						<li>
							<a href="{$paginator->url(round(($paginator->lastPage() + $paginator->currentPage() + 1) / 2))}">...</a>
						</li>&nbsp;
					{/if}
					{if ($paginator->lastPage() - $paginator->currentPage()) > 2}
						<li>
							<a href="{$paginator->url($paginator->lastPage())}">{$paginator->lastPage()}</a>
						</li>&nbsp;
					{/if}

					{* Ссылки следующая *}
					{if $paginator->hasMorePages()}
						<li class="next">
							<a class="next" href="{$paginator->nextPageUrl()}">&nbsp;</a>
						</li>
					{/if}
				</ul>
			</div>
		</div>
	{/if}
{/strip}
