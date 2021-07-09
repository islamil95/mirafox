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
/******/ 	return __webpack_require__(__webpack_require__.s = 23);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/pages/education/bootstrap.js":
/*!*****************************************************!*\
  !*** ./public_html/js/pages/education/bootstrap.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./education.js */ "./public_html/js/pages/education/education.js");

/***/ }),

/***/ "./public_html/js/pages/education/education.js":
/*!*****************************************************!*\
  !*** ./public_html/js/pages/education/education.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var classes = {
  'button': '.js-education-download-button',
  'radio': '.js-education-download-radio',
  'counter': '.js-education-download-counter',
  'wrapper': '.js-education-download',
  'parent': '.js-education-lessons-item',
  'label': '.js-education-lessons-label',
  'recently': 'js-education-recently-downloaded'
};
var counters = {};
/**
 * Счетчики
 */

function getCounters() {
  jQuery(classes.wrapper).each(function () {
    var _this = jQuery(this);

    var fileName = _this.find(classes.button).data('file-name');

    var counterVal = _this.find(classes.counter).data('count');

    counters[fileName] = counterVal;
  });
}
/**
 * Событие скачивания урока
 */


function increaseDownloadCounter() {
  var _this = jQuery(this);

  var wrapper = _this.closest(classes.wrapper);

  var counter = wrapper.find(classes.counter);
  var fileName = wrapper.find(classes.button).data('file-name');

  var lessonLabel = _this.closest(classes.parent);

  if (wrapper.hasClass(classes.recently) === false) {
    counters[fileName]++;
    var counterVal = counters[fileName];
    counter.attr('data-count', counterVal).html(Utils.numberFormat(counterVal, 0, '.', ' ') + ' ' + declension(counterVal, t('раз'), t('раза'), t('раз')));
    lessonLabel.find(classes.label).fadeIn(150);
    wrapper.addClass(classes.recently);
  }
}
/**
 * Смена расшерения файла урока
 */


function changeDownloadType() {
  var _this = jQuery(this);

  var downloadType = _this.val();

  var downloadButton = _this.closest(classes.wrapper).find(classes.button);

  var fileName = downloadButton.data('file-name');
  downloadButton.attr('href', base_url + '/kwork_book/files/' + fileName + '/' + downloadType);
}

jQuery(function () {
  getCounters();
  jQuery(classes.radio).on('click', changeDownloadType);
  jQuery(document).on('click', classes.button, _.throttle(increaseDownloadCounter, 500));
});

/***/ }),

/***/ 23:
/*!***********************************************************!*\
  !*** multi ./public_html/js/pages/education/bootstrap.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\education\bootstrap.js */"./public_html/js/pages/education/bootstrap.js");


/***/ })

/******/ });