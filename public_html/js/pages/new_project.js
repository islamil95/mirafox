var newProjectModule = (function () {
	var _data = {};

	var _titleInput;
	var _descriptionInput;
	var _categorySelect;
	var _subCategoriesSelect;
	var _subCategorySelect;
	var _emailInput;
	var _priceLimitInput;

	var _titleErrorField;
	var _descriptionErrorField;
	var _categoryErrorField;
	var _emailErrorField;
	var _priceLimitErrorField;
	var _loader;

	var _form;

	if (isNewForm) {
		var _classes = {
			'step': '.project-form__step',
			'stepsItem': '.project-form__steps-item',
			'stepsItemEdit': '.project-form__steps-edit',
			'stepsItemSaved': 'project-form__steps-item_saved',
			'filesList': '.project-form__files-list',
			'fullDescription': '.js-want-link-toggle-desc',
			'buttonPrev': '.js-project-goto-prev',
			'buttonNext': '.js-project-goto-next',
			'buttonLast': '.js-project-goto-last',
			'hidden': 'hidden',
			'attributesSelect': '.js-category-attributes-select',
			'attributesSelectWrapper': '.js-category-attributes-select-wrapper',
			'attributeSelectWrapper': '.js-attribute-select-wrapper',
			'filesTooltip': '.js-add-files-tooltip',
			'categoryWrapper': '.js-category-select-wrapper'
		};
		var _categorySelectWrapper;
	}

	var _init = function (data) {
		_data = data;
		_titleInput = document.querySelector(data["titleInput"]);
		_descriptionInput = document.querySelector(data["descriptionInput"]);
		_categorySelect = document.querySelector(data["categorySelect"]);
		_subCategoriesSelect = document.querySelectorAll(data["subCategoriesSelect"]);
		_emailInput = document.querySelector(data["emailInput"]);
		_priceLimitInput = document.querySelector(data["priceLimitInput"]);

		_titleErrorField = document.querySelector(data["titleErrorField"]);
		_descriptionErrorField = document.querySelector(data["descriptionErrorField"]);
		_categoryErrorField = document.querySelector(data["categoryErrorField"]);
		_emailErrorField = document.querySelector(data["emailErrorField"]);
		_priceLimitErrorField = document.querySelector(data["priceLimitErrorField"]);
		_loader = document.querySelector(data["loader"]);

		_form = document.querySelector(data["form"]);

		if (isNewForm) {
			_categorySelectWrapper = document.querySelector(_classes.categoryWrapper);
		}

		_setEvents();
	};
	var _setEvents = function () {
		if (isNewSelect) {
			jQuery(_categorySelect).on('change', _categoryChanged);
		} else {
			_categorySelect.addEventListener("change", _categoryChanged);
		}

		_titleInput.addEventListener("input", _checkTitle);
		_descriptionInput.addEventListener("input", _checkDescription);
		if (isNewForm) {
			_descriptionInput.addEventListener("paste", function (e) {
				setTimeout(function () {
					updateGigDescCharsCount();
				}, 200);
			});
		}
		_priceLimitInput.addEventListener("input", _checkPriceLimit);
		_emailInput && _emailInput.addEventListener("input", _checkEmail);
		_form.onsubmit = _formSubmited;
		$(_data["subCategoriesSelect"]).on('change', function (e) {
			$(e.target).parent().find(_data["subCategoriesSelect"]).each(function (k, v) {
				v.setCustomValidity('');
				$(v).attr('title', '');
			});
			_updateMinPrice(e.target.value);

			if (isNewForm) {
				// Подгрузка атрибутов при изменение подкатегории
				_setCategoryAttributes(jQuery(this).val(), false, false);
			}
		});

		if (isNewForm) {
			// Позиционирование подсказки загрузки файлов
			_setTooltipPosition();
			jQuery(window).on('resize', Utils.throttle(_setTooltipPosition, 500));

			// Подгрузка селектов с выбранными атрибутами при редактировании
			if (wantAttributesTree.length > 0) {
				var attributesHtml = _buildHtmlRecursive(selectedAttributesIds, wantAttributesTree);
				jQuery(_classes.attributesSelectWrapper).html(attributesHtml);
				jQuery(_classes.attributesSelect).chosen('destroy').chosen({
					width: '100%',
					disable_search: true
				});
			}

			// Подгрузка атрибутов при изменении классификации
			jQuery(document).on('change', _classes.attributesSelect, function () {
				_setCategoryAttributes(jQuery('.js-sub-category-select:not(.hidden)').val(), jQuery(this).val() | 0, jQuery(this).data('parent'));
			});

			// Перемещение по шагам вперед/назад
			jQuery(_classes.buttonPrev).on('click', _navStep);
			jQuery(_classes.buttonNext).on('click', _navStep);

			// Редактирование шага
			jQuery(_classes.stepsItem).on('click', _editStep);

			// Сохранение шага с переходом на последний доступный
			jQuery(_classes.buttonLast).on('click', _saveStep);

			// Отрисовка блока превью проекта на бирже
			_setAll();

			// Отключение кнопки "Далее" при незаполненных полях
			if (!_titleInput.value) {
				_changeNextButtonState(1, 'off');
			}
		}
	};
	var _updateMinPrice = function (value) {
		if (typeof minPrices !== "undefined") {
			if (minPrices.hasOwnProperty(value)) {
				$(_priceLimitInput).data("min", minPrices[value]);
			} else {
				$(_priceLimitInput).data("min", minPrices[0]);
			}
			if (_cleanNumber(_priceLimitInput.value)) {
				_checkPriceLimit();
			}
		}
	};
	var _categoryChanged = function (e) {
		_subCategorySelect = null;
		for (var i = 0; i < _subCategoriesSelect.length; i++) {
			if (e.target.value == _subCategoriesSelect[i].dataset.catid) {
				_subCategoriesSelect[i].classList.remove("hidden");
				_subCategoriesSelect[i].name = "category";
				if (!isNewSelect) {
					_subCategoriesSelect[i].required = true;
				}
				_subCategorySelect = _subCategoriesSelect[i];
				$(_subCategoriesSelect[i]).attr('title', t('Пожалуйста, выберите рубрику'));

				if (isNewSelect) {
					// Кастомный селект категорий
					jQuery(_subCategoriesSelect).chosen('destroy');
					jQuery(_subCategoriesSelect[i]).chosen({
						width: '100%',
						disable_search: true,
						display_disabled_options: false
					});

					jQuery(_subCategoriesSelect).off('change');
					jQuery(_subCategoriesSelect).on('change', function (e) {
						var chosenContainer = jQuery(e.target).parent().find('.chosen-container');
						chosenContainer.attr('title', '');
						_updateMinPrice(e.target.value);

						if (isNewForm) {
							// Подгрузка атрибутов при изменение подкатегории
							_setCategoryAttributes(jQuery(this).val(), false, false);
						}
					});
				}
			} else {
				_subCategoriesSelect[i].classList.add("hidden");
				_subCategoriesSelect[i].name = "";
				_subCategoriesSelect[i].required = false;
			}
			_subCategoriesSelect[i].selectedIndex = 0;
		}
		_checkCategory();
		_updateMinPrice(e.target.value);

		if (isNewForm) {
			_resetCategoryAttributes();
			if (_titleInput.value && _titleInput.value.length < 56) {
				_changeNextButtonState(1, 'off');
			}
		}
	};
	var _checkTitle = function () {
		if (isNewForm) {
			_setTitle();
		}
		var text = _titleInput.value;
		if (!text) {
			_setError(_titleErrorField, t("Введите название проекта"));
			if (isNewForm) {
				_changeNextButtonState(1, 'off');
			}
			return false;
		}
		if (text.length > 55) {
			_setError(_titleErrorField, t("Максимальная длина названия - 55 символов"));
			if (isNewForm) {
				_changeNextButtonState(1, 'off');
			}
			return false;
		}
		if (isNewForm) {
			if (_categorySelect.value != 0 &&
				jQuery('.js-sub-category-select:not(.hidden)').val() != 0 &&
				jQuery(_classes.attributesSelect).val() != 0) {
				_changeNextButtonState(1, 'on');
			}
		}
		_unsetError(_titleErrorField);
		return true;
	};
	var _checkDescription = function () {
		if (isNewForm) {
			_setDescription();
		}
		var text = _descriptionInput.value;
		if (!text) {
			_setError(_descriptionErrorField, t("Введите описание проекта"));
			if (isNewForm) {
				_changeNextButtonState(2, 'off');
			}
			return false;
		}
		if (text.length > 1500) {
			_setError(_descriptionErrorField, t("Максимальная длина описания - {{0}} символов", [1500]));
			if (isNewForm) {
				_changeNextButtonState(2, 'off');
			}
			return false;
		}
		if (isNewForm) {
			_changeNextButtonState(2, 'on');
		}
		_unsetError(_descriptionErrorField);
		return true;
	};
	var _checkPriceLimit = function () {
		if (isNewForm) {
			_setPrice('input');
		}
		var text = _priceLimitInput.value;

		var number = _cleanNumber(text);

		if (!text || isNaN(number)) {
			_setError(_priceLimitErrorField, t("Введите цену"));
			if (isNewForm) {
				_changeNextButtonState(3, 'off');
			}
			return false;
		}
		var minPriceLimit = $(_priceLimitInput).data("min");
		var maxPriceLimit = $(_priceLimitInput).data("max");
		var wantLang = $(_priceLimitInput).data("lang");

		if (number < minPriceLimit || number > maxPriceLimit) {
			var errorMessage = t("Допустимая цена от {{0}} до {{1}} руб.", [Utils.priceFormat(minPriceLimit, lang), Utils.priceFormat(maxPriceLimit, lang)]);
			if (wantLang == "en") {
				errorMessage = t("Допустимая цена от ${{0}} до ${{1}}", [Utils.priceFormat(minPriceLimit, lang), Utils.priceFormat(maxPriceLimit, lang)]);
			}
			_setError(_priceLimitErrorField, errorMessage);
			if (isNewForm) {
				_changeNextButtonState(3, 'off');
			}
			return false;
		}
		if (isNewForm) {
			_changeNextButtonState(3, 'on');
		}
		_unsetError(_priceLimitErrorField);
		return true;
	};
	var _checkEmail = function() {
		var regexp = /^\S+@\S+\.\S+$/;
		var text = _emailInput.value;
		if (!regexp.test(text)) {
			_setError(_emailErrorField, t("Адрес электронной почты указан некорректно"));
			return false;
		}

		_unsetError(_emailErrorField);
		return true;
	};

	var _cleanNumber = function (text) {
		var digits = text.replace(/(\s)/g, '').replace(/[^0-9]/g, ""),
			mark = hasMark(digits);
		if (mark) {
			var digitGroups = digits.split(mark);
			if (digitGroups.length > 1) {
				digits = parseInt(digitGroups[0]).toString() + mark + digitGroups[1].charAt(0);
			}
		} else {
			digits = parseInt(digits).toString();
		}

		return parseFloat(digits.replace(',', '.'));
	};

	var _checkCategory = function () {
		return true;
	};

	var _formSubmited = function () {
		if (!_checkAll()) {
			return false;
		}
		var formData = new FormData(document.sendKworkRequest);
		formData = formDataFilter(formData);
		// Убираем пробелы из цены
		formData.append("price_limit", _cleanNumber(_priceLimitInput.value));

		_loaderShow();

		var xhr = new XMLHttpRequest();
		xhr.open("post", window.location.pathname);
		xhr.send(formData);

		xhr.onreadystatechange = function () {
			if (this.readyState != 4) return;
			try {
				var response = JSON.parse(this.responseText);
				_loaderHide();
				if (response.code && response.code == 201) {
					phoneVerifiedOpenModal();
				} else if (response.success === true) {
					_ga = IS_MIRROR ? "&" + getGaGetParam() : "";

					var wantStatus = '';
					if (isNewForm && response.status && response.status.length) {
						wantStatus = '?want-status=' + response.status;
					}
					document.location.href = response.redirect + wantStatus + _ga;
				} else {
					if (response.errors !== undefined) {
						for (var field in response.errors) {
							_setError(_getErrorFieldByName(field), response.errors[field][0]);
							if (isNewForm) {
								return;
							}
						}
					}
				}
			} catch (e) {
				console.error('Некорректный ответ json');
				return false;
			}
		};
		return false;
	};
	var _getErrorFieldByName = function (fieldName) {
		switch (fieldName) {
			case "title":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="1"]').click();
					_scrollTo(_titleInput);
				}
				return _titleErrorField;
			case "description":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="2"]').click();
					_scrollTo(_descriptionInput);
				}
				return _descriptionErrorField;
			case "price_limit":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="3"]').click();
					_scrollTo(_priceLimitInput);
				}
				return _priceLimitErrorField;
			case "category":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="1"]').click();
					_scrollTo(_categorySelectWrapper);
				}
				return _categoryErrorField;
			case "attributes":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="1"]').click();
					_scrollTo(_categorySelectWrapper);
				}
				return _categoryErrorField;
			case "email":
				if (isNewForm) {
					jQuery(_classes.stepsItem + '[data-step="3"]').click();
					_scrollTo(_priceLimitInput);
				}
				return _emailErrorField;
		}
	};
	var _checkAll = function () {
		if (!_checkTitle()) {
			if (isNewForm) {
				jQuery(_classes.stepsItem + '[data-step="1"]').click();
			}
			_scrollTo(_titleInput);
			return false;
		}
		if (!_checkDescription()) {
			if (isNewForm) {
				jQuery(_classes.stepsItem + '[data-step="2"]').click();
			}
			_scrollTo(_descriptionInput);
			return false;
		}
		if (!_checkPriceLimit()) {
			if (isNewForm) {
				jQuery(_classes.stepsItem + '[data-step="3"]').click();
			}
			_scrollTo(_priceLimitInput);
			return false;
		}
		if (!_checkCategory()) {
			if (isNewForm) {
				jQuery(_classes.stepsItem + '[data-step="1"]').click();
			}
			_scrollTo(isNewForm ? _categorySelectWrapper : isNewSelect ? $(_categorySelect).parent()[0] : _categorySelect);
			return false;
		}
		if (!isNewForm && isNewSelect) {
			if (!_checkSubCategory()) {
				_scrollTo(isNewForm ? _categorySelectWrapper : $(_categorySelect).parent()[0]);
				return false;
			}
		}
		if (_emailInput && !_checkEmail()) {
			return false;
		}
		return true;
	};
	var _setError = function (errorField, text) {
		errorField.innerText = text;
		errorField.classList.remove("hidden");
	};
	var _unsetError = function (errorField) {
		errorField.innerText = "";
		errorField.classList.add("hidden");
	};
	var _scrollTo = function (field) {
		$('html, body').stop().animate({
			scrollTop: field.offsetTop - 120
		}, 200);
	};

	var _loaderShow = function () {
		$(_loader).show();
	};

	var _loaderHide = function () {
		$(_loader).hide();
	};

	/**
	 * Проверка подкатегории
	 *
	 * @returns {boolean}
	 * @private
	 */
	var _checkSubCategory = function () {
		return true;
	};

	/**
	 * Проверка атрибутов выбранной категории
	 *
	 * @returns {boolean}
	 * @private
	 */
	var _checkCategoryAttributesSelect = function () {
		var hasError = false;
		jQuery(_classes.attributesSelect).each(function () {
			if (jQuery(this).val() == 0) {
				hasError = true;
			}
		});

		if (hasError) {
			_setError(_categoryErrorField, t('Выберите классификацию'));
			return false;
		}

		_unsetError(_categoryErrorField);
		return true;
	};

	/**
	 * Валидация шагов
	 *
	 * @param stepNum
	 * @returns {*}
	 * @private
	 */
	var _checkStep = function (stepNum) {
		var errorScrollEl;

		switch (stepNum) {
			case 1:
				if (!_checkTitle()) {
					errorScrollEl = _titleInput;
				} else if (!_checkCategory()) {
					errorScrollEl = _categorySelectWrapper;
				} else if (!_checkSubCategory()) {
					errorScrollEl = _categorySelectWrapper;
				} else if (!_checkCategoryAttributesSelect()) {
					errorScrollEl = _categorySelectWrapper;
				} else {
					_setTitle();
				}
				break;
			case 2:
				if (!_checkDescription()) {
					errorScrollEl = _descriptionInput;
				} else {
					_setDescription();
				}
				break;
			case 3:
				if (!_checkPriceLimit()) {
					errorScrollEl = _priceLimitInput;
				} else {
					_setPrice('input');
				}
				break;
		}

		return errorScrollEl;
	};

	/**
	 * Перемещение по шагам вперед/назад
	 *
	 * @returns {boolean}
	 * @private
	 */
	var _navStep = function () {
		var _this = jQuery(this),
			projectStepBlock = _this.closest(_classes.step),
			projectStepNum = parseInt(projectStepBlock.data('step')),
			projectStepNav = jQuery(_classes.stepsItem + '[data-step="' + projectStepNum + '"]');

		if (_this.hasClass(_classes.buttonPrev.substr(1))) {
			projectStepBlock
				.hide()
				.prev().fadeIn(300);
			projectStepNav
				.removeClass(_classes.stepsItemSaved)
				.find(_classes.stepsItemEdit).addClass(_classes.hidden);
		} else {
			// Валидация шагов
			var errorScrollEl = _checkStep(projectStepNum);
			if (errorScrollEl !== undefined) {
				_scrollTo(errorScrollEl);
				return false;
			}

			projectStepBlock
				.hide()
				.next().fadeIn(300);
			projectStepNav
				.addClass(_classes.stepsItemSaved)
				.find(_classes.stepsItemEdit).removeClass(_classes.hidden);
		}

		if (jQuery(window).height() < 800) {
			_scrollTo(_form);
		}
	};

	/**
	 * Редактирование шага
	 *
	 * @private
	 */
	var _editStep = function () {
		var _this = jQuery(this);
		projectStepNum = parseInt(_this.data('step')),
			projectStepBlock = jQuery(_classes.step + '[data-step="' + projectStepNum + '"]');

		if (_this.hasClass(_classes.stepsItemSaved)) {
			projectStepBlock.find(_classes.buttonPrev).addClass(_classes.hidden);
			projectStepBlock.find(_classes.buttonNext).addClass(_classes.hidden);
			projectStepBlock.find(_classes.buttonLast).removeClass(_classes.hidden);

			jQuery(_classes.step).hide();
			projectStepBlock.fadeIn(300).css({
				'display': 'block'
			});

			if (jQuery(window).height() < 800) {
				_scrollTo(_form);
			}
		}
	};

	/**
	 * Сохранение шага с переходом на последний доступный
	 *
	 * @returns {boolean}
	 * @private
	 */
	var _saveStep = function () {
		var projectStepNum = parseInt(jQuery(this).closest(_classes.step).data('step')),
			projectStepLastNum = parseInt(jQuery('.' + _classes.stepsItemSaved + ':last').data('step'));

		projectStepLastNum++;
		if (projectStepLastNum > 4) {
			projectStepLastNum = 4;
		}

		// Валидация шагов
		var errorScrollEl = _checkStep(projectStepNum);
		if (errorScrollEl !== undefined) {
			_scrollTo(errorScrollEl);
			return false;
		}

		jQuery(_classes.step).hide();
		jQuery(_classes.step + '[data-step="' + projectStepLastNum + '"]').fadeIn(300);

		if (jQuery(window).height() < 800) {
			_scrollTo(_form);
		}
	};

	/**
	 * Устанавливает заголовок в блоке превью проекта на бирже
	 *
	 * @private
	 */
	var _setTitle = function () {
		jQuery('.js-project-title').text(_titleInput.value);
	};

	/**
	 * Устанавливает описание в блоке превью проекта на бирже
	 *
	 * @private
	 */
	var _setDescription = function () {
		var projectDescription = _descriptionInput.value,
			projectDescriptionLength;

		projectDescription = projectDescription.replace(/<\/?script[^>]*>/g, '');
		projectDescription = projectDescription.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		projectDescriptionLength = projectDescription.length;

		projectDescription = projectDescription.replace(/\\(.)/mg, '$1');
		projectDescription = projectDescription.replace(/(\\r?\\n)+/g, '\n');
		projectDescription = nl2br(projectDescription);

		if (projectDescriptionLength < 245) {
			projectDescription = '<div class="breakwords first-letter f14 lh22">' + projectDescription + '</div>';
			jQuery(_classes.filesList).removeClass(_classes.hidden);
		} else {
			projectDescription = '<div class="breakwords first-letter f14 js-want-block-toggle lh22">' +
				projectDescription.substring(0, 245) + '...&nbsp;' +
				'<a href="javascript:void(0);" class="js-want-link-toggle-desc link_local">' + t('Показать полностью') + '</a>' +
				'</div>' +
				'<div class="breakwords first-letter f14 js-want-block-toggle lh22 hidden">' +
				projectDescription + '&nbsp;' +
				'<a href="javascript:void(0);" class="js-want-link-toggle-desc link_local">' + t('Скрыть') + '</a>' +
				'</div>';
			jQuery(_classes.filesList).addClass(_classes.hidden);
		}

		jQuery('.js-project-description').html(projectDescription);

		// Показать/скрыть полное описание
		jQuery(_classes.fullDescription).on('click', function () {
			jQuery(this).closest('.js-want-container').find('.js-want-block-toggle').toggleClass(_classes.hidden);
			jQuery(this).closest('.js-want-container').find(_classes.filesList).toggleClass(_classes.hidden);
		});
	};

	/**
	 * Устанавливает цену в блоке превью проекта на бирже
	 *
	 * @param type
	 * @private
	 */
	var _setPrice = function (type) {
		var projectPrice = _cleanNumber(_priceLimitInput.value),
			projectPriceMax = parseInt(jQuery(_priceLimitInput).data('max')),
			projectLang = jQuery(_priceLimitInput).data('lang');

		if (projectPrice > projectPriceMax) {
			projectPrice = projectPriceMax;
		} else if (isNaN(projectPrice)) {
			projectPrice = 0;
		}

		if (projectLang === 'ru' && projectPrice >= 500) {
			if (projectPrice <= 5000) {
				projectPrice = Math.floor(projectPrice / 50) * 50;
			} else if (projectPrice <= 100000) {
				projectPrice = Math.floor(projectPrice / 1000) * 1000;
			} else {
				projectPrice = Math.floor(projectPrice / 5000) * 5000;
			}
		}

		var priceName;
		if (projectPrice > Utils.priceFormat(jQuery(_priceLimitInput).data('min'), lang)) {
			priceName = t('Цена до:');
		} else {
			priceName = t('Цена:');
		}

		projectPrice = '<span class="fs12">' + priceName + '</span> ' +
			(projectLang === 'en' ? '<span>$</span>' + projectPrice : projectPrice + ' <span class="rouble">Р</span>');

		jQuery('.js-project-price').html(projectPrice);
		if (type === 'input') {
			jQuery('.wants-card__price').removeClass('hidden');
		}
	};

	/**
	 * Отрисовка блока превью проекта на бирже
	 *
	 * @private
	 */
	var _setAll = function () {
		_setTitle();
		_setDescription();
		_setPrice('init');
	};

	/**
	 * Сброс атрибутов при выборе корневой категории
	 *
	 * @private
	 */
	var _resetCategoryAttributes = function () {
		jQuery(_classes.attributesSelectWrapper).text('');
		_unsetError(_categoryErrorField);
	};

	/**
	 * Подгрузка атрибутов выбранной подкатегории
	 *
	 * @param categoryId
	 * @param attributeId
	 * @param parentId
	 * @private
	 */
	var _setCategoryAttributes = function (categoryId, attributeId, parentId) {
		_unsetError(_categoryErrorField);

		var projectData = {
			categoryId: parseInt(categoryId)
		};

		if (attributeId) {
			projectData.attributeId = parseInt(attributeId);
		}

		jQuery.ajax({
			url: '/api/attribute/payer_json',
			data: projectData,
			method: 'GET',
			dataType: 'json',
			beforeSend: function () {
				_changeNextButtonState(1, 'off');
			},
			success: function (response) {
				var selectAttributesHtml = '';

				if (response.success && response.data.length) {
					jQuery(response.data).each(function (index, value) {
						var parentAttribute = response.data[index],
							childrenAttribute = parentAttribute.children,
							parentId = parentAttribute.parent_id === null ? parentAttribute.id : parentAttribute.parent_id,
							attributeWrapperClass = _classes.attributeSelectWrapper + '-' + parentId;

						selectAttributesHtml += '<div class="' + _classes.attributeSelectWrapper.substr(1) + ' ' + attributeWrapperClass.substr(1) + '">';
						selectAttributesHtml += '<select' +
							' name="attributes[]"' +
							' class="js-category-attributes-select select-styled select-styled--thin long-touch-js f15 dib mt15"' +
							' data-parent="' + parentId + '"' +
							' data-placeholder="' + parentAttribute.title + '"' +
							'>';
						selectAttributesHtml += '<option value="" selected>' + (jQuery(window).width() < 768 ? parentAttribute.title : '') + '</option>';

						jQuery(childrenAttribute).each(function (childrenIndex, childrenValue) {
							selectAttributesHtml += '<option value="' + childrenAttribute[childrenIndex].id + '">' + childrenAttribute[childrenIndex].title + '</option>';
						});

						selectAttributesHtml += '</select>';
						selectAttributesHtml += '</div>';
					});
				}

				if (attributeId) {
					//список атрибутов выбранной классификации
					jQuery(_classes.attributeSelectWrapper + '-' + parentId).children(_classes.attributeSelectWrapper).remove();
					jQuery(_classes.attributeSelectWrapper + '-' + parentId).append(selectAttributesHtml);
				} else {
					//список классификаций выбранной подкатегории
					jQuery(_classes.attributesSelectWrapper).html(selectAttributesHtml);
				}

				jQuery(_classes.attributesSelect).chosen('destroy').chosen({
					width: '100%',
					disable_search: true
				});

				if (_titleInput.value && _titleInput.value.length < 56 && selectAttributesHtml.length === 0) {
					_changeNextButtonState(1, 'on');
				}

				if (selectAttributesHtml.length === 0) {
					_unsetError(_categoryErrorField);
				}
			},
			error: function () {
				if (_titleInput.value && _titleInput.value.length < 56) {
					_changeNextButtonState(1, 'on');
				}
			}
		});
	};

	/**
	 * Отрисовка селектов классификаций/атрибутов на основе сохраненных данных
	 *
	 * @param attributesId
	 * @param tree
	 * @returns {string}
	 */
	var _buildHtmlRecursive = function (attributesId, tree) {
		var html = '',
			w = jQuery(window).width();

		for (var i in tree) {
			if (tree[i].children.length) {
				var parentId = tree[i].parent_id === null ? tree[i].id : tree[i].parent_id,
					attributeWrapperClass = _classes.attributeSelectWrapper + '-' + parentId;

				if (tree[i].is_classification) {
					html += '<div class="' + _classes.attributeSelectWrapper.substr(1) + ' ' + attributeWrapperClass.substr(1) + '">';
					html += '<select' +
						' name="attributes[]"' +
						' class="js-category-attributes-select select-styled select-styled--thin long-touch-js f15 dib mt15"' +
						' data-parent="' + parentId + '"' +
						' data-placeholder="' + tree[i].title + '"' +
						'>';
					html += '<option value="" selected>' + (w < 768 ? tree[i].title : '') + '</option>';
				}
				for (var j in tree[i].children) {
					if (tree[i].children[j].is_classification === false) {
						html += '<option ' +
							'value="' + tree[i].children[j].id + '"' +
							(attributesId.indexOf(tree[i].children[j].id) !== -1 ? ' selected' : '') +
							'>' + tree[i].children[j].title + '</option>';
					}

				}
				if (tree[i].is_classification) {
					html += '</select>';
				}

				html += _buildHtmlRecursive(attributesId, tree[i].children);

				if (tree[i].is_classification) {
					html += '</div>';
				}
			}
		}

		return html;
	};

	/**
	 * Позиционирование подсказки загрузки файлов
	 */
	var _setTooltipPosition = function () {
		if (jQuery(window).width() < 1550) {
			jQuery(_classes.filesTooltip).addClass('block-state-active_tooltip_bottom-right-arrow');
		} else {
			jQuery(_classes.filesTooltip).removeClass('block-state-active_tooltip_bottom-right-arrow');
		}
	};

	/**
	 * Отключение кнопки "Далее" при незаполненных полях
	 * @param step - шаг формы
	 * @param state - требуемое состояние кнопки on/off
	 */
	var _changeNextButtonState = function (step, state) {
		var buttonNext = jQuery(_classes.step + '[data-step="' + step + '"] ' + _classes.buttonNext);
		var buttonLast = jQuery(_classes.step + '[data-step="' + step + '"] ' + _classes.buttonLast);
		if (state === 'off') {
			buttonNext.addClass('disabled').prop('disabled', 'disabled');
			buttonLast.addClass('disabled').prop('disabled', 'disabled');
		} else if (state === 'on') {
			buttonNext.removeClass('disabled').prop('disabled', '');
			buttonLast.removeClass('disabled').prop('disabled', '');
		}
	};

	return {
		init: _init
	}
})();

