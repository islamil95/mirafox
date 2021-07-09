<div class="package-level js-current-package-price" data-package-price="{$order->orderPackage->price_ctp|intval}" data-package-worker-price="{($order->orderPackage->price - $order->orderPackage->price_ctp)}" data-package-buyer-price="{$order->orderPackage->price|intval}" data-package-name="{$order->orderPackage->type|packageName}">
	<div style="width:100%; border-spacing:0px; border:1px solid #f0f0f0; box-sizing:border-box;">
		<table>
			<thead>
				<tr>
					<th>{'Повысить уровень пакета'|t}</th>
					<th class="nowrap">{'Кол-во'|t}</th>
					<th class="hidden-565">{'Срок'|t}</th>
					<th>{'Стоимость'|t}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$order->getBetterPackages() key=num item=betterPackage name=betterPackagesList}
					{assign var="upgradeAddPriceForBuyer" value=($order->upgradePrice($betterPackage))}
					{assign var="comission" value=(\OrderManager::calculateCommission($upgradeAddPriceForBuyer, $turnover + $order->price, $order->kwork->lang))}
					{assign var="upgradeAddPrice" value=($comission->priceWorker)}
					{assign var="upgradeAddDays" value=($order->upgradeDays($betterPackage))}
					<tr class="js-package-price js-toggle-wrapper-worker" data-package-worker-price="{$upgradeAddPrice}" data-package-buyer-price-full="{$upgradeAddPriceForBuyer}" data-package-price="{($betterPackage->price - $betterPackage->price_ctp)|intval}" data-package-buyer-price="{$betterPackage->price|intval}" data-package-name="{$betterPackage->type|packageName}" data-id="package-price-{$betterPackage->type}">
						<td style="padding-left: 10px">
							<input class="styled-checkbox" name="upgrade-package" type="checkbox" value="{$betterPackage->type}" />
							<label class="js-upgrade-package">
								{'Пакет'|t}	<a class="js-package-expand nowrap noselect" data-type="{$betterPackage->type}">{$betterPackage->type|packageName} <img src="{"/arrow_right_blue.png"|cdnImageUrl}" alt="" width="9"></a>
							</label>
						</td>
						<td style="text-align: right">{$order->count}</td>
						<td class="hidden-565">
							+{($upgradeAddDays > 0) ? $upgradeAddDays : 0} {declension count=$upgradeAddDays form1="день" form2="дня" form5="дней"}
						</td>
						<td class="nowrap" style="text-align: right; color:#009900">
							{if $order->currency_id == \Model\CurrencyModel::USD}
								<span class="usd">$</span>
							{/if}
							+{$upgradeAddPrice}
							{if $order->currency_id == \Model\CurrencyModel::RUB}
								&nbsp;<span class="rouble">Р</span>
							{/if}
						</td>
					</tr>
					{foreach from=$betterPackage->items() item=item}
						<tr class="package-option" data-type="{$betterPackage->type}">
							<td>
								{if $item->packageItem()->type == 'label' && !$item.value}
									<img class="dib mr10 v-align-m" src="{"/greengalka-disabled.png"|cdnImageUrl}" width="11" alt="—"/>
									<span style="color: #bbb">{$item->packageItem()->name}</span>
								{else}
									<img class="dib mr10" src="{"/greengalka.png"|cdnImageUrl}" width="11" alt="ОК"/>
									{$item->packageItem()->name}
								{/if}
							</td>
							<td style="text-align: right">
								{if $item->packageItem()->type == 'label' && !$item->value}
									—
								{elseif $item->type == 'text'}
									{$item->value} {if $order->count > 1} x {$order->count} {/if}
								{else}
									{if $item->packageItem()->can_lower && $item->value > 1}{'до'|t} {/if} {(int) $item->value * (int) $order->count}
								{/if}
							</td>
							<td class="hidden-565"></td>
							<td>&nbsp;</td>
						</tr>
					{/foreach}
					<tr class="visible-565 js-toggle-wrapper-worker">
						<td style="padding-top: 0">&nbsp;</td>
						<td class='nowrap font-OpenSansSemi t-align-r' style="padding:0 20px">
							{'Срок:'|t}
						</td>
						<td class="hidden-565">&nbsp;</td>
						<td class='nowrap f20 font-OpenSansSemi p10 m-text-center t-align-r' style="padding:0 20px;">
							+{($upgradeAddDays > 0) ? $upgradeAddDays : 0} {declension count=$upgradeAddDays form1="день" form2="дня" form5="дней"}
						</td>
					</tr>
					{if not $smarty.foreach.betterPackagesList.last}
						<tr class="table-separator"><td colspan="4"></td></tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</div>