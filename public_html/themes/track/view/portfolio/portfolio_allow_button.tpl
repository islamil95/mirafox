{strip}
	{if $track->order->isDone() && isAllowToUser($track->order->USERID) && $track->order->has_stages && $track->order->portfolio_type == "new" && $track->order->kwork->getPortfolioType() != "none"}
		<div>
			<form class="mt15" method="POST" action="{absolute_url route="allow_order_portfolio" params=["orderId"=> $track->order->OID]}">
				<button type="submit" class="green-btn normal-btn">{'Разрешить продавцу загружать портфолио'|t}</button>
			</form>
		</div>
	{/if}
{/strip}
