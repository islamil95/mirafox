{strip}
	<div class="kwork-save-bundle relative">
		<table class="w100p relative {if $looked_lesson != 1}overlayed{/if}">
			<caption >
				<div class="bundle-overlay {if $looked_lesson != 1}show{else}hidden{/if}">
					<div class="bundle-overlay_message">
						<span class="f16 bold">{'Оформите кворк в трех ценовых вариантах'|t}</span><br><br>
						{if !($lang == Translations::EN_LANG)}
						{* Костыль - видео для англо кворка нет пока не показываем *}
						<span>{'Узнайте, какие возможности это дает, посмотрите короткое видео'|t}</span><br />
						<div class="js-show_lesson-btn btn btn_color_green btn_size_m mt10">{'Узнать'|t}</div>
						{/if}
					</div>
					<div class="bundle-overlay_back"></div>
				</div>
			</caption>
			<tbody class="kwork-save-bundle__table-header">
				<tr>
					<td class="bundle-item__header"></td>
					<td class="bundle-item__header">{PackageManager::getName('standard')}</td>
					<td class="bundle-item__header">{PackageManager::getName('medium')}</td>
					<td class="bundle-item__header">{PackageManager::getName('premium')}</td>
				</tr>
				<tr class="kwork-save-step__field-value_tooltip">
					<td class="bundle-item__field bundle-item__field_header bundle-item__field_header-textarea">
						<label class="tooltipster" data-tooltip-side="right" data-tooltip-theme="dark" data-tooltip-text="{'Краткое название пакета. Описывает, что включено в услугу.'|t}">
							{'Краткое описание'|t}
						</label>
					</td>
					<td class="js-field-block bundle-item__field bundle-item__field_textarea js-bundle-description js-bundle-standard">
						<div contenteditable="true"
						     spellcheck="false"
						     id="editor-bundle-standard-description"
						     class="js-content-editor kwork-save-step__field-input input input_size_s js-bundle-tooltip-error">
							{$kwork.standardPackage.description}
						</div>
						{* editable-pencil должен следовать сразу за полем т.к. в css используется селектор + *}
						<i class="fa fa-pencil editable-pencil"></i>
						<textarea id="bundle-standard-description"
						          class="js-content-storage kwork-save-step__field-input js-field-input hidden one-line"
						          name="bundle_standard_description"
								  data-required="true"
						          data-max="{KworkManager::MAX_PACKAGE_DESC_LENGTH}"
						          data-check-bad-words="true"
						          data-field-id="{\Kwork\StopWords\KworkTextFields::PACKAGE_DESCRIPTION}"
						          data-contenteditable="true"
						          data-check-text-valid="true"
						          data-has-tags="true"
						          {if $lang == Translations::EN_LANG && Translations::isDefaultLang()}placeholder="Краткое описание пакета Эконом на английском"{/if}
								{if $twinId || ($actor->lang == Translations::DEFAULT_LANG && $lang == Translations::EN_LANG)}
							data-only-english="true"
								{/if}>
						{$kwork.standardPackage.description}
						</textarea>
					</td>
					<td class="js-field-block bundle-item__field bundle-item__field_textarea js-bundle-description js-bundle-medium">
						<div contenteditable="true"
						     spellcheck="false"
						     id="editor-bundle-medium-description"
						     class="js-content-editor kwork-save-step__field-input input input_size_s js-bundle-tooltip-error">
							{$kwork.mediumPackage.description}
						</div>
						{* editable-pencil должен следовать сразу за полем т.к. в css используется селектор + *}
						<i class="fa fa-pencil editable-pencil"></i>
						<textarea id="bundle-medium-description"
						          class="js-content-storage js-field-input kwork-save-step__field-input hidden one-line"
						          name="bundle_medium_description"
						          data-max="{KworkManager::MAX_PACKAGE_DESC_LENGTH}"
								  data-required="true"
						          data-check-bad-words="true"
						          data-field-id="{\Kwork\StopWords\KworkTextFields::PACKAGE_DESCRIPTION}"
						          data-contenteditable="true"
						          data-check-text-valid="true"
						          data-has-tags="true"
						          {if $lang == Translations::EN_LANG && Translations::isDefaultLang()}placeholder="Краткое описание пакета Стандарт на английском"{/if}
								{if $twinId || ($actor->lang == Translations::DEFAULT_LANG && $lang == Translations::EN_LANG)}
							data-only-english="true"
								{/if}>
						{$kwork.mediumPackage.description}
						</textarea>
					</td>
					<td class="js-field-block bundle-item__field bundle-item__field_textarea js-bundle-description js-bundle-premium">
						<div contenteditable="true"
						     spellcheck="false"
						     id="editor-bundle-premium-description"
						     class="js-content-editor kwork-save-step__field-input input input_size_s js-bundle-tooltip-error">
							{$kwork.premiumPackage.description}
						</div>
						{* editable-pencil должен следовать сразу за полем т.к. в css используется селектор + *}
						<i class="fa fa-pencil editable-pencil"></i>
						<textarea id="bundle-premium-description"
						          class="js-content-storage js-field-input kwork-save-step__field-input hidden one-line"
						          name="bundle_premium_description"
						          data-max="{KworkManager::MAX_PACKAGE_DESC_LENGTH}"
								  data-required="true"
						          data-check-bad-words="true"
						          data-field-id="{\Kwork\StopWords\KworkTextFields::PACKAGE_DESCRIPTION}"
						          data-contenteditable="true"
						          data-check-text-valid="true"
						          data-has-tags="true"
						          {if $lang == Translations::EN_LANG && Translations::isDefaultLang()}placeholder="Краткое описание пакета Премиум на английском"{/if}
								{if $twinId || ($actor->lang == Translations::DEFAULT_LANG && $lang == Translations::EN_LANG)}
							data-only-english="true"
								{/if}>
						{$kwork.premiumPackage.description}
						</textarea>

						<div class="field-tooltip field-tooltip_description">
							<div class="field-tooltip__corner"></div>
							<div class="field-tooltip__image"></div>
							<div class="field-tooltip__text-block">
								<div class="field-tooltip__title">{'Кратко опишите, что данный пакет предлагает покупателям или какой конечный результат они получат'|t}</div>
								<div class="field-tooltip__message">
									{'Длина описания – не более %s символов'|t:KworkManager::MAX_PACKAGE_DESC_LENGTH}
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody class="js-bundle-extras">
				{* Сюда будут вставлятся строки в js *}
			</tbody>
			<tbody>
				<tr>
					<td class="bundle-item__field js-bundle-volume">
						<label class="bundle-item__field-label" for="">
							<span class="v-align-m dib">{'Количество'|t}</span> <span class="dib v-align-m" id="package-volume-type"></span>
						</label>
					</td>
					<td class="bundle-item__field bundle-item__field_text js-bundle-volume js-field-block js-bundle-standard js-bundle-volume-cell" colspan="3">
						<input type="text"
						       name="package_volume"
						       class="kwork-save-step__field-input js-bundle-item__input js-only-integer js-bundle-tooltip-error input package_volume"
						       value="{if $kwork.volume}{$kwork.volume|zero}{/if}" />
						{* editable-pencil должен следовать сразу за полем т.к. в css используется селектор + *}
						<i class="fa fa-pencil editable-pencil"></i>
					</td>
				</tr>
				<tr>
					<td class="bundle-header-move-trigger bundle-item__field bundle-item__field_select">
						<label class="bundle-item__field-label" for="bundle-standard-days">
							{'Срок выполнения'|t}
						</label>
					</td>
					<td class="bundle-item__field bundle-item__field_select bundle-standard-move-trigger js-bundle-standard">
						<select name="bundle_standard_duration" id="bundle-standard-days" class="js-bundle-item__input input input_size_s bundle-item__input_select">
							{for $i = 1 to 30}
								<option value="{$i}" class="{if $i>10}hidden{/if}"
										{if $kwork.standardPackage.duration == $i || (!$kwork.standardPackage.duration && $kwork.workTime == $i)} selected="selected"
									data-sel="sel"{/if}>{$i} {declension count=$i form1="день" form2="дня" form5="дней"}</option>
							{/for}
						</select>
					</td>
					<td class="bundle-item__field bundle-item__field_select bundle-medium-move-trigger js-bundle-medium">
						<label>
							<select name="bundle_medium_duration" id="bundle-medium-days" class="js-bundle-item__input input input_size_s bundle-item__input_select">
								{for $i = 1 to 30}
									<option value="{$i}" class="{if $i>10}hidden{/if}" {if $kwork.mediumPackage.duration == $i} selected="selected" data-sel="sel"{/if}>{$i} {declension count=$i form1="день" form2="дня" form5="дней"}</option>
								{/for}
							</select>
						</label>
					</td>
					<td class="bundle-item__field bundle-item__field_select bundle-premium-move-trigger js-bundle-premium">
						<label>
							<select name="bundle_premium_duration" id="bundle-premium-days" class="js-bundle-item__input input input_size_s bundle-item__input_select">
								{for $i = 1 to 30}
									<option value="{$i}" class="{if $i>10}hidden{/if}"
											{if $kwork.premiumPackage.duration == $i} selected="selected"
										data-sel="sel"{/if}>{$i} {declension count=$i form1="день" form2="дня" form5="дней"}</option>
								{/for}
							</select>
						</label>
					</td>
				</tr>
				<tr class="relative js-free-prices js-free-prices-tooltip-trigger">
					<td class="bundle-item__field bundle-header-move bundle-item__field_price-header">
						<span class="price_value_text tooltipster"
						      data-tooltip-text="{'Стоимость для покупателя будет выше на размер комиссии сервиса. Вы видите ту сумму, которую получите после выполнения данного пакета'|t}"
						      data-tooltip-theme="dark"
						      data-tooltip-side="right"
						      data-tooltip-class="package-edit-tooltip-text"
						      data-tooltip-block-class="package-add-bundle-extra-tooltip">
						{'Цена'|t},&nbsp;
							{if $lang == Translations::DEFAULT_LANG}
								{'руб.'|t}
							{else}
								$
							{/if}
						</span>
					</td>
					<td class="bundle-item__field bundle_price js-bundle_standard_price bundle-standard-move js-bundle-standard">
						{include file="kwork/new_edit/bundle_item_price/_price_label.tpl"}
					</td>
					<td class="bundle-item__field bundle_price js-bundle_medium_price bundle-medium-move js-bundle-medium">
						{include file="kwork/new_edit/bundle_item_price/_price_label.tpl"}
					</td>
					<td class="bundle-item__field bundle_price  js-bundle_premium_price bundle-premium-move js-bundle-premium">
						{include file="kwork/new_edit/bundle_item_price/_price_label.tpl"}
						<div class="price-tooltip">
							<div class="field-tooltip field-tooltip_bundle_price ta-left">
								<div class="field-tooltip__corner"></div>
								<div class="field-tooltip__image"></div>
								<div class="field-tooltip__text-block">
									<div class="field-tooltip__title">
										{'Выберите цены пакетов'|t}
									</div>
									<div class="field-tooltip__message">
										{'Допустимый диапазон цен указан с учетом реальных продаж кворков в выбранной категории.'|t}
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr class="js-bundle-item__field_add-custom-block kwork-save-step__field-value_tooltip">
					<td class="js-bundle-item__field_add-custom bundle-item__field bundle-item__field_add-custom">
						<div class="bundle-item__add-custom"></div>
						{'Добавить свою опцию'|t}
					</td>
					<td class="bundle-item__field_add-custom_empty bundle-item__field"></td>
					<td class="bundle-item__field_add-custom_empty bundle-item__field"></td>
					<td class="bundle-item__field_add-custom_empty bundle-item__field">

						<div class="field-tooltip field-tooltip_extras">
							<div class="field-tooltip__corner"></div>
							<div class="field-tooltip__image"></div>
							<div class="field-tooltip__text-block">
								<div class="field-tooltip__title">{'Добавьте разные опции к заказу.'|t}</div>
								<div class="field-tooltip__message">{'Это может быть срочность или повышенный объем заказа, или сопутствующие услуги (например: "Подберу изображение к статье", "Предварительный аудит сайта", "Плюс 3 черновых варианта лого на выбор"). Добавление к кворку опций позволит разнообразить Ваше предложение и увеличит среднюю стоимость заказа.'|t}</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<div class="clear"></div>
	</div>
{/strip}