{strip}
	<tr>
		<td style="width:10%"></td>
		<td class="offer-desc">
			<span class="order-info__extra-name">
				{$extra->extra_title}
			</span>
		</td>
		<td align="right" class="offer-price">
			<span class="order-info__extra-price">
				{if isUserOrContextRu()}
					{$extra->count} ({$extra->price|zero}&nbsp;
					<span class="rouble">Р</span>
					)
				{else}
					{$extra->count} (
					<span class="usd">$</span>
					{$extra->price|zero})
				{/if}
			</span>
		</td>
	</tr>
{/strip}