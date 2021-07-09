//перестраиваем списки доступных для перевода языков
function rebuildTranslations() {
	var translations = {
		form: {
			selector: '.translateFrom',
			type: 'from',
			declension: 'genitive',
			param: "translationsfrom",
		},
		to: {
			selector: '.translateTo',
			type: 'to',
			declension: 'nominative',
            param: "translationsto",
		}
	};
	//Получаем значение селекта (перевод с)
	var translateFromSelect = $('.translateFrom').children('option:selected').val();
	//Перестраиваем селекты перевода
	for (var keyTranslate in translations) {
        var isCatchSelect = false;
		//Тип склонение
		var declension = translations[keyTranslate].declension;
		//Тип перевода
		var type = translations[keyTranslate].type;
		//Селектор селекта
		var selector = translations[keyTranslate].selector;
		//Текущее значение селекта
		var tanslateSelect = $(selector).children('option:selected').val();
		//Получаем id значений
		var translationsList = $.map(
			existedLanguagesFilterList[type],
			function(element,index) {
				return index;
			}
		);
		//Если это (перевод на) и (перевод с) выбран то берем id которые в прае с (переводм с)
		var filterListFrom = existedLanguagesFilterList['from'][translateFromSelect];
		if (translateFromSelect !== '-1' && type === 'to' && filterListFrom !== undefined) {
			translationsList = existedLanguagesFilterList['from'][translateFromSelect];
		}
		//добавляем в начало массива элемент "любой язык"
		if (translationsList.indexOf(-1) === -1) {
			translationsList.unshift(-1);
		}
		//массив языков по алфавиту для противоположного списка
		var langsAlphabetList = langsAlphabet[type];
		//добавляем в начало массива элемент "любой язык"
		if (langsAlphabetList.indexOf(-1) === -1) {
			langsAlphabetList.unshift(-1);
		}
		var optionsHtml = '';
		for (var key in langsAlphabetList) {
			if (langsAlphabetList[key] === "") {
				continue;
			}

			var langsAlphabetKey = langsAlphabetList[key];
			if (langsAlphabetKey !== -1) {
				langsAlphabetKey = langsAlphabetKey.toString();
			}
			var translationsListKey = translationsList.indexOf(langsAlphabetKey);
			if (translationsListKey !== -1) {
				optionsHtml += '<option value="' + translationsList[translationsListKey] + '"';
				if (tanslateSelect !== '-1' && translationsList[translationsListKey] === tanslateSelect) {
					isCatchSelect = true;
					optionsHtml += ' selected';
				}
				optionsHtml += '>' +
					languagesFilterList[translationsList[translationsListKey]][declension] +
					'</option>';
			}
		}
		$(selector).html(optionsHtml);
		$(selector).trigger('chosen:updated');

        if(!isCatchSelect) {
            CatFilterModule.delParam(translations[keyTranslate].param);
        }

	}
}

function findAllParentsIds(tree, attributeId) {
	for (var i in tree) {
		if (tree[i].id === attributeId) {
			if (tree[i].parent_id) {
				return [tree[i].parent_id];
			} else {
				return [];
			}
		} else if (tree[i].children.length) {
			var parents = findAllParentsIds(tree[i].children, attributeId);
			if (parents.length) {
				if (tree[i].parent_id) {
					parents.unshift(tree[i].parent_id);
				}
				return parents;
			}
		}
	}

	return [];
}

