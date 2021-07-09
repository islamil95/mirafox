var KworkSaveModule = (function () {
	'use strict';

	var MAX_BUNDLE_CUSTOM_EXTRA = 3;

	/**
	 * Интервал автосохранения черновика (в секундах)
	 * Автосохранение срабатывает после любой правки в форме
	 */
	var DRAFT_AUTOSAVE_INTERVAL = 60; // 60
	/**
	 * Логирует ход выполнений автосохранений (true - для разработки)
	 */
	var DRAFT_LOGS = false;

    /**
     * Минимальное количесто элементов портфолио необходимое для загрузки
     * в новых кворках в категориях с максимальным количествои элементов протфолио равным 9 (max_photo_count)
     * @type {number}
     */
    var MIN_PORTFOLIO_COUNT = 3;

	var _checkFieldsAfterReturn = [];
	var _plannedFullCheck = false;
	var _plannedFields = {};
	var _checkTextTimeout = null;
	var _checkTextXhr = [];

    /**
     * @type {
	 *          {
	 *              categories: {object},
	 *              selectedCategory: {integer},
	 *              extras: {object},
	 *              categoryExtras: {object},
	 *              volumeCategories: {array},
	 *              volumeTypes: {array},
	 *              customBundleExtras: {object},
	 *              categoryPrices: {object},
	 *              portfolio: {object},
	 *              photo: {object}
	 *          }
	 *       }
     *
     * @private
     */
    var _options = {};
    var _selectedPackageExtras = {};
    var _kworkPackages = {};
    var _categorySinglePackageFilters = {};
    var _saveMode = {};
    var _kworkId = {};
    var _kworkPackage = {};
    var _formCache = '';
    var _kworkUrl = '';
    var _kworkPrice = 0;
	var isCheckSpeller = false;
	var _kworkCategoryId;
	var _kworkAttributes;
	var _currentStep = 1;
	var _portfolioList = null;

	var timer;
	var errorFields = {};

	var _timerCategoryHints;
	var _xhrCategoryHints;

	/**
	 * Типы пакетов
	 * @type {string[]}
	 */
	var bundleTypes = ["standard", "medium", "premium"];

	/**
	 * Массив id аттрибутов, которым был показан ахтунг комментария для продавца
	 * @type {Array}
	 */
	var attributeShowAlert = [];

	/**
	 * @readonly
	 *
	 * @namespace
	 * @property {string}  step
	 * @property {string}  bundleStep
	 * @property {string}  nextStep
	 * @property {string}  category
	 *
	 * @property {object}  field
	 * @property {string}  field.block
	 * @property {string}  field.hint
	 * @property {string}  field.input
	 * @property {string}  field.error
	 *
	 * @property {object}  bundle
	 * @property {string}  bundle.item
	 * @property {string}  bundle.input
	 * @property {string}  bundle.extras
	 *
	 * @property {object}  bundleSize
	 * @property {string}  bundleSize.block
	 * @property {string}  bundleSize.checkbox
	 */
	var _selectors = {
		step: '.js-step',
		bundleStep: '.js-step-bundle',
		nextStep: '.js-next-step-btn',
		nextStepBlock: '.kwork-save-step__footer',
		category: '.js-category-select',
		saveButton: '.js-save-kwork',
		kworkForm: '.js-kwork-save-form',
		overlay: '.bundle-overlay',
		overlayButton: '.js-show_lesson-btn',
		lessonPopupContainer: '.js-popup-bundle-overlay__container',
		tooltipLesson: '.js-popup-field-tooltip_bundle',
		content_editor: '.js-content-editor', // поле contenteditable
		content_storage: '.js-content-storage', // поле textarea или input где хрянится строка из contenteditable
		inPopup: '.js-inPopup',
		field: {
			block: '.js-field-block',
			hint: '.js-field-input-hint',
			hintBundle: '.js-field-input-hint_bundle',
			input: '.js-field-input',
			select: '.js-field-select',
			inputCategory: '.js-field-input-cat',
			description: '.js-field-input-description',
			instruction: '.js-field-input-instruction',
			error: '.js-kwork-save-field-error',
			kworkPhoto: '.js-kwork-photo-input-first',
			radioBox: '.kwork-save-field-radiobox',
			checkBox: '.kwork-save-field-checkbox',
			translate: ".js-field-translate"
		},
		bundle: {
			item: '.js-bundle-item',
			removeCustomExtra: '.js-bundle-item__remove-extra',
			descriptionBlock: '.js-bundle-item__description',
			input: '.js-bundle-item__input',
			inputSelect: '.bundle-item__input_select',
			description: '.js-bundle-description',
			extras: '.js-bundle-extras',
			addCustomExtra: {
				btn: '.js-add-bundle-extra-btn',
				type: '.js-add-bundle-extra-type',
				name: '.js-add-bundle-extra-name',
				hint: '.js-add-bundle-extra-hint',
				error: '.js-add-bundle-extra-error'
			}
		},
		bundleSize: {
			block: '.js-bundle-size-selector',
			checkbox: '.js-bundle-size-checkbox'
		},
		attribute: {
			addCustomAttribute: {
				btn: '.js-add-custom-attribute-btn'
			},
			removeCustomAttribute: {
				btn: '.js-remove-custom-attribute-btn'
			},
			bindableAttribute: '.js-bindable-attribute',
			classificationCheckbox: '.classification-checkbox',
		},
		disableButton: '.js-uploader-button-disable'
	};

	var _errorMessages = {
		notCustomName: t('Введите название опции'),
		notCustomHint: t('Введите подсказку опции'),
		duplicateOption: t('Дублирование опций недопустимо')
	};

	/**
	 * Bundle type of step
	 *
	 * @readonly
	 * @enum {string}
	 */
	var stepType = {
		BUNDLE: 'bundle',
		SINGLE: 'single'
	};

	var that;

	var _lessonPopupClass = 'popup-lesson';

	var _files = {
		description: {},
		instruction: {}
	};

	/**
	 * Переходим к следующему блоку заполнения формы,
	 * при условии если нет ошибок в предыдущем блоке и блок активен
	 *
	 * @private
	 */
	var _nextStep = function (e) {
		var $nextBtn = jQuery(this);
		var $currentStep = $nextBtn.closest(_selectors.step);
		var $nextStep = $currentStep.next(_selectors.step);

		if ($nextBtn.hasClass('btn_disabled') || $nextBtn.hasClass('disabled')) {
			return false;
		}

		if ($currentStep.hasClass('kwork-save-step_inactive') || parseInt($currentStep.attr('data-step')) > 1 && $currentStep.prev(_selectors.step).hasClass('kwork-save-step_inactive')) {
			return false;
		}

		if (_plannedFullCheck || Object.keys(_plannedFields).length > 0) {
			_checkFieldsAfterReturn.push($currentStep);
		}

		if (!_checkAllStepFields($currentStep, true)) {
			var draftId = $('#draft_id').val();

			if (_isSaveDraft() && draftId.length > 0 && window.checkUserActive === undefined) {
				return false;
			}

			_scrollOnTopError();
			// Если не страница редактирования, то блоки не раскрываем
			if (!_isEditKwork()) {
				return false;
			}
		}

		_currentStep++;

		$nextStep.removeClass('kwork-save-step_inactive');
		$nextBtn.css('visibility', 'hidden');
		var parent = $nextBtn.parent();
		parent.css({'height': parent.outerHeight()});
		setTimeout(function() {
			parent.addClass('next-invisible');
		}, 0);
		$nextBtn.parent().addClass('kwork-save-step__footer_alternative');

		// Для отображения кнопки сохранить
		var portfolioType = window.portfolioType || 'none';
		var indexNextStep = parseInt($nextStep.data('step')) || null;

		if (indexNextStep == 3 && portfolioType == 'none') {
			$nextStep.next(_selectors.step).removeClass('kwork-save-step_inactive');
			_portfolioTrigger(portfolioType);
		}

		// Тут мы смотрим, кто вызвал событие если пользователь, то заходим в условие
		if (_isSaveDraft() && e && e.which) {
			// Принудительно сохраняем черновик при переходе на следующий этап
			_saveDraft();
		}

		return true;
	};

	/**
	 * Очистить состояние окна добавления пользовательской пакетной опции
	 * @private
	 */
	var _clearAddCustomBundle = function () {
		var fields = [
			_selectors.bundle.addCustomExtra.name,
			_selectors.bundle.addCustomExtra.hint
		];
		for (var i in fields) {
			var $input = $(fields[i]);
			that.validator.hideError($input);
			$input.parent().find('.js-content-editor').html('');
			$input.val('');
		}
		$(_selectors.bundle.addCustomExtra.error).html('').hide();
	};

	var _checkAllStepFields = function ($stepBlock, isShowText) {
		if (isShowText !== true) {
			isShowText = false;
		}

		// Скрываем показ ошибок в черновике
		// если инициализировал событие не пользователь
		var draftId = $('#draft_id').val();
		if (_isSaveDraft() && draftId.length > 0 && window.checkUserActive === undefined) {
			isShowText = false;
		}

		$stepBlock.find(_selectors.field.input + ', ' + _selectors.field.inputCategory + ', ' + _selectors.field.kworkPhoto + ', ' + _selectors.field.translate).each(function () {
			that.validator.checkField($(this), isShowText);
			if($(this).val() !== null) {
				_setCurrentInputSymbolCount($(this));
			}
		});

		$stepBlock.find(_selectors.bundle.input).each(function () {
			that.validator.checkField($(this), isShowText);
			_setCurrentInputSymbolCount($(this));
		});

		$stepBlock.find(_selectors.field.radioBox).each(function () {
			that.validator.checkField($(this), isShowText);
		});

		$stepBlock.find(_selectors.field.checkBox).each(function () {
			that.validator.checkField($(this), isShowText);
		});

		if ($stepBlock.hasClass('js-step-bundle')) {
			_checkActiveBundleCustomExtraCount();
		}

		return !$stepBlock.find('.kwork-save-step__field-value_error').length;
	};

	/**
	 * Set base events
	 * @private
	 */
	var _setEvents = function () {
		$(_selectors.nextStep + ', ' + _selectors.saveButton).on('click', function(e) {
			if (e.which) {
				window.checkUserActive = true;
			}
		});

		$(_selectors.nextStep).on('click', _nextStep);
		$(_selectors.category).on('change.categoryList', _onChangeCategory);
		$(_selectors.overlay).on('click', _onClickOverlay);
		$(_selectors.overlayButton).on('click', _showLesson);
		$(_selectors.tooltipLesson).on('click', _showLesson);

		$(_selectors.bundleSize.checkbox).on('change', _changeBundleSize);
		$(_selectors.field.block).on('input', _selectors.field.input, _onChangeInput);
		$(_selectors.field.block).on('input', _selectors.bundle.input, _onChangeInput);
		$(_selectors.field.block).on('tbwchange', _selectors.field.description, _onChangeInput);
		$(_selectors.field.block).on('tbwchange', _selectors.field.instruction, _onChangeInput);

		$(_selectors.field.block).on('blur', _selectors.field.input, _onBlurInput);
		$(_selectors.field.block).on('blur', _selectors.bundle.input, _onBlurInput);
		$(_selectors.field.block).on('tbwblur', _selectors.field.input, _onBlurInput);
		$(_selectors.field.block).on('change', _selectors.field.inputCategory, _onBlurInput);

		$(_selectors.bundle.description).on('input', 'textarea', _onChangeDescription);

		$(document).on('input', _selectors.content_editor, _onInputEditor);

		$(document).on('input', _selectors.bundle.input, function () {
			that.validator.hideError($(this));

			if (jQuery(this).prop('checked')) {
				jQuery('.js-add-bundle-extra-min-count-error').addClass('hidden');
			}
		});

		$(document).on('click', _selectors.attribute.addCustomAttribute.btn, _onAddCustomAttribute);
		$(document).on('click', _selectors.attribute.removeCustomAttribute.btn, _onRemoveCustomAttribute);

		$(document).on('change', _selectors.bundle.inputSelect, _setSelectChecked);
		

		$(_selectors.saveButton).on('click', function () {
			_saveKwork(false);
		});

		$('.js-quick-input').on('click', _onQuickClick);

		$('#min_volume_price').chosen({width: '90px', disable_search: true});
		$('#step1-auditory').chosen({width: '100%', disable_search: true});
		$('#step2-work-time').chosen({disable_search: true});
		$('#step1-links').blur(function () {
			var $input = $(this);
			var text = $input.val();
			var lines = text.split(/[,\s]/);

			var sites = [];
			for (var i = 0; i < lines.length; i++) {
				if (lines[i] && sites.indexOf(lines[i]) === -1 && lines[i].match(/.\../)) {
					var hostname = _extractHostname(lines[i]);
					if (hostname) {
						sites.push(hostname);
					}
				}
			}

			sites = sites.slice(0, $input.data('limit'));

			$input.val(sites.join("\n"));
		});

		$(document).on('click', '.js-bundle-item__field_add-custom', function () {
			$('.js-add-package-extra-popup').modal('show');
		});

		$('.js-add-package-extra-popup').on('hidden.bs.modal', function (e) {
			_clearAddCustomBundle();
		});

		// Обновление названия числового объема кворка при вводе, с задержкой
		var volumePluralDelayTimer = null;
		$('#step2-volume').on('input', function () {
			clearTimeout(volumePluralDelayTimer);
			volumePluralDelayTimer = setTimeout(function () {
				var pluralLabel = $('#js-volume-type-block div');
				var volumeTypeId = pluralLabel.data("volumeTypeId");
				if(volumeTypeId){
					var volume = $('#step2-volume').val();
					volume = volume.replace(/[^0-9]/gim,'');
					pluralLabel.html(VolumeTypesModule.pluralVolumeById(volumeTypeId, volume));
				}
				// Расчитываем минимальный объем
				_options.packageKworkVolumeTypeInput.calculationsMinVolume();
			}, 500);
		});
		
		// Расчитываем минимальный объем при изменение параметров объема
		$(document).on('change', '#package_volume_type_id, #step2-volume-type-id, #min_volume_price', function () {
			_options.packageKworkVolumeTypeInput.calculationsMinVolume();
		});
		$('.js-bundle-item__input.package_volume').on('input', function () {
			_options.packageKworkVolumeTypeInput.calculationsMinVolume();
		});

		// Ответ LanguageTool по результатам проверки полей формы: ошибок нет или их мало
		window.bus.$on("language-tool-check-passed", function () {
			_saveKwork(true);
		});

		// Ответ LanguageTool по результатам проверки полей формы: ошибок слишком много
		window.bus.$on("language-tool-check-failed", function () {
			// Скрыть индикатор загрузки рядом с кнопкой Сохранить
			$(".preloader_kwork").hide();
		});

		// Изменение поля в процессе правки ошибок LanguageTool
		window.bus.$on("language-tool-field-changed", function (field) {
			if (!field.formatted) {
				var editor = $("#" + field.id).siblings(_selectors.content_editor)[0];
				$(editor).html(field.value);
			}
		});

		//отключение подсказки при наличии причин отклонения модератором
		$('.kwork-save-step__content').each(function () {
			var moderReasonsContainer = $(this).find('.moder-reasons-container');
			var fieldTooltips = $(this).find('.kwork-save-step__field-value_tooltip');
			if (moderReasonsContainer.length > 0) {
				fieldTooltips.removeClass('kwork-save-step__field-value_tooltip');
			}
			moderReasonsContainer.addClass('initiated');
		});

		// Выбор категорий и классификаций при клике на подсказке
		$(document).on('click', '.js-category-hint', _useCategoryHint);

		// Получаем подсказки категории
		var title = $('.kwork-save-step__field-input_name').html().replace(/<\/?(word-error)>/gi, '');
		_getCategoryHints(title);
	};
	
	/**
	 * Отключение показа подсказки про заполнение цен в пакетах
	 */
	var unbindFreeTooltip = function () {
		unbindEvents();
		$('.js-free-prices-tooltip-trigger').removeClass('kwork-save-step__field-value_tooltip');
		bindEvents();
	};

	/**
	 * Включение подсказки про заполение цен в пакетах
	 */
	var setFreeTooltip = function () {
		unbindEvents();
		$('.js-free-prices-tooltip-trigger').addClass('kwork-save-step__field-value_tooltip');
		bindEvents();
	};

    var deleteFileCallback = function (file, cb) {
        var html = '<div>' +
            '<h1 class="popup__title">' + t('Удаление файла') + '</h1>' +
            '<hr class="gray">' +
            '<div style="display:inline-block;width:100%;">' +
            '<p class="ta-center f15 ml10 mt20 pb20">' + t('Вы действительно хотите удалить файл?') + '</p>';

        html += '<button class="file-upload-delete-btn hoverMe white-btn w160 mt20  f16" style="height:40px;">' + t('Да') + '</button>' +
            '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height:40px;">' + t('Нет') + '</button></div>' +
            '</div>';

        show_popup(html);

        $('.file-upload-delete-btn').click(function () {
            remove_popup();
			unlockBodyForPopup();
            $.ajax({
                type: "POST",
                url: '/api/kwork/deleteattachment',
                dataType: 'json',
                data: {
                    kwork_id: _kworkId,
                    file_id: file.id
                },
                success: function (data) {
                    if (data.success === true) {
                        cb();
                        if (data.redirect) {
							window.location.replace(data.redirect);
						}
                    } else {
                        popup_error(t('Ошибка удаления файла'));
                    }
                },
                error: function () {
                    popup_error(t('Ошибка удаления файла'));
                },
                processData: true
            });
        })
    };

    var onChangeFile = function () {
		return;
	}

    var _extractHostname = function(url) {
        var hostname;
        //find & remove protocol (http, ftp, etc.) and get hostname

        if (url.indexOf("://") > -1) {
            hostname = url.split('/')[2];
        }
        else {
            hostname = url.split('/')[0];
        }

        //find & remove port number
        hostname = hostname.split(':')[0];
        //find & remove "?"
        hostname = hostname.split('?')[0];

        return hostname.toLowerCase();
    };

	/**
	 * Логирование действий
	 *
	 * @param {string} message
	 */
	var _logs = function(message) {
		if (!DRAFT_LOGS) {
			return;
		}

		var currentdate = new Date();
		var datetime = currentdate.getHours()
			+ ':' + currentdate.getMinutes()
			+ ':' + currentdate.getSeconds();

		console.log('[' + datetime + '] ' + message);
	};

	/**
	 * Создание черновика для одноразового события
	 * @private
	 */
	var _oneDraftEvents = function() {
		var $draftId = $('#draft_id');

		if ($draftId.length && parseInt($draftId.val()) > 0) {
			return false;
		}

		if (window.oneDraftEvents === false || window.oneDraftEvents === undefined) {
			window.oneDraftEvents = true;

			setTimeout(_saveDraft, 200);
		}
	};

	/**
	 * Можно ли сохранять чирновик
	 */
	var _isSaveDraft = function() {
		if (window.location.pathname === '/new') {
			return true;
		}

		return false;
	};

	/**
	 * Является ли данная страница редактирование кворка
	 */
	var _isEditKwork = function() {
		if (window.location.pathname === '/edit') {
			return true;
		}

		return false;
	};

	/**
	 * Инициализация на автосохранение черновиков
	 */
	var _initAutoSaveDraft = function() {
		if (!DRAFT_AUTOSAVE_INTERVAL || window.draftAutoSave) {
			return;
		}
		_logs('Черновик: Инициализация автосохранения');

		var draftId = $('#draft_id').val();
		var timeout = 200;

		setTimeout(function() {
			if (window.draftDataForCompare === undefined) {
				window.draftDataForCompare = _getFormDataForDraft();
			}
		}, timeout);

		window.draftAutoSave = setInterval(function() {
			// Открыто окно редактирования портфолио
			if ($('.portfolio-upload-modal').is(':visible')) {
				// Сохранять изменения портфолио
				var $btnSavePortfolio = $('.js-save-portfolio');

				if ($btnSavePortfolio.hasClass('btn_disabled') === false) {
					var _btnSavePortfolioDraft = 'js-save-portfolio-for-draft';

					$btnSavePortfolio.addClass(_btnSavePortfolioDraft);
					$btnSavePortfolio.click();
					$btnSavePortfolio.removeClass(_btnSavePortfolioDraft);
				}
			}

			var currentDraftData = _getFormDataForDraft();

			if (window.draftDataForCompare !== undefined && new URLSearchParams(window.draftDataForCompare).toString() !== new URLSearchParams(currentDraftData).toString()) {
				_saveDraft(currentDraftData);
				window.draftDataForCompare = currentDraftData;
			}
		}, DRAFT_AUTOSAVE_INTERVAL * 1000);
	};
	/**
	 * Сохраняем черновик
	 */
	var _saveDraft = function (formData) {
		var $saveButton = jQuery(_selectors.saveButton);
		// Если в данный момент сохраняется кворк, то черновик не сохраняем
		if ($saveButton.data('loading')) {
			return;
		}

		_logs('Черновик: Отправлен запрос на сохранение');

		formData = formData ? formData : _getFormDataForDraft();
		var allowSave = false;
		var saveButton = $(_selectors.saveButton);
		if (!$(_selectors.saveButton).hasClass("btn_disabled")) {
			allowSave = true;
			saveButton.prop('disabled', true).addClass('btn_disabled');
			$(".preloader_kwork.dib").css("display", "inline-block");
		}
		$.ajax({
			type: "POST",
			url: _options.draft.saveUrl,
			data: formData,
			success: function (response) {
				if (response.success) {
					if (allowSave) {
						saveButton.prop('disabled', false).removeClass('btn_disabled');
						$(".preloader_kwork.dib").css("display", "none");
					}
					_logs('Черновик: Сохранен');

					window.draftId = response.data.draftId;
					$("#draft_id").val(response.data.draftId);
					_kworkId = response.data.draftId;

					if (response.data && response.data.draftId) {
						var url = "?draft_id="+ response.data.draftId;
						var lang = $('#lang').val();
						if (lang === "en") {
							url = url + "&lang=" + lang;
						}
						$(".js-kwork-save-form").attr("action", url);
						history.replaceState(null, null, url);
					}
					if (response.data && response.data.portfolio && response.data.portfolio.length) {
						var responsePortfolioData = response.data.portfolio;

						// Для текущего ОТКРЫТОГО модального окна выставляем - id портфолио
						if ($('.portfolio-upload-modal').is(':visible') && responsePortfolioData.length) {
							var $modalData = $('.sortable-card-list[data-type="portfolios"]').data('sortableList').modal.portfolio;

							$.each(responsePortfolioData, function(index, _portfolioData) {
								if ($modalData.draftHash == _portfolioData.draftHash) {
									$modalData.id = _portfolioData.id || null;
								}
							});
						}

						// Для всех остальных
						$.each(responsePortfolioData, function(index, _portfolioData) {
							$.each(_portfolioList.items, function(k, _portfolio) {
								if (_portfolioData.draftHash == _portfolio.data.draftHash && !_portfolio.data.id) {
									_portfolio.data.id = _portfolioData.id;
								}
							});
						});
					}
					if (response.data && response.data.translates && response.data.translates.length) {
						$.each(response.data.translates, function(k, v) {
							$('select[name="translate[' + v.front_id + '][from]"]').attr('name', 'translate[' + v.id + '][from]');
							$('select[name="translate[' + v.front_id + '][to]"]').attr('name', 'translate[' + v.id + '][to]');
							$('[name="translate[' + v.front_id + '][id]"]').val(v.id);
							$('[name="translate[' + v.front_id + '][id]"]').attr('name', 'translate[' + v.id + '][id]');
							$('[name="translate[' + v.front_id + '][state]"]').val('');
							$('[name="translate[' + v.front_id + '][state]"]').attr('name', 'translate[' + v.id + '][state]');
							$('[name="translate[' + v.front_id + '][kwork-id]"]').val(v.kwork_id);
							$('[name="translate[' + v.front_id + '][kwork-id]"]').attr('name', 'translate[' + v.id + '][kwork-id]');
						});
					}
					if (response.data && response.data.customExtras && response.data.customExtras.length) {
						$.each(response.data.customExtras, function(k, v) {
							$('[name="bundle_custom_extra_name[' + v.front_id + ']"]').attr('name', 'bundle_custom_extra_name[' + v.id + ']');
							$('[name="bundle_custom_extra_type[' + v.front_id + ']"]').attr('name', 'bundle_custom_extra_type[' + v.id + ']');
							$('[name="bundle_custom_extra_hint[' + v.front_id + ']"]').attr('name', 'bundle_custom_extra_hint[' + v.id + ']');
							$('[name="bundle_extra_standard_value[custom][' + v.front_id + ']"]').attr('name', 'bundle_extra_standard_value[custom][' + v.id + ']');
							$('[name="bundle_extra_medium_value[custom][' + v.front_id + ']"]').attr('name', 'bundle_extra_medium_value[custom][' + v.id + ']');
							$('[name="bundle_extra_premium_value[custom][' + v.front_id + ']"]').attr('name', 'bundle_extra_premium_value[custom][' + v.id + ']');
							var bundleItemCustomWrap = document.getElementsByClassName('extra-id-n1');
							if (bundleItemCustomWrap.length) {
								$(bundleItemCustomWrap[0]).removeClass('extra-id-n1').addClass('extra-id-' + v.id);
							}
							if (_options.customBundleExtras[v.front_id]) {
								delete _options.customBundleExtras[v.front_id];
								_options.customBundleExtras[v.id] = v.data;
							}
						});
					}

					var firstPhoto = typeof window.firstPhoto !== 'undefined' && !$.isEmptyObject(window.firstPhoto) ? JSON.parse(window.firstPhoto) : {};
					if (!$.isEmptyObject(firstPhoto)) {
						$('.kwork-preview-wrapper .file-wrapper-block-rectangle').css('background', 'url("' + JSON.parse(window.firstPhoto).data.src + '") 0% 0% / 100% no-repeat');
					}
				} else {
					_logs('Черновик: Произошла ошибка');
				}
			},
			error: function(jqXHR, exception) {
				if (exception === 'timeout') {
					_logs('Черновик: Превышено время ожидания ответа от сервера');
				} else {
					_logs('Черновик: Произошла ошибка');
				}
			},
			contentType: false,
			processData: false
		});
	}

	/**
	 * Получить данные формы создания кворка
	 */
	var _getFormDataForDraft = function() {
		var portfolioData = null;
		if (window.portfolioType === 'photo' || window.portfolioType === 'video') {
			if (!_portfolioList) {
				_portfolioList = $('.sortable-card-list[data-type="portfolios"]').data('sortableList');
			}
			var portfolioData = _portfolioList.getData();
		}

		// Временно скрываем сохраненное первое фото, чтобы отправить только его путь
		var $photoInputFirst = $('.js-photo__block .add-photo__file-wrapper .js-kwork-photo-input-first');
		var photoInputFirstName = $photoInputFirst.attr('name');
		if ($('[name="first_photo_path"]').val()) {
			$photoInputFirst.removeAttr('name');
		}

		// Временно скрываем сохраненные файлы, чтобы отправить только их id
		$('.js-portfolio-item-wrapper').each(function () {
			$(this).find('.js-photo__block .add-photo__file-wrapper').each(function () {
				var imageId = $(this).find('.js-portfolio-image-id').val();
				if (imageId) {
					var $fileInput = $(this).find('.js-file-input');
					var fileInputName = $fileInput.attr('name');
					$fileInput.removeAttr('name').data('attr-name', fileInputName);
				}
			});
		});

		var formData = new FormData($(_selectors.kworkForm)[0]);
		formData = formDataFilter(formData);

		if (portfolioData) {
			formData.append('portfolio', JSON.stringify(portfolioData));
		}

		var firstPhotoJson = window.firstPhoto ? window.firstPhoto : {};
		formData.append('first_photo_json', firstPhotoJson);
		if (firstPhotoJson.length > 0) {
			var firstPhotoArray = JSON.parse(firstPhotoJson);
			if (firstPhotoArray["data"]["src"]) {
				$('.js-kwork-save-form .js-kwork-photo-path-input').val(firstPhotoArray["data"]["src"]);
			}
		}


		// Восстанавливаем скрытые файлы
		if ($('[name="first_photo_path"]').val()) {
			$photoInputFirst.attr('name', photoInputFirstName);
			formData.append(photoInputFirstName, null);
		}
		$('.js-portfolio-item-wrapper').each(function () {
			$(this).find('.js-photo__block .add-photo__file-wrapper').each(function () {
				var imageId = $(this).find('.js-portfolio-image-id').val();
				if (imageId) {
					var $fileInput = $(this).find('.js-file-input');
					var fileInputName = $fileInput.data('attr-name');
					$fileInput.attr('name', fileInputName).removeData('attr-name');
					formData.append(fileInputName, null);
				}
			});
		});

		return formData;
	}

	/**
	 * Сохранение кворка
	 *
	 * @param {*} skipSpellChecking
	 */
	var _saveKwork = function (skipSpellChecking) {
		var thisButton = $(_selectors.saveButton);
		if (thisButton.prop('disabled') || (thisButton.data('loading') && !isCheckSpeller)) {
			return;
		}
		var errorStatus = false;
		$(_selectors.step).each(function () {
			var currentErrorStatus = !_checkAllStepFields($(this), true);
			if (currentErrorStatus && !errorStatus) {
				errorStatus = true;
			}
		});

		if (errorStatus) {
			_scrollOnTopError();
			return false;
		}

		var saveUrl = '';
		if (_saveMode == 'update') {
			saveUrl = '/edit?id=' + _kworkId;
		} else {
			saveUrl = '/new';
		}

		thisButton.data('loading', true).addClass('btn_disabled');

		var timerId = setInterval(function () {

			clearInterval(timerId);

			if (_formCache == $(_selectors.kworkForm).serialize() && typeof wasRejected !== 'undefined' && !wasRejected) {
				window.location.href = _kworkUrl;
				return;
			}

			var formData = new FormData($(_selectors.kworkForm)[0]);
			formData = formDataFilter(formData);

			if (_saveMode !== 'update') {
				formData.append('draft_id', window.draftId || null);
			}

			$(".preloader_kwork").show().find('.preloader_kwork_text').text(t('Сохранение...'));

			// Проверить поля формы на офрографические ошибки
			if (!skipSpellChecking) {
				// Если создается кворк на английском
				var lang = $('#lang').val();
				if (lang == "en") {
					window.bus.$emit("start-language-tool");
					isCheckSpeller = true;
					return;
				}
			}

			$.ajax({
				type: "POST",
				url: saveUrl,
				data: formData,
				dataType: "json",
				success: function (result) {
					if (result.result == 'success') {
						window.location.href = result.redirectUrl;
					} else {
						$(thisButton).data('loading', false).removeClass('btn_disabled');
						$(".preloader_kwork").hide();
						_showErrors(result.errors);
					}
				},
				contentType: false,
				processData: false
			});
		}, 200);

		return true;
	};

    var _onQuickClick = function () {
        if ($(this).is(':checked')) {
            $(this).val(1);
        } else {
            $(this).val(0);
        }
    };

	/**
	 * Вывод ошибок
	 */
	var _showErrors = function(errors) {
		var portfolioErrors = [];
		var text = '';
		for (var i = 0; i < errors.length; i++) {
			if(errors[i].target == 'portfolio') {
				portfolioErrors.push(errors[i]);
				continue;
			}

			var $input = $(".kwork-save-step__field-input[name='" + errors[i].target + "']");
			var $fieldBlock = $input.closest(_selectors.field.block);

			//Очищаем инпут и ставим отметку, что он был очищен.
			if (errors[i].clear) {
				$input.val("");

				var clearedSign = $input.parent().find('.cleared-input-on-errors');
				if (!clearedSign.length) {
					var sign = $('<input class="cleared-input-on-errors" type="hidden" name="' + errors[i].target + '_cleared" value="true" />');
					sign.appendTo($input.parent());
				}
			}

			if ($fieldBlock.length && $fieldBlock.is(':visible')) {
				that.validator.setErrorText($fieldBlock, errors[i].text);

				// заменяем исходный текст на текст с выделенными ошибками
				if (errors[i].mistakes) {
					var editor;
					if ($input.hasClass("js-content-storage")) {
						editor = $input.siblings(_selectors.content_editor)[0];
					} else {
						editor = $input.siblings(".trumbowyg-editor")[0];
					}
					var html = $(editor).html();
					if (html !== false) {
						window.stopRecursion = true;
						html = applyWordErrors(html, errors[i].mistakes);
						$(editor).html(html);
						window.stopRecursion = false;
					}
				}
			} else {
				text += '<li ' + (errors[i].class ? 'class="' + errors[i].class + '"' : '') + '>' + errors[i].text + '</li>';
			}
		}
		if (_portfolioList) {
			_portfolioList.applyErrors(portfolioErrors);
		}

		if (text !== '') {
			text = '<ul class="kwork-form-errors-list">' + text + '</ul>';
			$('.fox_error').removeClass('hidden').find('.fox_error_content').html(text);
			$('html, body').animate({
				scrollTop: 0
			}, 200);
		} else {
			_scrollOnTopError();
		}
	};

	// Задержка на вывод ошибки
	window.timeoutShowError = '';

	/**
	 * Скролим страницу к первой ошибке
	 * (скролим с задержкой, для устранения передергивания страницы, если за короткое
	 * время функция была вызвана несколько раз, отработает последный вызов)
	 */
	var _scrollOnTopError = function () {
		var $firstError = $('.kwork-save-step__field-value_error').first();

		if ($firstError.length) {
			clearTimeout(window.timeoutShowError)

			window.timeoutShowError = setTimeout(function() {
				if ($firstError.is('.js-add-bundle-extra-price-error')) {
					$firstError = $('.js-free-prices');
				}
				$('html, body').animate({
					scrollTop: $firstError.offset().top - 100
				}, 200);
			}, 300);
		}
	};

	var _onInputEditor = function (e) {
		var $contentStorage = $(this).siblings(_selectors.content_storage);
		var inputString = $(this).html();
		
		var max = $contentStorage.data("max");

		var string = inputString;
		string = string.replace(/(?!<\/?(word-error)>)<\/?[\s\w="-:&;?]+>/gi, '');   // удаляем все теги кроме word-error
		string = string.replace(/( |&nbsp;)( |&nbsp;)+/g, ' ');  // Вырезаем множественные пробелы

		var needReplace = (string != inputString);
	
		var oldValue = $contentStorage.val();
		var max = $contentStorage.data("max");
		var editorLength = _getTextLengthWithoutTags(string);
		var diff = editorLength - _getTextLengthWithoutTags(oldValue);
		
		var returnAfterReplace = false;
		var newString = null;
		if (max && (editorLength > max) && (diff >= 0)) {
			returnAfterReplace = true;
			newString = oldValue;
		} else if (needReplace) {
			newString = string;
		}

		// если для поля задано максимальное значение и оно превышено не даём вводить и вставлять текст
		if (newString) {
			var strDiff = _getTextLengthWithoutTags(inputString, true) - _getTextLengthWithoutTags(newString, true);
			var selection = saveSelection($(this)[0]);
			selection.end = selection.end - strDiff;
			selection.start = selection.start - strDiff;
			$(this).html(newString);
			restoreSelection($(this)[0], selection);
		}
		if (!newString) {
			newString = string;
		}
		$contentStorage.val(newString);

		if (returnAfterReplace) {
			return;
		}
		_setCurrentInputSymbolCount($contentStorage);
		that.validator.hideError($contentStorage);

		that.validator.planCheckText($contentStorage);

		// проверяем опции на дубли
		if($contentStorage.hasClass("js-add-extra-row__name-input")) {
			that.validator.checkOptionDuplicate($contentStorage);
		}

		// Получаем подсказки категории
		if ($contentStorage.hasClass('kwork-save-step__field-input_name')) {
			clearTimeout(_timerCategoryHints);
			var title = string.replace(/<\/?(word-error)>/gi, '');
			_timerCategoryHints = setTimeout(_getCategoryHints, 800, title);
		}
	};

	var _onChangeInput = function () {
		var string = $(this).val();
		$(this).val(_removeTagsExceptAllowed(string));

		_setCurrentInputSymbolCount($(this));
		$('.js-add-bundle-extra-count-error').addClass('hidden');
		that.validator.hideError($(this));
		if($(this).data('only-english')) {
			that.validator.checkField($(this), true);
		}

		that.validator.planCheckText($(this));
	};

	var _onBlurInput = function () {
		var $input = $(this);
		_setCurrentInputSymbolCount($input);
		if(!$input.parent().is('.trumbowyg-box'))
			that.validator.hideError($input, false);
	};

	var _onChangeDescription = function () {
		var $this = $(this);
		var string = $this.val();

		var error = false;
		if (!that.validator.checkMaxInput($this)) {
			error = true;
		}

		$this.closest('.bundle-item__field').toggleClass('bundle-item__field_error', error);

		var $error = $('.js-add-bundle-description-error');
		var errors = $error.data('errors') || {};
		var type = $this.closest('.js-bundle-item').data('type');
		errors[type] = error;
		$error.data('errors', errors);

		var showError = false;
		for (var key in errors) {
			if (errors[key]) {
				showError = true;
			}
		}

		$error.toggleClass('hidden', !showError);
	};

	var _onAddCustomAttribute = function () {
		var list = $(this).closest('.attribute-list'),
			addBtn = list.find(_selectors.attribute.addCustomAttribute.btn),
			parentId = list.data('parent-id'),
			allowMultiple = list.data('allow-multiple'),
			inputType = (allowMultiple) ? 'checkbox' : 'radio',
			inputClass = (allowMultiple) ? 'styled-checkbox' : 'styled-radio',
			inputName = list.data('input-name'),
			customNextId = $(this).data('custom-count'),
			textInputName = 'new_custom_attribute_title[' + parentId + '][' + customNextId + ']',
			inputId = 'attribute_' + parentId + '_item_custom_' + customNextId;
		$('<div/>', {class: 'attribute-item-custom'})
			.append(
				$('<input/>', {type: inputType, class: inputClass, name: inputName, id: inputId, value: customNextId}),
				$('<label/>', {for: inputId}).append(
					$('<input/>', {type: 'text', name: textInputName, 'data-check-bad-words': 'true', class: 'new_custom_attribute', id:'new_custom_attribute_input_'+customNextId}),
					$('<a/>', {href: '#', onclick: 'return false;', class: 'js-remove-custom-attribute-btn', html: '&times;'})
				),
				$('<div/>', {class: 'js-kwork-save-field-error-custom kwork-save-step__field-error hidden mb0'})
			)
			.insertBefore(this).on('change', 'input[type="text"]',_onChangeAttributeItem);
		$(this).data('custom-count', ++customNextId);

		var customCount = list.find('.attribute-item-custom').length,
			customMaxCount = list.data('custom-max-count');
		if (customCount < customMaxCount) {
			addBtn.show();
		} else {
			addBtn.hide();
		}
		$(_selectors.field.checkBox).on('change', _changeClassification);
	};

	var _onRemoveCustomAttribute = function () {
		var list = $(this).closest('.attribute-list'),
			addBtn = list.find(_selectors.attribute.addCustomAttribute.btn);
		$(this).closest('.attribute-item-custom').remove();
		var customCount = list.find('.attribute-item-custom').length,
			customMaxCount = list.data('custom-max-count');
		if (customCount < customMaxCount) {
			addBtn.show();
		} else {
			addBtn.hide();
		}
		$(_selectors.field.checkBox).on('change', _changeClassification);
	};

	/**
	 * @deprecated
	 * @private
	 */
	var _setSelectChecked = function () {
		if (($(this).val() ^ 0) > 0) {
			$(this).closest('.bundle-item__field').addClass('bundle-item__field_checked');
		} else {
			$(this).closest('.bundle-item__field').removeClass('bundle-item__field_checked');
		}
	};

	var _setCurrentInputSymbolCount = function ($input) {
		var current;
		if ($input.data("hasTags") === true) {
			current = _getTextLengthWithoutTags($input.val());
		} else {
			current = _getTextLength($input.val());
		}


		var text = '';

		if ($input.data('min') && $input.data('max')) {
			if ($input.data('min')) {
				if (current < $input.data('min') ^ 0) {
					if (current > 0) {
						text = current + t(' из ');
					}
					text += t('{{0}} минимум', [$input.data('min')]);
				}
			}
			if ($input.data('max')) {
				if (text == '') {
					if (current > 0) {
						text = t('{{0}} из', [current]) + ' ';
					}
					text += t('{{0}} максимум', [$input.data('max')]);
				}
			}
		} else {
			if ($input.data('min')) {
				if (current > 0) {
					text = t('{{0}} из', [current]) + ' ';
				}
				text += t('{{0}} минимум', [$input.data('min')]);
			} else if ($input.data('max')) {
				if (current > 0) {
					text = t('{{0}} из', [current]) + ' ';
				}
				text += t('{{0}} максимум', [$input.data('max')]);
			}
		}

		// disable lang buttons when title is filled
		if($input.attr('id') == 'step1-name') {
			if(current > 0) {
				$('.tab').not('.selected').find('.tab_c').addClass('disabled');
				$('.tab .tab_c a').addClass('disabled');
			} else {
				$('.tab').not('.selected').find('.tab_c').removeClass('disabled');
				$('.tab .tab_c a').removeClass('disabled');
			}
		}

		if ($input.data('min') || $input.data('max')) {
			var $hintCurrent = $input.closest(_selectors.field.block).find(_selectors.field.hint);
			$hintCurrent.text(text);

			if (that.validator.checkMinInput($input) && that.validator.checkMaxInput($input)) {
				$hintCurrent.removeClass('kwork-save-step__field-hint_error');
			} else {
				$hintCurrent.addClass('kwork-save-step__field-hint_error');
			}
		}
	};

	var _getTextFromHtml = function (html) {
		return $('<div>' + html + '</div>').text();
	};

	var _getTextLength = function (html, includeSpaces)
	{
		var html = html.replace(/&nbsp;/gi, " ");
		if (!includeSpaces) {
			html = html.replace(/\s\s+/g, " ");
		}
		return html.length ^ 0;
	};

	var _getTextLengthWithoutSpace = function (html)
	{
		return html.replace(/&nbsp;/gi, " ").replace(/\s/g, "").length ^ 0;
	};

	/*
	 * Получить длину строки без тегов
	 * @param {string} html
	 * @param {bool} includeSpaces
	 * @returns {Number}
	 */
	var _getTextLengthWithoutTags = function (html, includeSpaces)
	{
		var pattern = _getPatternTags();
		return _getTextLength(html.replace(pattern, ""), includeSpaces);
	};

	/**
	 * Получить паттерн из тегов для замены
	 * @returns {RegExp}
	 */
	var _getPatternTags = function ()
	{
		return /<\/?[^>]*>/gi;
	};

	/**
	 * Удалить из строки все теги кроме разрешенных
	 */
	var _removeTagsExceptAllowed = function (string) {
		string.replace(/<(\w+)[^>]+>/i, "<$1>");
		string = string.replace(/(?!<\/?(word-error|p|br|strong|em|ol|li)>)<\/?[\s\w="-:&;?]+>/gi, "");
		return string;
	};

	/**
	 * Заменяет &nbsp; на простой пробел, и удаляет дубли пробелов
	 */
	var _removeSpaces = function(string) {
		return string.replace(/&[a-z]+;/gi, " ").replace(/\s+/gi, " ").trim();
	};

	var _removeLinks = function (string) {
		return string.replace(/(?:(https?|ftp):\/\/(?:[^\s<\[]+(?:\[(?!\/URL\]))?)*)/gi, "");
	};

	var _changeBundleSize = function () {
		var tabSelectors = '.js-bundle-medium .kwork-save-step__field-input, .js-bundle-premium .kwork-save-step__field-input';
		var selectsSelector = '.js-bundle-medium select:not(.free-category-item__input_select), .js-bundle-premium select:not(.free-category-item__input_select)';
		if ($(this).prop('checked')) {
			$('#bundle-standard-description').data('required', true);
			$(_selectors.bundleStep).removeClass('kwork-save-step_type_single').addClass('kwork-save-step_type_bundle');
			$(tabSelectors).removeAttr('tabindex');
			$(selectsSelector).each(function () {
				$(this).prop('disabled', false).trigger('chosen:updated');
			});
			$('.js-bundle-volume-cell').attr('colspan', 3);
			$(_selectors.overlay).hide();
			
			// Показываем диапазон цен для 3 пакетного кворка
			$('#min_volume_price option:not(.standard_price_option)').addClass('hide');					
			// Если выбранный (принимаю заказы от) недоступен, сбраываем значение
			$('#min_volume_price option:selected.hide').prop("selected", false);
		} else {
			$('#bundle-standard-description').data('required', false);
			$(_selectors.bundleStep).removeClass('kwork-save-step_type_bundle').addClass('kwork-save-step_type_single');

			$('.js-bundle-medium .js-bundle-tooltip-error, .js-bundle-premium .js-bundle-tooltip-error, #bundle-standard-description').each(function () {
				that.validator.hideError($(this));
			});
			$(tabSelectors).attr('tabindex', '-1');
			$(selectsSelector).each(function () {
				$(this).prop('disabled', true).trigger('chosen:updated');
			});
			$('.js-bundle-volume-cell').attr('colspan', 1);
			$(_selectors.overlay).show();
			
			// Показываем диапазон цен для 1 пакетного кворка
			$('#min_volume_price option').removeClass('hide');	
		}
		
		// Обновляем диапазон цен
		$('#min_volume_price').trigger('chosen:updated').trigger('change');

		// Проверка цен пакетов и показ или скрытие ошибок
		_togglePriceRangeError($(this).prop('checked') ? undefined : false);

		_singlePackageToggle();
	};

	var _onChangeAttributeItem = function () {
		that.validator.checkField($(this), true);
		that.validator.planCheckText($(this));
	};

	/**
	 * XHR запроса получения классификаций
	 * @private
	 */
	var _xhrLoadClassifications = null;

	/**
	 * XHR запроса получения свободных цен
	 * @private
	 */
	var _xhrLoadFreePrices = null;

	/**
	 * Event on changing category
	 * @private
	 */
	var _onChangeCategory = function (event, params) {

		var init = false,
			options = {};

		if(typeof params !== "undefined") {
			init = params.init;
			options = Object.assign(options, params.options)
		}

		if (_xhrLoadClassifications) {
			_xhrLoadClassifications.abort();
		}
		if (_xhrLoadFreePrices) {
			_xhrLoadFreePrices.abort();
		}

		var parentId =  $(this).categoryList('parentId');
		var categoryId = $(this).categoryList('categoryId');

		_toggleVolumeType();

		_setMaxDuration($(this).categoryList('data', 'maxDays'), categoryId);
		_setBundleOnChangeCategory(categoryId);

		_toggleTranslates(categoryId);
		_changePortfolioCategoryId(categoryId);

		that.validator.planCheckText(false);

		$('.js-first-photo-block').toggle(!!parentId);

		if(init) {
			_localLoadClassifications(options)
		} else {
			_loadClassification('','');
		}
	};

	var _updatePortfolioBlock = function() {
		var categories = $(_selectors.category);
		var maxPhotoCount = categories.categoryList('data', 'maxPhotoCount');
		var portfolioType = _GetFinalAttributesPortfolioType(categories);

		if (window.portfolioList) {
			window.portfolioList.maxCount = maxPhotoCount;
		}

		if (_isTrueInCheckedAttributes('demoFileUpload')) {
			portfolioType = 'demoFile';
			$('.js-step-4').attr('data-type', 'file');
        } else {
			$('.js-step-4').attr('data-type', 'portfolio');
        }

		_portfolioTplEvent(maxPhotoCount, portfolioType);
		_portfolioTrigger(portfolioType);

		var canUpload = ['photo', 'video'].indexOf(portfolioType) === -1 || maxPhotoCount < 7;
		_files.description.canUpload(canUpload);
		var labelText = canUpload ? t('Описание и файлы') : t('Описание');
		$('label[for="step1-description"]').text(labelText);
	};

	/**
	 * Отображение/скрытие блока "Портфолио"
	 * в зависимости от типа категории
	 * @param {string} portfolioType Тип портфолио для категории
	 */
	var _portfolioTrigger = function(portfolioType) {
		// step 3
		var $step_3 = jQuery('.js-step[data-step="3"]');
		var $step_3_footer = $step_3.find('.card__footer');
		var step_3_isInactive = $step_3.hasClass('kwork-save-step_inactive');
		// step 4
		var $step_4 = jQuery('.js-step[data-step="4"]');
		var $step_4_footer = $step_4.find('.card__footer');
		var $step_4_content = $step_4.find('.card__content');

		if (!portfolioType || portfolioType == 'none') {
			// Скрываем 4 этап
			$step_4_content.hide();
			if (!step_3_isInactive) {
				$step_4.removeClass('kwork-save-step_inactive');
				$step_4_footer.show();
			}

			$step_3_footer.hide();
		} else {
			if (step_3_isInactive) {
				$step_4.addClass('kwork-save-step_inactive');
				$step_3_footer.css({
					height: ''
				}).find('.js-next-step-btn').css({
					visibility: 'visible'
				});
			} else {
				$step_4.removeClass('kwork-save-step_inactive');
				$step_3_footer.css({
					height: '1px'
				}).find('.js-next-step-btn').css({
					visibility: 'hidden'
				});
			}

			$step_3_footer.show();
			$step_4_content.show();

			// Переинициализация грида для портфолио
			var $portfolioList = $('.sortable-card-list[data-type="portfolios"]').data('sortableList');
			$portfolioList.updateOffsets();
			$portfolioList.updateAllBlockCoordinates();
		}
	};

	// Разное отображение текстовок и проверок
	// в зависимости от категории
	var _portfolioTplEvent = function(maxPhotoCount, portfolioType) {
		window.portfolioType = portfolioType;
		if(window.portfolioList) {
			window.portfolioList.modal.portfolioType = portfolioType;
		}

		// Отображение текст: "Загрузите крупные изображения..."
		var $onlyPhoto = jQuery('.js-portfolio-only-photo');
		if (portfolioType == 'photo') {
			$onlyPhoto.removeClass('hidden');
		} else {
			$onlyPhoto.addClass('hidden');
		}
	};

	// Локальная загрузка классификаций
	// Используется для первой отрисовки
	var _localLoadClassifications = function(options) {
		var attributes = JSON.parse(options.kworkAttributes);
		var categoryId = options.kworkCategoryId;
		var prices = options.pricesByAttributes.prices;

		for(var index in attributes) {
			var attributeId = attributes[index];

			if (prices) {
				PackagePrices.setFreeCategoryPrices(prices, attributeId);
				$(".js-step-2 .kwork-save-step__container").addClass("free-price-step");
				setFreeTooltip();
				window.isFreePrice = true;
			} else {
				_hideFreePrice();
				window.isFreePrice = false;
				PackagePrices.setCategoryPrices(categoryId);
			}

			_updatePortfolioBlock();
			_bindAttributes();
			_setMaxDuration($('#step2-work-time').data('onload-max-days'));

			_setBundleOnChangeCategory(categoryId);
			_changeClassification();

			$(document).trigger('classification:loaded', [categoryId, attributeId]);
		}
	};

	var _loadClassification = function (attributeId, parentId, multiple) {
		multiple = multiple || false;

		var categoryId = $(_selectors.category).categoryList('categoryId');
		if (!attributeId && !parentId) {
			var $attributeBlock = $('.js-attribute-section-block').html('');
		}

		if (categoryId) {
			var $attributeBlock = $('.js-attribute-section-block[data-id="' + parentId + '"]');
			if (!multiple) {
				$attributeBlock.empty();
			} else {
				$attributeBlock.find('.js-field-block[data-parent-id="' + attributeId + '"]').remove();
			}
			_xhrLoadClassifications = $.get('/api/attribute/loadclassification',
				{
					categoryId: categoryId,
					lang: $('#lang').val(),
					attributeId: attributeId,
					kworkId: _kworkId
				},
				function (response) {
					if (response.success) {
						if(!attributeId && response.html === "") {
							_setFreePricesByCategory(categoryId);
						} else {
							_setFreePricesByAttribute(attributeId);
						}
						if (!multiple) {
							$attributeBlock.html(response.html);
						} else {
							$attributeBlock.append(response.html);
						}
						_updatePortfolioBlock();
						_bindAttributes();
						var $attributeBlockChecked = $attributeBlock.find(_selectors.attribute.bindableAttribute + ':checked');
						attributeShowAlert[$attributeBlockChecked.attr('id')] = 1;
						$attributeBlockChecked.change();
						
						
						var $classificationBlockChecked = $attributeBlock.find(_selectors.attribute.classificationCheckbox + ':checked');
						attributeShowAlert[$classificationBlockChecked.attr('id')] = 1;
						$classificationBlockChecked.change();

						_setBundleOnChangeCategory(categoryId);
						_changeClassification();

						$(document).trigger('classification:loaded', [categoryId, attributeId]);
					}
				},
				'json');
		}
	};

	/**
	 * Имеет ли текущаяя категория и классификация пакетные опции
	 *
	 * @returns {boolean}
	 * @private
	 */
	var _isCurrentCategoryAndAttributeHasBundleOptions = function() {
		var categoryId = _getCurrentCategory();
		var attributesIds = _getCurrentAttributeIds();

		return _isCategoryAndAttributeHasBundleOptions(categoryId, attributesIds);
	};

	/**
	 * Имеет ли категория и атрибуты пакетные опции
	 *
	 * @param categoryId
	 * @param attributesIds
	 * @returns {boolean}
	 * @private
	 */
	var _isCategoryAndAttributeHasBundleOptions = function (categoryId, attributesIds) {
		if (_options.categoryExtras.hasOwnProperty(categoryId)) {
			for (var i  in _options.categoryExtras[categoryId]) {
				if (_options.categoryExtras[categoryId][i].attributes_ids.length === 0) {
					// если есть опции привязанные к категории а не к атрибутам
					return true
				} else if (attributesIds && attributesIds.length && _options.categoryExtras[categoryId][i].attributes_ids.length > 0) {
					// Если есть опции привязанные к одному из выбранных атрибутов
					for (var j in attributesIds) {
						if (_options.categoryExtras[categoryId][i].attributes_ids.indexOf(attributesIds[j] ^ 0) !== -1) {
							return true;
						}
					}
				}
			}
		}
		return false;
	};

	/**
	 * Получение текущей категории
	 *
	 * @returns {string}
	 * @private
	 */
	var _getCurrentCategory = function () {
		return $(_selectors.category).categoryList('categoryId');
	};

	/**
	 * Получение массива идентификаторов выбранных атрибутов
	 *
	 * @returns {Array}
	 * @private
	 */
	var _getCurrentAttributeIds = function() {
		var attributesIds = [];
		$('.attribute-list input:checked').map(function (index, el) {
			if ($(el).val()) {
				attributesIds.push($(el).val());
			}
		});

		return attributesIds;
	};

	var _removeNeedUpdateNotice = function ()
	{
		var $attributes = $('.kwork-attribute-need-update');
		$attributes.removeClass('kwork-attribute-need-update kwork-save-step__field-value_tooltip');
		$('.field-tooltip_attributes').remove();
	};

	 /**
	 * Поиск первого атрибута среди отмеченных у которого отмечен числовой объем кворка
	 *
	 * @returns {string|boolean}
	 * @private
	 */
	var _firstVolumeTypeIdAttribute = function () {
		var finded = null;

		$('.attribute-list input:checked').each(function (index, el) {
			var input = $(el);
			if (input.data("volumeTypeId")) {
				finded = input;
			}
		});

		return finded;
	};

	/**
	 * Проверка нужно ли включить числовой объем кворка или текстовый
	 *
	 * @private
	 */
	var _toggleVolumeType = function () {
		var categoryId = parseInt($(_selectors.category).categoryList('categoryId'));
		var requiredVolumeTypeId = CategoriesVolumeTypesModule.getRequiredVolumeType(categoryId);
		var additionalVolumesTypesIds = CategoriesVolumeTypesModule.getAdditionalVolumeTypes(categoryId);
		if (!requiredVolumeTypeId) {
			var volumeTypeAttribute = _firstVolumeTypeIdAttribute();
			if (volumeTypeAttribute && volumeTypeAttribute.length) {
				requiredVolumeTypeId = volumeTypeAttribute.data("volumeTypeId");
				additionalVolumesTypesIds = volumeTypeAttribute.data("additionalVolumeTypesIds");
			}
		}

		//Менялась ли категория из пакетной в простую, без пакетных опций, если да, то сбрасываем объем
		var changePackageToSimple = false;
		var changeSimpleToPackage = false;
		if (_kworkCategoryId && _kworkCategoryId != categoryId) {
			changePackageToSimple
				= _isCategoryAndAttributeHasBundleOptions(_kworkCategoryId, _kworkAttributes)
				&& !_isCategoryAndAttributeHasBundleOptions(categoryId, _getCurrentAttributeIds())
			;
		}

		if (_kworkCategoryId && _kworkCategoryId != categoryId) {
			changeSimpleToPackage
				= !_isCategoryAndAttributeHasBundleOptions(_kworkCategoryId, _kworkAttributes)
				&& _isCategoryAndAttributeHasBundleOptions(categoryId, _getCurrentAttributeIds())
			;
		}

		if (_options.simpleKworkVolumeTypeInput) {
			if (changePackageToSimple || changeSimpleToPackage) {
				_options.simpleKworkVolumeTypeInput.setDropVolume();
			}
			_options.simpleKworkVolumeTypeInput.setRequiredVolumeTypeId(requiredVolumeTypeId);
			_options.simpleKworkVolumeTypeInput.setAllowedAdditionalVolumeTypesIds(additionalVolumesTypesIds);
			_options.simpleKworkVolumeTypeInput.setToggle();
		}

		if (_options.packageKworkVolumeTypeInput) {
			if (changePackageToSimple || changeSimpleToPackage) {
				_options.packageKworkVolumeTypeInput.setDropVolume();
			}
			_options.packageKworkVolumeTypeInput.setRequiredVolumeTypeId(requiredVolumeTypeId);
			_options.packageKworkVolumeTypeInput.setAllowedAdditionalVolumeTypesIds(additionalVolumesTypesIds);
			_options.packageKworkVolumeTypeInput.setToggle();
		}
	};

	/**
	 * Определение типа портфолио по выбранным атрибутам
	 * @private
	 */
	var _GetFinalAttributesPortfolioType = function (categories) {
		var portfolioType = categories.categoryList('data', 'portfolioType');
		$('.attribute-list input:checked').each(function (index, el) {
			var classificationPortfolioType = $(el).closest('.attribute-list').data('portfolioType');
			if (classificationPortfolioType && classificationPortfolioType !== 'default') {
				portfolioType = classificationPortfolioType;
			}
			var attributePortfolioType = $(el).data('portfolioType');
			if (attributePortfolioType && attributePortfolioType !== 'default') {
				portfolioType = attributePortfolioType;
			}
		});
		return portfolioType;
	};

	/**
	 * Определение есть ли true параметра в выбранных атрибутах
	 *
	 * @param dataKey Ключ подаваемый в $.data() - camelCase
	 * @returns {boolean}
	 * @private
	 */
	var _isTrueInCheckedAttributes = function (dataKey) {
		var need = false;
		$('.attribute-list input:checked').each(function (index, el) {
			if ($(el).data(dataKey)) {
				need = true;
			}
		});

		return need;
	};

	/**
	 * Показвать ли сообщение о необходимости загрузки примеров размещения
	 *
	 * @private
	 */
	var _toggleLinksExamples = function () {
		if (_isTrueInCheckedAttributes('isKworkLinksExampleImages')) {
			$('#need-kwork-links-examples-message').removeClass('hidden');
			$('#default-portfolio-message').addClass('hidden');
		} else {
			$('#need-kwork-links-examples-message').addClass('hidden');
			$('#default-portfolio-message').removeClass('hidden');
		}
	};

	/**
	 * Показвать ли блоки связанные с сайтами продвижения ссылок, продажей сайтов и доменов
	 *
	 * @private
	 */
	var _toggleLinksSites = function () {
		var kworkLinksSitesType = 0;
		$('.attribute-list input:checked').each(function (index, el) {
			if ($(el).data('isKworkLinksSites')) {
				kworkLinksSitesType = $(el).data('isKworkLinksSites');
			}
		});
		if (kworkLinksSitesType) {
			$('.kwork-links-sites-block').removeClass('hidden');
			$('#step1-links').data('required', true);
			switch(kworkLinksSitesType) {
				case 2:
					$('.kwork-links-sites-auditory').addClass('hidden');
					var label = t('Адреса доменов на продажу');
					var placeholder = t('Напишите список доменов на продажу');
					var help = t('Загрузите список доменов на продажу');
					break;
				case 3:
					$('.kwork-links-sites-auditory').addClass('hidden');
					label = t('Адреса сайтов на продажу');
					placeholder = t('Напишите список сайтов на продажу');
					help = t('Загрузите список сайтов на продажу');
					break;
				default:
					$('.kwork-links-sites-auditory').removeClass('hidden');
					label = t('Площадки для размещения ссылок');
					placeholder = t('Загрузите список площадок для размещения ссылок.');
					help = t('Загрузите список площадок для размещения ссылок.');
					break;
			}
			var $div = $('.kwork-links-sites-block');
			$('div label', $div).text(label);
			$('textarea', $div).prop('placeholder', placeholder);
			$('div.field-tooltip__title', $div).text(help);
		} else {
			$('.kwork-links-sites-auditory').addClass('hidden');
			$('.kwork-links-sites-block').addClass('hidden');
			$('#step1-links').data('required', false);
		}
	};

	var _bindAttributes = function() {
		$(_selectors.attribute.bindableAttribute).off('change');
		$(_selectors.attribute.bindableAttribute).on('change', function () {
			that.validator.hideError($(this));

			_removeNeedUpdateNotice();
			_toggleLinksExamples();
			_toggleLinksSites();
			_updatePortfolioBlock();
			_toggleVolumeType();
			var id = $(this).attr('id');
			if($(this).data('note-worker') && !attributeShowAlert[id]) {
				attributeShowAlert[id] = 1;
				show_popup("<div class='pt5'>" + $(this).data('note-worker').replace(/\[(\/?.)\]/g, "<$1>") + "</div>", '',false, 'popup--center');
			} 
			var attributeId = $(this).val();
			_setMaxDuration($(this).data('max-days') || $(_selectors.category).categoryList('data', 'maxDays'), $(this).data('parentId'));
			_loadClassification(attributeId, $(this).data('parentId'));
			//Меняем текст хинта для некоторых аттрибутов в демо-отчете
			$(".demo-file-text").addClass("hidden");
			var attributeDemoText = ".demo-file-text.attribute" + attributeId;
			if($(attributeDemoText).length > 0){
				$(attributeDemoText).removeClass("hidden");
			}else{
				$(".demo-file-text.default").removeClass("hidden");
			}
			//{'Проведите юзабилити-аудит произвольного приложения и загрузите отчет по его аудиту в качестве примера. Покупатели смогут просмотреть их в крупном размере.<br> Не используйте изображения, принадлежащие другим исполнителям или используемые в других кворках.'|t}

		});
		_toggleLinksExamples();
		_toggleLinksSites();
		$(_selectors.field.checkBox).on('change', _changeClassification);
		_toggleVolumeType();
	};

	var _changeClassification = function () {
		$(_selectors.field.checkBox).each(function(){
			var checkedSize = $(this).find('input[type="checkbox"]:checked').size(),
				addButton = $(this).find(_selectors.attribute.addCustomAttribute.btn),
				customMaxCount = $(this).data('custom-max-count'),
				customCount = $(this).find('.attribute-item-custom').length,
				multipleMaxCount = $(this).data('multiple-max-count');
			if(multipleMaxCount > 0){
				if (checkedSize >= multipleMaxCount){
					$(this).find('input[type="checkbox"]').not(':checked').prop('disabled', true).parent().css('opacity', '0.5');
					addButton.hide();
				}else {
					$(this).find('input[type="checkbox"]').not(':checked').prop('disabled', false).parent().css('opacity', '1');
					if(customCount < customMaxCount)
						addButton.show();
				}
			}
		});
	}

	var _checkClassification = function () {
		var result = 0;
		$(_selectors.field.checkBox).each(function(){
			var checkedSize = $(this).find('input[type="checkbox"]:checked').size(),
				multipleMaxCount = $(this).data('multiple-max-count');
			if(multipleMaxCount > 0 && checkedSize > multipleMaxCount) {
				result = multipleMaxCount;
				return false;
			}
		});
		return result;
	}

	var _toggleTranslates = function (categoryId) {
		var translateSelector = "input.translate-id, input.translate-state, input.kwork-id, select.translateFrom, select.translateTo";

		if (categoryId == 35 || categoryId == 152) {
			$("#translate_pairs").removeClass("hidden");
			$("#translate_pairs").find(translateSelector).prop('disabled', false);
			$("#translate_pairs .translates").addClass("js-field-translate");
		} else {
			$("#translate_pairs").addClass("hidden").prop('disabled', true);
			$("#translate_pairs").find(translateSelector).prop('disabled', true);
			$("#translate_pairs .translates").removeClass("js-field-translate");
		}
	};

	//задаем id категории в блоке загрузки портфолио
	var _changePortfolioCategoryId = function (categoryId) {
		$('.js-portfolio-category_id').val(categoryId);
	};

	var _setFreePricesByAttribute = function(attributeId) {
		clearTimeout(window.setTimeoutFreePricesByAttribute);
		window.setTimeoutFreePricesByAttribute = setTimeout(function() {
			if (_xhrLoadFreePrices) {
				_xhrLoadFreePrices.abort();
			}

			let attrIds = {};
			$("input.js-bindable-attribute:checked, .js-multiple-attribute[data-is-custom!='1']:checked").each(function(i, el) {
				attrIds[$(el).val()] = $(el).val();
			});

			_xhrLoadFreePrices = $.get('/api/freeprice/attributegetprices',
				{
					attributeIds: attrIds,
					lang: $('#lang').val()
				},
				function(response) {
					if (response.prices) {
						PackagePrices.setFreeCategoryPrices(response.prices, attributeId);
						$(".js-step-2 .kwork-save-step__container").addClass("free-price-step");
						setFreeTooltip();
						window.isFreePrice = true;
					} else {
						_hideFreePrice();
						var categoryId = $(_selectors.category).categoryList('categoryId');
						window.isFreePrice = false;
						PackagePrices.setCategoryPrices(categoryId);
					}
				},
				'json'
			);
		}, 2000);
	};

	var _setFreePricesByCategory = function(categoryId) {
		if (_xhrLoadFreePrices) {
			_xhrLoadFreePrices.abort();
		}
		_xhrLoadFreePrices = $.get('/api/freeprice/categorygetprices',
			{
				categoryId: categoryId,
				lang: $('#lang').val()
			},
			function(response) {
				if (response.prices) {
					PackagePrices.setFreeCategoryPrices(response.prices);
					$(".js-step-2 .kwork-save-step__container").addClass("free-price-step");
					$(".js-step-2 .kwork-save-step__container").addClass("free-price-by-category");
					setFreeTooltip();
				} else {
					_hideFreePrice();
					$(".js-step-2 .kwork-save-step__container").removeClass("free-price-by-category");
				}
			},
			'json'
		);
	};

	var _hideFreePrice = function () {
		unbindFreeTooltip();
		$(".js-step-2 .kwork-save-step__container").removeClass("free-price-step");
		$(".kwork-save-step__kwork-price-free-value").addClass("hide");
		$(".kwork-save-step__kwork-price-free-value").find("option:selected").removeAttr("selected");
		$(".kwork-save-step__kwork-price-static-value").removeClass("hide");
	};

	var _setMaxDuration = function (days, categoryId) {
		days = parseInt(days);

		$('#step2-work-time, #bundle-standard-days, #bundle-medium-days, #bundle-premium-days').each(function () {
			var $select = $(this);
			var isBundlePremiumDays = ($select.prop('id') === 'bundle-premium-days');

			$select.find('option').each(function (index) {
				if (index < days) {
					$(this).removeClass('hidden');
				} else if ( // #6193 - Увеличить срок пакетов для рубрики Сайт под ключ [Бэк]
					isBundlePremiumDays &&
					$.inArray(categoryId ^ 0, [37, 154]) !== -1 &&
					$.inArray(index + 1, [11, 12, 13, 14, 15, 20, 25, 30]) !== -1
				) {
					$(this).removeClass('hidden');
				} else {
					$(this).addClass('hidden')
				}
			});

			if ($select.data('value') && $select.data('value') <= days) {
				$select.val($select.data('value'));
			} else if (parseInt(($select.val())) > days) {
				$select.val(days);
			}

			$select.trigger("chosen:updated");
		});
	};

	/**
	 * Для рубрик с настройками создания однопакетных кворков скрываем у продавцов возможность создавать 3 пакета в кворке
	 *
	 * @private
	 */
	var _isAllowedSinglePackage = function () {
		var attributesIds = _getCurrentAttributeIds();
		var categoryId = _getCurrentCategory();
		var singlePackages = _getSinglePackageFilters(categoryId);

		if (!singlePackages) {
			return false;
		}
		if (!attributesIds) {
			return true;
		}

		for (var i in attributesIds) {
			var attributeId = parseInt(attributesIds[i]);

			for (var p in singlePackages) {
				var singlePackage = singlePackages[p];

				if (singlePackage.attribute_ids.indexOf(attributeId) !== -1) {
					return true;
				}
			}
		}

		return false;
	};
	var _getSinglePackageFilters = function (categoryId) {
		if (!categoryId || categoryId === undefined) {
			return [];
		}
		var cached = _categorySinglePackageFilters[categoryId];
		if (cached) {
			return _categorySinglePackageFilters[categoryId];
		}
		_categorySinglePackageFilters[categoryId] = [];

		$.get('/package_filter/get_single_package_filters/' + categoryId,
			function (response) {
				if (!response.success) {
					return;
				}
				_categorySinglePackageFilters[categoryId] = response.items;
			}, 'json');

		return _categorySinglePackageFilters[categoryId];
	};
	var _singlePackageToggle = function () {
		var stepBundle = $('.js-step-bundle');
		var bundleSizeCheckbox = $('.bundle-size-checkbox');

		var singlePackageTooltip = $('.js-single-package-tooltip');
		var singlePackageTooltipImage = $('.js-single-package-tooltip-image');
		var singlePackageError = $('.js-single-package-error');

		var defaultPackageTooltip = singlePackageTooltip.prev('.js-default-package-tooltip');
		var defaultPackageTooltipImage = $('.js-default-package-tooltip-image');
		var defaultPackageError = singlePackageError.prev('.js-default-package-error');

		if (_isAllowedSinglePackage()) {
			stepBundle.addClass('kwork-save-step__single-package-only');
			bundleSizeCheckbox.prop('disabled', true);

			defaultPackageTooltip.addClass('hidden');
			defaultPackageTooltipImage.addClass('hidden');
			defaultPackageError.addClass('hidden');

			singlePackageTooltip.removeClass('hidden');
			singlePackageTooltipImage.removeClass('hidden');
			singlePackageError.removeClass('hidden');
		} else {
			stepBundle.removeClass('kwork-save-step__single-package-only');
			bundleSizeCheckbox.prop('disabled', false);

			defaultPackageTooltip.removeClass('hidden');
			defaultPackageTooltipImage.removeClass('hidden');
			defaultPackageError.removeClass('hidden');

			singlePackageTooltip.addClass('hidden');
			singlePackageTooltipImage.addClass('hidden');
			singlePackageError.addClass('hidden');
		}
	};

	var _setBundleOnChangeCategory = function (categoryId) {
		var type = stepType.SINGLE;
		if (_isCurrentCategoryAndAttributeHasBundleOptions()) {
			type = stepType.BUNDLE;
			PackagePrices.setCategoryPrices(categoryId);

			_singlePackageToggle();
		}

		_setSecondStep(type, categoryId);
	};

	var _onClickOverlay = function () {
		$(this).hide();
		if (!$(_selectors.bundleSize.checkbox).is(':checked')) {
			$(_selectors.bundleSize.checkbox).trigger('click');
		}
	};

	var _showLesson = function () {
		var content = $(_selectors.lessonPopupContainer).html();
		show_popup(content, _lessonPopupClass);
		_setLookedLesson();
	};

	var _setLookedLesson = function () {
		$.post('/api/user/setlookedlesson', {}, function () {}, 'json');
	};

	/**
	 * Check if category has bundles
	 *
	 * @param categoryId {string|integer}
	 * @returns {boolean}
	 * @private
	 */
	var _isBundleCategory = function (categoryId) {
		if (_options.categoryExtras.hasOwnProperty(categoryId)) {
			for (var i in _options.categoryExtras[categoryId]) {
				if (_options.categoryExtras[categoryId][i].attribute_id === null) {
					return true;
				}
			}
		}
		return false;
	};

	/**
	 * Set view of second step
	 * @param {string} type  Type of second step (stepType.SINGLE|stepType.BUNDLE)
	 * @param {int} categoryId Id of category
	 * @private
	 */
	var _setSecondStep = function (type, categoryId) {
		if (type == stepType.BUNDLE) {
			$(_selectors.bundleSize.block).removeClass('kwork-save-step__bundle-size_inactive');
			$(_selectors.bundleStep).addClass('kwork-save-step__bundle-only');
			if (_kworkPackage == 'package' && !$(_selectors.bundleSize.checkbox).is(':checked')) {
				$(_selectors.bundleSize.checkbox).trigger('click');
			}
		} else if (type == stepType.SINGLE) {
			$(_selectors.bundleSize.block).addClass('kwork-save-step__bundle-size_inactive');
			$(_selectors.bundleStep).removeClass('kwork-save-step__bundle-only');
			if ($(_selectors.bundleSize.checkbox).is(':checked')) {
				$(_selectors.bundleSize.checkbox).trigger('click');
			}

			// Скрываем ошибки в пакетах
			$('.js-bundle-tooltip-error').each(function () {
				that.validator.hideError($(this));
			});
		}
		if (type == stepType.BUNDLE) {
			_setBundleExtras(categoryId);
		}
		MyExtras.showExtras();
		_changeBundleSize.apply($(_selectors.bundleSize.checkbox));
	};

	var _checkActiveBundleCustomExtraCount = function () {
		var uniqueValues = {};
		var totalCount = 0;

		var bundleValues = {
			'standard': 0,
			'medium': 0,
			'premium': 0
		};

		var presentFillableOptionsCount = 0;
		var hasNumericGradeOption = false; //Есть ли числовая опция которая больше в каждом пакете

		$(_selectors.bundle.extras).find('tr').each(function () {
			var numericValues = [];
			$(this).find('td').each(function (index, element) {
				if (index > 0) {
					var val = false;
					if ($(element).is('.bundle-item__field_list')) {
						val = $(element).find('input, select').val() > 0 || $(element).find('input, select').val().length;
					} else if ($(element).is('.bundle-item__field_text')) {
						val = $(element).find('input').val().length;
						numericValues.push($(element).find('input').val() ^ 0);
					} else {
						val = $(element).find('input').is(':checked');
					}
					presentFillableOptionsCount++;

					if (val) {
						bundleValues[bundleTypes[index - 1]]++;
						if (!uniqueValues.hasOwnProperty($(element).data('uniqueParamId'))) {
							uniqueValues[$(element).data('uniqueParamId')] = 1;
							totalCount++;
						}
					}
				}
			});

			if (numericValues.length === 3 && numericValues[2] > numericValues[1]  &&  numericValues[1] > numericValues[0]) {
				hasNumericGradeOption = true;
			}
		});

		var minCountError = false;
		if ($(_selectors.bundleStep).is('.kwork-save-step_type_bundle') && presentFillableOptionsCount) {
			for (var i in bundleValues) {
				if (bundleValues[i] === 0) {
					minCountError = true;
				}
			}
		}

		if ($(_selectors.bundleStep).is('.kwork-save-step__bundle-only') && presentFillableOptionsCount) {
			if (bundleValues.standard === 0) {
				minCountError = true;
			}
		}

		var priceError = false;
		$.each(PackagePrices.getPriceSelectors(), function (key, value) {
			if ($(value).length && !$(value).hasClass('range-change')) {
				priceError = true;
			}
				if ($(value).hasClass('for-mobile-value')) {
					priceError = false;
				}
			if (!$(_selectors.bundleStep).is('.kwork-save-step_type_bundle')) {
				// Если однопакетный кворк то прерываем после первой итерации чтобы проверить цены только в первом пакете
				return false;
			}
		});

		if (totalCount > 9) {
			$('.js-add-bundle-extra-count-error').addClass('kwork-save-step__field-value_error').removeClass('hidden');
		} else {
			$('.js-add-bundle-extra-count-error').removeClass('kwork-save-step__field-value_error').addClass('hidden');
		}
		if (minCountError) {
			$('.js-add-bundle-extra-min-count-error').addClass('kwork-save-step__field-value_error').removeClass('hidden');
		} else {
			$('.js-add-bundle-extra-min-count-error').removeClass('kwork-save-step__field-value_error').addClass('hidden');
		}

		// Если черновик то скрыть ошибку
		var draftId = $('#draft_id').val();
		if (_isSaveDraft() && draftId.length > 0 && window.checkUserActive === undefined) {
			$('.js-add-bundle-extra-min-count-error').removeClass('kwork-save-step__field-value_error').addClass('hidden');
		}

		$('.js-add-bundle-extra-grade-count-error, .js-add-bundle-select-price-error, .js-add-bundle-extra-duration-error')
			.removeClass('kwork-save-step__field-value_error').addClass('hidden');

		if (priceError && ($(_selectors.bundleStep).is('.kwork-save-step_type_bundle') || $(_selectors.bundleStep).is('.kwork-save-step__bundle-only'))) {
			$('.js-add-bundle-select-price-error').addClass('kwork-save-step__field-value_error').removeClass('hidden');
		}

		if ($(_selectors.bundleStep).is('.kwork-save-step_type_bundle') && presentFillableOptionsCount) {
			var hasSmallerOptionsCountInBiggerPackage = bundleValues.standard > bundleValues.medium || bundleValues.medium > bundleValues.premium;
			var hasSameOptionsCount = bundleValues.standard == bundleValues.medium && bundleValues.medium >= bundleValues.premium;
			if (hasSmallerOptionsCountInBiggerPackage || (hasSameOptionsCount && !hasNumericGradeOption)) {
				// Показываем ошибку если есть меньшее количество опций в пакете
				// или если количество опций во всех пакетах равно и нет опции с постоянным увеличением числового значения
				$('.js-add-bundle-extra-grade-count-error').addClass('kwork-save-step__field-value_error').removeClass('hidden');
			}
		}

		if ($(_selectors.bundleStep).is('.kwork-save-step_type_bundle') &&
			(parseInt($('#bundle-standard-days').val()) > parseInt($('#bundle-medium-days').val()) ||
			parseInt($('#bundle-medium-days').val()) > parseInt($('#bundle-premium-days').val()))) {
			$('.js-add-bundle-extra-duration-error').addClass('kwork-save-step__field-value_error').removeClass('hidden');
		}

		_togglePriceRangeError();

		return totalCount <= 9 && !minCountError && !priceError;
	};

	/**
	 * Получение идентификаторов всех выбранных атрибутов и идентификаторов их классификаций
	 *
	 * @returns {Array}
	 * @private
	 */
	var _getCheckedAttributesIdsWithParentIds = function(){
		var attributeIds = [];
		$(_selectors.attribute.bindableAttribute + ', ' +_selectors.attribute.classificationCheckbox)
		.filter(':checked')
		.each(function (i, item) {
			attributeIds.push($(item).val() ^ 0);
			var parentId = $(item).data('parent-id') ^ 0;
			if (attributeIds.indexOf(parentId) === -1) {
				attributeIds.push(parentId);
			}
		});

		return attributeIds;
	};

	var _setBundleExtras = function (categoryId) {
		var extras = [];
		if (_options.categoryExtras.hasOwnProperty(categoryId)) {
			extras = _options.categoryExtras[categoryId];

			var attributeIds = _getCheckedAttributesIdsWithParentIds();

			extras = $.grep(extras, function (item) {
				// Показываем опции которые либо без атрибутов
				if (item.attributes_ids.length === 0) {
					return true;
				} else {
					// Либо в которых атрибуты в числе выбранных в кворке
					for (var i in attributeIds) {
						if (item.attributes_ids.indexOf(attributeIds[i]) !== -1) {
							return true;
						}
					}
				}
			});

			var instead_ids = $.grep(extras, function (item) {
				return item.instead_id !== null;
			}).map(function (item) {
				return item.instead_id;
			});

			// Удаление опций помеченных в instead_id
			extras = $.grep(extras, function (item) {
				return instead_ids.indexOf(item.id) === -1;
			});
		}

		_clearBundleExtras();
		for (var i in extras) {
			extras[i].extraType = 'category';
		}

		var allExtras = extras;

		for (i in _options.customBundleExtras) {
			_options.customBundleExtras[i].extraType = 'custom';
			allExtras = allExtras.concat(_options.customBundleExtras[i]);
		}

		function compare(a, b) {
			var typesOrder = ['label', 'int', 'list', 'text'];
			if (typesOrder.indexOf(a.type) < typesOrder.indexOf(b.type))
				return -1;
			if (typesOrder.indexOf(a.type) > typesOrder.indexOf(b.type))
				return 1;

			return a.order_index < b.order_index ? -1 : 1;
		}

		allExtras.sort(compare);

		for (i = 0; i < allExtras.length; i++) {
			_addBundleRow(allExtras[i]);
		}

		$(_selectors.bundle.inputSelect).chosen({width: '100%', disable_search: true});
	};

	/**
	 * Определение нужно ли показывать кнопку "добавить свою опцию"
	 * и изменение соотвествующего состояния
	 *
	 * @private
	 */
	var _toggleShowAddCustomButton = function (){
		var total = $('.js-bundle-extra-custom.bundle-item__field_header').length;
		if (total >= MAX_BUNDLE_CUSTOM_EXTRA) {
			$('.kwork-save-bundle').addClass('without-add-custom-button');
		} else {
			$('.kwork-save-bundle').removeClass('without-add-custom-button');
		}
	};

	var _addBundleRow = function (extra) {
		var $row = $('<tr>');
		var $header = _addBundleHeader(extra, extra.extraType == 'custom');
		$row.append($header);

		for (var i in bundleTypes) {
			if (extra.extraType == 'category') {
				_addBundleExtra($row, bundleTypes[i], extra);
			} else {
				if (!_options.customBundleExtras.hasOwnProperty(extra.id)) {
					_options.customBundleExtras[extra.id] = extra;
				}
				_addBundleCustomExtra(
					$row,
					{
						name: extra.name,
						hint: extra.hint,
						type: extra.type,
						min: 1,
						max: extra.max_value,
						id: extra.id,
						can_lower: 1
					},
					_options.selectedPackageExtras,
					bundleTypes[i]
				);
			}
		}
		$(_selectors.bundle.extras).append($row);
		_toggleShowAddCustomButton();
	};

	var _onAddCustomBundleExtra = function () {
		var $name = $(_selectors.bundle.addCustomExtra.name);
		var $hint = $(_selectors.bundle.addCustomExtra.hint);
		var $error = $(_selectors.bundle.addCustomExtra.error);
		var name = _removeSpaces($name.val());
		var hint = _removeSpaces($hint.val());
		var nameLength = _getTextLengthWithoutTags(name);
		var maxLength = $name.data("max");

		// проверяем дли названия опции
		if (!nameLength) {
			$error.text(_errorMessages.notCustomName);
			$error.show();
			return;
		} else if(nameLength > maxLength) {
			var error = that.validator.getErrorText("max_req", {name: $name.data("field-name"), count: maxLength});
			$error.text(error).show();
			return;
		}

		// проверяем длину подсказки
		if (!hint.length) {
			$error.text(_errorMessages.notCustomHint);
			$error.show();
			return;
		}

		// Проверяем не дублирует ли новая опции уже существующие
		var duplicate = false;
		$(".bundle-item__field_header .bundle-item__field-label").each(function(i, el){
			if(!duplicate && name === $(el).text().trim()) {
				duplicate = true;
			}
		});
		if(duplicate) {
			$error.text(_errorMessages.duplicateOption);
			$error.show();
			return;
		}

		$error.hide();

		// проверяем на орфографические и прочие ошибки
		that.validator.planCheckText($name, true);
		if (that.validator.checkFieldsErrors($name.attr('id'))) {
			return;
		}
		that.validator.planCheckText($hint, true);
		if (that.validator.checkFieldsErrors($hint.attr('id'))) {
			return;
		}

		var $addButton = $('.js-bundle-item__field_add-custom');
		var iteration = 1 + ($addButton.data('iteration') ^ 0);
		var type = $(_selectors.bundle.addCustomExtra.type).filter(':checked').val();
		var id = 'n' + iteration;

		$addButton.data('iteration', iteration);

		var $extParams = $('<input>');
		$extParams.attr('type', 'hidden')
		.addClass('extra-custom-param-' + id)
		.attr('name', 'bundle_custom_extra_name[' + id + ']')
		.val(name);
		var $fieldsBlock = $('.js-add-bundle-extra-fields');
		$fieldsBlock.append($extParams);

		$extParams = $('<input>');
		$extParams.attr('type', 'hidden')
		.addClass('extra-custom-param-' + id)
		.attr('name', 'bundle_custom_extra_type[' + id + ']')
		.val(type);
		$fieldsBlock.append($extParams);

		$extParams = $('<input>');
		$extParams.attr('type', 'hidden')
		.addClass('extra-custom-param-' + id)
		.attr('name', 'bundle_custom_extra_hint[' + id + ']')
		.val(hint);
		$fieldsBlock.append($extParams);

		var max = 1;
		var can_lower = '';
		switch (type) {
			case 'integer':
				max = 10;
				can_lower = 1;
				break;
			case 'text':
				max = 0;
				break;
			default:
				break;
		}

		var extra = {
			id: id,
			name: name,
			hint: hint,
			type: type,
			extraType: 'custom',
			min: 1,
			max: max,
			can_lower: can_lower
		};

		_addBundleRow(extra);

		_clearAddCustomBundle();

		$('.js-add-package-extra-popup').modal("hide");
	};

	var _clearBundleExtras = function () {
		$('.js-bundle-extras').html('');
	};

	var _addBundleHeader = function (extra, isCustom) {
		var $header = $('<td class="bundle-item__field bundle-item__field_header">');
		var $label = $('<label class="bundle-item__field-label">');
		$label.text(extra.name);

		$header.append($label);

		if (extra.type == 'label') {
			$header.addClass('bundle-item__field_header-checkbox');
		}

		if (isCustom) {
			$label.addClass('bundle-item__field-label_custom');
			$header.addClass('js-bundle-extra-custom bundle-item__field_custom extra-id-' + extra.id);
			var $removeButton = $('<div>');
			$removeButton.addClass('js-bundle-item__remove-extra bundle-item__remove-extra').append('<i class="ico-close-12"></i>');
			$removeButton.css('opacity', 0);
			$removeButton.data('id', extra.id);
			$header.append($removeButton);

			$header.append('<div class="bundle-item__field-custom-label">' + t('Своя опция') + '</div>');
		}

		$header.on('mouseenter', function () {
			$('.extra-id-' + extra.id).find(_selectors.bundle.removeCustomExtra).css('opacity', 1);
		});

		$header.on('mouseleave', function () {
			$('.extra-id-' + extra.id).find(_selectors.bundle.removeCustomExtra).css('opacity', 0);
		});

		$header.on('click', _selectors.bundle.removeCustomExtra + ' .ico-close-12', _removeCustomBundleExtra);

		_setTooltipExtra($header, extra.hint);

		return $header;
	};

	var _addBundleExtra = function ($bundleExtraBlock, type, extra) {
		var selectedExtra = {};

		if (_selectedPackageExtras[type].hasOwnProperty('category')) {
			for (var j = 0; j < _selectedPackageExtras[type]['category'].length; j++) {
				if (extra.id == _selectedPackageExtras[type]['category'][j].item_id) {
					selectedExtra = _selectedPackageExtras[type]['category'][j];
				}
			}
		}

		$bundleExtraBlock.append(_getBundleExtraBlock(type, extra, 'category', selectedExtra));
	};

	var _addBundleCustomExtra = function ($bundleExtraBlock, extra, extras, type) {
		var selectedExtra = undefined;

		if (typeof extras[type] == 'object' && extras[type].hasOwnProperty('custom')) {
			var customExtras = extras[type]['custom'];
			for (var i = 0; i < customExtras.length; i++) {
				if (extra.id == customExtras[i].item_id) {
					selectedExtra = customExtras[i];
				}
			}
		}

		var $item = _getBundleExtraBlock(type, extra, 'custom', selectedExtra);

		$item.addClass('js-bundle-extra-custom extra-id-' + extra.id).data('id', extra.id);

		$bundleExtraBlock.append($item);

		$item.find('select').chosen({width: '100%', disable_search: true});
		$item.find('.chosen-container').css({
			'float': 'none',
		});

		$item.on('mouseenter', function () {
			$('.extra-id-' + extra.id).find(_selectors.bundle.removeCustomExtra).css('opacity', 1);
		});

		$item.on('mouseleave', function () {
			$('.extra-id-' + extra.id).find(_selectors.bundle.removeCustomExtra).css('opacity', 0);
		});
	};

	var _getBundleExtraBlock = function (bundleType, extra, extraType, selectedExtra) {
		var $extra = '';

		switch (extra.type) {
			case 'int':
				$extra = $('<td class="bundle-item__field bundle-item__field_text js-field-block js-bundle-' + bundleType + '">');
				$extra.append(_getBundleExtraTypeInteger(bundleType, extra, extraType, selectedExtra));
				break;
			case 'label':
				$extra = $('<td class="bundle-item__field bundle-item__field_checkbox">');
				$extra.append(_getBundleExtraTypeBool(bundleType, extra, extraType, selectedExtra));
				break;
			case 'text':
				$extra = $('<td class="bundle-item__field bundle-item__field_text js-bundle-' + bundleType + '">');
				$extra.append(_getBundleExtraTypeText(bundleType, extra, extraType, selectedExtra));
				break;
			case 'list':
				$extra = $('<td class="bundle-item__field bundle-item__field_select js-bundle-' + bundleType + '">');
				$extra.append(_getBundleExtraTypeList(bundleType, extra, extraType, selectedExtra));
				break;
		}

		if ($extra) {
			$extra.find(_selectors.bundle.input).data('uniqueParamId', extraType + extra.id);
		}

		return $extra;
	};

	var _getBundleExtraTypeInteger = function (bundleType, extra, extraType, selectedExtra) {
		var value = '';

		if (typeof selectedExtra == 'object' && selectedExtra.value) {
			value = selectedExtra.value;
		}

		var placeholder = "";
		if (extra.can_lower > 0) {
			placeholder = t('до ');
		}

		return $('<input name="bundle_extra_' + bundleType + '_value[' + extraType + '][' + extra.id + ']" class="js-bundle-item__input bundle-item__input_text input js-only-integer kwork-save-step__field-input js-bundle-tooltip-error" type="text"><i class="fa fa-pencil editable-pencil"></i>')
		.val(value).attr({
			'id': 'bundle-extra_' + bundleType + '_' + extraType + '_' + extra.id,
			'data-min-value': extra.min,
			'data-max-value': extra.max,
			'placeholder': placeholder,
		});
	};

	var _getBundleExtraTypeBool = function (bundleType, extra, extraType, selectedExtra) {
		var isSelected = '';
		if (typeof selectedExtra == 'object' && selectedExtra.value == 1) {
			isSelected = 'checked';
		}
		var $extraCheckbox = $('<input name="bundle_extra_' + bundleType + '_value[' + extraType + '][' + extra.id + ']" class="js-bundle-item__input styled-checkbox" type="checkbox" ' + isSelected + '>')
			.val(1).attr('id', 'bundle-extra_' + bundleType + '_' + extraType + '_' + extra.id);

		var $extraLabel = $('<label for="bundle-extra_' + bundleType + '_' + extra.id + '" class="bundle-item__field-label">')
			.html('&nbsp;')
			.attr('for', 'bundle-extra_' + bundleType + '_' + extraType + '_' + extra.id);

		return $extraCheckbox.add($extraLabel);
	};

	var _getBundleExtraTypeText = function (bundleType, extra, extraType, selectedExtra) {
		var value = '';

		if (typeof selectedExtra == 'object' && selectedExtra.value) {
			value = selectedExtra.value;
		}
		return $('<input name="bundle_extra_' + bundleType + '_value[' + extraType + '][' + extra.id + ']" class="js-bundle-item__input bundle-item__input_text input kwork-save-step__field-input js-bundle-tooltip-error" type="text"><i class="fa fa-pencil editable-pencil"></i>')
			.val(value).attr({
				'id': 'bundle-extra_' + bundleType + '_' + extraType + '_' + extra.id,
				'maxlength': '16'
			});
	};

	var _getBundleExtraTypeList = function (bundleType, extra, extraType, selectedExtra) {
		var $extraLabel = $('<label for="bundle-extra_' + bundleType + '_' + extra.id + '" class="bundle-item__field-label">');

		var $extraSelect = $('<select name="bundle_extra_' + bundleType + '_value[' + extraType + '][' + extra.id + ']" class="js-bundle-item__input input input_size_s bundle-item__input_select">')
			.attr('id', 'bundle-extra_' + bundleType + '_' + extraType + '_' + extra.id);

		for (var i = 0; i < extra.list_values.length; i++) {
			var isSelected = '';
			if (typeof selectedExtra == 'object' && selectedExtra.value == extra.list_values[i]) {
				isSelected = ' selected';
			}
			$extraSelect.append('<option value="' + extra.list_values[i] + '"' + isSelected + '>' + extra.list_values[i] + '</option>');
		}

		return $extraLabel.add($extraSelect);
	};

	var _setTooltipExtra = function ($el, message) {
		if (message != '') {
			var $label = $el.find('.bundle-item__field-label');
			$label.addClass('tooltipster');
			$label.attr('data-tooltip-side', 'right');
			$label.attr('data-tooltip-theme', 'dark');
			$label.attr('data-tooltip-text', message);
			$label.tooltipster(TOOLTIP_CONFIG);
		}
	};

	var _removeCustomBundleExtra = function () {
		var id = $(this).closest(_selectors.bundle.removeCustomExtra).data('id');
		delete _options.customBundleExtras[id];

		$('.extra-id-' + id).each(function () {
			$(this).trigger('mouseleave.tooltip').remove();
		});

		if (id.indexOf('n') === 0) {
			$('.extra-custom-param-' + id).remove();
		} else {
			var $extParams = $('<input>');
			$extParams.attr('type', 'hidden')
				.addClass('extra-custom-param-' + id)
				.attr('name', 'bundle_custom_extra_remove[]')
				.val(id);

			var $fieldsBlock = $('.js-add-bundle-extra-fields').append($extParams);
		}
		_toggleShowAddCustomButton();

		return false;
	};

	var _setFileObjects = function (files) {
		_files.description = new FileUploader({
			files: files.description,
			deleteCallback: deleteFileCallback,
			selector: '#load-files-description',
			input: {
				name: 'description_files'
			},
			buttonDisabled: '.js-uploader-button-disable',
			onChange: onChangeFile,
		});

		_files.instruction = new FileUploader({
			files: files.instruction,
			deleteCallback: deleteFileCallback,
			selector: '#load-files-instruction',
			input: {
				name: 'instruction_files'
			},
			buttonDisabled: '.js-uploader-button-disable',
			onChange: onChangeFile,
		});
	};

	var _isClearTrumbowyg = function(t) {
		var _html = t.trumbowyg('html') || '';
		if (
			_html == ''
			|| _html == '<p><br></p>'
			|| _html == '<p></br></p>'
			|| _html == '<p></ br></p>'
		) {
			t.trumbowyg('empty');
		}
	};

	var _trumbowygInit=function(t) {
		if(t.trumbowyg('html')=='') {
			t.trumbowyg('html', '<p><br></p>');

			var el = t.parent().find('.trumbowyg-editor')[0];
			var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], 0);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}
	}

	/**
	 * #6586 Получение подсказок категории
	 */
	var _getCategoryHints = function (title) {
		if (_xhrCategoryHints) {
			_xhrCategoryHints.abort();
		}

		if (title.length) {
			_xhrCategoryHints = $.post(
				'/category/get_category_hints',
				{
					title: title,
					lang: $('#lang').val(),
				},
				function (response) {
					_showCategoryHints(response.hints || []);
				},
				'json'
			);
		}
	};

	/**
	 * #6586 Отображение/скрытие подсказок категории
	 */
	var _showCategoryHints = function (hints) {
		// Длительность анимации
		var duration = 100;

		var $container = $('.js-category-hints');

		if (hints.length) {
			// Показанные в данный момент подсказки
			var oldHints = [];
			$container.find('.js-category-hint').each(function (i, e) {
				var $hint = $(this);
				oldHints.push({
					categoryId: $hint.data('categoryId'),
					subcategoryId: $hint.data('subcategoryId'),
					classificationId: $hint.data('classificationId'),
				});
			});

			// Проверка, изменились ли подсказки
			var updated = false;
			if (hints.length !== oldHints.length) {
				updated = true;
			} else {
				for (var i = 0; i < hints.length; i++) {
					if (hints[i].categoryId != oldHints[i].categoryId ||
						hints[i].subcategoryId != oldHints[i].subcategoryId ||
						hints[i].classificationId != oldHints[i].classificationId
					) {
						updated = true;
						break;
					}
				}
			}
			if (!updated) {
				return;
			}

			var html = '<div class="js-category-hints-title">' +
					t('Услуги с подобными заголовками чаще всего относятся к') + ':' +
				'</div>';

			for (var i = 0; i < hints.length; i++) {
				var hint = hints[i];

				html += '\
					<div class="category-hint js-category-hint"\
						style="opacity: 0;"\
						data-category-id="' + hint.categoryId + '"\
						data-subcategory-id="' + hint.subcategoryId + '"\
						data-classification-id="' + hint.classificationId + '"\
					><span>' + hint.categoryName + ' &gt; ' + hint.subcategoryName;

				if (hint.classificationName.length) {
					html += ' &gt; ' + hint.classificationName;
				}

				html += '</span></div>';
			}

			$container.html(html);

			var delay = 0;

			if (!$container.is(':visible')) {
				var $title = $container.find('.js-category-hints-title').css({ opacity: 0 });
				$container.show();
				$title.animate({ opacity: 1 }, duration);
				delay += duration;
			}

			$container.find('.js-category-hint').each(function (i, e) {
				$(this).delay(delay + i * duration).animate({ opacity: 1 }, duration);
			});
		} else {
			if ($container.is(':visible')) {
				var $hints = $container.find('.js-category-hint');
				$hints.each(function (i, e) {
					$(this).delay(($hints.length - 1 - i) * duration).animate({ opacity: 0 }, duration);
				});

				var $title = $container.find('.js-category-hints-title');
				$title.delay($hints.length * duration).animate({ opacity: 0 }, duration);
				$container.delay(($hints.length + 1) * duration).hide(0).empty();
			}
		}
	};

	/**
	 * #6586 Выбор категорий и классификаций при клике на подсказке
	 */
	var _useCategoryHint = function () {
		var $hint = $(this);
		var categoryId = $hint.data('categoryId');
		var subcategoryId = $hint.data('subcategoryId');
		var classificationId = $hint.data('classificationId');

		var $subcategory = $('.js-category_sub');

		if (subcategoryId == $subcategory.val()) {
			if (classificationId > 0) {
				$('#attribute_item_' + classificationId)
					.prop('checked', true)
					.trigger('change');
			}
		} else {
			if (classificationId > 0) {
				var onClassificationLoaded = function (event, catId, attrId) {
					if (catId == subcategoryId && attrId == '') {
						$('#attribute_item_' + classificationId)
							.prop('checked', true)
							.trigger('change');
						$(document).off('classification:loaded', onClassificationLoaded);
					}
				};
				$(document).on('classification:loaded', onClassificationLoaded);
			}

			var $category = $('.js-category_parent');
			if (categoryId != $category.val()) {
				$category
					.val(categoryId)
					.trigger('chosen:updated')
					.trigger('change');
			}

			$subcategory
				.val(subcategoryId)
				.trigger('chosen:updated')
				.trigger('change');
		}
	};

	/**
	 * #6666 Проверка цен пакетов и показ или скрытие ошибок.
	 * Стоимость цены Эконом пакета должна отличаться от цены Бизнес не более чем в 5 раз.
	 *
	 * @param bool|undefined error Если значение не определено, то происходит проверка
	 */
	var _togglePriceRangeError = function (error) {
		if (error === undefined) {
			var isBundle = $(_selectors.bundleStep).is('.kwork-save-step_type_bundle');
			var standardPrice = $('#priceStandardSelect').val();
			var premiumPrice = $('#pricePremiumSelect').val();

			error = isBundle && standardPrice && premiumPrice && (premiumPrice / standardPrice > 5);
			error = (error === undefined) ? false : error;
		}

		$('.js-add-bundle-extra-price-error')
			.toggleClass('kwork-save-step__field-value_error', error)
			.toggleClass('hidden', !error);

		$('#priceStandardSelect, #pricePremiumSelect')
			.toggleClass('select-error', error);

		$('#priceStandardSelect_chosen, #pricePremiumSelect_chosen')
			.toggleClass('chosen-container-error', error);
	};

	return {
		init: function (options) {
			_options = options;
			that = this;

			_saveMode = options.saveMode;
			_kworkId = options.kworkId;
			_kworkPackage = options.kworkPackage;
			_kworkUrl = options.kworkUrl;
			_kworkPrice = options.kworkPrice;
			_kworkCategoryId = options.kworkCategoryId;

			// Редактор - Описание и файлы
			jQuery(_selectors.field.description)
				.trumbowyg({
				lang: 'ru',
				fullscreenable: false,
				closable: false,
				btns: ['bold', '|', 'italic', '|', 'orderedList'],
				removeformatPasted: true
				})
				.on('tbwblur', function() {
					_isClearTrumbowyg(jQuery(this));
				})
				.on('tbwfocus', function() {
					_trumbowygInit(jQuery(this));
			});

			_setEvents();

			if (_kworkId) {
				$('.js-step').each(function(k, v) {
					var $el = $(this).find(_selectors.nextStep);
					setTimeout(function() {
						$el.click();
					}, 50);
				});
			}

			_formCache = $(_selectors.kworkForm).serialize();

			$(_selectors.bundle.inputSelect).each(_setSelectChecked);

			$(document).on('click', _selectors.bundle.addCustomExtra.btn, _onAddCustomBundleExtra);

			$('.js-tooltip').tooltip();

			$(document).on('change', _selectors.attribute.classificationCheckbox, function () {
				var categoryId = $(_selectors.category).categoryList('categoryId');
				var parentId = $(this).data('parentId');
				var attributeId = $(this).val();
				_setBundleOnChangeCategory(categoryId);
				if ($(this).prop("checked") === false) {
					$('.js-field-block[data-parent-id="' + attributeId + '"]').remove();
				}
				if ($(this).data("has-child")) {
					_loadClassification(attributeId, parentId, true);
				} else {
					_setFreePricesByAttribute(attributeId);
				}
				that.validator.hideError($(this));
			});
		},
		checkMaxFileSize: function () {
			var totalSize = 0;
			$('.js-kwork-file').each(function () {
				var file = this.files[0];
				if (typeof file != 'undefined') {
					totalSize += file.size || file.fileSize;
				}
			});

			//totalSize += KworkPhotoModule.getPhotoSize();

			return totalSize <= 16 * 1024 * 1024;
		},
		validator: (function () {

			var ERRORS = {
				EMPTY: 'empty',
				MIN_REQ: 'min_req',
				MAX_REQ: 'max_req',
				BAD_YOUTUBE_LINK: 'bad_youtube_link',
				FIRST_PHOTO: 'firstPhoto',
				NO_SYMBOLS: 'no_symbols',
				ONLY_ENGLISH: 'only_english',
				BAD_WORDS: 'bad_words',
				MULTIPLE_MAX_COUNT: 'multiple_max_count',
				BAD_RU_LANG: 'bad_ru_lang',
				DUPLICATE_SYMBOLS: 'duplicate_symbols',
				DUPLICATE_OPTIONS: 'duplicate_options',
				EMPTY_TRANSLATES: "empty_translates",
				PARTIAL_TRANSLATE_DATA: "partial_translate_data",
				DOUBLES_IN_TRANSLATES: "doubles_in_translates",
				SINGLE_TRANSLATES: "single_translates",
				PORTFOLIO_MIN_COUNT: "portfolio_min_count",
			};

			/**
			 * Hide error
			 *
			 * @private
			 */
			var _hideError = function ($input) {
				var $fieldBlock = $input.closest(_selectors.field.block);

				if($input.is('.new_custom_attribute')) {
					$input.parents('.attribute-item-custom').removeClass('kwork-save-step__field-value_error').find('.js-kwork-save-field-error-custom').text('').addClass('hidden');
				} else {
					$fieldBlock.removeClass('kwork-save-step__field-value_error');
					$fieldBlock.find(_selectors.field.error).text('').addClass('hidden');
					$fieldBlock.find(_selectors.field.hintBundle).removeClass('hidden');
					var tooltip = $fieldBlock.find('.js-bundle-tooltip-error');
					if (tooltip.length && tooltip.hasClass('tooltipstered')) {
						tooltip.tooltipster('destroy');
					}
				}

				if ($fieldBlock.is('.js-portfolio-field')) {
					_hidePortfolioItemError($fieldBlock);
				}
			};

			/**
			 * Скрыть выделение красным на элементе портфолио и ошибку
			 *
			 * @param $fieldBlock
			 * @private
			 */
			var _hidePortfolioItemError = function ($fieldBlock) {
				var $portfolioPopup = $fieldBlock.parents('.modal-portfolio').first();
				if ($portfolioPopup.length && $portfolioPopup.find('.js-field-block.kwork-save-step__field-value_error').length === 0) {
					var position = $portfolioPopup.data('position');
					$('.js-portfolio-item-wrapper[data-position="'+position+'"] > .js-file-wrapper-block-container > .file-wrapper-block-rectangle').removeClass("error-shadow-border");
					_showHidePortfolioPopupGeneralError();
				}
			};

			/**
			 * Check Field and show error
			 *
			 * @param $input
			 * @returns {boolean} false if error
			 * @private
			 */
			var _checkField = function ($input, isShowText) {
				var replaceParams = {},
					$fieldBlock = $input.closest(_selectors.field.block),
					errorText = "",
					errorBlock = "";

				if (_isFieldExcludeFromCheck($input.attr("id"))) {
					$fieldBlock.removeClass('kwork-save-step__field-value_error');
					return true;
				}

				if ($fieldBlock.is(':hidden')) {
					// Не пишем ошибки в скрытых блоках и они не должны блокировать сохранение
					$fieldBlock.removeClass('kwork-save-step__field-value_error');
					return true;
				}

				if ($input.is('.kwork-save-field-radiobox, .kwork-save-field-checkbox')) {
					if (_isRequired($input)) {
						var selected = $input.find('input:checked');
						if (!selected.length) {
							if (isShowText) {
								errorText = _getErrorText(ERRORS.EMPTY, replaceParams);
							}
							_setErrorText($fieldBlock, errorText);
							return false;
						}
					}
				} else {
					if (_isRequired($input) && ($input.val() === null || $input.val().length == 0)) {
						// Валидация обложки кворка
						if ($input.hasClass('js-kwork-photo-input-first') && $input.closest('.js-template-photo-block').length > 0) {
							return true;
						}

						if ($input.hasClass('js-kwork-photo-input-first')) {
							var $firstPhotoInput = $input.parent().find('.js-kwork-photo-path-input');
							if ($firstPhotoInput.length && $firstPhotoInput.val().length > 0) {
								return true;
							}

							if (isShowText) {
								$(".js-add-photo_error").html();
								errorText = _getErrorText(ERRORS.FIRST_PHOTO, replaceParams);
								errorBlock = ERRORS.FIRST_PHOTO;
							}
						} else {
							if (isShowText) {
								errorText = _getErrorText(ERRORS.EMPTY, replaceParams);
							}
						}
						_setErrorText($fieldBlock, errorText, errorBlock);
						$fieldBlock.attr('data-type-error', 'empty');
						return false;
					}
				}

				if ($input.data('only-english') && _checkEnglishLang($input)) {
					if (isShowText) {
						errorText = _getErrorText(ERRORS.ONLY_ENGLISH, {});
					}
					_setErrorText($fieldBlock, errorText);
					return false;
				}

				if ($input.is(_selectors.field.checkBox)) {
					var fieldCount = _checkClassification();
					if (fieldCount > 0) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.MULTIPLE_MAX_COUNT, {count:fieldCount});
						}
						_setErrorText($fieldBlock, errorText);
						return false;
					}
				}

				if ($input.data('validator') == 'youtube' && $input.val() && !_checkYoutubeLink($input)) {
					if (isShowText) {
						errorText = _getErrorText(ERRORS.BAD_YOUTUBE_LINK, replaceParams);
					}
					_setErrorText($fieldBlock, errorText);
					return false;
				}

				if (!_checkMinInput($input)) {
					replaceParams = {
						count: $input.data('min'),
						name: $input.data('fieldName')
					};
					if (isShowText) {
						errorText = _getErrorText(ERRORS.MIN_REQ, replaceParams);
					}
					_setErrorText($fieldBlock, errorText);
					return false;
				}

				if (!_checkMaxInput($input)) {
					replaceParams = {
						count: $input.data('max'),
						name: $input.data('fieldName')
					};
					if (isShowText) {
						errorText = _getErrorText(ERRORS.MAX_REQ, replaceParams);
					}
					_setErrorText($fieldBlock, errorText);
					return false;
				}

				if ($input.data('no-symbols') && _checkNoSymbols($input)) {
					if (isShowText) {
						errorText = _getErrorText(ERRORS.NO_SYMBOLS, {});
					}
					_setErrorText($fieldBlock, errorText);
					return false;
				}

				// Проверям запрещенные слова
				if (_checkFieldsErrors()) {
					return false;
				}

				// Проверям что большая часть текста на русском
				if ($("#lang").val() === "ru") {
					if ($input.data("min-lang-percent") && !_checkRuLangString($input.val(), $input.data("min-lang-percent"))) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.BAD_RU_LANG, {pcnt:MIN_LANG_PERCENT});
						}
						_setErrorText($fieldBlock, errorText);
						return false;
					}
				}

				// проверяем опции на дубли
				if ($input.hasClass("js-add-extra-row__name-input") && !_checkOptionDuplicate($input)) {
					return false;
				}

				if ($input.is(_selectors.field.translate)) {
					if (!_checkTranslatesRequired()) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.EMPTY_TRANSLATES);
						}
						_setErrorText($fieldBlock, errorText);
						return false;
					}

					var _isMobile = jQuery(window).width() < 768 || isMobile();
					if (!_checkTranslatesSingle() && window.checkUserActive === true) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.SINGLE_TRANSLATES);
						}
						_setErrorText(_isMobile ? $input : $fieldBlock, errorText);
						return false;
					}
					if (_isMobile) {
						$input.removeClass('kwork-save-step__field-value_error');
					}

					if (!_checkTranslatesData()) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.PARTIAL_TRANSLATE_DATA);
						}
						_setErrorText($fieldBlock, errorText);
						return false;
					}

					if (!_checkTranslatesDoubles()) {
						if (isShowText) {
							errorText = _getErrorText(ERRORS.DOUBLES_IN_TRANSLATES);
						}
						_setErrorText($fieldBlock, errorText);
						return false;
					}
				}
				_hideError($input);
			};

			/**
			 * Check if field is required
			 * @param $input
			 * @returns {boolean}
			 * @private
			 */
			var _isRequired = function ($input) {
				return $input.data('required') == true;
			};

			/**
			 * Set error text
			 *
			 * @param $fieldBlock
			 * @param {string} error - Error text
			 * @private
			 */
			var _setErrorText = function ($fieldBlock, error, errorBlock) {
				!errorBlock ? "" : errorBlock;
				$fieldBlock.addClass('kwork-save-step__field-value_error');
				$fieldBlock.find(_selectors.field.error).html(error).removeClass('hidden');
				if ($fieldBlock.is('.js-portfolio-field')) {
					_markPortfolioItemAsError($fieldBlock);
				}
				if(errorBlock){
					$fieldBlock.find(_selectors.field.error).addClass(errorBlock);
				}
				$fieldBlock.find(_selectors.field.hintBundle).addClass('hidden');

				var tooltip = $fieldBlock.find('.js-bundle-tooltip-error');
				if (tooltip.length) {
					var errorTooltipConfig = Object.assign({}, TOOLTIP_CONFIG);
					errorTooltipConfig.triggerOpen = {};
					errorTooltipConfig.triggerClose = {};
					errorTooltipConfig.content = error;
					errorTooltipConfig.functionBefore = null;
					errorTooltipConfig.maxWidth = $fieldBlock.outerWidth()-2;
					errorTooltipConfig.functionInit = null;
					errorTooltipConfig.side = 'top';
					errorTooltipConfig.parent = $fieldBlock;
					errorTooltipConfig.functionPosition = function(instance, helper, position) {
						parent = $fieldBlock.filter(function() {
							return jQuery(this).css('position') == 'relative';
						}).first();

						if (parent) {
							var arrow = position.target - position.coord.left;
							if (jQuery(parent) && jQuery(parent).offset()) {
								position.coord.top -= jQuery(parent).offset().top;
								position.coord.left -= jQuery(parent).offset().left;
								position.target = position.coord.left + arrow;
							}
						}
						return position;
					};

					tooltip.tooltipster(errorTooltipConfig).tooltipster('open');
				}
			};

			var _checkMinInput = function ($input) {
				var minSymbols = $input.data('min') ^ 0;
				var textLength = 0;
				if ($input.data("hasTags") === true)
				{
					textLength = _getTextLengthWithoutTags($input.val());
				} else {
					textLength = _getTextLength($input.val());
				}
				return minSymbols == 0 || minSymbols <= textLength;
			};

			var _checkMaxInput = function ($input) {
				var maxSymbols = $input.data('max') ^ 0;
				var textLength = 0;
				if ($input.data("hasTags") === true) {
					textLength = _getTextLengthWithoutTags($input.val());
				} else if ($input.data("withoutSpace") === true) {
					textLength = _getTextLengthWithoutSpace($input.val());
				} else {
					textLength = _getTextLength($input.val());
				}
				return maxSymbols == 0 || maxSymbols >= textLength;
			};

			var _updateSaveButton = function() {
				var lang = $('#lang').val();
				if (lang == 'en') {
					return;
				}
				var saveButton = $(_selectors.saveButton);
				if (_plannedFullCheck || Object.keys(_plannedFields).length > 0) {
					saveButton.prop('disabled', true).addClass('btn_disabled');
				} else {
					saveButton.prop('disabled', false).removeClass('btn_disabled');
				}
			};

			var _planCheckText = function($input, instant) {
				if (window.stopRecursion) {
					return;
				}
				var fieldIds = [];
				if ($input) {
					var fieldId = $input.attr('id');
					if (!$input.data('checkTextValid') && !$input.data('checkBadWords')) {
						return;
					}
					fieldIds.push(fieldId);
					if (fieldId == 'step1-instruction') {
						fieldIds.push('step1-description');
					}
					$.each(fieldIds, function(k, v) {
						_plannedFields[v] = true;
					});
					var newXhrArray = [];
					$.each(_checkTextXhr, function(k, v) {
						var found = -1;
						$.each(v.filedIds, function(k2, v2) {
							if ($.inArray(v2, fieldIds) > -1) {
								found = k;
								return false;
							}
						});
						if (found > -1) {
							v.xhr.abort();
						} else {
							newXhrArray.push(v);
						}
					});
					_checkTextXhr = newXhrArray;
				} else {
					_plannedFullCheck = true;
					$.each(_checkTextXhr, function(k, v) {
						v.xhr.abort();
					});
					_checkTextXhr = [];
				}
				if (_checkTextTimeout) {
					clearTimeout(_checkTextTimeout);
				}
				_updateSaveButton();
				if (instant) {
					_checkText($input);
				} else {
					_checkTextTimeout = setTimeout(_checkText, 1000, $input);
				}
			}

			var _rangyRe = new RegExp('<span id="selectionBoundary_([^"]+)[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>', 'gi');

			var _replaceRangyTags = function(html) {
				var boundaries = {};
				html = html.replace(_rangyRe, function(f, p1) {
					boundaries[p1] = f;
					return '##boundary_' + p1 + '##';
				});
				return {
					html: html,
					boundaries: boundaries,
				};
			}

			var _removeRangyTags = function(html) {
				return html.replace(_rangyRe, '');
			}

			var _restoreRangyTags = function(html, boundaries) {
				return html.replace(/##boundary_([^#]+)##/gi, function(f, p1) {
					if (p1 in boundaries) {
						return boundaries[p1];
					}
					return '';
				});
			}

			var _removeTags = function(tagsHtml) {
				var rt = _replaceRangyTags(tagsHtml);
				var html = rt.html;
				html = html.replace(/\r\n/g, '\n');
				html = html.replace(/\n/g, '');
				html = html.replace(/<\/?[^>]*>/gi, '');
				html = _restoreRangyTags(html, rt.boundaries);
				return html;
			}

			var _removeControlCharacters = function(html) {
				return html.replace(/[\x00-\x1F\x7F-\x9F\uFEFF]/g, '');
			}

			/**
			 * Выполняет POST запрос и проверяет:
			 *  поле на запрещенные слова
			 *  текст на валидность и выделением ошибок
			 * @param $input
			 * @param all
			 * @return {boolean}
			 * @private
			 */
			var _checkText = function($input, all) {
				all = typeof all !== 'undefined' ?  all : false;
				// Если запланирована полная проверка - обнуляем выбор конкретного поля
				var makeFullCheck = _plannedFullCheck;
				var checkedFields = $.extend({}, _plannedFields);
				if (_plannedFullCheck) {
					$input = null;
				}
				var data = {},
					string = null,
					subCat = $('.js-category-sub-wrapper select').val(),
					type = $('.attribute-item input[type=radio]:checked').val();
				//перебираем все поля для автопроверки
				$('[data-check-text-valid]').each(function() {
					var itemId = $(this).attr('id');
					var isLong = $(this).data('mistake-percent-long') == true ? 1 : 0;
					var oneLine = $(this).hasClass('one-line');
					if(!all && $input && itemId != $input.attr('id') && !(itemId in _plannedFields)) {
						return true;
					}
					string = $(this).val();
					if(string.length > 0){
						string = _removeRangyTags(string);
						string = _removeControlCharacters(string);
						if (oneLine) {
							string = _removeTags(string);
						}

						// если проверяем поле с инструкцией, то передаем так же описание чтобы сравнить тексты
						var descriptionString = "";
						if($(this).hasClass("js-field-input-instruction")) {
							descriptionString = $(_selectors.field.description).val();
						}
						data[itemId] = {};
						data[itemId].string = string;
						data[itemId].descriptionString = descriptionString;
						data[itemId].isLong = isLong;
					}
				});
				//перебираем поля для проверки на запрещенные слова
				$('[data-check-bad-words]').each(function() {
					var oneLine = $(this).hasClass('one-line');
					var string = $(this).val();
					if(!string && $input) {
						string = $input.val();
					}
					string = _removeRangyTags(string);
					string = _removeControlCharacters(string);
					if (oneLine) {
						string = _removeTags(string);
					}

					if(string.length > 0) {
						var itemId = $(this).attr('id'),
							field = $(this).data('field-id'),
							catArray = null;
						//исключить данные из попапа если он закрыт
						if((itemId === 'bundle-extra-name' || itemId === 'bundle-extra-hint') && !($(".js-add-package-extra-popup").data('bs.modal') || {}).isShown)
							return true;

						if(!all && $input && itemId !== $input.attr('id') && !(itemId in _plannedFields)) {
							return true;
						}

						if(typeof data[itemId] === 'undefined')
							data[itemId] = {};
						if(typeof data[itemId].string === 'undefined')
							data[itemId].string = string;
						data[itemId].field = field;
						data[itemId].category = subCat;
						data[itemId].attributes = type;
					}
				});
				var fieldIds = Object.keys(data);

				if (fieldIds.length < 1) {
					_plannedFullCheck = false;
					_plannedFields = [];
					_updateSaveButton();
					return;
				}

				var lang = $('#lang').val();
				var xhr = $.ajax({
					type: "POST",
					url: '/api/kwork/checktext',
					async: true,
					data: {
						data: data,
						lang: lang,
					},
					dataType: "json",
					success: function(response) {

						if (makeFullCheck) {
							_plannedFullCheck = false;
						}
						$.each(checkedFields, function(k, v) {
							if (k in _plannedFields) {
								delete _plannedFields[k];
							}
						});
						_updateSaveButton();

						if (response.success) {
							for(var field in response.data){
								var item =  response.data[field];
								var $element = $('#'+field);
								var $fieldBlock;
								var errorText;

								$fieldBlock = $element.closest(_selectors.field.block);

								var oneLine = $element.hasClass('one-line');

								var editor;
								var wysiwyg = !$element.hasClass("js-content-storage");
								var keepedSelection = null;
								window.stopRecursion = true;

								if(!wysiwyg) {
									editor = $element.siblings(_selectors.content_editor)[0];
								} else {
									editor = $element.siblings(".trumbowyg-editor")[0];
								}
								var keepSelection = ($(editor).is(':focus') || editor === document.activeElement);

								var area = $(editor);
								var html = '';

								// Сохраняем позицию курсора
								if (keepSelection) {
									try {
										keepedSelection = rangySelectionSaveRestore.saveSelection();
									} catch(e) {}
								}

								html = area.html();

								var rt = _replaceRangyTags(html);
								html = rt.html;
								html = _removeControlCharacters(html);
								html = _restoreRangyTags(html, rt.boundaries);

								if (oneLine) {
									html = _removeTags(html);
								}

								var mistakes = item.mistakes || [];
								html = applyWordErrors(html, mistakes);

								if (!wysiwyg) {
									$element.val(html);
									area.html(html);
								} else {
									$element.trumbowyg('html', html);
								}
								
								// Проверка на стоп-слова тут, так как $element.trumbowyg('html', html); приводит к вызову события tbwchange которое сразу скрывает ошибки
								if(item.badWords) {
									errorText = that.validator.getErrorText(ERRORS.BAD_WORDS, {'words': item.string});
									if($element.is('.new_custom_attribute')) {
										$element.parents('.attribute-item-custom').addClass('kwork-save-step__field-value_error').find('.js-kwork-save-field-error-custom').text(errorText).removeClass('hidden');
									} else {
										that.validator.setErrorText($fieldBlock, errorText);
									}
								}

								// восстанавливаем курсор
								if (keepSelection) {
									try {
										rangySelectionSaveRestore.restoreSelection(keepedSelection);
									} catch(e) {}
									html = area.html();
									$element.val(html);
								}

								window.stopRecursion = false;

								// показываем описание ошибки, для орфографических ошибок показываем сообщение только при сохранении
								if(item.validError && !item.badWords){
									errorText = that.validator.getErrorText(item.validError, {});
									that.validator.setErrorText($fieldBlock, errorText);
								}

								if(item.badWords || item.validError) {
									errorFields[field] = true;
								} else {
									errorFields[field] = false;
								}

								if (_checkFieldsAfterReturn.length) {
									setTimeout(function() {
										$.each(_checkFieldsAfterReturn, function(k, v) {
											_checkAllStepFields(v, true);
										});
										_checkFieldsAfterReturn = [];
									}, 0);
								}
							}
						}
					}
				}, 'json');
				_checkTextXhr.push({
					filedIds: fieldIds,
					xhr: xhr,
				});
			};


			/**
			 * Проверка полей на грамматические ошибки и запрещенные слова.
			 * Нужна для того, чтобы при каждом нажатии на кнопку "продолжить"
			 * не отправлялись ajax запросы
			 * @param searchField id поля, в котором производится поиск
			 * @returns {boolean}
			 * @private
			 */
			var _checkFieldsErrors = function(searchField){
				searchField = typeof searchField !== 'undefined' ?  searchField : false;

				if(searchField && errorFields[searchField])
					return true;
				else if(searchField && !errorFields[searchField])
					return false;

				for(var field in  errorFields){
					//Если ошибка в скрытом блоке, то её не учитывать
					if(!_isFieldExcludeFromCheck(field) && !$('#'+field).closest(_selectors.step).is('.kwork-save-step_inactive')) {
						if(errorFields[field])
							return true;
					}
				}
				return false;
			};

			/**
			 * Проверяет нужно ли исключить из проверки поле
			 * @param fieldId - ID поля
			 * @return {boolean} - true - исключаем
			 * @private
			 */
			var _isFieldExcludeFromCheck = function(fieldId) {
				// если выбраны пакеты, то игнорируем проверку полей из непакетного кворка
				if ($(_selectors.bundleStep).hasClass('kwork-save-step__bundle-only')) {
					if (fieldId === "step2-service-size") {
						return true;
					}

					if ($(_selectors.bundleStep).hasClass('kwork-save-step_type_single')) {
						// Для однопакетного кворка игнорируем проверку полей пакетов перекрытых оверлеем
						var highPackagesFields = ['bundle-medium-description', 'bundle-premium-description'];
						if (highPackagesFields.indexOf(fieldId) !== -1) {
							return true;
						}
					}
				}

				return false;
			};

			/**
			 * Проверяет соответсвие процента русских букв в строке
			 * @param string
			 * @param langPercent
			 * @return {boolean}
			 */
			var _checkRuLangString = function (string, langPercent) {
				// очистим теги, чтобы не считались
				string = string.replace(_getPatternTags(), "");
				string = _removeLinks(string);

				var match, cAll = 0, cRu = 0;
				// посчитаем все буквы
				match = string.replace(/[\d.,;:\/'"<>\?\!@#\$\%\^\&\*\(\)\[\]\{\}\|\_\+\=\-\s]/gi, "");
				if (match) {
					cAll = match.length;
				}
				// посчитаем русские буквы
				match = string.match(/[а-яё]/gi);
				if (match) {
					cRu = match.length;
				}

				cRu = cRu / cAll * 100;

				return (cRu >= langPercent);
			};

			var _checkOptionDuplicate = function($input){
				var currentVal = $input.val().trim();
				if(currentVal !== "") {
					var duplicate = false;
					$(".js-add-extra-row__name-input").not($input).each(function (i, el) {
						if(currentVal !== "" && currentVal === $(el).val().trim()) {
							duplicate = true;
						}
					});
					if(duplicate) {
						var $fieldBlock = $input.closest(_selectors.field.block);
						var errorText = that.validator.getErrorText(ERRORS.DUPLICATE_OPTIONS, {});
						that.validator.setErrorText($fieldBlock, errorText);

						return false;
					} else {
						return true;
					}
				} else {
					return true;
				}
			};

			var _checkYoutubeLink = function ($input) {
				return $input.val().match(/http(?:s?):\/\/(?:www\.)?youtu(?:be\.com\/watch\?v=|\.be\/)([\w\-\_]*)(&(amp;)?‌​[\w\?‌​=]*)?/) !== null;
			};

			var _checkNoSymbols = function ($input) {
				var string = $input.val();
				if($input.data("has-tags")) {
					string = string.replace(_getPatternTags(), "");
					string = string.replace(/&[a-z]+;/gi, " ");
				}

				return string.match(/[^а-яА-ЯёЁa-zA-Z0-9,.\+\%\#\s\-\']/) !== null;
			};

			var _checkEnglishLang = function ($input) {
				var checkText = $input.val();
				if(checkText.length > 0) {
					var patternTags = _getPatternTags();
					checkText = checkText.replace("&nbsp;", "").replace(/\s/g,'').replace(patternTags, "");
					var symbols = checkText.match(/[a-zA-Z0-9.,!?@#:;%^&*()\[\]{}<>~`'"\\/]/g);
					if(symbols === null) {
						return true;
					} else {
						if(symbols.length * 100 / checkText.length < 85) {
							return true;
						}
					}
				}

				return false;
			};

			// Проверка наличия хотя бы одной пары языков перевода
			var _checkTranslatesRequired = function () {
				return $("#translate_pairs .translates .translate:not('.hidden')").length > 0;
			};

			var _checkTranslatesData = function () {
				var r = true;
				var $translates = jQuery('.js-translate-content .js-translate-item:not(.hidden)');
				var fullPairs = 0; // Заполненных пар

				$translates.each(function () {
					var $translateRow = $(this);

					if (
						$translateRow.find(".js-translate-select-from").val() != "" &&
						$translateRow.find(".js-translate-select-to").val() != ""
					) {
						fullPairs++;
					}
				});

				$translates.each(function () {
					var $translateRow = $(this);

					if (
							fullPairs > 0 &&
							$translateRow.find(".js-translate-select-from").val() == "" &&
							$translateRow.find(".js-translate-select-to").val() == ""
					) {

					} else if (
						$translateRow.find(".js-translate-select-from").val() == "" ||
						$translateRow.find(".js-translate-select-to").val() == ""
					) {
						return r = false;
					}
				});

				return r;
			};

			/**
			 * Можно указать только одну пару и чекбокс обратного перевода
			 * @private
			 */
			var _checkTranslatesSingle = function () {
				return $("#translate_pairs .translates .translate:not('.hidden')").length === 1;
			};

			var _checkTranslatesDoubles = function () {
				var data = [];
				var uniqueData = [];

				$("#translate_pairs .translates .translate:not('.hidden')").each(function () {
					data.push($(this).find("select.translateFrom").val() + "-" + $(this).find("select.translateTo").val());
				})

				$.each(data, function (i, el) {
					if ($.inArray(el, uniqueData) === -1) uniqueData.push(el);
				});

				return data.length == uniqueData.length;
			}

			var _getErrorText = function (error, replaceParams) {

				var errors = {
					'empty': t('Поле обязательно для заполнения'),
					'min_req': t('Минимальная длина {{name}} {{count}} символов'),
					'max_req': t('Максимальная длина {{name}} {{count}} символов'),
					'bad_youtube_link': t('Некорректная ссылка youtube'),
					'firstPhoto': t('Необходимо загрузить обложку'),
					'no_symbols': t('Название кворка не должно содержать символы кроме букв, цифр, точки и запятой'),
					'only_english': t('Текст должен быть только на английском языке'),
					'bad_words': t('Исключите запрещенные слова: {{words}}'),
					'multiple_max_count': t('Максимальное количество категорий {{count}}'),
					'bad_ru_lang': t('Не менее {{pcnt}}%% текста должно быть написано на русском языке.'),
					'duplicate_symbols': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
					'big_word': t('Превышена максимальная длина слов'),
					'small_word': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
					'duplicate_options': t('Дублирование опций недопустимо'),
					'duplicate_description': t('Не копируйте описание услуги. В инструкции для покупателя укажите данные, необходимые вам для выполнения работы.'),
					'word_mistakes': t('Необходимо исправить ошибки или опечатки в тексте.\nСлова с ошибками подчеркнуты красным.'),
					"empty_translates": t("Необходимо указать, как минимум, одну пару языков"),
					"partial_translate_data": t("Необходимо указать языки перевода"),
					"doubles_in_translates": t("Дублирование пар языков перевода недопустимо"),
					"single_translates": t("По новым правилам можно выбрать только одну языковую пару и указать, что вы делаете обратный перевод. Отредактируйте языки."),
					"portfolio_min_count": t("Загрузите не менее 3-х примеров работ в Портфолио")
				};

				var errorText = errors[error];

				return errorText.replace(/({{.*?}})/g, function (str, p) {
					var param = p.replace(/([{}]*)?/g, '');
					return replaceParams[param];
				});
			};

			var _getErrors = function () {
				return ERRORS;
			}

			return {
				checkField: _checkField,
				hideError: _hideError,
				checkMinInput: _checkMinInput,
				checkMaxInput: _checkMaxInput,
				setErrorText: _setErrorText,
				getErrorText: _getErrorText,
				checkText: _checkText,
				planCheckText: _planCheckText,
				checkFieldsErrors: _checkFieldsErrors,
				checkOptionDuplicate: _checkOptionDuplicate,
				checkEnglishLang: _checkEnglishLang,
				getErrors: _getErrors()
			}
		})(),
		onChangeInput: _onChangeInput,
		onInputEditor: _onInputEditor,
		togglePriceRangeError: _togglePriceRangeError,
	}
})();

var MyExtras = (function () {
	var EXTRA_TYPE_MY = 'my',
		EXTRA_TYPE_CAT = 'cat',
		/**
		 * Максимальное кол-во доп. опций
		 * @type {number}
		 */
		MAX_EXTRAS_COUNT = 10;

	var _selectors = {
		myExtraBlock: '.js-my-extras',
		myExtraContainer: '.js-my-extras-container',
		content_editor: '.js-content-editor', // поле contenteditable
		content_storage: '.js-content-storage', // поле textarea или input где хрянится строка из contenteditable
		saveButton: '.js-save-kwork',
		row: {
			row: '.js-add-extra-row',
			nameBlock: '.js-add-extra-name',
			nameInput: '.js-add-extra-row__name-input',
			nameCat: '.js-add-extra-row__name',
			price: '.js-extra-price',
			duration: '.js-extra-duration',
			catCheckbox: '.js-add-extra-row__cat-checkbox',
			delete: '.js-add-extra-delete',
			catId: '.js-add-extra-row__cat-id'
		},
		add: {
			block: '.js-extra-add-block',
			btn: '.js-extra-add-btn'
		},
		template: {
			templateContainer: '.js-my-extras-templates__item',
			chosen: '.js-chosen-template'
		}
	};

	var $_extraBlock = {};

	var _templates = {};

	var _currentExtras = {
		'cat': {},
		'my': {}
	};

	var _categoryExtras = {};

	var _setRowEvents = function ($row) {
		$row.find(_selectors.template.chosen).chosen({width: '90px', disable_search: true});
	};

	var _setEvents = function () {
		$_extraBlock.on('click', _selectors.add.btn, _addNewRowEvent);
		$_extraBlock.on('click', _selectors.row.delete, _deleteRow);
		var extraOptions = _selectors.row.price + ", " + _selectors.row.nameBlock;
		$_extraBlock.off('click input keyup keydown', extraOptions, _processCustomOptionsPrices).
		on('click input keyup keydown', extraOptions, _processCustomOptionsPrices);
	};

	var _addNewRowEvent = function () {
		_addNewExtraRow({});
	};

	var _deleteRow = function () {

		if (($(this).data('id') ^ 0) == 0) {
			$(this).closest(_selectors.row.row).remove();
			_checkMaxCustomExtras();
			return;
		}

		$(this).closest(_selectors.row.row).parent().prepend($('<input>').attr({
			type: 'hidden',
			name: 'remove_myextra_ids[]',
			value: $(this).data('id')
		}));

		$(this).closest(_selectors.row.row).remove();
		_checkMaxCustomExtras();
	};

	var _clearCategoryRows = function () {
		if (!$_extraBlock.length) {
			return
		}
		$_extraBlock.find(_selectors.row.row).filter('.add-extra__item_category').remove();
	};

	var _checkMaxCustomExtras = function () {
		_processCustomOptionsPrices();
	};

	/**
	 * Add Custom Extra row
	 *
	 * @param values {
	 *				  {
	 *					  id: {integer},
	 *					  name: {string},
	 *					  duration: {integer},
	 *					  price: {integer},
	 *					  payerPrice: {integer},
	 *				  }
	 *			   }
	 * @private
	 */
	var _addNewExtraRow = function (values) {
		var $nameBlock,
			timeStamp = new Date().getTime();

		$nameBlock = $(_getTemplate('row-name-my'));
		$nameBlock.find(_selectors.row.nameInput).val(values.name).attr('id','extraRow_'+timeStamp);
		$nameBlock.find(_selectors.content_editor).html(values.name);
		$nameBlock.find(_selectors.row.catId).val(values.id);

		var $row = $(_getTemplate('row'));

		$row.addClass('add-extra__item_my');
		$row.find(_selectors.row.nameBlock).append($nameBlock);
		$row.find(_selectors.row.delete).data('id', values.id);

		$_extraBlock.find(_selectors.add.block).before($row);

		_setSelectedOption($row, _selectors.row.price, values.payerPrice);
		_setSelectedOption($row, _selectors.row.duration, values.duration);

		_setRowEvents($row);

		_checkMaxCustomExtras();

		$nameBlock.find(_selectors.row.nameInput).on("input", KworkSaveModule.onChangeInput);
		$nameBlock.find(_selectors.content_editor).on("input", KworkSaveModule.onInputEditor);
	};

	/**
	 * Add Category Extra row
	 *
	 * @param values {
	 *				  {
	 *					  id: {integer},
	 *					  name: {string},
	 *					  checked: {boolean},
	 *					  duration: {integer},
	 *					  price: {integer},
	 *				  }
	 *			   }
	 * @private
	 */
	var _addNewCatRow = function (values) {
		var $nameBlock;

		$nameBlock = $(_getTemplate('row-name-cat'));
		$nameBlock.filter(_selectors.row.nameCat)
			.text(values.name)
			.attr('for', 'add-extra-cat-' + values.id);

		$nameBlock.filter(_selectors.row.catCheckbox)
			.attr('id', 'add-extra-cat-' + values.id)
			.prop('checked', values.checked === true);

		var $row = $(_getTemplate('row'));

		$row.addClass('add-extra__item_category');
		$row.find(_selectors.row.nameBlock).append($nameBlock);
		$row.find(_selectors.row.catCheckbox).attr('name', 'bundle_extra_cat[' + values.id + ']');
		$row.find(_selectors.row.price).attr('name', 'bundle_extra_price[' + values.id + ']');
		$row.find(_selectors.row.duration).attr('name', 'bundle_extra_duration[' + values.id + ']');

		if ($_extraBlock.find(_selectors.row.row).filter('.add-extra__item_category').length) {
			$_extraBlock.find(_selectors.row.row).filter('.add-extra__item_category').last().after($row);
		} else {
			$_extraBlock.find(_selectors.myExtraContainer).prepend($row);
		}

		_setSelectedOption($row, _selectors.row.price, values.price);
		_setSelectedOption($row, _selectors.row.duration, values.duration);

		_setRowEvents($row);
	};

	var _setSelectedOption = function ($row, selector, value) {
		var found = false;
		$row.find(selector).find('option').each(function () {
			if ($(this).attr('value') == value) {
				$(this).prop('selected', true);
				found = true;
			}
		});
		if (!found && value) {
			var priceWorker = value - value / 100 * window.commissionPercent;
			$row.find(selector).prepend('<option value="' + value + '" data-seller-value="' + priceWorker + '" disabled selected>' + value + '</option>');
		}
	};

	var _getTemplate = function (name) {
		return _templates[name];
	};

	var _setTemplates = function () {
		$(_selectors.template.templateContainer).each(function () {
			_templates[$(this).data('template')] = $(this).html();
		});
	};

	var _preSetExtras = function () {
		if (!_currentExtras[EXTRA_TYPE_MY].length) {
			_addNewExtraRow({});
			return;
		}

		for (var i = 0; i < _currentExtras[EXTRA_TYPE_MY].length; i++) {
			var extra = _currentExtras[EXTRA_TYPE_MY][i];
			_addNewExtraRow(extra);
		}
	};

	/**
	 * Set Category Extras
	 *
	 * @param categoryId {string}
	 *
	 * @private
	 */
	var _setCategoryExtras = function (categoryId) {
		_clearCategoryRows();
		if (_categoryExtras.hasOwnProperty(categoryId)) {
			for (var i = 0; i < _categoryExtras[categoryId].length; i++) {
				var extra = _categoryExtras[categoryId][i];
				extra.duration = 0;
				extra.price = 0;

				var currentExtra = false;
				for (var j = 0; j < _currentExtras[EXTRA_TYPE_CAT].length; j++) {
					if (_currentExtras[EXTRA_TYPE_CAT][j].item_id == extra.id) {
						currentExtra = _currentExtras[EXTRA_TYPE_CAT][j];
						break;
					}
				}

				if (currentExtra !== false) {
					extra.checked = true;
					extra.duration = currentExtra.duration;
					extra.price = currentExtra.price;
					extra.value = 1;
				}

				_addNewCatRow(extra);
			}
		}
	};

	var _hideExtras = function () {
		$('.kwork-save-step__field-block_extras').parent().hide();
	};

	var _showExtras = function () {
		$('.kwork-save-step__field-block_extras').parent().show();
	};

	var _processCustomOptionsPrices = function () {
		// Удалим все подсказки про опции чтобы избежать дублей
		$(".max-custom-options-tooltip, .error-custom-options-price").remove();

		var workerMaxSumString = Utils.priceFormatWithSign(window.maxOptionsSum, kworkLang, " ", "руб.");

		// Пересчитаем суммы для продавца по всем селектам цен доп. опций с учётом комиссии
		var $customExtras = $(".js-my-extras-container div.add-extra__item_my");

		// Посчитаем сумму доп. опций
		var total = _calculateExtrasPrices();
		var workerTotalSumString = Utils.priceFormatWithSign(total, kworkLang, " ", "руб.");
		var tooltip = "Общая цена всех доп. опций - до " + workerMaxSumString + " Сейчас " + workerTotalSumString;

		// Если заказана хотябы одна опция, добавим подсказу
		if (total > 0) {
			$("<div class='max-custom-options-tooltip'>" + tooltip + "</div>").insertBefore(_selectors.add.block);
		}

		var $submitButton = $(_selectors.saveButton);
		if (total <= window.maxOptionsSum) {
			// Если сумма проходит проверку, то активируем кнопку сохранения
			$submitButton.removeClass("btn_disabled").prop("disabled", false);
			// Уберем красную обводку с цен доп. опций
			$customExtras.removeClass("custom-price-error");
			if ($customExtras.length < MAX_EXTRAS_COUNT) {
				// Если сумма проходит все проверки, то показываем добавления новой опции
				$(".js-extra-add-btn").show();
			} else {
				$(".js-extra-add-btn").hide();
			}
		} else {
			// Если сумма доп. опций превышает допустимую, то:
			// - отключить кнопку сохранения
			$submitButton.addClass("btn_disabled").prop("disabled", true);
			// - досветить цены доп. опций красным
			$customExtras.addClass("custom-price-error");
			// - подсветим подсказку красным если она ещё не красная и добавим текст
			$(".max-custom-options-tooltip")
				.addClass("error")
				.html(tooltip + "<br>Уменьшите стоимость предыдущих опций, чтобы добавить еще одну");
			// - спрячем кнопку добавления опции
			$(".js-extra-add-btn").hide();
			// - удалить пустые строки с опциями
			for (var i = 0; i < $customExtras.length; i++) {
				var $customExtra = $($customExtras[i]);
				if (!$($customExtras[i]).find('input[type="text"]').val()) {
					$customExtra.remove();
				}
			}
		}

		// Дополнительно проверим были ли некорректные значения в ценах опций и выделим их
		$customExtras.each(function() {
			if ($(this).find(".custom-option-price select option:selected").is(":disabled")) {
				$(this).addClass("custom-price-error");
				$submitButton.addClass("btn_disabled").prop("disabled", true);
				if ($(".error-custom-options-price").length <= 0) {
					$("<div class='error-custom-options-price'>Цена одной или нескольких опций имеет недопустимое значение</div>").insertBefore(_selectors.add.block);
				}
			}
		});
	};

	var _calculateExtrasPrices = function () {
		var price = 0;
		var customExtras = $_extraBlock.find(_selectors.row.row).filter(".add-extra__item_my");
		for (var i = 0; i < customExtras.length; i++) {
			var $customExtra = $(customExtras[i]);
			if ($customExtra.find('input[type="text"]').val()) {
				price += $customExtra.find('select option:selected').data("sellerValue");
			}
		}
		return price;
	};

	return {
		init: function (options) {
			_setTemplates();
			$_extraBlock = $(_selectors.myExtraBlock);

			if (typeof options == 'object' && typeof options.extras == 'object') {
				_currentExtras = options.extras;
			}

			_categoryExtras = options.categoryExtras;

			_preSetExtras();
			if (options.kworkPackage == 'single') {
				_showExtras();
			} else {
				_hideExtras();
			}

			_setEvents();
		},
		setCategoryExtras: _setCategoryExtras,
		hideExtras: _hideExtras,
		showExtras: _showExtras
	}
})();

(function ($) {
	var _selectors = {
		parent: '.js-category_parent',
		sub: '.js-category_sub'
	};
	window.isFreePrice = false;

	var _setEvents = function ($block) {
		$block.on('change', _selectors.parent, function () {
			var parentId = $(this).val();

			var $sub = $block.find(_selectors.sub);
			$sub.html('');
			$sub.trigger("chosen:updated");

			if (!parentId) {
				$sub.parent().addClass('hidden');
				return;
			}

			$sub.parent().removeClass('hidden');
			var subList = $block.data('options').categories[parentId]['cats'];
			$sub.append(_buildOptions(subList, 0));

			// Нужно для отображения placeholder
			$sub.prepend(
				$('<option value="" selected></option>')
			);

			$sub.trigger("chosen:updated");
		});
	};

	var _buildList = function ($block) {
		var parentSelectedId = _getCurrentParent($block);

		var categories = $block.data('options').categories;

		var $parent = _buildSelect(categories, parentSelectedId, $block.data('options'), 'parent', '#select-parent');

		$parent.addClass('js-category_parent');

		if (!$block.data('options').noEmptyOption) {
			var $emptyOption = $('<option disabled hidden value="">' + t("Выберите категорию") + '</option>');
			$parent.prepend($emptyOption);
		}

		if (!parentSelectedId) {
			$parent.children('option:first-child').attr('selected', true);
		}

		var subList = [];
		if (parentSelectedId) {
			subList = categories[parentSelectedId]['cats'];
		}

		var $subList = _buildSelect(subList, $block.data('options').current, $block.data('options'), 'sub', '#select-sub').addClass('js-category_sub');

		var $parentWrapper = $('<div class="js-category-parent-wrapper kwork-save-step__field-input_category">').append($parent);
		var $subWrapper = $('<div class="js-category-sub-wrapper kwork-save-step__field-input_category">').append($subList);
		if (!subList.length) {
			$subWrapper.addClass('hidden');
		}

		$block.html('');
		$block.append($parentWrapper, $subWrapper);

		$parent.chosen({disable_search: true, width: '100%', display_disabled_options: false});
		$subList.chosen({disable_search: true, width: '100%', display_disabled_options: false});
	};

	var _setData = function ($select, options) {
		if (options.hasOwnProperty('data')) {
			$select.data(options.data);
		}
	};

	var _buildSelect = function (categories, current, options, type, container) {
		var name = type === 'sub' ? 'name="category_id"' : '';
		var placeholder = type === 'sub' ? t("Выберите рубрику") : t("Выберите категорию");
		var $wrapper = $(container);
		var $select = $('<select class="' + options.classes + '" ' + name + ' data-placeholder="' + placeholder + '">');
		$select.append(_buildOptions(categories, current));

		_setData($select, options);


		return $select;
	};

	var _buildOptions = function (categories, current) {
		var keys = Object.keys(categories);
		var $options = [];
		for (var i = 0; i < keys.length; i++) {
			var key = keys[i],
				category = categories[key];

			var $option = $('<option value="' + category.id + '">' + category.name + '</option>');

			if (category['max_photo_count']) {
				$option.data('maxPhotoCount', category['max_photo_count']);
			}

			if (current == category.id) {
				$option.prop('selected', true);
			}

			$option.data({
				maxDays: category.max_days,
				maxPhotoCount: category.max_photo_count,
				categoryName: category.name,
				portfolioType: category.portfolio_type
			});

			$options.push($option);
		}

		return $options;
	};

	var _getCurrentParent = function ($block) {
		var categories = $block.data('options').categories;
		var categoryIds = Object.keys(categories),
			current = $block.data('options').current;

		for (var i = 0; i < categoryIds.length; i++) {
			var parentId = categoryIds[i];
			var subList = categories[parentId]['cats'];
			for (var j = 0; j < subList.length; j++) {
				if (subList[j].id == current) {
					return parentId;
				}
			}
		}

		return 0;
	};

	var methods = {
		init: function (options) {
			return this.each(function () {
				var $this = $(this);
				$this.data('options', options);

				_buildList($this);
				_setEvents($this);
			});
		},
		parentId: function () {
			var $parent = $(this).find(_selectors.parent);
			return $parent.find('option:selected').val();
		},
		categoryId: function () {
			var $sub = $(this).find(_selectors.sub);
			return $sub.find('option:selected').val();
		},
		data: function (name) {
			var $sub = $(this).find(_selectors.sub);
			return $sub.find('option:selected').data(name);
		}

		//TODO: Получить ID дочерней категории и их свойства
	};

	/**
	 * Category List jQuery Plugin
	 * @param method
	 * @returns {*}
	 */
	$.fn.categoryList = function (method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error(t('Метод с именем {{0}} не существует для jQuery.tooltip', [method]));
		}

	};

})(jQuery);


