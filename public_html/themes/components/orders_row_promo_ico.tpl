{strip}
	{* TODO: Если будет другая акция с бонус текстом то нужно будет пересмотреть *}
	{if $order.bonus_text && $order.status != OrderManager::STATUS_CANCEL}
		<img class="ml6 v-align-t" width="20" height="20" src="{"/promo/newyear_2018/order_bonus_snowflake.png"|cdnImageUrl}" alt="{"Заказ акционного кворка"|t}" title="{"Заказ акционного кворка"|t}">
	{/if}
{/strip}