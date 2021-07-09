{strip}
<div class="tips step-block-order_item  {if $config.track.isFocusGroupMember} border-top pt20 track--item {/if}">
    {if $config.track.isFocusGroupMember}
	<div class="track--item__sidebar">
		<div class="track--item__sidebar-image">
				<svg width="25" height="25" viewBox="0 0 25 25">
					<use xlink:href="#ico-tips-green"></use>
				</svg>
		</div>
	</div>
	<div class="track--item__main">
        {/if}
        {if $config.track.isFocusGroupMember}
			<div class="track--item__title">
				<h3 class="f15 bold">
                    {"Бонус продавцу"|t}
				</h3>
			</div>
        {/if}
        {if $config.track.isFocusGroupMember}
		<div class="track--item__content">
            {else}
			<div class="t-align-c">
				<h3 class="track-green pt10 font-OpenSansSemi mb10">{"Бонус продавцу"|t}</h3>
                {/if}
				<div class="f15 {if !$config.track.isFocusGroupMember}mt15 {/if}">
                    {"Если вам понравилось выполнение заказа,<br>вы можете отблагодарить продавца за хорошую работу, отправив бонус"|t}
				</div>
				<div class="f15 mt15 {if $config.track.isFocusGroupMember} t-align-c {/if}">
					<ul class="choose-bonus btn-group btn-group_3">
						<li class="btn white-btn" data-sum="{$tips->getFirstSum()}">
                            {$tips->getFirstSumText()}
						</li>
						<li class="btn white-btn" data-sum="{$tips->getSecondSum()}">
                            {$tips->getSecondSumText()}
						</li>
						<li class="btn white-btn js-btn-other-sum">{"Другая сумма"|t}</li>
					</ul>
				</div>
				<div class="comment-block f15 pt15 t-align-l" style="display: none">
					<form id="js-tips-send-form" class="ajax-disabling" action="/api/track/sendtips" method="post">
						<input type="hidden" name="orderId" value="{$order->OID}">
						<input id="tips-sum" type="hidden" name="sum" value="">
						<div class="js-other-sum other-sum" style="display: none; padding-bottom: 20px;">
							<label for="tips-another-sum">
                                {"Сумма"|t}
                                {if $order->currency_id == Model\CurrencyModel::USD}<span class="fs16">$</span>{/if}
							</label>
							<div>
								&nbsp;
								<input id="tips-another-sum" class="styled-input f14 w80"
									   placeholder="{$tips->getMinSum()|zero} - {$tips->getMaxSum()|zero}"
									   maxlength="5"
									   data-min="{$tips->getMinSum()}"
									   data-max="{$tips->getMaxSum()}"
								>&nbsp;
                                {if $order->currency_id == Model\CurrencyModel::RUB}<span class="fs16 rouble">Р</span>{/if}
								<div class="input-error mt5"></div>
							</div>
						</div>
						<label>
                            {"Комментарий для продавца"|t}<br>
							<textarea name="tips_message" class="styled-input db wMax f14 mh85"
									  maxlength="512"></textarea>
						</label>
						<div class="t-align-r mt10">
							<div class="field-error pull-left tips__field-error" style="display: none"></div>
							<a href="javascript:void(0)" class="js-cancel-bonus btn orange-btn">{"Отмена"|t}</a>
							<button class="js-send-bonus btn green-btn">{"Отправить бонус"|t}</button>
						</div>
					</form>
				</div>
			</div>
            {if $config.track.isFocusGroupMember}
		</div>
        {/if}
	</div>
{/strip}