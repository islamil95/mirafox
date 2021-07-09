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
/******/ 	return __webpack_require__(__webpack_require__.s = 16);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/app/text.js":
/*!************************************!*\
  !*** ./public_html/js/app/text.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

window.htmlToText = function (messageBody, handleImages) {
  messageBody = messageBody.replace(/\r?\n/g, '');
  messageBody = messageBody.replace(/<[\s]*\/[\s]*?img[\s]*?>/g, '');
  var mode = 0;
  var lines = [];
  var image = '';
  var text = '';
  var tag = '';

  for (var i = 0; i < messageBody.length; i++) {
    var symbol = messageBody[i];

    if (mode == 0) {
      if (symbol == '<') {
        tag = symbol;
        mode = 1;
      } else {
        text += symbol;
      }
    } else if (mode == 1) {
      tag += symbol;

      if (symbol == '>') {
        var tagName = tag.match(/^<[\/\s]*?([^\/\s>]+)/)[1];

        if (tagName == 'img') {
          if (handleImages) {
            if (text.length > 0) {
              lines.push(image + text);
              image = '';
              text = '';
            }

            image += tag;
          }
        } else if (tagName == 'br') {
          lines.push(image + text);
          image = '';
          text = '';
        } else if (tagName == 'p' || tagName == 'div') {
          if (text.length > 0) {
            lines.push(image + text);
            image = '';
            text = '';
          }
        }

        mode = 0;
      }
    }
  }

  if (image.length > 0 || text.length > 0) {
    lines.push(image + text);
  }

  messageBody = lines.join('\n');

  if (handleImages) {
    var re = new RegExp('<img[^>]+?' + window.filesMiniatureUrl + '[^>]+?>', 'gi');
    messageBody = messageBody.replace(re, function (str) {
      var dom = $($.parseHTML(str));
      var src = dom.attr('src').replace(window.filesMiniatureUrl + '/', '');
      var id = dom.data('id');
      return '[attached-img id="' + id + '"]';
    });
  }

  messageBody = messageBody.replace(/<\/?[^>]*>/gi, '');
  messageBody = he.decode(messageBody);
  messageBody = messageBody.replace(/^[ \r\n\f\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  return messageBody;
};

/***/ }),

