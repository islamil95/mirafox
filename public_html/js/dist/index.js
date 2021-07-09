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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/app/search.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./public_html/js/app/search.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var appJs_i18n_mixin__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! appJs/i18n-mixin */ "./public_html/js/app/i18n-mixin.js");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/**
 * Поле для ввода поисковой строки с подсказками. Обязательные свойства:
 *   suggestionsEndpoint - url для загрузки поисковых подсказок
 *   suggestionsData - дополнительные данные для загрузки поисковых подсказок (объект),
 *   перед передачей в url в объект будут добавлены свойства:
 *     query - строка поиска
 *     spellerResponse - ответ спеллеря Яндекса (результат проверки строки поиска на ошибки)
 *   clearHistoryEndpoint - url для очистки истории запросов
 *  Опционально можно задать:
 *    placeholder - плейсходер для поля ввода
 *    defaultSearch - запрос по умолчанию, который появляется в строке поиска при сбросе 
 *      (кнопка с крестиком). Если не задано, то при сбросе строка поиска очищается
 *    suggestionsHeader - заголовок для выпадающего списка с поисковыми подсказками
 *  Методы:
 *    clear - очистить строку поиска
 *  Компонент генерирует события:
 *    search-executed - пользователь попросил выполнить поиск по строке, в событие передается
 *      строка поиска (набранная пользователем вручную или из поисковых подсказок)
 */
// Локализация

