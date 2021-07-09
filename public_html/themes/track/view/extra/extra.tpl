{strip}
	<div id="track-id-{$track->MID}"
		 class="{if $config.track.isFocusGroupMember}track--item {/if} tr-track step-block-order_item{if $isUnread} unread{/if}  {$direction}"
		 data-track-id="{$track->MID}">
        {if !$config.track.isFocusGroupMember}
		<div class="step-block-order_item_body font-OpenSans">
            {/if}
            {if $config.track.isFocusGroupMember}
				<div class="track--item__sidebar">
					<div class="track--item__sidebar-image {if $track->isClose() || $track->isCancel()}red{/if}">
                        {if $track->isNew() || $track->isDone()}
							<svg width="25" height="25" viewBox="0 0 25 25">
								<use xlink:href="#ico-green-extras"></use>
							</svg>
                        {elseif $track->isClose() || $track->isCancel()}
							<svg width="25" height="25" viewBox="0 0 25 25">
								<use xlink:href="#ico-red-extras"></use>
							</svg>
                        {/if}
					</div>
				</div>
            {else}
				<div class="f14 color-gray mt3 t-align-r">{$date|date}</div>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__main">
                {else}
				<div class="t-align-c">
                    {/if}
                    {if $config.track.isFocusGroupMember}
						<div class="track--item__title">
                            {if $track->isNew()}
								<h3 class="f15 bold">
                                    {if isAllowToUser($track->order->USERID)}{'Продавец предложил опции'|t}{else}{'Вы предложили опции'|t}{/if}
								</h3>
                            {elseif $track->isDone()}
								<h3 class="f15 bold">{'К заказу добавлены дополнительные опции'|t}</h3>
                            {elseif $track->isClose() || $track->isCancel()}
                                {if $track->isClose()}
									<h3 class="f15 bold">
                                        {if isAllowToUser($track->order->USERID)}{'Вы отказались от опций'|t}{else}{'Покупатель отказался от опций'|t}{/if}
									</h3>
                                {elseif $track->isCancel()}
									<h3 class="f15 bold">
                                        {if isAllowToUser($track->order->USERID)}{'Продавец отменил предложение опций'|t}{else}{'Вы отменили предложение опций'|t}{/if}
									</h3>
                                {/if}
                            {/if}
							<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>
						</div>
                    {else}
                        {if $track->isNew()}
							<i class="ico-green-extras"></i>
							<h3 class="pt10 font-OpenSansSemi track-green">
                                {if isAllowToUser($track->order->USERID)}{'Продавец предложил опции'|t}{else}{'Вы предложили опции'|t}{/if}
							</h3>
                        {elseif $track->isDone()}
							<i class="ico-green-extras"></i>
							<h3 class="pt10 font-OpenSansSemi track-green">{'К заказу добавлены дополнительные опции'|t}</h3>
                            {if isAllowToUser($track->order->USERID)}
                                {if isAllowToUser($track->user_id)}
									<h3 class="pt10 font-OpenSansSemi fs15"
										style="font-weight: 400;">
                                        {'Вы выбрали и оплатили следующие опции'|t}
									</h3>
                                {else}
									<h3 class="pt10 font-OpenSansSemi fs15"
										style="font-weight: 400;">
                                        {'Вы оплатили предложенные продавцом опции'|t}
									</h3>
                                {/if}
                            {else}
								<h3 class="pt10 font-OpenSansSemi fs15"
									style="font-weight: 400;">
                                    {'Покупатель оплатил следующие опции'|t}
								</h3>
                            {/if}
                        {elseif $track->isClose() || $track->isCancel()}
							<i class="ico-red-extras"></i>
                            {if $track->isClose()}
								<h3 class="pt10 font-OpenSansSemi track-red">
                                    {if isAllowToUser($track->order->USERID)}{'Вы отказались от опций'|t}{else}{'Покупатель отказался от опций'|t}{/if}
								</h3>
                                {if !$track->order->has_stages}
									<h3 class="pt10 font-OpenSansSemi fs15"
										style="font-weight: 400;">
                                        {if isAllowToUser($track->order->USERID)}{'Вы можете дозаказать опции в любой момент'|t}{else}{'Вы можете обсудить с покупателем возможные опции<br> и предложить их еще раз'|t}{/if}
									</h3>
                                {/if}
                            {elseif $track->isCancel()}
								<h3 class="pt10 font-OpenSansSemi track-red">
                                    {if isAllowToUser($track->order->USERID)}{'Продавец отменил предложение опций'|t}{else}{'Вы отменили предложение опций'|t}{/if}
								</h3>
								<h3 class="pt10 font-OpenSansSemi fs15"
									style="font-weight: 400;">
                                    {if isAllowToUser($track->order->USERID)}{'Вы можете дозаказать опции в любой момент'|t}{else}{'Вы можете обсудить с покупателем возможные опции<br> и предложить их еще раз'|t}{/if}
								</h3>
                            {/if}
                        {/if}
                    {/if}
                    {if !$config.track.isFocusGroupMember}
				</div>
                {else}
				<div class="track--item__content">
					<div class="f15">
                        {if $track->isDone()}
                            {if isAllowToUser($track->order->USERID)}
                                {if isAllowToUser($track->user_id)}
                                    {'Вы выбрали и оплатили следующие опции'|t}
                                {else}
                                    {'Вы оплатили предложенные продавцом опции'|t}
                                {/if}
                            {else}
                                {'Покупатель оплатил следующие опции'|t}
                            {/if}
                        {elseif $track->isClose() || $track->isCancel()}
                            {if $track->isClose()}
                                {if isAllowToUser($track->order->USERID)}{'Вы можете дозаказать опции в любой момент'|t}{else}{'Вы можете обсудить с покупателем возможные опции<br> и предложить их еще раз'|t}{/if}
                            {elseif $track->isCancel()}
                                {if isAllowToUser($track->order->USERID)}{'Вы можете дозаказать опции в любой момент'|t}{else}{'Вы можете обсудить с покупателем возможные опции<br> и предложить их еще раз'|t}{/if}
                            {/if}
                        {/if}
					</div>
                    {/if}
					<div class="track-extra-table-wrapper">
						<table style="width:100%;border-spacing:0px;border:1px solid #f0f0f0"
							   class="mt10 track-extra-table">
							<col width="67%">
							<colgroup width="11%">
							<thead style="background-color:#f0f0f0;" class="font-OpenSansSemi">
							<td style="padding:10px 20px;">{'Опция'|t}</td>
							<td class="ta-center nowrap">{'Кол-во'|t}</td>
							<td class="ta-center nowrap">{'Срок'|t}</td>
							<td class="ta-center nowrap">{'Стоимость'|t}</td>
							</thead>
							<tbody>
                            {assign var="extrasSum" value=0}
                            {if $track->upgradePackageExtra}
                                {$extrasSum = $extrasSum + $track->upgradePackageExtra->payer_price}
								<tr class="option font-OpenSans">
									<td>
                                        {Translations::translateByLang($track->order->kwork->lang, "Повышение пакета до уровня \"%s\"", PackageManager::getName($track->upgradePackageExtra->package_type, $track->order->kwork->lang))}
									</td>
									<td class="ta-center nowrap">
                                        {$track->upgradePackageExtra->count}
									</td>
									<td class="ta-center nowrap">
                                        {$track->upgradePackageExtra->duration|zero} {declension count=$track->upgradePackageExtra->duration form1="день" form2="дня" form5="дней"}
									</td>
									<td class="ta-center nowrap">
                                        {if isAllowToUser($track->order->USERID)}
                                            {include file="utils/currency.tpl" lang=$track->order->kwork->lang total=$track->upgradePackageExtra->payer_price}
                                        {else}
                                            {include file="utils/currency.tpl" lang=$track->order->kwork->lang total=$track->upgradePackageExtra->worker_price}
                                        {/if}
									</td>
								</tr>
                            {/if}
                            {foreach $track->extras as $extra}
								<tr class="option font-OpenSans">
									<td>
                                        {$extra->extra_title|mb_ucfirst}
									</td>
									<td class="ta-center nowrap">
                                        {if $extra->isVolumed()}
                                            {$extra->custom_volume|zero:1}
                                        {else}
                                            {$extra->count}
                                        {/if}
									</td>
									<td class="ta-center nowrap">
                                        {$extra->extra_duration|zero} {declension count=$extra->extra_duration form1="день" form2="дня" form5="дней"}
									</td>
									<td class="ta-center nowrap" data-price="{$extra->workerPrice()}">
                                        {if isAllowToUser($track->order->USERID)}
                                            {include file="utils/currency.tpl" lang=$track->order->kwork->lang total=$extra->buyerPrice()}
                                        {else}
                                            {include file="utils/currency.tpl" lang=$track->order->kwork->lang total=$extra->workerPrice()}
                                        {/if}
									</td>
								</tr>
                            {/foreach}
							</tbody>
						</table>
					</div>
                    {if $track->isNew() && $track->order->isInBuyStatus()}
                        {if isAllowToUser($track->order->USERID)}
							<div class="pt15 clearfix">
								<form class="worker_extra_suggestion_form"
									  action="{absolute_url route="track_extra_approveextrassubmited"}"

									  method="POST">
									<input type="hidden" name="track_id" value="{$track->MID}">
									<input type="hidden" name="approveextrassubmited" value="1">
									<input type="hidden" name="orderId" value="{$track->order->OID}">
									<button type="submit"
											class="green-btn pull-right ml10 worker_extra_suggestion_form_agree">
                                        {'Купить за '|t:$track->getExtrasSum()}{include file="utils/currency.tpl" lang=$track->order->kwork->lang total=$track->getExtrasSum()}
									</button>
								</form>
								<form class="worker_extra_suggestion_form_cancel"
									  action="{absolute_url route="track_extra_declineextrassubmited"}"
									  method="POST">
									<input type="hidden" name="track_id" value="{$track->MID}">
									<input type="hidden" name="declineextrassubmited" value="1">
									<input type="hidden" name="orderId" value="{$track->order->OID}">
									<button type="submit"
											class="orange-btn inactive pull-right">
                                        {'Не нужно, спасибо'|t}
									</button>
								</form>
							</div>
                        {else}
							<div style="padding-top:15px; padding-bottom:25px;">
								<form action="{absolute_url route="track_extra_declineextrassuggestion"}" method="POST">
									<input type="hidden" name="track_id" value="{$track->MID}">
									<input type="hidden" name="declineextrassuggestion" value="1">
                                    {if $track->extras|sizeof > 0}
										<input type="hidden" name="extrasSuggested" value="1">
                                    {/if}
                                    {if $track->upgradePackageExtra}
										<input type="hidden" name="upgradePackagesSuggested" value="1">
                                    {/if}
									<input type="hidden" name="orderId" value="{$track->order->OID}">
									<button type="submit" class="white-btn pull-right ml10">
                                        {'Отменить предложение'|t}
									</button>
								</form>
							</div>
                        {/if}
                    {/if}
                    {if $config.track.isFocusGroupMember}
				</div>
			</div>
            {/if}
            {if !$config.track.isFocusGroupMember}
		</div>
        {/if}
	</div>
{/strip}