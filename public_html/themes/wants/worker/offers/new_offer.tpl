{include file="header.tpl"}
{strip}
	{include file="fox_error7.tpl"}
	{block name="styles"}
		{Helper::printCssFile("/css/dist/projects.css"|cdnBaseUrl)}
	{/block}
	{Helper::registerFooterJsFile("/js/dist/projects.js"|cdnBaseUrl)}
	<div class="centerwrap clearfix pt20 pb20 page-offer fixed-offer">
		<div class="w700 mAuto">
			{if $offer}
				<h1 class="semibold f32">{'Редактирование предложения'|t}</h1>
			{else}
				<h1 class="semibold f32">{'Предложить услугу'|t}</h1>
			{/if}
			<div class="block-response mb15">
				<i class="ico-arrow-left dib v-align-m"></i>
				{if $offer}
					<a class="dib v-align-m f14 color-gray color-text"
					   href="{absolute_url route="offers"}">
						{'К списку предложений'|t}
					</a>
					{else}
					<a class="dib v-align-m f14 color-gray color-text"
					   href="{absolute_url route="projects_worker"}">
						{'К списку проектов'|t}
					</a>
				{/if}

			</div>
			<div class="send-request__block-title ">{'Детали проекта'|t}</div>
			<div class="clear"></div>
			{include file="wants/worker/wants_list_item.tpl" hasCardFooter=true}
			<div class="send-request__block-title">
				{if $offer}
					{'Редактирование предложения'|t}
				{else}
					{'Предложить услугу'|t}
				{/if}
			</div>
			<div class="p0">
				<form id="offer_kwork_form" name="offer_kwork" class="vf-form ajax-disabling of-form" action="" method="post">
					<div class="js-individual-kwork offer-individual border-b-none white-bg-border" style="padding: 15px 20px;">
						<input type="hidden"
							   id="offer-submited-type"
							   name="offerType"
							   value="custom" />
						<input type="hidden"
							   id="want-id"
							   name="wantId"
							   value="{$want->id}" />
						<input type="hidden"
							   id="offer-id"
							   name="offerId"
							   value="{$offer->id}" />
						<div class="js-offer-individual-item offer-individual__item mt0 overflow-hidden vf-block" data-target="description" data-name="offer-description">
							<label class="semibold offer-individual__label">
								{'Как вы будете решать задачу клиента'|t}
							</label>
							<div class="new-offer__tip">
								<div class="new-offer__tip-icon m-hidden">
									<i class="icon ico-warning"></i>
								</div>
								<div class="new-offer__tip-text">
									<ol>
										<li>{'Опишите свой релевантный опыт. Продемонстрируйте 1-3 примера выполнения похожей работы.'|t}</li>
										<li>{'Как именно вы собираетесь выполнять это задание. Опишите ключевые важнейшие моменты.'|t}</li>
										<li>
											{'Не используйте'|t}
											<span 
												class="tooltipster"
												data-tooltip-text="{'Шаблонные предложения раздражают покупателей, потому что показывают, что продавец не вник в проект. Поэтому Kwork не пропускает предложения-клоны. Пожалуйста, составляйте уникальные предложения по каждому проекту.'|t}">
												&nbsp;
												<a class="link_local" href="javascript:void(0)">
													{'шаблонные тексты'|t}
												</a>
												<span class="kwork-icon icon-custom-help icon-custom-help_no-hover ml5"></span>
											</span>
										</li>
									</ol>
								</div>
							</div>
							<div class="offer-individual__item-line">
								<div class="offer-sprite offer-sprite-comment pull-left m-hidden"></div>
								<div contenteditable="true" spellcheck="false" data-placeholder="{'Напишите, как вы будете решать задачу клиента'|t}{if $controlEnLang}{' на английском языке'|t}{/if}" class="description-field padded-width of-scrollable-field of-resizable-field vf-field js-content-editor wMax styled-input kwork-save-step__field-input_textarea" data-field-id="2" data-mistake-percent-long="true" data-min="{OfferManager::COMMENT_MIN_LENGTH}" data-max="{OfferManager::COMMENT_MAX_LENGTH}" {if $controlEnLang}data-en="true"{/if}>{$offer->comment|nl2br|replace:"\r":''|replace:"\n":''}</div>
								<textarea name="description"
										rows="12"
										style="height:auto;padding-bottom: 5px;"
										data-min="{OfferManager::COMMENT_MIN_LENGTH}"
										data-max="{OfferManager::COMMENT_MAX_LENGTH}"
										maxlength="{OfferManager::COMMENT_MAX_LENGTH}"
										class="js-content-storage js-kwork-description control-en styled-input wMax mh145 js-stopwords-check hidden"
										placeholder="{'Напишите, как вы будете решать задачу клиента'|t}{if $controlEnLang}{' на английском языке'|t}{/if}"
								>{$offer->comment}</textarea>
							</div>
							<div id="offer-description-hint" class="offer-individual__item-hint unactivated"></div>
							<div class="vf-error no-hide js-target-error offer-individual__item-error ml40 m-ml0"></div>
						</div>
						{if $showKworkPanelEdit || !$offer}
						<div class="js-active-kwork {if !$showKworkPanelEdit}hidden{/if}">
							{include file="wants/worker/offers/offer_active_kwork.tpl"}
						</div>
						{/if}
						{if !$showKworkPanelEdit || !$offer}
						<div class="js-custom-kwork">
							{include file="wants/worker/offers/offer_custom_kwork.tpl" actorType='worker' buttonDisableClass='js-submit-btn'}
						</div>
						{/if}
					</div>
					<div class="p20 w700 bgLightGray" style="border: 1px solid #eee;">
						<div class="mb10 fs12 light-gray lh15 js-payment-type-hint hidden">
							{'Вы указали желаемый вариант оплаты. Покупатель может выбрать другой или отредактировать задачи. Уменьшать срок или цену он не сможет.'|t}
						</div>
						{if $offer}
							<input type="submit" name="submit"
									class="js-submit-btn green-btn btn--big btn-disable-toggle"
									value="{'Сохранить'|t}" />
						{else}
							<input type="submit" name="submit"
							   class="js-submit-btn disabled green-btn btn--big btn-disable-toggle"
							   value="{'Предложить'|t}" disabled />
						{/if}
						<div class="js-individual-kwork-error offer-individual__error vf-bottom-error"></div>

						{if $kworks && !$offer}
							<div id="change-to-kwork-choose" class="f14 mt20">
								{'Также вы можете %sпредложить покупателю%s один из своих активных кворков'|t:"<span class=\"link_local\">":"</span>"}
							</div>
							<div id="change-to-custom-kwork" class="f14 mt20 hidden">
								{'Создать %sиндивидуальное предложение%s для покупателя'|t:"<span class=\"link_local\">":"</span>"}
							</div>
						{/if}
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="popup-nojs popup-clone-find" style="display: none;">
		<div class="overlay overlay-disabled"></div>

		<div class="popup_content popup_content_tpl_text balance-popup ">
			<h1 class="popup__title">{'Шаблонный текст'|t}</h1>
			<hr class="gray mt20 balance-popup__line">
			<div class="pull-right popup-close cur"
				 style="width:30px; height:20px; text-align:center; padding:10px 10px 10px;">X
			</div>
			<p class="mb10">{"Вы собираетесь отправить шаблонный текст предложения. Такие отклики очень редко помогают получить заказ и приводят к пустой трате коннектов. В тексте предложения стоит описать свой релевантный опыт, то, как вы поняли задачу, и как собираетесь ее выполнять. Это значительно повысит шанс получить заказ."|t}</p>

			<button class="white-btn big send-as-is">{"Отправить как есть"|t}</button>
			<button class="green-btn big pull-right want-change-text">{"Изменить текст"|t}</button>
		</div>
	</div>
{/strip}
<script>
{literal}
	var offer = {
		kworkPackages: {/literal}{if $kworkPackage}{$kworkPackage}{else}0{/if}{literal},
		maxKworkCount: {/literal}{$maxKworkCount}{literal},
		multiKworkRate: {/literal}{OrderManager::getMultiKworkRate($want->category)}{literal},
		customMinPrice: {/literal}{$customMinPrice}{literal},
		customMaxPrice: {/literal}{$customMaxPrice}{literal},
		stageMinPrice:  {/literal}{$stageMinPrice}{literal},
		isOrderStageTester: {/literal}{$isStageTester|intval}{literal},
		offerMaxStages: {/literal}{$offerMaxStages}{literal},
		lang: "{/literal}{$offerLang}{literal}",
		customPricesOptionsHtml:
		{/literal}{foreach from=$optionsPrices item=option}{literal}
		'<option data-payer-value="{/literal}{$option->price}{literal}" data-seller-value="{/literal}{$option->getPriceWithCommission}{literal}" value="{/literal}{$option->price}{literal}">{/literal}{$option->getDisplayWorkerPrice()}{literal}</option>' +
		{/literal}{/foreach}{literal} "",
	};
	var turnover = {/literal}{$turnover}{literal};
	var commission = {/literal}{App::config("commission_percent")}{literal};
	var controlEnLang = {/literal}{$controlEnLang|intval}{literal};
	var isWorker = 1;
{/literal}
</script>
{Helper::registerFooterCssFile("/css/dist/new-offer.css"|cdnBaseUrl)}
{Helper::registerFooterCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/commission.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/caret.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/dist/new-offer.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/new_offer.js"|cdnBaseUrl)}
{include file="footer.tpl"}
