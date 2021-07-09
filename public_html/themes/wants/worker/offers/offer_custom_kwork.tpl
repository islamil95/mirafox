{strip}
	{if $conversation}
		<div class="mt20 kwork-category-select" >
			<label for="category" class="offer-individual__label">
				{'Рубрика:'|t}
			</label>
			<div class="offer-individual__item-line">

				<div class="offer-sprite offer-sprite-category m-hidden" style="top: -2px;"></div>
				<div class="offer-individual__category wMax">
					<div class="js-offer-individual-item" data-target="category">
						<div class="js-category-wrap">
							<select class="select-styled select-styled--thin long-touch-js js-category-select"
									id="offer_main_category"
									name="offer_main_category"
									autocomplete="off">
								<option selected disabled value="">{'Выберите рубрику'|t}</option>
								{foreach from=$categories key=parentId item=parentCategory}
									<option value="{$parentId}">
										{$parentCategory->name|t}
									</option>
								{/foreach}
							</select>
						</div>
						<div class="js-target-error offer-individual__item-error pl15"></div>
					</div>
					<div class="js-offer-individual-item" data-target="sub_category">
						{foreach from=$categories key=parentId item=parentCategory}
							{if $parentCategory->cats}
							<div data-category-id="{$parentCategory->id}" class="js-sub-category-wrap hidden">
								<select class="js-sub-category-select select-styled select-styled--thin long-touch-js"
										name=""
										autocomplete="off">
									<option selected disabled value="">{'Выберите подкатегорию'|t}</option>
									{foreach from=$parentCategory->cats key=id item=category}
										<option
												value="{$category->id}"
												{if $category->id == $want->category_id}selected="selected"{/if}>
											{$category->name|t}
										</option>
									{/foreach}
								</select>
							</div>
							{/if}
						{/foreach}
						<div class="js-target-error offer-individual__item-error pl10"></div>
					</div>
				</div>
			</div>

		</div>
	{/if}
	{* Если включено тестирование заказов с задачами *}
	{* Показываем ли возможность выбора заказов с задачами*}
	{assign var="isStagesAllow" value=false}
	{assign var="hasStages" value=($offer->order->stages && $offer->order->stages|count)}
	{if $isStagesAllow}
		<div class="js-offer-individual-item offer-individual__item offer-individual__item--flex offer-individual__item--flex-start" data-target="price">
			<div class="offer-sprite offer-sprite-budget m-hidden mt2"></div>
			<label for="request-kwork-price" class="offer-individual__label mt4 mr10">
				{'Стоимость'|t}
			</label>
			{if $offerLang == Translations::EN_LANG}
				<div class="mr5 usd v-align-m mt4 f20 m-hidden">$</div>
			{/if}
			<input type="tel"
						name="kwork_price"
						style="height: 32px;"
						id="request-kwork-price"
						class="js-kwork-price input styled-input f14 border-box m-border-box w140 m-wMax"
						placeholder="{if $customMinPrice == $customMaxPrice}{$customMaxPrice}{else}{$customMinPrice|zero} - {$customMaxPrice|zero}{/if}"
						value="{if $offer}{$offer->order->price|round:0}{/if}"/>
			{if $offerLang == Translations::DEFAULT_LANG}
				<div class="ml6 rouble v-align-m mt4 f20 m-hidden">Р</div>
			{/if}
			<div>
				<div class="js-target-error offer-individual__item-error"></div>
				<div id="kwork-price-description" class="f12 italic mt5">
					{if $offer}
						{"Вы получите"|t} {if $offer->order->currency_id == \Model\CurrencyModel::USD}<span>$</span>{/if} {$offer->order->crt|zero} {if $offer->order->currency_id == \Model\CurrencyModel::RUB} <span>{"руб"|t}.</span> {/if}
						<a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation ml3" data-price="{$offer->order->price}">{"Подробнее"|t}</a>
					{/if}
				</div>
			</div>
		</div>
		{* Селектор вариантов оплаты *}
		{include file="wants/worker/offers/offer_payment_type.tpl"}

		{* если создана только одна задача, то её даные берутся из заказа. Нужно для редактирования предложения продавцом *}
		{if (!$offer->order->stages || !$offer->order->stages|count) && $offer->order->kwork_title}
			{$stages = [['number' => 1, 'title' => $offer->order->kwork_title, 'payer_price' => $offer->order->price]]}
		{else}
			{$stages = $offer->order->stages}
		{/if}

		<div class="offer-individual__item mt20 js-stages-data js-offer-stages js-stages-payment-type {if !$hasStages}hidden{/if}"
			data-stages='{$stages|@json_encode|htmlspecialchars}'
			data-actor-type='{$actorType}'
			data-button-disable-class='{$buttonDisableClass}'
			data-page-type='offer'
			data-order-id='0'>
			{include file="wants/common/stages.tpl" spellcheck=true}
		</div>
	{/if}
	<div class="vf-block js-offer-individual-item js-full-payment-type offer-individual__item{if $isStagesAllow && (!$offer->order || $hasStages)} hidden{/if}" data-target="title" data-name="title">
		<label for="request-kwork-name" class="offer-individual__label">
			{'Название:'|t}
			<span class="kwork-icon icon-custom-help tooltipster ml5 m-hidden"
					data-tooltip-side="right"
					data-tooltip-text="{'Название используется для оформления отзыва или портфолио, когда услуга будет выполнена.'|t}"></span>
		</label>
		<div class="offer-individual__item-line">
			<div class="offer-sprite offer-sprite-list m-hidden"></div>
			<div contenteditable="true" spellcheck="false" data-placeholder="{'Введите название заказа'|t}{if $controlEnLang}{' на английском языке'|t}{/if}" class="input styled-input lh28 js-content-editor wMax contenteditable-single-line" data-field-id="3" data-max="80" {if $controlEnLang}data-en="true"{/if}>{$offer->order->kwork_title|nl2br|replace:"\r":''|replace:"\n":''}</div>
			<input type="text"
						name="kwork_name"
						id="request-kwork-name"
						placeholder="{'Введите название заказа'|t}{if $controlEnLang}{' на английском языке'|t}{/if}"
						maxlength="80"
						class="js-content-storage js-kwork-title control-en styled-input input wMax m-border-box hidden"
						value="{$offer->order->kwork_title}"/>
		</div>
		<div class="vf-error no-hide js-target-error offer-individual__item-error ml40 m-ml0"></div>
	</div>

	{if !$isStagesAllow}
		<div class="js-offer-individual-item offer-individual__item offer-individual__item--flex offer-individual__item--flex-start" data-target="price">
			<div class="offer-sprite offer-sprite-budget m-hidden mt2"></div>
			<label for="request-kwork-price" class="offer-individual__label mt4 mr10">
				{'Стоимость'|t}
			</label>
			{if $offerLang == Translations::EN_LANG}
				<div class="mr5 usd v-align-m mt4 f20 m-hidden">$</div>
			{/if}
			<input type="tel"
						name="kwork_price"
						style="height: 32px;"
						id="request-kwork-price"
						class="js-kwork-price input styled-input f14 border-box m-border-box w140 m-wMax"
						placeholder="{if $customMinPrice == $customMaxPrice}{$customMaxPrice}{else}{$customMinPrice|zero} - {$customMaxPrice|zero}{/if}"
						value="{if $offer}{$offer->order->price|round:0}{/if}"/>
			{if $offerLang == Translations::DEFAULT_LANG}
				<div class="ml6 rouble v-align-m mt4 f20 m-hidden">Р</div>
			{/if}
			<div>
				<div class="js-target-error offer-individual__item-error"></div>
				<div id="kwork-price-description" class="f12 italic mt5">
					{if $offer}
						{"Вы получите"|t} {if $offer->order->currency_id == \Model\CurrencyModel::USD}<span>$</span>{/if} {$offer->order->crt|zero} {if $offer->order->currency_id == \Model\CurrencyModel::RUB} <span>{"руб"|t}.</span> {/if}
						<a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation ml3" data-price="{$offer->order->price}">{"Подробнее"|t}</a>
					{/if}
				</div>
			</div>
		</div>
	{/if}
	<div class="clear"></div>
{/strip}
