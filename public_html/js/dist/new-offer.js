/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 20);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/app/offer-individual.js":
/*!************************************************!*\
  !*** ./public_html/js/app/offer-individual.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

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
window.OfferIndividualModule = function () {
  /**
   * Оплата заказа по задачам
   * @type {string}
   * @const
   */
  var TYPE_OFFER_STAGES = 'stages';
  /**
   * Полная оплата
   * @type {string}
   * @const
   */

  var TYPE_OFFER_ALL = 'all';
  var _hideErrorsBlock = false;
  var _errors = [];
  var _showErrorForBlock = [];
  var _loadExtrasXhr = null;
  var _selectors = {
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
      price: '.js-kwork-price'
    },
    activeKwork: {
      block: '.js-active-kwork'
    },
    paymentType: {
      wrapper: '.js-offer-payment-type',
      full: '.js-full-payment-type',
      stages: '.js-stages-payment-type',
      radio: '.js-new-offer-radio',
      hint: '.js-payment-type-hint'
    }
  };
  /** стоимость предложения кворка */

  var _activeKworkPrice = 0;
  /** стоимость индивидуального предложения */

  var _lastCustomKworkPrice = 0;
  /** отображать или нет элементы выбора типа оплаты */

  var _paymentTypeVisible = false;
  /** показывать полный типа оплаты? */

  var _fullPaymentTypeVisible = false;
  /** показывать задачный типа оплаты? */

  var _stagesPaymentTypeVisible = false;
  /** какой тип опалты выбран */

  var _selectedPaymentType = null;
  /**
   * Инициализация событий
   * @private
   */

  var _initEvent = function _initEvent() {
    _initChosen();

    $(document) // события для предложения кворка
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
    }).on('change', '.js-request-kwork-count, .js-request-kwork-package-type, .order-extras__input, .js-order-extras__count, .js-new-extra-select', function () {
      _calcActiveKworkPrice();

      _validateIndividualKwork();
    }).on('click', '.order-extras__item', function (e) {
      var checkboxOrderExtras = $('.styled-checkbox[data-id=' + $(this).data('id') + ']');

      if (checkboxOrderExtras.prop('checked')) {
        if (!$(e.target).is('.chosen-container') && $(e.target).closest('.chosen-container').length === 0) {
          checkboxOrderExtras.prop('checked', false).trigger('change');
        }
      } else {
        checkboxOrderExtras.prop('checked', true).trigger('change');
      }
    }).on('change', '.js-new-extra-price', function () {
      var price = $(this).find('option:checked').data('sellerValue');
      var $countSelect = $(this).closest('.new-order-extras__select-block').find('.js-new-extra-count');
      $countSelect.find('option').each(function () {
        var sum = price * $(this).attr('value');
        $(this).text($(this).attr('value') + "(" + Utils.priceFormatWithSign(sum, offer.lang) + ")");
      });
      $countSelect.trigger("chosen:updated");
    }).on('change', '.js-request-kwork-id', _selectActiveKwork).on('click', '.js-new-order-extras-delete', _removeCustomOptionForm).on('click', '.js-order-extras-create', _showCustomOptionForm).on('input', 'input[name=want_id]', _validateIndividualKwork).on('input', 'input[name="customExtraName[]"]', _validateIndividualKwork) // события для индивидуального предложения
    .on('change', _selectors.customKwork.categorySelect, _changeCategorySelect).on('change', _selectors.customKwork.duration, _validateIndividualKwork).on('input', _selectors.customKwork.title, _validateIndividualKwork).on('input', _selectors.customKwork.volume, _validateIndividualKwork).on('input', _selectors.customKwork.price, _validateIndividualKwork).on('blur', _selectors.customKwork.price, _updateCustomPrice).on('input', _selectors.customKwork.categorySelect, _validateIndividualKwork).on('input', _selectors.customKwork.subCategorySelect, _validateIndividualKwork) // события для предложения кворка и индивидуального предложения
    .on('input', _selectors.customKwork.description, _validateIndividualKwork).on('click', '.js-price-explanation', _showPopupCommission) //Переключение на предложить кворк
    .on('click', '#change-to-kwork-choose', function () {
      $('#suggestCustomKworkToggle, #change-to-kwork-choose').addClass('hidden');
      $('#suggestKworkToggle, #change-to-custom-kwork').removeClass('hidden');
    }) //Переключение на индивидуальное предложение
    .on('click', '#change-to-custom-kwork', function () {
      $('#suggestKworkToggle, #change-to-custom-kwork').addClass('hidden');
      $('#suggestCustomKworkToggle, #change-to-kwork-choose').removeClass('hidden');
    }); // Переключение типов оплаты

    $(_selectors.paymentType.radio).on('click', _selectPaymentType);

    if (offer.isOrderStageTester) {
      window.offerStages.onChangeStage = function () {
        _validateIndividualKwork(false);
      };
    }

    _initPaymentType();
  };
  /**
   * Инициализация выбора типа оплаты
   * @private
   */


  var _initPaymentType = function _initPaymentType() {
    var price = parseInt($(_selectors.customKwork.price).val());

    if (price && price >= offer.stagesPriceThreshold) {
      var selectedPaymentRadio = $('.js-new-offer-radio.offer-payment-type__radio-item--active');

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


  var _initChosen = function _initChosen() {
    $('.js-request-kwork-id').chosen({
      width: "100%",
      disable_search: true
    });
    $('.js-request-kwork-count').chosen({
      width: "100%",
      disable_search: true
    });
    $('.js-request-kwork-package-type').chosen({
      width: "100%",
      disable_search: true
    });
    $(_selectors.customKwork.duration).chosen({
      width: "100%",
      disable_search: true,
      display_disabled_options: false
    });
    $(_selectors.customKwork.categorySelect).chosen({
      width: "100%",
      disable_search: true
    });
    $(_selectors.customKwork.subCategorySelect).chosen({
      width: "100%",
      disable_search: true
    });
    $('.order-extras__select-block select.styled').chosen({
      width: "108px",
      disable_search: true
    });
  };
  /**
   * Событие при изменении категории
   * @private
   */


  var _changeCategorySelect = function _changeCategorySelect() {
    var parentId = parseInt($(this).val());
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


  var _validateIndividualKwork = function _validateIndividualKwork(hideErrors) {
    _hideErrorsBlock = typeof hideErrors === "boolean" ? hideErrors : false; // если происходит ввод в поле, то отображать ошибку для этого поля

    if ($(this).is('input, select, textarea') && $(this).parents('.js-offer-individual-item') !== null) {
      var target = $(this).parents('.js-offer-individual-item').data('target');

      if (!_showErrorForBlock.includes(target)) {
        _showErrorForBlock.push($(this).parents('.js-offer-individual-item').data('target'));
      }
    }

    var errors = [];
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


  var _validateActiveKwork = function _validateActiveKwork() {
    _hideErrors();

    _updateMinPrice(0);

    var kworkId = $(".js-request-kwork-id").val();

    if (!kworkId || kworkId === '') {
      return false;
    }

    if ($('input[name=want_id]').length && !$('input[name=want_id]:checked').length) {
      return false;
    }

    var emptyExtraInputCount = 0;
    var extraInputs = $('input[name="customExtraName[]"]');

    if (extraInputs) {
      emptyExtraInputCount = extraInputs.filter(function () {
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


  var _validateCustomKwork = function _validateCustomKwork() {
    var errors = [];
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


  var _validateTitle = function _validateTitle() {
    _hideErrors('title');

    if ($(_selectors.customKwork.title).siblings('.js-content-editor').is(":visible") && ($(_selectors.customKwork.title).val() === "" || StopwordsModule._testContacts($(_selectors.customKwork.title).val()).length !== 0)) {
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


  var _validateDescription = function _validateDescription() {
    _hideErrors('description');

    if (!$(_selectors.customKwork.description).length) {
      return true;
    }

    var descriptionValue = $(_selectors.customKwork.description).val().replace(/<\/?[^>]*>/gi, '').replace(/&nbsp;/gi, " ").replace(/\s\s+/g, " ").trim();

    if (!$(_selectors.customKwork.description).hasClass('js-ignore-min') && descriptionValue === "" || !$(_selectors.customKwork.description).hasClass('js-ignore-min') && descriptionValue.length < $(_selectors.customKwork.description).data('min') || descriptionValue.length > $(_selectors.customKwork.description).data('max') || StopwordsModule._testContacts(descriptionValue).length !== 0) {
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


  var _validateCategory = function _validateCategory() {
    if (!$(_selectors.customKwork.categorySelect).length) {
      return true;
    }

    _hideErrors('category');

    if ($(_selectors.customKwork.categoryWrap).length && $(_selectors.customKwork.categoryWrap).is(':visible') && isNaN(parseInt($(_selectors.customKwork.categorySelect).val()))) {
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


  var _validateSubCategory = function _validateSubCategory() {
    if (!$(_selectors.customKwork.subCategoryWrap).length) {
      return true;
    }

    _hideErrors('sub_category');

    var categoryId = $(_selectors.customKwork.categorySelect).val();
    var $subCategoryWrap = $(_selectors.customKwork.subCategoryWrap).filter('[data-category-id="' + categoryId + '"]');
    var $subCategorySelect = $subCategoryWrap.find(_selectors.customKwork.subCategorySelect);

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


  var _updateMinPrice = function _updateMinPrice(categoryId) {
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


  var _validateWantId = function _validateWantId() {
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


  var _validateCustomKworkPrice = function _validateCustomKworkPrice() {
    _hideErrors('price');

    var $obj = $(_selectors.customKwork.price);

    if (!$obj.is(":visible")) {
      return true;
    }

    if (/[^0-9]/ig.test($obj.val())) {
      $obj.val($obj.val().replace(/[^0-9]/ig, ''));
    }

    var price = parseInt($obj.val());

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
          text: t('Стоимость должна быть равна {{0}}', [Utils.priceFormatWithSign(offer.customMinPrice, offer.lang)])
        });

        return false;
      }

      _errors.push({
        target: "price",
        text: t('Стоимость может быть от {{0}} до {{1}}', [Utils.priceFormatWithSign(offer.customMinPrice, offer.lang), Utils.priceFormatWithSign(offer.customMaxPrice, offer.lang)])
      });

      return false;
    } // Проверка на возможность заказ с задачами оплаты


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


  _updateCustomPrice = function _updateCustomPrice() {
    var selectedPaymentActiveType = $('.js-new-offer-radio.offer-payment-type__radio-item--active').data('type');

    if (selectedPaymentActiveType === TYPE_OFFER_STAGES) {
      window.offerStages.updateCustomPrice();
    }
  };
  /**
   * Показать элементы выбора типа оплаты
   * @private
   */


  var _showPaymentType = function _showPaymentType() {
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


  var _hidePaymentType = function _hidePaymentType() {
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


  var _showFullPaymentType = function _showFullPaymentType() {
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


  var _hideFullPaymentType = function _hideFullPaymentType() {
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


  var _showStagesPaymentType = function _showStagesPaymentType() {
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


  var _hideStagesPaymentType = function _hideStagesPaymentType() {
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


  var _setSerialize = function _setSerialize(paymentType) {
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


  var _selectPaymentType = function _selectPaymentType(event) {
    var $element = $(event && event.currentTarget);
    var paymentType = $element && $element.data('type');
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


  var _changeStageDurationVisibility = function _changeStageDurationVisibility(isVisible) {
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


  var _showErrors = function _showErrors() {
    _buttonDisable();

    if (_hideErrorsBlock) {
      return;
    }

    $.each(_errors, function (k, v) {
      if (v.text && _showErrorForBlock.includes(v.target)) {
        $(_selectors.individualKwork).find('[data-target="' + v.target + '"] .js-target-error').html(v.text).data('isTemp', true);
      }
    });
  };
  /**
   * Показать ошибки, которые пришли с бэка в индивидуальном предложении и предложении кворка
   * @param response
   * @private
   */


  var _showBackendErrors = function _showBackendErrors(response) {
    _buttonDisable();

    if (response.errors) {
      $.each(response.errors, function (k, v) {
        $(_selectors.individualKwork).find('[data-target="' + v.target + '"] .js-target-error').html(v.text);
      });
    } else {
      $(_selectors.errorBlock).html(response.error);
    }

    if (offer.isOrderStageTester && response.errors) {
      window.offerStages.showBackendStageErrors(response.errors);
    }
  };

  var _cleanBlockErrors = function _cleanBlockErrors(errorBlock) {
    if (errorBlock.data('isTemp') == true || !errorBlock.hasClass('no-hide')) {
      errorBlock.html('');
    }
  };
  /**
   * Скрыть ошибки по target
   * @param target
   * @private
   */


  var _hideErrors = function _hideErrors(target) {
    if (target) {
      var errorBlock = $(_selectors.individualKwork).find('[data-target="' + target + '"] .js-target-error');

      _cleanBlockErrors(errorBlock);

      _errors = _errors.filter(function (v) {
        return v.target !== target;
      });
    } else {
      $(_selectors.individualKwork).find('.js-target-error').each(function (k, v) {
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


  var _clearShowErrorForBlock = function _clearShowErrorForBlock() {
    _hideErrorsBlock = false;
    _errors = [];
    _showErrorForBlock = [];
  };
  /**
   * Заблокировать кнопку отправки
   * @private
   */


  var _buttonDisable = function _buttonDisable() {
    $('.btn-disable-toggle').prop('disabled', true).addClass('disabled');
  };
  /**
   * Разблокировать кнопку отправки
   * @private
   */


  var _buttonEnable = function _buttonEnable() {
    $('.btn-disable-toggle').prop('disabled', false).removeClass('disabled');
  };
  /**
   * Показать попап комиссии
   * @private
   */


  var _showPopupCommission = function _showPopupCommission() {
    if (typeof price === "undefined") {
      price = $(this).data("price");
    }

    var toPrice = function toPrice(price) {
      return Utils.priceFormatWithSign(price, offer.lang, undefined, '₽');
    };

    var commission = $(this).data('commission');

    if (typeof commission === "undefined") {
      commission = calculateCommission(price, turnover);
    }

    var html = '\
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

    for (var i = 0; i < commission.ranges.length; i++) {
      var range = commission.ranges[i];
      html += '\
				<tr' + (range.price ? '' : ' class="disabled"') + '>\
					<td>' + range.title + '</td>\
					<td>' + range.percentage * 100 + '%</td>\
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


  var _selectActiveKwork = function _selectActiveKwork() {
    $('.js-request-kwork-id .empty').remove();
    var pid = $('.js-request-kwork-id option:checked').val();
    $('.order-extras li').hide();
    var orderExtrasItem = $('.order-extras li[data-pid=' + pid + ']');

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
        data: {
          'kwork_id': pid
        },
        success: function success(response) {
          _loadExtrasXhr = null;

          if (response.success === true) {
            for (var i in response.extras) {
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
                var extra = response.extras[i];
                var html = "";
                html = '<li class="order-extras__item order-extras__item_old" data-pid="' + pid + '" data-id="' + extra.id + '" style="display:none;">' + '<input class="order-extras__input styled-checkbox" data-payer-price="' + extra.payerPrice + '" data-price="' + extra.price + '" data-time="' + extra.duration + '" data-id="' + extra.id + '" name="gextras[]" type="checkbox" value="' + extra.id + '">' + '<label for="order-extras_' + extra.id + '" class="w460">' + extra.name.ucFirst() + '</label>' + '<div class="order-extras__select-block">' + '<select class="js-order-extras__count styled input input_size_s" data-affected-checkbox="' + extra.id + '" id="extra_count' + extra.id + '" name="extra_count' + extra.id + '">';

                for (var j = 0; j < offer.maxKworkCount; j++) {
                  var value = j + 1;
                  var commission = calculateCommission(extra.payerPrice * value, turnover);

                  var _price = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, "&nbsp;", "Р");

                  html += '<option value="' + value + '">' + value + ' (' + _price + ')</option>';
                }

                html += '</select>' + '</div>' + '</li>';
                $('.order-extras__list').prepend(html);
                $('.js-order-extras__count[data-affected-checkbox]').on('change', function (e) {
                  var t = $(e.delegateTarget);
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


  var _calcActiveKworkPrice = function _calcActiveKworkPrice() {
    var $requestKworkId = $('.js-request-kwork-id option:selected');
    var isPackage = $requestKworkId.data('package');
    var rate = $requestKworkId.data('rate') || offer.multiKworkRate;
    var count;
    var payerSum;
    var days;

    if (isPackage !== 1) {
      count = $('.js-request-kwork-count').val() ^ 0;
      payerSum = count * $requestKworkId.data('price');
      days = $requestKworkId.data('time') ^ 0;
    } else {
      var packageType = $(".js-request-kwork-package-type").val();

      if (!packageType) {
        return false;
      }

      var pack = offer.kworkPackages[$('.js-request-kwork-id').val()][packageType];
      count = 1;
      payerSum = parseFloat(pack.payerPrice);
      days = pack.duration ^ 0;
    }

    if ($requestKworkId.data('base-volume') > 0) {
      var base = $requestKworkId.data('base-volume');
      var volume = base * count;
      days = getVolumedDuration(days, base, volume, 'offer');
    } else {
      days = getDuration(days, count, rate);
    } // Опции кворка


    $('.order-extras__item_old').each(function () {
      var $item = $(this);
      var $checkbox = $item.find('.order-extras__input');
      var $select = $('#extra_count' + $checkbox.val());
      var payerPrice = $checkbox.data('payerPrice') ^ 0;
      var time = $checkbox.data('time') ^ 0;
      var count = $select.val() ^ 0;
      var commission = calculateCommission(payerPrice, turnover);
      $checkbox.data("price", commission.priceWorker);
      $select.find('option').each(function () {
        var count = parseInt($(this).prop('value'));
        var commission = calculateCommission(payerPrice * count, turnover);
        var workerSumString = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, ' ', 'Р');
        $(this).text(count + ' (' + workerSumString + ')');
      });
      $select.trigger('chosen:updated');

      if ($checkbox.is(':checked')) {
        payerSum += payerPrice * count;
        days += getDuration(time, count, rate);
      }
    }); // Добавленные опции

    $('.order-extras__item.custom').each(function () {
      var $item = $(this);
      var $selectPrice = $item.find('.js-new-extra-price');
      var $selectDays = $item.find('.js-new-extra-days');
      var payerPrice = $selectPrice.find('option:checked').data('payerValue') ^ 0;
      var time = $selectDays.val() ^ 0;
      var count = 1;
      $selectPrice.find('option').each(function () {
        var price = parseFloat($(this).data('payerValue'));
        var commission = calculateCommission(price, turnover);
        var workerSumString = Utils.priceFormatWithSign(commission.priceWorker, offer.lang, ' ', 'Р');
        $(this).text(workerSumString).data('seller-value', commission.priceWorker).attr('data-seller-value', commission.priceWorker);
      });
      $selectPrice.trigger('chosen:updated');
      payerSum += payerPrice * count;
      days += getDuration(time, count, rate);
    });

    _processCustomOptionsPrices(turnover);

    var commission = calculateCommission(payerSum, turnover);
    var workerSum = commission.priceWorker;
    var payerSumString = Utils.priceFormat(payerSum, offer.lang, '&nbsp;');
    var workerSumString = Utils.priceFormat(workerSum, offer.lang, '&nbsp;');
    _activeKworkPrice = payerSum;

    if (offer.lang === 'ru') {
      payerSumString += '&nbsp;<span class="rouble">Р</span>';
      workerSumString += '&nbsp;<span class="rouble">Р</span>';
    } else {
      payerSumString = '<span class="usd">$</span>' + payerSumString;
      workerSumString = '<span class="usd">$</span>' + workerSumString;
    }

    $('.js-total-sum').html(t('Стоимость: ') + payerSumString);
    var message = t('Вы получите') + ': ' + workerSumString + ' <a href="javascript:;" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';
    var totalWorkerSum = $('.js-total-worker-sum');
    totalWorkerSum.html(message);
    var $suggestForm = totalWorkerSum.closest('.js-active-kwork, .js-individual-kwork');
    $suggestForm.find('.js-price-explanation').data('commission', commission);
    $('.js-total-time').html(days + ' ' + Utils.declOfNum(days, [t('день'), t('дня'), t('дней')]));
  };
  /**
   * Посчитать итоговую цену для индивидуального предложения
   * @returns {boolean}
   * @private
   */


  var _calcCustomKworkPrice = function _calcCustomKworkPrice() {
    var $obj = $(_selectors.customKwork.price);
    var $desc = $('#kwork-price-description');

    if (!$obj.is(":visible")) {
      return true;
    }

    var isValidate = _validateCustomKworkPrice();

    if (isValidate) {
      var _price2 = $obj.val() ^ 0;

      var commission = calculateCommission(_price2, turnover);
      var ctp = _price2 - commission.priceKwork;
      var message = t('Вы получите {{0}}', [Utils.priceFormatWithSign(ctp, offer.lang)]);
      message += ' <a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';
      $desc.html(message);
      var $suggestForm = $obj.closest('.js-custom-kwork, .js-individual-kwork');
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


  var _showCustomOptionForm = function _showCustomOptionForm() {
    var el = $(this);
    var html = '<li class="order-extras__item extrasping-settings extra-field-block-css extra-edit-block-css custom">' + '<div class="offer-sprite offer-sprite-option pull-left m-hidden mt2"></div>' + '<input name="customExtraName[]" maxlength="50" value="" style="width:340px;" class="styled-input f14 pull-left m-wMax m-border-box order-new-extras__input" type="text">' + '<div class="new-order-extras__select-block">' + '<select id="extratime" class="js-new-extra-select js-new-extra-days input input_size_s" name="customExtraTime[]" style="width:16%;">' + '<option value="0"> ' + t('{{0}} дней', [0]) + ' </option>' + '<option value="1"> ' + t('{{0}} день', [1]) + ' </option>' + '<option value="2"> ' + t('{{0}} дня', [2]) + ' </option>' + '<option value="3"> ' + t('{{0}} дня', [3]) + ' </option>' + '<option value="4"> ' + t('{{0}} дня', [4]) + ' </option>' + '<option value="5"> ' + t('{{0}} дней', [5]) + ' </option>' + '<option value="6"> ' + t('{{0}} дней', [6]) + ' </option>' + '<option value="7"> ' + t('{{0}} дней', [7]) + ' </option>' + '<option value="8"> ' + t('{{0}} дней', [8]) + ' </option>' + '<option value="9"> ' + t('{{0}} дней', [9]) + ' </option>' + '<option value="10"> ' + t('{{0}} дней', [10]) + ' </option>' + '</select>' + '<div class="custom-option-price"><select name="customExtraPrice[]" class="js-new-extra-select js-new-extra-price input input_size_s" style="width:16%;">' + offer.customPricesOptionsHtml + '</select></div>' + '</div>' + '<div class="js-new-order-extras-delete new-order-extras__delete del-opt cur ml6 dib" data-id="0" title="' + t('Удалить') + '">' + '<i class="ico-close-14"></i>' + '</div>' + '<div class="clear"></div>' + '</li>';
    var $html = $(html);
    $html.find('.js-new-extra-price').chosen({
      width: "100px",
      disable_search: true
    });
    $html.find('.js-new-extra-days').chosen({
      width: "80px",
      disable_search: true
    });
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


  var _removeCustomOptionForm = function _removeCustomOptionForm() {
    var target = $(this);
    target.closest('li').remove();
    $('#message_body').trigger('input');

    if ($('.js-add-extras li.extrasping-settings').length < 1) {
      $('#order-extras__create').text(t('Создать опцию'));
    }

    _calcActiveKworkPrice();

    _validateIndividualKwork();
  };

  var _calculateCustomExtrasPrices = function _calculateCustomExtrasPrices() {
    var price = 0;
    var customExtras = $(".order-extras__list li.custom");

    for (var i = 0; i < customExtras.length; i++) {
      var $customExtra = $(customExtras[i]);
      price += clearPriceStr($customExtra.find('select.js-new-extra-price option:selected').data('sellerValue'));
    }

    return price;
  };

  var _processCustomOptionsPrices = function _processCustomOptionsPrices(currentTurnover) {
    // Удалим все подсказки про опции чтобы избежать дублей
    $(".max-custom-options-tooltip").remove();
    var $customExtras = $(".order-extras__list li.custom"); // Посчитаем максимальную допустимую суппу доп. опций для продавкца с учётом комиссии

    var commissionMax = calculateCommission(window.maxOptionsSum, currentTurnover); // Посчитаем сумму доп. опций

    var totalCustom = _calculateCustomExtrasPrices();

    var totalKwork = 0;
    $("li.order-extras__item_old").each(function () {
      if ($(this).is(":visible")) {
        $input = $(this).find("input.order-extras__input");
        totalKwork += $input.data("price");
      }
    });
    var total = totalCustom + totalKwork; // Если заказана хотябы одна опция, добавим подсказу

    if (totalCustom > 0) {
      var workerMaxSumString = Utils.priceFormatWithSign(commissionMax.priceWorker, offer.lang, " ", "руб.");
      var workerTotalSumString = Utils.priceFormatWithSign(total, offer.lang, " ", "руб.");
      var tooltip = "Общая цена всех доп. опций - до " + workerMaxSumString + " Сейчас " + workerTotalSumString;
      $customExtras.last().parent().append("<div class='max-custom-options-tooltip'>" + tooltip + "</div>");
    }

    var $submitButton = $("#track-send-message-button");

    if (total <= commissionMax.priceWorker) {
      // Если сумма проходит проверку, то активируем кнопку отправки предожения
      $submitButton.removeClass("btn_disabled").prop("disabled", false); // Уберем красную обводку с цен доп. опций

      $customExtras.removeClass("custom-price-error"); // Если сумма проходит все проверки, то показываем кнопку добавления опции

      $(".js-order-extras-create").show();
    } else {
      // Если сумма доп. опций превышает допустимую, то:
      // - отключить кнопку отправки предложения
      $submitButton.addClass("btn_disabled").prop("disabled", true); // - досветить цены доп. опций красным

      $customExtras.addClass("custom-price-error"); // - подсветим подсказку красным если она ещё не красная

      $(".max-custom-options-tooltip").addClass("error").html(tooltip + "<br>Уменьшите стоимость предыдущих опций, чтобы добавить еще одну"); // - спрятать кнопку добавления опции

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
    selectPaymentType: _selectPaymentType
  };
}();

/***/ }),

/***/ "./public_html/js/app/stages/offer-stages.js":
/*!***************************************************!*\
  !*** ./public_html/js/app/stages/offer-stages.js ***!
  \***************************************************/
/*! exports provided: OfferStages */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "OfferStages", function() { return OfferStages; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * Работа с задачами
 */
var OfferStages =
/*#__PURE__*/
function () {
  function OfferStages() {
    _classCallCheck(this, OfferStages);

    /**
     * Изменять ли отображение ошибки. Призначении true: если скрыта - оставлять скрытой, если отображена - не скрывать.
     * @type {boolean}
     */
    this.notChangeShowErrorsBlock = false;
    /**
     * Массив ошибок задач
     * @type {Array}
     * @property {string} target
     * @property {number} position
     * @property {string} text
     */

    this.errorsStage = [];
    /**
     * Заблокировать ли кнопку отправки формы
     * @type {boolean}
     */

    this.isDisableButtonSend = false;
    /**
     * Итоговая стоимость заказа
     * @type {number}
     */

    this.totalPrice = 0;
    /**
     * Наличие ошибки в добавлении срока
     * @type {boolean}
     */

    this.durationError = false;
    /**
     * Тип пользователя - продавец
     * @type {string}
     * @const
     */

    this.ACTOR_TYPE_WORKER = 'worker';
    /**
     * Страница трека
     * @type {string}
     * @const
     */

    this.PAGE_TRACK = 'track';
    /**
     * Предложение индивидуального предложения
     * @type {string}
     * @const
     */

    this.PAGE_OFFER = 'offer';
    /**
     * Тип пользователя - покупатель
     * @type {string}
     * @const
     */

    this.ACTOR_TYPE_PAYER = 'payer';
    this.selectors = {
      offerStages: '.js-offer-stages',
      stages: '.js-stages',
      stage: {
        itemDefault: '.js-stage-default',
        item: '.js-stage',
        infoBlock: '.offer-individual__stage-info',
        number: '.js-stage-number',
        numberSelect: '.js-stage-number-select',
        title: '.js-stage-name',
        titleEditor: '.js-content-editor',
        titleText: '.js-stage-name-text',
        titleBlock: '.offer-individual__stage-name',
        price: '.js-stage-price',
        priceText: '.js-stage-price-text',
        error: '.js-stage-error',
        add: '.js-stage-add',
        edit: '.js-stage-edit',
        save: '.js-stage-save',
        remove: '.js-stage-delete'
      },
      totalPrice: {
        block: '.js-stage-total-price',
        wrap: '.js-stage-total-price-wrap',
        price: '.js-offer-total-price',
        priceCommission: '.js-stage-total-price-commission',
        error: '.js-stage-total-price-error',
        addPayerBlock: '.js-stage-total-price-add-payer-block',
        addPayer: '.js-stage-total-price-add-payer',
        priceWithCommission: '.js-price-explanation'
      },
      textLimit: '.js-stage-limit',
      duration: '.js-stages-duration',
      durationValue: '.js-stages-duration-value',
      durationChange: '.js-stages-duration-change',
      durationChangeSelect: '.js-stages-duration-change-select',
      durationChangeError: '.js-stages-duration-change-error',
      customPrice: '.js-kwork-price'
    };
    this.classes = {
      stageItem: 'js-stage',
      stageEdit: 'stage-edit',
      stageError: 'stage-error',
      disableAddStage: 'disable',
      enStages: 'offer-stages--en',
      stageActive: 'offer-individual__stage--active',
      durationError: 'duration-error',
      durationErrorLight: 'duration-error-light'
    };
    /**
     * @type {Object}
     */

    this.stagesOfferBlock = $(this.selectors.offerStages);
    this.timerValidateTitle = null;
  }

  _createClass(OfferStages, [{
    key: "init",
    value: function init(options) {
      if (this.options) {
        this.prevOption = this.options;
      }
      /**
       * @type {Object}
       * @property {string} actorType
       * @property {string} pageType
       * @property {Object} stages
       * @property {boolean} showOneStage
       * @property {number} turnover
       * @property {boolean} controlEnLang
       * @property {number} countStages
       * @property {Object} offer
       * @property {number} offer.orderId
       * @property {string} offer.lang
       * @property {number} offer.stageMinPrice
       * @property {number} offer.customMinPrice
       * @property {number} offer.customMaxPrice
       * @property {number} offer.offerMaxStages
       * @property {number} offer.price
       * @property {number} offer.duration
       * @property {number} offer.initialDuration
       * @property {number} offer.stageMaxIncreaseDays
       * @property {number} offer.stageMaxDecreaseDays
       */


      this.options = options;
    }
    /**
     * Обновление значения параметра stageMinPrice
     * @param value
     */

  }, {
    key: "updateOptionsStageMinPrice",
    value: function updateOptionsStageMinPrice(value) {
      this.options['offer']['stageMinPrice'] = value;
    }
    /**
     * Инициализация предыдущих настроек.
     * Актульано для диалогов в роли продавца. Когда пользователь может как принять предложение, так и содать свое
     */

  }, {
    key: "initPrev",
    value: function initPrev() {
      if (this.prevOption) {
        this.options = this.prevOption;
      }
    }
  }, {
    key: "events",
    value: function events() {
      var _this = this;

      this.stagesOfferBlock.off('click change input').on('change', this.selectors.stage.numberSelect, function (e) {
        _this.defaultNotChangeShowErrorsBlock();

        _this.validateStageNumber($(e.target));

        _this.isEnableAddStage(true);

        _this.onChangeStage();
      }).on('input', this.selectors.stage.title, function (e) {
        _this.defaultNotChangeShowErrorsBlock();

        var $stage = $(e.target).parents(_this.selectors.stage.item);

        _this.controlLang($(e.target));

        clearTimeout(_this.timerValidateTitle);
        _this.timerValidateTitle = setTimeout(function () {
          _this.validateStageName($(e.target));

          _this.showCurrentError($(e.target));

          _this.isEnableAddStage(true);

          _this.onChangeStage();

          _this.onEditStage($stage);
        }, 500);
      }).on('input', this.selectors.stage.price, function (e) {
        _this.defaultNotChangeShowErrorsBlock();

        var $stage = $(e.target).parents(_this.selectors.stage.item);

        _this.limitMaxPrice($(e.target), _this.selectors.stage.item);

        _this.calcTotalPrice();

        _this.changeDurationChange();

        _this.validateTotalPrice(); // создание сообщения об ошибке только у полей с данными


        $(_this.selectors.stage.price).each(function (i) {
          var $element = $(_this.selectors.stage.price).eq(i);

          if ($element.val()) {
            _this.validateStagePrice($element);

            _this.showCurrentError($element);
          } else {
            _this.validateStagePrice($element, true);

            _this.showCurrentError($element);
          }
        });

        _this.isEnableAddStage(true);

        _this.onChangeStage();

        _this.onEditStage($stage);

        _this.calcPlaceholder();

        if (_this.options.pageType === _this.PAGE_OFFER) {
          _this.updateCustomPrice();
        }
      }).on('click', this.selectors.stage.add, function () {
        _this.defaultNotChangeShowErrorsBlock();

        var isAddStage = _this.addStage();

        if (isAddStage) {
          _this.isEnableAddStage(true);

          _this.onChangeStage();
        }
      }).on('click', this.selectors.stage.remove, function (e) {
        _this.defaultNotChangeShowErrorsBlock();

        var $stage = $(e.target).parents(_this.selectors.stage.item);

        _this.removeStage($stage);

        _this.changeDurationChange();

        _this.validateTotalPrice();

        _this.updateControlHide();

        _this.isEnableAddStage(true);

        _this.onChangeStage();

        _this.validateStagesPrices();

        _this.calcPlaceholder();

        _this.onDeleteStage($stage);
      }).on('click', this.selectors.stage.edit, function (e) {
        var $stage = $(e.target).parents(_this.selectors.stage.item);

        _this.defaultNotChangeShowErrorsBlock();

        _this.hideTotalPriceErrorByFirstStage();

        _this.editStage($stage);
      }).on('click', this.selectors.stage.save, function (e) {
        if (window.offerForm && window.offerForm.isBusy) {
          return;
        }

        _this.defaultNotChangeShowErrorsBlock();

        _this.saveStage($(e.target).parents(_this.selectors.stage.item));

        _this.validateTotalPrice();

        _this.isEnableAddStage(true);

        _this.onChangeStage();
      }).on('change', this.selectors.durationChangeSelect, function () {
        _this.validateDurationChange();

        _this.onChangeStage();
      });
      $(window).resize(function () {
        _this.reBuildStages();
      });
      this.stagesOfferBlock.on('keypress', '.js-content-editor', function (e) {
        _this.eventEnter(e);
      }).on('keypress', this.selectors.stage.title, function (e) {
        _this.eventEnter(e);
      }).on('keypress', this.selectors.stage.price, function (e) {
        _this.eventEnter(e);
      });
    }
    /**
     * Событие при нажатии на Enter
     * Сохраняем текущую задачу
     */

  }, {
    key: "eventEnter",
    value: function eventEnter(e) {
      if (e.keyCode === 13) {
        e.preventDefault();
        $(e.target).parents(this.selectors.stage.item).find(this.selectors.stage.save).click();
      }
    }
    /**
     * Событие при изменении задачи
     */

  }, {
    key: "onChangeStage",
    value: function onChangeStage() {}
    /**
     * Событие при удалении задачи
     * @param $stage
     */

  }, {
    key: "onDeleteStage",
    value: function onDeleteStage($stage) {}
    /**
     * Событие при редактировании задачи
     * @param $stage
     */

  }, {
    key: "onEditStage",
    value: function onEditStage($stage) {}
    /**
     * Сгенерировать задач
     */

  }, {
    key: "generationStages",
    value: function generationStages() {
      var _this2 = this;

      this.initStagesBlock(this.options.offer.orderId);
      this.stagesOfferBlock.removeClass(this.classes.enStages);

      if (this.options.offer.lang === 'en') {
        this.stagesOfferBlock.addClass(this.classes.enStages);
      } // если переданы данные задач, то отображаем их


      if (this.options.stages && Object.keys(this.options.stages).length) {
        $.each(this.options.stages, function (k, values) {
          _this2.buildStage(k + 1, values.number);

          _this2.setDataStage(values);
        }); // на странице трэка открыть задачи сразу для редактирования

        if (this.options.pageType === this.PAGE_TRACK) {
          this.stagesOfferBlock.find(this.selectors.stage.item).each(function (key, value) {
            _this2.showStageInput($(value));
          });
        }
      } else if (this.options.pageType === this.PAGE_TRACK) {
        // на странице трека при нажатии на кнопку "Добавить задачу"
        this.buildStage(1, this.options.countStages + 1);
      } else if (this.options.actorType === this.ACTOR_TYPE_PAYER || this.options.pageType === this.PAGE_OFFER) {
        // если покупатель и в предложении нет задач, то показываем три пустых задачи (биржа и диалоги)
        this.buildStage(1, 1);
        this.buildStage(2, 2);
        this.buildStage(3, 3);
        this.setPlaceholderTitle();
      } else {
        this.buildStage(1, 1);
      }

      this.calcTotalPrice();
      this.updateControlHide();
      this.isEnableAddStage(true);
      this.reBuildStages();
      this.initTooltip();
      this.setDuration();
      this.events();
    }
  }, {
    key: "initStagesBlock",
    value: function initStagesBlock(orderId) {
      this.stagesOfferBlock = $(this.selectors.offerStages).filter('[data-order-id="' + orderId + '"]');
    }
    /**
     * Инициализация тултипа для иконок задачи
     */

  }, {
    key: "initTooltip",
    value: function initTooltip() {
      if ($.fn.tooltipster) {
        this.stagesOfferBlock.children('div:not(' + this.selectors.stage.itemDefault + ')').find('.tooltip:not(.tooltipstered)').tooltipster(TOOLTIP_CONFIG);
      }
    }
    /**
     * Установить текущий срок заказа в днях
     */

  }, {
    key: "setDuration",
    value: function setDuration() {
      var durationDays = this.options.offer.duration / 86400;
      durationDays = durationDays.toFixed(0); // округляем до целого

      var durationText = durationDays + ' ' + Utils.declOfNum(durationDays, [t('день'), t('дня'), t('дней')]);
      this.stagesOfferBlock.find(this.selectors.durationValue).html(durationText);
    }
    /**
     * Валидация задач
     * @param notChangeShowErrors
     */

  }, {
    key: "validateStages",
    value: function validateStages(notChangeShowErrors) {
      var _this3 = this;

      this.notChangeShowErrorsBlock = typeof notChangeShowErrors === "boolean" ? notChangeShowErrors : false;
      this.errorsStage = [];
      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        _this3.validateStage($(v));
      });
      this.showAllStageErrors();
    }
    /**
     * Валидация всех цен
     */

  }, {
    key: "validateStagesPrices",
    value: function validateStagesPrices() {
      var _this4 = this;

      this.calcTotalPrice();
      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        var stagePrice = $(v).find(_this4.selectors.stage.price);

        if (stagePrice.val() !== '') {
          _this4.defaultNotChangeShowErrorsBlock();

          _this4.validateStagePrice(stagePrice);

          _this4.showCurrentError(stagePrice);

          _this4.isEnableAddStage(true);
        }
      });
    }
    /**
     * Валидация задачи
     * @param {Object} $stage
     */

  }, {
    key: "validateStage",
    value: function validateStage($stage) {
      var isErrorName = this.validateStageName($stage.find(this.selectors.stage.title));
      var isErrorPrice = this.validateStagePrice($stage.find(this.selectors.stage.price));
      var isErrorNumber = this.validateStageNumber($stage.find(this.selectors.stage.numberSelect));
      return isErrorName && isErrorPrice && isErrorNumber;
    }
    /**
     * Валидация названия задачи
     * @param {Object} $input
     * @returns {boolean}
     */

  }, {
    key: "validateStageName",
    value: function validateStageName($input) {
      var idStage = $input.parents(this.selectors.stage.item).data('number');
      this.hideStageInputError($input);
      var backendError = $input.data('backendError');

      if (backendError) {
        this.addErrorStage({
          target: "title",
          text: backendError,
          position: idStage
        });
        return false;
      }

      if ($input.val() === "" || StopwordsModule._testContacts($input.val()).length !== 0) {
        this.addErrorStage({
          target: "title",
          text: t('Укажите название задачи'),
          position: idStage
        });
        return false;
      }

      this.removeErrorStage({
        target: "title",
        position: idStage
      });
      return true;
    }
    /**
     * Валидация стоимости задачи
     * @param {Object} $input
     * @returns {boolean}
     */

  }, {
    key: "validateStagePrice",
    value: function validateStagePrice($input, clearErrors) {
      var price = $input.val();
      var idStage = $input.parents(this.selectors.stage.item).data('number');

      if (idStage === null) {
        return false;
      }

      if (clearErrors) {
        // удаление ошибки задачи
        this.removeErrorStage({
          target: "payer_price",
          position: idStage
        });
        return true;
      }

      price = price == '' ? 0 : price;
      this.hideStageInputError($input); // ввод только цифр

      if (/[^0-9]/ig.test(price)) {
        $input.val(price.replace(/[^0-9]/ig, ''));
        return false;
      }

      if (isNaN(price) || price === '') {
        this.addErrorStage({
          target: "payer_price",
          text: t('Укажите цену'),
          position: idStage
        });
        return false;
      } // формирование текста ошибки при вводе стоимости задачи


      if (price < this.options.offer.stageMinPrice || price > this.options.offer.customMaxPrice) {
        var stageMaxCount = this.options.offer.customMaxPrice - this.totalPrice + parseInt(price);
        var stageMinPrice = Utils.priceFormatWithSign(this.options.offer.stageMinPrice, this.options.offer.lang);
        var stageMaxPrice = Utils.priceFormatWithSign(stageMaxCount, this.options.offer.lang);
        var errorText = ''; // текст ошибки в зависимости от условий

        if (this.options.offer.stageMinPrice === this.options.offer.customMaxPrice || stageMaxCount === this.options.offer.stageMinPrice) {
          errorText = t('Стоимость должна быть равна {{0}}', [stageMinPrice]);
        } else if (stageMaxCount <= this.options.offer.stageMinPrice) {
          if (this.options.offer.customMaxPrice <= this.totalPrice) {
            errorText = t('Достигнута максимальная общая стоимость задач. Отредактируйте стоимость предыдущих задач, либо удалите текущую задачу.');
          } else {
            errorText = t('Стоимость задачи должна быть от {{0}}', [stageMinPrice]);
          }
        } else {
          errorText = t('Стоимость задачи должна быть от {{0}} до {{1}}', [stageMinPrice, stageMaxPrice]);
        } // добавление ошибки задачи


        this.addErrorStage({
          target: "payer_price",
          text: errorText,
          position: idStage
        });
        this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
        return false;
      } // удаление ошибки задачи


      this.removeErrorStage({
        target: "payer_price",
        position: idStage
      });
      return true;
    }
    /**
     * Обрезаем стоимость по максимальному значению
     * @param {Object} $input
     * @param {String} stageSelector
     * @returns {Void}
     */

  }, {
    key: "limitMaxPrice",
    value: function limitMaxPrice($input, stageSelector) {
      var inputValue = parseInt($input.val());
      var stageList = $(stageSelector);
      var otherStagesPrice = 0;
      var passStagesPrice = false;

      for (var i = 0; i < stageList.length; i++) {
        var inputVal = $(stageList[i]).find('.js-stage-price').val();
        inputVal = inputVal || 0;
        var stagePrice = parseInt(inputVal);

        if (stagePrice && stagePrice !== inputValue || passStagesPrice) {
          otherStagesPrice = otherStagesPrice + stagePrice;
        } else if (stagePrice === inputValue) {
          passStagesPrice = true;
        }
      }

      if (isNaN(inputValue)) {
        inputValue = '';
      }

      if (inputValue > this.options.offer.customMaxPrice - otherStagesPrice) {
        // устанавливаем максимальное значение в текущем инпуте
        // в зависимости от максимальной стоимости и уже введенной стоимости в других задач
        inputValue = this.options.offer.customMaxPrice - otherStagesPrice;
      }

      $input.val(inputValue);
    }
    /**
     * Если дошли до предела максимльной цены, то блокируем кнопку добавления задачи
     * @returns {boolean}
     */

  }, {
    key: "checkSumTotalPrice",
    value: function checkSumTotalPrice() {
      return this.totalPrice === this.options.offer.customMaxPrice || this.options.offer.customMaxPrice - this.totalPrice < this.options.offer.stageMinPrice;
    }
    /**
     * Валидация итоговой стоимости заказа
     * @returns {boolean}
     */

  }, {
    key: "validateTotalPrice",
    value: function validateTotalPrice() {
      this.hideTotalPriceError();
      this.enableButtonSend();

      if (this.totalPrice > this.options.offer.customMaxPrice) {
        this.showTotalPriceError(t('Стоимость заказа достигла предельной суммы ') + Utils.priceFormatWithSign(this.options.offer.customMaxPrice, this.options.offer.lang));
        this.disableAddStage();
        this.disableButtonSend();
        this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
        return false;
      }

      if (this.totalPrice < this.options.offer.customMinPrice) {
        this.showTotalPriceError(t('Итоговая цена заказа не может быть ниже ') + Utils.priceFormatWithSign(this.options.offer.customMinPrice, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>'));
        this.disableButtonSend();
        this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
      }

      return true;
    }
    /**
     * Валидация селекта номера задачи
     */

  }, {
    key: "validateStageNumber",
    value: function validateStageNumber($select) {
      var _this5 = this;

      var returnValue = true;
      var idStageSelect = $select.parents(this.selectors.stage.item).data('number'); // перибираем задачи по порядку и ищем с одинаково выбраным номером

      $.each($(this.selectors.stage.numberSelect).filter(':visible'), function (k1, v1) {
        var idStage1 = $(v1).parents(_this5.selectors.stage.item).data('number');

        _this5.removeErrorStage({
          position: idStage1,
          target: 'number'
        });

        _this5.hideStageInputError($(v1));

        $.each($(_this5.selectors.stage.numberSelect).filter(':visible'), function (k2, v2) {
          var idStage2 = $(v2).parents(_this5.selectors.stage.item).data('number'); // если значения номеров одинаково
          // и это не одина и та же задача,
          // то ошибка

          if ($(v1).val() === $(v2).val() && idStage1 !== idStage2) {
            _this5.addErrorStage({
              position: idStage1,
              target: 'number',
              text: t('Задачи имеют одинаковый номер. Исправьте их последовательность.')
            });

            _this5.showCurrentError($(v1)); // если ошибка в текущей задачи,
            // то возвращаем false, чтобы нельзя было сохранить задачу


            if (idStage1 === idStageSelect) {
              returnValue = false;
            }
          }
        });
      });
      this.reBuildStages();
      return returnValue;
    }
    /**
     * Валидация селекта "Добавить (удалить) срок к заказу"
     * @returns {boolean}
     */

  }, {
    key: "validateDurationChange",
    value: function validateDurationChange() {
      if (this.stagesOfferBlock.find(this.selectors.durationChange).is(':visible') && isNaN(parseInt(this.stagesOfferBlock.find(this.selectors.durationChangeSelect).val()))) {
        return false;
      }

      this.hideDurationChangeError();
      return true;
    }
    /**
     * Добавить задачу
     */

  }, {
    key: "addStage",
    value: function addStage() {
      if (this.stagesOfferBlock.find(this.selectors.stage.add).hasClass('disable') || this.isEnableAddStage(false) === false) {
        return false;
      }

      this.reSaveStages();
      var countStages = this.stagesOfferBlock.find(this.selectors.stage.item).length;
      var maxNumberResult = this.options.countStages || 0; // актуально для странице трэка. Если при редактировании удаляются все задачи, то для новой задачи надо кол-во задачи

      if (countStages) {
        var _this$getMaxAndMinNum = this.getMaxAndMinNumber(),
            minNumber = _this$getMaxAndMinNum.minNumber,
            maxNumber = _this$getMaxAndMinNum.maxNumber;

        if (maxNumber >= maxNumberResult) {
          maxNumberResult = maxNumber;
        }
      }

      this.buildStage(countStages + 1, parseInt(maxNumberResult) + 1);
      this.initTooltip();
      this.stagesOfferBlock.find(this.selectors.stage.item).css({
        'z-index': 1
      });
      this.stagesOfferBlock.find(this.selectors.stage.item).last().css({
        'z-index': 0
      });
      this.reBuildStages();
      return true;
    }
    /**
     * Удалить задачи
     * @param {Object} $stage
     */

  }, {
    key: "removeStage",
    value: function removeStage($stage) {
      $stage.remove();

      var _this$getMaxAndMinNum2 = this.getMaxAndMinNumber(),
          minNumber = _this$getMaxAndMinNum2.minNumber,
          maxNumber = _this$getMaxAndMinNum2.maxNumber;

      var countStages = this.stagesOfferBlock.find(this.selectors.stage.item).length;
      this.updateSortStages($stage, maxNumber + 1, countStages + 1);
      this.calcTotalPrice();

      if (this.options.pageType === this.PAGE_OFFER) {
        this.updateCustomPrice();
      }
    }
    /**
     * Редактировать задачи
     * @param {Object} $stage
     */

  }, {
    key: "editStage",
    value: function editStage($stage) {
      var _this6 = this;

      // для покупателя нужно включать редактирование всех задач. А текущий выделяется
      if (this.options.actorType === this.ACTOR_TYPE_PAYER) {
        this.stagesOfferBlock.find(this.selectors.stage.item).each(function (key, value) {
          _this6.showStageInput($(value));

          _this6.updateNumberSelectStage($(value));

          _this6.showNumberSelectStage($(value));
        });
        this.selectCurrentStage($stage);
      } else {
        this.showStageInput($stage);
        this.updateNumberSelectStage($stage);
        this.showNumberSelectStage($stage);
      }

      this.reBuildStages();
    }
    /**
     * Сохранить задачу
     * @param {Object} $stage
     */

  }, {
    key: "saveStage",
    value: function saveStage($stage) {
      if (!this.validateStage($stage)) {
        this.showCurrentError($stage.find(this.selectors.stage.title));
        this.showCurrentError($stage.find(this.selectors.stage.price));
        return false;
      }

      $stage.removeClass(this.classes.stageError);
      $stage.removeClass(this.classes.stageActive);
      this.showStageText($stage);
      this.saveNumberSelectStage($stage);
      this.hideNumberSelectStage($stage);
      this.reBuildStages();
    }
    /**
     * Сохранить все задачи
     */

  }, {
    key: "reSaveStages",
    value: function reSaveStages() {
      var _this7 = this;

      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        _this7.saveStage($(v));
      });
    }
    /**
     * Отслеживает количество отображаемых задач
     * @returns {boolean}
     */

  }, {
    key: "checkStageCountLimit",
    value: function checkStageCountLimit() {
      var countStage = this.stagesOfferBlock.find(this.selectors.stage.item).length;

      if (!this.options.offer.offerMaxStages || countStage < this.options.offer.offerMaxStages) {
        $(this.selectors.textLimit).addClass('hidden');
        return true;
      } else {
        $(this.selectors.textLimit).removeClass('hidden');
        return false;
      }
    }
    /**
     * Подсчитать итоговую цену предложения
     * TODO: разбить на методы по-хорошемы бы
     */

  }, {
    key: "calcTotalPrice",
    value: function calcTotalPrice() {
      var countStage = this.stagesOfferBlock.find(this.selectors.stage.item).length;
      this.stagesOfferBlock.find(this.selectors.totalPrice.block).addClass('hidden');
      this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');

      if (this.options.actorType === this.ACTOR_TYPE_WORKER && countStage > 1) {
        this.stagesOfferBlock.find(this.selectors.totalPrice.block).removeClass('hidden');
      } // Для покупател показывать итоговую стоимость только, если он заполнил 2 задачи.
      // Необходимо в случае если он принимает безэтпное предложение.
      // В без задачном предложении отображается 3 пустых задачи (реализовано в generationStages)


      if (this.options.actorType === this.ACTOR_TYPE_PAYER && countStage > 1 && this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.price).val() !== '' && this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='2']").find(this.selectors.stage.price).val() !== '') {
        this.stagesOfferBlock.find(this.selectors.totalPrice.block).removeClass('hidden');
      }

      if (this.options.actorType === this.ACTOR_TYPE_WORKER && this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.price).val() !== '') {
        this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).removeClass('hidden');
      }

      var totalPrice = 0;
      this.stagesOfferBlock.find(this.selectors.stage.price).each(function (k, v) {
        var value = parseInt($(v).val());

        if (!isNaN(value)) {
          totalPrice += value;
        }
      });
      this.totalPrice = totalPrice;
      this.stagesOfferBlock.find(this.selectors.totalPrice.price).html(Utils.priceFormatWithSign(totalPrice, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>'));
      this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock).addClass('hidden'); // Если стоимость предложения продавца превышает итоговую стоимость
      // и если итоговую стоимость не превышает свой лимит,
      // то показываем уточнение для покупателя на сколько увеличится стоимсоть

      if (this.options.actorType === this.ACTOR_TYPE_PAYER && this.totalPrice > this.options.offer.customMinPrice && this.totalPrice <= this.options.offer.customMaxPrice && this.options.pageType !== this.PAGE_TRACK) {
        var diff = Math.abs(this.options.offer.customMinPrice - this.totalPrice);
        var formatDiff = Utils.priceFormatWithSign(diff, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>');
        this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock).removeClass('hidden');
        this.stagesOfferBlock.find(this.selectors.totalPrice.addPayer).html('+' + formatDiff);
        var priceForTooltip = formatDiff.replace(/\.+$/, '');
        var tooltipContent = '<div class="stage-tooltip-content">' + t('Итоговая цена, которую вы готовы заплатить, превысила ставку продавца на {{0}}. Это допустимо, особенно, если вы добавили задачи, которые не предусмотрели заранее.', [priceForTooltip]) + '</div>';
        this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock).find('.tooltipster').tooltipster('content', tooltipContent);
      }

      if (this.options.actorType === this.ACTOR_TYPE_WORKER) {
        this.calcPriceWithCommission(totalPrice);
      } else {
        this.clearPriceWithCommission();
      }
    }
    /**
     * Обновление общей стоимости инд. предложения. Актуально только при отправке предложения
     */

  }, {
    key: "updateCustomPrice",
    value: function updateCustomPrice() {
      // через window, чтобы можно было использовать в другом месте
      if (window.offerStages.totalPrice > window.offerStages.options.offer.stagesPriceThreshold) {
        $(window.offerStages.selectors.customPrice).val(window.offerStages.totalPrice);
      }
    }
    /**
     * Подсчет цены с комиссией
     * @param {number} summ
     */

  }, {
    key: "calcPriceWithCommission",
    value: function calcPriceWithCommission(summ) {
      var price = summ ^ 0;
      var commission = calculateCommission(price, this.options.turnover);
      var ctp = price - commission.priceKwork;
      var message = t('Вы получите {{0}}', [Utils.priceFormatWithSign(ctp, this.options.offer.lang)]);
      message += ' <a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';
      $(this.selectors.totalPrice.priceCommission).html(message);
      this.stagesOfferBlock.find(this.selectors.totalPrice.priceWithCommission).data('commission', commission);
    }
    /**
     * Очистка блока цены с комиссией
     */

  }, {
    key: "clearPriceWithCommission",
    value: function clearPriceWithCommission() {
      $(this.selectors.totalPrice.priceCommission).html('');
    }
    /**
     * Изменение блока "Добавить (уменьшить) срок к заказу"
     */

  }, {
    key: "changeDurationChange",
    value: function changeDurationChange() {
      this.hideDurationChangeBlock(); // если общая стоимость задач меньше минимальной стоимости заказа
      // или равна текущей цене заказа
      // то ничего не делаем

      var $durationChangeSelect = this.stagesOfferBlock.find(this.selectors.durationChangeSelect);
      var countOptions;
      var textErrorDuration;
      var statusErrorDuration;
      var labelDuration;

      if (this.options.actorType !== this.ACTOR_TYPE_PAYER) {
        return;
      } else if (this.totalPrice >= this.options.offer.customMinPrice && this.totalPrice < parseInt(this.options.offer.price) && this.options.offer.stageMaxDecreaseDays) {
        textErrorDuration = t('Уменьшение срока не обязательно, но допустимо.');
        statusErrorDuration = 'green';
        labelDuration = t('Уменьшить срок заказа на');
        countOptions = this.options.offer.stageMaxDecreaseDays;
      } else if (this.totalPrice > parseInt(this.options.offer.price) && this.options.offer.stageMaxIncreaseDays) {
        textErrorDuration = t('Увеличьте срок, поскольку сумма заказа станет больше.');
        statusErrorDuration = 'red';
        labelDuration = t('Добавить срок к заказу');
        countOptions = this.options.offer.stageMaxIncreaseDays;
      } else {
        return;
      }

      this.showDurationChangeBlock();

      if ($durationChangeSelect.val() && $durationChangeSelect.find('option').length === countOptions + 1) {
        return;
      }

      this.showDurationChangeError(textErrorDuration, statusErrorDuration);
      this.stagesOfferBlock.find(this.selectors.durationChange).find('label').text(labelDuration);
      this.reBuildDuration(countOptions);
    }
    /**
     * Обновить селект в блоке "Добавить (уменьшить) срок к заказу"
     * @param countOptions
     */

  }, {
    key: "reBuildDuration",
    value: function reBuildDuration(countOptions) {
      var $durationChangeSelect = this.stagesOfferBlock.find(this.selectors.durationChangeSelect);
      $durationChangeSelect.html('');
      var options = '<option value="" disabled selected>' + t('Выберите срок') + '</option>';

      for (var i = 1; i <= countOptions; i++) {
        options += '<option value="' + i + '">' + i + ' ' + declension(i, t('день'), t('дня'), t('дней'), 'ru') + '</option>';
      }

      $durationChangeSelect.append(options);
      $durationChangeSelect.chosen({
        width: "100%",
        disable_search: true
      });
      $durationChangeSelect.trigger('chosen:updated');
    }
    /**
     * Скрыть блок "Добавить (уменьшить) срок к заказу"
     */

  }, {
    key: "hideDurationChangeBlock",
    value: function hideDurationChangeBlock() {
      this.stagesOfferBlock.find(this.selectors.durationChange).addClass('hidden');
      this.hideDurationChangeError();
    }
    /**
     * Показать блок "Добавить (уменьшить) срок к заказу"
     */

  }, {
    key: "showDurationChangeBlock",
    value: function showDurationChangeBlock() {
      this.stagesOfferBlock.find(this.selectors.durationChange).removeClass('hidden');
    }
    /**
     * Показать задачу в виде инпутов
     * @param {Object} $stage
     */

  }, {
    key: "showStageInput",
    value: function showStageInput($stage) {
      $stage.addClass(this.classes.stageEdit);
    }
    /**
     * Показать задачу в виде текста
     * @param {Object} $stage
     */

  }, {
    key: "showStageText",
    value: function showStageText($stage) {
      $stage.removeClass(this.classes.stageEdit);
      $stage.find(this.selectors.stage.titleText).html($stage.find(this.selectors.stage.title).val());
      $stage.find(this.selectors.stage.priceText).text(Utils.priceFormat($stage.find(this.selectors.stage.price).val()));
    }
    /**
     * Выделить текущую задачу
     * @param $stage
     */

  }, {
    key: "selectCurrentStage",
    value: function selectCurrentStage($stage) {
      $(this.selectors.stage.item).removeClass(this.classes.stageActive);
      $stage.addClass(this.classes.stageActive);
    }
    /**
     * Заблокировать кнопку добавления задачи
     */

  }, {
    key: "disableAddStage",
    value: function disableAddStage() {
      this.stagesOfferBlock.find(this.selectors.stage.add).addClass(this.classes.disableAddStage);
    }
    /**
     * Разблокировать кнопку добавления задачи
     */

  }, {
    key: "enableAddStage",
    value: function enableAddStage() {
      this.stagesOfferBlock.find(this.selectors.stage.add).removeClass(this.classes.disableAddStage);
    }
    /**
     * Разблокировать ли кнопку добавления задач. Валидация всех задач без отображения ошибок
     * @param {boolean} notChangeShowErrors
     * @returns {boolean}
     */

  }, {
    key: "isEnableAddStage",
    value: function isEnableAddStage(notChangeShowErrors) {
      this.validateStages(notChangeShowErrors);

      if (this.errorsStage.length || this.checkSumTotalPrice() || !this.checkStageCountLimit()) {
        this.disableAddStage();
        return false;
      } else {
        this.enableAddStage();
        return true;
      }
    }
    /**
     * Построить html задачи
     * @param {number} position
     * @param {number} numberId
     * @returns {string}
     */

  }, {
    key: "buildStage",
    value: function buildStage(position, numberId) {
      var htmlDefault = this.stagesOfferBlock.find(this.selectors.stage.itemDefault).html();
      this.stagesOfferBlock.find(this.selectors.stages).append(htmlDefault);
      var stageHtml = this.stagesOfferBlock.find(this.selectors.stages).children().eq(position - 1);
      stageHtml.data('number', numberId).attr('data-number', numberId).data('position', position).attr('data-position', position).attr('data-name', 'stage-' + numberId + '-title').addClass(this.classes.stageItem);
      stageHtml.find(this.selectors.stage.number).text(numberId + '.');
      stageHtml.find(this.selectors.stage.title).attr('name', 'stages[' + numberId + '][title]');
      stageHtml.find(this.selectors.stage.price).attr('name', 'stages[' + numberId + '][payer_price]').attr('placeholder', this.options.offer.stageMinPrice === this.options.offer.customMaxPrice ? this.options.offer.stageMinPrice : Utils.priceFormat(this.options.offer.stageMinPrice) + ' - ' + Utils.priceFormat(this.options.offer.customMaxPrice - this.totalPrice));
      stageHtml.show();
      stageHtml.addClass(this.classes.stageEdit);

      if (window.offerForm) {
        window.offerForm.initEventListeners(this.stagesOfferBlock);
      }
    }
    /**
     * Меняем плесходлдер в зависимости от введенных стоимостей других задач
     * @returns {Void}
     */

  }, {
    key: "calcPlaceholder",
    value: function calcPlaceholder() {
      var placeholderText = '';
      var stageMinPrice = this.options.offer.stageMinPrice;
      var customMaxPrice = this.options.offer.customMaxPrice;

      if (stageMinPrice === customMaxPrice || customMaxPrice - this.totalPrice <= stageMinPrice) {
        placeholderText = stageMinPrice;
      } else {
        placeholderText = Utils.priceFormat(stageMinPrice) + ' - ' + Utils.priceFormat(customMaxPrice - this.totalPrice);
      }

      $(this.selectors.stage.price).attr('placeholder', placeholderText);
    }
    /**
     * Заполнить данными задачу
     * @param {Object} values
     * @param {number} values.number - порядковый номер задачи
     * @param {string} values.title - название задачи
     * @param {string} values.payer_price - стоимость задачи
     */

  }, {
    key: "setDataStage",
    value: function setDataStage(values) {
      var $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-number='" + values.number + "']");
      $stage.data('id', values.id).attr('data-id', values.id);
      $stage.find(this.selectors.stage.title).val(values.title);
      $stage.find(this.selectors.stage.titleText).html(values.title);
      $stage.find(this.selectors.stage.titleEditor).html(values.title);
      $stage.find(this.selectors.stage.price).val(parseInt(values.payer_price));
      $stage.find(this.selectors.stage.priceText).text(Utils.priceFormat(values.payer_price));
      $stage.removeClass(this.classes.stageEdit);
    }
    /**
     * Проставить плейсхолеры для чистых задач для покупателя, если в предложении не было задач
     */

  }, {
    key: "setPlaceholderTitle",
    value: function setPlaceholderTitle() {
      this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.title).attr('placeholder', t('Первая задача'));
      this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='2']").find(this.selectors.stage.title).attr('placeholder', t('Вторая задача'));
      this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='3']").find(this.selectors.stage.title).attr('placeholder', t('Третья задача'));
    }
    /**
     * Обноление плейсхолдера для цены задачи
     * Актуально в диалогах, когда меняется категрия. В зависимости от категрии может быть разная минимальная стоимость задачи
     */

  }, {
    key: "updatePlaceholderPrice",
    value: function updatePlaceholderPrice() {
      var _this8 = this;

      $.each($(this.selectors.stage.item), function (k, v) {
        var stage = $(v);
        var stageId = stage.data('position');
        stage.find(_this8.selectors.stage.price).attr('name', 'stages[' + stageId + '][payer_price]').attr('placeholder', _this8.options.offer.stageMinPrice === _this8.options.offer.customMaxPrice ? _this8.options.offer.stageMinPrice : Utils.priceFormat(_this8.options.offer.stageMinPrice) + ' - ' + Utils.priceFormat(_this8.options.offer.customMaxPrice));
      });
    }
    /**
     * Обновление контролов управления задачи
     */

  }, {
    key: "updateControlHide",
    value: function updateControlHide() {
      // для всех задач показываем кнопку удаления
      this.stagesOfferBlock.find(this.selectors.stage.remove).removeClass('hidden'); // на странице трэка покупатель не может удалить первую задачу при редактировании, если минимально возможной стоимости > 0
      // или на бирже и диалогах первую задачу удалить нельзя

      if (this.options.pageType === this.PAGE_TRACK && this.options.offer.customMinPrice > 0 || this.options.pageType !== this.PAGE_TRACK) {
        this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]').find(this.selectors.stage.remove).addClass('hidden');
      } // покупатель должен заполнить минимум 2 задачи при выборе задачной оплаты на бирже или в диалогах


      if (this.options.actorType === this.ACTOR_TYPE_PAYER && this.options.pageType !== this.PAGE_TRACK) {
        this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="2"]').find(this.selectors.stage.remove).addClass('hidden');
      }
    }
  }, {
    key: "getMaxAndMinNumber",
    value: function getMaxAndMinNumber() {
      var maxNumber = 0;
      var minNumber = this.stagesOfferBlock.find(this.selectors.stage.item).eq(0).data('number');
      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        var curNumber = $(v).data('number');

        if (parseInt(curNumber) > parseInt(maxNumber)) {
          maxNumber = curNumber;
        }

        if (parseInt(curNumber) < parseInt(minNumber)) {
          minNumber = curNumber;
        }
      });
      return {
        maxNumber: parseInt(maxNumber),
        minNumber: parseInt(minNumber)
      };
    }
    /**
     * Обновить селект порядквых номеров задачи
     * @param {Object} $stage
     */

  }, {
    key: "updateNumberSelectStage",
    value: function updateNumberSelectStage($stage) {
      var $select = $stage.find('select');
      var options = '';
      var stageId = parseInt($stage.data('number'));

      if ($select.val() === stageId) {
        return;
      }

      $select.html('');

      var _this$getMaxAndMinNum3 = this.getMaxAndMinNumber(),
          minNumber = _this$getMaxAndMinNum3.minNumber,
          maxNumber = _this$getMaxAndMinNum3.maxNumber;

      for (var i = minNumber; i <= maxNumber; i++) {
        var id = i;
        var position = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + i + '"]').data('position');
        options += '<option data-position="' + position + '" value="' + id + '"' + (stageId === id ? "selected" : "") + '>' + id + '</option>';
      }

      $select.append(options);
    }
    /**
     * Показать селект выбора порядкового номера задачи
     * @param {Object} $stage
     */

  }, {
    key: "showNumberSelectStage",
    value: function showNumberSelectStage($stage) {
      // на странице трэка нельзя менять порядковый номер задачи
      if (this.stagesOfferBlock.find(this.selectors.stage.item).length > 1 && this.options.pageType !== this.PAGE_TRACK) {
        $stage.find(this.selectors.stage.numberSelect).removeClass('hidden');
        $stage.find(this.selectors.stage.number).addClass('hidden');
      }
    }
    /**
     * Скрыть селект выбора порядкового номера задачи
     * @param {Object} $stage
     */

  }, {
    key: "hideNumberSelectStage",
    value: function hideNumberSelectStage($stage) {
      $stage.find(this.selectors.stage.numberSelect).addClass('hidden');
      $stage.find(this.selectors.stage.numberSelect).val([]);
      $stage.find(this.selectors.stage.number).removeClass('hidden');
    }
    /**
     * Сохранить новый порядковый номер задачи
     * @param {Object} $stage
     */

  }, {
    key: "saveNumberSelectStage",
    value: function saveNumberSelectStage($stage) {
      var $select = $stage.find('select');
      var newNumber = parseInt($select.val());

      if (isNaN(newNumber)) {
        newNumber = $stage.data('number');
      }

      var _this$getMaxAndMinNum4 = this.getMaxAndMinNumber(),
          minNumber = _this$getMaxAndMinNum4.minNumber,
          maxNumber = _this$getMaxAndMinNum4.maxNumber;

      if (maxNumber < newNumber) {
        newNumber = maxNumber;
      }

      var newPosition = parseInt($select.find('option[value="' + newNumber + '"]').data('position'));
      this.updateSortStages($stage, newNumber, newPosition);
      this.updateControlHide();
    }
    /**
     * Обновление сортировки задач
     * @param {Object} $stage
     * @param {number} newNumber
     * @param {number} newPosition
     */

  }, {
    key: "updateSortStages",
    value: function updateSortStages($stage, newNumber, newPosition) {
      var oldNumber = $stage.data('number');
      OfferStages.setStageNewNumber($stage, newNumber);
      OfferStages.setStageNewPosition($stage, newPosition);
      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        var $curStage = $(v);
        var curNumber = parseInt($curStage.data('number'));
        var curPosition = parseInt($curStage.data('position'));

        if (curNumber < oldNumber && curNumber >= newNumber) {
          OfferStages.setStageNewNumber($curStage, curNumber + 1);
          OfferStages.setStageNewPosition($curStage, curPosition + 1);
        }

        if (curNumber > oldNumber && curNumber <= newNumber) {
          OfferStages.setStageNewNumber($curStage, curNumber - 1);
          OfferStages.setStageNewPosition($curStage, curPosition - 1);
        }
      });
      this.updateStageNumber();
      this.reBuildStages();
    }
    /**
     * Устанавливаем новый номер задачи
     * @param {Object} $stage
     * @param {number} number
     */

  }, {
    key: "updateStageNumber",

    /**
     * Обновляем номера задач
     */
    value: function updateStageNumber() {
      var _this9 = this;

      this.stagesOfferBlock.find(this.selectors.stage.item).each(function (k, v) {
        var $stage = $(v);
        var number = $stage.data('new-number') || $stage.data('number');
        var position = $stage.data('new-position') || $stage.data('position');
        $stage.attr('data-position', position).data('position', position).attr('data-number', number).data('number', number);
        $stage.find(_this9.selectors.stage.number).text(number + '.');
        $stage.find(_this9.selectors.stage.title).attr('name', 'stages[' + number + '][title]');
        $stage.find(_this9.selectors.stage.price).attr('name', 'stages[' + number + '][payer_price]');
      });
    }
    /**
     * Перестраиваем задачи по высоте
     */

  }, {
    key: "reBuildStages",
    value: function reBuildStages() {
      var heightStages = 0;

      for (var i = 1; i <= this.stagesOfferBlock.find(this.selectors.stage.item).length; i++) {
        var $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='" + i + "']");
        var heightName = $stage.find(this.selectors.stage.titleBlock).height();
        var heightError = $stage.find(this.selectors.stage.error).height();
        var heightStage = $(window).width() < 768 ? 40 : 40; // если задача не редактируется и высота названия больше заданной

        if (!$stage.hasClass(this.classes.stageEdit) && heightName > heightStage) {
          heightStage = heightName;
        } // если задача редактируется


        if ($stage.hasClass(this.classes.stageEdit)) {
          heightStage = $(window).width() < 768 ? 80 : 40;
        }

        var heightInfo = heightStage; // если есть ошибки в задаче

        if ($stage.hasClass(this.classes.stageError)) {
          heightInfo = $(window).width() < 768 ? 80 : 40;
          heightStage = heightInfo + heightError;
        }

        heightStages = heightStages + heightStage;
        $stage.css({
          top: heightStages - heightStage,
          height: heightStage + 'px'
        });
        $stage.find(this.selectors.stage.infoBlock).css({
          height: heightInfo
        });
      }

      this.stagesOfferBlock.find(this.selectors.stages).css({
        height: heightStages
      });
    }
    /**
     * Для некотрых заказов нельзя вводить кириллицу
     * @param $input
     */

  }, {
    key: "controlLang",
    value: function controlLang($input) {
      if (this.options.controlEnLang) {
        var newValue = $input.val().replace(/[А-Яа-яЁё]/g, '');
        $input.val(newValue);
      }
    }
    /**
     * Добавление ошибки в массив
     * @param {Object} values
     */

  }, {
    key: "addErrorStage",
    value: function addErrorStage(values) {
      this.removeErrorStage(values);
      this.errorsStage.push(values);
    }
    /**
     * Удаляем ошибки из массива
     * @param {Object} values
     */

  }, {
    key: "removeErrorStage",
    value: function removeErrorStage(values) {
      this.errorsStage = this.errorsStage.filter(function (v) {
        return !(v.target === values.target && v.position === values.position);
      });
    }
    /**
     * Показать ошибки задач с бэка
     * @param {Array} errors
     * @param {boolean} isScrollToError
     */

  }, {
    key: "showBackendStageErrors",
    value: function showBackendStageErrors(errors) {
      var _this10 = this;

      var isScrollToError = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

      if (errors.length) {
        this.defaultNotChangeShowErrorsBlock();
        $.each(errors, function (k, v) {
          if (v.target === 'price') {
            _this10.showTotalPriceError(v.text);

            return true;
          } // если ошибка при изменении срока покупателем


          if (v.target === 'days') {
            _this10.showDurationChangeError(v.text, 'red');

            return true;
          }

          _this10.showStageError(v);

          _this10.editStage(_this10.stagesOfferBlock.find(_this10.selectors.stage.item).filter('[data-number="' + v.position + '"]'));
        });

        if (isScrollToError) {
          OfferStages.scrollToError(this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + errors[0].position + '"]'));
        }
      }
    }
    /**
     * Показать ошибки задач
     */

  }, {
    key: "showAllStageErrors",
    value: function showAllStageErrors() {
      var _this11 = this;

      if (this.notChangeShowErrorsBlock || !this.errorsStage.length) {
        return;
      }

      $.each(this.errorsStage, function (k, v) {
        _this11.showStageError(v); // если ошибка в цене


        if (v.target === 'payer_price') {
          _this11.showTotalPriceError('');
        }
      });
      this.reBuildStages();
    }
    /**
     * Отображение ошибки для текущего изменяемого поля
     * @param {Object} $input
     */

  }, {
    key: "showCurrentError",
    value: function showCurrentError($input) {
      var currentTarget = $input.data('target');
      var currentPosition = $input.parents(this.selectors.stage.item).data('number');
      var currentError = this.errorsStage.filter(function (item) {
        return item.target === currentTarget && item.position === currentPosition;
      });
      this.hideStageInputError($input);

      if (currentError.length) {
        this.showStageError(currentError[0]);
      }

      this.reBuildStages();
    }
    /**
     * Показать одну ошибку задачи
     * @param {Object} values
     */

  }, {
    key: "showStageError",
    value: function showStageError(values) {
      var position = values.position || 1;
      var $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + position + '"]');
      $stage.addClass(this.classes.stageError);
      $stage.find('input[data-target="' + values.target + '"]').addClass('error');
      $stage.find('select[data-target="' + values.target + '"]').addClass('error');
      $stage.find(this.selectors.stage.error).find('[data-target="' + values.target + '"]').html(values.text.replace(/\.+\s*$/, '') + '. ');
    }
    /**
     * Скрыть ошибку задачи
     * @param {Object} $input
     */

  }, {
    key: "hideStageInputError",
    value: function hideStageInputError($input) {
      var $stage = $input.parents(this.selectors.stage.item);

      if (!this.notChangeShowErrorsBlock) {
        // убираем отображение ошибки
        $input.removeClass('error');
        $stage.find(this.selectors.stage.error).find('[data-target="' + $input.data('target') + '"]').html('');

        if ($stage.find(this.selectors.stage.error).find('span').text() === '') {
          $stage.removeClass(this.classes.stageError);
        }

        this.reBuildStages();
      }
    }
    /**
     * Блокирвать ли кнопку отправки формы
     * @returns {boolean}
     */

  }, {
    key: "checkDisableButtonSend",
    value: function checkDisableButtonSend() {
      return this.errorsStage.length !== 0 || this.isDisableButtonSend || this.durationError;
    }
    /**
     * Скролл до задачи с ошибкой
     * @param {Object} $stage
     */

  }, {
    key: "showDurationChangeError",

    /**
     * Показать ошибку для изменения срока для покупателя
     * @param {string} error
     * @param {string} level
     */
    value: function showDurationChangeError(error, level) {
      this.stagesOfferBlock.find(this.selectors.durationChangeError).text(error);

      if (level === 'red') {
        this.stagesOfferBlock.find(this.selectors.duration).addClass(this.classes.durationError);
        this.durationError = true;
      } else {
        this.stagesOfferBlock.find(this.selectors.duration).addClass(this.classes.durationErrorLight);
      }
    }
    /**
     * Скрыть ошибку для изменения срока для покупателя
     */

  }, {
    key: "hideDurationChangeError",
    value: function hideDurationChangeError() {
      this.durationError = false;
      this.stagesOfferBlock.find(this.selectors.durationChangeError).text('');
      this.stagesOfferBlock.find(this.selectors.duration).removeClass(this.classes.durationError).removeClass(this.classes.durationErrorLight);
    }
    /**
     * Показать ошибку итоговой стоимости
     * @param {string} error
     */

  }, {
    key: "showTotalPriceError",
    value: function showTotalPriceError(error) {
      if (this.notChangeShowErrorsBlock) {
        return;
      } // если задача одина, то отображать ошибку итоговой стоимости под этой задачи


      if (this.stagesOfferBlock.find(this.selectors.stage.item).length === 1) {
        var firstStageNumber = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]').data('number'); // если задача одина, то отображать ошибку под этой задачи

        this.showStageError({
          position: firstStageNumber,
          target: 'payer_price',
          text: error
        });
        this.addErrorStage({
          position: firstStageNumber,
          target: 'payer_price',
          text: error
        });
        this.reBuildStages(); // при добавлении 2ой задачи нужно ошибку около 1ой задачи убирать

        this.hideTotalPriceError();
      } else {
        $(this.selectors.totalPrice.wrap).addClass('error');
        $(this.selectors.totalPrice.error).html(error);
        $(this.selectors.totalPrice.priceCommission).addClass('hidden'); // если задач больше чем один, то скрывать ошибку итоговой стоимости под этих задач

        this.hideTotalPriceErrorByFirstStage();
      }
    }
    /**
     * Скрыть ошибку итоговой цены
     */

  }, {
    key: "hideTotalPriceError",
    value: function hideTotalPriceError() {
      if (this.notChangeShowErrorsBlock) {
        return;
      }

      $(this.selectors.totalPrice.wrap).removeClass('error');
      $(this.selectors.totalPrice.error).html('');
    }
    /**
     * Скрыть ошибку итоговой цены по первой задачи
     */

  }, {
    key: "hideTotalPriceErrorByFirstStage",
    value: function hideTotalPriceErrorByFirstStage() {
      var $firstStage = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]');
      var firstStageNumber = $firstStage.data('number');
      this.hideStageInputError($firstStage.find(this.selectors.stage.price));
      this.removeErrorStage({
        position: firstStageNumber,
        target: 'payer_price'
      });
    }
    /**
     * Привести к дефолтному значению переменную notChangeShowErrorsBlock
     */

  }, {
    key: "defaultNotChangeShowErrorsBlock",
    value: function defaultNotChangeShowErrorsBlock() {
      this.notChangeShowErrorsBlock = false;
    }
    /**
     * Заблокировать кнопку отправки
     */

  }, {
    key: "disableButtonSend",
    value: function disableButtonSend() {
      $(this.options.classDisableButton).prop('disabled', true).addClass('disabled');
      this.setButtonSend(true);
    }
  }, {
    key: "enableButtonSend",

    /**
     * Разблокировать кнопку отправки
     */
    value: function enableButtonSend() {
      $(this.options.classDisableButton).prop('disabled', '').removeClass('disabled');
      this.setButtonSend(false);
    }
  }, {
    key: "setButtonSend",

    /**
     * Установить значение для переменной isDisableButtonSend
     * @param value
     */
    value: function setButtonSend(value) {
      this.isDisableButtonSend = value;
    }
  }], [{
    key: "setStageNewNumber",
    value: function setStageNewNumber($stage, number) {
      $stage.attr('data-new-number', number).data('new-number', number);
    }
    /**
     * Устанавливаем новую позицию задачи
     * @param {Object} $stage
     * @param {number} position
     */

  }, {
    key: "setStageNewPosition",
    value: function setStageNewPosition($stage, position) {
      $stage.attr('data-new-position', position).data('new-position', position);
    }
  }, {
    key: "scrollToError",
    value: function scrollToError($stage) {
      if ($stage.length) {
        $('html, body').animate({
          scrollTop: $stage.offset().top - $('.header').height() - 23
        }, 'slow');
      }
    }
  }]);

  return OfferStages;
}();
window.offerStages = new OfferStages();

/***/ }),

/***/ "./public_html/js/pages/new-offer/bootstrap.js":
/*!*****************************************************!*\
  !*** ./public_html/js/pages/new-offer/bootstrap.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! appJs/stages/offer-stages.js */ "./public_html/js/app/stages/offer-stages.js");

__webpack_require__(/*! appJs/offer-individual.js */ "./public_html/js/app/offer-individual.js");

/***/ }),

/***/ 20:
/*!***********************************************************!*\
  !*** multi ./public_html/js/pages/new-offer/bootstrap.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\new-offer\bootstrap.js */"./public_html/js/pages/new-offer/bootstrap.js");


/***/ })

/******/ });