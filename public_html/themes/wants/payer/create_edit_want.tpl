{include file="header.tpl"}
{strip}
    {Helper::printJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/field-tooltips.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/create-edit-want.js"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/create-edit-want.css"|cdnBaseUrl)}
	{include file="fox_error7.tpl"}
	<div class="centerwrap pt20 mb20">
        {* 7942 временно отключено *}
		{if false && UserManager::isTester($actor->id)}
		<h1 class="mb30 f32 sm-text-center">
			{if $want && $isNew}
				{'Создание проекта'|t}
			{elseif $want}
				{'Редактирование проекта'|t}
			{else}
				{'Опишите, что нужно сделать'|t}
			{/if}
		</h1>
		<div class="project-form">
			<div class="project-form__aside">
				<ul class="project-form__steps">
					<li class="project-form__steps-item{if $want->id} project-form__steps-item_saved{/if}" data-step="1">
						<i class="project-form__steps-icon project-form__steps-icon_one"></i>
						<i class="project-form__steps-icon project-form__steps-icon_done project-form__steps-icon_position_right"></i>
						{'Название и сфера проекта'|t}
						<a href="javascript:;" class="project-form__steps-edit{if !$want->id} hidden{/if}">
							<i class="kwork-icon icon-pencil"></i>
						</a>
					</li>
					<li class="project-form__steps-item{if $want->id} project-form__steps-item_saved{/if}" data-step="2">
						<i class="project-form__steps-icon project-form__steps-icon_two"></i>
						<i class="project-form__steps-icon project-form__steps-icon_done project-form__steps-icon_position_right"></i>
						{'Описание'|t}
						<a href="javascript:;" class="project-form__steps-edit{if !$want->id} hidden{/if}">
							<i class="kwork-icon icon-pencil"></i>
						</a>
					</li>
					<li class="project-form__steps-item{if $want->id} project-form__steps-item_saved{/if}" data-step="3">
						<i class="project-form__steps-icon project-form__steps-icon_three"></i>
						<i class="project-form__steps-icon project-form__steps-icon_done project-form__steps-icon_position_right"></i>
						{'Бюджет'|t}
						<a href="javascript:;" class="project-form__steps-edit{if !$want->id} hidden{/if}">
							<i class="kwork-icon icon-pencil"></i>
						</a>
					</li>
					<li class="project-form__steps-item{if $want->id} project-form__steps-item_saved{/if}" data-step="4">
						<i class="project-form__steps-icon project-form__steps-icon_four"></i>
						{'Просмотр и публикация'|t}
					</li>
				</ul>
			</div>
			<div class="project-form__content" id="foxPostForm">
				<form id="sendKworkRequest"
					  method="POST"
					  name="sendKworkRequest"
					  enctype="multipart/form-data"
					  class="js-form">
					{if $want}
						<input type="hidden" name="want_id" value="{$want->id}">
					{/if}

					{* Шаг 1 *}
					<div class="project-form__step" data-step="1">
						<div class="project-form__block">
							<div class="project-form__block-header">
								<div class="project-form__block-header-title">{'Название и сфера проекта'|t}</div>
								<div class="project-form__block-header-subtitle">{'Шаг %s из %s'|t:1:3}</div>
							</div>
							<div class="project-form__block-content">
								<div class="project-form__group">
									<label for="wish_name" class="project-form__block-title">{'Укажите название проекта'|t}</label>
									<input name="title"
										   id="wish_name"
										   type="text"
										   class="js-title-input w100p styled-input styled-input--thin f15 db"
										   placeholder="{'Введите название'|t}"
										   value="{$want->name|stripslashes}">
									<span class="js-title-error-field color-red hidden"></span>
								</div>
								<div class="project-form__block-title f13 ml15 mt10">{'Хорошие примеры названий'|t}:</div>
								<ul class="project-form__block-list f13 pl15">
									<li>{'Изменения в интернет-магазине на ModX'|t}</li>
									<li>{'Разработка фирменной атрибутики компании с нуля'|t}</li>
									<li>{'Адаптация большого веб-проекта под мобильные устройства'|t}</li>
								</ul>
							</div>
						</div>
						<div class="project-form__block">
							<div class="project-form__block-content">
								<div class="project-form__group js-category-select-wrapper">
									<label for="parents" class="project-form__block-title">{'Я ищу фрилансеров, которые специализируются на'|t}:</label>
									{insert name=get_categories2 assign=categories type=3 lang=$wantLang}
									<select class="select-styled select-styled--thin long-touch-js f15 parents js-category-select dib"
											id="parents"
											name="parents"
											autocomplete="off"
											data-placeholder="{'Выберите специальность'|t}">
										<option value="" selected>{if $isMobile && !$isTablet && !$onlyDesktopVersion}{'Выберите специальность'|t}{/if}</option>
										{foreach from=$categories key=parentId item=parentCategory}
											{$selected = ""}
											{foreach from=$parentCategory->cats key=id item=category}
												{if $category->id == $want->category_id}
													{$selected = 'selected="selected" data-sel="sel"'}
												{/if}
											{/foreach}
											<option value="{$parentId}" {$selected}>
												{$parentCategory->name|t}
											</option>
										{/foreach}
									</select>
									{foreach from=$categories key=parentId item=parentCategory}
										{if $parentCategory->cats}
											{$selected = false}
											{foreach from=$parentCategory->cats key=id item=category}
												{if $category->id == $want->category_id}
													{$selected = true}
												{/if}
											{/foreach}
											<select data-catId="{$parentCategory->id}"
													class="sub-{$parentId} js-sub-category-select js-sub-category-{$parentId} sub_category select-styled select-styled--thin long-touch-js f15 childs gig_categories dib mt15 {if !$selected}hidden{/if}"
													{if $selected}name="category"{/if}
													autocomplete="off"
													data-placeholder="{'Уточните специальность'|t}">
												<option value="" selected>{if $isMobile && !$isTablet && !$onlyDesktopVersion}{'Уточните специальность'|t}{/if}</option>
												{foreach from=$parentCategory->cats key=id item=category}
													<option style="color:#000" value="{$category->id}"
															data-max-days="{$category->max_days}"
															data-max-photo-count="{$category->max_photo_count}"
															{if $category->id == $want->category_id}selected="selected"
															data-sel="sel"{/if}>
														{$category->name|t}
													</option>
												{/foreach}
											</select>
										{/if}
									{/foreach}
									<div class="js-category-attributes-select-wrapper"></div>
									<span class="js-category-error-field color-red hidden db"></span>
								</div>
							</div>
							<div class="project-form__block-footer">
								<button type="button" class="green-btn btn--big pull-reset m-wMax js-project-goto-next{if $want->id} hidden{/if}">{'Далее'|t}</button>
								<button type="button" class="green-btn btn--big pull-reset m-wMax js-project-goto-last{if !$want->id} hidden{/if}">{'Готово'|t}</button>
							</div>
						</div>
					</div>
					{* Конец Шага 1 *}

					{* Шаг 2 *}
					<div class="project-form__step project-form__step-hidden" data-step="2">
						<div class="project-form__block">
							<div class="project-form__block-header">
								<div class="project-form__block-header-title">{'Описание'|t}</div>
								<div class="project-form__block-header-subtitle">{'Шаг %s из %s'|t:2:3}</div>
							</div>
							<div class="project-form__block-content">
								<label for="wish_description" class="project-form__block-title">{'Опишите суть проекта'|t}</label>
								<div class="project-form__block-title f13 ml15">{'Хорошее описание включает'|t}:</div>
								<ul class="project-form__block-list f13 pl15">
									<li>{'Что и в каком объеме нужно сделать'|t}</li>
									<li>{'Что должно получиться на выходе'|t}</li>
									<li>{'Особенности проекта и требования к фрилансерам'|t}</li>
								</ul>
								<div class="project-form__group mt20 relative">
								<textarea data-autoresize class="js-description-input text f15 autoheight-js js-stopwords-check"
										  style="height:auto;"
										  cols="74"
										  id="wish_description"
										  name="description"
										  rows="9"
										  placeholder="{'Опишите, что именно вам нужно, в каком объеме и за какой срок'|t}"
								>{$want->desc|stripslashes}</textarea>
									<span class="js-description-error-field color-red hidden"></span>
									<div class="color-gray f12 pt10">
										{'Максимальная длина - %s символов. Сейчас: %s'|t:1500:'<span class="wishdescused">0</span>'}
									</div>
									<div class="block-state-active no-hover">
										<div id="load-files-description" class="add-files" data-input-name="description"></div>
									</div>
								</div>
							</div>
							<div class="project-form__block-footer">
								<button type="button" class="white-btn btn--big pull-reset m-wMax js-project-goto-prev">{'Назад'|t}</button>
								<button type="button" class="green-btn btn--big pull-reset m-wMax ml30 js-project-goto-next{if $want->id} hidden{else} disabled{/if}"{if !$want->id} disabled{/if}>{'Далее'|t}</button>
								<button type="button" class="green-btn btn--big pull-reset m-wMax js-project-goto-last{if !$want->id} hidden{/if}">{'Готово'|t}</button>
							</div>
						</div>
					</div>
					{* Конец Шага 2 *}

					{* Шаг 3 *}
					<div class="project-form__step project-form__step-hidden" data-step="3">
						<div class="project-form__block">
							<div class="project-form__block-header">
								<div class="project-form__block-header-title">{'Укажите бюджет заказа'|t}</div>
								<div class="project-form__block-header-subtitle">{'Шаг %s из %s'|t:3:3}</div>
							</div>
							<div class="project-form__block-content">
								<div class="project-form__pay">
									<div class="project-form__pay-item project-form__pay-item_inactive">
										<div class="project-form__pay-inner">
											<i class="project-form__pay-icon project-form__pay-icon_time"></i>
											<div class="project-form__pay-title">{'Оплата по часам'|t}</div>
											<div class="project-form__pay-info">{'Подходит для длинных проектов'|t}</div>
											<div class="project-form__pay-label">{'Скоро'|t}</div>
										</div>
									</div>
									<div class="project-form__pay-item project-form__pay-item_active">
										<div class="project-form__pay-inner">
											<i class="project-form__pay-icon project-form__pay-icon_label-{$wantLang}"></i>
											<div class="project-form__pay-title">{'Оплата за проект'|t}</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="project-form__block">
							<div class="project-form__block-content">
								<div class="project-form__group">
									<label for="price_limit" class="project-form__block-title">{'Цена не более'|t}</label>
									<input name="price_limit"
										   id="price_limit"
										   data-min="{$minPriceLimit}"
										   data-max="{$maxPriceLimit}"
										   data-lang="{$wantLang}"
										   type="tel"
										   class="js-price-limit-input js-only-numeric w100p styled-input styled-input--thin f15 db"
										   placeholder="{'Введите цену'|t}" 
										   autocomplete="off" 
										   value="{$want->price_limit|substr:0:-3}">
									<span class="js-price-limit-error-field color-red hidden"></span>
								</div>
							</div>
							<div class="project-form__block-footer">
								<button type="button" class="white-btn btn--big pull-reset m-wMax js-project-goto-prev">{'Назад'|t}</button>
								<button type="button" class="green-btn btn--big pull-reset m-wMax ml30 js-project-goto-next{if $want->id} hidden{else} disabled{/if}"{if !$want->id} disabled{/if}>{'Далее'|t}</button>
								<button type="button" class="green-btn btn--big pull-reset m-wMax js-project-goto-last{if !$want->id} hidden{/if}">{'Готово'|t}</button>
							</div>
						</div>
					</div>
					{* Конец Шага 3 *}

					{* Просмотр и публикация *}
					<div class="project-form__step project-form__step-hidden" data-step="4">
						<div class="project-form__block">
							<div class="project-form__block-header">
								<div class="project-form__block-header-title">{'Опубликуйте задание'|t}</div>
							</div>
							<div class="project-form__block-content">
								<div class="card want-card js-want-container">
									<div class="card__content pb5">
										<div class="mb15">
											<div class="wants-card__header">
												<div class="wants-card__header-title first-letter js-project-title"></div>
												<div class="wants-card__header-right-block">
													<div class="wants-card__header-controls">
														<div class="wants-card__header-price wants-card__price m-hidden{if !$want->id} hidden{/if}">
															<span class="js-project-price"></span>
														</div>
													</div>
												</div>
											</div>
											<div class="mt10 br-with-lh js-project-description"></div>
											<div class="files-list project-form__files-list mt10{if $want->desc|mb_strlen >= 245} hidden{/if}"></div>
											<div class="wants-card__header-price wants-card__price mt10 m-visible{if !$want->id} hidden{/if}">
												<span class="js-project-price"></span>
											</div>
										</div>
										{if $user}
											{include file="wants/common/want_payer_statistic.tpl" user=$user}
										{/if}
									</div>
								</div>
								{if isNotAuth() && Translations::isDefaultLang()}
									<div class="project-form__group mt20">
										<label class="project-form__block-title" for="request-form__email">
											{'Адрес электронной почты'|t}
										</label>
										<input name="email"
											   id="request-form__email"
											   required
											   type="email"
											   class="js-email-input w100p styled-input styled-input--thin f15 db"
											   placeholder="{'Введите email'|t}">
										<span class="js-email-error-field color-red hidden db"></span>
										<span id="request-form__email_warning"
											  class="color-orange font-OpenSans f14 ml10"
											  style="display: none;">{'Введен неправильный email'|t}</span>
									</div>
								{/if}
							</div>
							<div class="project-form__block-footer project-form__block-footer_theme_flex">
								<button type="submit"
										class="js-sendKworkRequest__submit js-uploader-button-disable green-btn btn--big pull-reset m-wMax w250">
									{if $want}{'Сохранить'|t}{else}{'Опубликовать'|t}{/if}
								</button>
								<div class="js-preloader preloader-want" style="display: none">
									<div class="preloader__ico preloader-want__ico"></div>
									<div class="preloader-want__text">{'Сохранение...'|t}</div>
								</div>
								<input type="hidden" name="action" value="submit">
							</div>
						</div>
					</div>
					{if isNotAuth()}
						<div class="mt20 f14 t-align-c">
							{'Размещая проект, вы регистрируетесь и принимаете %sПользовательское соглашение%s и соглашаетесь на email-рассылки'|t:'<a href="/terms" target="_blank" class="color-text underline">':'</a>'}
						</div>
					{/if}
					{* Конец Просмотра и публикации *}

				</form>
			</div>
		</div>
		{else}
		<div class="w700">
			<h1 class="f32 sm-text-center sm-lh-120p">
				{if $want && $isNew}
					{'Создание проекта'|t}
				{elseif $want}
					{'Редактирование проекта'|t}
				{else}
					{'Опишите, что нужно сделать'|t}
				{/if}
			</h1>
			<div class="info-block" data-type="new_project">
				<div class="info-block__text f14 lh23 v-align-m sm-margin-reset">
					<p>{'Разместите свою задачу на бирже. Ваш проект станет видимым для тысяч фрилансеров, и некоторые из них сделают вам предложения. Изучите их рейтинг, портфолио и выберите лучших из них. Подтвердите заказ, когда будете удовлетворены результатом на 100%%. Только после этого оплата спишется в пользу продавца.'|t}</p>
				</div>
			</div>
			<div class="clear"></div>
			<div id="foxPostForm" class="pt0 p15-20 white-bg-border w700">
				<form id="sendKworkRequest"
					  method="POST"
					  name="sendKworkRequest"
					  enctype="multipart/form-data"
					  class="js-form">
					{if $want}
					<input type="hidden" name="want_id" value="{$want->id}" />
					{/if}
					<div>
						<div class="pt20 pb10 long-touch-js">
							<div class="field-tooltip-activator" data-show-from-width="1000">
								<div class="pb10">
									<label class="semibold mr5" for="wish_name">{'Основная задача'|t}</label>
								<span class="tooltipster kwork-icon icon-custom-help fs16before"
										data-tooltip-text="{'Укажите в заголовке главную задачу'|t}"></span>
								</div>
								<div class="offer-sprite offer-sprite-list m-hidden pull-left"></div>
								<input name="title"
										id="wish_name"
										type="text"
										class="js-title-input w618 m-wMax styled-input styled-input--thin f15 pull-left pli4"
										placeholder="{'Введите название'|t}"
										value="{$want->name|stripslashes}" />
								<span class="js-title-error-field color-red hidden"></span>
								<div class="tooltip-container">
									<div class="field-tooltip field-tooltip_name">
										<div class="field-tooltip__corner"></div>
										<div class="field-tooltip__image"></div>
										<div class="field-tooltip__text-block">
											<div class="field-tooltip__title">{'Примеры хороших заголовков:'|t}</div>
											<div class="field-tooltip__message">
												<ul>
													<li>{'Нужен разработчик для создания темы на WordPress'|t}</li>
													<li>{'Создать 3D-модель существующего дома'|t}</li>
													<li>{'Нужен дизайн для нового логотипа компании'|t}</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clear"></div>
					<div>
						<div class="pt10 pb10 long-touch-js">
							<div class="field-tooltip-activator" data-show-from-width="1000">
								<div class="pb10">
									<label class="semibold mb10 mr5" for="wish_description">
									{'Детальное описание задачи'|t}
								</label>
								<span class="tooltipster kwork-icon icon-custom-help fs16before"
										data-tooltip-text="{'Опишите услуги, которые вам нужны. Включите в описание важные аспекты.'|t}"></span>
								</div>
								<div class="offer-sprite offer-sprite-text m-hidden pull-left"></div>
								<div class="w618 pull-left m-wMax relative">
									<textarea data-autoresize class="js-description-input text f15 autoheight-js js-stopwords-check pli4 lh22 mhi210"
												style="height:auto;"
										  cols="74"
										  id="wish_description"
										  name="description"
												rows="9"
										  placeholder="{'Опишите, что именно вам нужно, в каком объеме и за какой срок'|t}"
								>{$want->desc|stripslashes}</textarea>
									<span class="js-description-error-field color-red hidden"></span>
									<div class="color-gray f12 pt10">
										{'Максимальная длина - %s символов. Сейчас: %s'|t:1500:'<span class="wishdescused">0</span>'}
									</div>
								</div>
								<div class="tooltip-container">
									<div class="field-tooltip field-tooltip_description">
										<div class="field-tooltip__corner"></div>
										<div class="field-tooltip__image"></div>
										<div class="field-tooltip__text-block">
											<div class="field-tooltip__title">{'Хорошее описание включает в себя:'|t}</div>
											<div class="field-tooltip__message">
												<ul>
													<li>{'Что конкретно нужно сделать'|t}</li>
													<li>{'В каком объеме (количестве) и в какой желаемый срок'|t}</li>
													<li>{'Какие умения или компетенции требуются от исполнителя'|t}</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix">
						<div class="pt10 pb20 ml40 m-ml0">
							<div class="block-state-active no-hover">
								<div id="load-files-description" class="add-files" data-input-name="description"></div>
								<div class="block-state-active_tooltip"
									 style="right:-280px; top: -60px;">
									{"На сайте есть ограничение на размер каждого файла: %sМб. Всё, что больше, можно заливать в любое облако и добавлять ссылку в сообщение."|t:$config.files.maxSize}
								</div>
							</div>
						</div>
					</div>
					<div class="clear"></div>
					<div>
						<div class="mt10 long-touch-js">
							<div>
								<div class="mb10">
									<label class="semibold mr5" for="price_limit">
										{'Цена не более'|t}
									</label>
									<span class="tooltipster kwork-icon icon-custom-help fs16before" data-tooltip-text="{'Отсекать предложения от продавцов свыше указанной суммы'|t}"></span>
								</div>
								<div class="offer-sprite offer-sprite-budget pull-left m-hidden mt2"></div>
								<div class="w618 m-wMax">
									<input name="price_limit" 
										   id="price_limit" 
										   data-min="{$minPriceLimit}" 
										   data-max="{$maxPriceLimit}" 
										   data-lang="{$wantLang}" 
										   data-hour-price="0" 
										   data-project-price="0" 
										   style="width: 49%;min-width:250px"
										   type="tel"
										   class="js-price-limit-input js-price-changer js-input-number border-box styled-input styled-input--thin f15 pli4"
										   placeholder="{'Введите цену'|t}" 
										   autocomplete="off" 
										   value="{$want->price_limit|substr:0:-3}" />
								</div>
								<span class="js-price-limit-error-field color-red hidden"></span>
							</div>
						</div>
					</div>
					<div class="clear"></div>

						{if isNotAuth() && Translations::isDefaultLang()}
						<div class="clear"></div>
						<div class="pt10">
								<div class="mb10">
								<label class="semibold mr5" for="request-form__email">
									{'Адрес электронной почты'|t}
								</label>
								<span class="tooltipster kwork-icon icon-custom-help fs16before"
										data-tooltip-text="{'На этот адрес электронной почты будет выслано письмо для подтверждения регистрации.'|t}"></span>
								</div>
							<div class="offer-sprite offer-sprite-mail pull-left m-hidden mt2"></div>
							<div class="w618 m-wMax">
								<input name="email"
									   id="request-form__email"
									   required style="width: 49%"
									   type="email"
									   class="js-email-input border-box styled-input styled-input--thin f15"
									   placeholder="{'Введите email'|t}" />
							</div>
								<span class="js-email-error-field color-red hidden db"></span>
								<span id="request-form__email_warning"
									  class="color-orange font-OpenSans f14 ml10"
									  style="display: none;">{'Введен неправильный email'|t}</span>
							</div>
						{/if}
					<div class="clear"></div>
					<div class="bottom-form-filed">
						<div class="t-align-c clearfix">
						<input {if isNotAuth() && !Translations::isDefaultLang()}onclick="show_login('order'); return false;"}{/if} type="submit" value="{if $want}{'Сохранить'|t}{else}{'Разместить'|t}{/if}"
							   class="js-sendKworkRequest__submit js-uploader-button-disable green-btn btn--big pull-reset m-wMax w250 mt20" />
						</div>
						<input type="hidden" name="action" value="submit" />
						<div class="js-preloader preloader-want" style="display: none">
							<div class="preloader__ico preloader-want__ico"></div>
							<div class="preloader-want__text">{'Сохранение...'|t}</div>
						</div>
						{if isNotAuth()}
							<div class="mt20 f14 t-align-c">
								{'Размещая проект, вы регистрируетесь и принимаете %sПользовательское соглашение%s и соглашаетесь на email-рассылки'|t:'<a href="/terms" target="_blank" class="color-text underline">':'</a>'}
							</div>
						{/if}
					</div>
				</form>
			</div>
		</div>
		{/if}
	</div>
	{Helper::registerFooterJsFile("/js/pages/new_project.js"|cdnBaseUrl)}
{/strip}
		<script>
			var minPrices = {$minPrices|@json_encode};

			$(function () {
				var submitButton = $(".js-sendKworkRequest__submit");
				var submitButtonTitle = submitButton.val();
				$("#wish_description").keyup(function () {
					updateGigDescCharsCount();
				});

				{if $isPageNeedSmsVerification}
				submitButton.click(function (e) {
					e.preventDefault();
					$.ajax({
						url: "/check_payer_phone_verification",
						type: "GET",
						context: this,
						success: function (result) {
							if (!result.success) {
								phoneVerifiedOpenModal();
							} else {
								$(this).unbind("click").click();
							}
						}
					});
				});
				{/if}
			});
			function updateGigDescCharsCount() {
				var used = $("#wish_description").val().length;
				$(".wishdescused").html(used);
			}

			$(window).load(function () {
				updateGigDescCharsCount();
			});

			var isNewSelect = true;
			var isNewForm = false;
			
			{* 7942 временно отключено *}{if false && UserManager::isTester($actor->id)}
			isNewForm = true;
			var selectedAttributesIds = {if $want}{$want->getAttrsIds()|@json_encode:JSON_NUMERIC_CHECK}{else}[]{/if};
			var wantAttributesTree = {if $wantAttributesTree}{$wantAttributesTree|@json_encode:JSON_NUMERIC_CHECK}{else}[]{/if};
			{/if}

		</script>
	
{include file="footer.tpl"}
