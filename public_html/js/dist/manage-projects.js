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
/******/ 	return __webpack_require__(__webpack_require__.s = 17);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/pages/manage-projects/bootstrap.js":
/*!***********************************************************!*\
  !*** ./public_html/js/pages/manage-projects/bootstrap.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./manage-projects.js */ "./public_html/js/pages/manage-projects/manage-projects.js");

/***/ }),

/***/ "./public_html/js/pages/manage-projects/manage-projects.js":
/*!*****************************************************************!*\
  !*** ./public_html/js/pages/manage-projects/manage-projects.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(document).ready(function () {
  $('.rollable-name').each(function (k, v) {
    var el = $(v);
    var fullContentBlock = el.find('.wish_name');
    var innerContentBlock = fullContentBlock.find('div');
    var showMoreBlock = el.find('.wish-name-more');
    var files = el.siblings('.files-list');

    var updateRollableMode = function updateRollableMode() {
      var wasRolled = false;

      if (el.hasClass('rolled')) {
        wasRolled = true;
        el.removeClass('rolled');
        files.addClass('hidden');
      }

      if (innerContentBlock.height() > fullContentBlock.height()) {
        el.addClass('rollable');
        files.addClass('hidden');
      } else {
        el.removeClass('rollable');
        files.removeClass('hidden');
      }

      if (wasRolled) {
        el.addClass('rolled');
        files.removeClass('hidden');
      }
    };

    var toggleMore = function toggleMore() {
      if (!el.hasClass('rolled')) {
        el.addClass('rolled');
        files.removeClass('hidden');
      } else {
        el.removeClass('rolled');
        files.addClass('hidden');
        var headerSize = $('body > .header').outerHeight();
        var nameOffset = el.parent().find('.wants-card__header-title').offset().top;
        var windowScroll = $(window).scrollTop();

        if (nameOffset < windowScroll + headerSize) {
          $(window).scrollTop(nameOffset - headerSize - 15);
        }
      }
    };

    fullContentBlock.on('click', function () {
      if (el.hasClass('rolled')) {
        return;
      }

      toggleMore();
    });
    showMoreBlock.on('click', function () {
      toggleMore();
    });
    $(window).resize(function () {
      updateRollableMode();
    });
    updateRollableMode();
  });
  /**
   * Показать/скрыть архивные проекты
   */

  $(".js-archive-view").on("click", function () {
    var $this = $(this);
    $this.toggleClass("green-btn");
    $(".project_card.project-card--archive").toggleClass("hidden");
    $(".project_card_reason.project-card--archive").toggleClass("hidden");
  });
  $('.js-how-get-result-block-link').on('click', function () {
    $('.js-how-get-result-block-link').toggleClass('how-get-result-block__title--active');
    $('.js-js-how-get-result-block-content').toggleClass('how-get-result-block__content--active');
  });
});

/***/ }),

/***/ 17:
/*!*****************************************************************!*\
  !*** multi ./public_html/js/pages/manage-projects/bootstrap.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\manage-projects\bootstrap.js */"./public_html/js/pages/manage-projects/bootstrap.js");


/***/ })

/******/ });