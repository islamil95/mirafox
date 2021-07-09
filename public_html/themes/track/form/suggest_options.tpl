<div class="options" style="display:none;">
	<div style="width:100%;border-spacing:0px;border:1px solid #f0f0f0;box-sizing:border-box;">
		{if $order->getUpgradeExtras() && $order->getUpgradeExtras()->count()}
			{foreach $order->getUpgradeExtras() as $extras}
				<input type="hidden" id="newe{$extras->EID}" value="{$extras->getPrice()}"/>
				<input type="hidden" id="newt{$extras->EID}" value="{$extras->duration}"/>
			{/foreach}
		{/if}
		<div style="background: #fafafa none repeat scroll 0 0;" id="addextras" class="block-state-active">
			<div class="order-extras order-extras-mobile pb10" id="newextrachecks">
				<ul class="order-extras-list order-extras-list-bottom track-extras-list extra-block-js" id="foxPostForm">
					{if $order->getUpgradeExtras() && $order->getUpgradeExtras()->count()}
						{foreach $order->getUpgradeExtras() as $extras}
							<li class="order-extra-item{if !$extras->is_volume} kwork-extra{/if}" data-id="{$extras->EID}">
								<input class="styled-checkbox order-extras__input"
									   data-price-worker="{$extras->getPriceWithCommissionWOFormatting($order->kwork->lang)}"
									   data-price="{$extras->getPrice()}"
									   data-time="{$extras->duration}"
									   data-id="{$extras->EID}"
									   name="gextras[]"
									   type="checkbox"
									   value="{$extras->EID}"
								/>
								<label for="order-extras_{$extras->EID}" class="w460 mb0 option-item__text">{$extras->etitle|stripslashes|html_entity_decode|mb_ucfirst}</label>
								
								{if $order->getOrderVolumeType() && $order->data->kwork_volume && $extras->is_volume}
									<div class="kwork-count-wrapper-volume pull-right">
										<div class="bold pull-left">{'Выберите кол-во'|t}</div>
										<input type="hidden" name="as_volume" value="1" />
										<input type="text" class="volume_mobile kwork-save-step__field-input input input_size_s js-field-input js-only-numeric js-volume-order w108i p5 ml10" id="extra_count{$extras->EID}" data-multiplier="{$order->data->kwork_volume}" data-base-time="{if $order->getOrderBaseTime()}{$order->getOrderBaseTime()}{else}1{/if}" data-additional-time="{if $order->getOrderAdditionalTime()}{$order->getOrderAdditionalTime()}{elseif $order->data->volume_type_id==$order->data->custom_volume_type_id}0{else}1{/if}" data-max="{$order->maxOrderVolume()}" name="extra_count{$extras->EID}" />
									</div>
								{else}
									<div class="option-item__price m-visible">
										+
										{if $order->currency_id == \Model\CurrencyModel::USD}
											<span class="usd">$</span>
										{/if}
										<span class="option-item__price-value">{$extras->getPriceWithCommissionWOFormatting($order->kwork->lang)}</span>
										{if $order->currency_id == \Model\CurrencyModel::RUB}
											&nbsp;
											<span class="rouble">Р</span>
										{/if}
									</div>
									<div class="kwork-count-wrapper clearfix">
										<div class="bold pull-left">{'Выберите кол-во'|t}</div>
										<div class="kwork-count pull-right">
											<a href="javascript:;" class="kwork-count__link kwork-count_minus js-kwork-count-link" touch-action="manipulation"></a>
											<input type="text" value="1" class="kworkcnt_mobile" readonly data-max="{$maxKworkCount}" id="mobile_extra_count{$extras->EID}" />
											<a href="javascript:;" class="kwork-count__link kwork-count_plus js-kwork-count-link" touch-action="manipulation"></a>
										</div>
									</div>
									<div class="m-hidden order-extras__select-block">
										<select class="floatright h25 styled chosen_select" id="extra_count{$extras->EID}" name="extra_count{$extras->EID}">
											{for $i=1 to $maxKworkCount}
												<option value="{$i}" data-price="{$extras->getPrice() * $i}" data-price-worker="{$extras->getPriceWithCommission($i, $order->kwork->lang, $turnover + $order->price)}">
													{$i} ({$extras->getLocalizedPriceWithCommissionString($i, $order->kwork->lang, $turnover + $order->price)|stripslashes})
												</option>
											{/for}
										</select>
									</div>
								{/if}
							</li>
						{/foreach}
					{/if}
				</ul>
				<a id="add-custom-option" class="cur dib pl10 mt10" href="javascript:void(0);">
					{'Добавить еще опцию'|t}
				</a>
			</div>
			<div style="right:-282px;top:-38px;" class="block-state-active_tooltip">
				{'Стоимость опций указана с учетом комиссии системы. Покупатель увидит большие суммы. Например, вместо 400 руб. у покупателя отобразится 500 руб.'|t}
			</div>
		</div>
	</div>
</div>