//TODO: переделать под модуль jquery
var SwitchModule = (function () {
	return {
		init: function () {
			$('.js-switch').find('.switch__track').on('click', function () {
				$(this).closest('.js-switch').find('.switch__input').trigger('click');
			});
		}
	}
})();

$(function () {
	SwitchModule.init();
	$('.js-chosen').chosen({width: '90px', disable_search: true});
});

function foxtoggle(obj, obbox) {
	if (obbox == 'favouriteme') {
		if ($('#favouriteme').is(":checked")) {
			$('#favouritemore').show();
			 $('#favouritemore').find('.favs').attr('disabled', false);
		} else {
			$('#favouritemore').hide();
			$('#favouritemore').find('.favs').attr('disabled', true);

		}
	}
}

var TranslatesModule = (function () {

	var _selectors = {
		translateField: 'js-field-translate',
		translateContent: 'js-translate-content',
		translateItem: 'js-translate-item',
		translateSelect: 'js-translate-select',
		translateSelectFrom: 'js-translate-select-from',
		translateSelectTo: 'js-translate-select-to',
		translateReverseBlock: 'js-translate-reverse-block',
		translateReverseCheckbox: 'js-translate-reverse-checkbox',
		translateDeleteLink: 'js-translate-delete',
	};

	var events = function () {
		$('.' + _selectors.translateField)
			.on('change', '.' + _selectors.translateSelect, _changeFromSelect)
			.on('change', '.' + _selectors.translateReverseCheckbox, _changeReverseCheckbox)
			.on('click', '.' + _selectors.translateDeleteLink, function() {
				_deleteTranslate($(this).closest('.' + _selectors.translateItem));
			})
	};

	var _init = function () {
		events();

		// Отображение селектов для выбора пары языков
		if ($('.' + _selectors.translateField).find('.' + _selectors.translateItem).length === 0) {
			_addTranslate();
		}

		_chosenTranslate();
		_toggleShowTranslateReverse();
	};

	var _addTranslate = function() {
		var $translates = $('.' + _selectors.translateField);

		var id = $translates.find('.' + _selectors.translateItem).length + 1;
		var translateName = 'translate-' + id;

		$("div.translate-template")
			.clone()
			.find("input.translate-state").val('added').end()
			.find('.' + _selectors.translateItem).attr('id', translateName).end()
			.find('.' + _selectors.translateItem)
			.appendTo($('.' + _selectors.translateContent));

		$('#' + translateName + ' [name]').each(function () {
			$(this).attr('name', $(this).attr('name').replace(/translate\[-1\]/g, 'translate[' + id + ']'));
		});
	};

	/**
	 * Стилизовать выпадающий список для языков
	 */
	var _chosenTranslate = function() {
		var $translates = $('.' + _selectors.translateField);
		var $selects = $translates.find('.' + _selectors.translateSelect);

		$selects.chosen("destroy");
		$selects.chosen({
			width: '154px',
			disable_search: true
		});

		//ускоряем скролл в FF
		$translates.find('.chosen-results').unbind('mousewheel');
	};

	/**
	 * Событие при изменении слекта перевода
	 * @param e
	 * @private
	 */
	var _changeFromSelect = function(e) {
		_toggleShowTranslateReverse();
		_setReverseValues();
	};

	/**
	 * Удаляем перевод
	 * @param $translateItem
	 * @private
	 */
	var _deleteTranslate = function($translateItem) {
		$translateItem.addClass("hidden");
		$translateItem.find("input.translate-state").val("deleted");

		_toggleShowTranslateReverse();
	};

	/**
	 * Отображать или нет чекбокс обратного перевода
	 * @private
	 */
	var _toggleShowTranslateReverse = function() {
		var $translates = $('.' + _selectors.translateField);
		var $translateItems = $translates.find('.' + _selectors.translateItem).not(".hidden");
		var $translateFrom = $translateItems.eq(0).find('.' + _selectors.translateSelectFrom);
		var $translateTo = $translateItems.eq(0).find('.' + _selectors.translateSelectTo);
		var translateReverseBlock = $('.' + _selectors.translateReverseBlock);

		// если отображено больше одного перевода, то чекбокс не отображать
		if ($translateItems.length > 2) {
			translateReverseBlock.addClass('hidden');
		} else if ($translateItems.length === 2) {
			_isReversePair();
		} else if ($translateItems.length === 1 && $translateTo.val() !== "" && $translateFrom.val() !== "") {
			translateReverseBlock.removeClass('hidden');

			_setReverseText();
		} else {
			translateReverseBlock.addClass('hidden');
		}
	};

	/**
	 * Для уже существующих кворков, если два перевода реверсивны, то второй перевод скрываем и отображаем чекбокс обратного перевода
	 * @private
	 */
	var _isReversePair = function () {
		var $translates = $('.' + _selectors.translateField);
		var $translateItems = $translates.find('.' + _selectors.translateItem).not(".hidden");

		var translateFromFirstValue = $translateItems.eq(0).find('.' + _selectors.translateSelectFrom).val();
		var translateToFirstValue = $translateItems.eq(0).find('.' + _selectors.translateSelectTo).val();

		var translateFromSecondValue = $translateItems.eq(1).find('.' + _selectors.translateSelectFrom).val();
		var translateToSecondValue = $translateItems.eq(1).find('.' + _selectors.translateSelectTo).val();

		if (translateFromFirstValue === translateToSecondValue && translateToFirstValue === translateFromSecondValue) {
			// скрываем второй перевод, но не удаляем
			$translateItems.eq(1).addClass("hidden");

			// отображаем чекбокс обратного перевода
			_toggleShowTranslateReverse();

			// ставим галочку чекбоксу обратного перевода
			$('.' + _selectors.translateReverseCheckbox).prop('checked', true);
		}
	};

	/**
	 * Прописываем текст для чекбокса обратного перевода
	 * @private
	 */
	var _setReverseText = function () {
		var $translates = $('.' + _selectors.translateField);
		var $translateItems = $translates.find('.' + _selectors.translateItem).not(".hidden").eq(0);
		var $translateFrom = $translateItems.find('.' + _selectors.translateSelectFrom);
		var $translateTo = $translateItems.find('.' + _selectors.translateSelectTo);
		var translateFromSelectId = $translateFrom.val();
		var translateToSelectId = $translateTo.val();

		var translateFromSelectText = $translateFrom.find('option[value=' + translateToSelectId + ']').text();
		var translateToSelectText = $translateTo.find('option[value=' + translateFromSelectId + ']').text();

		$('.' + _selectors.translateReverseBlock).find('label').text(t('Делаю обратный перевод (с {{0}} на {{1}})', [translateFromSelectText, translateToSelectText]));
	};

	/**
	 * Действие при изменении на чекбокс обратного перевода
	 * @private
	 */
	var _changeReverseCheckbox = function () {
		if ($('.' + _selectors.translateReverseCheckbox).is(':checked')) {
			// добавляем дополнительный блок обратного перевода
			_addTranslate();

			// скрываем дополнитлеьный блок обратного перевода
			$('.' + _selectors.translateField).find('.' + _selectors.translateItem).not(".hidden").eq(1).addClass('hidden');

			_toggleShowTranslateReverse();
			_setReverseValues();
		} else {
			//удаляем дополнительный блок
			_deleteTranslate($('.' + _selectors.translateField).find('.' + _selectors.translateItem).last());
		}
	};

	/**
	 * Устанавливаем значения для обратного перевода
	 * @private
	 */
	var _setReverseValues = function () {
		var $translates = $('.' + _selectors.translateField);

		var $translateItems = $translates.find('.' + _selectors.translateItem).not(".hidden").eq(0);
		var translateFromSelectId = $translateItems.find('.js-translate-select-from').val();
		var translateToSelectId = $translateItems.find('.js-translate-select-to').val();

		var $reverseItem = $translates.find('.' + _selectors.translateItem).filter(".hidden").last();
		var $reverseFrom = $reverseItem.find('.js-translate-select-from');
		var $reverseTo = $reverseItem.find('.js-translate-select-to');

		$reverseFrom.val(translateToSelectId);
		$reverseTo.val(translateFromSelectId);
	};

	return {
		init: _init,
		updateTranslate: _init
	}
})(jQuery);


