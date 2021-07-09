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
/******/ 	return __webpack_require__(__webpack_require__.s = 24);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/pages/kwork-manage/bootstrap.js":
/*!********************************************************!*\
  !*** ./public_html/js/pages/kwork-manage/bootstrap.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! ./kwork-manage.js */ "./public_html/js/pages/kwork-manage/kwork-manage.js");

/***/ }),

/***/ "./public_html/js/pages/kwork-manage/kwork-manage.js":
/*!***********************************************************!*\
  !*** ./public_html/js/pages/kwork-manage/kwork-manage.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(document).ready(function () {
  $(".change-kwork-name-js").click(function (e) {
    e.preventDefault();
    var kworkId = $(this).attr("rel");
    var kworkName = he.decode($(this).parent().find("a").find("span").html());
    $(".modal-kwork-change-name .kwork-name").val(kworkName);
    $(".modal-kwork-change-name .kwork-id").val(kworkId);
    resetRenameKworkError();
    $(".modal-kwork-change-name").modal("show");
  });
  $(".modal-kwork-change-name .js-kwork-change-name-cancel").click(function (e) {
    e.preventDefault();
    closeRenameKworkModal();
  });
  $(".modal-kwork-change-name .save-new-name").click(function (e) {
    e.preventDefault();
    resetRenameKworkError();
    var name = $(".modal-kwork-change-name .kwork-name").val();
    var kworkId = $(".modal-kwork-change-name .kwork-id").val();
    var sourceEl = $("#kwork_name_" + kworkId).find("span");

    if (sourceEl.html() == name) {
      closeRenameKworkModal();
    }

    if (name != "") {
      $.ajax({
        url: "/set_kwork_name",
        type: "POST",
        data: {
          kwork_id: kworkId,
          name: name
        },
        success: function success(response) {
          if (response.success) {
            sourceEl.html(name);
            closeRenameKworkModal();
          } else {
            showRenameKworkError(response.data[0].text);
          }
        }
      });
    } else {
      showRenameKworkError("Название услуги не может быть пустым");
    }
  });

  function closeRenameKworkModal() {
    $(".modal-kwork-change-name .kwork-name").val("");
    $(".modal-kwork-change-name .kwork-id").val(0);
    $(".modal-kwork-change-name").modal("hide");
    resetRenameKworkError();
  }

  function resetRenameKworkError() {
    $(".modal-kwork-change-name .form-entry-error").html("").addClass("hidden");
  }

  function showRenameKworkError(error) {
    $(".modal-kwork-change-name .form-entry-error").html(error).removeClass("hidden");
  }
});

/***/ }),

/***/ 24:
/*!**************************************************************!*\
  !*** multi ./public_html/js/pages/kwork-manage/bootstrap.js ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\kwork-manage\bootstrap.js */"./public_html/js/pages/kwork-manage/bootstrap.js");


/***/ })

/******/ });