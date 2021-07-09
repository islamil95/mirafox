{strip}
	<table class="order-info">
		<thead>
		<tr>
			<td class="order-info__head order-info__head--title">{'Повысить уровень пакета'|t}</td>
			<td class="order-info__head order-info__head--quantity nowrap">
				<div class="js-upgrade-package-title" style="display: none;">{'Кол-во'|t}</div>
			</td>
			<td class="hidden-565" style="width:14%;text-align:center;">{'Срок'|t}</td>
			<td style="width:18%;text-align:center;">{'Стоимость'|t}</td>
		</tr>
		</thead>
		<tbody>
		{foreach from=$upgradePackages item=upgradePackage}
			{assign var="upgradeAddPrice" value=$order->upgradePrice($upgradePackage)}
			{assign var="upgradeAddDays" value=$order->upgradeDays($upgradePackage)}
			<tr class="kwork js-upgrade-package js-toggle-wrapper" data-id="{$order->OID}{$upgradePackage->type}" style="background: #fafafa none repeat scroll 0 0;">
				<td colspan="1" class="order-info__title">
					<input class="styled-checkbox"
						   data-price="{$upgradeAddPrice|zero}"
						   data-type="{$upgradePackage->type}"
						   name="upgrade_package_{$order->OID}_{$upgradePackage->type}"
						   type="checkbox"
						   id="upgrade_package_{$order->OID}_{$upgradePackage->type}"
						   value="{$order->OID}{$upgradePackage->type}">
					<label for="upgrade_package_{$order->OID}_{$upgradePackage->type}" class="order-info__title-spoiler break-word">
						{'Пакет'|t} <span class="js-toggle-description-label link-color nowrap" data-id="{$order->OID}{$upgradePackage->type}">{PackageManager::getName($upgradePackage->type)} <img src="{"/arrow_right_blue.png"|cdnImageUrl}" width="9" alt=""></span>
					</label>
				</td>
				<td class="order-info__quantity" style="text-align:right;">{$o.count}</td>
				<td class="nowrap hidden-565" style="padding:10px 0;text-align:center;">
					+{($upgradeAddDays > 0) ? $upgradeAddDays : 0} {declension count=(($upgradeAddDays > 0) ? $upgradeAddDays : 0) form1=Translations::t("день") form2=Translations::t("дня") form5=Translations::t("дней")}</td>
				<td class="nowrap" class="nowrap" style="padding:10px 20px 10px 0;text-align:right;color:#009900">
					+{if isNotRu($order->kwork->lang)}${/if}{$upgradeAddPrice|zero}{if isRu($order->kwork->lang)} <span class="rouble">Р</span>{/if}
			</tr>
			{foreach $upgradePackage->items() as $item}
				<tr data-id="{$order->OID}{$upgradePackage->type}" class="js-toggle-description-block order-info__option kwork upgradepackageinfo"
						style="display:none; background: #fafafa none repeat scroll 0 0;">
					<td>
						<span class="f15 break-word" style="cursor:pointer;">
							{if $item->packageItem()->type == "label" && !$item->value}
								<img class="dib mr10 v-align-m" src="{"/greengalka-disabled.png"|cdnImageUrl}" width="11" alt="—"/>
								<span style="color: #bbb">{$item->packageItem()->name}</span>
							{else}
								<img class="dib mr10" src="{"/greengalka.png"|cdnImageUrl}" width="11" alt="ОК">
								{$item->packageItem()->name}
							{/if}
						</span>
					</td>
					<td class="order-info__quantity">
						{if $item->packageItem()->type == "label" && !$item->value}
							—
						{elseif $item->packageItem()->type == "text" || $item->packageItem()->type == "list"}
							{$item->value} {if $order->count > 1} x {$order->count} {/if}
						{else}
							{if $item->packageItem()->can_lower && $item->value > 1}{'до'|t} {/if} {$item->value * $order->count}
						{/if}
					</td>
					<td class="hidden-565"></td>
					<td></td>
				</tr>
			{/foreach}
			{foreach $order->getGroupedExtrasWithoutVolume() as $extra}
				<tr data-id="{$order->OID}{$upgradePackage->type}" class="js-toggle-description-block option upgradepackageinfo" style="display:none;">
					<td style="width: 100%;" class="break-word">
						{$extra->title}
					</td>
					<td class="nowrap" style="text-align:center;">
						{$extra->count}
					</td>
					<td class="nowrap hidden-565" style="text-align:right;"></td>
					<td class="" style="text-align:right;color:#009900;"></td>
				</tr>
			{/foreach}
			<tr class="visible-565 js-toggle-wrapper" data-id="{$order->OID}{$upgradePackage->type}">
				<td>&nbsp;</td>
				<td class='nowrap f14 fw600 m-text-center t-align-r' style="padding:0 20px">
					{'Срок:'|t}
				</td>
				<td class="hidden-565">&nbsp;</td>
				<td class='nowrap f20 fw600 m-text-center t-align-r' style="padding:0 20px;">
					+{($upgradeAddDays > 0) ? $upgradeAddDays : 0} {declension count=(($upgradeAddDays > 0) ? $upgradeAddDays : 0) form1=Translations::t("день") form2=Translations::t("дня") form5=Translations::t("дней")}
				</td>
			</tr>
		{/foreach}
		<tr class="kwork">
			<td style="padding:10px 0;" colspan="4">
				<form action="{route route="track_extra_upgradepackagesubmited"}" method="post" name="upgradepackages" class="js-upgrade-package-form upgradepackagestop">
					<input type="hidden" name="upgradepackagesubmited" value="1">
					<input type="hidden" name="orderId" value="{$order->OID}">
					<input type="hidden" name="upgrade_package_type" value="">
					<button class="green-btn pull-right submit noOutline disabled" type="button">
						{'Повысить за '|t}
						{if $order->currency_id == \Model\CurrencyModel::USD}
							$
						{/if}
						<span>0</span>
						{if $order->currency_id == \Model\CurrencyModel::RUB}
							&nbsp;руб.
						{/if}
					</button>
				</form>
			</td>
		</tr>
		</tbody>
	</table>
{/strip}