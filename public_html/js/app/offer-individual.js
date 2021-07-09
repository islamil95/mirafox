/** global offer, Utils, turnover */
/** @namespace offer.lang: {string} */
/** @namespace offer.isOrderStageTester: {integer} */
/** @namespace offer.stageMinPrice: {integer} */
/** @namespace offer.customMinPrice: {integer} */
/** @namespace offer.customMaxPrice: {integer} */
/** @namespace offer.maxKworkCount: {integer} */
/** @namespace offer.kworkPackages: {array} */
/** @namespace offer.multiKworkRate: {integer} */
/** @namespace offer.customPricesOptionsHtml: {string} */
/** @namespace turnover: {integer} */
/** @namespace minPrices: {array} */
/** @namespace stageMinPrices: {array} */

/**
 *
 * @type {{init, btnDisable, btnEnable, validateIndividualKwork, showBackendError}}
 */
window.OfferIndividualModule = (function () {

	/**
	 * Оплата заказа по задачам
	 * @type {string}
	 * @const
	 */
	const TYPE_OFFER_STAGES = 'stages';
	/**
	 * Полная оплата
	 * @type {string}
	 * @const
	 */
	const TYPE_OFFER_ALL = 'all';

	let _hideErrorsBlock = false;
	
	let _errors = [];
	let _showErrorForBlock = [];

	let _loadExtrasXhr = null;
	
	let _selectors = {
		individualKwork: '.js-individual-kwork',
		errorBlock: '.js-individual-kwork-error',
		customKwork: {
			block: '.js-custom-kwork',
			categoryWrap: '.js-category-wrap',
			categorySelect: '.js-category-select',
			description: '.js-kwork-description',
			subCategoryWrap: '.js-sub-category-wrap',
			subCategorySelect: '.js-sub-category-select',
			duration: '.js-kwork-duration',
			stageDuration: '.js-stages-duration',
			title: '.js-kwork-title',
			volume: '.js-kwork-volume',
			price: '.js-kwork-price',
		},
		activeKwork: {
			block: '.js-active-kwork',
		},
		paymentType: {
			wrapper: '.js-offer-payment-type',
			full: '.js-full-payment-type',
			stages: '.js-stages-payment-type',
			radio: '.js-new-offer-radio',
			hint: '.js-payment-type-hint',
		}
	};

	/** стоимость предложения кворка */
	let _activeKworkPrice = 0;
	/** стоимость индивидуального предложения */
	let _lastCustomKworkPrice = 0;
	/** отображать или нет элементы выбора типа оплаты */
	let _paymentTypeVisible = false;
	/** показывать полный типа оплаты? */
	let _fullPaymentTypeVisible = false;
	/** показывать задачный типа оплаты? */
	let _stagesPaymentTypeVisible = false;
	/** какой тип опалты выбран */
	let _selectedPaymentType = null;

	/**
	 * Инициализация событий
	 * @private
	 */
	let _initEvent = function () {
		_initChosen();

		$(document)
			// события для предложения кворка
			.on('change', '.js-request-kwork-id', function () {
				if ($(this).val()) {
					$('.js-send-request__total').show();
					$('.js-order-extras').show();
				} else {
					$('.js-send-request__total').hide();
					$('.js-order-extras').hide();
				}
				if ($(this).find('option:checked').data('package')) {
					$('.request-kwork-count-block').hide();
					$('.request-kwork-package-block').show();
				} else {
					$('.request-kwork-count-block').show();
					$('.request-kwork-package-block').hide();
				}
				_calcActiveKworkPrice();
				_validateIndividualKwork();
			})
			.on('change', '.js-request-kwork-count, .js-request-kwork-package-type, .order-extras__input, .js-order-extras__count, .js-new-extra-select', function () {
				_calcActiveKworkPrice();
				_validateIndividualKwork();
			})
			.on('click', '.order-extras__item', function (e) {
				let checkboxOrderExtras = $('.styled-checkbox[data-id=' + $(this).data('id') + ']');
				if (checkboxOrderExtras.prop('checked')) {
					if (!$(e.target).is('.chosen-container') && $(e.target).closest('.chosen-container').length === 0) {
						checkboxOrderExtras.prop('checked', false).trigger('change');
					}
				} else {
					checkboxOrderExtras.prop('checked', true).trigger('change');
				}
			})
			.on('change', '.js-new-extra-price', function () {
				let price = $(this).find('option:checked').data('sellerValue');
				let $countSelect = $(this).closest('.new-order-extras__select-block').find('.js-new-extra-count');
				$countSelect.find('option').each(function () {
					let sum = price * $(this).attr('value');
					$(this).text($(this).attr('value') + "(" + Utils.priceFormatWithSign(sum, offer.lang) + ")");
				});
				$countSelect.trigger("chosen:updated");
			})
			.on('change', '.js-request-kwork-id', _selectActiveKwork)
			.on('click', '.js-new-order-extras-delete', _removeCustomOptionForm)
			.on('click', '.js-order-extras-create', _showCustomOptionForm)
			.on('input', 'input[name=want_id]', _validateIndividualKwork)
			.on('input', 'input[name="customExtraName[]"]', _validateIndividualKwork)

			// события для индивидуального предложения
			.on('change', _selectors.customKwork.categorySelect, _changeCategorySelect)
			.on('change', _selectors.customKwork.duration, _validateIndividualKwork)
			.on('input', _selectors.customKwork.title, _validateIndividualKwork)
			.on('input', _selectors.customKwork.volume, _validateIndividualKwork)
			.on('input', _selectors.customKwork.price, _validateIndividualKwork)
			.on('blur', _selectors.customKwork.price, _updateCustomPrice)
			.on('input', _selectors.customKwork.categorySelect, _validateIndividualKwork)
			.on('input', _selectors.customKwork.subCategorySelect, _validateIndividualKwork)

			// события для предложения кворка и индивидуального предложения
			.on('input', _selectors.customKwork.description, _validateIndividualKwork)
			.on('click', '.js-price-explanation', _showPopupCommission)

			//Переключение на предложить кворк
			.on('click', '#change-to-kwork-choose', function () {
				$('#suggestCustomKworkToggle, #change-to-kwork-choose').addClass('hidden');
				$('#suggestKworkToggle, #change-to-custom-kwork').removeClass('hidden');
			})

			//Переключение на индивидуальное предложение
			.on('click', '#change-to-custom-kwork', function () {
				$('#suggestKworkToggle, #change-to-custom-kwork').addClass('hidden');
				$('#suggestCustomKworkToggle, #change-to-kwork-choose').removeClass('hidden');
			});

		// Переключение типов оплаты
		$(_selectors.paymentType.radio).on('click', _selectPaymentType);

		if (offer.isOrderStageTester) {
			window.offerStages.onChangeStage = () => {
				_validateIndividualKwork(false);
			};
		}

		_initPaymentType();
	};

	/**
	 * Инициализация выбора типа оплаты
	 * @private
	 */
	let _initPaymentType = function () {
		let price = parseInt($(_selectors.customKwork.price).val());

		if (price && price >= offer.stagesPriceThreshold) {
			let selectedPaymentRadio = $('.js-new-offer-radio.offer-payment-type__radio-item--active');
			if (selectedPaymentRadio && selectedPaymentRadio.data('type') === TYPE_OFFER_STAGES) {
				_selectedPaymentType = TYPE_OFFER_STAGES;
				_stagesPaymentTypeVisible = true;
				_paymentTypeVisible = true;
			} else if (selectedPaymentRadio && selectedPaymentRadio.data('type') === TYPE_OFFER_ALL) {
				_selectedPaymentType = TYPE_OFFER_ALL;
				_fullPaymentTypeVisible = true;
				_paymentTypeVisible = true;
			}
		} else if (price) {
			_fullPaymentTypeVisible = true;
		}

		if (offer.customMinPrice >= offer.stagesPriceThreshold) {
			_paymentTypeVisible = true;
		}

		if (offer.customMaxPrice < offer.stagesPriceThreshold) {
			_fullPaymentTypeVisible = true;
		}

		if ((_fullPaymentTypeVisible || _stagesPaymentTypeVisible) && _paymentTypeVisible) {
			$(_selectors.paymentType.hint).removeClass('hidden');
		}
	};

	/**
	 * Инициализация красивого селекта chosen
	 * @private
	 */
	let _initChosen = function () {
		$('.js-request-kwork-id').chosen({width: "100%", disable_search: true});
		$('.js-request-kwork-count').chosen({width: "100%", disable_search: true});
		$('.js-request-kwork-package-type').chosen({width: "100%", disable_search: true});
		$(_selectors.customKwork.duration).chosen({width: "100%", disable_search: true, display_disabled_options: false});
		$(_selectors.customKwork.categorySelect).chosen({width: "100%", disable_search: true});
		$(_selectors.customKwork.subCategorySelect).chosen({width: "100%", disable_search: true});

		$('.order-extras__select-block select.styled').chosen({width: "108px", disable_search: true});
	};

	/**
	 * Событие при изменении категории
	 * @private
	 */
	let _changeCategorySelect = function() {
		let parentId = parseInt($(this).val());
		$(_selectors.customKwork.subCategoryWrap).each(function () {
			if (parseInt($(this).data("category-id")) === parentId) {
				$(this).find('select').attr("name", "offer_category");
				$(this).find('select').attr("required", "required");
				$(this).removeClass("hidden");
			} else {
				$(this).find('select').attr("name", "");
				$(this).find('select').removeAttr("required");
				$(this).addClass("hidden");
			}
		});
	};

	/**
	 * Валидация индивидуального предложения и предложения кворка
	 * @param hideErrors
	 * @returns {boolean}
	 * @private
	 */
	let _validateIndividualKwork = function (hideErrors) {
		_hideErrorsBlock = (typeof hideErrors === "boolean") ? hideErrors : false;
		
		// если происходит ввод в поле, то отображать ошибку для этого поля
		if ($(this).is('input, select, textarea') && $(this).parents('.js-offer-individual-item') !== null) {
			let target = $(this).parents('.js-offer-individual-item').data('target');
			if (!_showErrorForBlock.includes(target)) {
				_showErrorForBlock.push($(this).parents('.js-offer-individual-item').data('target'));
			}
		}

		let errors = [];
		$(_selectors.errorBlock).html('');
		if ($(_selectors.activeKwork.block).length && !$(_selectors.activeKwork.block).hasClass("hidden")) {
			errors.push(_validateActiveKwork());
		} else {
			errors.push(_validateCustomKwork());
		}
		
		_showErrors();

		if (errors.indexOf(false) !== -1) {
			_buttonDisable();
			return false;
		}

		_buttonEnable();
		return true;
	};

	/**
	 * Валидация предложения кворка
	 * @returns {boolean}
	 * @private
	 */
	let _validateActiveKwork = function () {
		_hideErrors();
		_updateMinPrice(0);

		let kworkId = $(".js-request-kwork-id").val();
		if (!kworkId || kworkId === '') {
			return false;
		}

		if ($('input[name=want_id]').length && !$('input[name=want_id]:checked').length) {
			return false;
		}
	
		let emptyExtraInputCount = 0;
		let extraInputs = $('input[name="customExtraName[]"]');
		if (extraInputs) {
			emptyExtraInputCount = extraInputs.filter(function() {
				return !$(this).val();
			}).length;
		}

		if (emptyExtraInputCount > 0) {
			return false;
		}

		_calcActiveKworkPrice();
		if (_activeKworkPrice > offer.customMaxPrice || _activeKworkPrice < offer.customMinPrice) {
			if (offer.customMaxPrice === offer.customMinPrice) {
				$(_selectors.errorBlock).html(t('Цена услуги для данного заказа не может быть более {{0}}', [Utils.priceFormatWithSign(offer.customMaxPrice, offer.lang)]));
			} else {
				$(_selectors.errorBlock).html(t('Допустимая цена услуги для данного заказа от {{0}} до {{1}}', [Utils.priceFormatWithSign(offer.customMinPrice, offer.lang), Utils.priceFormatWithSign(offer.customMaxPrice, offer.lang)]));
			}

			return false;
		}

		if (!window.offerForm || !window.offerForm.isValidated(['offer-description'])) {
			return false;
		}

		return _validateDescription();
	};

	/**
	 * Валидация индивидуального предложения
	 * @returns {boolean}
	 * @private
	 */
	let _validateCustomKwork = function () {
		let errors = [];
		
		errors.push(_calcCustomKworkPrice());
		errors.push(_validateDescription());
		errors.push(_validateWantId());

		if (isNaN(parseInt($("input[name=want_id]:checked").val()))) {
			errors.push(_validateCategory());
			errors.push(_validateSubCategory());
		}

		if (offer.isOrderStageTester && _selectedPaymentType === TYPE_OFFER_STAGES) {
			window.offerStages.validateStages(true);
			errors.push(!window.offerStages.checkDisableButtonSend());
		} else {
			errors.push(_validateTitle());
		}

		if (!window.offerForm || !window.offerForm.isValidated(['offer-description', 'title', 'description'])) {
			return false;
		}

		if (offer.isOrderStageTester && _lastCustomKworkPrice >= offer.stagesPriceThreshold && !_selectedPaymentType) {
			return false;
		}

		return errors.indexOf(false) === -1;
	};

	/**
	 * Валидация названия в индивидуальном предложении
	 * @returns {boolean}
	 * @private
	 */
	let _validateTitle = function () {
		_hideErrors('title');

		if($(_selectors.customKwork.title).siblings('.js-content-editor').is(":visible")
			&& ($(_selectors.customKwork.title).val() === "" || StopwordsModule._testContacts($(_selectors.customKwork.title).val()).length !== 0)){

			_errors.push({
				target: "title",
				text: t('Введите название')
			});
			return false;
		}

		return true;
	};

	/**
	 * Валидация описания в индивидуальном предложении и предложении кворка
	 * @returns {boolean}
	 * @private
	 */
	let _validateDescription = function () {
		_hideErrors('description');

		if (!$(_selectors.customKwork.description).length) {
			return true;
		}

		let descriptionValue = $(_selectors.customKwork.description).val()
			.replace(/<\/?[^>]*>/gi, '')
			.replace(/&nbsp;/gi, " ")
			.replace(/\s\s+/g, " ")
			.trim();

		if (
			(!$(_selectors.customKwork.description).hasClass('js-ignore-min') && descriptionValue === "")
			|| (!$(_selectors.customKwork.description).hasClass('js-ignore-min') && descriptionValue.length < $(_selectors.customKwork.description).data('min'))
			|| descriptionValue.length > $(_selectors.customKwork.description).data('max')
			|| StopwordsModule._testContacts(descriptionValue).length !== 0
		){
			_errors.push({
				target: "description",
				text: ''
			});
			return false;
		}
		
		return true;
	};

	/**
	 * Валидация селекта категории в индивидуальном предложении в диалогах
	 * @returns {boolean}
	 * @private
	 */
	let _validateCategory = function () {
		if (!$(_selectors.customKwork.categorySelect).length) {
			return true;
		}

		_hideErrors('category');
		
		if ($(_selectors.customKwork.categoryWrap).length && $(_selectors.customKwork.categoryWrap).is(':visible') &&
			isNaN(parseInt($(_selectors.customKwork.categorySelect).val()))) {
			_errors.push({
				target: "category",
				text: t('Выберите рубрику')
			});
			return false;
		}

		return true;
	};

	/**
	 * Валидация селекта подкатегории в индивидуальном предложении в диалогах
	 * @returns {boolean}
	 * @private
	 */
	let _validateSubCategory = function () {
		if (!$(_selectors.customKwork.subCategoryWrap).length) {
			return true;
		}
		
		_hideErrors('sub_category');
		let categoryId= $(_selectors.customKwork.categorySelect).val();
		let $subCategoryWrap = $(_selectors.customKwork.subCategoryWrap).filter('[data-category-id="' + categoryId + '"]');
		let $subCategorySelect = $subCategoryWrap.find(_selectors.customKwork.subCategorySelect);

		_updateMinPrice($subCategorySelect.val());

		if ($subCategoryWrap.length && isNaN(parseInt($subCategorySelect.val()))) {

			return false;
		}

		return true;
	};

	/**
	 * Обновить минимальную цену индивидуального предложения по категории
	 * @param categoryId
	 * @private
	 */
	let _updateMinPrice = function(categoryId) {
		if (typeof minPrices !== "undefined") {
			if (minPrices.hasOwnProperty(categoryId)) {
				offer.customMinPrice = minPrices[categoryId];
			} else {
				offer.customMinPrice = minPrices[0];
			}

			if (typeof stageMinPrices !== "undefined") {
				if (stageMinPrices.hasOwnProperty(categoryId)) {
					offer.stageMinPrice = stageMinPrices[categoryId];
				} else {
					offer.stageMinPrice = stageMinPrices[0];
				}
			} else {
				offer.stageMinPrice = offer.customMinPrice;
			}

			if (offer.customMinPrice === offer.customMaxPrice) {
				$(_selectors.customKwork.price).attr("placeholder", Utils.priceFormat(offer.customMaxPrice, offer.lang));
			} else {
				$(_selectors.customKwork.price).attr("placeholder", Utils.priceFormat(offer.customMinPrice, offer.lang) + ' - ' + Utils.priceFormat(offer.customMaxPrice, offer.lang));
			}

			if (categoryId !== 0) {
				_calcCustomKworkPrice();

				if (offer.isOrderStageTester) {
					window.offerStages.updateOptionsStageMinPrice(offer.stageMinPrice);
					window.offerStages.updatePlaceholderPrice();
					window.offerStages.validateStagesPrices();
				}
			}
		}
	};

	/**
	 * Валидация привязки предложения к заказу
	 * @returns {boolean}
	 * @private
	 */
	let _validateWantId = function() {

		if ($('input[name=want_id]').length && !$('input[name=want_id]:checked').length) {
			return false;
		}

		_hideErrors('category');
		_hideErrors('sub_category');
		return true;
	};

	/**
	 * Валидация цены в индивидуальном предложении в диалогах
	 * @returns {boolean}
	 * @private
	 */
	let _validateCustomKworkPrice = function(){
		_hideErrors('price');
		
		let $obj = $(_selectors.customKwork.price);
		if (!$obj.is(":visible")) {
			return true;
		}

		if (/[^0-9]/ig.test($obj.val())) {
			$obj.val($obj.val().replace(/[^0-9]/ig, ''));
		}

		let price = parseInt($obj.val());

		if (isNaN(price)) {

			_errors.push({
				target: "price",
				text: t('Введите стоимость')
			});
			return false;
		}

		_lastCustomKworkPrice = price;

		if (price < offer.customMinPrice || price > offer.customMaxPrice) {
			if (offer.customMinPrice === offer.customMaxPrice) {
				_errors.push({
					target: "price",
					text: t('Стоимость должна быть равна {{0}}', [
							Utils.priceFormatWithSign(offer.customMinPrice, offer.lang)
						]
					)
				});
				return false;
			}

			_errors.push({
				target: "price",
				text: t('Стоимость может быть от {{0}} до {{1}}', [
						Utils.priceFormatWithSign(offer.customMinPrice, offer.lang),
						Utils.priceFormatWithSign(offer.customMaxPrice, offer.lang)
					]
				)
			});
			return false;
		}

		// Проверка на возможность заказ с задачами оплаты
		if (price >= offer.stagesPriceThreshold && offer.isOrderStageTester) {
			_showPaymentType();
		} else {
			_hidePaymentType();
			_showFullPaymentType();
		}

		return true;
	};

	/**
	 * Обновление общей стоимости инд. предложения. Актуально только при отправке предложения
	 */
	_updateCustomPrice = function() {
		let selectedPaymentActiveType = $('.js-new-offer-radio.offer-payment-type__radio-item--active').data('type');
		if (selectedPaymentActiveType === TYPE_OFFER_STAGES) {
			window.offerStages.updateCustomPrice();
		}
	};

	/**
	 * Показать элементы выбора типа оплаты
	 * @private
	 */
	let _showPaymentType = function () {
		if (_paymentTypeVisible) {
			return true;
		}

		_paymentTypeVisible = true;
		$(_selectors.paymentType.wrapper).removeClass('hidden');
		_hideFullPaymentType();
		_hideStagesPaymentType();
		
		_selectPaymentType(null);
	};

	/**
	 * Скрыть элементы выбора типа оплаты
	 * @private
	 */
	let _hidePaymentType = function () {
		if (!_paymentTypeVisible) {
			return true;
		}

		_paymentTypeVisible = false;
		$(_selectors.paymentType.wrapper).addClass('hidden');
		_selectPaymentType(null);
	};

	/**
	 * Показать элементы оплаты проекта целиком
	 * @private
	 */
	let _showFullPaymentType = function () {
		if (_fullPaymentTypeVisible) {
			return true;
		}

		_fullPaymentTypeVisible = true;
		$(_selectors.paymentType.full).removeClass('hidden');
		_changeStageDurationVisibility(false);
	};

	/**
	 * Скрыть элементы оплаты проекта целиком
	 * @private
	 */
	let _hideFullPaymentType = function () {
		if (!_fullPaymentTypeVisible) {
			return true;
		}

		_fullPaymentTypeVisible = false;
		$(_selectors.paymentType.full).addClass('hidden');
	};

	/**
	 * Показать элементы оплаты проекта заказа с задачами
	 * @private
	 */
	let _showStagesPaymentType = function () {
		if (_stagesPaymentTypeVisible) {
			return true;
		}

		_stagesPaymentTypeVisible = true;
		$(_selectors.paymentType.stages).removeClass('hidden');
		_changeStageDurationVisibility(true);
	};

	/**
	 * Скрыть элементы оплаты проекта заказа с задачами
	 * @private
	 */
	let _hideStagesPaymentType = function () {
		if (!_stagesPaymentTypeVisible) {
			return true;
		}

		_stagesPaymentTypeVisible = false;
		$(_selectors.paymentType.stages).addClass('hidden');
	};

	/**
	 * Добавить / удалить из отправки элементы
	 * @private
	 * @param paymentType - тип отправки
	 */
	let _setSerialize = function (paymentType) {
		if (paymentType === TYPE_OFFER_STAGES) {
			$('.js-stage-name').removeClass('no-serialize');
			$('.js-stage-price').removeClass('no-serialize');
			$('.js-kwork-title').addClass('no-serialize');
			$('.js-kwork-volume').addClass('no-serialize');
		} else {
			$('.js-kwork-title').removeClass('no-serialize');
			$('.js-kwork-volume').removeClass('no-serialize');
			$('.js-stage-name').addClass('no-serialize');
			$('.js-stage-price').addClass('no-serialize');
		}
	};

	/**
	 * Выбрать тип оплаты
	 * @param event - событие клика
	 * @private
	 */
	let _selectPaymentType = function (event) {
		let $element = $(event && event.currentTarget);
		let paymentType = $element && $element.data('type');

		$(_selectors.paymentType.radio).removeClass('offer-payment-type__radio-item--active');
		$element.addClass('offer-payment-type__radio-item--active');
		$(_selectors.paymentType.hint).removeClass('hidden');

		if (paymentType === TYPE_OFFER_STAGES) {
			_selectedPaymentType = TYPE_OFFER_STAGES;
			_hideFullPaymentType();
			_showStagesPaymentType();
			_validateIndividualKwork();
			_updateCustomPrice();
		} else if (paymentType === TYPE_OFFER_ALL) {
			_selectedPaymentType = TYPE_OFFER_ALL;
			_showFullPaymentType();
			_hideStagesPaymentType();
			_validateIndividualKwork();
		} else {
			_selectedPaymentType = null;
			$(_selectors.paymentType.hint).addClass('hidden');
			_hideStagesPaymentType();
			_hideFullPaymentType();
		}
	};

	/**
	 * Активировать / деактивировать сроки выполнения для заказа с задачами оплаты
	 */
	let _changeStageDurationVisibility = function (isVisible) {
		if (isVisible) {
			$(_selectors.customKwork.stageDuration).removeAttr('disabled');
		} else {
			$(_selectors.customKwork.stageDuration).attr('disabled', 'disabled');
		}
		$(_selectors.customKwork.duration).trigger('chosen:updated');
	};

	/**
	 * Показать ошибки в индивидуальном предложении и предложении кворка
	 * @private
	 */
	let _showErrors = function () {
		_buttonDisable();
		if (_hideErrorsBlock) {
			return;
		}
		$.each(_errors, function (k, v) {
			if (v.text && _showErrorForBlock.includes(v.target)) {
				$(_selectors.individualKwork).find('[data-target="' + v.target + '"] .js-target-error').html(v.text).data('isTemp', true);
			}
		})
	};

	/**
	 * Показать ошибки, которые пришли с бэка в индивидуальном предложении и предложении кворка
	 * @param response
	 * @private
	 */
	let _showBackendErrors = function (response) {
		_buttonDisable();
		if (response.errors) {
			$.each(response.errors, function (k, v) {
				$(_selectors.individualKwork).find('[data-target="' + v.target + '"] .js-target-error').html(v.text);
			})
		} else {
			$(_selectors.errorBlock).html(response.error);
		}

		if (offer.isOrderStageTester && response.errors) {
			window.offerStages.showBackendStageErrors(response.errors);
		}
	};


	let _cleanBlockErrors = function(errorBlock) {
		if (errorBlock.data('isTemp') == true || !errorBlock.hasClass('no-hide')) {
			errorBlock.html('');
		}
	}

	/**
	 * Скрыть ошибки по target
	 * @param target
	 * @private
	 */
	let _hideErrors = function (target) {
		if (target) {
			let errorBlock = $(_selectors.individualKwork).find('[data-target="' + target + '"] .js-target-error');
			
			_cleanBlockErrors(errorBlock);
			_errors = _errors.filter(function(v) { return v.target !== target })
		} else {
			$(_selectors.individualKwork).find('.js-target-error').each(function(k, v) {
				var errorBlock = $(v);
				_cleanBlockErrors(errorBlock);
			});
		}

		_buttonEnable();
	};

	/**
	 * Привести к дефолту переменные _hideErrorsBlock, _showErrorForBlock и _errors
	 * @private
	 */
	let _clearShowErrorForBlock = function() {
		_hideErrorsBlock = false;
		_errors = [];
		_showErrorForBlock = [];
	};

	/**
	 * Заблокировать кнопку отправки
	 * @private
	 */
	let _buttonDisable = function () {
		$('.btn-disable-toggle').prop('disabled', true).addClass('disabled');
	};

	/**
	 * Разблокировать кнопку отправки
	 * @private
	 */
	let _buttonEnable = function () {
		$('.btn-disable-toggle').prop('disabled', false).removeClass('disabled');
	};

	/**
	 * Показать попап комиссии
	 * @private
	 */
	let _showPopupCommission = function () {

		if (typeof price === "undefined"){
			price = $(this).data("price");
		}

		let toPrice = function (price) {
			return Utils.priceFormatWithSign(price, offer.lang, undefined, '₽');
		};

		let commission = $(this).data('commission');
		if (typeof commission === "undefined"){
			commission = calculateCommission(price, turnover);
		}

		let html = '\
		<h1>' + t('Комиссия Kwork') + '</h1>\
		\
		<p>' + t('Комиссия в Kwork снижается по мере того, как растет ваш оборот с конкретным клиентом. Чем больше ваша сумма сделок с отдельным покупателем, тем ниже комиссия Kwork.') + '</p>\
		\
		<p class="mt15">' + t('В прошлом оборот с этим клиентом составил:') + '</p>\
		<p><strong>' + toPrice(commission.turnover) + '</strong></p>\
		\
		<p class="mt15">' + t('Ваша цена за работу:') + '</p>\
		<p><strong>' + toPrice(commission.price) + '</strong></p>\
		\
		<table class="mt15">\
			<thead>\
				<tr>\
					<th>' + t('Оборот с клиентом') + '</th>\
					<th>' + t('Комиссия') + '</th>\
					<th>' + t('Ваша цена') + '</th>\
					<th>' + t('Вы получите') + '</th>\
				</tr>\
			</thead>\
			<tbody>\
	';

		for (let i = 0; i < commission.ranges.length; i++) {
			let range = commission.ranges[i];
			html += '\
				<tr' + (range.price ? '' : ' class="disabled"') + '>\
					<td>' + range.title + '</td>\
					<td>' + (range.percentage * 100) + '%</td>\
					<td>' + (range.price ? toPrice(range.price) : '') + '</td>\
					<td>' + (range.priceWorker ? toPrice(range.priceWorker) : 'n/a') + '</td>\
				</tr>\
		';
		}

		html += '\
			</tbody>\
			<tfoot>\
				<tr>\
					<td colspan="2"></td>\
					<td>' + t('Итого: ') + toPrice(commission.price) + '</td>\
					<td>' + t('Итого: ') + toPrice(commission.priceWorker) + '</td>\
				</tr>\
			</tfoot>\
		</table>\
		<div class="ta-center">\
			<button class="popup__button popup-close-js green-btn m-wMax w250">' + t('Закрыть') + '</button>\
		</div>\
	';

		show_popup(html, undefined, undefined, 'kwork-price-explanation-popup');
	};

	/**
	 * Выбрать активный кворк для предложения кворка
	 * @private
	 */
	let _selectActiveKwork = function () {
		$('.js-request-kwork-id .empty').remove();
		let pid = $('.js-request-kwork-id option:checked').val();
		$('.order-extras li').hide();

		let orderExtrasItem = $('.order-extras li[data-pid=' + pid + ']');
		if (orderExtrasItem.length > 0) {
			orderExtrasItem.show();
			_calcActiveKworkPrice();
		} else if (pid) {
			if (_loadExtrasXhr) {
				_loadExtrasXhr.abort();
			}
			$('.order-extras__list input[type=checkbox]').prop('checked', false).trigger('change');
			_loadExtrasXhr = $.ajax({
				url: '/api/extras/getbykwork',
				type: 'post',
				dataType: 'json',
				data: {'kwork_id': pid},
				success: function (response) {
					_loadExtrasXhr = null;
					if (response.success === true) {
						for (let i in response.extras) {
							if (response.extras.hasOwnProperty(i)) {
								/**
								 * @type {
								 *          {
								 *              id: {integer},
								 *              payerPrice: {integer},
								 *              price: {object},
								 *              name: {string},
								 *              duration: {integer},
								 *          }
								 *       }
								 */
								let extra = response.extras[i];
								let html = "";
								html = '<li class="order-extras__item order-extras__item_old" data-pid="' + pid + '" data-id="' + extra.id + '" style="display:none;">' +
									'<input class="order-extras__input styled-checkbox" data-payer-price="' + extra.payerPrice + '" data-price="' + extra.price + '" data-time="' + extra.duration + '" data-id="' + extra.id + '" name="gextras[]" type="checkbox" value="' + extra.id + '">' +
									'<label for="order-extras_' + extra.id + '" class="w460">' + extra.name.ucFirst() + '</label>' +
									'<div class="order-extras__select-block">' +
									'<select class="js-order-extras__count styled input input_size_s" data-affected-checkbox="' + extra.id + '" id="extra_count' + extra.id + '" name="extra_count' + extra.id + '">';
								for (let j = 0; j < offer.maxKworkCount; j++) {
									let value = j + 1;
									let commission = calculateCommission(extra.payerPrice * value, turnover);
									let price = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, "&nbsp;", "Р");
									html += '<option value="' + value + '">' + value + ' (' + price + ')</option>';
								}
								html += '</select>' +
									'</div>' +
									'</li>';
								$('.order-extras__list').prepend(html);
								$('.js-order-extras__count[data-affected-checkbox]').on('change', function (e) {
									let t = $(e.delegateTarget);
									$('.order-extras__item_old[data-id="' + t.data('affected-checkbox') + '"] .order-extras__input').prop('checked', true);
								});
								$('.order-extras li[data-pid=' + pid + ']').show();
								$('.order-extras li[data-pid=' + pid + '] select').chosen({
									width: "108px",
									disable_search: true
								});
							}
						}
						_calcActiveKworkPrice();
					}
				}
			});
		}
		$('.order-extras li.custom').show();
	};

	/**
	 * Посчитать итоговую цену для предложения кворка
	 * @returns {boolean}
	 * @private
	 */
	let _calcActiveKworkPrice = function () {

		let $requestKworkId = $('.js-request-kwork-id option:selected');
		let isPackage = $requestKworkId.data('package');
		let rate = $requestKworkId.data('rate') || offer.multiKworkRate;

		let count;
		let payerSum;
		let days;
		if (isPackage !== 1) {
			count = $('.js-request-kwork-count').val() ^ 0;
			payerSum = count * $requestKworkId.data('price');
			days = $requestKworkId.data('time') ^ 0;
		} else {
			let packageType = $(".js-request-kwork-package-type").val();
			if (!packageType) {
				return false;
			}

			let pack = offer.kworkPackages[$('.js-request-kwork-id').val()][packageType];
			count = 1;
			payerSum = parseFloat(pack.payerPrice);
			days = pack.duration ^ 0;
		}

		if ($requestKworkId.data('base-volume') > 0) {
			let base = $requestKworkId.data('base-volume');
			let volume = base * count;
			days = getVolumedDuration(days, base, volume, 'offer');
		} else {
			days = getDuration(days, count, rate);
		}

		// Опции кворка
		$('.order-extras__item_old').each(function () {
			let $item = $(this);
			let $checkbox = $item.find('.order-extras__input');
			let $select = $('#extra_count' + $checkbox.val());

			let payerPrice = $checkbox.data('payerPrice') ^ 0;
			let time = $checkbox.data('time') ^ 0;
			let count = $select.val() ^ 0;

			let commission = calculateCommission(payerPrice, turnover);
			$checkbox.data("price", commission.priceWorker);

			$select.find('option').each(function () {
				let count = parseInt($(this).prop('value'));
				let commission = calculateCommission(payerPrice * count, turnover);
				let workerSumString = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, ' ', 'Р');
				$(this).text(count + ' (' + workerSumString + ')');
			});

			$select.trigger('chosen:updated');

			if ($checkbox.is(':checked')) {
				payerSum += payerPrice * count;
				days += getDuration(time, count, rate);
			}
		});

		// Добавленные опции
		$('.order-extras__item.custom').each(function () {
			let $item = $(this);
			let $selectPrice = $item.find('.js-new-extra-price');
			let $selectDays = $item.find('.js-new-extra-days');

			let payerPrice = $selectPrice.find('option:checked').data('payerValue') ^ 0;
			let time = $selectDays.val() ^ 0;
			let count = 1;

			$selectPrice.find('option').each(function () {
				let price = parseFloat($(this).data('payerValue'));
				let commission = calculateCommission(price, turnover);
				let workerSumString = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, ' ', 'Р');
				$(this)
					.text(workerSumString)
					.data('seller-value', commission.priceWorker)
					.attr('data-seller-value', commission.priceWorker);
			});

			$selectPrice.trigger('chosen:updated');

			payerSum += payerPrice * count;
			days += getDuration(time, count, rate);
		});

		_processCustomOptionsPrices(turnover);

		let commission = calculateCommission(payerSum, turnover);
		let workerSum = commission.priceWorker;

		let payerSumString = Utils.priceFormat(payerSum, offer.lang, '&nbsp;');
		let workerSumString = Utils.priceFormat(workerSum, offer.lang, '&nbsp;');

		_activeKworkPrice = payerSum;

		if (offer.lang === 'ru') {
			payerSumString += '&nbsp;<span class="rouble">Р</span>';
			workerSumString += '&nbsp;<span class="rouble">Р</span>';
		} else {
			payerSumString = '<span class="usd">$</span>' + payerSumString;
			workerSumString = '<span class="usd">$</span>' + workerSumString;
		}

		$('.js-total-sum').html(t('Стоимость: ') + payerSumString);

		let message = t('Вы получите') + ': ' + workerSumString +
			' <a href="javascript:;" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';
		let totalWorkerSum = $('.js-total-worker-sum');
		totalWorkerSum.html(message);
		let $suggestForm = totalWorkerSum.closest('.js-active-kwork, .js-individual-kwork');
		$suggestForm.find('.js-price-explanation').data('commission', commission);

		$('.js-total-time').html(days + ' ' + Utils.declOfNum(days, [t('день'), t('дня'), t('дней')]));
	};

	/**
	 * Посчитать итоговую цену для индивидуального предложения
	 * @returns {boolean}
	 * @private
	 */
	let _calcCustomKworkPrice = function() {
		let $obj = $(_selectors.customKwork.price);
		let $desc = $('#kwork-price-description');

		if (!$obj.is(":visible")) {
			return true;
		}

		let isValidate = _validateCustomKworkPrice();
		if (isValidate) {
			let price = $obj.val() ^ 0;
			let commission = calculateCommission(price, turnover);
			let ctp = price - commission.priceKwork;
			let message = t('Вы получите {{0}}', [Utils.priceFormatWithSign(ctp, offer.lang)]);
			message += ' <a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';
			$desc.html(message);
			let $suggestForm = $obj.closest('.js-custom-kwork, .js-individual-kwork');
			$suggestForm.find('.js-price-explanation').data('commission', commission);
			
			return true;
		} else {
			$desc.html('');

			return false;
		}
	};

	/**
	 * Добавить опцию в предложении кворка
	 * @private
	 */
	let _showCustomOptionForm = function() {
		let el = $(this);
		let html =
			'<li class="order-extras__item extrasping-settings extra-field-block-css extra-edit-block-css custom">' +
			'<div class="offer-sprite offer-sprite-option pull-left m-hidden mt2"></div>' +
			'<input name="customExtraName[]" maxlength="50" value="" style="width:340px;" class="styled-input f14 pull-left m-wMax m-border-box order-new-extras__input" type="text">' +
			'<div class="new-order-extras__select-block">' +
			'<select id="extratime" class="js-new-extra-select js-new-extra-days input input_size_s" name="customExtraTime[]" style="width:16%;">' +
			'<option value="0"> ' + t('{{0}} дней', [0]) + ' </option>' +
			'<option value="1"> ' + t('{{0}} день', [1]) + ' </option>' +
			'<option value="2"> ' + t('{{0}} дня', [2]) + ' </option>' +
			'<option value="3"> ' + t('{{0}} дня', [3]) + ' </option>' +
			'<option value="4"> ' + t('{{0}} дня', [4]) + ' </option>' +
			'<option value="5"> ' + t('{{0}} дней', [5]) + ' </option>' +
			'<option value="6"> ' + t('{{0}} дней', [6]) + ' </option>' +
			'<option value="7"> ' + t('{{0}} дней', [7]) + ' </option>' +
			'<option value="8"> ' + t('{{0}} дней', [8]) + ' </option>' +
			'<option value="9"> ' + t('{{0}} дней', [9]) + ' </option>' +
			'<option value="10"> ' + t('{{0}} дней', [10]) + ' </option>' +
			'</select>' +
			'<div class="custom-option-price"><select name="customExtraPrice[]" class="js-new-extra-select js-new-extra-price input input_size_s" style="width:16%;">' +
			offer.customPricesOptionsHtml +
			'</select></div>' +
			'</div>' +
			'<div class="js-new-order-extras-delete new-order-extras__delete del-opt cur ml6 dib" data-id="0" title="' + t('Удалить') + '">' +
			'<i class="ico-close-14"></i>' +
			'</div>' +
			'<div class="clear"></div>' +
			'</li>';
		let $html = $(html);

		$html.find('.js-new-extra-price').chosen({width: "100px", disable_search: true});
		$html.find('.js-new-extra-days').chosen({width: "80px", disable_search: true});
		$('.order-extras__list').append($html);
		$('#message_body').trigger('input');
		$(el).text(t('Создать еще опцию'));
		_calcActiveKworkPrice();
		_buttonDisable();
	};

	/**
	 * Удалить опцию в предложении кворка
	 * @private
	 */
	let _removeCustomOptionForm = function() {
		let target = $(this);

		target.closest('li').remove();
		$('#message_body').trigger('input');
		if ($('.js-add-extras li.extrasping-settings').length < 1) {
			$('#order-extras__create').text(t('Создать опцию'));
		}
		_calcActiveKworkPrice();
		_validateIndividualKwork();
	};

	let _calculateCustomExtrasPrices = function() {
		var price = 0;
		var customExtras = $(".order-extras__list li.custom");
		for (var i = 0; i < customExtras.length; i++) {
			var $customExtra = $(customExtras[i]);
			price += clearPriceStr($customExtra.find('select.js-new-extra-price option:selected').data('sellerValue'));
		}
		return price;
	};

	let _processCustomOptionsPrices = function(currentTurnover) {
		// Удалим все подсказки про опции чтобы избежать дублей
		$(".max-custom-options-tooltip").remove();

		let $customExtras = $(".order-extras__list li.custom");

		// Посчитаем максимальную допустимую суппу доп. опций для продавкца с учётом комиссии
		let commissionMax = calculateCommission(window.maxOptionsSum, currentTurnover);

		// Посчитаем сумму доп. опций
		let totalCustom = _calculateCustomExtrasPrices();
		let totalKwork = 0;
		$("li.order-extras__item_old").each(function () {
			if ($(this).is(":visible")) {
				$input = $(this).find("input.order-extras__input");
				totalKwork += $input.data("price");
			}
		});
		let total = totalCustom + totalKwork;

		// Если заказана хотябы одна опция, добавим подсказу
		if (totalCustom > 0) {
			var workerMaxSumString = Utils.priceFormatWithSign(commissionMax.priceWorker, offer.lang, " ", "руб.");
			let workerTotalSumString = Utils.priceFormatWithSign(total, offer.lang, " ", "руб.");
			var tooltip = "Общая цена всех доп. опций - до " + workerMaxSumString + " Сейчас " + workerTotalSumString;
			$customExtras
				.last()
				.parent()
				.append("<div class='max-custom-options-tooltip'>" + tooltip + "</div>");
		}

		let $submitButton = $("#track-send-message-button");
		if (total <= commissionMax.priceWorker) {
			// Если сумма проходит проверку, то активируем кнопку отправки предожения
			$submitButton.removeClass("btn_disabled").prop("disabled", false);
			// Уберем красную обводку с цен доп. опций
			$customExtras.removeClass("custom-price-error");
			// Если сумма проходит все проверки, то показываем кнопку добавления опции
			$(".js-order-extras-create").show();
		} else {
			// Если сумма доп. опций превышает допустимую, то:
			// - отключить кнопку отправки предложения
			$submitButton.addClass("btn_disabled").prop("disabled", true);
			// - досветить цены доп. опций красным
			$customExtras.addClass("custom-price-error");
			// - подсветим подсказку красным если она ещё не красная
			$(".max-custom-options-tooltip")
				.addClass("error")
				.html(tooltip + "<br>Уменьшите стоимость предыдущих опций, чтобы добавить еще одну");
			// - спрятать кнопку добавления опции
			$(".js-order-extras-create").hide();
		}
	};

	return {
		init: _initEvent,
		btnDisable: _buttonDisable,
		btnEnable: _buttonEnable,
		validateIndividualKwork: _validateIndividualKwork,
		showBackendError: _showBackendErrors,
		clearShowErrorForBlock: _clearShowErrorForBlock,
		setSerialize: _setSerialize,
		hidePaymentType: _hidePaymentType,
		selectPaymentType: _selectPaymentType,
	}
})();
