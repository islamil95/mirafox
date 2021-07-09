{extends file="page_with_user_header.tpl"}

{block name="content"}
{capture assign=workerPriceSpan}
	{if $lang == Translations::EN_LANG}${/if}
	<span class="tooltip__title-worker-price">{$realprice|zero}</span>
	{if $lang == Translations::DEFAULT_LANG}&nbsp;{'руб.'|t}{/if}
{/capture}
{capture assign=payerPriceSpan}
	{if $lang == Translations::EN_LANG}${/if}
	<span class="tooltip__title-payer-price">{$tooltipPrice|zero}</span>
	{if $lang == Translations::DEFAULT_LANG}&nbsp;{'руб.'|t}{/if}
{/capture}

{Helper::printJsFile("/js/slick.min.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/jquery.kworkpopup.js"|cdnBaseUrl)}
{Helper::printCssFile("/css/dist/jquery.kworkpopup.css"|cdnBaseUrl)}
{Helper::printJsFile("/js/jquery.kworkmore.js"|cdnBaseUrl)}
{Helper::printCssFile("/css/dist/kwork-edit.css"|cdnBaseUrl)}

{Helper::printJsFile("/js/kwork_new_edit.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/jquery.imgareaselect.min.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/add-files.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/caret.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/components/volume_type_input.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/components/file-upload-block.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/field-tooltips.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/pages/save_kwork.js"|cdnBaseUrl)}
{* bootstrap modal *}
{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
{Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{* get youtube thumbnail *}
{Helper::printJsFile("/js/components/youtube-thumbnail.js"|cdnBaseUrl)}

{* lineProgressbar *}
{Helper::printCssFile("/css/components/jquery.lineProgressbar.css"|cdnBaseUrl)}
{Helper::printJsFile("/js/components/jquery.lineProgressbar.js"|cdnBaseUrl)}

 <!-- Trumbowyg WYSIWYG -->
{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
{if Translations::isDefaultLang()}
{Helper::printJsFile("/trumbowyg/langs/ru.min.js"|cdnBaseUrl)}
{/if}
{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
{Helper::printJsFile("/trumbowyg/plugins/colors/trumbowyg.colors.min.js"|cdnBaseUrl)}
{Helper::printCssFile("/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css"|cdnBaseUrl)}
<!-- !Trumbowyg WYSIWYG -->

<span id="app">
	{* LanguageTool - утилита для проверки грамматики на странице *}
	<language-tool field-ids="step1-name|step1-description|step2-service-size|step1-instruction"
		formatted-field-ids="step1-description|step1-instruction"
		server="{$languageToolServer}">
	</language-tool>
</span>
{Helper::printJsFile("/js/dist/kwork-edit.js"|cdnBaseUrl)}
<div class='fox_error hidden'><div class="centerwrap fox_error_content"></div></div>
<div class="centerwrap pt20 mb20 kwork-save-page lang-{Translations::getLang()}">
	<form class="js-kwork-save-form" enctype="multipart/form-data" method="post">
		<input type="hidden" name="draft_id" id="draft_id" value="{$draftId}" />
		<input type="hidden" name="lang" id="lang" value="{$lang}" />
		{if $twinId}
			<input type="hidden" name="twin_id" id="twin_id" value="{$twinId}" />
		{/if}
		<div class="pull-left">
			<h1 class="f32 sm-text-center">
				{if isset($kwork.id)}
					{'Редактирование кворка'|t}
				{else}
					{'Создание кворка'|t}
				{/if}
			</h1>
			<div class="block-response mb15">
				<i class="ico-arrow-left dib v-align-m"></i>
				<a class="dib v-align-m f14 color-gray color-text" href="{$baseurl}/manage_kworks">{'К списку кворков'|t}</a>
			</div>
		</div>
		<div class="clearfix"></div>

		{assign var=en_on_ru value=($lang == Translations::EN_LANG && Translations::getLang() == Translations::DEFAULT_LANG)}

		{if $en_on_ru}
			<div class="card card__content kwork-edit-tip-block">
				<div class="kwork-edit-tip-block__icon">!</div>
				<div>
					Составьте качественное описание кворка, будто оно написано носителем английского языка. <a class="kwork-edit-tip">Как повысить качество описания кворка?</a>
				</div>
			</div>
			<br />
			<div class="clearfix"></div>
		{/if}

		<div class="js-step card card_borderless kwork-save-step kwork-save-page__step position-r" data-step="1">
			<div class="card__content kwork-save-step__container">
				<div class="card__content-inner kwork-save-step__header">
					<div class="kwork-save-step__header-inner">
						<div class="kwork-save-step__number">1</div>
						<div class="kwork-save-step__title">{'Основное'|t}</div>
					</div>
				</div>
				<div class="kwork-save-step__content kwork-save-step__animation card__content-inner card__content-inner_separator">
					<div class="js-field-block kwork-save-step__field-block">
						<div class="kwork-save-step__field-label">
							<label class="kwork-save-step__field-label-name" for="step1-name">{'Название'|t}</label>
							<div class="js-field-input-hint kwork-save-step__field-hint">{'70 максимум'|t}</div>
							<div class="js-kwork-save-field-error kwork-save-step__field-error hidden"></div>
						</div>
						<div class="kwork-save-step__field-value kwork-save-step__field-value_tooltip">
							<input type="text"
								name="title"
								   data-required="true"
								class="js-field-input js-content-storage kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_name input one-line"
								value="{$kwork.title}"/>
							<div class="field-tooltip field-tooltip_name">
								<div class="field-tooltip__corner"></div>
								<div class="field-tooltip__image"></div>
								<div class="field-tooltip__text-block">
									<div class="field-tooltip__title">{'Введите название кворка.'|t}</div>
									<div class="field-tooltip__message">{'Оно должно отражать суть вашего предложения и описывать услугу, которую вы готовы выполнить.'|t}</div>
								</div>
							</div>
						</div>
					</div>
					<div class="js-field-block kwork-save-step__field-block" style="display: none !important;">
						<div class="kwork-save-step__field-label">
							<label class="kwork-save-step__field-label-name" for="step1-category">{'Категория'|t}</label>
							<div class="js-kwork-save-field-error kwork-save-step__field-error hidden"></div>
						</div>
						<div class="kwork-save-step__field-value kwork-save-step__field-value_tooltip">
							<div class="js-category-select">
								<select class="select-styled select-styled--thin long-touch-js f15 parents input input_size_s dib js-price-changer js-category-select pli4"
										id="parents"
										name="category"
										data-placeholder=""
										autocomplete="off"
										value="{$kwork.category_id}"
								>
									<option disabled hidden value="-1" selected="selected">&nbsp;</option>
                                    {foreach from=$categories item=category}
                                        {$selected = ""}
                                        {if $parentCategory->id == $want->category_id}
                                            {$selected = 'selected="selected" data-sel="sel"'}
                                        {/if}
										<option value="{$category->CATID}" {$selected}>
                                            {$category->name|t}
										</option>
                                    {/foreach}
								</select>
							</div>
							<div class="js-category-hints mt15"></div>
							<div class="field-tooltip field-tooltip_category">
								<div class="field-tooltip__corner"></div>
								<div class="field-tooltip__image"></div>
								<div class="field-tooltip__text-block">
									<div class="field-tooltip__title">{'Выберите категорию, наиболее подходящую для этого кворка.'|t}</div>
									<div class="field-tooltip__message">{'Это позволит покупателям быстро найти ваш кворк.'|t}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card__content-inner kwork-save-step__header">
					<div class="kwork-save-step__header-inner">
						<div class="kwork-save-step__number">2</div>
						<div class="kwork-save-step__title">{'Описание'|t}</div>
					</div>
				</div>
				<div class="kwork-save-step__content kwork-save-step__animation card__content-inner card__content-inner_separator">
					{include file="kwork/new_edit/reject_reasons_list.tpl" type=["description"]}
					<div class="js-field-block kwork-save-step__field-block">
						<div class="kwork-save-step__field-label">
							<label class="kwork-save-step__field-label-name" for="step1-description">
								{if $allowDescriptionFiles}
									{'Описание и файлы'|t}
								{else}
									{'Описание'|t}
								{/if}
							</label>
							<div class="js-field-input-hint kwork-save-step__field-hint">{'100 минимум'|t}</div>
							<div class="js-kwork-save-field-error kwork-save-step__field-error hidden"></div>
						</div>
						<div class="kwork-save-step__field-value kwork-save-step__field-value_tooltip pb0">
							<textarea id="step1-description"
										{if empty($canModer)}data-min="100" data-max="1200"{/if}
										data-required="true"
										data-field-name="{'описания'|t}"
									  	data-mistake-percent-long="true"
										data-has-tags="true"
										{if $twinId || ($actor->lang == Translations::DEFAULT_LANG && $lang == Translations::EN_LANG)}
										data-only-english="true"
										{/if}
										data-check-bad-words="true"
										data-min-lang-percent="{$minLangPercent}"
										data-check-text-valid="true"
										name="description"
										class="js-field-input js-field-input-description kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_description input"
										placeholder="{if $en_on_ru}Описание услуги (на английском){/if}"
							>{if $kwork.description|mb_strlen > 0}{$kwork.description}{/if}</textarea>
							<div class="field-tooltip field-tooltip_description">
								<div class="field-tooltip__corner"></div>
								<div class="field-tooltip__image"></div>
								<div class="field-tooltip__text-block">
									<div class="field-tooltip__title">{'Опишите услуги, которые вы предлагаете в этом кворке, как можно подробнее.'|t}</div>
									<div class="field-tooltip__message">{'Проявите оригинальность: добавьте аргументы в свою пользу, дайте гарантии, предоставьте бонусы и т.д., чтобы покупатель выбрал именно ваш кворк.'|t}</div>
								</div>
							</div>
						</div>
						<div class="kwork-save-step__field-value files-block">
							<div class="kwork-save-step__files">
								<div id="load-files-description" class="add-files" data-input-name="description"
									data-allow-upload="{if $allowDescriptionFiles}true{else}false{/if}"></div>
							</div>
						</div>
					</div>

				<div class="card__content-inner kwork-save-step__header">
					<div class="kwork-save-step__header-inner">
						<div class="kwork-save-step__number">3</div>
						<div class="kwork-save-step__title">{'Стоимость'|t}</div>
					</div>
				</div>
				<div class="kwork-save-step__content kwork-save-step__animation card__content-inner card__content-inner_separator">
						<div class="kwork-save-step__field-block kwork-save-step__field-label_multiline">
							<div class="kwork-save-step__field-label">
								<label class="kwork-save-step__field-label-name kwork-price-label">{'Стоимость 1 кворка'|t}</label>
							</div>
							<div class="kwork-save-step__field-value kwork-save-step__field-value_tooltip kwork-save-step__kwork-price">

								<input name="price"
									   id="price"
									   data-min="{$minPriceLimit}"
									   data-max="{$maxPriceLimit}"
									   data-lang="{$lang}"
									   data-hour-price="0"
									   data-project-price="0"
									   data-required="true"
									   style="width: 49%;min-width:250px"
									   type="tel"
									   class="js-price-limit-input js-price-changer js-input-number border-box styled-input styled-input--thin f15 pli4"
									   placeholder="{'Введите цену'|t}"
									   autocomplete="off"
									   value="{$kwork.price|zero}" />

								<div class="field-tooltip field-tooltip_price">
									<div class="field-tooltip__corner"></div>
									<div class="field-tooltip__image"></div>
									<div class="field-tooltip__text-block">
											<div class="field-tooltip__title" id="priceInfo" data-currency-val="{if $lang == Translations::EN_LANG}${else}руб.{/if}">
												{'Стоимость 1 кворка для покупателя - %s Продавец получает %s с продажи 1 кворка. Учитывайте это при создании кворка.'|t:($payerPriceSpan|lastdot):$workerPriceSpan}
											</div>
										<div class="field-tooltip__message"></div>
									</div>
								</div>
							</div>
						</div>
						{if $isQuickEnable}
						<div class="kwork-save-step__field-block kwork-save-step__field-block-quick">
							<div class="kwork-save-step__field-label kwork-save-step__field-label"></div>
							<div class="kwork-save-step__field-value kwork-save-step__field-value_tooltip">
								<input type="checkbox" class="js-quick-input" name="is_quick" id="step2-quick" {if $kwork.isQuick}checked="checked"{/if} value="1"/>
								<label class="kwork-save-step__field-label-name"
									   for="step2-quick">{'Включить опцию срочности'|t}</label>
								<div class="field-tooltip field-tooltip_quick">
									<div class="field-tooltip__corner"></div>
									<div class="field-tooltip__image"></div>
									<div class="field-tooltip__text-block">
										<div class="field-tooltip__title">{'Рекомендуем включить эту опцию.'|t}</div>
										<div class="field-tooltip__message">{'Так вы сможете повысить стоимость заказа на 50%% и привлечь покупателей, которым необходимо выполнить работу в сжатые сроки. Время выполнения срочного заказа рассчитывается системой и составляет в среднем 1 день на каждые 1 500 рублей заказа. При включении данной опции будьте готовы выполнить заказ в срок.'|t}</div>
									</div>
								</div>
							</div>
						</div>
						{/if}
				</div>
			</div>
			<div class="kwork-save-step__footer card__footer card__footer_borderless kwork-save-step__animation">
				<div class="btn btn_color_green btn_size_l js-save-kwork js-uploader-button-disable pull-right">
					{if isset($kwork.id)}
						{'Сохранить'|t}
					{else}
						{'Готово'|t}
					{/if}
				</div>
				<div class="preloader_kwork dib">
					<div class="preloader__ico prealoder__ico_kwork pull-left" ></div>
					<div class="pull-left preloader_kwork_text"></div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<input type="hidden" name="is_save_kwork" value="1"/>
	</form>
</div>
{literal}
	<script>
		var kworkLang = {/literal}'{$lang}'{literal};

		$(function () {
			KworkSaveModule.init({
                {/literal}{if App::config('commission_percent')}{literal}
				crt:{/literal}{App::config('commission_percent')}{literal},
                {/literal}{/if}{literal}
                {/literal}{if $kwork.id}{literal}
				saveMode: 'update',
				skip_first_photo_check: true,
				kworkId: {/literal}{$kwork.id}{literal},
				kworkPackage: {/literal}'{$kwork.packageType}'{literal},
				kworkUrl: {/literal}'{$kwork.url}'{literal},
				kworkPrice: {/literal}'{$kwork.price}'{literal},
				kworkCategoryId: {/literal}'{$kwork.category.CATID}'{literal},
				kworkAttributes: {/literal}'{$kwork.attributes|@json_encode}'{literal},
                {/literal}{/if}{literal}
			});
		});

        {/literal}{if $kwork.id}{literal};
		var kworkId = {/literal}{$kwork.id}{literal};
        {/literal}{/if}{literal};

		var draftId = {/literal}{if $draftId}{$draftId}{else}{literal}"underfined"{/literal}{/if}
                {literal};
	</script>
{/literal}
{/block}