/**
 * Перерасчет введеных значений в поля сроков в днях или в часах
 * @param currentInput - текущее поле ввода в форме
 */

function calculateDeadlineDaysHours(currentInput) { // 
	var daysInput = $('.js-deadline-input[name="deadline_days"]'),
		hoursInput = $('.js-deadline-input[name="work_hours"]'),
		currentInputValue = parseFloat(currentInput.val());

	if (currentInput.attr('name') == 'work_hours') {
		daysInput.val(Number((currentInputValue / 8).toFixed(1)));
		if (isNaN(daysInput.val())) {
			daysInput.val('')
		}
	} else {
		hoursInput.val(Number((currentInputValue * 8).toFixed(0)));
		if (isNaN(hoursInput.val())) {
			hoursInput.val('')
		}
	}
}

/**
 * Проверка проекта на минимальные значения за час или за проект в целом
 * @param currentInput - текущее поле ввода в форме
 */

function checkMinPriceForProject(currentInput) { // 
	var submitBtn = $('.js-sendKworkRequest__submit'),
		errorPlaceholder = $('#price-error'),
		priceLimitInput = $('#price_limit'),
		lowPriceBlockError = $('#low-project-price-error'),
		deadLineInput = $('#work_hours'),
		projectPriceInputVal = parseFloat(priceLimitInput.val().replace(/\s/g, '')),
		subCatergory = $('.js-project-name');

	if (deadLineInput.val() == 0) // дополнительная проверка на отсутствие значений в часах
		deadLineInput.val('')

	if (currentInput) {
		var projectURL = currentInput.find('option:selected').attr('data-project-url')
		priceLimitInput.attr('data-hour-price', currentInput.find('option:selected').attr('data-hour-price'));
		priceLimitInput.attr('data-project-price', currentInput.find('option:selected').attr('data-project-price'));
		$('span[data-project-price]').html(currentInput.find('option:selected').attr('data-project-price'));
		if (projectURL) {
			subCatergory.html('<strong>"'+ currentInput.find('option:selected').html() +'"</strong>');
			subCatergory.attr('href', KWORK_BASE_URL + projectURL);
		}
	}

	if (deadLineInput.val().length > 0 && priceLimitInput.val()) {
		var hourPrice = parseFloat(priceLimitInput.attr('data-hour-price')),
			userHourSet = parseFloat($('[name="work_hours"]').val());
		if (projectPriceInputVal < (hourPrice * userHourSet)) {
			errorPlaceholder.fadeIn();
			lowPriceBlockError.fadeOut();
			submitBtn.prop('disabled', true).addClass('disabled')
		} else {
			errorPlaceholder.fadeOut();
			submitBtn.prop('disabled', false).removeClass('disabled');
		}
	} else if (deadLineInput.val().length == 0 && $('.js-sub-category-select:not(.hidden) option:selected').length == 1) {
		if (projectPriceInputVal < parseInt(priceLimitInput.attr('data-project-price')) * 0.7) { // ставим условие если проект ниже 70% от ср. стоимости
			lowPriceBlockError.fadeIn();
			errorPlaceholder.fadeOut();
			submitBtn.prop('disabled', true).addClass('disabled');
		} else {
			lowPriceBlockError.fadeOut();
			errorPlaceholder.fadeOut();
			submitBtn.prop('disabled', false).removeClass('disabled');
		}
	} else {
		errorPlaceholder.fadeOut();
		submitBtn.prop('disabled', false).removeClass('disabled');
	}
}

