{if $ordersItems['all']->count >= 10 }
{strip}
	{if $actor->type eq "worker"}
		{assign var=placeholder value="Поиск по названию заказа, покупателю"}
	{else}
		{assign var=placeholder value="Поиск по названию заказа, продавцу"}
	{/if}
	<div class="filter-order-search pull-right m-pull-reset">
		<form method="GET" class="mb0" action="">
			<input class="order-search-field-js wMax" 
				name="search" type="text" placeholder="{$placeholder|t}" value="{$searchQuery}"/>
			<input type="reset" value="" class="ico-close-12 fos-clear-js js_clearBtn">
			<input type="submit" value="" class="searchbtn">
			<input type="hidden" name="s" value="{$s}"/>
			<input type="hidden" name="b" value="{$b}"/>
			<input type="hidden" name="a" value="{$a}"/>
		</form>
	</div>
{/strip}
{/if}