var ProjectsModule = (function ($) {

	var _selectors = {
		projectList: ".js-project-list",
		filter: {
			categoryContainer: ".js-category-container",
			category: ".js-category",
			subCategoryContainer: ".js-sub-category-container",
			subCategory: ".js-sub-category",
			subCategoryWrap: ".js-sub-category-wrap",
			priceFrom: '#price-from',
			priceTo: '#price-to',
			hiring: "#hiring-from",
			filterInput: ".js-filter-input",
			inputError: ".js-filter-error",
			inputClear: ".js-filter-clear",
			inputFavourite: ".js-favourite-category-input",
			favouriteCategoryFilter: '.js-favourite-category-filter',
			favouriteCategoryItem: '.favourite-category-item',
			favouriteCategoryAll: '.js-favourite-category-all',
			favouriteCategoryList: '.js-favourite-category-list',
			favouriteCategoryListHide: '.js-favourite-category-list-hide',
			favouriteCategoryMore: '.js-favourite-category-filter-more',
			inputCheckbox: '.js-input-checkbox',
			inputCheckboxClear: '.js-input-checkbox-clear'
		},
		popups: {
			close: '.js-popup-close',
			email: {
				link: '.js-link-email-notification',
				popup: '.js-popup-email-notification',
				listInput: '.js-email-notification-yes',
				input: '.js-email-notification-period',
				warning: '.form-warning',
				buttonSave: '.js-save-email-notification-periods',
			},
			favorite: {
				link: '.js-link-change-my-categories',
				popup: '.js-popup-change-my-categories',
				itemMy: '.js-my-category-item',
				buttonSave: '.js-save-my-categories',
				checkbox: '.js-change-categories',
				result: '.js-result-delete-categories',
			},
			deleted: {
				link: '.js-link-delete-categories',
				popup: '.js-popup-delete-categories',
				buttonDelete: '.js-delete-my-categories',
			},
			warning: {
				block: '.js-warning-profile',
				link: '.js-link-popup-warning-profile',
			},
			connect: {
				link: '.js-link-penalty-orders',
				popup: '.js-popup-penalty-orders'
			}
		},
		want: {
			container: '.js-want-container',
			linkMore: '.js-want-link-toggle-desc',
			blockMore: '.js-want-block-toggle',
			files: '.files-list',
		}
	},
	_params = {},
	sendTimeout;

	var _initEvents = function () {
		_styleSelect();
		_hideFiles();

		$(document)
			.on("click", _selectors.want.linkMore, function () {
				$(this).closest(_selectors.want.container).find(_selectors.want.blockMore).toggleClass("hidden");
				$(this).closest(_selectors.want.container).find(_selectors.want.files).toggleClass("hidden");
			})
			.on("click", ".projects-offer-btn.denied", function () {
				$(this).parent().find(".js-denied-message").removeClass("hidden");
				return false;
			})
			.on("change", _selectors.filter.subCategory, function () {
				_removeParams('c');
				_removeParams('a');
				_removeParams('fc');

				if ($(this).val() === "subAll") {
					_setParams('c', $(_selectors.filter.category).val());
				} else {
					_setParams('c', $(this).val());
				}

				_loadProjects();
			})
			.on("change", _selectors.filter.inputFavourite, function(e) {
				e.preventDefault();

				_setFavouriteCategory($(this).val());
				_loadProjects();

				$(_selectors.filter.favouriteCategoryAll).removeClass('hidden');
			})
			.on('click', _selectors.filter.favouriteCategoryAll, function () {
				_setFavouriteCategory('all');
				_loadProjects();

				$(_selectors.filter.favouriteCategoryAll).addClass('hidden');
			})
			.on("change", _selectors.filter.inputCheckbox, function(e) {
				var $this = $(this);
				var _name = $this.attr('name');

				if ($this.prop('checked')) {
					// Сбрасываем цену в полях вбитых вручную
					if (_name === 'prices-filters[]') {
						_clearBlockFilters(['price-from', 'price-to']);
					}
				}

				// отображаем (скрваем) кнопку "Сбросить"
				var checkedCount = $(_selectors.filter.inputCheckbox + '[name="' + _name + '"]:checked').length;
				if (checkedCount > 0) {
					_showCheckboxClear(_name);
				} else {
					_hideCheckboxClear(_name);
				}

				var _selected = [];
				$(_selectors.filter.inputCheckbox + '[name="' + _name + '"]:checked').each(function() {
					_selected.push(
						$(this).val()
					);
				});

				_removeParams(_name);
				if (_selected.length) {
					_setParams(_name, _selected);
				}

				_loadProjects();
			})
			.on('click', _selectors.filter.inputCheckboxClear, _onCheckboxClear)
			.on("change", _selectors.filter.category, function () {
				_removeParams('c');
				_removeParams('a');
				_removeParams('fc');

				if ($(this).val() === "mine") {
					_setParams('a', 1);
					_setFavouriteCategory('all');
				} else if ($(this).val() !== "all") {
					_setParams('c', $(this).val());
				}

				_changeCategoryBlock();
				_loadProjects();
				_isShowPopupFavorite();
			})
			.on('click', _selectors.popups.email.link, function () {
				_showPopup(_selectors.popups.email.popup);
			})
			.on('change', _selectors.popups.email.listInput, _selectNotificationList)
			.on('change', _selectors.popups.email.input, _selectNotificationPeriod)
			.on('click', _selectors.popups.email.buttonSave, _saveNotificationPeriod)
			.on('click', _selectors.popups.favorite.link, _showPopupFavorite)
			.on('click touchstart', _selectors.popups.close, function () {
				var popup = $(this).parents('.popup');
				if (popup.is(_selectors.popups.email.popup)) {
					_updateNotificationPeriodRadio();
				}
				_hidePopup(popup);
			})
			.on('input', _selectors.filter.filterInput, _eventFilterInput)
			.on('click', _selectors.popups.deleted.link, _showPopupDelete)
			.on('click', '.js-link-change-categories', function () {
				$(this).toggleClass('change-my-categories__item-title-open');
				$(this).siblings('.js-block-change-categories').stop().slideToggle();
			})
			.on('click', _selectors.popups.favorite.buttonSave, _saveMyCategories)
			.on('click', _selectors.popups.deleted.buttonDelete, _deleteMyCategories)
			.on('change', _selectors.popups.favorite.checkbox, _checkCountFavorite)
			.on('click', _selectors.popups.warning.link, _showPopupWarningProfile)
			.on('click', _selectors.popups.connect.link, _showPopupConnectLimit)
			.on('click', _selectors.filter.inputClear, _clearFilterInput)
			.on('click', _selectors.filter.favouriteCategoryMore, _showFavouriteCategoryListMore)
			.on('click', '.projects-offer-btn.disabled', function () {
				return false;
			});

		_updateParams();
		_isShowPopupFavorite();
		_setHistory($('.js-project-list').html());

		// нужно для ссылок в письме
		var popup = new RegExp('[\?&]popup=([^&#]*)').exec(window.location.href);
		if (popup && popup[1] === 'email') {
			_showPopup(_selectors.popups.email.popup);
		} else if (popup && popup[1] === 'my') {
			_showPopup(_selectors.popups.favorite.popup);
		}
	},

	/**
	 * Устанавливаем пунк фильтра: "Мои любимые рубрики"
	 * @param value
	 * @private
	 */
	_setFavouriteCategory = function (value) {
		var $input = $(_selectors.filter.inputFavourite + '[value="' + value + '"]');

		$input.prop('checked', true);

		_removeParams('c');
		_removeParams('a');
		_removeParams('fc');

		if (value === "all") {
			_setParams('a', 1);
		} else {
			_setParams('fc', value);
		}
	},

	_styleSelect = function () {
		if (!isMobile()) {
			$(_selectors.filter.category).chosen({width: '100%', disable_search: true});
			$(_selectors.filter.subCategory).chosen({width: '100%', disable_search: true});
		} else {
			$(_selectors.filter.category).removeClass('hidden');
			$(_selectors.filter.subCategory).removeClass('hidden');
		}
	},

	_hideFiles = function () {
		$(_selectors.want.container).each(function () {
			if ($(this).find(_selectors.want.linkMore).length) {
				$(this).find(_selectors.want.files).addClass('hidden');
			}
		})
	},

	/**
	 * Показываем попап
	 * @param block
	 * @private
	 */
	_showPopup = function (block) {
		$(block).addClass('popup').removeClass('hidden');
		$('body').css({overflow: 'hidden'});
		lockBodyForPopup();
		_eventEsc();
	},

	/**
	 * Скрываем попап
	 * @param block
	 * @param unlockBody
	 * @private
	 */
	_hidePopup = function (block, unlockBody) {
		$(block).removeClass('popup').addClass('hidden');
		if (!$('.popup:not(.hidden)').length) {
			$('body').css('overflow', '');
		}

		if (unlockBody || typeof unlockBody === 'undefined') {
			unlockBodyForPopup();
		}
	},

	/**
	 * Отлавливает нажатие клавиши ESC
	 * @private
	 */
	_eventEsc = function() {
		$(document)
			.off('keyup', _escapePopup)
			.on('keyup', _escapePopup);
	},

	/**
	 * Действие при нажатии на клавишу ESC
	 * @param e
	 * @private
	 */
	_escapePopup = function(e) {
		var popupHidden = $(_selectors.popups.favorite.popup).hasClass('hidden');

		if (!popupHidden && e.keyCode == 27) {
			_hidePopup(_selectors.popups.favorite.popup);
		}
	},

	/**
	 * Действие при вводе в input
	 * @returns {boolean}
	 * @private
	 */
	_eventFilterInput = function () {
		var inputName = $(this).prop('name');

		if ($.inArray(inputName, ['price-from', 'price-to']) > -1) {
			_clearBlockFilters('prices-filters[]');
		}

		if (_validateFilterNumber($(this)) === false) {
			$(this).parents('.projects-filter__item')
				.find(_selectors.filter.inputError)
				.removeClass('hidden').html(
					t('Ввод только цифр')
				);

			return false;
		}

		if ($(this).val()) {
			_setParams(inputName, $(this).val());

			$(this).parents('.projects-filter__item').find(_selectors.filter.inputError).addClass('hidden');
			$(this).siblings(_selectors.filter.inputClear).removeClass('hidden');
		} else {
			_removeParams(inputName);
			$(this).parents('.projects-filter__item').find(_selectors.filter.inputClear).addClass('hidden');
		}

		_hideCheckboxClear(inputName);

		_updateFilterTimeout();
	},

	/**
	 * Сбросить принудительно фильтр
	 * @param filtersName Название фильтра(ов)
	 * @param callback Коллбэк который выполнится после очистки всех фильтров
	 * @private
	 */
	_clearBlockFilters = function(filtersName, callback) {
		if (!filtersName) {
			return false;
		}
		if ($.isArray(filtersName)) {
			$.each(filtersName, function(key, value) {
				_remove(value);
			});
		} else {
			_remove(filtersName);
		}

		if (callback) {
			callback();
		}

		function _remove(filterName) {
			_removeParams(filterName);

			$('[name="' + filterName + '"]').each(function() {
				if ($(this).is(':radio') || $(this).is(':checkbox')) {
					$(this).prop('checked', false);
				} else {
					$(this).val('')
						.find('+' + _selectors.filter.inputClear).addClass('hidden');
				}
			});
		}
	},

	_clearFilterInput = function () {
		var $input = $(this).prev(_selectors.filter.filterInput);
		var name = $input.prop('name');

		$input.val('');
		$(this).addClass('hidden');

		_removeParams(name);
		_updateFilterTimeout();
	},

	_updateFilterTimeout = function () {
		clearTimeout(sendTimeout);
		sendTimeout = setTimeout(function () {
			_loadProjects();
		}, 500);
	},

	_validateFilterNumber = function ($obj) {
		if (/[^0-9]/ig.test($obj.val())) {
			$obj.val($obj.val().replace(/[^0-9]/ig, ''));

			return false;
		}

		return true;
	},

	_filterState = function (values) {
		_queryUrlToParams();
		_getParams(_params);

		function checkedFilter(name, value) {
			$('[name="' + name + '"]').prop('checked', false);
			_hideCheckboxClear(name);

			if ($.isArray(value)) {
				$.each(value, function(k, v) {
					$('[name="' + name + '"][value="' + v + '"]').prop('checked', true);
				});

				if (value.length) {
					_showCheckboxClear(name);
				}

			} else {
				$('[name="' + name + '"][value="' + value + '"]').prop('checked', true);
			}
		}

		if (values) {
			if (values.parentId) {
				$(_selectors.filter.category).val(values.parentId);
			}

			if (values.childId) {
				$(_selectors.filter.subCategory).filter(":visible").val(values.childId);
			}

			$(_selectors.filter.filterInput).val('');
			if (values.priceFrom) {
				$(_selectors.filter.priceFrom).val(values.priceFrom);
			}

			if (values.priceTo) {
				$(_selectors.filter.priceTo).val(values.priceTo);
			}

			if (values.hiring) {
				$(_selectors.filter.hiring).val(values.hiring);
			}

			// Количество предложений
			if (values.kworksFilters !== undefined) {
				checkedFilter('kworks-filters[]', values.kworksFilters);
			}
			// Бюджет
			if (values.pricesFilters !== undefined) {
				checkedFilter('prices-filters[]', values.pricesFilters);
			}
		}

		_changeCategoryBlock(true);
	},

	/**
	 * Отображаем актуальный блок с категорией в зависимости от _params
	 * @private
	 */
	_changeCategoryBlock = function(isLoadHistory) {
		if (_params['a']) {
			$(_selectors.filter.favouriteCategoryAll).addClass('hidden');

			if (isLoadHistory) {
				$(_selectors.filter.category).find('option[value="mine"]').prop('selected', true);
			}
		} else if (_params['fc']) {
			_showFavoriteCategoryBlock(_params['fc']);

			$(_selectors.filter.favouriteCategoryAll).removeClass('hidden');

			if (isLoadHistory) {
				$(_selectors.filter.category).find('option[value="mine"]').prop('selected', true);
			}
		} else if (_params['c']) {
			_showCategoryBlock(_params['c']);

			var parentId = $('option[value="' + _params['c'] + '"]').closest('[data-category-id]').attr('data-category-id') || null;
			if (!parentId) {
				$(_selectors.filter.subCategoryWrap + '[data-category-id="' + _params['c'] + '"]')
					.removeClass("hidden")
					.find('option[value="subAll"]').prop('selected', true);
			}
			if (isLoadHistory) {
				if (parentId) {
					$('option[value="' + _params['c'] + '"]').prop('selected', true)
						.closest(_selectors.filter.subCategoryWrap).removeClass('hidden');
				}
				parentId = parentId ? parentId : _params['c'];
				$(_selectors.filter.category).find('option[value="' + parentId + '"]').prop('selected', true);
			}
		} else {
			_showCategoryBlock();
			$(_selectors.filter.subCategoryContainer).addClass("hidden");

			if (isLoadHistory) {
				$(_selectors.filter.category).find('option[value="all"]').prop('selected', true);
			}
		}

		$(_selectors.filter.category).trigger('chosen:updated');
		$(_selectors.filter.subCategory).trigger('chosen:updated');
	},

	_setParams = function (name, value) {
		_params[name] = value;
	},

	_removeParams = function (name) {
		delete _params[name];
	},

	/**
	 * Собрать данные из заброса url в _params
	 * @private
	 */
	_queryUrlToParams = function() {
		if (window.location.search !== '') {
			var query = decodeURI(window.location.search.substring(1)).split('&') || {};

			_params = {};
			$.each(query, function(key, value) {
				var param = value.split('=');
				var paramName = param[0].replace(/([\d])/g, '');
				var paramValue = param[1];

				if (_params[paramName] !== undefined) {
					if (!$.isArray(_params[paramName])) {
						var tmp = _params[paramName];
						_params[paramName] = [];
						_params[paramName].push(tmp);
					}
					_params[paramName].push(paramValue);
				} else {
					_params[paramName] = paramValue;
				}
			});

			return true;
		}

		return false;
	};

	/**
	 * Наполняем _params при первой загрузке страницы
	 */
	_updateParams = function () {
		_queryUrlToParams();

		if (_params['a']) {
			_setFavouriteCategory('all');
		}
		if (_params['fc']) {
			_setFavouriteCategory(_params['fc']);
		}

		_initCurrentChecked();
	},


	_showCategoryBlock = function() {
		$(_selectors.filter.subCategoryWrap).addClass("hidden");
		$(_selectors.popups.favorite.link).addClass("hidden");
		$(_selectors.filter.favouriteCategoryFilter).addClass("hidden");

		$(_selectors.filter.subCategoryContainer).removeClass("hidden");
		$(_selectors.filter.categoryContainer).removeClass('projects-filter__select--my');
	},

	_showFavoriteCategoryBlock = function(categoryId) {
		$(_selectors.filter.categoryContainer).addClass('projects-filter__select--my');
		$(_selectors.filter.subCategoryContainer).addClass("hidden");

		$(_selectors.popups.favorite.link).removeClass("hidden");
		$(_selectors.filter.favouriteCategoryFilter).removeClass("hidden");

		_setFavouriteCategory(categoryId);
	},

	/**
	 * Проставляем актуальные чекбоксы в зависимости от _params
	 */
	_initCurrentChecked = function() {
		$.each(_params, function(key, value) {
			if ($.isArray(value)) {
				$.each(_params[key], function(k, v) {
					_propChecked(key, v);
				});
			} else {
				_propChecked(key, value);
			}

			// если проставлен хоть один чекбокс, то показываем кнопку "Сбросить"
			_showCheckboxClear(key);
		});

		function _propChecked(name, value) {
			$('[name="' + name + '"][value="' + value + '"]').prop('checked', true);
		}
	},

	/**
	 * Нажимаем кнопку "Сбросить" у фильтра
	 */
	_onCheckboxClear = function() {
		var _name = $(this).data('name');

		var $checkboxes = $(_selectors.filter.inputCheckbox + '[name="' + _name + '"]');
		$checkboxes.prop('checked', false);

		$(this).addClass('hidden');

		_removeParams(_name);
		_loadProjects();
	},

	/**
	 * Отображаем кнопку "Сбросить"
	 */
	_showCheckboxClear = function(name) {
		$(_selectors.filter.inputCheckboxClear + '[data-name="' + name + '"]').removeClass('hidden');
	},

	/**
	 * Скрываем кнопку "Сбросить"
	 */
	_hideCheckboxClear = function(name) {
		$(_selectors.filter.inputCheckboxClear + '[data-name="' + name + '"]').addClass('hidden');
	},

	/**
	 * Подгрузка проектов
	 */
	_loadProjects = function () {
		var $container = $(_selectors.projectList);

		var categoryId = $(_selectors.filter.category).val();
		if (categoryId === "mine" && _params["a"] === undefined && _params["fc"] === undefined) {
			_setParams("a", 1);
		}

		var preloaderClass = $container.data('preloader-class');
		if (!$container.find('.' + preloaderClass).length) {
			$container.preloader("show");
		}

		if (window.requestLoadProjects) {
			window.requestLoadProjects.abort();
		}

		_removeParams('page');

		window.requestLoadProjects = $.post("/projects", _params, function(response) {
			// Очищаем страницу, т.к. переходы строго по ссылкам пагинации
			_setHistory(response.data.html);
			_showProjects(response.data.html);
			_updateFavouriteCategoryList(response.data);
			_updateAllCounters(response.data.counts);
		});
	},

	_setHistory = function (response) {
		var url = Object.keys(_params).length ? '?' + $.param(_params) : '';
		var parentId = $(_selectors.filter.category).val();
		var childId = "";

		if (parentId === "mine") {
			childId = $(_selectors.filter.inputFavourite + ':checked').val() || "all";
		} else {
			childId = $(_selectors.filter.subCategory).filter(":visible").val();
		}

		window.history.pushState({
			content: response,
			parentId: parentId,
			childId: childId,
			priceFrom: $(_selectors.filter.priceFrom).val(),
			priceTo: $(_selectors.filter.priceTo).val(),
			hiring: $(_selectors.filter.hiring).val(),
			kworksFilters: _params["kworks-filters[]"] || [],
			pricesFilters: _params["prices-filters[]"] || []
		}, "", url);
	},

	_loadHistory = function () {
		window.onpopstate = function (event) {
			if (event.state) {
				_showProjects(event.state.content);
				_filterState(event.state);
			}
		};
	},

	_showProjects = function (html) {
		if (typeof html === "undefined") {
			location.reload();
			return;
		}

		$(_selectors.projectList).html(html);

		_hideFiles();

		var $offerBtn = $('.projects-offer-btn');
		if (window.connectPoints === 0 && !$offerBtn.hasClass('offer-signup-js')) {
			$offerBtn.addClass('tooltipster disabled');
		} else {
			$offerBtn.removeClass('tooltipster disabled');
		}
	},

	_isShowPopupFavorite = function () {
		if ($(_selectors.filter.category).val() === "mine" && $(_selectors.filter.category).data('show-popup-my') == '1') {
			_showPopupFavorite();
		}
	},

	_showPopupDelete = function () {
		var idCategory = $(this).parents(_selectors.popups.favorite.itemMy).data('category-id');
		var nameCategory = $(this).parents(_selectors.popups.favorite.itemMy).find('span').text();

		$(_selectors.popups.deleted.popup).data('category-id', idCategory);
		$(_selectors.popups.deleted.popup).find('.popup__title').html(t('Удалить рубрику <strong>{{0}}</strong> из любимых?', [nameCategory]));

		_showPopup(_selectors.popups.deleted.popup);
	},

	_showPopupFavorite = function () {
		_updateFavoriteCheckbox();
		_checkCountFavorite();
		_showPopup(_selectors.popups.favorite.popup);
	},

	_saveMyCategories = function () {
		var myCategories = [];
		$(_selectors.popups.favorite.checkbox).filter(':checked').each(function () {
			myCategories.push($(this).val());
		});
		$.ajax({
			url: '/user_favourite_categories/save',
			type: "POST",
			data: {
				favourite_categories: myCategories
			},
			success: function (data) {
				if (data.success) {
					_hidePopup(_selectors.popups.favorite.popup);
					_loadProjects();
					_updateFavoriteCategories();

					show_message('success', t('Ваши настройки сохранены'));
				} else {
					show_message('error', t('Ошибка при сохранении'));
				}
			}
		});
	},

	_deleteMyCategories = function () {
		var categoryId = $(this).parents(_selectors.popups.deleted.popup).data('category-id');
		$.ajax({
			url: '/user_favourite_categories/remove',
			type: "POST",
			data: {
				category_id: categoryId
			},
			success: function (data) {
				if (data.success) {
					$(_selectors.popups.favorite.result)
						.removeClass('change-my-categories__result-delete--error')
						.addClass('change-my-categories__result-delete--success')
						.text(t('Удалено'))
						.show();
					$(_selectors.popups.favorite.itemMy).filter('[data-category-id="' + categoryId + '"]').remove();
					$(_selectors.popups.favorite.checkbox).filter('[data-category-id="' + categoryId + '"]').prop('checked', false);
					setTimeout(function () {
						$(_selectors.popups.favorite.result).fadeOut();
					}, 3000);
					_checkCountFavorite();
					var $deleted = $(_selectors.filter.inputFavourite + '[value="' + categoryId + '"]');
					if ($deleted.length && $deleted.is(":checked")) {
						$deleted.prop("checked", false);

						$(_selectors.filter.favouriteCategoryAll).removeClass('hidden');
					} else {
						_loadProjects();
					}
				} else {
					$(_selectors.popups.favorite.result)
						.removeClass('change-my-categories__result-delete--success')
						.addClass('change-my-categories__result-delete--error')
						.text(t('Ошибка удаления'))
						.show();
				}
				_hidePopup(_selectors.popups.deleted.popup, false);
			}
		});
	},

	_checkCountFavorite = function () {
		var countCheck = $(_selectors.popups.favorite.checkbox + ':checked').length;
		var $notChecked = $(_selectors.popups.favorite.checkbox + ':not(:checked)');
		var $notCheckedLabel = $(_selectors.popups.favorite.checkbox + ':not(:checked) + label');
		var $checkboxLabel = $(_selectors.popups.favorite.checkbox).siblings('label');

		if (countCheck >= 7) {
			$(_selectors.filter.category).data('show-popup-my', 0);
			$notChecked.prop("disabled", true);
			$checkboxLabel.tooltipster('disable');
			$notCheckedLabel.tooltipster('enable');
		} else {
			$(_selectors.filter.category).data('show-popup-my', 1);
			$notChecked.prop("disabled", false);
			if ($notCheckedLabel.length && $notCheckedLabel.hasClass('tooltipstered')) {
				$checkboxLabel.tooltipster('disable');
			}
		}
	},

	_updateFavoriteCategories = function () {
		$.ajax( {
			url: '/user_favourite_categories/get',
			type: "POST",
			success: function(data) {
				if(data.success) {
					var html = '';
					if (data.favorite_categories.length > 0) {
						data.favorite_categories.forEach(function(e) {
							html += '<li class="js-my-category-item" data-category-id="' + e.category_id + '"><span>' + e.name + '</span><a href="javascript:;" class="js-link-delete-categories" title="' + t('Удалить') + '"><i class="fa fa-times color-red"></i></a></li>';
						});
					}

					$(_selectors.popups.favorite.popup).find('ul').html(html);
					_updateFavoriteCheckbox();
				}
			}
		});
	},

	_updateFavoriteCheckbox = function () {
		$(_selectors.popups.favorite.checkbox).prop('checked', false);
		$('.js-link-change-categories').removeClass('change-my-categories__item-title-open');
		$('.js-block-change-categories').hide();

		$(_selectors.popups.favorite.itemMy).each(function () {
			var id = $(this).data('category-id');
			var checkbox = $(_selectors.popups.favorite.checkbox).filter('[data-category-id="' + id + '"]');

			checkbox.prop('checked', true);

			checkbox.parents('.js-block-change-categories').show();
			checkbox.parents('.js-block-change-categories').siblings('.js-link-change-categories')
				.addClass('change-my-categories__item-title-open');
		});
	},

	_selectNotificationList = function () {
		_showNotificationList();
		$(_selectors.popups.email.input).first().prop('checked', true);
	},

	_showNotificationList = function () {
		$(_selectors.popups.email.warning).addClass('hidden');
		$(_selectors.popups.email.input).filter(':not([value="-1"])').parents('.form-group').removeClass('hidden');
		$(_selectors.popups.email.input).filter('[value="-1"]').prop('checked', false);
	},

	_selectNotificationPeriod = function () {
		var val = $(this).val();

		if (val == -1) {
			$(_selectors.popups.email.warning).removeClass('hidden');
			$(_selectors.popups.email.listInput).prop('checked', false);

			$(_selectors.popups.email.input).not(this).parents('.form-group').addClass('hidden');
		} else {
			$(_selectors.popups.email.listInput).prop('checked', true);
			_showNotificationList();
		}
	},

	_saveNotificationPeriod = function () {
		var notification_period = $(_selectors.popups.email.input).filter(':checked').val();

		$.ajax( {
			url: '/user_notification_period/set',
			type: "POST",
			data: {
				notification_period: notification_period
			},
			success: function(data) {
				_hidePopup(_selectors.popups.email.popup);
				if(!data.success) {
					show_message('error', t('Ошибка при сохранении'));
				}
			}
		} );
	},

	_updateNotificationPeriodRadio = function () {
		$.ajax( {
			url: '/user_notification_period/get',
			type: "POST",
			success: function(data) {
				if(data.success) {
					$(_selectors.popups.email.input).filter('[value="' + data.notification_period + '"]').prop('checked', true).change();
				}
			}
		} );
	},

	_showPopupWarningProfile = function (e) {
		e.preventDefault();
		var text = $(_selectors.popups.warning.block).data('text-popup');
		var content = '' +
			'<h1 class="popup__title mr20">' + t('Необходимо заполнить профиль') + '</h1>'+
			'<hr class="gray mt20" style="margin-bottom:10px;">'+
			text +
			'<a href="/settings" class="popup__button green-btn">' +
			'<span class="dib v-align-m pt5">'+ t('Перейти в Профиль') + '</span>' +
			'</a>';

		show_popup(content, 'popup-w500');
	},

	/**
	 * показать попап по снижению лимита
	 */
	_showPopupConnectLimit = function(e) {
		e.preventDefault();
		$(_selectors.popups.connect.popup).modal('show');
	},

	/**
	 * Получаем актуальные данные о состоянии фильтра
	 * @param data
	 * @private
	 */
	_getParams = function(data) {
		data = data ? data : {};

		$.ajax({
			data: data,
			url: '/projects_params',
			type: "POST",
			success: function(response) {
				if (response.success) {
					_updateFavouriteCategoryList(response.data);
					_updateAllCounters(response.data.counts);
				}
			}
		});
	},

		/**
		 * Обновляем все счетчики фильтра
		 * @param counters
		 * @private
		 */
	_updateAllCounters = function(counters) {
		if (counters === undefined) {
			return false;
		}

		$.each(counters, function(key, value) {
			var $filter = $('[data-filter="' + key + '"]');
			$filter.attr('data-count', value)
				.find('.filter-counter')
					.text('(' + value + ')');
			if (value == 0) {
				// В случае если проставляется нулевое значение у фильтра - это означает его скрытие
				var $input = $filter.find("input:checked");
				if ($input.length > 0) {
					if ($input.hasClass("js-input-checkbox")) {
						// Если чекбокс то просто его сбрасываем
						$input.prop("checked", false);
						$input.change();
					} else if ($input.hasClass("js-favourite-category-input")) {
						// Если это радиобаттон выбора категории - выбираем все любимые
						$input.prop("checked", false);
						$(_selectors.filter.favouriteCategoryAll).removeClass('hidden');
					}
				}
			}
		});

		// Обновляем общий счетчик у блока
		$('[data-total-count]').each(function() {
			var $block = $(this);

			var totalCount = 0;
			$block.find('[data-count]').each(function() {
				totalCount += parseInt(
					$(this).attr('data-count')
				) || 0;
			});

			$block.attr('data-total-count', totalCount);
		});
	},

	/**
	 * Обновляем список избранных категорий
	 * @param data
	 * @private
	 */
	_updateFavouriteCategoryList = function(data) {
		if (!data || data.favouriteCategories === null || data.favouriteCategories === undefined) {
			return;
		}

		var _newList = '';
		var _newListHide = '';
		var $favouriteCategory = $(_selectors.filter.favouriteCategoryFilter);
		var totalCount = 0;

		// Удаляем старые записи
		$favouriteCategory.find(_selectors.filter.favouriteCategoryItem).remove();

		// Формируем новый список
		$.each(data.favouriteCategories, function(i) {
			var _name = data.favouriteCategories[i].name;
			var _id = data.favouriteCategories[i].category_id;
			var _count = data.favouriteCategoriesCount[_id];

			var _listItem = ''
				+ '<li class="favourite-category-item" data-filter="category_id_' + _id + '" data-count="' + _count + '">'
					+ '<label class="projects-filter__radio">'
						+ '<input name="favourite_category" class="js-favourite-category-input styled-radio" type="radio" value="' + _id + '">'
						+ '<div class="radio_style">'
							+ _name + '&nbsp;<span class="filter-counter">(' + _count + ')</span>'
						+ '</div>'
					+ '</label>'
				+ '</li>';

			if (_count > 0) {
				totalCount++;
			}

			if (totalCount > 7) {
				_newListHide += _listItem;
			} else {
				_newList += _listItem;
			}
		});

		if (totalCount > 7) {
			$(_selectors.filter.favouriteCategoryMore).removeClass('hidden');
		} else {
			$(_selectors.filter.favouriteCategoryMore).addClass('hidden');
		}

		// Выводим новый список
		$favouriteCategory.find(_selectors.filter.favouriteCategoryList).append(_newList);
		$favouriteCategory.find(_selectors.filter.favouriteCategoryListHide).append(_newListHide);

		var _value = Utils.getUrlParameter('a') === 1
			? 'all'
			: Utils.getUrlParameter('fc');

		// Визуально помечаем актуальный пункт в фильтре
		$(_selectors.filter.inputFavourite + '[value="' + _value + '"]')
			.prop('checked', true);

		if (_params['a']) {
			_showFavoriteCategoryBlock('all');
		}
	},

	/**
	 * Отображаем полный список любимых категорий
	 */
	_showFavouriteCategoryListMore = function () {
		var $favouriteCategoryListHide = $(_selectors.filter.favouriteCategoryFilter).find(_selectors.filter.favouriteCategoryListHide);

		$favouriteCategoryListHide.toggleClass('projects-filter__favourite-category-list--hidden');

		if ($favouriteCategoryListHide.hasClass('projects-filter__favourite-category-list--hidden')) {
			$(_selectors.filter.favouriteCategoryMore).text(t('Показать все'));
		} else {
			$(_selectors.filter.favouriteCategoryMore).text(t('Свернуть'));
		}
	};

	return {
		init: function () {
			_initEvents();
			_loadHistory();
		}
	}
})(jQuery);

ProjectsModule.init();