/* harmony default export */ __webpack_exports__["default"] = ({
  mixins: [appJs_i18n_mixin__WEBPACK_IMPORTED_MODULE_0__["default"]],
  data: function data() {
    return {
      // Строка поиска
      search: "",
      // Поисковые подсказки
      suggestions: [],
      // Выбранная поисковая подсказка
      selectedSuggestion: "",
      // Запрос поисковых подсказок
      // (для возможности отмены)
      axiosRequest: null,
      // Запросы спеллера/поисковых подсказок в данный момент выполняются
      // (не запускать новые)
      requestsExecuting: false,
      // Можно ли выводить подсказки при изменении строки поиска. Нужно
      // в следующих случаях:
      // 1) Пользователь выбрал подсказку. В этот момент строка поиска должна
      // измениться на выбранную подсказку, но повторно давать подсказки уже
      // не нужно
      // 2) Пользователь нажал на кнопку "очистить поиск". В этот момент нужно обнулить
      // строку поиска, но при этом не выдавать историю запросов
      suggestWhenSearchChanged: true,
      // Можно ли очищать подсказки при потере фокуса строкой поиска. Нужно
      // в случае, если пользователь кликнул мышкой (не выбрал стрелками на
      // клавиатуре и нажал Enter, а именно кликнул) на подсказке. В этом случае
      // событие blur срабатывает раньше click и очищает список подсказок (click,
      // соответственно, не вызывается, т.к. элемента с подсказкой уже нет на
      // странице)
      clearSuggestionsOnBlur: true,
      // Флаг устанавливается, если в данный момент показаны подсказки
      // по пустому запросу (т.е. история запросов пользователя)			
      historySuggested: true,
      // Локализация компонента
      i18n: {
        en: {
          "Недавний поиск": "Recent search",
          "Очистить": "Delete"
        }
      }
    };
  },
  props: [// Url для загрузки поисковых подсказок
  "suggestionsEndpoint", // Дополнительные данные для загрузки поисковых подсказок
  "suggestionsData", // Url для очистки истории запросов
  "clearHistoryEndpoint", // Плейсходлер для поля ввода
  "placeholder", // Запрос по умолчанию, который появляется в строке
  // поиска при сбросе (кнопка с крестиком). Если не задано, 
  // то при сбросе строка поиска очищается
  "defaultSearch", // Заголовок для выпадающего списка с поисковыми подсказками
  "suggestionsHeader", // Имя поиска
  "searchName"],
  computed: {
    // Показать кнопку сброса поиска, если текущий запрос отличается
    // от запроса по умолчанию (или, если не задан запрос по умолчанию,
    // то от пустой строки)
    showClearButton: function showClearButton() {
      return this.search != "";
    },
    // Для текущей строки поиска есть хотя бы одна подсказка
    // (развернут выпадающий список с подсказками)
    hasSuggestions: function hasSuggestions() {
      return this.suggestions.length > 0;
    },
    // В настоящий момент выбрана одна из поисковых подсказок
    suggestionSelected: function suggestionSelected() {
      return this.selectedSuggestion != '';
    },
    // Классы для поля ввода
    inputClass: function inputClass() {
      return {
        "has-suggestions": this.hasSuggestions,
        "suggestion-selected": this.suggestionSelected,
        "has-text": this.search != ""
      };
    }
  },
  watch: {
    // Изменилась строка поиска
    search: function search(val) {
      window.bus.$emit('search-' + this.searchName + '-change', this.search);

      if (this.suggestWhenSearchChanged) {
        this.onInput();
      } else {
        this.suggestWhenSearchChanged = true;
      }
    }
  },

  /**
   * Created event
   */
  created: function created() {
    var _this = this;

    // Инициализировать mixin локализации
    this.i18nInit(); // Заполнить значение строки поиска по умолчанию

    var defaultSearch = this.defaultSearch ? this.defaultSearch : "";
    this.changeSearchWithoutSuggesting(defaultSearch);
    window.bus.$on('search-' + this.searchName + '-change', function (val) {
      _this.search = val;
    });
  },
  methods: {
    /**
     * Обработчик события на ввод текста в строку поиска
     */
    onInput: function onInput() {
      var _this2 = this;

      if (this.requestsExecuting) {
        return;
      }

      this.requestsExecuting = true;
      var searchBeforeRequest = this.search; // Для строки поиска в 1 и 2 символа никаких подсказок не выводим

      if (this.search.length < 3 && this.search.length > 0) {
        this.historySuggested = false;
        this.suggestions = [];
        this.selectedSuggestion = "";
        this.requestsExecuting = false;
        return;
      }

      this.axiosRequest = axios.CancelToken.source();
      var suggestionsData = this.suggestionsData ? this.suggestionsData : {};
      suggestionsData.query = this.search;
      axios.post(this.suggestionsEndpoint, suggestionsData, {
        cancelToken: this.axiosRequest.token
      }).then(function (response) {
        _this2.historySuggested = _this2.search == "";
        _this2.suggestions = response.data.data.suggestions;
        _this2.selectedSuggestion = "";
        _this2.requestsExecuting = false; // Если за время выполнения запросов строка поиска изменилась,
        // повтроить вызов

        if (searchBeforeRequest != _this2.search) {
          _this2.onInput();
        }
      })["catch"](function (thrown) {});
    },

    /**
     * Обработчик события на нажатие кнопки вниз
     */
    onArrowDown: function onArrowDown() {
      if (this.suggestions.length == 0) {
        return;
      }

      var index = _.findIndex(this.suggestions, ["suggestion", this.selectedSuggestion]);

      if (index == -1) {
        index = 0;
      } else if (index >= this.suggestions.length - 1) {
        index = this.suggestions.length - 1;
      } else {
        index++;
      }

      this.selectedSuggestion = this.suggestions[index].suggestion;
    },

    /**
     * Обработчик события на нажатие кнопки вверх
     */
    onArrowUp: function onArrowUp() {
      if (this.suggestions.length == 0) {
        return;
      }

      var index = _.findIndex(this.suggestions, ["suggestion", this.selectedSuggestion]);

      if (index == -1) {
        return;
      } else if (index > 0) {
        index--;
        this.selectedSuggestion = this.suggestions[index].suggestion;
      } else {
        this.selectedSuggestion = "";
      }
    },

    /**
     * Обработчик события на нажатие Enter
     */
    onEnter: function onEnter() {
      // Остановить процесс формирования поисковых подсказок
      this.stopSuggestingProcess(); // Если подсказка выбрана, взять ее, иначе текст из строки поиска

      var val = this.selectedSuggestion != "" ? this.selectedSuggestion : this.search; // Изменить строку поиска без вывода подсказок

      this.changeSearchWithoutSuggesting(val); // Сообщить родителю, что строка поиска изменилась 
      // (можно выполнять поиск)

      this.$emit("search-executed", val); // Очистить список подсказок и выбранную подсказку

      this.clearSuggestions();
    },

    /**
     * Обработчик события на нажатие Escape
     */
    onEscape: function onEscape() {
      this.stopSuggestingProcess();
      this.clearSuggestions();
    },

    /**
     * Обработчик события на наведение мышкой на поисковую подсказку
     */
    onMouseOver: function onMouseOver(val) {
      this.selectedSuggestion = val;
    },

    /**
     * Обработчик события на покидание мышки поисковой подсказки
     */
    onMouseLeave: function onMouseLeave() {
      this.selectedSuggestion = "";
    },

    /**
     * Обработчик события на клик на поисковой подсказке
     */
    onSuggestionClick: function onSuggestionClick() {
      this.onEnter();
    },

    /**
     * Обработчик события mousedown на элементе выпадающего списка
     * (кнопка "очистить историю", поисковая подсказка и т.д.)
     */
    onDropdownMouseDown: function onDropdownMouseDown() {
      // Не очищать список подсказок при потере строкой поиска
      // фокуса (чтобы сработало событие click на элементе выпадающего списка)
      this.clearSuggestionsOnBlur = false;
    },

    /**
     * Обработчик события на нажатие кнопки "Сбросить поиск"
     */
    onClear: function onClear() {
      var emptySearch = ""; // Сбросить строку поиска без вывода подсказок

      this.changeSearchWithoutSuggesting(emptySearch); // Сообщить родителю, что строка поиска сбросилась 
      // (например, чтобы закрыть результаты поиска)

      this.$emit("search-clear", emptySearch); // Очистить список подсказок и выбранную подсказку

      this.clearSuggestions();
    },

    /**
     * Обработчик события на потерю фокуса полем
     * для ввода строки поиска
     */
    onBlur: function onBlur() {
      this.stopSuggestingProcess();

      if (this.clearSuggestionsOnBlur) {
        this.clearSuggestions();
      } else {
        this.clearSuggestionsOnBlur = true;
      }
    },

    /**
     * Обработчик события на фокуса поля
     * для ввода строки поиска
     */
    onFocus: function onFocus() {
      this.onInput();
    },

    /**
     * Очистить список подсказок и выбранную подсказку
     */
    clearSuggestions: function clearSuggestions() {
      this.suggestions = [];
      this.selectedSuggestion = "";
    },

    /**
     * Изменить строку поиска без вывода подсказок
     * @param {string} val новое значение для строки поиска
     */
    changeSearchWithoutSuggesting: function changeSearchWithoutSuggesting(val) {
      if (this.search == val) {
        return;
      } // Сбросить флаг suggestWhenSearchChanged, чтобы компонент не начал
      // заново формировать подсказки


      this.suggestWhenSearchChanged = false; // Изменить строку поиска

      this.search = val;
    },

    /**
     * Остановить процесс формирования поисковых подсказок:
     * 1) debounce ввода в строке поиска
     * 2) проверку спеллера
     * 3) загрузку подсказок
     */
    stopSuggestingProcess: function stopSuggestingProcess() {
      if (this.axiosRequest) {
        this.axiosRequest.cancel();
      }

      this.requestsExecuting = false;
    },

    /**
     * Сбросить строку поиска
     */
    clear: function clear() {
      this.onClear();
    },

    /**
     * Программно установить строку поиска без вывода подсказок
     * @param {string} search
     */
    setSearch: function setSearch(search) {
      this.changeSearchWithoutSuggesting(search);
    },

    /**
     * Очистить историю запросов пользователя
     */
    clearHistory: function clearHistory() {
      this.clearSuggestions();
      axios.post(this.clearHistoryEndpoint).then(function (response) {})["catch"](function (thrown) {});
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var appJs_i18n_mixin__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! appJs/i18n-mixin */ "./public_html/js/app/i18n-mixin.js");
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/**
 * Компонент отображает строку поиска на главной странице сайта.
 */
// Поиск
Vue.component("search", __webpack_require__(/*! appJs/search.vue */ "./public_html/js/app/search.vue")["default"]); // Локализация


/* harmony default export */ __webpack_exports__["default"] = ({
  mixins: [appJs_i18n_mixin__WEBPACK_IMPORTED_MODULE_0__["default"]],
  data: function data() {
    return {
      // Локализация компонента
      i18n: {
        en: {
          "Найти услуги": "Search",
          "Какая услуга вас интересует?": "What service are you looking for?",
          "Поиск услуг": "Services"
        }
      }
    };
  },

  /**
   * Created event
   */
  created: function created() {
    // Инициализировать mixin локализации
    this.i18nInit();
  },
  methods: {
    /**
     * Обработчик события на выполнение поиска
     * @param {string} search строка поиска
     */
    onSearchExecuted: function onSearchExecuted(search) {
      // Добавление открытия окна регистрации для незареганых
      var additionalArgs = '';
      var userId = USER_ID || 0;
      userId = parseInt(userId);

      if (userId < 1) {
        Cookies.set('registerPopupForce', '1', {
          expires: 2,
          path: '/',
          SameSite: 'Lax'
        });
      } // Сформировать url для запуска поиска


      var encodedQuery = $.param({
        'query': search
      });
      var url = window.location.origin + "/search?" + encodedQuery + "&c=0"; // Редирект на сформированный url

      window.location.href = url;
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/app/search.vue?vue&type=template&id=6d3d6370&":
/*!****************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./public_html/js/app/search.vue?vue&type=template&id=6d3d6370& ***!
  \****************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "custom-search" }, [
    _c("input", {
      directives: [
        {
          name: "model",
          rawName: "v-model",
          value: _vm.search,
          expression: "search"
        }
      ],
      staticClass: "form-control",
      class: _vm.inputClass,
      attrs: { type: "text", placeholder: _vm.placeholder },
      domProps: { value: _vm.search },
      on: {
        input: [
          function($event) {
            if ($event.target.composing) {
              return
            }
            _vm.search = $event.target.value
          },
          function($event) {
            _vm.search = $event.target.value
          }
        ],
        keydown: [
          function($event) {
            if (
              !$event.type.indexOf("key") &&
              _vm._k($event.keyCode, "up", 38, $event.key, ["Up", "ArrowUp"])
            ) {
              return null
            }
            $event.preventDefault()
            return _vm.onArrowUp($event)
          },
          function($event) {
            if (
              !$event.type.indexOf("key") &&
              _vm._k($event.keyCode, "down", 40, $event.key, [
                "Down",
                "ArrowDown"
              ])
            ) {
              return null
            }
            $event.preventDefault()
            return _vm.onArrowDown($event)
          },
          function($event) {
            if (
              !$event.type.indexOf("key") &&
              _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
            ) {
              return null
            }
            $event.preventDefault()
            return _vm.onEnter($event)
          }
        ],
        keyup: function($event) {
          if (
            !$event.type.indexOf("key") &&
            _vm._k($event.keyCode, "esc", 27, $event.key, ["Esc", "Escape"])
          ) {
            return null
          }
          $event.preventDefault()
          return _vm.onEscape($event)
        },
        blur: _vm.onBlur,
        focus: _vm.onFocus
      }
    }),
    _vm._v(" "),
    _vm.showClearButton
      ? _c(
          "span",
          {
            staticClass: "clear-button",
            on: {
              click: function($event) {
                $event.preventDefault()
                return _vm.onClear($event)
              }
            }
          },
          [_vm._t("clear-button", [_vm._v("×")])],
          2
        )
      : _vm._e(),
    _vm._v(" "),
    _c(
      "span",
      {
        staticClass: "search-button",
        class: [_vm.hasSuggestions ? "has-suggestions" : ""],
        on: {
          click: function($event) {
            $event.preventDefault()
            return _vm.onEnter($event)
          }
        }
      },
      [_vm._t("search-button", [_vm._m(0)])],
      2
    ),
    _vm._v(" "),
    _c(
      "div",
      {
        directives: [
          {
            name: "show",
            rawName: "v-show",
            value: _vm.suggestions.length > 0,
            expression: "suggestions.length > 0"
          }
        ],
        staticClass: "dropdown"
      },
      [
        _vm.historySuggested
          ? _c(
              "div",
              { staticClass: "history d-flex justify-content-between" },
              [
                _c("div", { staticClass: "d-inline-block" }, [
                  _vm._v(_vm._s(_vm.t("Недавний поиск")))
                ]),
                _vm._v(" "),
                _c(
                  "div",
                  {
                    staticClass: "d-inline-block clear-history-button",
                    on: {
                      mousedown: _vm.onDropdownMouseDown,
                      click: _vm.clearHistory
                    }
                  },
                  [
                    _vm._v(
                      "\n\t\t\t\t" + _vm._s(_vm.t("Очистить")) + "\n\t\t\t"
                    )
                  ]
                )
              ]
            )
          : _vm._e(),
        _vm._v(" "),
        _vm.suggestionsHeader && !_vm.historySuggested
          ? _c("div", { staticClass: "suggestions-header" }, [
              _c("div", [_vm._v(_vm._s(_vm.suggestionsHeader))])
            ])
          : _vm._e(),
        _vm._v(" "),
        _c(
          "div",
          { staticClass: "suggestions" },
          _vm._l(_vm.suggestions, function(suggestion) {
            return _c(
              "div",
              {
                staticClass: "suggestion d-flex justify-content-start",
                class: {
                  selected: suggestion.suggestion == _vm.selectedSuggestion
                },
                on: {
                  mouseover: function($event) {
                    return _vm.onMouseOver(suggestion.suggestion)
                  },
                  mouseleave: _vm.onMouseLeave,
                  mousedown: _vm.onDropdownMouseDown,
                  click: _vm.onSuggestionClick
                }
              },
              [
                _c("div", { staticClass: "d-inline-block" }, [
                  _c("span", {
                    domProps: { innerHTML: _vm._s(suggestion.excerpt) }
                  })
                ])
              ]
            )
          }),
          0
        )
      ]
    )
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("span", { staticClass: "fa-stack text-success" }, [
      _c("i", { staticClass: "fa fa-square fa-stack-2x" }),
      _vm._v(" "),
      _c("i", { staticClass: "fa fa-search fa-stack-1x fa-inverse" })
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214& ***!
  \**************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "search",
    {
      staticClass: "general-search-index d-none d-md-block",
      attrs: {
        "suggestions-endpoint": "/general-search/suggest",
        "clear-history-endpoint": "/general-search/clear-history",
        placeholder: _vm.t("Какая услуга вас интересует?"),
        "suggestions-header": _vm.t("Поиск услуг")
      },
      on: { "search-executed": _vm.onSearchExecuted }
    },
    [
      _c("template", { slot: "clear-button" }),
      _vm._v(" "),
      _c("template", { slot: "search-button" }, [
        _c("button", { staticClass: "button button-success" }, [
          _vm._v(_vm._s(_vm.t("Найти услуги")))
        ])
      ])
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return normalizeComponent; });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () { injectStyles.call(this, this.$root.$options.shadowRoot) }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ "./public_html/js/app/i18n-mixin.js":
/*!******************************************!*\
  !*** ./public_html/js/app/i18n-mixin.js ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * Mixin добавляет возможность локализации компонент. Перед использованием необходимо
 * вызвать метод i18nInit (например, в событии created). Далее в шаблоне .vue можно
 * локализовать строки при помощи метода t() или в скрипте, вызвав this.t().
 * 
 * В локализуемых строках можно использовать подстановки, например:
 *   t("это строка с первой {{0}} и второй {{1}} подстановками", ["подстановка 1", "подстановка 2"]);
 *   
 * Каждый компонент должен сам задавать свои переводы, например:
 *   data () {
 *     return {
 *       i18n: {
 *         en: {
 *           "строка на русском": "строка на английском",
 *            ...
 *         },
 *       },
 *     };
 *   },
 *
 * Если для строки требуется множественное число, то переводы задаются следующим образом:
 *   data () {
 *     return {
 *       i18n: {
 *         ru: {
 *           "{{0}} яблоко": {
 *             0: "{{0}} яблоко",
 *             1: "{{0}} яблока",
 *             2: "{{0}} яблок",
 *           },
 *         },
 *         en: {
 *           "{{0}} яблоко": {
 *             0: "{{0}} apple",
 *             1: "{{0}} apples",
 *           },
 *         },
 *       },
 *     };
 *   },
 */
/* harmony default export */ __webpack_exports__["default"] = ({
  data: function data() {
    return {
      // Данные для переводов
      i18n: null,
      // Текущая локаль
      locale: "ru",
      // Локаль по умолчанию
      defaultLocale: "ru"
    };
  },
  methods: {
    /**
     * Инициализация mixin
     */
    i18nInit: function i18nInit() {
      // Получить текущую локаль
      this.locale = document.documentElement.lang;
    },

    /**
     * Форсировать локаль
     */
    i18nForceLocale: function i18nForceLocale(locale) {
      this.locale = locale;
    },

    /**
     * Локализовать сообщение
     * @param {string} msgid
     * @param {array} placeholders
     * @return {string}
     */
    t: function t(msgid, placeholders) {
      // Если текущая локаль совпадает с дефолтной
      if (this.locale == this.defaultLocale) {
        return this.replacePlaceholders(msgid, placeholders);
      } // Если не совпадает, должны быть заданы переводы


      if (this.i18n) {
        // Для текущей локали
        if (this.i18n.hasOwnProperty(this.locale)) {
          // Для конкретной строки
          if (this.i18n[this.locale].hasOwnProperty(msgid)) {
            var message = this.i18n[this.locale][msgid]; // Вернуть локализованную строку с замененными placeholders

            return this.replacePlaceholders(message, placeholders);
          }
        }
      }

      return msgid;
    },

    /**
     * Локализовать сообщение для множественного чила
     * @param {string} msgid
     * @param {number} count
     * @param {array} placeholders
     * @return {string}
     */
    tn: function tn(msgid, count, placeholders) {
      // Должны быть заданы переводы
      if (this.i18n) {
        // Для текущей локали
        if (this.i18n.hasOwnProperty(this.locale)) {
          // Для конкретной строки
          if (this.i18n[this.locale].hasOwnProperty(msgid)) {
            // Должны быть заданы все формы, а не конкретный перевод
            var pluralForms = this.i18n[this.locale][msgid];

            if (Array.isArray(pluralForms)) {
              // Кол-во форм должно соответствовать локали
              // (для русской - 3, для английской - 2)
              if (pluralForms.length == this.getPluralFormsCount()) {
                // Определить форму по кол-ву
                var pluralFormIndex = this.getPluralForm(count); // Найти перевод для формы

                var message = this.i18n[this.locale][msgid][pluralFormIndex]; // Вернуть локализованную строку с замененными placeholders

                return this.replacePlaceholders(message, placeholders);
              }
            }
          }
        }
      }

      return msgid;
    },

    /**
     * Получить форму для множественного числа
     * (в русском - 3 формы, в английском - 2)
     * @param {number} count
     * @return {number}
     */
    getPluralForm: function getPluralForm(count) {
      if (this.locale == "ru") {
        if (count % 10 == 1 && count % 100 != 11) {
          return 0;
        } else {
          if (count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 10 || count % 100 >= 20)) {
            return 1;
          } else {
            return 2;
          }
        }
      } else {
        return count > 1 ? 1 : 0;
      }
    },

    /**
     * Кол-во форм множественного числа для текущей локали
     * @return {number}
     */
    getPluralFormsCount: function getPluralFormsCount() {
      return this.locale == "ru" ? 3 : 2;
    },

    /**
     * Заменить placeholders ({{0}}, {{1}} и т.д.)
     * @param {string} message
     * @param {array} placeholders
     * @return {string}
     */
    replacePlaceholders: function replacePlaceholders(message, placeholders) {
      if (!placeholders) {
        return message;
      }

      for (var i = 0; i < placeholders.length; i++) {
        message = message.replace('{{' + i + '}}', placeholders[i]);
      }

      return message;
    }
  }
});

/***/ }),

/***/ "./public_html/js/app/search.vue":
/*!***************************************!*\
  !*** ./public_html/js/app/search.vue ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./search.vue?vue&type=template&id=6d3d6370& */ "./public_html/js/app/search.vue?vue&type=template&id=6d3d6370&");
/* harmony import */ var _search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./search.vue?vue&type=script&lang=js& */ "./public_html/js/app/search.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__["render"],
  _search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "public_html/js/app/search.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./public_html/js/app/search.vue?vue&type=script&lang=js&":
/*!****************************************************************!*\
  !*** ./public_html/js/app/search.vue?vue&type=script&lang=js& ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./search.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/app/search.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_search_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./public_html/js/app/search.vue?vue&type=template&id=6d3d6370&":
/*!**********************************************************************!*\
  !*** ./public_html/js/app/search.vue?vue&type=template&id=6d3d6370& ***!
  \**********************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./search.vue?vue&type=template&id=6d3d6370& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/app/search.vue?vue&type=template&id=6d3d6370&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_search_vue_vue_type_template_id_6d3d6370___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./public_html/js/pages/index/bootstrap.js":
/*!*************************************************!*\
  !*** ./public_html/js/pages/index/bootstrap.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 * Уже используется в общем поиске в шапке
 * public_html/js/pages/general-search/bootstrap.js
 *
 * require('appJs/bootstrap.js');
 */
Vue.component("general-search-index", __webpack_require__(/*! ./general-search-index.vue */ "./public_html/js/pages/index/general-search-index.vue")["default"]);
var app = new Vue({
  el: '#app'
});

/***/ }),

/***/ "./public_html/js/pages/index/general-search-index.vue":
/*!*************************************************************!*\
  !*** ./public_html/js/pages/index/general-search-index.vue ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./general-search-index.vue?vue&type=template&id=16bbb214& */ "./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214&");
/* harmony import */ var _general_search_index_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./general-search-index.vue?vue&type=script&lang=js& */ "./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _general_search_index_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__["render"],
  _general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "public_html/js/pages/index/general-search-index.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js&":
/*!**************************************************************************************!*\
  !*** ./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_general_search_index_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./general-search-index.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/pages/index/general-search-index.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_general_search_index_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214&":
/*!********************************************************************************************!*\
  !*** ./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214& ***!
  \********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./general-search-index.vue?vue&type=template&id=16bbb214& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./public_html/js/pages/index/general-search-index.vue?vue&type=template&id=16bbb214&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_general_search_index_vue_vue_type_template_id_16bbb214___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ 4:
/*!*******************************************************!*\
  !*** multi ./public_html/js/pages/index/bootstrap.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\index\bootstrap.js */"./public_html/js/pages/index/bootstrap.js");


/***/ })

/******/ });