$(function () {

	if ($('.js-deadline-input').length > 0) { // проверяем если ли поле для ввода часов работы (проверка на необходимость функционала на сайте)

		// делаем пересчет при смене значений в селекте рубрики и при изменений значения в вводе суммы
		$('.js-deadline-input').on('change keyup', function () {
			var inputValue = $(this).val(),
				daysInput = $('[name="deadline_days"]');

			if (inputValue == '0' && $(this).attr('id') == 'work_hours') {
				$(this).val('');
			} else if (parseFloat(daysInput.val()) == 0 && daysInput.val().length > 2) {
				$(this).val('');
			}

			// дополнительная проверка 00 в инпуте дней
			daysInput.on('blur', function () {
				if (parseFloat(daysInput.val()) == 0) {
					$('.js-deadline-input').val('');
				}
			});
			calculateDeadlineDaysHours($(this));
			checkMinPriceForProject();
		});

		// делаем пересчет при смене значений в селекте рубрики
		$('body').on('keyup change', '.js-price-changer', function () {
			checkMinPriceForProject($(this));
		});

		// сформировано для страницы редактирования проекта
		$(window).on('load', function () {
			$('.js-sub-category-select:not(.hidden)').each(function () {
				checkMinPriceForProject($(this));
			});
			$('[name="work_hours"]').each(function () {
				calculateDeadlineDaysHours($(this));
			});
		});
	}

	// только цифры и точка в инпуте без разделения на разряды
	$(".js-input-number-with-comma").on("change keyup input click", function () {
		this.value.match(/[^0-9/.]/g, "") && (this.value = this.value.replace(/[^0-9]/g, ""));
	});

});

newProjectModule.init({
	"titleInput": ".js-title-input",
	"descriptionInput": ".js-description-input",
	"categorySelect": ".js-category-select",
	"subCategoriesSelect": ".js-sub-category-select",
	"emailInput": ".js-email-input",
	"priceLimitInput": ".js-price-limit-input",

	"titleErrorField": ".js-title-error-field",
	"descriptionErrorField": ".js-description-error-field",
	"categoryErrorField": ".js-category-error-field",
	"emailErrorField": ".js-email-error-field",
	"priceLimitErrorField": ".js-price-limit-error-field",
	"loader": ".js-preloader",

	"form": ".js-form"
});