function bindEvents() {
	$(document).on('mouseenter', '.kwork-save-step__field-value_tooltip', function() { tooltipShow(this); });
	$(document).on('mouseleave', '.kwork-save-step__field-value_tooltip', function() { tooltipHide(this); });
}

function unbindEvents() {
	$(document).off('mouseenter', '.kwork-save-step__field-value_tooltip');
	$(document).off('mouseleave', '.kwork-save-step__field-value_tooltip');
}

$(window).load(function(){
	$('.favs').on('click', function () {
		var count = $('.favs[type=checkbox]:checked').length;
		if (count > 4) {
			this.checked = '';
		}
	});

	function showAttributesTooltip()
	{
		var $attributes = $('#kwork-save-attributes');
		if ($attributes.find('.field-tooltip').length === 1) {
			showKworkTooltip.apply($attributes);
		}
	}

	unbindEvents();
	bindEvents();

	showAttributesTooltip();

	var selectChangeTimer = null;
	$('.js-category-select select').change(function(){
		unbindEvents();
		var e = $(this).closest('.kwork-save-step__field-value_tooltip');
		if (selectChangeTimer !== null) {
			clearTimeout(selectChangeTimer);
			selectChangeTimer = null;
		}
		selectChangeTimer = setTimeout(function () {
			unbindEvents();
			bindEvents();
			var element = $(':hover').last().closest('.kwork-save-step__field-value_tooltip');
			if (element[0] !== e[0]) {
				e.trigger('mouseleave');
				element.trigger('mouseenter');
			}
			selectChangeTimer = null;
		}, 1000);
	});
});

