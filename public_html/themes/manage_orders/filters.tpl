{strip}
	<div class="live-tabs live-tabs--order-menu order-types-menu">
		<div class="js-live-tabs live-tabs__content" style="width: 100%">
			{* Вывод поискового таба *}
			{if $searchQuery neq null || $projectId neq null}
				<a href="{$baseurl}/orders?s={$s}&b={$b}&a={$a}&search={$searchQuery}"
				   class="live-tabs__item live-tabs__item--active">
					<span class="live-tabs__item-title">{'Поиск'|t}</span>
					{if $ordersCount > 0}
						<span class="live-tabs__item-number m-hidden">{$ordersCount}</span>
						<span class="live-tabs__item-number--m m-visible">({$ordersCount})</span>
					{/if}
				</a>
			{/if}

			{foreach from=$ordersItems key=status item=item name=orderList}
				<a href="{$baseurl}/{$ordersPath}?s={$status}{if $status == 'delivered'}&b=deadline{/if}{if $filter_user_id}&filter_user_id={$filter_user_id}{/if}"
				   class="js-live-tabs-item-not-active live-tabs__item {if $item->isActive}live-tabs__item--active{/if}">
					<span class="live-tabs__item-title">{$item->title}</span>
					{if $item->count}
						<span class="live-tabs__item-number{if !empty($item->ordersNumCssClasses)} {$item->ordersNumCssClasses}{/if} m-hidden">{$item->count}</span>
						<span class="m-visible live-tabs__item-number--m">({$item->count})</span>
					{/if}
				</a>
			{/foreach}
		</div>

		<a href="javascript:" class="live-tabs__prev"><span class="kwork-icon icon-prev"></span></a>
		<a href="javascript:" class="live-tabs__next"><span class="kwork-icon icon-next"></span></a>
	</div>
{/strip}
