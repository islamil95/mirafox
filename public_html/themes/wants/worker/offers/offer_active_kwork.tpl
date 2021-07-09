{strip}
	{assign var="selectedKwork" value=$offer->kwork->PID}
    {if $isChat}
        {assign var="kworks" value=array()}
    {/if}
	<div class="d-flex flex-wrap justify-content-between">
		<div class="pull-left mr10 send-request__kwork-name mt20 m-pull-reset">
			<label for="request-kwork-id" class="offer-individual__label">{'Выбрать кворк'|t}</label>
			<div class="offer-sprite offer-sprite-kwork pull-left m-hidden mt1"></div>
			<div class="pull-left w500 m-wMax">
				<select id="request-kwork-id" name="kwork_id" data-placeholder=" " class="js-request-kwork-id m-w250 input input_size_s" {if $selectedKwork}disabled{/if}>
					{if !$selectedKwork && $kworks|count > 1}
						<option selected disabled></option>
					{/if}
					{foreach $kworks as $kwork}
						<option data-time="{$kwork->days}"
								data-rate="{$kwork->rate}"
								data-package="{$kwork->is_package}"
								data-price="{$kwork->price|round}"
								data-workerprice="{$kwork->workerPrice|round}"
								data-base-volume="{$kwork->volume}"
								{if isset($kwork->isTimeVolume)}
								data-time-volume="{$kwork->isTimeVolume}"
								{/if}
								value="{$kwork->PID}"
								{if $selectedKwork == $kwork->PID}selected{/if}>
							{$kwork->gtitle|mb_ucfirst}
						</option>
					{/foreach}
				</select>
			</div>
			<div class="clear"></div>
		</div>
		<div class="pull-right w100 mt20 m-pull-reset m-wMax request-kwork-count-block">
			<label for="request-kwork-count" class="offer-individual__label">
				{'Количество'|t}
			</label>
			<select id="request-kwork-count"
					data-placeholder=" "
					name="kwork_count"
					class="js-request-kwork-count w100 m-w250 input input_size_s"
					{if $selectedKwork}disabled{/if}>
				{for $count=1 to 10}
					<option {if $selectedKwork && $offer->order->count == $count}selected{/if}>{$count}</option>
				{/for}
			</select>
		</div>
	</div>
	<div class="clear"></div>
	{if !$selectedKwork}
	<div class="clear"></div>

		<div class="mt20 js-order-extras send-request-form__order-extras order-extras" style="display:none;">
			<div class="mb20 mb10">{'Дополнительные опции'|t}</div>

			<div class="order-extras__container" id="newextrachecks">
				<ul class="js-add-extras order-extras__list" id="addextras"></ul>
				{if App::config("order.worker_extras.enable")}
					<a id="order-extras__create" class="js-order-extras-create cur pl10" onclick="
					   {if $offerLang == 'en'}$('.order-new-extras__input').on('input', (e) => e.target.value = e.target.value.replace(/[А-Яа-яЁё]/g, ''));{/if}">
						{'Создать опцию'|t}
					</a>
				{/if}
			</div>

		</div>

		<div class="pull-right js-send-request__total send-request__total clearfix m-m0" style="display: none">
			<div class="dib mr20">
				{'Срок'|t}:&nbsp;<span class="js-total-time">{$offer->order->kwork_days}</span>
			</div>
			<div class="js-total-sum send-request__total-sum dib f14 mr20">{$offer->order->price}</div>
			<div class="js-total-worker-sum color-light-green dib f14">{$offer->order->crt}</div>
		</div>
	{else}
		{insert name=declension value=a assign=days count=$offer->order->duration/Helper::ONE_DAY form1="день" form2="дня" form3="дней"}
		<div class="pull-right js-send-request__total send-request__total clearfix m-m0 mt10">
			<div class="dib mr20">{'Срок'|t}: <span>{$offer->order->duration/Helper::ONE_DAY}&nbsp;{$days}</span></div>
			<div class="send-request__total-sum dib f14 mr20">{'Стоимость'|t}: {include file="utils/currency.tpl" total=$offer->order->price currencyId=$offer->order->currency_id}</div>
			<div class="color-light-green dib f14">{'Вы получите'|t}:&nbsp;
				{include file="utils/currency.tpl" total=$offer->order->crt currencyId=$offer->order->currency_id}
				<a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation ml5" data-price="{$offer->order->price}">{'Подробнее'|t}</a>
			</div>
		</div>
	{/if}
	<div class="clear"></div>

{/strip}