jQuery(function() {
	TranslatesModule.init();

	// Отображение Youtube превью
	jQuery('.js-portfolio-block [data-youtube]').each(function() {
		var $block = jQuery(this);
		var youtubeLink = $block.data('youtube');

		if (youtubeLink) {
			var youtubePreview = Youtube.thumb(youtubeLink);
			$block.attr('style',
				'background-image: url(/images/play2.png), url(' + youtubePreview + ');'
				+ 'background-position: center center, center center;'
				+ 'background-repeat: no-repeat, no-repeat;'
				+ 'background-size: auto, cover;'
			);
			$block.removeAttr('data-youtube');
		}
	});
});

//скролл к первому блоку с причинами отклонений после полной загрузки страницы + таймаут для асинхронной подгрузки блоков
jQuery(window).on('load', function() {
	var reasonsEl = jQuery('.moder-reasons-container.initiated:first');
	if (reasonsEl.length) {
		var reasonsElOffsetTop =
			reasonsEl.closest('.js-step').offset().top -
			jQuery('.header').height() -
			jQuery('.kwork-save-step__footer:first').height();
		jQuery('html, body').animate({scrollTop: reasonsElOffsetTop}, 250);
	}
});

var PackagePrices = (function () {
	var _selectors = {
		priceStandardBlock: '.js-bundle_standard_price',
		priceMediumBlock: '.js-bundle_medium_price',
		pricePremiumBlock: '.js-bundle_premium_price'
	};
	var _priceSelectors = {
		priceStandardSelect: '#priceStandardSelect',
		priceMediumSelect: '#priceMediumSelect',
		pricePremiumSelect: '#pricePremiumSelect'
	};
	var _freePriceClassSelector = ".free-category-item__input_select";

	var _categoryPrices = {};
	var _priceGradation = {};
	var _kworkPackages = {};
	var _crt = 0;
	var _kworkPrice = 0;

	var _setCategoryPrices = function (categoryId) {
		//Если заданы свободные цены, то цену не пересчитываем
		if (_categoryPrices.hasOwnProperty(categoryId) && !window.isFreePrice) {
			var crt = _crt / 100;
			var standard_price = _categoryPrices[categoryId]['standard'];
			var standard_price_crt = Math.round(standard_price * crt * 100) / 100;
			var medium_price = _categoryPrices[categoryId]['medium'];
			var medium_price_crt = Math.round(medium_price * crt * 100) / 100;
			var premium_price = _categoryPrices[categoryId]['premium'];
			var premium_price_crt = Math.round(premium_price * crt * 100) / 100;

			$(_selectors.priceStandardBlock + ' .price_value_text').text(Math.round((standard_price - standard_price_crt) * 100) / 100);
			$(_selectors.priceMediumBlock + ' .price_value_text').text(Math.round((medium_price - medium_price_crt) * 100) / 100);
			$(_selectors.pricePremiumBlock + ' .price_value_text').text(Math.round((premium_price - premium_price_crt) * 100) / 100);
			$('#typicalPriceSelect option:first').val(standard_price);
			
			// Показываем для пакетного кворка диапазон цен (принимаю заказы от)
			// до стандартного тарифа, если 3 пакетный кворк
			if ($('.js-step-bundle').hasClass('kwork-save-step__bundle-only')) {
				var countVisibleSelect = Math.round(medium_price / 500) - 1;			
				$('#min_volume_price option').removeClass('hide');	
				$('#min_volume_price option').removeClass('standard_price_option');	
				// Помечаем селекты для эконом пакета.
				$('#min_volume_price option').slice(0, countVisibleSelect).addClass('standard_price_option');
				// Показываем диапазон цены для 3х пакетного кворка
				if($('.js-bundle-size-checkbox').prop('checked')) {	
					$('#min_volume_price option:not(.standard_price_option)').addClass('hide');						
					// Если выбранный (принимаю заказы от) недоступен, сбраываем значение
					$('#min_volume_price option:selected.hide').prop("selected", false);
				}
			} else {
				// Показываем все цены
				$('#min_volume_price option').removeClass('hide');	
			}				
			
			// Расчитываем цену для продовца (принимаю заказы от)
			$('#min_volume_price option').each(function() {
				var price = $(this).attr('value');
				var sellerPrice = price - (Math.round(price * crt * 100) / 100);
				if (kworkLang == 'en') {
					sellerPrice = "$" + sellerPrice;
				} else {
					sellerPrice = sellerPrice + " Р";
				}
				$(this).text(sellerPrice);				
			});
			$('#min_volume_price').trigger('chosen:updated').trigger('change');
			
			
			
		}
	};
	var _setFreeCategoryPrices = function (attributePrices, attributeId) {
		_priceGradation = attributePrices.priceGradation;
		var priceBlock = $('<select class="js-free-category-item-item__input free-category-item__input_select input input_size_s">');
		$.each(_priceGradation, function (key, value) {
			var selectId = key + "Select";
			var priceGradationBlock = priceBlock.clone().attr({
				"name": "bundle_free_price_value[" + key + "]",
				'id': selectId
			});
			var min = value[Object.keys(value)[0]];
			var max = value[Object.keys(value)[Object.keys(value).length - 1]];
			min = min - (min * (_crt / 100));
			max = max - (max * (_crt / 100));
			var hasChange = false;
			$.each(value, function (index, data) {
				var selected = false;
				var crtPrice = data - (data * (_crt / 100));
				if (_kworkPackages) {
					$.each(_kworkPackages, function (type, package) {
						name = type.toLowerCase().replace(/^[\u00C0-\u1FFF\u2C00-\uD7FF\w]|\s[\u00C0-\u1FFF\u2C00-\uD7FF\w]/g, function (letter) {
							return letter.toUpperCase();
						});
						name = "price" + name;
						if (name == key && package["price"] == data) {
							selected = hasChange = true;
						}
					});
				} else if(key == 'priceStandard' && _kworkPrice && index > 0 && _kworkPrice == data) {
					// Если есть цена кворка (не стандартная) то устанавливаем ее в первом пакете
					selected = hasChange = true;
				}
				$("<option>").attr({
					'value': data,
					'selected': selected
				}).text(crtPrice).appendTo(priceGradationBlock);
			});
			var typeBlock = key + "Block";
			$(_selectors[typeBlock] + ' .price_value_text').html(priceGradationBlock);
			priceGradationBlock.chosen({width: '100%', disable_search: true}).on('change', function (e) {
				var id = $(this).prop('id');
				if (id === 'priceStandardSelect' || id === 'pricePremiumSelect') {
					KworkSaveModule.togglePriceRangeError();
				}
			});
			if (selectId === 'priceStandardSelect' || selectId === 'pricePremiumSelect') {
				KworkSaveModule.togglePriceRangeError();
			}
			$('.price_value_text').on('change', 'select', function () {
				var select = $(this);
				select.addClass("range-change");
				PackagePrices.rebuildFreeCategoryPrices(select);
			});
			$(".price_value_text").on('click touchstart', '.chosen-results', function () {
				var select = $(this).parent().parent().prev();
				select.addClass("range-change");
				PackagePrices.rebuildFreeCategoryPrices(select);
			});

			if (!hasChange) {
				if ($(priceGradationBlock).next().find('.chosen-single span').length) {
					$(priceGradationBlock).next().find('.chosen-single span').text(min + " - " + max);
				} else {
					$(priceGradationBlock).addClass('for-mobile-value');
				}
			} else {
				$(priceGradationBlock).addClass('range-change');
			}
			// сделаем пересчёт по премиум пакету, без этого некорректно выводятся сохранённые свободные цены
			if($(_priceSelectors.priceStandardSelect + " option:selected").attr("selected") !== undefined && $(_priceSelectors.priceMediumSelect + " option:selected").attr("selected") !== undefined) {
				PackagePrices.rebuildFreeCategoryPrices($(_priceSelectors.pricePremiumSelect));
			}
		});
	var name = "typicalPriceSelect";
		if(attributeId){
			name = "typicalPriceSelect["+attributeId+"]";
		}
		var typicalPriceGradationBlock = priceBlock.clone().attr({
			"name": name,
			'id': "typicalPriceSelect",
			'class': "input input_size_s h30 w80i"
		});
		$.each(attributePrices.typicalPriceGradation, function (index, data) {
			var selected = false;
			var crtPrice = data - (data * (_crt / 100));
			if (_kworkPrice == data) {
				selected = true;
				$(".tooltip__title-payer-price").html(data);
				$(".tooltip__title-worker-price").html(crtPrice);
			}
			if($('#lang').val() == 'en'){
				crtPrice = "$" + crtPrice;
			}else{
				crtPrice = crtPrice + " Р";
			}

			$("<option>").attr({
				'value': data,
				'selected': selected
			}).text(crtPrice).appendTo(typicalPriceGradationBlock);
		});

		typicalPriceGradationBlock.on('change', function (e) {
			$(".tooltip__title-payer-price").html($(this).val());
			$(".tooltip__title-worker-price").html($(this).find("option:selected").text().replace(/[^0-9]+/gi, ""));
		});
		$('.kwork-save-step__kwork-price-free-value').html(typicalPriceGradationBlock);
		$(".kwork-save-step__kwork-price-free-value").removeClass("hide");
		$('#typicalPriceSelect').chosen({disable_search: true});
		$("#typicalPriceSelect_chosen").css("width", "");


		$(".kwork-save-step__kwork-price-static-value").addClass("hide");
		$(_freePriceClassSelector).parent().find('.chosen-container').css({
			'float': 'left',
			'text-align': 'left'
		});

		// После создания всех селектов нужно сделать ребилд тем у кого значение выбрано
		for (var i in _priceSelectors) {
			var select = $(_priceSelectors[i]);
			if (select.hasClass('range-change')) {
				_rebuildFreeCategoryPrices(select);
			}
		}
	};
	var _updatePriceSelect = function (selector, priceType, currentSelectValMin, direction) {
		var nextSelect = $(selector);
		var nextSelectVal = nextSelect.val();
		nextSelect.find('option').remove();
		var values = [];
		$.each(_priceGradation[priceType], function (index, data) {
			var crtPrice = data - (data * (_crt / 100));
			if (direction) {
				if (currentSelectValMin > crtPrice) {
					if (priceType == 'priceMedium') {
						var standardSelectVal = $(_priceSelectors.priceStandardSelect).val();
						var premiumSelectVal = $(_priceSelectors.pricePremiumSelect).val();
						if ((standardSelectVal < data) && (premiumSelectVal > data)) {
							$("<option>").attr({
								'value': data,
							}).text(crtPrice).appendTo(nextSelect);
							values.push(crtPrice);
						}
					} else {
						$("<option>").attr({
							'value': data,
						}).text(crtPrice).appendTo(nextSelect);
						values.push(crtPrice);
					}
				}
			} else {
				if (currentSelectValMin < crtPrice) {
					if (priceType == 'priceMedium') {
						var standardSelectVal = $(_priceSelectors.priceStandardSelect).val();
						var premiumSelectVal = $(_priceSelectors.pricePremiumSelect).val();
						if (!$(_priceSelectors.pricePremiumSelect).hasClass("range-change")) {
							premiumSelectVal = $(_priceSelectors.pricePremiumSelect + ' option:last').val();
						}
						if ((data > standardSelectVal) && (data < premiumSelectVal)) {
							$("<option>").attr({
								'value': data,
							}).text(crtPrice).appendTo(nextSelect);
							values.push(crtPrice);
						}
					} else {
						$("<option>").attr({
							'value': data
						}).text(crtPrice).appendTo(nextSelect);
						values.push(crtPrice);
					}

				}
			}

		});
		max = Math.max.apply(Math, values);
		min = Math.min.apply(Math, values);
		nextSelect.val(nextSelectVal);
		$(nextSelect).trigger("chosen:updated");
		if (min != max) {
			if (!nextSelect.hasClass("range-change")) {
				nextSelect.next().find('.chosen-single span').text(min + " - " + max);
			}
		} else {
			if (nextSelect.find("option").length == 1) {
				$(nextSelect).addClass('range-change');
				nextSelect.next().find('.chosen-single span').text(max);
			} else {
				nextSelect.next().find('.chosen-single span').text(nextSelectVal);
			}
		}
	}
	var _rebuildFreeCategoryPrices = function (currentSelect) {
		currentSelect = $(currentSelect);
		var currentSelectVal = currentSelect.val() - (currentSelect.val() * (_crt / 100));
		var currentSelectId = "#" + currentSelect.attr('id');
		switch (currentSelectId) {
			case _priceSelectors.priceStandardSelect:
				var nextSelectVal = $(_priceSelectors.pricePremiumSelect).val();
				_updatePriceSelect(_priceSelectors.priceMediumSelect, "priceMedium", currentSelectVal, false, nextSelectVal);
				var premiumPrice = $(_priceSelectors.priceMediumSelect).val();
				if (!$(_priceSelectors.priceMediumSelect).hasClass("range-change")) {
					premiumPrice = $(_priceSelectors.priceMediumSelect).find('option:first').val();
				}
				_updatePriceSelect(_priceSelectors.pricePremiumSelect, "pricePremium", premiumPrice - (premiumPrice * (_crt / 100)), false);
				$(_priceSelectors.pricePremiumSelect).val(nextSelectVal);
				break;
			case _priceSelectors.priceMediumSelect:
				var nextSelectVal = $(_priceSelectors.pricePremiumSelect).val();
				var prevSelectVal = $(_priceSelectors.priceStandardSelect).val();
				_updatePriceSelect(_priceSelectors.pricePremiumSelect, "pricePremium", currentSelectVal, false);
				_updatePriceSelect(_priceSelectors.priceStandardSelect, "priceStandard", currentSelectVal, true);
				$(_priceSelectors.priceStandardSelect).val(prevSelectVal);
				$(_priceSelectors.pricePremiumSelect).val(nextSelectVal);
				break;
			case _priceSelectors.pricePremiumSelect:
				var prevSelectVal = $(_priceSelectors.priceMediumSelect).val();
				_updatePriceSelect(_priceSelectors.priceMediumSelect, "priceMedium", currentSelectVal, true);
				$(_priceSelectors.priceMediumSelect).val(prevSelectVal);
				if (!$(_priceSelectors.priceMediumSelect).hasClass("range-change")) {
					prevSelectVal = $(_priceSelectors.priceMediumSelect).find('option:last').val();
				}
				_updatePriceSelect(_priceSelectors.priceStandardSelect, "priceStandard", prevSelectVal - (prevSelectVal * (_crt / 100)), true);
				break;
		}
	}
	var _getPriceSelectors = function () {
		return _priceSelectors;
	}
	return {
		init: function (options) {
			_categoryPrices = options.categoryPrices;
			_crt = options.crt;
			_kworkPrice = options.kworkPrice;
			_kworkPackages = options.kworkPackages;
		},
		setCategoryPrices: _setCategoryPrices,
		setFreeCategoryPrices: _setFreeCategoryPrices,
		rebuildFreeCategoryPrices: _rebuildFreeCategoryPrices,
		getPriceSelectors: _getPriceSelectors
	}
})();
