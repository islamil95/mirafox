{strip}
    {if $order->currency_id == \Model\CurrencyModel::USD}
		<span class="usd">$</span>
    {/if}
    {if $order->has_stages && $order->isNotNew()}
        {*Для задачных *}
        {if isAllowToUser($order->USERID) || $priceFor == "payer"}
            {$order->stages_price|zero}
        {else}
            {$order->stages_crt|zero}
        {/if}
    {else}
        {if isAllowToUser($order->USERID) || $priceFor == "payer"}
            {$order->price|zero}
        {else}
            {$order->crt|zero}
        {/if}
    {/if}
    {if $order->currency_id == \Model\CurrencyModel::RUB}
		&nbsp;
		<span class="rouble">Р</span>
    {/if}
{/strip}