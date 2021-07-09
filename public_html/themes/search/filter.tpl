{strip}
	{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/libs/jquery-ui-slider.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/libs/jquery.ui.touch-punch.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/attributes.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/modules/filter.js"|cdnBaseUrl)}
	{if !$pageSpeedMobile}
		{Helper::printCssFile("/css/filter.css"|cdnBaseUrl, "screen")}
		{Helper::printCssFile("/css/jquery-ui.css"|cdnBaseUrl, "screen")}
	{/if}
		<div class="left-col w230 js-kworks-filter kworks-filter popup-filter">
			<form id="filter-form">

				<div class="popup-filter__header m-visible">
					<div class="popup-filter__header-name">{'Фильтры'|t}</div>

					<span class="popup-filter__close js-kworks-filter-close">{'Отмена'|t}</span>
					<input type="submit" value="{'Применить'|t}" class="popup-filter__apply-btn">
				</div>
				<div class="popup-filter__container">

					{if UserManager::isAdmin() && $s && $s eq 'newrating'}
					<div class="popup-filter__group">
						<div class="card__content-column">
							<div class="card__content-header">
								<span class="span-h3 tooltipster" data-tooltip-side="right" data-tooltip-text="Укажите настройки для расчёта рейтинга кворков" data-tooltip-theme="dark">Настройки</span>
							</div>
							<div class="card__content-body card__content_separator">
								<label>
									<span class="mr5">Коэффициент k</span>
									<select name="scoefficient" class="js-kwork-filter-input styled-input">
										{for $i = 1 to 10}
											<option value="{$i}" {if $scoefficient == $i}selected="selected"{/if}>{$i * 10} тыс. руб.</option>
										{/for}
									</select>
								</label>
							</div>
						</div>
					</div>
					{/if}


					<div class="popup-filter__group expandable expanded mt0 js-popup-filter-cats">
						{include file="search/inc/categories.tpl"}
					</div>
					{if $isSearchPage || $CATID != 'all'}
						<div class="js-left-filters-container"></div>

						<div class="js-unembedded-filter">
							{if is_array($unembeddedFilters) && count($unembeddedFilters) > 0 && !$isSearchPage && $CATID != 'all'}
								{include file="./inc/unembedded_filters.tpl" filters=$unembeddedFilters selected=$selectedSubattributesIds}
							{/if}
						</div>

						<div class="price-filters__block">
						{if $priceFilterBounds|is_array && $priceFilterBounds|@count gt 0}
							{include file="search/inc/price.tpl"}
						{/if}
						</div>

						<div class="popup-filter__group popup-filter__group_margin_mobile volume-price-filters__block {if !$volumeType || !$baseVolume} hidden{/if}">
							{if $volumeType && $baseVolume}
								{include file="search/inc/volume_price.tpl"}
							{/if}
						</div>

					{/if}

					{if $isLinksCategory}
					<div class="links_filters">
						{include file="search/inc/links_filters.tpl"}
					</div>
					{/if}

					{if $activeCat.seo == 'translations' || $cseo == 'translations'}
					<div class="popup-filter__group no-overflow">
						<h3 class="popup-filter__group-title m-visible">{'Перевод'|t}</h3>
						<div class="card__content-column">
							<div class="card__content-header">
								<strong>{'Перевод'|t}</strong>
							</div>
							<div class="card__content-body card__content-body-translations">
								<div>
									<div class="translations-label">
										<span class="{if Translations::getLang() == 'en'}w35{else}w25{/if}">{'с'|t}</span>
										<div class="translations-select-wrapper {if Translations::getLang() == 'en'}ml35{else}ml25{/if}">
											<select name="translationsfrom" class="translateFrom translateFromLeft js-kwork-filter-input js-chosen-template {if Translations::getLang() == 'en'}w162{else}w174{/if} translations-select">
												{foreach from=$translationsFromList item=translateId}
													{if array_key_exists($translateId, $languagesFilterList)}
														<option value="{$translateId}" {if $translateId == $translationsFrom} selected{/if}>{$languagesFilterList.$translateId.genitive}</option>
													{/if}
												{/foreach}
											</select>
										</div>
									</div>
								</div>
								<div>
									<div class="translations-label">
										<span class="{if Translations::getLang() == 'en'}w35{else}w25{/if}">{'на'|t}</span>
										<div class="translations-select-wrapper {if Translations::getLang() == 'en'}ml35{else}ml25{/if}">
											<select name="translationsto" class="translateTo translateToLeft js-kwork-filter-input js-chosen-template {if Translations::getLang() == 'en'}w162{else}w174{/if} translations-select">
												{foreach from=$translationsToList item=translateId}
													{if array_key_exists($translateId, $languagesFilterList)}
														<option value="{$translateId}" {if $translateId == $translationsTo} selected{/if}>{$languagesFilterList.$translateId.nominative}</option>
													{/if}
												{/foreach}
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					{/if}

					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">{'Уровень продавца'|t}: <span>
								{if $sellerLvl eq "1"}
									{'Новичок или выше'|t}
								{elseif $sellerLvl eq "2"}
									{'Продвинутый или выше'|t}
								{elseif $sellerLvl eq "3"}
									{'Профессионал'|t}
								{/if}
							</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column">
							<div class="card__content-header">
								<strong>{'Уровень продавца'|t}</strong>
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
									<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
									  data-tooltip-side="right"
									  data-tooltip-text="{'<p>Продавцы с уровнем «Продвинутый» и «Профессионал» - это те, кто уже зарекомендовал себя на Kwork, и кому можно доверять.</p><p>Продвинутые продавцы должны выполнить не менее 10 заказов, из них не менее 90%% должны быть успешными. У профессионалов должно быть от 50 заказов с долей успешных не ниже 92%%.</p>'|t}"
									  data-tooltip-theme="dark">?</span>
								</span>
							</div>
							<div class="card__content-body">
								<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
								<div>
									<input name="sellerlvl" class="js-kwork-filter-input styled-radio" id="sellerlvl_1" type="radio" value="1"
											{if $sellerLvl eq "1"} checked="checked" {/if}>
									<label for="sellerlvl_1">{'Новичок или выше'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sellerlvl" class="js-kwork-filter-input styled-radio" id="sellerlvl_2" type="radio" value="2"
											{if $sellerLvl eq "2"} checked="checked" {/if}>
									<label for="sellerlvl_2">{'Продвинутый или выше'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sellerlvl" class="js-kwork-filter-input styled-radio" id="sellerlvl_3" type="radio" value="3"
											{if $sellerLvl eq "3"} checked="checked" {/if}>
									<label for="sellerlvl_3">{'Профессионал'|t}</label>
								</div>
							</div>
						</div>
					</div>

					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">{'Активность продавцов'|t}: <span>
									{if $sonline === "online"}
										{'Онлайн'|t}
									{elseif $sonline eq "1"}
										{'Заходил до 1 дн. назад'|t}
									{elseif $sonline eq "3"}
										{'Заходил до 3 дн. назад'|t}
									{/if}
								</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column">
							<div class="card__content-header">
								<strong>{'Активность продавцов'|t}</strong>
							</div>
							<div class="card__content-body">
								<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
								<div>
									<input name="sonline" class="js-kwork-filter-input styled-radio" id="sonline_0" type="radio" value="online"
											{if $sonline === "online"} checked="checked" {/if}>
									<label for="sonline_0">{'Онлайн'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sonline" class="js-kwork-filter-input styled-radio" id="sonline_1" type="radio" value="1"
											{if $sonline eq "1"} checked="checked" {/if}>
									<label for="sonline_1">{'Заходил до 1 дн. назад'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sonline" class="js-kwork-filter-input styled-radio" id="sonline_3" type="radio" value="3"
											{if $sonline eq "3"} checked="checked" {/if}>
									<label for="sonline_3">{'Заходил до 3 дн. назад'|t}</label>
								</div>
							</div>
							{if false}
								<div class="card__content-body card__content_separator">
									<div>
										<span class="fw400">{'Кворков продано'|t}</span>
										<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
											<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
												  data-tooltip-side="right"
												  data-tooltip-text="{'Укажите минимальное количество продаж кворков у одного исполнителя'|t}"
												  data-tooltip-theme="dark">?</span>
										</span>
									</div>
									<label class="fw400 mt5">
										{'Не менее'|t}
										<input name="sminusersales"
											   class="js-kwork-filter-input js-kwork-filter-input-text styled-input ml5"
											   type="text"
											   style="width: 78px;"
											   {if $sMinUserSales}value="{$sMinUserSales}"{/if}
											   data-default=""
										>
									</label>
								</div>
								<div class="card__content-body card__content_separator">
									<div>
										<span class="fw400">{'Конкретный кворк продан'|t}</span>
										<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
											<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
												  data-tooltip-side="right"
												  data-tooltip-text="{'Укажите минимальное количество продаж конкретного кворка'|t}"
												  data-tooltip-theme="dark">?</span>
										</span>
									</div>
									<label class="fw400 mt5">
										{'Не менее'|t}
										<input name="sminkworksales"
											   class="js-kwork-filter-input js-kwork-filter-input-text styled-input ml5"
											   type="text"
											   style="width: 78px;"
											   {if $sMinKworkSales}value="{$sMinKworkSales}"{/if}
											   data-default=""
										>
									</label>
								</div>
							{/if}
						</div>
					</div>

					{if $attributeReviews[1] > 12}
					<div class="popup-filter__group attibute-review-filter expandable">
						{include file="search/inc/review_count.tpl"}
					</div>
					{/if}

					<div class="popup-filter__group">
						<h3 class="popup-filter__group-title m-visible">{'Срок выполнения'|t}:</h3>
						<div class="card__content-column">
							<div class="card__content-header">
								<strong>{'Срок выполнения'|t}</strong>
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
									<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16" data-tooltip-side="right" data-tooltip-text="{'Укажите желаемый срок выполнения кворка'|t}" data-tooltip-theme="dark">?</span>
								</span>
							</div>
							<div class="card__content-body fw400">
								<a href="javascript: void(0);" class="filter-clear m-hidden">{'Сбросить'|t}</a>
								<div class="popup-filter__btn-group">
									<div class="first-item">
										<input name="sdeliverytime" class="js-kwork-filter-input styled-radio" id="foxdeliverytime_1" type="radio" value="1"
												{if $sdeliverytime eq "1"} checked="checked" {/if}>
										<label for="foxdeliverytime_1"><span>{'За 24 часа'|t}</span></label>
									</div>
									<div>
										<input name="sdeliverytime" class="js-kwork-filter-input styled-radio" id="foxdeliverytime_2" type="radio" value="2"
												{if $sdeliverytime eq "2"} checked="checked" {/if}>
										<label for="foxdeliverytime_2"><span>{'До 2 дней'|t}</span></label>
									</div>
									<div>
										<input name="sdeliverytime" class="js-kwork-filter-input styled-radio" id="foxdeliverytime_3" type="radio" value="3"
												{if $sdeliverytime eq "3"} checked="checked" {/if}>
										<label for="foxdeliverytime_3"><span>{'До 3 дней'|t}</span></label>
									</div>
									<div>
										<input name="sdeliverytime" class="js-kwork-filter-input styled-radio" id="foxdeliverytime_5" type="radio" value="5"
												{if $sdeliverytime eq "5"} checked="checked" {/if}>
										<label for="foxdeliverytime_5"><span>{'До 5 дней'|t}</span></label>
									</div>
									<div class="last-item">
										<input name="sdeliverytime" class="js-kwork-filter-input styled-radio" id="foxdeliverytime_10" type="radio" value="10"
												{if $sdeliverytime eq "10"} checked="checked" {/if}>
										<label for="foxdeliverytime_10"><span>{'До 10 дней'|t}</span></label>
									</div>
									</div>
							</div>
						</div>
					</div>

					{if false}
					{if $hasTopRated eq true}
						<div class="mt20">
							<div class="card__content-column options">
								<div class="card__content-header">
									<strong>{'Высший рейтинг'|t}</strong>
									<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
										<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
											  data-tooltip-side="right"
											  data-tooltip-text="{'Выберите пункт, чтобы найти кворки только с высшим рейтингом'|t}"
											  data-tooltip-theme="dark">?</span>
									</span>
								</div>
								<div class="card__content-body">
									<input name="stoprated" class="js-kwork-filter-input styled-checkbox" id="fox_toprated"
										   type="checkbox" value="1"
											{if $stoprated eq "1"}
												checked="checked"
											{/if}
									>
									<label style="background-position: -2px 4px;" class="fw400" for="fox_toprated">
										{'Только кворки с Высшим рейтингом'|t}
									</label>
								</div>
							</div>
						</div>
					{/if}
					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">
							{'Положительных отзывов'|t}: <span>{'От'|t} {$sMinReview}</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column options">
							<div class="card__content-header">
								<strong>{'Отзывы'|t}</strong>
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
									<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
										  data-tooltip-side="right"
										  data-tooltip-text="{'Укажите минимальное количество положительных отзывов, которое должно быть у кворка'|t}"
										  data-tooltip-theme="dark">?</span>
								</span>
							</div>
							<div class="card__content-body">
								<div class="fw400">{'Положительных отзывов'|t}</div>
								<label class="fs13 fw400 mt5">
									{'от'|t}
									<input name="sminreview" class="js-kwork-filter-input js-kwork-filter-input-text styled-input ml5"
										   type="text" id="foxwithreviews"
										   style="width: 78px;"
										   value="{$sMinReview}"
										   data-default="0"
									>
								</label>
							</div>
						</div>
					</div>
					{/if}

					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">{'Заказов в очереди'|t}: <span>
									{if $sOrdersQueue eq "1"}
										{'Нет'|t}
									{elseif $sOrdersQueue eq "2"}
										{'До 1'|t}
									{elseif $sOrdersQueue eq "4"}
										{'До 3'|t}
									{elseif $sOrdersQueue eq "6"}
										{'До 5'|t}
									{elseif $sOrdersQueue eq "9"}
										{'До 8'|t}
									{/if}
								</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column">
							<div class="card__content-header">
								<strong>{'Заказов в очереди'|t}</strong>
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
									<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
										  data-tooltip-side="right"
										  data-tooltip-text="{'Укажите допустимое количество заказов, которые сейчас находятся в работе у продавца'|t}"
										  data-tooltip-theme="dark">?</span>
								</span>
							</div>
							<div class="card__content-body fw400">
								<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
								<div>
									<input name="sordersqueue" class="js-kwork-filter-input styled-radio" id="sordersqueue_1" type="radio"
										   value="1" {if $sOrdersQueue eq "1"} checked="checked" {/if}>
									<label for="sordersqueue_1">{'Нет'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sordersqueue" class="js-kwork-filter-input styled-radio" id="sordersqueue_2" type="radio"
										   value="2" {if $sOrdersQueue eq "2"} checked="checked" {/if}>
									<label for="sordersqueue_2">{'До 1'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sordersqueue" class="js-kwork-filter-input styled-radio" id="sordersqueue_4" type="radio"
										   value="4" {if $sOrdersQueue eq "4"} checked="checked" {/if}>
									<label for="sordersqueue_4">{'До 3'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sordersqueue" class="js-kwork-filter-input styled-radio" id="sordersqueue_6" type="radio"
										   value="6" {if $sOrdersQueue eq "6"} checked="checked" {/if}>
									<label for="sordersqueue_6">{'До 5'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sordersqueue" class="js-kwork-filter-input styled-radio" id="sordersqueue_9" type="radio"
										   value="9" {if $sOrdersQueue eq "9"} checked="checked" {/if}>
									<label for="sordersqueue_9">{'До 8'|t}</label>
								</div>
							</div>
						</div>
					</div>

					{if $actor && ($catViewType == $viewPortfolio || App::config("module.user_kwork_filter_enable") && App::config("module.user_kwork_marks.enable"))}
					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">{'Просмотренные'|t}: <span>
								{if $sview eq "2"}
									{'Только просмотренные'|t}
								{elseif $sview eq "3"}
									{'Только не просмотренные'|t}
								{/if}</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>

						<div class="card__content-column">
							<div class="card__content-header"><strong>{'Просмотренные'|t}</strong></div>
							<div class="card__content-body">
								<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
								<div>
									<input name="sview" class="js-kwork-filter-input styled-radio" id="sview_2" type="radio"
										   value="2" {if $sview eq "2"} checked="checked" {/if}>
									<label for="sview_2">{'Только просмотренные'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="sview" class="js-kwork-filter-input styled-radio" id="sview_3" type="radio"
										   value="3" {if $sview eq "3"} checked="checked" {/if}>
									<label for="sview_3">{'Только не просмотренные'|t}</label>
								</div>
							</div>
						</div>
					</div>

					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">{'Заказанные'|t}: <span>
								{if $strack eq "2"}
									{'Только заказанные'|t}
								{elseif $strack eq "3"}
									{'Только не заказанные'|t}
								{/if}</span>
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column">
							<div class="card__content-header"><strong>{'Заказанные'|t}</strong></div>
							<div class="card__content-body">
								<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
								<div>
									<input name="strack" class="js-kwork-filter-input styled-radio" id="strack_2" type="radio"
										   value="2" {if $strack eq "2"} checked="checked" {/if}>
									<label for="strack_2">{'Только заказанные'|t}</label>
								</div>
								<div class="m-mt10">
									<input name="strack" class="js-kwork-filter-input styled-radio" id="strack_3" type="radio"
										   value="3" {if $strack eq "3"} checked="checked" {/if}>
									<label for="strack_3">{'Только не заказанные'|t}</label>
								</div>
							</div>
						</div>
					</div>
					{/if}

					{if !$actor and $CATID != 'all' and not $isSearchPage and $activeCat neq 0}
					{* Дубль для seo - с переходом на лендинг *}
					<div class="popup-filter__group expandable">
						<h3 class="popup-filter__group-title m-visible">
							{'Теги'|t}
							<div class="kwork-icon icon-down-arrow"></div>
						</h3>
						<div class="card__content-column options"	>
							<div class="allmusic">
								<div class="card__content-header">
									<div class="toggle-land showmorebtnx show-land" id="toggleTags">
										<a href="javascript:void(0)" class="showSubLand">
											{'Теги'|t}
										</a>
									</div>
								</div>

								<div class="card__content-body">
									{if $sub_cats|@count gt '0'}
										{section name=i loop=$sub_cats}
											{if ($activeCat neq 0) and ($activeCat.CATID neq $sub_cats[i].CATID)}
												{* Если у нас выбрана категория, то остальные мы даже не загружаем *}
												{continue}
											{/if}
											{if $cname eq $sub_cats[i].name}
												<div class="sub_land" style="display: none;">
													{insert name=get_category_landings value=a assign=landList category_id=$sub_cats[i].CATID}
													{if $landList|@count gt '0'}
														<ul class="sub_land_list mt5" id="foxdontshowland">
															{foreach item=v_cat from=$landList}
																<li>
																	<a class="f13"
																	   href="{$baseurl}/land/{$v_cat->name|t|lower|stripslashes}">
																		{$v_cat->seo|t|stripslashes}
																	</a>
																</li>
															{/foreach}
														</ul>
													{/if}
												</div>
											{/if}
										{/section}
									{/if}
								</div>
							</div>
						</div>
					</div>
					{/if}

				</div>
			</form>
		</div>
		<a href="javascript: void(0);" class="js-kworks-filter-button mb20">
			<i class="fa fa-sliders"></i>
			<span class="filter-name">{'Фильтры'|t}</span>
		</a>

{literal}
	<script>
		// 7474 URL нужен для безшовного перехода по алиасам атрибутов, не удалять его
		{/literal}
		{if $pageType == "searchresults"}
			window.catalogUrl = "{$baseurl}/search";
			window.filterIsSearch = true; {*Флаг того что мы находимсся на странице поиска, некоторые вещи должны работать по другому, например алиасы атрибутов*}
		{else}
			window.catalogUrl = "{$baseurl}/categories/{$cid|lower}";
			window.filterIsSearch = false;
		{/if}
		{if $activeCat.seo == 'translations' || $cseo == 'translations'}{literal}
			//глобальная existedLanguagesFilterList нужна для мобильного и экранов меньшь 767, так как на мобильных не срабатывает функциля CatFilterModule._load
			//доступные в кворках языки переводов
			var existedLanguagesFilterList = {/literal}{json_encode($existedLanguagesFilterList)}{literal};

			//массив всех языков переводов с падежами доступных для перевода языков
			var languagesFilterList = {/literal}{json_encode($languagesFilterList)}{literal};

			//json_encode сортирует массив по ключам, поэтому сохраняем их порядок отдельно для алфавитной сортировки
			var langsAlphabet = {
				'from': {/literal}{json_encode(array_keys($existedLanguagesFilterList['from']))}{literal},
				'to': {/literal}{json_encode(array_keys($existedLanguagesFilterList['to']))}{literal}
			};

			window.addEventListener('DOMContentLoaded', function() {
				$('.js-chosen-template').chosen({width: '100%', disable_search: true});

				//ускоряем скролл в FF
				jQuery('.translations-select-wrapper').find('.chosen-results').unbind('mousewheel');
			});
		{/literal}{/if}{literal}

		window.addEventListener('DOMContentLoaded', function() {
			CatFilterModule.init({/literal}{$queryParamsJson}{literal});
		});
	</script>
{/literal}
{/strip}