/***/ "./public_html/js/app/validatable-form.js":
/*!************************************************!*\
  !*** ./public_html/js/app/validatable-form.js ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var _default =
/*#__PURE__*/
function () {
  function _default(selector) {
    var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    _classCallCheck(this, _default);

    this.onUpdate = args.onUpdate;
    this.formEl = $(selector);
    this.bottomError = this.formEl.find('.vf-bottom-error');
    this.textCheckErrors = {
      'bad_words': t('Исключите запрещенные слова: '),
      'duplicate_symbols': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
      'big_word': t('Превышена максимальная длина слов'),
      'small_word': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
      'word_mistakes': t('Необходимо исправить ошибки или опечатки в тексте.\nСлова с ошибками подчеркнуты красным.')
    };
    this.plannedCheck = {};
    this.textCheckTimeout = null;
    this.textCheckXhr = null;
    this.fields = {};
    this.isBusy = false;
    this.initEventListeners();
  }

  _createClass(_default, [{
    key: "isValidated",
    value: function isValidated(fieldNames) {
      if (this.isBusy) {
        return false;
      }

      var validated = true;
      $.each(this.fields, function (k, v) {
        if (fieldNames) {
          if ($.inArray(k, fieldNames) == -1) {
            return true;
          }
        }

        if (v.status != 'success') {
          validated = false;
          return false;
        }
      });
      return validated;
    }
  }, {
    key: "initEventListeners",
    value: function initEventListeners(startEl) {
      var _this = this;

      var rootEl = this.formEl;

      if (startEl) {
        rootEl = startEl;
      }

      rootEl.find('.vf-block').each(function (k, v) {
        var block = $(v);
        var name = block.data('name');

        if (!name) {
          return true;
        }

        var initialized = block.data('initialized');

        if (initialized) {
          return true;
        }

        var el = block.find('.vf-field');
        var isLong = el.data('mistakePercentLong') === true ? 1 : 0;
        var noHint = el.data('noHint') ? 1 : 0;
        var fieldId = el.data('fieldId');
        var errorOutputToData = false;
        var errorOutputDataBlock = null;
        var errorOutputSelector = block.data('errorOutput');

        if (errorOutputSelector) {
          errorOutputDataBlock = block.find(errorOutputSelector);

          if (errorOutputDataBlock.length) {
            errorOutputToData = true;
          } else {
            errorOutputDataBlock = null;
          }
        }

        var errorOutput = block.find('.vf-error');
        var errorNoHide = errorOutput.length > 0 ? errorOutput.hasClass('no-hide') : false;
        var en = el.data('en');
        _this.fields[name] = {
          block: $(v),
          errorOutput: errorOutput,
          errorNoHide: errorNoHide,
          el: el,
          isLong: isLong,
          noHint: noHint,
          fieldId: fieldId,
          status: 'success',
          en: en,
          errorOutputToData: errorOutputToData,
          errorOutputDataBlock: errorOutputDataBlock
        };
        el.on('input', function () {
          _this.checkField(name);
        });
        block.data('initialized', true);
      });
    }
  }, {
    key: "checkField",
    value: function checkField(name) {
      var _this2 = this;

      var field = this.fields[name]; // Скрываем текст ошибки

      if (!field.errorOutputToData && (!field.errorNoHide || field.errorOutput.data('isTemp')) && field.errorOutput.length > 0) {
        field.errorOutput.text('');
      }

      this.isBusy = true;

      if (this.onUpdate) {
        this.onUpdate();
      }

      if (this.textCheckXhr) {
        this.textCheckXhr.abort();
      }

      var html = field.el.html();
      var fData = {
        string: html,
        isLong: field.isLong,
        noHint: field.noHint
      };

      if (field.fieldId) {
        fData.field = field.fieldId;
      }

      this.plannedCheck[name] = fData;

      if (this.textCheckTimeout) {
        clearTimeout(this.textCheckTimeout);
        this.textCheckTimeout = null;
      }

      this.textCheckTimeout = setTimeout(function () {
        var data = {
          data: _this2.plannedCheck,
          lang: field.en ? 'en' : 'ru',
          checkAll: true
        };
        _this2.textCheckXhr = $.post('/api/kwork/checktext', data, function (r) {
          _this2.textCheckXhr = null;
          _this2.plannedCheck = {}; // Скрываем текст ошибки

          if (!field.errorOutputToData) {
            if (field.errorNoHide && field.errorOutput.length > 0) {
              field.errorOutput.text('');
            }
          } else {
            field.errorOutputDataBlock.data('backendError', '');
          }

          if (!('data' in r)) {
            return;
          }

          var sel = rangy.getSelection();
          var ranges = sel.getAllRanges();

          var range = _.last(ranges);

          var editor = null;

          if (range) {
            var rangeEl = $(range.startContainer);
            editor = rangeEl.closest('.js-content-editor');
          }

          var needCursorSave = false;

          if (editor && editor.length > 0) {
            needCursorSave = true;
          } // если курсор находится в поле для валидации (не в обычном input, например)


          var keepedSelection = null;

          if (needCursorSave) {
            // Сохраняем позицию курсора
            try {
              keepedSelection = rangySelectionSaveRestore.saveSelection();
            } catch (e) {}
          }

          _this2.isBusy = false;

          _this2.handleBackendValidation(r);

          if (needCursorSave) {
            // восстанавливаем курсор
            if (keepedSelection) {
              try {
                rangySelectionSaveRestore.restoreSelection(keepedSelection);
              } catch (e) {}
            }
          }
        });
      }, 1000);
    }
  }, {
    key: "reset",
    value: function reset() {
      this.formEl.find('.vf-error').text('');
    }
  }, {
    key: "handleBackendValidation",
    value: function handleBackendValidation(r) {
      var _this3 = this;

      var bottomErrorText = r.error || r.response || '';
      var data = r.data || r.errorsDetail || r.errors || [];
      $.each(data, function (k, v) {
        var target = v.target || k;
        var errorText = ''; // Орфографические ошибки

        var validError = v.validError || v.text || '';

        if (validError) {
          errorText = validError;

          if (validError in _this3.textCheckErrors) {
            errorText = _this3.textCheckErrors[validError];
          }
        } // Наличие недопустимых слов


        if ('badWords' in v && v.badWords) {
          errorText = _this3.textCheckErrors['bad_words'] + v.string;
        } // Если поля нет - переводим ошибку под кнопку отправки


        if (!(target in _this3.fields)) {
          if (errorText.length > 0) {
            bottomErrorText += '<div>' + errorText + '</div>';
          }

          return true;
        }

        var field = _this3.fields[target]; // Визуализируем ошибки

        var elHtml = field.el.html();
        var mistakes = v.mistakes || [];
        var newHtml = applyWordErrors(elHtml, mistakes);
        field.el.html(newHtml); // Меняем статус поля

        field.status = errorText.length > 0 ? 'error' : 'success';

        if (!field.errorOutputToData) {
          if (field.errorOutput.length > 0) {
            field.errorOutput.html(errorText).data('isTemp', false);
          } else {
            bottomErrorText += '<div>' + errorText + '</div>';
          }
        } else {
          field.errorOutputDataBlock.data('backendError', errorText);
          field.errorOutputDataBlock.trigger('input'); // Скрываем текст ошибки

          if ((!field.errorNoHide || field.errorOutput.data('isTemp')) && field.errorOutput.length > 0) {
            field.errorOutput.text('');
          }
        }
      });

      if (bottomErrorText.length > 0 && this.bottomError.length > 0) {
        this.bottomError.html(bottomErrorText);
      }

      if (this.onUpdate) {
        this.onUpdate();
      }
    }
  }]);

  return _default;
}();



/***/ }),

/***/ "./public_html/js/pages/projects/bootstrap.js":
/*!****************************************************!*\
  !*** ./public_html/js/pages/projects/bootstrap.js ***!
  \****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var appJs_validatable_form_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! appJs/validatable-form.js */ "./public_html/js/app/validatable-form.js");
__webpack_require__(/*! appJs/text.js */ "./public_html/js/app/text.js");


$(document).ready(function () {
  var form = $('#offer_kwork_form');

  if (form.length > 0) {
    window.offerForm = new appJs_validatable_form_js__WEBPACK_IMPORTED_MODULE_0__["default"]('#offer_kwork_form', {
      onUpdate: window.OfferIndividualModule.validateIndividualKwork
    });
  }
});

/***/ }),

/***/ 16:
/*!**********************************************************!*\
  !*** multi ./public_html/js/pages/projects/bootstrap.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\projects\bootstrap.js */"./public_html/js/pages/projects/bootstrap.js");


/***/ })

/******/ });