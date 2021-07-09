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
/******/ 	return __webpack_require__(__webpack_require__.s = 21);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/pages/bookmarks/bookmarks.js":
/*!*****************************************************!*\
  !*** ./public_html/js/pages/bookmarks/bookmarks.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var Bookmarks = function ($) {
  var _selectors = {
    filterSelect: '.js-bookmarks-category',
    linkLoadKworks: '.js-link-load-kworks',
    containerKworks: '.js-bookmark-kworks',
    filterCategoryLink: '.js-bookmarks-category-link'
  },
      _init = function _init() {
    _loadHistory();

    $(document).on('change', _selectors.filterSelect, function () {
      _loadKworks($(this).val());
    }).on('click', _selectors.linkLoadKworks, function () {
      loadKworks();
    }).on('click', _selectors.filterCategoryLink, function () {
      _loadKworks($(this).data('href'));
    });
    $(window).scroll(_scrollWindow);
  },
      _scrollWindow = function _scrollWindow() {
    if (!window.matchMedia("(max-width:767px)").matches && !isMobile() || $(_selectors.linkLoadKworks).hasClass('hidden') || $(_selectors.linkLoadKworks).hasClass('onload')) {
      return false;
    }

    if ($(window).scrollTop() + $(window).height() + 250 >= $(document).height()) {
      loadKworks();
    }
  },
      _setHistory = function _setHistory(url, response) {
    window.history.pushState({
      url: url,
      response: response
    }, "", $(_selectors.filterSelect).val());
  },
      _loadHistory = function _loadHistory() {
    window.onpopstate = function (event) {
      console.log(event);

      if (event.state) {
        _showKworks(event.state.response);

        _updateFilter(event.state.url);
      }
    };
  },
      _loadKworks = function _loadKworks(url) {
    $(_selectors.containerKworks).html('');
    $(_selectors.containerKworks).preloader("show");
    $(_selectors.linkLoadKworks).addClass('hidden');

    _updateFilter(url);

    $.ajax({
      url: url,
      type: 'post',
      data: {},
      dataType: 'json',
      success: function success(response) {
        _setHistory(url, response);

        _showKworks(response);
      }
    });
  },
      _showKworks = function _showKworks(response) {
    if (typeof response === "undefined") {
      location.reload();
      return;
    }

    if (response.paging.page * response.paging.items_per_page < response.paging.total) {
      $(_selectors.linkLoadKworks).removeClass('hidden');
    }

    $(_selectors.containerKworks).html(response.html);
  },
      _updateFilter = function _updateFilter(dataHref) {
    $(_selectors.filterCategoryLink).removeClass('link-color');
    $(_selectors.filterCategoryLink).filter("[data-href='" + dataHref + "']").addClass('link-color');
    $(_selectors.filterSelect).val(dataHref);
  };

  return {
    init: _init
  };
}(jQuery);

Bookmarks.init();

/***/ }),

/***/ "./public_html/js/pages/bookmarks/bootstrap.js":
/*!*****************************************************!*\
  !*** ./public_html/js/pages/bookmarks/bootstrap.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./bookmarks.js */ "./public_html/js/pages/bookmarks/bookmarks.js");

/***/ }),

/***/ 21:
/*!***********************************************************!*\
  !*** multi ./public_html/js/pages/bookmarks/bootstrap.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\bookmarks\bootstrap.js */"./public_html/js/pages/bookmarks/bootstrap.js");


/***/ })

/******/ });