var CatFilterModule = (function () {
	var $_form = {};
	var _params = {};
	var _temporaryParams = {};
	var _subattributes = {};
	var _baseParams = {};
	var _anotherTypeLink = null;
	var _anotherTypeHref = null;
	var _anotherTypeView = null;
	var _clearClass = '.filter-clear';
	var _blockContentClass = '.card__content-body';	
	// Сюда сохраняем последний ajax запрос, что бы мы могли его остановить и начать новый
	var _postRequest;
	// Запоминаем предыдущий атрибут, для обновления html фильтров(js-unembedded-filter)
	var _oldParamAttributeId = undefined;
	var _oldParamSubAttributeId = undefined;
	// Помечаем что мы очистили фильтр, для обновления html фильтров(js-unembedded-filter)
	var _isClearUnembeddedFilter = false;
	
	var _onChangeInput = function (el, workOnMobile) {
		var onMobile = workOnMobile || false;
		var $currentEl;
		if(el instanceof HTMLElement) {
			$currentEl = $(el);
		} else {
			$currentEl = $(this);
		}
		if ($.isEmptyObject(_params)) {
			_params = _baseParams;
		}
		_params['page'] = 1;
		_rebuildParam($currentEl);
		_reloadFilter();
	};
	var timeoutActions = {};
	var _isInited = false;

	var _reloadFilter = function() {
		_setLinkTypeParams();
		_pushUrlHistory();
		_rebuildClearLinks();
		_setHtmlFilter();
		_load();
	};

	var _preloaderIco = '<div class="preloader_wrapper" style="height: 50px;position:relative;"><div class="preloader__ico preloader__ico_small"></div></div>';

	var _changeDisplayModeValue = function(el, type) {
		var elKey = (type === 'sort' ? 's' : 'sdisplay');
		var elValue = el.data('type');
		var newLink = '';

		for (var key in _params) {
			var value = _params[key];
			if (key === elKey) {
				value = elValue;
			}
			newLink += '&' + key + '=' + value;
		}
		if (!(elKey in _params)) {
			newLink += '&' + elKey + '=' + elValue;
		}
		newLink = window.location.href.split('?')[0] + '?' + newLink.substr(1);

		if (type === 'sort') {
			el.val(newLink);
		} else if (type === 'view') {
			el.attr('href', newLink);
		}
	};

	var _pushUrlHistory = function(){
		var newUrl = implodeCatalogUrl(_params);
		window.history.pushState({urlPath:newUrl}, "", newUrl);
		if(_anotherTypeHref) {
			var view = _anotherTypeLink.data("view");
			var tabParams = Object.assign({}, _params, {"viewSave": view});
			var newTypeUrl = implodeURI(tabParams, _anotherTypeHref);
			_anotherTypeLink.attr('href', newTypeUrl);
		}

		//актуализируем value селекта "Сортировать по"
		$('.kwork-list-display-mode .js-sort-by option').each(function () {
			_changeDisplayModeValue($(this), 'sort');
		});

		//актуализируем href ссылки в плиточно/табличном представлении
		$('.kwork-list-display-mode a[data-field="sdisplay"]').each(function () {
			_changeDisplayModeValue($(this), 'view');
		});
	};

	var _load = function(disablePreloader) {
		// Эта функция используется при  полном обновлении содержимого на странице,
		// для загрузки с кнопки "Показать еще" используется функция loadKworks в fox.js
		_params['page'] = 1;
		if(!disablePreloader) {
			$('.cusongslist').html(_preloaderIco);
		}
		$('.camp-amount-block').hide();
		$('.camp-error-block').hide();
		$('.loadKworks').addClass('hidden').hide();

		delete _params['translationsfrom'];
        delete _params['translationsto'];

		if ($(".translateFrom").length) {
            _params['translationsfrom'] = $(".translateFrom").children('option:selected').val();
		}
        if ($(".translateTo").length) {
            _params['translationsto'] = $(".translateTo").children('option:selected').val();
        }
		
		// Отменяем предыдущий запрос
		if (_postRequest !== undefined) {
			_postRequest.abort();
		}
		_postRequest = $.post(location.pathname, $.extend({}, _params, _temporaryParams), function (response) {
			$('.cusongslist').html(response.html);
			if (response.paging.page * response.paging.items_per_page < response.paging.total) {
				$('.loadKworks').removeClass('hidden').show();
			}
			if (parseInt(response.paging.total) === 0) {
				$('.no-results').removeClass('hidden');
			} else {
				$('.no-results').addClass('hidden');
			}
			
			// Обновляем фильтр
			if(
				_params.attribute_id !== _oldParamAttributeId
				|| _params.subattribute !== _oldParamSubAttributeId
				|| _isClearUnembeddedFilter
			) {
				$('.js-unembedded-filter').html(response.unembeddedFiltersHtml || '');
			}
			_oldParamAttributeId = _params.attribute_id;
			_oldParamSubAttributeId = _params.subattribute;
			_isClearUnembeddedFilter = false;

			if (response.renderPriceFilters) {
				$('.price-filters__block').html(response.priceFiltersHtml);
				$('.volume-price-filters__block').html(response.volumePriceFiltersHtml || '')
					.toggleClass('hidden', !response.volumePriceFiltersHtml);
				$('.attibute-review-filter').html(response.attributeReviewsHtml);
				if (response.linksFiltered) {
					window.location.reload();
				}

				_setHtmlFilter();

				if (isMobile()) {
					params = getUrlParams();
					initPriceSlider('.popup-filter__price-range-slider');
				}
			}
			if (response.attributes && response.attributes.objectTree !== null){
				window.TopFiltersAttributeUpdate.init(response.attributes.objectTree, response.attributes.ids);
			}

			//Обновляем переводы (с)(на)
			if (typeof(langsAlphabet) != "undefined" && langsAlphabet !== null) {
				existedLanguagesFilterList = response.existedLanguagesFilterList;
				langsAlphabet["from"] = response.translationsFromList;
				langsAlphabet["to"] = response.translationsToList;
				rebuildTranslations();
			}

			if (isMobile()) {
				setUnembeddedBlockName();
				openUnembeddedBlock();
			}

			_initClearLinksForBlocks();
			_rebuildClearLinks();

		}, 'json');

		_clearTemporaryParams();
	};

	var _rebuildParam = function ($currentEl) {
		var name = $currentEl.attr('name');
		var value = $currentEl.val();

		_params[name] = value;
	};
	
	// Оброботчик события кнопки назад в браузере
	var _rebuildFilter = function () {
		_params = window
			.location
			.search
			.replace('?','')
			.split('&')
			.reduce(
				function(p,e){
					var a = e.split('=');
					if(decodeURIComponent(a[0]))
						p[decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
					return p;
				},
				{}
			);

		var gp = getGetParams();
		for (var paramName in gp) {
			_params[paramName] = gp[paramName];
		}
		
		//Обновляем массив с выбранными unembedded-filter
		_subattributes = _getSubattributesFromParams() || {};

		if (_getHtmlFilter() !== null) {
			$_form.html(_getHtmlFilter());

			//обновление tooltip
			$('.tooltipster').tooltipster(TOOLTIP_CONFIG);

			//обновление цены в input
			if (_params['price']) {
				var prices = _params['price'].split('_');
				var $inputPrice = $('input[name="price"]');

				if (!$inputPrice.is(':checked')) {
					$('#priceFrom').val(prices[0]);
					$('#priceTo').val(prices[1]);
				}
			}

			if (_params['volume_price_from']) {
				$('input[name="volume_price_from"]').val(_params['volume_price_from']);
			}
			if (_params['volume_price_to']) {
				$('input[name="volume_price_to"]').val(_params['volume_price_to']);
			}

			//обновление conditions в input
			var idParam;
			$.each(_params, function (nameParam, valueParam) {
				if (nameParam.indexOf('package_items_conditions') >= 0 && valueParam === 'int') {
					var resultRegExp = /package_items_conditions\[(\d)\]\[type\]$/g.exec(nameParam);
					idParam = resultRegExp[1];
				}
			})

			if (typeof idParam !== 'undefined') {
				var idItem = _params['package_items_conditions[' + idParam + '][id]'];
				var valueItem = _params['package_items_conditions[' + idParam + '][value]'];
				var filterElement = $('.js-filter-package-item').find('.custom-volume__input[data-id="' + idItem + '"]');
				filterElement.val(valueItem);
			}


			$('.other-filter-input').each(function () {
				var $this = $(this);
				var name = $this.data('name');
				if (name in _params) {
					var values = _params[name].split('_');

					$this.filter('[data-type="from"]').val(values[0]);
					$this.filter('[data-type="to"]').val(values[1]);
				}
			})

			$('.chosen-container ').remove();
			$('.js-chosen-template').chosen({width: '100%', disable_search: true});
		}
	};

	var _setLinkTypeParams = function () {
		var $linkTypes = $('.js-kwork-filter-lt-input');

		var name = $($linkTypes[0]).attr('name');
		var linkTypesValues = [];
		var hasUnchecked = false;
		$linkTypes.each(function () {
			if ($(this).is(':checked')) {
				linkTypesValues.push($(this).val());
			} else {
				hasUnchecked = true;
			}
		});

		if (hasUnchecked) {
			_params[name] = linkTypesValues;
		} else {
			delete _params[name];
		}
	};

	var _onChangeLinkTypeInput = function () {
		if (!$('.js-kwork-filter-lt-input').filter(":checked").length) {
			$(this).prop('checked', true).attr('checked', 'checked');
			return false;
		}

		_onChangeInput();
	};

	var _clearBlockFilter = function() {
		var $el = $(this);
		var $parent = $el.closest(_blockContentClass);
		var name = $el.attr('data-name');
		var nameArr = name ? (name.split(' ') || []) : [];

		$el.hide();
		$parent.find('input').each(function() {
			if ($(this).is('[type="text"]') || $(this).is('[type="number"]')) {
				$(this).val('');
				$parent.find('.price-filter-input__clear').each(function () {
					$(this).addClass('hidden'); 
				});
			} else {
				$(this).prop('checked', false).removeAttr('checked');
			}
		});
		_removeFilterForUrl(nameArr);
	};

	var _removeFilterForUrl = function(nameFilterArr) {
		var params = _getCurrentParams();

		for (var i in nameFilterArr) {
			var name = nameFilterArr[i];
			delete _params[name];
		}
		_pushUrlHistory();
		_setHtmlFilter();

		if (!isMobile()) {
			_load();
		}
	};

	var _loadGetParameters = function () {
		var urlParams = getUrlParams();
		$('.other-filter-input').each(function(k, v) {
			var t = $(v);
			var name = t.data('name'), type = t.data('type');
			if (name in urlParams) {
				var value = urlParams[name];
				if (type) {
					var parts = urlParams[name].split('_');
					if (type == 'from') value = parts[0] || '';
					if (type == 'to') value = parts[1] || '';
				}
				t.val(value);
				_updateOtherInputState(value, t);
			}
		});
	}

	var _updateOtherInputState = function(text, t) {
		if (text.length) {
			t.next('.other-filter-input__clear').removeClass('hidden');
		} else {
			t.next('.other-filter-input__clear').addClass('hidden');
		}
	}

	var _onInputOtherFilter = function(e) {
		var t = $(e.target);
		var name = t.data('name');
		var type = t.data('type');
		var text = t.val();
        var value;
		var clearedText = text.replace(/[^0-9]/g, '');
		var num = parseInt(clearedText);
		if(num > t.data('max')) clearedText = t.data('max').toString();
		if(text !== clearedText) t.val(clearedText);

		_updateOtherInputState(clearedText, t);

		if(type === 'from') {
			value = clearedText + '_' + $('.other-filter-input[data-name="' + name + '"][data-type="to"]').val();
		} else if(type === 'to') {
			value = $('.other-filter-input[data-name="' + name + '"][data-type="from"]').val() + '_' + clearedText;
		} else {
			value = clearedText;
		}
		if(value === '_') value = '';

		$('[name="' + name + '"]').val(value);

		if(value === '') {
			if(name in _params)
				delete _params[name];
		} else {
			_params[name] = value;
		}
		timeoutValue = 800;

        if (!isMobile()) _updateSendTimeout();
	}

	var _clearOtherFilterInput = function(e) {
		timeoutValue = 200;
		$(this).prev('input').val('').trigger('input');
		$(this).addClass('hidden');
	};

	var _setEvents = function () {
		$_form.on('change', 'input[type="radio"]', _setActiveInput);
		$_form.on('change', '.js-kwork-filter-input:not(.js-kwork-filter-input-text)', _onChangeInput);
		$_form.on('change', '.js-kwork-filter-lt-input', _onChangeLinkTypeInput);
		$_form.on('click', '.subId', _setActiveLanding);
		$_form.on('click', '.unembedded-filter a', _setSubattribute);
		$_form.on('change', '.unembedded-item input', _setMultipleSubattribute);
		$_form.on('click', _clearClass, _clearBlockFilter);
		$_form.on('click', '.js-kwork-filter-input[name="price"]', _resetPriceFilterInputs);
		$_form.on('keyup', '.price-filter-input', _onKeyupPriceFilter);
        $_form.on('keyup', '.volume-price-filter-input', _onKeyupVolumePriceFilter);
		$_form.on('keydown', '.price-filter-input,.volume-price-filter-input', _onKeydownPriceFilter);
		$_form.on('click', '.price-filter-input__clear', _clearPriceFilterInput);
		$_form.on('input', '.other-filter-input', _onInputOtherFilter);
		$_form.on('click', '.other-filter-input__clear', _clearOtherFilterInput);
		$_form.on('click', '.toggle-land', _toggleTags);
		$_form.on('click', '.js-search-category__more', _toggleSearchMore);
		$_form.on('click', '.js-attributes__more', _toggleAttributesMore);
		$_form.on('click', '.js-unembedded-filter__more', _toggleUnembedded);
		$(document).on('click', '' +
			'.custom-select__list-item, ' +
			'.custom-select__filter-clear, ' +
			'.custom-volume__filter-clear, ' +
			'.custom-checkbox__filter-clear' +
			'', _onChangeNewTopFilter);
		$(document).on('keyup', '.custom-volume__input', function(e) {
			e.preventDefault();

			var _this = $(this);

			if (window.setTimeoutCustomVolume) {
				clearTimeout(window.setTimeoutCustomVolume);
			}
			window.setTimeoutCustomVolume = setTimeout(function() {
				_onChangeNewTopFilter.call(_this);
			}, 1000);
		});
		$(document).on('click', '.js-top-filters-button', _setActiveLanding);
		$(document).on('submit', '#filter-form', _onSubmitFilterForm);

		var _onInput = Utils.debounce(_onChangeInput, 1000);
		$_form.on('input', '.js-kwork-filter-input-text', _onInput);

		window.onpopstate = function() {
			_rebuildFilter();

			// Актуализируем данные и форму по податрибутам
			_subattributes = _getSubattributesFromParams() || {};
			$('.unembedded-filter').find('.clear-button').hide();
			$('.unembedded-filter').find('.unembedded-item a').removeClass('active');
			if ($.isEmptyObject(_subattributes)) {
				delete _params['subattribute'];
			} else {
				$.each(_subattributes, function(i, v) {
					$('.unembedded-filter')
						.find('.unembedded-item a[data-id="' + v + '"]').addClass('active')
						.closest('.unembedded-filter').find('.clear-button').show();
				});
			}

			_load();
		}
	};

	var _setActiveLanding = function(e) {
		var $el = $(this);
		var isButton = false; //кнопка атрибутов и пакетных услуг

		//кнопка атрибутов и пакетных услуг
		if ($el.hasClass('js-top-filters-button')) {
			//переопределяем выбранный атрибут
			$el = $('.subId[data-id="' + $el.data('id') + '"]');
			isButton = true;
		}

		var $isCategory = $el.hasClass('category');
		var $firstParentUl = $el.closest('ul');
		var $firstParentLi = $el.closest('li');
		var $parentsUl = $el.parents('ul');
		var $parentsLi = $el.parents('li');
		var attributeId = $el.data('id');
		var attributeAlias = $el.data('alias');
		if (window.filterIsSearch) {
			// Алиасы атрибутов в поиске не поддерживаются
			attributeAlias = "";
		}
		var hasChildren = $firstParentLi.find('ul').length > 0 ? true : false;

		var isKworkLinksSites = false;
		if ($isCategory) {
			isKworkLinksSites = window.isLinksCategory;
		} else if (window.isLinksCategory) {
			var attributeData = findInTreeRecursive(attributeId);
			if (attributeData.is_kwork_links_sites) {
				isKworkLinksSites = true;
			} else {
				var parents = findAllParents(attributeId);
				$.each(parents, function(k, v) {
					var parent = findInTreeRecursive(v);
					if (parent.is_kwork_links_sites) {
						isKworkLinksSites = true;
						return false;
					}
				});
			}
		}
		if (isKworkLinksSites) {
			$('.js-links-filters').removeClass('hidden');
		} else {
			delete _params['lcount'];
			delete _params['lsqi'];
			delete _params['lmajestic'];
			delete _params['ltrust'];
			delete _params['lspam'];
			delete _params['ltraffic'];
			var filtersBlock = $('.js-links-filters').addClass('hidden');
			filtersBlock.find('input').val('');
			filtersBlock.find('.other-filter-input__clear').addClass('hidden');
		}

		$parentsUl.find('li.active').removeClass('active');
		$firstParentLi.addClass('active');

		// Основная магия
		if (hasChildren) {
			// Скрыть ВСЕ дочерние категории
			$firstParentLi.find('ul > li').removeClass('hide');
			// Скрыть ВСЕ списки дочерних категорий
			$firstParentLi.find('.sub_cat_list').addClass('hide');
			// Раскрываем дочерний список
			$firstParentLi.find('> .sub_cat_list').removeClass('hide');
			// Скрываем одноуровневые элементы
			$firstParentUl.find('> li:not(.active)').addClass('hide');

			// У всех дочерних элементов скрываем элементы
			$firstParentLi.find('.arrow').addClass('hide');
			$parentsLi.each(function(i, e) {
				jQuery(this).find('> a .arrow').removeClass('hide');
			});

			// Убираем смещение
			$firstParentUl.find('.has-sub').removeClass('has-sub');
			$firstParentUl.closest('ul').addClass('has-sub');
		}

		// Скрытие классификаций при длинном списке в мобильной версии
		attributesBtnMore($firstParentLi);

		// Слайдер
		if ($firstParentLi.length) {
			var nestedItems = '';
			if (hasChildren) {
				nestedItems = $firstParentLi.find('> ul > li');
			} else {
				nestedItems = $firstParentUl.find('> li');
			}

			if (nestedItems.length) {
				var itemsObject = [];
				nestedItems.each(function(index) {
					var item = $(this).find('> a');
					var itemId = item.attr('data-id');
					itemsObject.push({
						seo: item.text(),
						id: itemId,
					});
				});
			}
		}

		// Хлебные крошки
		refreshBreadCrumbs();

		//верхний фильтр классификаций
		if ($isCategory) {
			topFilters.setSelectedAttributes([]);
		} else {
			topFilters.setSelectedAttributes([attributeId]);
		}

		if ($isCategory) {
			delete _params['attribute_alias'];
			delete _params['attribute_id'];
		} else {
			_params['attribute_alias'] = attributeAlias;
			_params['attribute_id'] = attributeId;
		}

		_actualizeSubattributes();

		//удаляем добавленные ранее параметры пакетных кворков вида package_items_conditions[0][id]
		delete _temporaryParams['package_items_conditions'];
		_clearPackageItemsConditions();

		//кнопка атрибутов и пакетных услуг
		if (isButton) {
			var packageItems = $(this).data('package_items');
			var packageItemsConditions = []; //typeof object

			if (packageItems !== undefined) {
				packageItems = packageItems.toString().split(','); //typeof object
			}

			for (var i in packageItems) {
				if (packageItems[i].length) {
					packageItemsConditions.push({
						id: parseInt(packageItems[i]),
						type: 'label',
						value: 1
					});
				}
			}
		}

		_temporaryParams['recalculate_price_filters'] = 1;
		delete _params['price'];
		delete _params['volume_price_from'];
		delete _params['volume_price_to'];

		//кнопка атрибутов и пакетных услуг
		if (isButton) {
			//передаем пакетные услуги в ajax нужном формате
			_temporaryParams['package_items_conditions'] = packageItemsConditions;

			//формируем параметры пакетных кворков в адресной строке в соответствии с требованиями
			for (var i in packageItems) {
				if (packageItems[i].length) {
					_params['package_items_conditions[' + i + '][id]'] = packageItems[i];
					_params['package_items_conditions[' + i + '][type]'] = 'label';
					_params['package_items_conditions[' + i + '][value]'] = 1;

					//выделяем выбранные пакетные услуги
					$('.custom-select__list-item[data-id=' + packageItems[i] + ']').addClass('custom-select__list-item_active');
				}
			}
		}
		_reloadFilter();
	};

	/**
	 * Актуализируем допустимые податрибуты в зависимости от атрибута
	 * @private
	 */
	var _actualizeSubattributes = function() {
		// При переключении атрибутов отсекаем не общие податрибуты
		var subattributesExclusion = _subattributesExclusion(_params);
		if (subattributesExclusion) {
			_subattributes = {};

			$('.unembedded-filter').find('.clear-button').hide();
			$('.unembedded-filter').find('.unembedded-item a').removeClass('active');

			if (subattributesExclusion.length > 0) {
				_params['subattribute'] = subattributesExclusion.join('-');
				
				

				$.each(subattributesExclusion, function(i, v) {
					if (findInTreeRecursive(v) !== undefined) {
						if (_subattributes[findInTreeRecursive(v).parent_id] === undefined) {
							_subattributes[findInTreeRecursive(v).parent_id] = [];
						}
						_subattributes[findInTreeRecursive(v).parent_id].push(Number(v));

						$('.unembedded-filter')
							.find('.unembedded-item a[data-id="' + v + '"]').addClass('active')
							.closest('.unembedded-filter').find('.clear-button').show();
					}
				});
			} else {
				delete _params['subattribute'];
			}
		}
	};

	/**
	 * Исключаем не дочерние податрибуты
	 */
	var _subattributesExclusion = function(_params) {
		if (!_params) {
			return;
		}

		var newListSubattribute = [];
		var subattribute = _params['subattribute'] ? _params['subattribute'] : false;
		var attributeId = _params['attribute_id'] ? _params['attribute_id'] : false;

		_checkSubattribute = function(attributeId) {
			var allChildren = findAllChildrenIds(attributeId);
			for (var i in subattribute) {
				for (var k in allChildren) {
					if (subattribute[i] == allChildren[k]) {
						newListSubattribute.push(subattribute[i]);
						delete subattribute[i];
						break;
					}
				}
			}

			var filteredSubattribute = subattribute.filter(function(el) {
				return el !== null;
			});
			subattribute = filteredSubattribute;
		};

		if (!subattribute) {
			return;
		}

		subattribute = subattribute.split('-');

		// Поиск по локальной ветке
		if (attributeId) {
			_checkSubattribute(attributeId);
		}

		// Поиск по всем веткам первого уровня, кроме родительского
		for (var a in attributesTree) {
			if (attributesTree[a].unembedded == true) {
				_checkSubattribute(attributesTree[a].id);
			}
		}

		return newListSubattribute;
	};

	var _getSubattributesString = function() {
		var subattributes = [];
		$.each(_subattributes, function(k, v) {
			subattributes = Array.prototype.concat(subattributes, v);
		});
		return subattributes.join('-');
	}

	var _setSubattribute = function(e) {
		var el = $(this);
		var parent = el.closest('.unembedded-filter');
		var classificationId = parent.data('id');
		parent.find('a').removeClass('active');
		var clearButton = parent.find('.clear-button');
		var id = el.data('id');
		if (id) {
			_addClassificationRecursive(classificationId, id, false);
			el.addClass('active');
			clearButton.css('display', 'block');
		} else {
			_deleteClassificationRecursive(classificationId);
			clearButton.hide();
			el.closest('.popup-filter__group.expandable')
				.find('.popup-filter__group-title span')
				.text('');
			_isClearUnembeddedFilter = true;
			
		}
		_params["subattribute"] = _getSubattributesString();

		_reloadFilter();
	};

	var _setMultipleSubattribute = function(e) {
		var el = $(this);
		var parent = el.closest('.unembedded-filter');
		var classificationId = parent.data('id');
		parent.find('a').removeClass('active');
		var clearButton = parent.find('.clear-button');
		var id = el.data('id');
		if (el.prop("checked")) {
			_addClassificationRecursive(classificationId, id, true);
			el.addClass('active');
			clearButton.css('display', 'block');
		} else {
			_deleteMultipleSubattrubite(classificationId, id);
			if (_subattributes[classificationId] === undefined || _subattributes[classificationId].length  === 0) {
				clearButton.hide();
			}
			el.closest('.popup-filter__group.expandable')
				.find('.popup-filter__group-title span')
				.text('');
		}
		_params["subattribute"] = _getSubattributesString();

		_reloadFilter();

		// Хлебные крошки
		refreshBreadCrumbs();
	};

	/**
	 * Обновление хлебных крошек под дерево категорий
	 */
	var refreshBreadCrumbs = function() {
		var $breadCrump = jQuery('.bread-crump');
		var $firstItem = $breadCrump.find('li[data-item-type="category"]').first();
		var _delimiter = '<span class="bread-crump-delimiter">&nbsp;&nbsp;</span>';

		$breadCrump.find('li[data-item-type="category"]').each(function() {
			var $category = jQuery(this).find('a');
			$category.attr('href', $category.data('href'));
		});

		$breadCrump.find('li[data-item-type="category"]').last().find('.bread-crump-delimiter').remove();

		// Собираем последовательность
		var tree = [];
		jQuery('#foxdontshowcats .active > a').parents('li').each(function() {
			var $el = jQuery(this).find('> a:not(.category)');
			if ($el.length) {
				tree.push({
					'name': $el.text(),
					'id': $el.data('id'),
					'isActive': function() {
						return $el.closest('li').hasClass('active');
					}()
				});
			}
		});

		// Удаляем все атрибуты, оставляем только категории
		$breadCrump.find('li[data-item-type="attribute"]').remove();

		var $newH1 = $breadCrump.find('li[data-item-type="category"]').last().text();

		if (tree.length == 0) {
			$("h1").html($newH1);
			return false;
		}
		// Ревертим последовательность списка
		tree = tree.reverse();

		jQuery.each(tree, function(index, value) {
			var $newListItem = $firstItem.clone(true);

			$newListItem.attr('data-level', index + 1);
			$newListItem.attr('data-item-type', 'attribute');
			$newListItem.find('a')
				.attr('href', Utils.updateQueryString('attribute_id', value.id, ''))
				.removeAttr('data-href');

			$newListItem.find('.bread-crump-delimiter').remove();
			$newListItem.find('a').before(jQuery(_delimiter));
			$newListItem.find('.bread-crumb-title').text(value.name);
			$newListItem.find('meta').remove();

			if (value.isActive) {
				$newListItem.find('a').removeAttr('href');
			}

			$breadCrump.find('ol').append($newListItem);

			$newH1 += " - " + value.name;
		});

		jQuery.each(jQuery(".js-unembedded-filter .unembedded-item label.custom-select__list-item_active"), function(index, subAttribute) {
			$newH1 += " - " + $(subAttribute).text();
		});
		$("h1").html($newH1);
	};

	var _setActiveInput = function() {
		var $el = $(this);
		var name = $el.attr('name');

		$_form.find('input[name="' + name + '"]').prop('checked', false).removeAttr('checked');
		$el.prop('checked', true).attr('checked', 'checked');
	};

	var _initClearLinksForBlocks = function() {
		var $blocks = $_form.find(_blockContentClass);

		$blocks.each(function() {
			var $el = $(this);
			var $link = $el.find(_clearClass);
			var names = [];

			// собрать все имена input-ов блока
			$el.find('input').each(function() {
				names.push($(this).attr('name'));
			});
			names = Utils.uniqueArray(names);
			names = names.join(' ');

			$link.attr('data-name', names);
		});
	};

	var allowedKeyCodes = [8, 9, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105],
		arrowsKeyCodes = [37, 38, 39, 40];
	var _onKeydownPriceFilter = function(e) {
		var keyCode = e.keyCode || e.which;
		if (allowedKeyCodes.concat(arrowsKeyCodes).indexOf(keyCode) === -1) {
			return false;
		}
	};


	var priceFilterInputTimer,
        volumePriceFilterInputTimer,
		timeoutValue = 800,
		priceFiltersTestRegExp = new RegExp(/^[0-9]*$/),
		sendTimeout;

	var _updateSendTimeout = function() {
		clearTimeout(sendTimeout);
		sendTimeout = setTimeout(function() {
			$.each(timeoutActions, function(k, v) {
				v();
			});
			timeoutActions = {};
			_load();
			_pushUrlHistory();
			_setHtmlFilter();
		}, timeoutValue);
	}

	var _onKeyupPriceFilter = function(e) {
		var _this = $(this),
			keyCode = e.keyCode || e.which;

		if (allowedKeyCodes.indexOf(keyCode) === -1) {
			return false;
		}

		if (_this.val().length && _this.next('.price-filter-input__clear').hasClass('hidden')) {
			_this.next('.price-filter-input__clear').removeClass('hidden');
		} else if (!_this.val().length && !_this.next('.price-filter-input__clear').hasClass('hidden')) {
			_this.next('.price-filter-input__clear').addClass('hidden');
		}

		if (_this.hasClass('price-filter-input-error')) {
			_this.removeClass('price-filter-input-error');
		}



		timeoutActions["priceFilterInputTimer"] = function() {
			$('.js-kwork-filter-input[name="price"]')
				.prop('checked', false)
				.removeAttr('checked');

			_this
				.closest('.card__content-body')
				.find(_clearClass)
				.hide();

			var priceValue = '',
				inputError = false;
			$('.price-filter-input').each(function() {
				var elementValue = $(this).val();
				if (priceFiltersTestRegExp.test(elementValue)) {
					var fieldVal = elementValue.length ? parseFloat(elementValue) : '';
					priceValue += priceValue.length ? fieldVal : fieldVal + '_';
				} else {
					$(this).addClass('price-filter-input-error');
					inputError = true;
				}
			});

			_params['price'] = priceValue;

			if (!inputError) {
				if (priceValue === '_') {
					delete _params['price'];
				}
			}
		};
		timeoutValue = 800;
		_updateSendTimeout();
	};

	var _onKeyupVolumePriceFilter = function (e) {
		var _this = $(this),
			keyCode = e.keyCode || e.which;

		if (allowedKeyCodes.indexOf(keyCode) === -1) {
			return false;
		}

		if (_this.val().length && _this.next('.price-filter-input__clear').hasClass('hidden')) {
			_this.next('.price-filter-input__clear').removeClass('hidden');
		} else if (!_this.val().length && !_this.next('.price-filter-input__clear').hasClass('hidden')) {
			_this.next('.price-filter-input__clear').addClass('hidden');
		}

		timeoutActions["volumePriceFilterInputTimer"] = function() {
			_this
			.closest('.card__content-body')
			.find(_clearClass)
			.hide();

			var priceValue = '',
				inputError = false,
				elementValue = _this.val();
			if (priceFiltersTestRegExp.test(elementValue)) {
				priceValue = elementValue.length ? parseFloat(elementValue) : '';
			} else {
				_this.addClass('price-filter-input-error');
				inputError = true;
			}
			_params[_this.attr('name')] = priceValue;
			if (!inputError) {
				_load();
				if (priceValue === '') {
					delete _params[_this.attr('name')];
				}
			}
		};
		timeoutValue = 800;
		if (!isMobile()) _updateSendTimeout();
	};

	var _onChangeNewTopFilter = function (e) {
		if (e) {
			e.preventDefault();
		}
		
		var parent = $(this).closest('.custom-select');
		var id = $(this).data('id');
		var parentId = parent.data('id');

		//пакетные услуги визуальная часть

		if (parent.hasClass('custom-select_theme_multiple')) {
			if ($(this).hasClass('custom-checkbox__filter-clear')) {
				var $parentBlock = $(this).closest('.js-filter-package-item');
				$parentBlock.find('.custom-select__list-item_active').removeClass('custom-select__list-item_active');
				$parentBlock.find('.custom-select__list-checkbox').prop('checked', false).removeAttr('checked');

				$(this).hide();
			} else {
				//множественный выбор
				var customSelectListItem = $('.custom-select__list-item[data-id="' + id + '"]');
				if (customSelectListItem.hasClass('custom-select__list-item_active')) {
					customSelectListItem.removeClass('custom-select__list-item_active');
					customSelectListItem.parent().find('.custom-select__list-checkbox').prop('checked', false).removeAttr('checked');

					var active = parent.find('.custom-select__list-item_active').length;
					if (active == 0) {
						parent.find('.custom-checkbox__filter-clear').hide();
					}
				} else {
					customSelectListItem.addClass('custom-select__list-item_active');
					customSelectListItem.parent().find('.custom-select__list-checkbox').prop('checked', true).attr('checked', 'checked');

					parent.find('.custom-checkbox__filter-clear').css('display', 'block');
				}
			}
		} else {
			//единичный выбор
			if ($(this).hasClass('custom-select__filter-clear') === false && $(this).hasClass('custom-volume__filter-clear') === false) {
				//пакетные услуги
				if ($(this).hasClass('custom-volume__input')) {
					var customVolumeInput = $('.custom-volume__input[data-id="' + id + '"]');
					if (parseInt($(this).val()) > 0) {
						customVolumeInput.next('.custom-volume__filter-clear').removeClass('hidden');
					} else {
						customVolumeInput.next('.custom-volume__filter-clear').addClass('hidden');
					}
				} else {
					if ($(this).hasClass('custom-select__list-item_active')) {
						$('.custom-select[data-id="' + parentId + '"]').find('.custom-select__list-item[data-id="' + id + '"]').removeClass('custom-select__list-item_active');
						$('.custom-select[data-id="' + parentId + '"] .custom-select__filter-clear').hide();
						$(this).closest('.js-filter-package-item').find('.popup-filter__group-title span').text('');
					} else {
						$('.custom-select[data-id="' + parentId + '"] .custom-select__list-item_active').removeClass('custom-select__list-item_active');
						$('.custom-select[data-id="' + parentId + '"]').find('.custom-select__list-item[data-id="' + id + '"]').addClass('custom-select__list-item_active');
						$('.custom-select[data-id="' + parentId + '"] .custom-select__filter-clear').css('display', 'block');
					}
				}
			} else if ($(this).hasClass('custom-volume__filter-clear')) {
				var customVolumeInput = $(this).parent().find('.custom-volume__input');

				customVolumeInput.val('');
				customVolumeInput.next('.custom-volume__filter-clear').addClass('hidden');
			} else if ($(this).hasClass('custom-select__filter-clear')) {
				$(this).hide();
				$(this).parent().find('.custom-select__list-item_active').removeClass('custom-select__list-item_active');
				$(this).closest('.js-filter-package-item').find('.popup-filter__group-title span').text('');
			}
		}

		//пакетные услуги логическая часть
		if (parent.hasClass('js-filter-package-item')) {
			var conditions = window.topFilters.getPackageItemsConditionsFromInput();
			if (conditions && conditions.length) {
				//передаем данные в ajax нужном формате и инициируем $.post
				_clearPackageItemsConditions();
				_temporaryParams['package_items_conditions'] = conditions;
				if (!isMobile()) {
					_load();
				}

				//формируем параметры пакетных кворков в адресной строке в соответствии с требованиями
				_clearPackageItemsConditions();
				for (var i in conditions) {
					_params['package_items_conditions[' + i + '][id]'] = conditions[i].id;
					_params['package_items_conditions[' + i + '][type]'] = conditions[i].type;
					_params['package_items_conditions[' + i + '][value]'] = conditions[i].value;
				}
			} else {
				//удаляем добавленные ранее параметры пакетных кворков вида package_items_conditions[0][id]
				_clearPackageItemsConditions();

				if (!isMobile()) {
					//инициируем $.post
					_load();
				}
			}

			if (!isMobile()) {
				_pushUrlHistory();
				_setHtmlFilter();
			}
		}
		
		//Проверяем если label ссылается на checkbox то генерируем событие change
		var checkboxId = $(this).attr('for');
		if(checkboxId !== undefined && checkboxId !== '') {
			$('#'+checkboxId).change();
		}
	};

	var _onSubmitFilterForm = function(e) {
		e.preventDefault();

		var form = $(this);
		var priceSlider = $('.popup-filter__price-range-slider');
		var priceFrom = $('#priceFrom').val();
		var priceTo = $('#priceTo').val();

		$('#volumePrice').val('');

		if (priceFrom >= priceSlider.slider('option','min') || priceTo <= priceSlider.slider('option', 'max')) {
			$('#volumePrice').val(
				$('#priceFrom').val() + '_' + $('#priceTo').val()
			);
		}

		var parent = $(this).closest('.custom-select');

		//пакетные услуги логическая часть
		if (parent.hasClass('js-filter-package-item')) {
			var conditions = window.topFilters.getPackageItemsConditionsFromInput();

			if (conditions && conditions.length) {
				//передаем данные в ajax нужном формате и инициируем $.post
				_clearPackageItemsConditions();
				_temporaryParams['package_items_conditions'] = conditions;
				_load();

				//формируем параметры пакетных кворков в адресной строке в соответствии с требованиями
				_clearPackageItemsConditions();
				for (var i in conditions) {
					_params['package_items_conditions[' + i + '][id]'] = conditions[i].id;
					_params['package_items_conditions[' + i + '][type]'] = conditions[i].type;
					_params['package_items_conditions[' + i + '][value]'] = conditions[i].value;
				}
			} else {
				//удаляем добавленные ранее параметры пакетных кворков вида package_items_conditions[0][id]
				_clearPackageItemsConditions();
				//инициируем $.post
				_load();
			}
		}

		var formData = {};
		$(form.serialize().split('&')).each(function (index, item) {
			var value = item.split('=');
			if (value[1] !== '') {
				formData[value[0]] = value[1];
			}
		});

		if (_params.query && _params.query !== '') {
			_params.query = decodeURIComponent(_params.query);
		}

		location.href = '?' + $.param(Object.assign(_params, formData));
	}

	/**
	 * Удаление из _params условий по пакетным фильтрам
	 * @private
	 */
	var _clearPackageItemsConditions = function () {
		for (var key in _params) {
			if (key.indexOf('package_items_conditions') !== -1) {
				delete _params[key];
			}
		}
	};

	var _clearPriceFilterInput = function(e) {
		e.stopPropagation();
		var customEvent = $.Event('keyup');
		customEvent.which = 8;
		timeoutValue = 200;
		$(this).prev('input').val('').trigger(customEvent);
		$(this).addClass('hidden');
	};

	var _setHtmlFilter = function() {
		try {
			sessionStorage.setItem(window.location.href, $_form.html());
		} catch (e) {
			// Если закончилось место для сессии, удаляем два последних элемена(на случай если места все ровно не хватит) и записываем новый
			var key;
			if(localStorage.length) {        
				var key = sessionStorage.key(0);
				sessionStorage.removeItem(key);
			}
			if(localStorage.length) {        
				var key = sessionStorage.key(0);
				sessionStorage.removeItem(key);
				sessionStorage.setItem(window.location.href, $_form.html());
			}
		}
	}

	var _getHtmlFilter = function () {
		return sessionStorage.getItem(window.location.href)
	}

	/**
	 * Очищение _temporaryParams
	 * @private
	 */
	var _clearTemporaryParams = function () {
		for (var key in _temporaryParams) {
			delete _temporaryParams[key];
		}
	};

	/**
	 * Показать и скрыть раздел Тегов (похожих лотов)
	 * @private
	 */
	var _toggleTags = function () {
		var $buttonTags = $(this);
		var $subTags = $('.sub_land');
		if($buttonTags.hasClass('show-land')) {
			$subTags.fadeIn();
		} else {
			$subTags.fadeOut(150);
		}
		$buttonTags.toggleClass('show-land');
	}

	/**
	 * Показать и скрыть раздел результата поиска рубрик
	 * @private
	 */
	var _toggleSearchMore = function () {
		var $button = $(this);
		var $buttonText = $(this).find('a');
		if($button.hasClass('show-search-cat')) {
			$('.js-search-category__list').fadeOut(150);
			$buttonText.text($buttonText.data('show-text'));
		} else {
			$('.js-search-category__list').fadeIn(300);
			$buttonText.text($buttonText.data('hide-text'));
		}
		$button.toggleClass('show-search-cat');
	}

	/**
	 * Показать и скрыть атрибуты
	 * @private
	 */
	var _toggleAttributesMore = function () {
		var $button = jQuery(this),
			$buttonText = $button.find('a'),
			$parentList = $button.closest('.sub_cat_list').children('.more-hidden:not(.active)');
		if ($button.hasClass('category-attributes-more_active')) {
			$parentList.fadeOut(150);
			$buttonText.text($buttonText.data('show-text'));
		} else {
			$parentList.fadeIn(150);
			$buttonText.text($buttonText.data('hide-text'));
		}
		$button.toggleClass('category-attributes-more_active');
	};

	var _toggleUnembedded = function (e) {
		var $button = $(this);
		if($button.hasClass('show-unembedded-filter')) {
			$button.siblings('.js-unembedded-filter__list').fadeOut(150);
			$button.text($button.data('show-text'));
		} else {
			$button.siblings('.js-unembedded-filter__list').fadeIn(300);
			$button.text($button.data('hide-text'));
		}
		$button.toggleClass('show-unembedded-filter');
	}

	var _resetPriceFilterInputs = function() {
		$('#priceFrom').val('');
		$('#priceTo').val('');
	};

	var _rebuildClearLinks = function() {
		var params = _getCurrentParams();

		for (var i in params) {
			var clearLink = $_form.find(_clearClass + '[data-name~="' + i + '"]');
			if (typeof clearLink.attr('data-hidden-at-start') === 'undefined') {
				clearLink.css('display', 'block');
			} else {
				clearLink.removeAttr('data-hidden-at-start');
			}
		}
	};

	var _init = function (currentParams) {

		if(currentParams == undefined){
			currentParams = {};
		}

		_baseParams = currentParams;
		_params = _baseParams;

		var gp = getGetParams();
		for (var paramName in gp) {
			_params[paramName] = gp[paramName];
		}

		$_form = $('.js-kworks-filter');
		_setEvents();
		_loadGetParameters();
		_initClearLinksForBlocks();
		_rebuildClearLinks();
		_getCurrentParams();
		_setHtmlFilter();
		_isInited = true;

		_subattributes = _getSubattributesFromParams() || {};
	};

	var _getCurrentParams = function() {
		var params = window.location.search;
		if (params) {
			params = params.replace('?','');
			params = JSON.parse('{"' + decodeURI(params).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
		}
		var attributeAlias = window.location.pathname.split("/")[3];
		if (attributeAlias) {
			params.attribute_alias = attributeAlias;
		}
		return params;
	};

	var _getParams = function () {
		return _params;
	};

	var _getIsInited = function () {
		return _isInited;
	};

	var _getSubattributesFromParams = function() {
		var params = _getParams();
		var subattributeIds = [];
		var subattributes = {};

		if (params['subattribute'] !== undefined) {
			
			$('.js-unembedded-filter .unembedded-item .custom-select__list-checkbox').prop('checked', false).removeAttr('checked');
			
			subattributeIds = params['subattribute'].split('-');

			jQuery.each(subattributeIds, function(k, v) {
				if (v.length > 0) {
					var $item = $('#unembedded_attribute_' + v);
					$item.prop('checked', true).attr('checked', 'checked');
					var parentId = $item.closest('.unembedded-filter').attr('data-id');
					if (subattributes[parentId] === undefined) {
						subattributes[parentId] = [];
					}
					subattributes[parentId].push(Number(v));
				}
			});
		}

		return subattributes;
	};

	var _deleteClassificationRecursive = function (classificationId) {
		var deletedIds = _subattributes[classificationId];
		delete _subattributes[classificationId];
		$(".unembedded-filter").each(function() {
			var cls = $(this);
			var key = $.inArray(Number(cls.data("parentId")), deletedIds);
			if (key !== -1) {
				_deleteClassificationRecursive(cls.data("id"));
			}
		});
	};

	var _deleteMultipleSubattrubite = function (classificationId, id) {
		if (_subattributes[classificationId] === undefined) {
			return;
		}
		var key = $.inArray(Number(id), _subattributes[classificationId]);
		if (key === -1) {
			return;
		}
		_subattributes[classificationId].splice(key, 1);
		$(".unembedded-filter").each(function() {
			var cls = $(this);
			if (cls.data("parentId") === id) {
				_deleteClassificationRecursive(cls.data("id"));
			}
		});
		if (_subattributes[classificationId].length === 0) {
			_deleteClassificationRecursive(classificationId);
		}
	};

	var _addClassificationRecursive = function (classificationId, id, multiple) {
		if (_subattributes[classificationId] !== undefined && $.inArray(id, _subattributes[classificationId]) !== -1) {
			return;
		}

		if (_subattributes[classificationId] === undefined) {
			_subattributes[classificationId] = [];
		}
		if (!multiple && _subattributes[classificationId].length > 0) {
			var oldId = _subattributes[classificationId].pop();
			_subattributes[classificationId] = [];

			$(".unembedded-filter").each(function() {
				var cls = $(this);
				if (cls.data("parentId") === oldId) {
					_deleteClassificationRecursive(cls.data("id"));
				}
			});
		}
		_subattributes[classificationId].push(Number(id));
	};

    var _delParam = function (name) {
		delete _params[name];
        _pushUrlHistory();
    };

	/* start методы для TopFilter на странице gallery */
	var _onChangeCategory = function(rcat) {
		if (rcat !== undefined ) {
			_params["rcat"] = rcat;
		}
		delete _params["attribute_id"];
		_load();
		_pushUrlHistory();
	};

	var _onChangeAttribute = function (attributeId) {
		if (attributeId !== undefined ) {
			_params["attribute_id"] = attributeId;
		}
		_load();
		_pushUrlHistory();
	};

	var _onChangeSort = function (name, value) {
		_params['page'] = 1;

		_params[name] = value;
		// если выбрана сортировка по свежему, то сортровку по времени убираем
		if (value === 'new') {
			delete _params['time'];
		}

		_load();
		_pushUrlHistory();
	};
	/* end методы для TopFilter на странице gallery */

	var _updateParams = function (currentParams) {
		for (var currentParam in currentParams) {
			_params[currentParam] = currentParams[currentParam];
		}

		var gp = getGetParams();
		for (var paramName in gp) {
			_params[paramName] = gp[paramName];
		}
	};

	return {
		init: _init,
		getParams: _getParams,
		delParam: _delParam,
		getIsInited: _getIsInited,
		onChangeInput: _onChangeInput,
		load: _load,
		onChangeCategory: _onChangeCategory,
		onChangeAttribute: _onChangeAttribute,
		onChangeSort: _onChangeSort,
		updateParams: _updateParams,
	}
})();

function TopFilters() {

	var _tree = [],
		_selectors = {
			containerLeft: '.js-left-filters-container',
			package_filter: '.js-filter-package-item',
		},
		_selectedAttributesIds = [],
		_packageFilters = [],
		_packageItemsConditions = {};

	function _init(tree, selectedAttributesIds, packageFilters, packageItemsConditions) {
		_tree = tree;
		_selectedAttributesIds = selectedAttributesIds;
		_packageFilters = packageFilters;
		_packageItemsConditions = packageItemsConditions;

		findNeededFilters();
	}

	function _setSelectedAttributes(attributesIds) {
		_selectedAttributesIds = attributesIds;
		removeSelects();
		findNeededFilters();
	}

	function removeSelects() {
		$(_selectors.package_filter).closest('.custom-select-wrapper_theme_left-filter').remove();
		$(_selectors.package_filter).remove();
	}

	/**
	 * Поиск атрибута в дереве по идентификатору
	 *
	 * @param attributeId
	 * @returns {*}
	 */
	function findInTreeRecursive(attributeId, tree) {
		for (var i in tree) {
			if (tree[i].id == attributeId) {
				return tree[i];
				//получаем ВСЕХ потомков
			} else if (tree[i].children.length) {
				var finded = findInTreeRecursive(attributeId, tree[i].children);
				if (finded) {
					return finded;
				}
			}
		}
	}

	function findNeededFilters() {
		if (!_packageFilters || !_packageFilters.length) {
			return;
		}

		var selectedIds = getSelectedAttributeIdsWithParents();
		var selectedFilters = [];
		for (var i in _packageFilters) {
			if (_packageFilters[i].attribute_ids.length === 0) {
				// Клонирование нужно т.к. будут изменятся пакетные опции внутри фильтра
				selectedFilters.push($.extend(true, {}, _packageFilters[i]));
			} else {
				for (var j in _packageFilters[i].attribute_ids) {
					if (selectedIds.indexOf(_packageFilters[i].attribute_ids[j]) !== -1) {
						// Клонирование нужно т.к. будут изменятся пакетные опции внутри фильтра
						selectedFilters.push($.extend(true, {}, _packageFilters[i]));
						break;
					}
				}
			}
		}

		for (var i in selectedFilters) {
			for (var j in selectedFilters) {
				if (selectedFilters[i] && selectedFilters[j] &&
					selectedFilters[i].id !== selectedFilters[j].id &&
					selectedFilters[i].name === selectedFilters[j].name) {
					selectedFilters[i].package_items = $.merge(selectedFilters[i].package_items, selectedFilters[j].package_items);
					selectedFilters.splice(j, 1);
				}
			}
		}

		for (var i in selectedFilters) {
			makePackageFilterSelect(selectedFilters[i]);
		}
	}

	function getPackageItemsConditionsFromInput(){
		var conditions = [];
		$('.js-filter-package-item').each(function () {
			var filterElement = $(this);
			var filter = getPackageFilterById(filterElement.data('id'));
			if (filter && filter.id && filter.package_items.length) {
				var firstItem = filter.package_items[0];
				if (firstItem.type === 'label') {
					filterElement.find('.custom-select__list-item_active').each(function () {
						conditions.push({
							id: $(this).data('id'),
							type: firstItem.type,
							value: 1,
						});
					});
				} else if (firstItem.type === 'int') {
					filterElement.find('.custom-volume__input').each(function () {
						var filterElementVal = parseInt(
							$(this).val().replace(/(\s)/g, '')
						);
						if (filterElementVal > 0) {
							conditions.push({
								id: $(this).data('id'),
								type: firstItem.type,
								value: filterElementVal,
							});
						}
					});
				} else {
					if (filterElement.find('.custom-select__list-item_active').length > 0) {
						conditions.push({
							id: firstItem.id,
							type: firstItem.type,
							value: filterElement.find('.custom-select__list-item_active:first').data('id'),
						});
					}
				}
			}
		});
		return conditions;
	}

	function makePackageFilterSelect(packageFilter) {
		if (packageFilter && packageFilter.id && packageFilter.package_items && packageFilter.package_items.length) {

			// если блок с таким id уже есть на странице, то рисовать еще раз не нужно. Проверка для перерисовки фильтра
			var filterPackageItem = $('.js-filter-package-item[data-id="' + packageFilter.id + '"]');
			if (filterPackageItem.length > 0) {
				return;
			}

			var multipleHtml = '';
			var selectedHtml = '';
			var checkedInput = '';

			var firstPackageItem = packageFilter.package_items[0];
			if (firstPackageItem.type === 'label') {
				multipleHtml = ' custom-select_theme_multiple';
			}

			var html = '';
			var htmlClear = '';
			var isActive = null;

			if (firstPackageItem.type === 'label') {
				for (var i in packageFilter.package_items) {
					selectedHtml = '';
					checkedInput = '';
					if (_packageItemsConditions && containsInPackageItemsConditions(_packageItemsConditions, packageFilter.package_items[i].id.toString())) {
						selectedHtml = ' custom-select__list-item_active';
						checkedInput = ' checked';
						if (isActive === null) {
							isActive = true;
						}
					}
					html += '<li>' +
						'<input class="custom-select__list-checkbox m-hidden" type="checkbox" ' +
							'value="' + packageFilter.package_items[i].id + '"' +
							'name="" id="package_filter_' + packageFilter.package_items[i].id + '" ' + checkedInput + '>' +
						'<label for="package_filter_' + packageFilter.package_items[i].id + '" class="custom-select__list-item' + selectedHtml + '" ' +
							'data-id="' + packageFilter.package_items[i].id + '">' + packageFilter.package_items[i].name + '</label>' +
					'</li>';

					htmlClear = '<a class="custom-checkbox__filter-clear" href="javascript: void(0);"'
						+ (isActive === true > 0 ? ' style="display: block;"' : '') + '>' + t('Сбросить') + '</a>';
				}
			} else if (firstPackageItem.type === 'int') {
				var packageItemsTotal = packageFilter.package_items.length;

			html += '<div class="custom-volume clearfix">' +
					'<div class="card__content-column">' +
					(packageItemsTotal > 1 ? '<div class="card__content-header popup-filter__group-title filter-custom-name bold">' + packageFilter.name + '</div>' : '') +
					'<div class="card__content-body">';
				for (var i in packageFilter.package_items) {
					var selectedValue = '';
					var selectedHidden = true;
					for (var j = 0; j < _packageItemsConditions.length; j++) {
						if (_packageItemsConditions[j].id === packageFilter.package_items[i].id.toString()) {
							selectedValue = _packageItemsConditions[j].value;
							selectedHidden = false;
						}
					}
					html += '<div class="custom-volume__input-wrapper">' +
							'<div class="card__content-header popup-filter__group-title' + (packageItemsTotal === 1 ? ' bold' : ' mt5') + '">' +
								packageFilter.package_items[i].name +
							'</div>' +
							'<div class="custom-volume__input-box">' +
								'<input type="text" class="custom-volume__input js-only-integer"' +
									' data-id="' + packageFilter.package_items[i].id + '"' +
									' data-min-value="' + packageFilter.package_items[i].min_value + '"' +
									' data-max-value="' + packageFilter.package_items[i].max_value + '"' +
									' data-type="integer"' +
									' value="' + (selectedValue ? Utils.priceFormat(selectedValue) : selectedValue) + '" autocomplete="off"' +
									' placeholder="' + t('От') + '"' +
									' pattern="[0-9\\s]*" inputmode="numeric">' +
								'<div class="custom-volume__filter-clear' + (selectedHidden ? ' hidden' : '') + '"></div>' +
							'</div>' +
						'</div>';
				}
				html +=
					'</div>' +
					'</div>' +
					'</div>';
			} else if (firstPackageItem.type === 'list' && firstPackageItem.list_values && firstPackageItem.list_values.length) {
				htmlClear = '<a class="custom-select__filter-clear" href="javascript: void(0);"' +
					(_packageItemsConditions && _packageItemsConditions.length > 0 ? ' style="display: block;"' : '') +
					'>' + t('Сбросить') + '</a>';

				for (var j in firstPackageItem.list_values) {
					selectedHtml = '';
					if (_packageItemsConditions) {
						for (var i = 0; i < _packageItemsConditions.length; i++) {
							if (_packageItemsConditions[i].id === firstPackageItem.id.toString()) {
								if (_packageItemsConditions[i].value === firstPackageItem.list_values[j]) {
									selectedHtml = ' custom-select__list-item_active';
								}
							}
						}
					}
					html += '<li>' +
						'<span class="custom-select__list-item' + selectedHtml + '" ' +
						'data-id="' + firstPackageItem.list_values[j] + '">' +
						firstPackageItem.list_values[j] +
						'</span></li>';
				}
			}

			if (html.length > 0) {
				if (firstPackageItem.type === 'int') {
					html = '<div class="js-filter-package-item custom-select custom-volume-wrapper" data-id="' + packageFilter.id + '">' + html + '</div>';
				} else {
					html = '<div class="js-filter-package-item custom-select' + multipleHtml + '" data-id="' + packageFilter.id + '">' +
						'<div class="custom-select__title popup-filter__group-title">' +
							packageFilter.name +
							' <span class="m-visible"></span><div class="kwork-icon icon-down-arrow m-visible"></div>' +
						'</div>' +
						htmlClear +
						'<ul class="custom-select__list card__content-body">' +
						html +
						'</ul></div>';
				}

				//выводим в левый фильтр под цены
				html = '<div class="custom-select-wrapper_theme_left-filter popup-filter__group' + (firstPackageItem.type === 'int' ? '' : ' expandable') + '">' + html + '</div>';
				$(_selectors.containerLeft).append(html);

				setPackageBlockName();
			}
		}
	}

	function getPackageItemById(packageFilter, itemId) {
		if (packageFilter && itemId && packageFilter.package_items && packageFilter.package_items.length) {
			for (var i in packageFilter.package_items) {
				if (packageFilter.package_items[i].id == itemId) {
					return packageFilter.package_items[i];
				}
			}
		}
	}

	function getPackageFilterById(filterId) {
		if (_packageFilters && _packageFilters.length) {
			for (var i in _packageFilters) {
				if (_packageFilters[i].id == filterId) {
					return _packageFilters[i];
				}
			}
		}
	}

	function getSelectedAttributeIdsWithParents() {
		if (_selectedAttributesIds && _selectedAttributesIds.length) {
			var firstAttributeId = _selectedAttributesIds[0];
			var parentsIds = findAllParentsIds(_tree, firstAttributeId);
			if(parentsIds && parentsIds.length){
				return _selectedAttributesIds.concat(parentsIds);
			} else {
				return _selectedAttributesIds;
			}
		} else {
			return [];
		}
	}

	//поиск всех родителей
	function findAllParentsIds(tree, attributeId) {
		for (var i in tree) {
			if (tree[i].id === attributeId) {
				if (tree[i].parent_id) {
					return [tree[i].parent_id];
				} else {
					return [];
				}
			} else if (tree[i].children.length) {
				var parents = findAllParentsIds(tree[i].children, attributeId);
				if (parents.length) {
					if (tree[i].parent_id) {
						parents.unshift(tree[i].parent_id);
					}
					return parents;
				}
			}
		}

		return [];
	}

	//поиск ближайших детей
	function findSelectedChildrens(tree, attributeId) {
		for (var i in tree) {
			if (tree[i].id === attributeId) {
				if (tree[i].children.length) {
					return tree[i].children
				} else {
					return [];
				}
			} else if (tree[i].children.length) {
				var childrens = findSelectedChildrens(tree[i].children, attributeId);
				if (childrens.length) {
					if (tree[i].id === attributeId) {
						childrens.push(tree[i]);
					}
					return childrens;
				}
			}
		}

		return [];
	}

	//поиск ближайшей родительской классификации
	function findParentClassification(tree, attributeId) {
		for (var i in tree) {
			if (tree[i].id === attributeId) {
				if (tree[i].parent_id) {
					return [{
						id: tree[i].parent_id,
						type: 'subcategory'
					}];
				} else {
					return [{
						id: tree[i].category_id,
						type: 'category',
					}];
				}
			} else if (tree[i].children.length) {
				var parents = findParentClassification(tree[i].children, attributeId);
				if (parents.length) {
					if (tree[i].parent_id && tree[i].id === attributeId) {
						parents.push(tree[i].parent_id);
					}
					return parents;
				}
			}
		}

		return [];
	}

	function containsInPackageItemsConditions(arr, elem) {
		for (var i = 0; i < arr.length; i++) {
			if (arr[i].id === elem) {
				return true;
			}
		}
		return false;
	}

	return {
		refresh: _init,
		setSelectedAttributes: _setSelectedAttributes,
		getPackageItemsConditionsFromInput: getPackageItemsConditionsFromInput,
	}
}

/* filter for mobile version */

var blockLoadMore = false;
var popupFilterOpen = false;

$(document).ready(function() {
	// js-top-filters-container - чтобы не грузилось на странице gallery
	if (isMobile() && !$('.js-top-filters-container').length) {
		setUnembeddedBlockName();
		initPriceSlider('.popup-filter__price-range-slider');

		$('.js-kworks-filter-button').on('click', function(e) {
			e.preventDefault();

			filterOpenPopup();
		});

		$('.js-kworks-filter-close').on('click', function(e) {
			e.preventDefault();

			filterClosePopup();
			clearListOpenedUnembeddedBlock();
		});

		var filterHeaderHeight = $('.popup-filter__header').height() || 0;

		$('body').on('click', '.popup-filter__group-title', function() {
			var group = $(this).parents('.popup-filter__group');
			if (group.hasClass('expandable')) {
				if (group.hasClass('expanded')) {
					group.removeClass('expanded');
				} else {
					group.addClass('expanded');
					var elOffset = $(this).offset().top - filterHeaderHeight;

					$('html').stop().animate({
						scrollTop: elOffset + 'px'
					}, 200);
				}
			}
			setListOpenedUnembeddedBlock();
		});

		$('body').on('focus', '.popup-filter input[type="text"], .popup-filter input[type="number"]', function() {
			var elOffset = $(this).closest('.popup-filter__group').offset().top - filterHeaderHeight;

			$('html').stop().animate({
				scrollTop: elOffset + 'px'
			}, 200);
		});

		$('.popup-filter').on('click', '.unembedded-filter a[data-id]', function(event) {
			if (isMobile()) {
				setUnembeddedBlockName(this);
			}
		});

		$('body').on('click', '.js-filter-package-item:not(.custom-select_theme_multiple) .custom-select__list-item', function(event) {
			if (isMobile()) {
				setPackageBlockName(this);
			}
		});

		$('.popup-filter .filter-clear').on('click', function() {
			var block = $(this).parents('.popup-filter__group')[0];
			$('.popup-filter__group-title span', block).text('');
		});

		$('.search-category-attributes .subId').click(function() {
			$('#cat_attribute_id').val($(this).data('id'));
		});

		$("#priceFrom").val(
			$(".popup-filter__price-range-slider" ).slider("values", 0)
		);
		$("#priceTo").val(
			$(".popup-filter__price-range-slider" ).slider("values", 1)
		);

		$('#filter-form').on('submit', function(e) {
			if ($('#volumePrice').val() === "_") {
				$('#volumePrice').val('');
			}
		});

		$('.price-filter-input').on('change', function(element) {
			var value = $(element).val();
			var id = $(element).attr('id');
			var slider = $(".popup-filter__price-range-slider");

			if (id === 'priceFrom'
				&& value > slider.slider('option', 'min')
				&& value < slider.slider('values', 1)) {
				$(".popup-filter__price-range-slider").slider("values", 0, value)
			}
			if (id === 'priceTo'
				&& value < slider.slider('option', 'max')
				&& value > slider.slider('values', 0)) {
				$(".popup-filter__price-range-slider").slider("values", 1, value)
			}
		});
	}

	if (jQuery(window).width() < 768) {
		jQuery('.cusongslist .signout-fav-div .tooltipstered').tooltipster('disable');

		// Скрытие классификаций при длинном списке в мобильной версии
		attributesBtnMore(jQuery('#foxdontshowcats .subcats.active'));
	}
});

/**
 * Открытие модального окна с фильтром
 */
function filterOpenPopup() {
	popupFilterOpen = true;
	window.contentScrollTop = $('html').scrollTop();

	$('html').scrollTop(0);
	$('body').addClass('popup-filter-open');
	$('body').prepend($('.js-kworks-filter'));
	$('.header, .all_page').hide();
}

/**
 * Закрытие модального окна с фильтром
 */
function filterClosePopup() {
	popupFilterOpen = false;

	$('body').removeClass('popup-filter-open');
	$('#options-by-fox-dotcom').prepend($('.js-kworks-filter'));
	$('.header, .all_page').show();

	if (window.contentScrollTop != undefined) {
		$('html').scrollTop(window.contentScrollTop);
	}
}

var params = getUrlParams();

function initPriceSlider(slider_class) {
	if (!slider_class) {
		return;
	}

	var priceSlider = $(slider_class);
	var price = params['price'] ? params['price'].split('_') : [];
	var priceFrom = price[0] || priceSlider.attr('data-min');
	var priceTo = price[1] || priceSlider.attr('data-max');

	priceFrom = parseInt(priceFrom);
	priceTo = parseInt(priceTo);

	$('#priceFrom').val(priceFrom);
	$('#priceTo').val(priceTo);

	priceSlider.slider({
		range: true,
		min: priceFrom,
		max: priceTo,
		step: parseInt(priceSlider.attr('data-step')),
		values: [priceFrom, priceTo],
		slide: function(event, ui) {
			$('#priceFrom').val(ui.values[0]);
			$('#priceTo').val(ui.values[1]);
		}
	});
}

var tempScrollTop = 0;
jQuery(window).on('scroll', function() {
	if (jQuery('body').hasClass('popup-filter-open')) {
		return;
	}

	/**
	 * jQuery(window).height() в мобильных браузерах может отдавать неверные значения из-за тулбаров.
	 * Вместо этого стоит использовать window.outerHeight.
	 * Но в iOS window.outerHeight = 0, поэтому используем window.innerHeight
	 */
	var
		timeout,
		windowWidth = jQuery(window).width(),
		windowHeight = window.outerHeight > 0 ? window.outerHeight : window.innerHeight,
		windowScroll = Math.ceil(jQuery(window).scrollTop()),
		documentHeight = jQuery(document).height(),
		documentBottom = documentHeight - windowHeight;

	if (windowWidth < 768 && ((documentHeight - windowScroll) / windowHeight) < 2 && !blockLoadMore) {
		if ($('.loadKworks').hasClass('hidden')) {
			return;
		}

		var userId = USER_ID || 0;
		if (userId < 1) {
			var gp = getGetParams();
			if ('page' in gp && gp['page'] > 1) {
				if (windowScroll >= tempScrollTop && windowScroll >= documentBottom) {
					clearTimeout(timeout);
					timeout = setTimeout(show_signup, 250);
				}
				tempScrollTop = windowScroll;
				return;
			}
		}

		blockLoadMore = true;
		jQuery('.loader').show();

		window.loadMoreFunction(false, function() {
			blockLoadMore = false;
			jQuery('.loader').hide();
		});
	}
});

$(window).resize(function() {
	if ($(window).width() > 767 && popupFilterOpen) {
		filterClosePopup();
	}
	if ($(window).width() < 768 && $('.js-kwork-filter-input[name="price"]:checked').length > 0) {
		$('.filter-clear[data-name="price"]').click();
	}
});

function setUnembeddedBlockName(el) {
	if (el) {
		_setBlockName($(el));
	} else {
		$('.unembedded-filter a[data-id].active').each(function(k, v) {
			_setBlockName($(this));
		});
	}

	function _setBlockName($el) {
		if (!$el || $el.length === 0) {
			return;
		}

		var text = $el.text() || '';
		$el.closest('.popup-filter__group.expandable').find('.popup-filter__group-title span').text(text);
	}
}

function setPackageBlockName(el) {
	if (el) {
		_setBlockName($(el));
	} else {
		$('.js-filter-package-item:not(.custom-select_theme_multiple) .custom-select__list-item_active').each(function(k, v) {
			_setBlockName($(this));
		});
	}

	function _setBlockName($el) {
		if (!$el || $el.length === 0) {
			return;
		}

		var text = $el.text() || '';
		$el.closest('.js-filter-package-item').find('.popup-filter__group-title span').text(text);
	}
}

/**
 * Скрытие классификаций при длинном списке в мобильной версии
 *
 * @param parent
 */
function attributesBtnMore(parent) {
	if (jQuery(window).width() < 768) {
		var parentUl = parent.closest('ul');
		if (parentUl.attr('id') !== 'foxdontshowcats' && parentUl.hasClass('has-sub') === false) {
			parent = parentUl.closest('.subcats');
		}

		var attributesList = parent.children('.sub_cat_list:not(.hide)'),
			attributesListItems = attributesList.children('.subcats:not(.hide):not(.js-attributes__more)'),
			attributesBtnMore = attributesList.children('.js-attributes__more'),
			attributesBtnMoreTmpl = '<li class="subcats js-attributes__more category-attributes-more">' +
				'<a href="javascript:;" data-show-text="' + t('Показать еще') + '" data-hide-text="' + t('Свернуть') + '">' + t('Показать еще') + '</a>' +
				'</li>';

		if (attributesListItems.length > 4) {
			attributesListItems.slice(3).addClass('more-hidden');
			if (attributesBtnMore.length === 0) {
				attributesList.append(attributesBtnMoreTmpl);
			}
		}
	}
}

/**
 * Новый списко для блоков
 * фильтра js-unembedded-filter
 */
function clearListOpenedUnembeddedBlock() {
	window.listOpenedUnembeddedBlock = [];
}

/**
 * Открываем блоки из списка
 * фильтров js-unembedded-filter
 */
function openUnembeddedBlock() {
	if (window.listOpenedUnembeddedBlock === undefined) {
		return;
	}

	for (var i in window.listOpenedUnembeddedBlock) {
		jQuery('.js-unembedded-filter')
			.find('.unembedded-filter[data-id="' + window.listOpenedUnembeddedBlock[i] + '"]')
			.closest('.popup-filter__group.expandable')
			.addClass('expanded');
	}
}

/**
 * Устарновить список открытых блоков
 * в фильтре js-unembedded-filter
 */
function setListOpenedUnembeddedBlock() {
	clearListOpenedUnembeddedBlock();

	jQuery('.js-unembedded-filter')
		.find('.popup-filter__group.expanded')
		.each(function() {
			window.listOpenedUnembeddedBlock.push(
				jQuery(this).find('.unembedded-filter').attr('data-id')
			);
		});
}
