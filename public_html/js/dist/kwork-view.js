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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/app/file-uploader.js":
/*!*********************************************!*\
  !*** ./public_html/js/app/file-uploader.js ***!
  \*********************************************/
/*! exports provided: FileUploader */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "FileUploader", function() { return FileUploader; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var globalUploadBlock;
var FileUploader =
/*#__PURE__*/
function () {
  function FileUploader(block) {
    _classCallCheck(this, FileUploader);

    this.url = '/';
    this.fileName = 'file';
    this.postData = {};
    this.aborted = false;
    this.maxFileSize = 10485760;
    this.minImageWidth = 660;
    this.minImageHeight = 440;
    this.maxImageWidth = 6000;
    this.maxImageHeight = 6000;
    this.errors = {
      bigFilesize: 'Превышен максимально допустимый объём файла!',
      incorrectType: 'Некорректный тип файла!'
    };
    this.extensions = {
      'image/jpeg': ['jpg', 'jpeg'],
      'image/gif': ['gif'],
      'image/png': ['png']
    };
    this.mimeTypes = null;

    this.onError = function () {};

    if (globalUploadBlock) {
      this.input = globalUploadBlock;
    } else {
      globalUploadBlock = $('<input type="file" class="d-none" />');
      globalUploadBlock.prependTo('body');
      this.input = globalUploadBlock;
      this.input.on('change', function (e) {
        var el = $(e.target);
        el.data('loadHandler').fileUpload(e);
      });
    }
  }

  _createClass(FileUploader, [{
    key: "getExtensions",
    value: function getExtensions() {
      var _this = this;

      if (!this.mimeTypes) {
        return '';
      }

      var extensions = [];
      $.each(this.mimeTypes, function (k, v) {
        if (v in _this.extensions) {
          extensions = extensions.concat(_this.extensions[v]);
        }
      });
      $.each(extensions, function (k, v) {
        extensions[k] = '.' + v;
      });
      return extensions.join(',');
    }
  }, {
    key: "upload",
    value: function upload() {
      this.input.attr('accept', this.getExtensions());
      this.input.data('loadHandler', this);
      this.input.trigger('click');
    }
  }, {
    key: "fileUpload",
    value: function fileUpload() {
      var _this2 = this;

      this.abort();
      var file = this.input.get(0).files[0];
      this.input.val('');

      if (file.size > this.maxFileSize) {
        if (this.onError) {
          this.onError(t(this.errors.bigFilesize));
        }

        return;
      }

      if (this.mimeTypes) {
        if ($.inArray(file.type, this.mimeTypes) == -1) {
          if (this.onError) {
            this.onError(t(this.errors.incorrectType));
          }

          return;
        }
      }

      this.readPhotoForPreview(file, function () {
        _this2.fileUploadAjax(file);
      });
    }
  }, {
    key: "fileUploadAjax",
    value: function fileUploadAjax(file) {
      var _this3 = this;

      var data = new FormData();
      $.each(this.postData, function (k, v) {
        data.append(k, v);
      });
      data.append(this.fileName, file);
      this.xhr = $.ajax({
        xhr: function xhr() {
          var lXhr = new window.XMLHttpRequest();
          lXhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
              var percentComplete = parseInt(e.loaded / e.total * 100);

              if (_this3.onProgress) {
                _this3.onProgress(percentComplete);
              }
            }
          }, false);
          return lXhr;
        },
        url: this.url,
        data: data,
        async: true,
        contentType: false,
        processData: false,
        type: 'POST',
        complete: function complete(jXhr, status) {
          if (_this3.aborted) {
            return;
          }

          var rj = {};

          try {
            rj = JSON.parse(jXhr.responseText);
          } catch (e) {}

          if ('success' in rj && rj.success == true) {
            if (_this3.onSuccess) {
              _this3.onSuccess(rj.data);
            }
          } else {
            if (_this3.onFail) {
              _this3.onFail(rj.data);
            }
          }
        }
      });
    }
  }, {
    key: "readPhotoForPreview",
    value: function readPhotoForPreview(f, callback) {
      var _this4 = this;

      var reader = new FileReader();

      reader.onload = function () {
        var image = new Image();
        image.src = reader.result;

        image.onload = function () {
          if (!_this4.checkMinImageResolution(image)) {
            _this4.onError(t('Размер изображения должен быть не меньше ') + _this4.minImageWidth + 'x' + _this4.minImageHeight + t(' пикселей'));

            return;
          }

          if (!_this4.checkMaxImageResolution(image)) {
            _this4.onError(t('Размер изображения должен быть не больше ') + _this4.maxImageWidth + 'x' + _this4.maxImageHeight + t(' пикселей'));

            return;
          }

          if (_this4.onLoad) {
            _this4.onLoad(image.src);
          }

          callback();
        };
      };

      reader.readAsDataURL(f);
    }
  }, {
    key: "checkMinImageResolution",
    value: function checkMinImageResolution(img) {
      return !(img.width < this.minImageWidth || img.height < this.minImageHeight);
    }
  }, {
    key: "checkMaxImageResolution",
    value: function checkMaxImageResolution(img) {
      return !(img.width > this.maxImageWidth || img.height > this.maxImageHeight);
    }
  }, {
    key: "abort",
    value: function abort() {
      if (this.xhr) {
        this.aborted = true;
        this.xhr.abort();
        this.aborted = false;
      }
    }
  }]);

  return FileUploader;
}();

/***/ }),

/***/ "./public_html/js/app/portfolio-upload/ajax-portfolio-modal.js":
/*!*********************************************************************!*\
  !*** ./public_html/js/app/portfolio-upload/ajax-portfolio-modal.js ***!
  \*********************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var appJs_portfolio_upload_portfolio_modal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! appJs/portfolio-upload/portfolio-modal.js */ "./public_html/js/app/portfolio-upload/portfolio-modal.js");
/* harmony import */ var appJs_portfolio_upload_sortable_card_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! appJs/portfolio-upload/sortable-card.js */ "./public_html/js/app/portfolio-upload/sortable-card.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }




var AjaxPortfolioModal =
/*#__PURE__*/
function () {
  function AjaxPortfolioModal() {
    var _this = this;

    _classCallCheck(this, AjaxPortfolioModal);

    this.body = $('body');
    this.modal = new appJs_portfolio_upload_portfolio_modal_js__WEBPACK_IMPORTED_MODULE_0__["PortfolioModal"]();

    this.modal.onSave = function (item) {
      _this.savePortfolio(_this.modal.portfolioItem);
    };

    this.modal.getImagesHashes = function (hash) {
      return _this.getImagesHashes(hash);
    };

    this.modal.getCoverHashes = function (hash) {
      return _this.getCoverHashes(hash);
    };

    this.modal.checkCoverHash = function (hash) {
      return _this.checkCoverHash(hash);
    };

    this.modal.checkImageHash = function (hash) {
      return _this.checkImageHash(hash);
    };

    this.modal.checkVideoUrl = function (url, pos) {
      return _this.checkVideoUrl(url, pos);
    };

    this.events();
  }

  _createClass(AjaxPortfolioModal, [{
    key: "events",
    value: function events() {
      var _this2 = this;

      // Редактирование портфолио со страницы "Мои работы"
      this.body.on('click', '.portfolio-card .js-edit-portfolio, .portfolio-card-collage .js-edit-portfolio', function (e) {
        var portfolioId = $(e.target).closest('.portfolio-card, .portfolio-card-collage').data('id');

        _this2.openPortfolioModal(portfolioId);
      }); // Удаление портфолио со страницы "Мои работы"

      this.body.on('click', '.portfolio-card .js-delete-portfolio, .portfolio-card-collage .js-delete-portfolio', function (e) {
        var portfolioId = $(e.target).closest('.portfolio-card, .portfolio-card-collage').data('id');

        _this2.deletePortfolioCardModal(e.target, portfolioId);
      });
      this.body.on('click', '.portfolio-card-delete-modal .js-portfolio-card-delete-cancel', function (e) {
        _this2.deletePortfolioCardModalClose();
      });
      this.body.on('click', '.js-portfolio-card-delete-confirm', function (e) {
        _this2.deletePortfolioCardModalConfirm();
      }); // Редактирование из окна просмотра портфолио

      this.body.on('click', '.portfolio-large .js-edit-portfolio', function (e) {
        portfolioCard.close();
        setTimeout(function () {
          var portfolioId = $(e.target).closest('.portfolio-large').data('portfolioId');

          _this2.openPortfolioModal(portfolioId);
        }, 100);
      });
      this.body.on('click', '.js-new-portfolio', function (e) {
        _this2.openPortfolioModal(null);
      });
    }
  }, {
    key: "openPortfolioModal",
    value: function openPortfolioModal(portfolioId) {
      var _this3 = this;

      this.getPortfolioData(portfolioId, function (data) {
        var portfolio = new appJs_portfolio_upload_sortable_card_js__WEBPACK_IMPORTED_MODULE_1__["Portfolio"](data.portfolio);

        _this3.modal.edit(portfolio, data.additional);
      });
    }
  }, {
    key: "deletePortfolioCardModal",
    value: function deletePortfolioCardModal(el, portfolioId) {
      var _this4 = this;

      this.startLoader();
      $.ajax({
        type: 'GET',
        url: '/portfolio/can_delete/' + portfolioId,
        dataType: "json",
        success: function success(response) {
          _this4.stopLoader();

          if (response.success) {
            var deleteModal;

            if (response.data.canDeletePortfolio) {
              //работу удалить можно
              deleteModal = $('.js-portfolio-card-can-delete-modal');
              deleteModal.find('.js-portfolio-card-delete-confirm').attr('data-id', portfolioId);
            } else {
              //работу удалить нельзя, т.к. не будет нужного количества работ в портфолио
              deleteModal = $('.js-portfolio-card-cant-delete-modal');
              var deleteModalText = '';
              var kworkName = $(el).data('name');
              var kworkUrl = $(el).data('url');
              var neededPortfolioCount = response.data.neededPortfolioCount ? response.data.neededPortfolioCount : '';

              if (neededPortfolioCount) {
                neededPortfolioCount = ' (' + t('минимум') + ' ' + neededPortfolioCount + ')';
              }

              if (kworkName && kworkName != '') {
                deleteModalText += '<p>' + t('Удалить работу нельзя, поскольку в кворке «{{0}}» не будет нужного количества работ в портфолио', [kworkName]) + neededPortfolioCount + '.</p>';
              } else {
                deleteModalText += '<p>' + t('Удалить работу нельзя, поскольку в кворке не будет нужного количества работ в портфолио') + neededPortfolioCount + '.</p>';
              }

              deleteModalText += '<p class="mt10">' + t('<a href="{{0}}">Добавьте в кворк</a> новую работу, чтобы удалить данную.', [kworkUrl]) + '</p>';
              deleteModal.find('.modal-body').html(deleteModalText);
            }

            deleteModal.modal('show');
          } else {// TODO view error
          }
        }
      });
    }
  }, {
    key: "deletePortfolioCardModalClose",
    value: function deletePortfolioCardModalClose() {
      $('.portfolio-card-delete-modal').modal('hide');
    }
  }, {
    key: "deletePortfolioCardModalConfirm",
    value: function deletePortfolioCardModalConfirm() {
      if (window.portfolioList.modal.page === 'my-portfolios') {
        var formData = new FormData(),
            portfolioId = $('.js-portfolio-card-delete-confirm').attr('data-id');
        formData.append('portfolio_id', portfolioId); //formData.append('unlink', 'true');

        $.ajax({
          url: '/portfolio/delete',
          data: formData,
          async: true,
          contentType: false,
          processData: false,
          type: 'POST',
          complete: function complete(jXhr, status) {
            var rj = {};

            try {
              rj = JSON.parse(jXhr.responseText);
            } catch (e) {}

            if ('success' in rj && rj.success === true) {
              $('.js-portfolio-card[data-id="' + portfolioId + '"]').remove();
              $('.header_top').append('<div class="fox_success" style="display:none"><div class="text-center"><p>Работа успешно удалена</p></div></div>');
              $('.fox_success').slideDown(300);
              setTimeout(function () {
                // показываем блок успешного удаления на 3 секунды
                $('.fox_success').slideUp(300);
                setTimeout(function () {
                  // удаляем его сразу по автоматическому закрытию
                  $('.fox_success').remove();
                }, 400);
              }, 2000);
            }
          }
        });
      }

      this.deletePortfolioCardModalClose();
    }
  }, {
    key: "checkCoverHash",
    value: function checkCoverHash(hash) {
      var hashes = this.getCoverHashes();
      return $.inArray(hash, hashes) == -1;
    }
  }, {
    key: "checkImageHash",
    value: function checkImageHash(hash) {
      var hashes = this.getImagesHashes();
      return $.inArray(hash, hashes) == -1;
    }
  }, {
    key: "checkVideoUrl",
    value: function checkVideoUrl(url, pos) {
      var urls = this.getVideoUrls(pos);
      return $.inArray(url, urls) == -1;
    }
  }, {
    key: "getCoverHashes",
    value: function getCoverHashes() {
      return this.modal.additionalData.coverHashes || [];
    }
  }, {
    key: "getImagesHashes",
    value: function getImagesHashes() {
      var hashes = [];
      $.each(this.modal.portfolio.images, function (k, v) {
        hashes.push(v.hash);
      });
      hashes = hashes.concat(this.modal.additionalData.imagesHashes || []);
      return hashes;
    }
  }, {
    key: "getVideoUrls",
    value: function getVideoUrls(pos) {
      var urls = [];
      $.each(this.modal.portfolio.videos, function (k, v) {
        if (k == pos) {
          return true;
        }

        urls.push(v);
      });
      urls = urls.concat(this.modal.additionalData.anotherVideos || []);
      return urls;
    }
  }, {
    key: "savePortfolio",
    value: function savePortfolio(portfolioItem) {
      return this.modal.coverHashes;
    }
    /**
     * Подтянуть данные по портфолио
     * 
     * @param {*} portfolioId Id портфолио (null - будет как новый)
     * @param {*} successCallback Коллбеэк при успехе, возвращает данные
     */

  }, {
    key: "getPortfolioData",
    value: function getPortfolioData() {
      var _this5 = this;

      var portfolioId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var successCallback = arguments.length > 1 ? arguments[1] : undefined;
      this.startLoader();
      $.ajax({
        type: "POST",
        url: '/portfolio/get_popup',
        data: {
          portfolioId: portfolioId
        },
        dataType: "json",
        success: function success(response) {
          _this5.stopLoader();

          if (response.success) {
            if (successCallback) {
              successCallback(response.data);
            }
          } else {// TODO view error
          }
        }
      });
    }
  }, {
    key: "startLoader",
    value: function startLoader() {
      lockBodyForPopup();
      this.body.append('<div class="portfolio-loader-wrapper">' + ' <div class="portfolio-loader portfolio-loader-white">' + '<div class="ispinner ispinner--gray ispinner--animating ispinner--large">' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '<div class="ispinner__blade"></div>' + '</div>' + '</div>' + '</div>');
    }
  }, {
    key: "stopLoader",
    value: function stopLoader() {
      unlockBodyForPopup();
      this.body.find('.portfolio-loader-wrapper').remove();
    }
  }]);

  return AjaxPortfolioModal;
}();

$(document).ready(function () {
  window.portfolioList = new AjaxPortfolioModal();
});

/***/ }),

/***/ "./public_html/js/app/portfolio-upload/portfolio-modal.js":
/*!****************************************************************!*\
  !*** ./public_html/js/app/portfolio-upload/portfolio-modal.js ***!
  \****************************************************************/
/*! exports provided: PortfolioModal */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "PortfolioModal", function() { return PortfolioModal; });
/* harmony import */ var _sortable_card_list_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./sortable-card-list.js */ "./public_html/js/app/portfolio-upload/sortable-card-list.js");
/* harmony import */ var appJs_file_uploader_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! appJs/file-uploader.js */ "./public_html/js/app/file-uploader.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var PortfolioModal =
/*#__PURE__*/
function () {
  function PortfolioModal() {
    var _this2 = this;

    _classCallCheck(this, PortfolioModal);

    this.portfolioType = window.portfolioType || 'photo';
    this.PORTFOLIO_TYPE_PHOTO = 'photo';
    this.PORTFOLIO_TYPE_VIDEO = 'video';
    this.COVER_IMAGE_TYPE_VIDEO = 'video';
    this.COVER_IMAGE_TYPE_IMAGE = 'image';
    this.COVER_IMAGE_TYPE_UPLOAD = 'upload';
    this.MAX_YOUTUBE = 5;
    this.MIN_WIDTH_CROP_BOX = 660;
    this.MIN_HEIGHT_CROP_BOX = 440;
    this.PAGE_ORDER = 'track';
    this.PAGE_KWORK = 'edit-kwork';
    this.PAGE_MY_PORTFOLIO = 'my-portfolios';
    this.block = $('.portfolio-upload-modal');
    this.titleInput = this.block.find('input[name="title"]');
    this.fieldKworks = this.block.find('.js-field-kworks');
    this.fieldOrders = this.block.find('.js-field-orders');
    this.coverUploadGrid = this.block.find('.js-cover-upload-grid');
    this.coverTextNotLoad = this.block.find('.js-portfolio-cover-text-not-load');
    this.coverTextLoad = this.block.find('.js-portfolio-cover-text-load');
    this.exampleCard = this.block.find('.portfolio-example-card');
    this.exampleCardCntLikes = this.exampleCard.find('.cnt-likes');
    this.exampleCardCntViews = this.exampleCard.find('.cnt-views');
    this.exampleCardCntComments = this.exampleCard.find('.cnt-comments');
    this.exampleCardName = this.exampleCard.find('.portfolio-card-name');
    this.exampleCardCategory = this.exampleCard.find('.portfolio-card-content--category');
    this.exampleCardThumbnail = this.exampleCard.find('.portfolio-cropper-preview');
    this.confirmBlock = $('.portfolio-upload-modal-confirm');
    this.confirmAtClose = true; // Запрашивать ли при закрытии подтверждение

    this.reloadAfterSave = this.block.data('portfolioReloadAfterSave') || false;
    this.page = this.block.data('portfolioPage');
    this.orderId = this.block.data('orderId');
    this.requiredPortfolioType = this.block.find('.js-required-portfolio-type');
    this.secondaryPortfolioType = this.block.find('.js-secondary-portfolio-type');
    this.fieldImages = this.block.find('.js-field-images');
    this.fieldVideos = this.block.find('.js-field-videos');
    this.attributesLoaded = true;
    this.kworksList = {};
    this.ordersList = {};
    this.errorFields = [];
    this.classes = {
      field: 'js-field',
      fieldCounter: 'js-field-counter',
      fieldCounterHint: 'js-field-counter-hint',
      btnSave: 'js-save-portfolio',
      btnDisabled: 'btn_disabled',
      inputClassError: 'input-error-portfolio',
      videos: 'videos',
      fieldYoutube: 'youtube-field',
      youtubeRemove: 'js-youtube-remove',
      btnCloseConfirm: 'js-confirm-portfolio',
      btnCloseContinue: 'js-continue-portfolio',
      cover: 'js-portfolio-cover',
      coverUploadField: 'js-cover-upload-field',
      coverImage: 'js-cover-image',
      coverBtnUpload: 'js-portfolio-cover-upload',
      coverMini: 'js-cover-mini',
      categoryError: 'js-category-error',
      attributesError: 'js-attributes-error',
      kworkError: 'js-portfolio-kwork-error',
      coverError: 'js-portfolio-cover-error',
      titleError: 'js-portfolio-title-error',
      imagesError: 'sortable-card-list .portfolio-error',
      videosError: 'js-portfolio-videos-error',
      selectKwork: 'js-portfolio-kwork',
      selectOrder: 'js-portfolio-order',
      selectParentCategories: 'js-portfolio-parent-categories',
      selectCategories: 'js-portfolio-categories',
      attributes: 'js-attributes',
      linkShowImages: 'js-portfolio-images-link',
      linkShowVideos: 'js-portfolio-videos-link'
    };
    this.frontendErrors = {
      'title': [],
      'cover': [],
      'images': [],
      'videos': []
    };
    this.coverCropper = '';
    this.cropperConfig = {
      checkCrossOrigin: false,
      checkOrientation: false,
      background: true,
      rotatable: false,
      scalable: false,
      zoomable: true,
      zoomOnTouch: true,
      zoomOnWheel: true,
      toggleDragModeOnDblclick: false,
      aspectRatio: 3 / 2,
      autoCropArea: 0.8,
      preview: this.exampleCardThumbnail,
      viewMode: 1,
      dragMode: 'move'
    };
    this.initSortableContainer();
    this.initCover();
    this.events();
    this.block.find('.' + this.classes.selectParentCategories + ', .' + this.classes.selectCategories).chosen({
      width: '100%',
      disable_search: true,
      display_disabled_options: false
    });
    this.block.find('.' + this.classes.selectKwork + ', .' + this.classes.selectOrder).chosen({
      width: '100%',
      disable_search: false,
      disable_search_threshold: 10
    });
    this.block.find('.' + this.classes.selectOrder).on('keyup change chosen:showing_dropdown', function (e) {
      _this2.formatChosenList();
    });
    var orderParent = this.block.find('.' + this.classes.selectOrder).parent();
    orderParent.on('click', function (e) {
      if (!orderParent.hasClass('loaded')) {
        _this2.getFullOrders(_this2.portfolio.kwork_id, function (data) {
          _this2.renderOrders(data.orders, null, false, true);

          $(e.target).find('select').trigger('mousedown').trigger('chosen:open');

          _this2.formatChosenList();
        });
      }
    });
  }
  /**
   * Форматирование выпадающего списка, добавление переносов строк
   */


  _createClass(PortfolioModal, [{
    key: "formatChosenList",
    value: function formatChosenList() {
      $('.chosen-results .active-result').html(function () {
        var html = $(this).html();

        if (/ \(/.test(html)) {
          html = html.replace(' (', '<div class="chosen-multiline">') + '</div>';
          html = html.replace(')', '');
          return html;
        } else {
          return html;
        }
      });
    }
  }, {
    key: "initSortableContainer",
    value: function initSortableContainer() {
      var _this3 = this;

      this.sortableContainer = new _sortable_card_list_js__WEBPACK_IMPORTED_MODULE_0__["SortableCardList"](this.block.find('.sortable-card-list')[0]);

      this.sortableContainer.onChangeState = function () {
        _this3.updateSaveButton();

        _this3.updateImages();

        _this3.validateImages();
      };

      this.sortableContainer.onSuccess = function (item) {
        _this3.updateCoverMini(); // если обложек ранее не было
        // если выбрана загруженная обложка, то картинку в нарезке не меняем
        // при загрузке каждого нового изображения картинку в нарезке не меняем. Меняем только при загрузке первого изображения портфолио


        _this3.portfolioItem.backendErrors.cover = [];

        if (!_this3.portfolio.cover.urlBig || _this3.portfolio.cover.urlDefault !== _this3.portfolio.cover.urlBig && _this3.portfolio.images.length === 1) {
          _this3.updateCoverUrl(item.data.urlBig);

          _this3.updateCoverIdPortfolioImage(item.data.id);

          _this3.updateCoverBlock();

          _this3.portfolio.cover.type = _this3.COVER_IMAGE_TYPE_IMAGE;
        }

        _this3.setCoverMiniSelected(_this3.portfolio.cover.urlBig);
      };

      this.sortableContainer.onDelete = function (item) {
        _this3.updateImages();

        _this3.selectRandomCover(item.data.urlBig, true);
      };
    }
    /**
     * Инициализация для обложки
     */

  }, {
    key: "initCover",
    value: function initCover() {
      var _this4 = this;

      this.coverUploader = new appJs_file_uploader_js__WEBPACK_IMPORTED_MODULE_1__["FileUploader"]();
      this.coverUploader.postData = {
        validator: 'KworkCover'
      };
      this.coverUploader.url = '/temp-image-upload';
      this.coverUploader.fileName = 'file';
      this.coverUploader.maxSize = 10485760;
      this.coverUploader.minImageWidth = 660;
      this.coverUploader.minImageHeight = 440;
      this.coverUploader.maxImageWidth = 6000;
      this.coverUploader.maxImageHeight = 6000;
      this.coverUploader.mimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
      this.coverUploader.errors.unknown = t('Произошла ошибка. Пожалуйста, попробуйте еще раз.'); // callbacks

      this.coverUploader.onError = function (text) {
        _this4.hideCoverProgress();

        _this4._unlockSaveBtn();

        _this4.rollbackCover();

        _this4.errorCover(text || _this4.coverUploader.errors.unknown);
      };

      this.coverUploader.onLoad = function (base64) {
        _this4.base64 = base64;

        _this4.updateCoverBlock(_this4.base64);

        _this4.showCoverProgress();

        _this4._lockSaveBtn();
      };

      this.coverUploader.onProgress = function (percent) {
        _this4.updateCoverProgress(percent);
      };

      this.coverUploader.onSuccess = function (data) {
        _this4.portfolioItem.backendErrors.cover = [];
        _this4.portfolio.cover.id = data.id;
        _this4.portfolio.cover.hash = data.hash;
        _this4.portfolio.cover.urlDefault = _this4.base64;
        _this4.portfolio.cover.crop = null;
        _this4.portfolio.cover.type = _this4.COVER_IMAGE_TYPE_UPLOAD;

        _this4.updateCoverMini();

        _this4.updateCoverUrl(_this4.base64);

        _this4.updateCoverIdPortfolioImage();

        _this4.setCoverMiniSelected(_this4.base64);

        _this4.errorCover();

        _this4.hideCoverProgress();

        _this4._unlockSaveBtn();
      };

      this.coverUploader.onFail = function (errors) {
        _this4.hideCoverProgress();

        _this4._unlockSaveBtn();

        var errorText = '';
        $.each(errors, function (k, v) {
          errorText += v + '<br>';
        });

        _this4.rollbackCover();

        _this4.errorCover(errorText);
      }; // event
      // минимальные ограничения на кроп область


      this.block.find('.' + this.classes.coverImage).on('zoom', 'img', function (e) {
        var data = _this4.coverCropper.cropper('getData');

        if (e.originalEvent.detail.ratio > e.originalEvent.detail.oldRatio && (data.width < _this4.MIN_WIDTH_CROP_BOX || data.height < _this4.MIN_HEIGHT_CROP_BOX)) {
          e.preventDefault();
        }
      }); // загрузка обложки

      this.block.find('.' + this.classes.coverUploadField).on('click', function () {
        _this4.updateCoverCrop();

        _this4.coverUploader.upload();
      }); // загрузка обложки

      this.block.find('.' + this.classes.coverBtnUpload).on('click', function () {
        _this4.updateCoverCrop();

        var portfolioId = window.portfolioList.modal.portfolio.id;

        if (portfolioId) {
          _this4.coverUploader.postData.portfolio_id = portfolioId;
        }

        var hashes = _this4.getImagesHashes().concat(_this4.getCoverHashes());

        if (_this4.portfolio.cover.hash) {
          hashes.push(_this4.portfolio.cover.hash);
        }

        if (window.firstPhotoHash) {
          hashes.push(window.firstPhotoHash);
        }

        if (hashes.length) {
          $.each(hashes, function (k, v) {
            _this4.coverUploader.postData['hashes[' + k + ']'] = v;
          });
        }

        _this4.coverUploader.upload();
      });
    }
    /**
     * Откатить изменения по обложке
     */

  }, {
    key: "rollbackCover",
    value: function rollbackCover() {
      this.defaultCoverField(); // Очистка примера превью

      this.clearExampleCardPreview();

      if (this.portfolio.cover && this.portfolio.cover.urlBig) {
        this.updateCoverUrl(this.portfolio.cover.urlBig);
        this.updateCoverIdPortfolioImage(this.portfolio.cover.idPortfolioImage);
        this.updateCoverBlock();
      } else {
        this.hideCoverUpload();
      }
    }
    /**
     * Очистить блок обложки в исходное положение (без обложки)
     */

  }, {
    key: "clearCover",
    value: function clearCover() {
      this.defaultCoverField();
      this.clearExampleCardPreview();
      this.hideCoverUpload();
    }
    /**
     * Если выбрано переданое изображение, то выбрать другое. Если нет вариантов обложки, то очищаем нарезку 
     * @param url
     * @param afterDelete
     */

  }, {
    key: "selectRandomCover",
    value: function selectRandomCover(url, afterDelete) {
      var _this5 = this;

      this.updateCoverMini();

      if (this.block.find('.' + this.classes.coverImage).find('img[src="' + url + '"]').length && this.coverMini.length || this.coverMini[0] && this.coverMini[0].url) {
        this.updateCoverUrl(this.coverMini[0].url);
        this.updateCoverIdPortfolioImage(this.coverMini[0].id);
        this.updateCoverBlock();
        this.portfolio.cover.type = this.coverMini[0].type;
      } else if (this.coverMini.length === 0) {
        this.clearCover();
        this.updateCoverUrl('');
        this.updateCoverIdPortfolioImage(0);
        this.updateCoverHash();
        delete this.portfolio.cover.type;

        if (afterDelete) {
          setTimeout(function () {
            _this5.updateYoutubeCover();
          }, 0);
        }
      }
    }
    /**
     * Привести в изначальное состояние область для загрузки обложки
     */

  }, {
    key: "defaultCoverField",
    value: function defaultCoverField() {
      this.block.find('.' + this.classes.coverImage).find('> *:not(.progress)').remove();
      this.showCoverField();
      this.hideCoverImage();
      this.destroyCropper();
    }
    /**
     * Обновить обложку
     */

  }, {
    key: "updateCoverBlock",
    value: function updateCoverBlock() {
      var _this6 = this;

      var coverUrl = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      this.destroyCropper();
      this.showCoverUpload();
      this.errorCover();
      var coverImage = this.block.find('.' + this.classes.coverImage);
      var img = coverImage.find('img');
      var url = coverUrl || this.portfolio.cover.urlBig;
      var cropData = this.portfolio.cover.crop ? this.portfolio.cover.crop : '';
      this.showCoverImage();
      this.hideCoverField();

      if (img.length) {
        img.attr('src', url);
      } else {
        coverImage.append(this._cropImgTpl(url));
      }

      coverImage.find('img').ready(function () {
        _this6.initCropper(cropData);
      });
      this.setCoverMiniSelected(url);
    }
    /**
     * Инициализация кроппера
     * 
     * @param {object} cropData Позиционировани кроппа
     */

  }, {
    key: "initCropper",
    value: function initCropper() {
      var _this7 = this;

      var cropData = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var coverImage = this.block.find('.' + this.classes.coverImage);
      var img = coverImage.find('img');

      if (cropData) {
        // get natural img size :)
        $('<img>').attr('src', img.attr('src')).load(function (e) {
          var naturalWidth = e.target.width;
          var naturalHeight = e.target.height;
          _this7.cropperConfig.data = {
            x: cropData.x * naturalWidth,
            y: cropData.y * naturalHeight,
            width: Math.round(cropData.w * naturalWidth),
            height: Math.round(cropData.h * naturalHeight)
          };
          _this7.coverCropper = img.cropper(_this7.cropperConfig);
        });
      } else {
        this.cropperConfig.data = {};
        this.coverCropper = img.cropper(this.cropperConfig);
      }
    }
    /**
     * Получить кропнутое изображение (base64)
     */

  }, {
    key: "getImgCropper",
    value: function getImgCropper() {
      if (this.coverCropper) {
        try {
          return this.coverCropper.cropper('getCroppedCanvas').toDataURL('image/jpeg');
        } catch (e) {
          return false;
        }
      } else {
        return false;
      }
    }
    /**
     * Удалить кроп-обложку
     */

  }, {
    key: "destroyCropper",
    value: function destroyCropper() {
      if (this.coverCropper) {
        this.coverCropper.cropper('destroy');
        this.coverCropper = '';
      }
    }
    /**
     * Получить данные для кроп-обложки
     */

  }, {
    key: "getCropperData",
    value: function getCropperData() {
      // w - размер ширины кропа делится на ширину изображения
      // h - размер высоты кропа делится на высоту изображения
      // x - сдвиг по ширине кроп области от левого края изображения делится на ширину изображения
      // y - сдвиг по высоте кроп области от верхнего края изображения делится на высоту изображения
      if (this.coverCropper) {
        try {
          var data = this.coverCropper.cropper('getData');
          var imgData = this.coverCropper.cropper('getImageData');
          return {
            x: data.x / imgData.naturalWidth,
            y: data.y / imgData.naturalHeight,
            w: data.width / imgData.naturalWidth,
            h: data.height / imgData.naturalHeight
          };
        } catch (e) {
          return {
            x: null,
            y: null,
            w: null,
            h: null
          };
        }
      } else {
        return false;
      }
    }
    /**
     * Установить ошибку для обложки
     * @param {*} text Текст ошибки (пустое значение очистить ошибку)
     */

  }, {
    key: "errorCover",
    value: function errorCover() {
      var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      var coverError = this.block.find('.' + this.classes.coverError);
      coverError.html(text);
    }
    /**
     * Показать текст если обложка загружена
     */

  }, {
    key: "showCoverTextLoad",
    value: function showCoverTextLoad() {
      this.coverTextNotLoad.hide();
      this.coverTextLoad.show();
    }
    /**
     * Показать текст если обложка не загружена
     */

  }, {
    key: "showCoverTextNotLoad",
    value: function showCoverTextNotLoad() {
      this.coverTextNotLoad.show();
      this.coverTextLoad.hide();
    }
    /**
     * Показать нарезку обложки
     */

  }, {
    key: "showCoverUpload",
    value: function showCoverUpload() {
      this.coverUploadGrid.addClass('show-cover-upload');
    }
    /**
     * Скрыть нарезку обложки
     */

  }, {
    key: "hideCoverUpload",
    value: function hideCoverUpload() {
      this.coverUploadGrid.removeClass('show-cover-upload');
      this.hideCoverMini();
    }
    /**
     * Показать картинку обложки
     */

  }, {
    key: "showCoverImage",
    value: function showCoverImage() {
      var coverImage = this.block.find('.' + this.classes.coverImage);
      coverImage.show();
    }
    /**
     * Скрыть картинку обложки
     */

  }, {
    key: "hideCoverImage",
    value: function hideCoverImage() {
      var coverImage = this.block.find('.' + this.classes.coverImage);
      coverImage.hide();
    }
    /**
     * Показать миниатюрки обложки
     */

  }, {
    key: "showCoverMini",
    value: function showCoverMini() {
      this.block.find('.' + this.classes.coverMini).show();
      this.coverUploadGrid.addClass('show-cover-mini');
    }
    /**
     * Скрыть миниатюрки обложки
     */

  }, {
    key: "hideCoverMini",
    value: function hideCoverMini() {
      this.block.find('.' + this.classes.coverMini).hide();
      this.coverUploadGrid.removeClass('show-cover-mini');
    }
    /**
     * Показать области загрузки обложки
     */

  }, {
    key: "showCoverField",
    value: function showCoverField() {
      var coverUploadField = this.block.find('.' + this.classes.coverUploadField);
      coverUploadField.show();
    }
    /**
     * Скрыть области загрузки обложки
     */

  }, {
    key: "hideCoverField",
    value: function hideCoverField() {
      var coverUploadField = this.block.find('.' + this.classes.coverUploadField);
      coverUploadField.hide();
    }
  }, {
    key: "showCoverProgress",
    value: function showCoverProgress() {
      var coverImage = this.block.find('.' + this.classes.coverImage);
      this.updateCoverProgress(0);
      coverImage.addClass('loading');
    }
  }, {
    key: "hideCoverProgress",
    value: function hideCoverProgress() {
      var coverImage = this.block.find('.' + this.classes.coverImage);
      coverImage.removeClass('loading');
    }
  }, {
    key: "updateCoverProgress",
    value: function updateCoverProgress(percent) {
      var coverImage = this.block.find('.' + this.classes.coverImage);
      coverImage.find('.progress div').css({
        'width': percent + '%'
      });
    }
    /**
     * очистить миниатюрные обложки
     */

  }, {
    key: "clearCoverMini",
    value: function clearCoverMini() {
      this.coverMini = [];
      var coverMini = this.block.find('.' + this.classes.coverMini);

      if (coverMini.hasClass('slick-initialized')) {
        coverMini.slick('unslick');
      }

      coverMini.html('');
    }
    /**
     * обновить миниатюрные обложки
     */

  }, {
    key: "updateCoverMini",
    value: function updateCoverMini() {
      var _this8 = this;

      this.clearCoverMini();

      if (this.portfolio.cover && this.portfolio.cover.urlDefault) {
        this.coverMini.push({
          id: 0,
          url: this.portfolio.cover.urlDefault,
          type: this.COVER_IMAGE_TYPE_UPLOAD
        });
      } // обновление миниатюрных облоек из картинок портфолио


      $.each(this.portfolio.images, function (k, v) {
        if (v.urlBig) {
          _this8.coverMini.push({
            id: v.id,
            url: v.urlBig,
            type: _this8.COVER_IMAGE_TYPE_IMAGE
          });
        }
      });

      if (this.portfolio.cover.urlFromVideo) {
        this.coverMini.push({
          id: 0,
          url: this.portfolio.cover.urlFromVideo,
          type: this.COVER_IMAGE_TYPE_VIDEO
        });
      }

      this.addCoverMini();
      this.initCoverSlick();

      if (this.block.find('.' + this.classes.coverMini).find('.preview').length > 1) {
        this.showCoverMini();
      } else {
        this.hideCoverMini();
      }
    }
    /**
     * добавление миниатюрной обложки
     */

  }, {
    key: "addCoverMini",
    value: function addCoverMini() {
      var _this9 = this;

      $.each(this.coverMini, function (k, v) {
        _this9.block.find('.' + _this9.classes.coverMini).append('<div class="preview">' + '<img src="' + v.url + '" alt="" data-id-image-portfolio="' + v.id + '"' + (v.type ? ' data-type="' + v.type + '"' : '') + '>' + '</div>'); // выбор обложки из мини вариантов


        _this9.block.find('.' + _this9.classes.coverMini).find('.preview').on('click', function (e) {
          _this9.updateCoverUrl($(e.target).attr('src'));

          _this9.updateCoverIdPortfolioImage($(e.target).data('idImagePortfolio'));

          _this9.updateCoverBlock();

          _this9.portfolio.cover.type = $(e.target).data('type');
        });
      });
    }
    /**
     * выделить выбранное изображение 
     * @param url
     */

  }, {
    key: "setCoverMiniSelected",
    value: function setCoverMiniSelected(url) {
      var preview = this.block.find('.' + this.classes.coverMini).find('.preview');
      preview.removeClass('selected');
      preview.find('img').filter('[src="' + url + '"]').parents('.preview').addClass('selected');
    }
  }, {
    key: "initCoverSlick",
    value: function initCoverSlick() {
      if (this.coverMini.length > 4) {
        this.block.find('.' + this.classes.coverMini).slick({
          dots: false,
          infinite: true,
          slidesToShow: 3,
          slidesToScroll: 3,
          variableWidth: true,
          centerMode: true,
          focusOnSelect: true,
          prevArrow: '<span class="cover-upload-mini__slick cover-upload-mini__slick-prev"><i class="fa fa-angle-left"></i></span>',
          nextArrow: '<span class="cover-upload-mini__slick cover-upload-mini__slick-next"><i class="fa fa-angle-right"></i></span>'
        });
      }
    }
    /**
     * Список событий
     */

  }, {
    key: "events",
    value: function events() {
      var _this10 = this;

      // Закрытие модального окна "Да, закрыть"
      this.confirmBlock.find('.' + this.classes.btnCloseConfirm).on('click', function (e) {
        _this10.confirmBlock.modal('hide');

        _this10._modalHide();
      }); // Закрытие модального окна "Продолжить"

      this.confirmBlock.find('.' + this.classes.btnCloseContinue).on('click', function (e) {
        _this10.confirmBlock.modal('hide');

        $('body').addClass('modal-open');

        _this10.block.fadeIn(300);
      }); // Событие на закрытие модального окна

      this.block.on('hide.bs.modal', function (e) {
        // окно подтверждения
        if (_this10.confirmAtClose) {
          _this10.updateAll();

          if (_this10.isChangedPortfolio() === false) {
            return;
          }

          _this10.setDefaultTitle();

          e.preventDefault();

          _this10.block.fadeOut(300);

          _this10.confirmBlock.modal('show');
        }
      }); // Подсчет количества символов

      this.block.find('.' + this.classes.fieldCounter).on('input', function (e) {
        _this10.countCharacters($(e.target));
      }); // Обработка ввода заголовка

      this.titleInput.on('input', function () {
        _this10.updateTitle();

        _this10.validateTitle();

        _this10.setExampleCardName();
      }); // Сохранение портфолио

      this.block.find('.' + this.classes.btnSave).on('click', function (e) {
        if ($(e.target).hasClass('js-save-portfolio-for-draft')) {
          // Сохраняем черновик
          _this10.save(true);
        } else {
          _this10.save();
        }
      }); // Перерасчёт координат при полном показе модального окна

      this.block.on('shown.bs.modal', function () {
        _this10.sortableContainer.updateOffsets();

        _this10.sortableContainer.updateAllBlockCoordinates();
      }); // Удаление поля youtube

      this.block.on('click', '.' + this.classes.youtubeRemove, function (e) {
        var field = $(e.target).closest('.' + _this10.classes.fieldYoutube);

        _this10.youtubeRemove(field.find('input'));
      }); // Слушаем поля youtube, для добавления новых полей

      this.block.on('input', '.' + this.classes.fieldYoutube + ' input', function (e) {
        _this10.youtubeEvents($(e.target));
      }); // Событие при смене кворка

      this.block.on('change', '.' + this.classes.selectKwork, function (e) {
        _this10.kworkChange();
      }); // Событие при смене заказа

      this.block.on('change', '.' + this.classes.selectOrder, function (e) {
        _this10.orderChange();
      }); // Событие при смене родительской рубрики

      this.block.on('change', '.' + this.classes.selectParentCategories, function (e) {
        _this10.portfolio.attributes_ids = [];

        _this10.renderCategories();

        _this10.updateCategory();

        _this10.validateCategory();

        _this10.portfolio.attributes_ids = [];

        _this10.block.find('.' + _this10.classes.attributes).html('');

        _this10.renderAttributes();

        _this10.renderKworksAndOrders();

        _this10.setExampleCardCategory();

        _this10.selectBlockVideosOrImages();
      }); // Событие при смене подрубрик

      this.block.on('change', '.' + this.classes.selectCategories, function (e) {
        _this10.updateCategory();

        _this10.validateCategory();

        _this10.portfolio.attributes_ids = [];

        _this10.block.find('.' + _this10.classes.attributes).html('');

        _this10.renderAttributes();

        _this10.renderKworksAndOrders();

        _this10.setExampleCardCategory();

        _this10.selectBlockVideosOrImages();
      });
      this.block.find('.' + this.classes.attributes).on('change', 'input:radio', function (e) {
        var attributeId = $(e.target).val();
        var $parent = $(e.target).closest('.js-field-block');
        var level = $parent.data('level');
        $parent.children('.js-attribute-section-block').empty();

        _this10.updateAttributes();

        _this10.renderAttributes(level, attributeId);

        _this10.renderKworksAndOrders();
      });
      this.block.find('.' + this.classes.attributes).on('change', 'input:checkbox', function (e) {
        var attributeId = $(e.target).val();
        var $parent = $(e.target).closest('.js-field-block');
        var level = $parent.data('level');

        _this10.updateAttributes();

        if ($(e.target).prop("checked")) {
          _this10.renderAttributes(level, attributeId);
        } else {
          $parent.children('.js-attribute-section-block').find('.js-field-block[data-parent-id=' + attributeId + ']').remove();
        }

        _this10.renderKworksAndOrders();
      });
      this.block.on('click', '.' + this.classes.linkShowImages, function () {
        _this10.showBlockImages();
      });
      this.block.on('click', '.' + this.classes.linkShowVideos, function () {
        _this10.showBlockVideos();
      });
    }
  }, {
    key: "kworkChange",
    value: function kworkChange() {
      var _this11 = this;

      var updateOrders = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      this.updateKwork();
      this.hideError(this.block.find('.' + this.classes.kworkError));
      var kworkId = this.portfolio.kwork_id;
      var kwork = this.kworksList[parseInt(kworkId)];

      if (updateOrders) {
        if (!kworkId) {
          this.getKworks(function (response) {
            _this11.renderOrders([], null, response.hasOrders);
          });
        } else {
          this.getOrders(kworkId, function (data) {
            _this11.renderOrders([], null, data.hasOrders);
          });
        }
      }

      if (!kwork) {
        return;
      }

      var parentCategoryId = null;
      this.portfolio.category_id = kwork.category;
      $.each(this.additionalData.categories, function (k, v) {
        if (v.CATID == _this11.portfolio.category_id) {
          parentCategoryId = v.parent;
          return false;
        }
      });
      this.renderParentCategories(parentCategoryId);
      this.renderCategories(this.portfolio.category_id);
      this.portfolio.attributes_ids = kwork.attributesIds; // Очищаем выбранные атрибуты

      this.block.find('.' + this.classes.attributes).html('');
      this.renderAttributes(null);
    }
  }, {
    key: "orderChange",
    value: function orderChange() {
      var selectKwork = this.block.find('.' + this.classes.selectKwork);
      this.updateOrder();
      var orderId = parseInt(this.portfolio.order_id);
      var kworkId = "null";

      if (orderId) {
        var order = this.ordersList[parseInt(this.portfolio.order_id)];
        kworkId = order.PID;
      }

      selectKwork.val(kworkId);
      selectKwork.trigger("chosen:updated");
      this.kworkChange(false);
    }
  }, {
    key: "renderKworksAndOrders",
    value: function renderKworksAndOrders() {
      var _this12 = this;

      this.getKworks(function (response) {
        _this12.renderKworks(response.kworks);

        _this12.renderOrders([], null, response.hasOrders);
      });
    }
    /**
     * Наполнение формы
     */

  }, {
    key: "updateContent",
    value: function updateContent() {
      var _this13 = this;

      // Очистка примера превью
      this.clearExampleCardPreview(); // Генерация случайных показателей, для примера карточки

      this.generateExampleCardCnt(); // Заголовок формы

      this.block.find('.js-modal-title').text(this.portfolio && this.portfolio.id ? t('Редактировать работу в Портфолио') : t('Добавить работу в Портфолио')); // Название работы

      this.block.find('input[name="title"]').val(he.decode(this.portfolio.title));
      this.setExampleCardName();
      this.sortableContainer.loadItems(this.portfolio.images);
      this.block.find('.' + this.classes.videos).html('');

      if (this.portfolio.videos.length) {
        $.each(this.portfolio.videos, function (k, v) {
          _this13.youtubeAdd(v);
        });
      }

      this.youtubeAdd();
      this.youtubeVisibleRemoveBtn(); // пересчитаем счетчики

      this.countCharacters(); // обложка

      this.defaultCoverField();
      var coverUrl;
      var coverId = 0;
      var assignedId = this.portfolio.cover.idPortfolioImage || -1;
      var coverPortfolioImage = this.portfolio.images.find(function (x) {
        return x.hash === _this13.portfolio.cover.hash || x.id == assignedId;
      });

      if (coverPortfolioImage) {
        // если обложка выбрана из изображений портфолио
        coverUrl = coverPortfolioImage.urlBig;
        coverId = coverPortfolioImage.id;
        this.showCoverTextNotLoad();
      } else if (this.portfolio.cover && this.portfolio.cover.type === this.COVER_IMAGE_TYPE_VIDEO) {
        // если обложка - это превью первого видео
        coverUrl = this.portfolio.cover.urlBig;
        this.portfolio.cover.urlFromVideo = this.portfolio.cover.urlBig;
        this.showCoverTextNotLoad();
      } else if (this.portfolio.cover && this.portfolio.cover.urlBig) {
        // если обложка загружена
        this.portfolio.cover.urlDefault = this.portfolio.cover.urlBig;
        coverUrl = this.portfolio.cover.urlBig;
        this.showCoverTextLoad();
      } else if (this.portfolio.images.length) {
        // если обложка не выбрана, но есть изображения портфолио, то выбираем первое изображение портфолио для обложки
        coverUrl = this.portfolio.images[0].urlBig;
        coverId = this.portfolio.images[0].id;
        this.showCoverTextNotLoad();
      }

      if (coverUrl) {
        this.updateCoverMini();
        this.updateCoverUrl(coverUrl);
        this.updateCoverIdPortfolioImage(coverId);
        this.updateCoverBlock();
      } else {
        // если обложка не выбрана и нет изображений портфолио
        this.hideCoverUpload();
      }

      this.titleInput.attr('placeholder', this.getPortfolioNumber());

      if (this.page == this.PAGE_MY_PORTFOLIO) {
        this.block.find('.' + this.classes.attributes).html('');
        this.fieldKworks.hide();
        this.fieldOrders.hide();

        if (this.additionalData && !this.additionalData.canDeletePortfolio && this.portfolio.id) {
          this.fixedKworkId = this.portfolio.kwork_id;
          this.block.find('.js-can-delete-portfolio').hide();
        } else {
          this.fixedKworkId = null;
          this.block.find('.js-can-delete-portfolio').show();
          var categoryId = this.portfolio.category_id || null;
          var parentCategoryId = null;
          $.each(this.additionalData.categories, function (k, v) {
            if (v.CATID == categoryId) {
              parentCategoryId = v.parent;
              return false;
            }
          });
          this.renderParentCategories(parentCategoryId);
          this.renderCategories(categoryId);

          if (!this.portfolio.category_id) {
            this.updateCategory();
          }

          this.renderAttributes(); // Kворк

          if (!$.isEmptyObject(this.additionalData.kworks)) {
            this.renderKworks(this.additionalData.kworks, this.portfolio.kwork_id);
          }

          this.setExampleCardCategory();
        } // Заказ


        if (!$.isEmptyObject(this.additionalData.order)) {
          this.renderOrders([this.additionalData.order], this.portfolio.order_id);
        } else {
          this.renderOrders([], null, this.additionalData.hasOrders);
        }
      }
    }
    /**
     * Создание/редактирование портфолио
     * 
     * @param {*} portfolio 
     * @param {*} additional 
     */

  }, {
    key: "edit",
    value: function edit(portfolio) {
      var additional = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      this.portfolioItem = portfolio;
      this.portfolio = $.extend(true, {}, portfolio.data);
      this.additionalData = additional;

      this._modalShow();

      this.updateContent();
      this.validateAll();
      this.portfolioItem.blank = false;
      this.updateSaveButton();
      this.selectBlockVideosOrImages();
    }
  }, {
    key: "setTitleErrors",
    value: function setTitleErrors(fromInput) {
      var portfolioText = '';

      if (fromInput && fromInput === true) {
        portfolioText = this.titleInput.val();
      } else {
        portfolioText = he.decode(this.portfolio.title);
      }

      this.portfolioItem.backendErrors.title = '';

      if (portfolioText.length > this.titleInput[0].maxLength) {
        this.portfolioItem.backendErrors.title = t('Заголовок работы портфолио указан неверно, максимальная длина 40 символов');
      }
    }
  }, {
    key: "updateTitle",
    value: function updateTitle() {
      this.setTitleErrors(true);
      this.portfolio.title = this.titleInput.val();
    }
  }, {
    key: "updateCategory",
    value: function updateCategory() {
      this.portfolio.category_id = this.block.find('.' + this.classes.selectCategories).val();
    }
  }, {
    key: "updateAttributes",
    value: function updateAttributes() {
      var _this14 = this;

      this.portfolio.attributes_ids = [];
      this.block.find('.' + this.classes.attributes).find(':checked').each(function (k, v) {
        var el = $(v);
        var $attrList = el.closest('.attribute-list');
        var multipleMaxCount = $attrList.data('multiple-max-count');
        var checkedSize = $attrList.find('input[type="checkbox"]:checked').size();

        if (multipleMaxCount > 0) {
          if (checkedSize >= multipleMaxCount) {
            $attrList.find('input[type="checkbox"]').not(':checked').prop('disabled', true).parent().css('opacity', '0.5');
          } else {
            $attrList.find('input[type="checkbox"]').not(':checked').prop('disabled', false).parent().css('opacity', '1');
          }
        }

        _this14.portfolio.attributes_ids.push(el.val());
      });
    }
  }, {
    key: "updateKwork",
    value: function updateKwork() {
      this.portfolio.kwork_id = null;

      if (this.fixedKworkId) {
        this.portfolio.kwork_id = this.fixedKworkId;
        return;
      }

      if (this.fieldKworks.is(':visible')) {
        this.portfolio.kwork_id = parseInt(this.block.find('.' + this.classes.selectKwork).val()) || null;
      }
    }
  }, {
    key: "updateOrder",
    value: function updateOrder() {
      this.portfolio.order_id = null;

      if (this.fieldOrders.is(':visible')) {
        this.portfolio.order_id = parseInt(this.block.find('.' + this.classes.selectOrder).val()) || null;
      }
    }
  }, {
    key: "updateCoverCrop",
    value: function updateCoverCrop() {
      this.portfolio.cover.crop = this.getCropperData();
    }
    /**
     * Обновление параметра урла обложки
     * @param url
     */

  }, {
    key: "updateCoverUrl",
    value: function updateCoverUrl(url) {
      this.portfolio.cover.url = url;
      this.portfolio.cover.urlBig = url;
    }
    /**
     * id изображения портфолио, если обложка берется из изображений портфолио
     * @param id
     */

  }, {
    key: "updateCoverIdPortfolioImage",
    value: function updateCoverIdPortfolioImage() {
      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
      this.portfolio.cover.idPortfolioImage = id;
    }
    /**
     * Обновляем hash обложки
     * @param hash
     */

  }, {
    key: "updateCoverHash",
    value: function updateCoverHash() {
      var hash = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.portfolio.cover.hash = hash;
    }
  }, {
    key: "updateImages",
    value: function updateImages() {
      var images = [];
      $.each(this.sortableContainer.items, function (k, v) {
        images.push(v.data);
      });
      this.portfolio.images = images;
    }
  }, {
    key: "updateVideos",
    value: function updateVideos() {
      var videos = [];
      this.block.find('.youtube-field input').each(function (k, v) {
        var el = $(v);
        var link = el.val().trim();

        if (link.length > 0) {
          videos.push(link);
        }
      });
      this.portfolio.videos = videos;
    }
  }, {
    key: "setDefaultTitle",
    value: function setDefaultTitle() {
      if (!this.portfolio.title) {
        this.portfolio.title = this.getPortfolioNumber();
      }
    }
  }, {
    key: "getPortfolioNumber",
    value: function getPortfolioNumber() {
      if (this.portfolio.workNum) {
        return t('Работа №') + this.portfolio.workNum;
      }

      return !$.isEmptyObject(this.additionalData) && this.additionalData.workNum ? t('Работа №') + this.additionalData.workNum : '';
    }
    /**
     * Отображаем блок видео или изображений в зависимости от типа портфолио
     */

  }, {
    key: "selectBlockVideosOrImages",
    value: function selectBlockVideosOrImages() {
      var _images = '<span class="js-portfolio-images-link">' + t('изображения') + '</span>';

      var _video = '<span class="js-portfolio-videos-link">' + t('видео') + '</span>';

      if (this.isTypeVideo()) {
        this.portfolioType = "video";
        this.requiredPortfolioType.html(_video);
        this.secondaryPortfolioType.html(_images);
        this.showBlockVideos();
      } else {
        this.portfolioType = "photo";
        this.requiredPortfolioType.html(_images);
        this.secondaryPortfolioType.html(_video);
        this.validateVideos();
        this.showBlockImages();
      }

      this.updateYoutubeCover();
    }
  }, {
    key: "isTypeVideo",
    value: function isTypeVideo() {
      var categoryId = this.portfolio.category_id;
      var portfolioType;

      if (categoryId) {
        $.each(this.additionalData.categories, function (k, v) {
          if (v.CATID == categoryId) {
            portfolioType = v.portfolio_type;
          }
        });
      }

      if ((this.page == this.PAGE_KWORK || this.page == this.PAGE_ORDER) && this.portfolioType == this.PORTFOLIO_TYPE_VIDEO || portfolioType == this.PORTFOLIO_TYPE_VIDEO) {
        return true;
      } else {
        return false;
      }
    }
    /**
     * Отображаем блок изображений и скрываем блок видео
     */

  }, {
    key: "showBlockImages",
    value: function showBlockImages() {
      this.fieldImages.show();
      this.fieldVideos.hide();
      this.block.find('.' + this.classes.linkShowImages).removeClass('portfolio-images-videos-link');
      this.block.find('.' + this.classes.linkShowVideos).addClass('portfolio-images-videos-link');
      this.block.find('.' + this.classes.linkShowImages).parents('.' + this.classes.field).find('.tooltipster').removeClass('hidden');
    }
    /**
     * Отображаем блок видео и скрываем блок изображений
     */

  }, {
    key: "showBlockVideos",
    value: function showBlockVideos() {
      this.fieldImages.hide();
      this.fieldVideos.show();
      this.block.find('.' + this.classes.linkShowImages).addClass('portfolio-images-videos-link');
      this.block.find('.' + this.classes.linkShowVideos).removeClass('portfolio-images-videos-link');
      this.block.find('.' + this.classes.linkShowImages).parents('.' + this.classes.field).find('.tooltipster').addClass('hidden');
    }
  }, {
    key: "concatErrors",
    value: function concatErrors(field) {
      var errors = [];

      if (field in this.portfolioItem.backendErrors) {
        errors = errors.concat(this.portfolioItem.backendErrors[field]);
      }

      if (field in this.frontendErrors) {
        errors = errors.concat(this.frontendErrors[field]);
      }

      var result = [];
      $.each(errors, function (k, v) {
        if ($.inArray(v, result) == -1) {
          result.push(v);
        }
      });
      return result.join('<br />');
    }
  }, {
    key: "showError",
    value: function showError(field, errorText) {
      var invisible = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      field.html(errorText);

      if (invisible || errorText.length > 0) {
        this.errorFields.push(field);
      }
    }
  }, {
    key: "hideError",
    value: function hideError(field) {
      field.html('');
      var index = this.errorFields.indexOf(field);

      if (index === undefined) {
        return;
      }

      this.errorFields.splice(index, 1);
    }
  }, {
    key: "validateTitle",
    value: function validateTitle() {
      this.frontendErrors.title = [];
      var errorText = this.concatErrors('title');

      if (errorText.length > 0) {
        this.titleInput.addClass('input-error-portfolio');
      } else {
        this.titleInput.removeClass('input-error-portfolio');
      }

      this.showError(this.block.find('.' + this.classes.titleError), errorText);
    }
  }, {
    key: "validateCategory",
    value: function validateCategory() {
      this.frontendErrors.category = [];
      var errorText = this.concatErrors('category');
      this.showError(this.block.find('.' + this.classes.categoryError), errorText);
      this.portfolioItem.backendErrors.category = [];
    }
  }, {
    key: "validateAttributes",
    value: function validateAttributes() {
      this.frontendErrors.attributes = [];
      var errorText = this.concatErrors('attributes');
      this.showError(this.block.find('.' + this.classes.attributesError), errorText);
      this.portfolioItem.backendErrors.attributes = [];
    }
  }, {
    key: "validateKwork",
    value: function validateKwork() {
      this.frontendErrors.kwork = [];
      var errorText = this.concatErrors('kwork');
      this.showError(this.block.find('.' + this.classes.kworkError), errorText);
    }
  }, {
    key: "validateCover",
    value: function validateCover() {
      this.frontendErrors.cover = [];

      if (!this.portfolioItem.blank) {
        if (!this.portfolio.cover.url && (!this.isTypeVideo() || !this.portfolio.cover.urlFromVideo)) {
          this.frontendErrors.cover.push(t('Необходимо загрузить обложку'));
        }
      }

      var errorText = this.concatErrors('cover');
      this.showError(this.block.find('.' + this.classes.coverError), errorText);
    }
  }, {
    key: "validateImages",
    value: function validateImages() {
      var _this15 = this;

      this.frontendErrors.images = [];

      if (!this.portfolioItem.blank && this.page == this.PAGE_KWORK) {
        if (this.portfolioType == this.PORTFOLIO_TYPE_PHOTO && this.portfolio.images.length < 1) {
          this.frontendErrors.images.push(t('Загрузите изображение'));
        }
      }

      var imageErrors = this.portfolioItem.backendErrors.image;

      if (imageErrors && Object.keys(imageErrors).length > 0) {
        if (!('images' in this.portfolioItem.backendErrors)) {
          this.portfolioItem.backendErrors.images = [];
        }

        $.each(this.sortableContainer.items, function (k, v) {
          v.hasError = k in imageErrors;
          v.updateErrorVisuals();

          _this15.portfolioItem.backendErrors.images.push(imageErrors[k]);
        });
      }

      var errorText = this.concatErrors('images');

      if (errorText.length > 0) {
        this.showBlockImages();
      }

      this.showError(this.block.find('.' + this.classes.imagesError), errorText);
      this.portfolioItem.backendErrors.images = [];
      this.portfolioItem.backendErrors.image = [];
    }
    /**
     * Проверка наличия ошибок у полей
     */

  }, {
    key: "getErrors",
    value: function getErrors() {
      this.errorFields = [];
      var items = this.block.find('.' + this.classes.videos).find('.youtube-field input.input-error-portfolio');

      if (items.length) {
        this.errorFields.push(items);
      } else {
        this.errorFields = [];
        this.frontendErrors.videos = [];
      }
    }
    /**
     * Проверка Youtube Url на бэке
     * @param item  Input элемент val которого передается в бэк
     */

  }, {
    key: "backendValidateVideos",
    value: function backendValidateVideos(item) {
      var val = item.val().trim();
      var errorField = item.parent().find('.portfolio-error');

      var _this = this;

      $.ajax({
        type: 'POST',
        url: '/api/portfolio/youtubelinkvalidate?url=' + val,
        data: {
          kwork_id: this.portfolio.kwork_id || null,
          portfolio_id: this.portfolio.id || null
        },
        success: function success(response) {
          if (!response.success) {
            item.addClass(_this.classes.inputClassError);
            errorField.html(response.data);
            _this.hasError = true;

            _this.frontendErrors.videos.push('');

            _this.youtubeRemoveEmptyFiels(true);
          } else {
            item.removeClass(_this.classes.inputClassError);
            _this.hasError = false;
            errorField.text('');

            _this.youtubeRemoveEmptyFiels();
          }
        },
        error: function error(e) {},
        complete: function complete() {
          _this.getErrors();

          _this.updateVideos();

          _this.updateYoutubeCover();
        }
      });
    }
  }, {
    key: "validateVideos",
    value: function validateVideos(saveCheck, curItem) {
      if (!this.portfolioItem.blank) {
        var videosCount = 0;
        var emptyCount = 0;
        this.block.find('.' + this.classes.fieldYoutube + ' input').each(function (k, v) {
          var input = $(v);

          if (input.val().trim().length > 0) {
            videosCount++;
          } else {
            emptyCount++;
          }
        });

        if (curItem) {
          var index = this.block.find('.' + this.classes.fieldYoutube).index(curItem.parents('.youtube-field'));

          var valid = this._youtubeValidation(curItem, index, saveCheck);

          if (!valid) {
            this.youtubeRemoveEmptyFiels();
          } else {
            this.backendValidateVideos(curItem);
          }
        }

        if ((this.page == this.PAGE_KWORK || this.PAGE_MY_PORTFOLIO) && this.portfolioType == this.PORTFOLIO_TYPE_VIDEO && videosCount < 1) {
          var item = this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input');
          var errorField = item.parent().find('.portfolio-error');
          errorField.text(t('Необходимо указать ссылку на видео'));
          this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input').addClass('input-error-portfolio');
          this.youtubeRemoveEmptyFiels();
        }

        if ((this.page == this.PAGE_KWORK || this.PAGE_MY_PORTFOLIO) && this.portfolioType == this.PORTFOLIO_TYPE_PHOTO && videosCount < 1) {
          var _item = this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input');

          var _errorField = _item.parent().find('.portfolio-error');

          _errorField.text('');

          this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input').removeClass('input-error-portfolio');
        }
      }

      this.getErrors();
      this.portfolioItem.backendErrors.videos = [];
      this.portfolioItem.backendErrors.video = {};
    }
    /**
     * Обновить данные для this.portfolio
     */

  }, {
    key: "updateAll",
    value: function updateAll() {
      this.updateTitle();

      if (this.page == this.PAGE_MY_PORTFOLIO) {
        this.updateCategory();
        this.updateAttributes();
        this.updateKwork();
        this.updateOrder();
      }

      this.updateCoverCrop();
      this.updateImages();
      this.updateVideos();
    }
  }, {
    key: "moveToError",
    value: function moveToError() {
      var errorBlock = this.errorFields[0];

      if (errorBlock) {
        var errorPosition = this.block.scrollTop() + errorBlock.offset().top - this.block.offset().top;
        var screenToErrorPosition = errorPosition - $(window).height() / 2;
        this.block.animate({
          scrollTop: screenToErrorPosition
        }, 200);
      }
    }
    /**
     * Сохранение портфолио
     * @param saveForDraft boolean При сохранении черновика не валидируем форму.
     */

  }, {
    key: "save",
    value: function save() {
      var _this16 = this;

      var saveForDraft = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      this.updateAll();
      this.getErrors();
      var photoError = false;
      var videoError = false;

      if (saveForDraft === false) {
        this.validateAll(true);
        var hasErrors = false;
        $.each(this.errorFields, function (k, v) {
          if (v.length > 0) {
            if (v.selector.includes("youtube-field")) {
              videoError = true;
            } else {
              photoError = true;
            }

            hasErrors = true;
          }
        });

        if (hasErrors) {
          if (this.portfolioType == 'photo') {
            if (photoError) {
              this.showBlockImages();
            } else {
              this.showBlockVideos();
            }
          } else if (this.portfolioType == 'video') {
            if (videoError) {
              this.showBlockVideos();
            } else {
              this.showBlockImages();
            }
          }

          this.moveToError();
          return;
        }
      }

      if (!this.errorFields.length) {
        this.portfolioItem.html.removeClass('error');
        this.portfolioItem.html.find('.draggable-block_error').text('');
      } // Превью обложки


      var coverPreview = this.getImgCropper();

      if (coverPreview) {
        this.portfolio.cover.url = coverPreview;
      } // Удаляем обложку от видео, если выбрана обложка другого типа


      if (this.portfolio.cover.type != this.COVER_IMAGE_TYPE_VIDEO) {
        delete this.portfolio.cover.urlFromVideo;
      }

      if (this.page === this.PAGE_KWORK) {
        this.saveSucess(saveForDraft);
      } else {
        this.setDefaultTitle();

        this._lockSaveBtn();

        var portfolioData = this.portfolioItem.getData(this.portfolio);

        if (this.orderId) {
          portfolioData["order_id"] = this.orderId;
        }

        var formData = new FormData();
        formData.append('portfolio', JSON.stringify(portfolioData));

        if (this.xhr) {
          this.xhr.abort();
        }

        this.xhr = $.ajax({
          url: '/portfolio/add',
          data: formData,
          async: true,
          contentType: false,
          processData: false,
          type: 'POST',
          complete: function complete(jXhr, status) {
            var rj = {};

            try {
              rj = JSON.parse(jXhr.responseText);
            } catch (e) {}

            if ('success' in rj && rj.success == true) {
              if (_this16.reloadAfterSave == true) {
                window.location = window.location.href;
                return;
              }

              if (_this16.page == _this16.PAGE_ORDER && !_this16.portfolio.id) {
                window.location = window.location.href;
              } else if (_this16.page == _this16.PAGE_MY_PORTFOLIO) {
                _this16.updatePortfolioCard(rj.data.id);

                _this16.saveSucess();
              } else {
                _this16.saveSucess();
              }
            } else {
              _this16.portfolioItem.applyErrors(rj.data);

              _this16.validateAll();

              _this16.moveToError();

              _this16._unlockSaveBtn();
            }
          }
        });
      }

      if (this.additionalData && this.additionalData.workNum) {
        this.additionalData.workNum++;
      }
    }
  }, {
    key: "saveSucess",
    value: function saveSucess() {
      var saveForDraft = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

      // Если сохранение черновика и работа пустая, то не сохраняем
      if (!this.portfolio.draftHash && !this.portfolio.id) {
        this.portfolio.draftHash = Date.now();
      }

      if (saveForDraft === true && !this.portfolio.title && !this.portfolio.images.length && !this.portfolio.videos.length && this.portfolio.cover.hash === null) {
        return false;
      }

      if (saveForDraft === true && !this.portfolio.title) {
        this.portfolio.title = this.titleInput.attr('placeholder');
      } else {
        this.setDefaultTitle();
      }

      $.extend(this.portfolioItem.data, this.portfolio);
      this.onSave();

      if (saveForDraft === false) {
        this.confirmAtClose = false;

        this._modalHide();

        this._unlockSaveBtn();

        this.destroyCropper();
      }
    }
    /**
     * Подсчет количества символов input/textarea
     * 
     * @param {*} item Элемент для подстчета кол-ва знаков
     * (если пусто, пробежится по всем полям и расчитает)
     */

  }, {
    key: "countCharacters",
    value: function countCharacters(item) {
      var _this17 = this;

      if (item) {
        this._countCharacters(item);
      } else {
        $(this.block).find('.' + this.classes.fieldCounter).each(function (k, v) {
          _this17._countCharacters($(v));
        });
      }
    }
    /**
     * Добавление поля youtube
     * 
     * @param {string} value Ссылка на видео
     */

  }, {
    key: "youtubeAdd",
    value: function youtubeAdd() {
      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      // Должно работать только для полей ручного наполнения
      var focusInput = this.block.find('.' + this.classes.videos).find('input:focus');

      if (focusInput.length) {
        var focusInputValue = focusInput.val();

        if (Youtube.isUrl(focusInputValue) == false) {
          return;
        }
      } // end


      var videoContent = this.block.find('.' + this.classes.videos);
      var fields = this.block.find('.' + this.classes.videos).find('.' + this.classes.fieldYoutube);
      var fieldsEmpty = videoContent.find('.' + this.classes.fieldYoutube + ' input').filter(function () {
        return this.value === '';
      });

      if (fields.length >= this.MAX_YOUTUBE || fieldsEmpty.length > 0) {
        return false;
      }

      videoContent.append("\n\t\t\t<div class=\"" + this.classes.fieldYoutube + "\">\n\t\t\t\t<input type=\"text\"\n\t\t\t\t\t class=\"input styled-input f14 wMax\"'\n\t\t\t\t\t placeholder=\"" + t("Ссылка на видео с YouTube") + "\"\n\t\t\t\t\t value=\"" + value + "\" />\n\t\t\t\t<div class=\"js-youtube-remove youtube-remove\"><i class=\"kwork-icon icon-close\"></i></div>\n\t\t\t\t<div class=\"portfolio-error\"></div>\n\t\t\t</div>\n\t\t");
      this.youtubeVisibleRemoveBtn();
    }
    /**
     * Удаление поля youtube
     * 
     * @param {*} item Элемент для удаления (input)
     */

  }, {
    key: "youtubeRemove",
    value: function youtubeRemove(item) {
      if (!item) {
        return false;
      }

      var cntYoutube = this.block.find('.' + this.classes.fieldYoutube).length;

      if (cntYoutube > 1) {
        item.closest('.' + this.classes.fieldYoutube).remove();
        this.youtubeAdd();
      } else {
        item.removeClass(this.classes.inputClassError);
        item.val('');
        var errorField = item.parent().find('.portfolio-error');
        errorField.text('');
      }

      this.youtubeVisibleRemoveBtn();
      this.updateYoutubeCover();
    }
    /**
     * События на youtube поле
     *
     * @param {*} item input
     */

  }, {
    key: "youtubeEvents",
    value: function youtubeEvents(item) {
      // Убираем дубликаты
      this.youtubeRemoveDuplicate(item); // Показывать/скрывать кнопку удаления

      this.youtubeVisibleRemoveBtn(); // Валидация

      this.validateVideos(null, item);
      this.updateYoutubeCover();
    }
    /**
     * Добавить превью ютуб ролика в нарезку если не загружена обложка и тип портфолио "видео"
     * 
     * показывать превью видео в мниатюрках, если после добавления видео загрузили обложку или изображения портфолио
     */

  }, {
    key: "updateYoutubeCover",
    value: function updateYoutubeCover() {
      if ((this.portfolio.images.length || this.portfolio.cover.urlDefault) && !this.portfolio.cover.urlFromVideo) {
        return;
      }

      delete this.portfolio.cover.urlFromVideo;

      if (!this.isTypeVideo() || this.frontendErrors.videos.length) {
        this.selectRandomCover();
        return;
      }

      var items = this.block.find('.' + this.classes.fieldYoutube + ' input');

      if (items.length === 1 && !items.val()) {
        this.selectRandomCover();
        return;
      }

      var value = $(items[0]).val();
      var thumb = Youtube.thumb(value, 'max'); // превью видео проверяется на наличие нужного размера и после этого добавляется в нарезку

      this.setYoutubeThumb(thumb);
    }
    /**
     * Превью видео проверяется на наличие нужного размера
     * @param thumb
     */

  }, {
    key: "setYoutubeThumb",
    value: function setYoutubeThumb(thumb) {
      var _this18 = this;

      var img = new Image();

      img.onload = function () {
        var thumb;

        if (img.width > _this18.MIN_WIDTH_CROP_BOX && img.height > _this18.MIN_HEIGHT_CROP_BOX) {
          thumb = img.src;
        }

        _this18.showYoutubeCoverThumb(thumb);
      };

      img.src = thumb;
    }
    /**
     * Превью видео добавляется в нарезку
     * @param thumb
     */

  }, {
    key: "showYoutubeCoverThumb",
    value: function showYoutubeCoverThumb(thumb) {
      if (!thumb) {
        this.selectRandomCover();
        return;
      }

      this.portfolio.cover.urlFromVideo = thumb;
      this.portfolioItem.backendErrors.cover = [];
      this.updateCoverMini();

      if (!this.portfolio.cover.urlDefault) {
        this.updateCoverUrl(thumb);
        this.updateCoverIdPortfolioImage();
        this.updateCoverBlock();
        this.portfolio.cover.type = this.COVER_IMAGE_TYPE_VIDEO;
      }
    }
    /**
     * Проверка и удаление дубликата ссылки на видео
     * 
     * @param {*} item input с ссылкой
     */

  }, {
    key: "youtubeRemoveDuplicate",
    value: function youtubeRemoveDuplicate(item) {
      if (!item || item.length == 0) {
        return false;
      }

      var value = item.val();
      var tplValArray = [];

      if (value == '') {
        return null;
      }

      var items = this.block.find('.' + this.classes.fieldYoutube + ' input');
      items.not(item).each(function (k, v) {
        var val = $(v).val();

        if (val != '') {
          tplValArray.push(val);
        }
      });

      if (tplValArray.length > 0 && $.inArray(value, tplValArray) >= 0) {
        this.youtubeRemove(item);
      }
    }
    /**
     * Скрывает/показывает кнопку удаления 
     * youtube поля в зависимости от заполненности
     */

  }, {
    key: "youtubeVisibleRemoveBtn",
    value: function youtubeVisibleRemoveBtn() {
      var _this19 = this;

      var items = $('.' + this.classes.videos + ' .' + this.classes.fieldYoutube + ' input');
      items.each(function (k, v) {
        var value = $(v).val();
        var removeBtn = $(v).closest('.' + _this19.classes.fieldYoutube).find('.' + _this19.classes.youtubeRemove);

        if (value.length <= 0) {
          removeBtn.hide();
        } else {
          removeBtn.show();
        }
      });
    }
    /**
     * Удаление пустых полей
     */

  }, {
    key: "youtubeRemoveEmptyFiels",
    value: function youtubeRemoveEmptyFiels() {
      var _this20 = this;

      var withoutAdd = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var items = $('.' + this.classes.videos + ' .' + this.classes.fieldYoutube + ' input');
      items.each(function (k, v) {
        var input = $(v);
        var value = input.val();
        var field = input.closest('.' + _this20.classes.fieldYoutube);
        var isFocus = input.is(':focus');

        if (value.length == 0 && !isFocus) {
          field.remove();
        }
      });

      if (!withoutAdd) {
        this.youtubeAdd();
      }
    }
    /**
     * Логика расчета количества символов input/textarea
     * 
     * @param {*} item Элемент для подстчета кол-ва знаков
     */

  }, {
    key: "_countCharacters",
    value: function _countCharacters(item) {
      var filed = item.closest('.' + this.classes.field);
      var maxCharacter = item.data('max');
      var hintMsg = '';
      var value = '';

      if (item.is("input")) {
        value = item.val();
      } else if (item.is("textarea")) {
        value = item.text();
      } else {
        return false;
      }

      if (maxCharacter) {
        maxCharacter = parseInt(maxCharacter);
        var count = value.replace(/&nbsp;/gi, " ").replace(/\s\s+/g, " ").length ^ 0;
        var fieldHint = filed.find('.' + this.classes.fieldCounterHint);

        if (count > 0) {
          hintMsg = t('{{0}} из', [count]) + ' ';
        }

        hintMsg += t('{{0}} максимум', [maxCharacter]);
        fieldHint.text(hintMsg);

        if (count > maxCharacter) {
          fieldHint.addClass('color-red');
        } else {
          fieldHint.removeClass('color-red');
        }
      }
    }
    /**
     * Валидация youtube полей
     *
     * @param {*} input Поле youtube
     * @param pos
     * @param saveCheck
     * @returns {boolean}
     * @private
     */

  }, {
    key: "_youtubeValidation",
    value: function _youtubeValidation(input, pos, saveCheck) {
      if (!input) {
        return;
      }

      if (!window.Youtube) {
        console.error('Youtube is not defined ("youtube-thumbnail.js")');
        return;
      }

      var val = input.val().trim();
      var errorField = input.parent().find('.portfolio-error');

      if (!val.length > 0) {
        input.removeClass(this.classes.inputClassError);
        this.frontendErrors.videos = [];
        errorField.text('');
        return;
      }

      var urlCollision = false;

      if (this.checkVideoUrl) {
        urlCollision = !this.checkVideoUrl(val, pos);
      }

      if (pos in (this.portfolioItem.backendErrors.video || {})) {
        input.addClass(this.classes.inputClassError);
        errorField.html(this.portfolioItem.backendErrors.video[pos]);
        return false;
      } else if (urlCollision) {
        input.addClass(this.classes.inputClassError);
        errorField.text(t('Данное видео уже загружено в рамках другой работы'));
        return false;
      } else if (val && !Youtube.isUrl(val)) {
        input.addClass(this.classes.inputClassError);
        errorField.text(t('Указанная ссылка не является ссылкой на видео с youtube.com'));
        return false;
      } else {
        return true;
      }
    }
    /**
     * Валидация данных
     */

  }, {
    key: "validateAll",
    value: function validateAll(saveCheck) {
      this.validateTitle();
      this.validateCategory();
      this.validateAttributes();
      this.validateKwork();
      this.validateCover();
      this.validateImages();
      this.validateVideos(saveCheck);
    }
  }, {
    key: "updateSaveButton",
    value: function updateSaveButton() {
      if (this.isFormValid()) {
        this._unlockSaveBtn();
      } else {
        this._lockSaveBtn();
      }
    }
    /**
     * Валидация данных
     */

  }, {
    key: "isFormValid",
    value: function isFormValid() {
      if (!this.sortableContainer.isReady()) {
        return false;
      }

      return true;
    }
    /**
     * Заблокировать кнопку "Сохранить"
     */

  }, {
    key: "_lockSaveBtn",
    value: function _lockSaveBtn() {
      var btn = this.block.find('.' + this.classes.btnSave);
      btn.attr('disabled', 'disabled').addClass(this.classes.btnDisabled);
    }
    /**
     * Разблокировать кнопку "Сохранить"
     */

  }, {
    key: "_unlockSaveBtn",
    value: function _unlockSaveBtn() {
      var btn = this.block.find('.' + this.classes.btnSave);
      btn.removeAttr('disabled').removeClass(this.classes.btnDisabled);
    }
    /**
     * Открытие модального окна
     */

  }, {
    key: "_modalShow",
    value: function _modalShow() {
      this.confirmAtClose = true;
      this.setTitleErrors();
      this.block.modal('show');
    }
    /**
     * Закрытие модального окна
     */

  }, {
    key: "_modalHide",
    value: function _modalHide() {
      this.confirmAtClose = false;
      this.block.modal('hide');
      this.destroyCropper();
      this.clearCover();
    }
    /**
     * Шаблон изображения для обложки
     * 
     * @param {string} url Путь к картинке
     */

  }, {
    key: "_cropImgTpl",
    value: function _cropImgTpl() {
      var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return '<div class="cover-image__cropper"><img src="' + url + '"></div>';
    }
    /**
     * Отрисовать список Категорий
     * 
     * @param {*} currentId Выбрать заказ из списка
     */

  }, {
    key: "renderParentCategories",
    value: function renderParentCategories() {
      var currentId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var selectCategories = this.block.find('.' + this.classes.selectParentCategories);
      selectCategories.html('');
      var categories = this.additionalData.parentCategories;
      selectCategories.append('<option disabled="" selected="selected" hidden="">' + t('Выберите категорию') + '</option>');
      $.each(categories, function (k, v) {
        selectCategories.append('<option value="' + v.CATID + '" data-parent="' + v.parent + '">' + v.name + '</option>');
      });
      this.selectOption(selectCategories, currentId); // обновление списка для chosen селекта

      selectCategories.trigger('chosen:updated');
    }
    /**
     * Отрисовать список подкатегорий
     * 
     * @param {*} currentId Выбрать заказ из списка
     */

  }, {
    key: "renderCategories",
    value: function renderCategories() {
      var currentId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var selectCategories = this.block.find('.' + this.classes.selectCategories);
      var parentCategorieId = this.block.find('.' + this.classes.selectParentCategories).find('option:selected').val();
      selectCategories.html('');
      var categories = this.additionalData.categories;
      var categoriesCount = 0;
      $.each(categories, function (k, v) {
        if (v.parent != parentCategorieId) {
          return true;
        }

        selectCategories.append('<option value="' + v.CATID + '" data-parent="' + v.parent + '">' + v.name + '</option>');
        categoriesCount++;
      });

      if (categoriesCount < 1) {
        selectCategories.parent().addClass('empty-list');
      } else {
        selectCategories.parent().removeClass('empty-list');
      }

      this.selectOption(selectCategories, currentId); // обновление списка для chosen селекта

      selectCategories.trigger('chosen:updated');
    }
    /**
     * Отрисовать список кворков
     * 
     * @param {*} kworks Список кворков
     * @param {*} currentId Выбрать заказ из списка
     */

  }, {
    key: "renderKworks",
    value: function renderKworks() {
      var _this21 = this;

      var kworks = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var currentId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var selectKwork = this.block.find('.' + this.classes.selectKwork);
      selectKwork.html('<option value="null">' + t('Не выбрано') + '</option>');

      if ($.isEmptyObject(kworks)) {
        this.fieldKworks.hide();
      } else {
        this.fieldKworks.show();
        $.each(kworks, function (k, v) {
          _this21.kworksList[parseInt(v.PID)] = v;
          selectKwork.append('<option value="' + v.PID + '">' + v.gtitle + '</option>');
        });
      }

      this.selectOption(selectKwork, currentId); // обновление списка для chosen селекта

      selectKwork.trigger('chosen:updated');
    }
    /**
     * Отрисовать список заказов
     * 
     * @param {*} orders Список заказов
     * @param {*} currentId Выбрать заказ из списка
     * @param {*} hasOrders Есть ли заказы для загрузки
     * @param {*} isFull Это рендернг полного списка
     */

  }, {
    key: "renderOrders",
    value: function renderOrders() {
      var _this22 = this;

      var orders = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var currentId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var hasOrders = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      var isFull = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
      var selectOrder = this.block.find('.' + this.classes.selectOrder);

      if (isFull) {
        this.block.find('.' + this.classes.selectOrder).parent().addClass('loaded');
      } else {
        this.block.find('.' + this.classes.selectOrder).parent().removeClass('loaded');
      }

      selectOrder.html('<option value="null">' + t('Не выбрано') + '</option>');

      if (this.additionalData.order && this.additionalData.order.OID && (!this.portfolio.kwork_id || this.additionalData.order.PID == this.portfolio.kwork_id)) {
        orders.unshift(this.additionalData.order);
      }

      if (!$.isEmptyObject(orders)) {
        hasOrders = true;
      }

      if (!hasOrders) {
        this.fieldOrders.hide();
      } else {
        this.fieldOrders.show();

        if (!$.isEmptyObject(orders)) {
          $.each(orders, function (k, v) {
            _this22.ordersList[parseInt(v.OID)] = v;
            var dateDone = new Date(v.date_done).toLocaleString() || null;
            dateDone = dateDone ? t('принят:') + ' ' + dateDone : '';
            var payerUserName = v.payerUsername || null;
            payerUserName = payerUserName ? t('покупатель:') + ' ' + payerUserName : '';
            var separete = dateDone && payerUserName ? '; ' : '';
            var value = v.kwork_title + ' (' + dateDone + separete + payerUserName + ')';
            selectOrder.append('<option' + (v.OID == _this22.portfolio.order_id ? ' selected' : '') + ' value="' + v.OID + '">' + value + '</option>');
          });
        }
      }

      this.selectOption(selectOrder, currentId); // обновление списка для chosen селекта

      selectOrder.trigger('chosen:updated');
    }
    /**
     * Выбрать опцию из выпадающего списка
     * 
     * @param {*} select Элемент селекта
     * @param {int|string} value Выбор по значению
     */

  }, {
    key: "selectOption",
    value: function selectOption() {
      var select = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

      if (!select || !value) {
        return false;
      }

      select.find('option').prop('selected', false).removeAttr('selected');
      select.find('option[value="' + value + '"]').prop('selected', true);
    }
    /**
     * Получить список заказов по кворку
     * 
     * @param {*} kworkId 
     * @param {*} successCallback 
     */

  }, {
    key: "getOrders",
    value: function getOrders() {
      var _this23 = this;

      var kworkId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var successCallback = arguments.length > 1 ? arguments[1] : undefined;
      var selectOrder = this.block.find('.' + this.classes.selectOrder);
      this.startSelectLoader(selectOrder);

      if (this.getOrdersXhr) {
        this.getOrdersXhr.abort();
      }

      this.getOrdersXhr = $.ajax({
        type: 'POST',
        url: '/portfolio/load_form_orders',
        data: {
          kworkId: kworkId
        },
        dataType: 'json',
        success: function success(response) {
          _this23.stopSelectLoader(selectOrder);

          if (response.success) {
            if (successCallback) {
              successCallback(response.data);
            }
          } else {// TODO view error
          }
        }
      });
    }
    /**
     * Получить полный список заказов по кворку
     * 
     * @param {*} kworkId 
     * @param {*} successCallback 
     */

  }, {
    key: "getFullOrders",
    value: function getFullOrders() {
      var _this24 = this;

      var kworkId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var successCallback = arguments.length > 1 ? arguments[1] : undefined;
      var selectOrder = this.block.find('.' + this.classes.selectOrder);
      this.startSelectLoader(selectOrder);

      if (this.getOrdersXhr) {
        this.getOrdersXhr.abort();
      }

      this.getOrdersXhr = $.ajax({
        type: 'POST',
        url: '/portfolio/load_form_orders_full',
        data: {
          kworkId: kworkId
        },
        dataType: 'json',
        success: function success(response) {
          _this24.stopSelectLoader(selectOrder);

          if (response.success) {
            if (successCallback) {
              successCallback(response.data);
            }
          } else {// TODO view error
          }
        }
      });
    }
    /**
     * Получить список кворков
     * 
     * @param {*} successCallback
     */

  }, {
    key: "getKworks",
    value: function getKworks(successCallback) {
      var _this25 = this;

      if (this.kworksOrdersXhr) {
        this.kworksOrdersXhr.abort();
      }

      var categoryId = this.portfolio.category_id;
      var attributesId = this.portfolio.attributes_ids;

      if (!categoryId) {
        return false;
      }

      var selectKwork = this.block.find('.' + this.classes.selectKwork);
      var selectOrder = this.block.find('.' + this.classes.selectOrder);
      this.startSelectLoader(selectKwork);
      this.startSelectLoader(selectOrder);
      this.kworksOrdersXhr = $.ajax({
        type: 'POST',
        url: '/portfolio/load_form_kworks',
        data: {
          categoryId: categoryId,
          attributesId: attributesId
        },
        dataType: 'json',
        success: function success(response) {
          _this25.stopSelectLoader(selectKwork);

          _this25.stopSelectLoader(selectOrder);

          if (response.success) {
            if (successCallback) {
              successCallback(response.data);
            }
          } else {// TODO view error
          }
        }
      });
    }
    /**
     * Запуск лоадера для селекта
     * 
     * @param {*} select Селект
     */

  }, {
    key: "startSelectLoader",
    value: function startSelectLoader(select) {
      this.stopSelectLoader(select);
      var parent = select.parent();
      var chosen = parent.addClass('empty-list');
      parent.append('<div class="js-loader select-loader">' + '<img src="' + Utils.cdnImageUrl("/ajax-loader.gif") + '">' + t('Загрузка...') + '</div>');
    }
    /**
     * Останавливаем лоадер для селекта
     * 
     * @param {*} select Селект
     */

  }, {
    key: "stopSelectLoader",
    value: function stopSelectLoader(select) {
      var parent = select.parent();
      var loader = parent.find('.js-loader');
      loader.remove();
      parent.removeClass('empty-list');
    }
    /**
     * Запрос атрибутов с сервера
     * 
     * @param int[] ids Идентификаторы
     * @param function callback Колбэк после загрузки всех данных
     */

  }, {
    key: "requestAttributes",
    value: function requestAttributes(ids, callback) {
      var _this26 = this;

      if (ids.length <= 0) {
        callback();
        return;
      }

      var id; // Выбираем все загруженные атрибуты
      // Ищем в массиве уже имеющийся id атрибута

      var $inputs = this.block.find('.' + this.classes.attributes + ' input');
      $($inputs).each(function () {
        var val = parseInt($(this).attr('value'));
        var key = ids.indexOf(val);

        if (key != -1) {
          id = ids.splice(key, 1)[0];
          return false;
        }
      }); // Если не нашли атрибу, берем первый

      if (!id) {
        id = ids.shift();
      }

      if (!id && !this.portfolio.category_id) {
        callback();
        return;
      }

      var data = {
        'categoryId': this.portfolio.category_id,
        'lang': window.lang,
        'onlyPortfolioAllowed': 1
      };

      if (id) {
        data.attributeId = id;
      }

      if (this.attrXhr) {
        this.attrXhr.abort();
      }

      this.attrXhr = $.ajax({
        type: 'GET',
        url: '/api/attribute/loadclassification',
        data: data,
        dataType: 'json',
        success: function success(response) {
          if (response.success) {
            var $appendBlock = _this26.block.find('.' + _this26.classes.attributes + ' input[value=' + id + ']').closest('.js-field-block').children('.js-attribute-section-block');

            if (!$appendBlock.length) {
              $appendBlock = _this26.block.find('.' + _this26.classes.attributes);
            }

            $appendBlock.append(response.html);

            _this26.requestAttributes(ids, callback);
          } else {// TODO view error
          }
        }
      });
    }
    /**
     * Загрузка списка атрибутов
     * 
     * @param level уровень вложенности
     * @param attributeId id атрибута в который мы хотим загрузить под атрибуты
     */

  }, {
    key: "renderAttributes",
    value: function renderAttributes(level, attributeId) {
      var _this27 = this;

      this.attributesLoaded = false;
      var attributes;

      if (level) {
        attributes = [this.portfolio.attributes_ids[this.portfolio.attributes_ids.length - 1]];
      } else {
        attributes = [null].concat(this.portfolio.attributes_ids || []);
      }

      if (attributeId) {
        attributes = [attributeId];
      }

      this.requestAttributes(attributes, function () {
        _this27.block.find('.js-field-block').each(function (k, v) {
          $(v).attr('data-level', k + 1);
        });

        if (_this27.portfolio.attributes_ids) {
          $.each(_this27.portfolio.attributes_ids, function (k, v) {
            _this27.block.find('.js-field-block input[value="' + v + '"]').prop('checked', true);
          });
        }

        _this27.updateAttributes();

        _this27.validateAttributes();

        _this27.attributesLoaded = true;
      });
    }
    /**
     * Разница объектов
     * 
     * @param {*} obj1
     * @param {*} obj2
     */

  }, {
    key: "getObjectChanges",
    value: function getObjectChanges(obj1, obj2) {
      var changes = {};

      for (var prop in obj2) {
        if (!obj1 || obj1[prop] != obj2[prop]) {
          if (_typeof(obj2[prop]) == "object") {
            var c = this.getObjectChanges(obj1[prop], obj2[prop]);

            if (!$.isEmptyObject(c)) {
              changes[prop] = c;
            }
          } else {
            changes[prop] = obj2[prop];
          }
        }
      }

      return changes;
    }
    /**
     * Поиск массива и его сортировка
     * @param {*} obj
     */

  }, {
    key: "sortInArray",
    value: function sortInArray(obj) {
      var sort = {};

      for (var value in obj) {
        var type = Object.prototype.toString.call(obj[value]);

        if (type === '[object Array]') {
          sort[value] = obj[value].sort();
        } else if (type === '[object Object]') {
          sort[value] = this.sortInArray(obj[value]);
        } else {
          sort[value] = obj[value];
        }
      }

      return sort;
    }
    /**
     * Были ли произведены изменения в форме
     * 
     * @return {bool} 
     * 	true - есть изменения;
     * 	false - изменений нет;
     */

  }, {
    key: "isChangedPortfolio",
    value: function isChangedPortfolio() {
      var rawData = Object.assign({}, this.portfolioItem.data);
      var changeData = Object.assign({}, this.portfolio);
      rawData.coverHash = rawData.cover.hash;
      changeData.coverHash = changeData.cover.hash;
      delete rawData.cover;
      delete changeData.cover;
      rawData = this.sortInArray(rawData);
      changeData = this.sortInArray(changeData);
      var diff = this.getObjectChanges(rawData, changeData);

      if ($.isEmptyObject(diff)) {
        return false;
      }

      return true;
    }
    /**
     * Допустимая погрешность для кроппа
     * 
     * @param {*} a 
     * @param {*} b 
     */

  }, {
    key: "allowableRangeForCrop",
    value: function allowableRangeForCrop(a, b) {
      if (!a || !b) {
        return;
      }

      return Math.abs(a - b) < 0.0001 ? true : false;
    }
    /**
     * Обновление/добавление карточки портфолио
     * 
     * @param {int} portfolioId Id нового/отредактированного портфолио
     */

  }, {
    key: "updatePortfolioCard",
    value: function updatePortfolioCard(portfolioId) {
      var _this28 = this;

      if (this.page != this.PAGE_MY_PORTFOLIO) {
        return false;
      }

      var portfolioList = $('.portfolio-list-collage');
      var actualPortfolioCard = '';

      if (!this.portfolio.id) {
        window.location = window.location.href;
        return;
      }

      $.ajax({
        type: 'GET',
        url: '/portfolio_card/' + portfolioId,
        dataType: 'html',
        success: function success(response) {
          actualPortfolioCard = response;

          if (_this28.portfolio.id) {
            // edit
            var portfolioCard = portfolioList.find('.portfolio-card-collage[data-id="' + portfolioId + '"]');
            portfolioCard.replaceWith(actualPortfolioCard);
          } else {
            // new
            // вот тут что-то нужно придумать
            // с корректным добавлением и убрать дубли на пагинацию
            portfolioList.prepend(actualPortfolioCard);
            portfolioList.find('.portfolio-card-collage').last().remove();
          }
        },
        error: function error(e) {}
      });
    }
    /**
     * Генерация случайных показателей, для примера карточки
     */

  }, {
    key: "generateExampleCardCnt",
    value: function generateExampleCardCnt() {
      var min = 100;
      var max = 999;
      var views = Math.round(min - 0.5 + Math.random() * (max - min + 1));
      var likes = Math.round(views * 0.6);
      var comments = Math.round(likes * 0.25);
      this.exampleCardCntViews.text(views);
      this.exampleCardCntLikes.text(likes);
      this.exampleCardCntComments.text(comments);
    }
    /**
     * Установить название для карточки примера
     */

  }, {
    key: "setExampleCardName",
    value: function setExampleCardName() {
      var placeholder = this.exampleCardName.data('isEmpty');
      var name = this.portfolio.title || this.getPortfolioNumber() || placeholder;
      this.exampleCardName.html(name);
    }
    /**
     * Установить категорию для карточки примера
     */

  }, {
    key: "setExampleCardCategory",
    value: function setExampleCardCategory() {
      var placeholder = this.exampleCardCategory.data('isEmpty');
      var categoryId = this.portfolio.category_id;
      var categoryName = '';

      if (categoryId) {
        $.each(this.additionalData.categories, function (k, v) {
          if (v.CATID == categoryId) {
            categoryName = v.name;
          }
        });
      }

      this.exampleCardCategory.text(categoryName || placeholder);
    }
    /**
     * Очистка примера превью
     */

  }, {
    key: "clearExampleCardPreview",
    value: function clearExampleCardPreview() {
      this.exampleCardThumbnail.find('img').remove();
      this.exampleCardThumbnail.css({
        overflow: 'hidden'
      });
    }
  }]);

  return PortfolioModal;
}();

/***/ }),

/***/ "./public_html/js/app/portfolio-upload/sortable-card-list.js":
/*!*******************************************************************!*\
  !*** ./public_html/js/app/portfolio-upload/sortable-card-list.js ***!
  \*******************************************************************/
/*! exports provided: SortableCardList */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SortableCardList", function() { return SortableCardList; });
/* harmony import */ var _portfolio_modal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./portfolio-modal.js */ "./public_html/js/app/portfolio-upload/portfolio-modal.js");
/* harmony import */ var _sortable_card_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./sortable-card.js */ "./public_html/js/app/portfolio-upload/sortable-card.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var SortableCardList =
/*#__PURE__*/
function () {
  function SortableCardList(el) {
    var _this = this;

    _classCallCheck(this, SortableCardList);

    this.draggedBlock = null;
    this.stageOffset = null;
    this.stageWidth = 0;
    this.stageHeight = 0;
    this.containerWidth = 0;
    this.containerHeight = 0;
    this.containerRowSize = 3;
    this.blockDragLeft = 0;
    this.blockDragTop = 0;
    this.stageBlock = $(el);
    this.draggableBlocks = this.stageBlock.find('.draggable-blocks');
    this.placeholders = this.stageBlock.find('.placeholders');
    this.errorBlock = this.stageBlock.find('.portfolio-error');
    this.type = this.stageBlock.data('type');
    this.maxCount = this.stageBlock.data('maxCount') || 9;
    this.sortable = this.stageBlock.data('sortable') == "unsortable" ? false : true;
    this.items = [];
    this.additionalData = {};
    var additional = this.stageBlock.find('.data-additional');

    if (additional.length) {
      this.additionalData = JSON.parse(additional.val());
    }

    if (this.type == 'portfolios') {
      this.modal = new _portfolio_modal_js__WEBPACK_IMPORTED_MODULE_0__["PortfolioModal"]();

      this.modal.onSave = function (item) {
        _this.itemSave(_this.modal.portfolioItem);

        _this.modal.portfolioItem.hasError = false;

        _this.modal.portfolioItem.updateErrorVisuals();

        $.each(_this.items, function (k, vItem) {
          if (vItem.hasError) {
            return false;
          }

          _this.errorBlock.text('');
        });
      };

      this.modal.getImagesHashes = function (hash) {
        return _this.getImagesHashes(hash);
      };

      this.modal.getCoverHashes = function (hash) {
        return _this.getCoverHashes(hash);
      };

      this.modal.checkCoverHash = function (hash) {
        return _this.checkCoverHash(hash);
      };

      this.modal.checkImageHash = function (hash) {
        return _this.checkImageHash(hash);
      };

      this.modal.checkVideoUrl = function (url, pos) {
        return _this.checkVideoUrl(url, pos);
      };

      this.loadItems(window.portfolios);
    }

    this.stageBlock.find('.create-block').on('click', function () {
      _this.createItemDialogue();
    });
    $(document).mousemove(function (e) {
      return _this.dragMove(e);
    });
    $(document).mouseup(function (e) {
      _this.dragStop(e);
    });
    $(window).on('resize', function () {
      _this.updateOffsets();

      _this.updateAllBlockCoordinates();
    });
  }

  _createClass(SortableCardList, [{
    key: "updateAddBlock",
    value: function updateAddBlock() {
      if (this.items.length >= this.maxCount) {
        this.stageBlock.find('.create-block').hide();
        return 0;
      } else {
        this.stageBlock.find('.create-block').show();
        return 1;
      }
    }
  }, {
    key: "updatePlaceholders",
    value: function updatePlaceholders() {
      var addBlockCount = this.updateAddBlock();
      var placeholders = '';

      for (var i = 0, l = this.items.length + addBlockCount; i < l; i++) {
        placeholders += '<div class="placeholder"><div class="image"></div></div>';
      }

      this.placeholders.html(placeholders);
    }
  }, {
    key: "createItemDialogue",
    value: function createItemDialogue() {
      var _this2 = this;

      var item = this.createItem();

      if (this.type == 'portfolios') {
        this.modal.edit(item, this.additionalData);
      } else {
        item.onLoad = function () {
          _this2.itemSave(item);
        };

        item.fileUploader.upload();
      }
    }
  }, {
    key: "editItem",
    value: function editItem(item) {
      if (this.type == 'portfolios') {
        this.modal.edit(item, this.additionalData);
      } else {
        this.uploaderBlock.trigger('click');
      }
    }
  }, {
    key: "loadItems",
    value: function loadItems(items) {
      var _this3 = this;

      this.items = [];
      this.draggableBlocks.find('.item').remove();
      $.each(items, function (k, v) {
        var item = _this3.createItem(v);

        _this3.items.push(item);

        _this3.draggableBlocks.append(item.html);
      });
      this.updatePlaceholders();
      this.updateOffsets();
      this.updateAllBlockCoordinates();
    }
  }, {
    key: "itemSave",
    value: function itemSave(item) {
      item.updateImage();

      if (this.items.indexOf(item) != -1) {
        return;
      }

      this.items.push(item);
      this.draggableBlocks.append(item.html);
      this.updatePlaceholders();
      this.updateOffsets();
      this.updateAllBlockCoordinates();
    }
  }, {
    key: "createItem",
    value: function createItem(data) {
      var _this4 = this;

      var item = null;

      if (this.type == 'portfolios') {
        item = new _sortable_card_js__WEBPACK_IMPORTED_MODULE_1__["Portfolio"](data);
      } else if (this.type == 'images') {
        item = new _sortable_card_js__WEBPACK_IMPORTED_MODULE_1__["Image"](data);
      }

      item.onLoadStart = function () {
        _this4.stageBlock.find('.portfolio-error').html('');
      };

      item.onError = function (text) {
        _this4.stageBlock.find('.portfolio-error').html(text);
      };

      item.onDragStart = function (e) {
        _this4.dragStart(e, item);
      };

      item.onEdit = function () {
        _this4.editItem(item);
      };

      item.onDelete = function () {
        _this4.deleteItem(item);

        if (_this4.type == 'images') {
          _this4.onDelete(item);
        }
      };

      item.onChangeState = function () {
        _this4.onChangeState();
      };

      item.onSuccess = function () {
        _this4.onSuccess(item);
      };

      return item;
    }
  }, {
    key: "deleteItem",
    value: function deleteItem(item) {
      item.html.remove();
      this.items.splice(this.items.indexOf(item), 1);
      this.stageBlock.find('.placeholder:last-child').remove();
      this.updatePlaceholders();
      this.updateOffsets();
      this.updateAllBlockCoordinates();
    }
  }, {
    key: "updateOffsets",
    value: function updateOffsets() {
      this.stageOffset = this.stageBlock.offset();
      this.stageWidth = this.stageBlock.outerWidth();
      this.stageHeight = this.stageBlock.outerHeight();
      var container = this.stageBlock.find('.placeholder');
      this.containerWidth = container.outerWidth();
      this.containerHeight = container.outerHeight();
      var colsCount = -1;

      if (this.sortable == false) {
        var _colsCount = 1;
      } else {
        for (var i = this.stageWidth + 30; i > 0; i -= 190 + 15) {
          colsCount++;
        }
      }

      this.stageBlock.attr('data-cols', colsCount);
      this.containerRowSize = colsCount;
    }
  }, {
    key: "getPositionByCoords",
    value: function getPositionByCoords(x, y) {
      var row = Math.round(y / this.containerHeight);
      var col = Math.round(x / (this.stageWidth / this.containerRowSize));
      var pos = row * this.containerRowSize + col;

      if (pos >= this.items.length) {
        pos = this.items.length - 1;
      }

      return pos;
    }
  }, {
    key: "updateAllBlockCoordinates",
    value: function updateAllBlockCoordinates() {
      var _this5 = this;

      $.each(this.items, function (k, v) {
        _this5.updateBlockCoordinates(v.html, k);
      });
      this.updateBlockCoordinates(this.stageBlock.find('.create-block'), this.items.length);
    }
  }, {
    key: "updateBlockCoordinates",
    value: function updateBlockCoordinates(el, newPosition) {
      var container = this.stageBlock.find('.placeholder:nth-child(' + (newPosition + 1) + ')');

      if (container.length < 1) {
        return;
      }

      var offset = container.offset();
      var offsetTop = offset.top - this.stageOffset.top;
      var offsetLeft = offset.left - this.stageOffset.left;
      el.css({
        'top': offsetTop,
        'left': offsetLeft
      });
    }
  }, {
    key: "dragStart",
    value: function dragStart(e, item) {
      if (!this.sortable || e.which != 1) {
        return;
      }

      this.updateOffsets();
      item.html.addClass('moved');
      var offset = item.html.offset();
      this.blockDragLeft = e.pageX - offset.left;
      this.blockDragTop = e.pageY - offset.top;
      this.draggedBlock = item;
      return false;
    }
  }, {
    key: "dragStop",
    value: function dragStop(e) {
      if (!this.draggedBlock) {
        return;
      }

      this.draggedBlock.html.removeClass('moved');
      this.updateAllBlockCoordinates();
      this.draggedBlock = null;
    }
  }, {
    key: "dragMove",
    value: function dragMove(e) {
      if (!this.draggedBlock) {
        return;
      }

      var top = e.pageY - this.stageOffset.top - this.blockDragTop;
      var left = e.pageX - this.stageOffset.left - this.blockDragLeft;
      var maxWidth = this.stageWidth - this.containerWidth;
      var maxHeight = this.stageHeight - this.containerHeight;

      if (left < 0) {
        left = 0;
      } else if (left > maxWidth) {
        left = maxWidth;
      }

      if (top < 0) {
        top = 0;
      } else if (top > maxHeight) {
        top = maxHeight;
      }

      var oldPos = this.items.indexOf(this.draggedBlock);
      var newPos = this.getPositionByCoords(left, top);

      if (newPos != oldPos) {
        this.moveElement(this.items, oldPos, newPos);
      }

      ;
      this.updateAllBlockCoordinates();
      this.draggedBlock.html.css({
        'left': left + 'px',
        'top': top + 'px'
      });
      return false;
    }
  }, {
    key: "moveElement",
    value: function moveElement(arr, old_index, new_index) {
      if (new_index >= arr.length) {
        var k = new_index - arr.length + 1;

        while (k--) {
          arr.push(undefined);
        }
      }

      arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
    }
  }, {
    key: "getData",
    value: function getData() {
      var data = [];
      $.each(this.items, function (k, v) {
        data.push(v.getData());
      });
      return data;
    }
  }, {
    key: "isReady",
    value: function isReady() {
      var ready = true;
      $.each(this.items, function (k, v) {
        if (v.uploadableBase64) {
          ready = false;
          return false;
        }
      });
      return ready;
    }
  }, {
    key: "applyErrors",
    value: function applyErrors(errors) {
      var itemListErrors = {};
      $.each(errors, function (k, v) {
        itemListErrors[parseInt(v.position)] = v.errors;
      });
      $.each(this.items, function (k, v) {
        var itemErrors = [];

        if (k in itemListErrors) {
          itemErrors = itemListErrors[k];
        }

        v.applyErrors(itemErrors);
      });
    }
  }, {
    key: "checkCoverHash",
    value: function checkCoverHash(hash) {
      var hashes = this.getCoverHashes();
      return $.inArray(hash, hashes) == -1;
    }
  }, {
    key: "checkImageHash",
    value: function checkImageHash(hash) {
      var hashes = this.getImagesHashes();
      return $.inArray(hash, hashes) == -1;
    }
  }, {
    key: "checkVideoUrl",
    value: function checkVideoUrl(url, pos) {
      var urls = this.getVideoUrls(pos);
      return $.inArray(url, urls) == -1;
    }
  }, {
    key: "getCoverHashes",
    value: function getCoverHashes() {
      var hashes = [];
      var activeWork = this.modal.portfolioItem;
      $.each(this.items, function (k, v) {
        if (v == activeWork) {
          return true;
        }

        if (v.data.cover.hash) {
          hashes.push(v.data.cover.hash);
        }
      });
      return hashes;
    }
  }, {
    key: "getImagesHashes",
    value: function getImagesHashes() {
      var hashes = [];
      var activeWork = this.modal.portfolioItem;
      var modalImages = this.modal.portfolio ? this.modal.portfolio.images : [];
      $.each(this.items, function (k, v) {
        if (v == activeWork) {
          return true;
        }

        $.each(v.data.images, function (k2, v2) {
          hashes.push(v2.hash);
        });
      });
      $.each(modalImages, function (k2, v2) {
        hashes.push(v2.hash);
      });
      return hashes;
    }
  }, {
    key: "getVideoUrls",
    value: function getVideoUrls(pos) {
      var urls = [];
      var activeWork = this.modal.portfolioItem;
      var modalVideos = this.modal.portfolio.videos;
      $.each(this.items, function (k, v) {
        if (v == activeWork) {
          return true;
        }

        $.each(v.data.videos, function (k2, v2) {
          urls.push(v2);
        });
      });
      $.each(modalVideos, function (k, v) {
        if (k == pos) {
          return true;
        }

        urls.push(v);
      });

      if (window.ordersPortfolioVideos) {
        $.each(window.ordersPortfolioVideos, function (k, v) {
          urls.push(v);
        });
      }

      return urls;
    }
  }]);

  return SortableCardList;
}();

/***/ }),

/***/ "./public_html/js/app/portfolio-upload/sortable-card.js":
/*!**************************************************************!*\
  !*** ./public_html/js/app/portfolio-upload/sortable-card.js ***!
  \**************************************************************/
/*! exports provided: Portfolio, Image */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Portfolio", function() { return Portfolio; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Image", function() { return Image; });
/* harmony import */ var appJs_file_uploader_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! appJs/file-uploader.js */ "./public_html/js/app/file-uploader.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var SortableCard =
/*#__PURE__*/
function () {
  function SortableCard() {
    var _this = this;

    _classCallCheck(this, SortableCard);

    this.addToListAfterLoad = true;
    this.uploadableBase64 = null;
    this.hasError = false;
    this.textError = '';
    this.html = $("<div class=\"draggable-block item\">\n\t\t\t<div class=\"clickable\">\n\t\t\t\t<div class=\"image draggable-anchor\">\n\t\t\t\t\t<div class=\"thumbnail-img-load\">\n\t\t\t\t\t\t<div class=\"ispinner-lite\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<img src=\"\" alt=\"\">\n\t\t\t\t\t<div class=\"progress\">\n\t\t\t\t\t\t<div></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"controls\">\n\t\t\t\t\t<a class=\"upload\">" + t('Изменить') + "</a>\n\t\t\t\t\t<a class=\"delete\">" + t('Удалить') + "</a>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"draggable-block_error\"></div>\n\t\t\t</div>\n\t\t</div>");
    this.html.find('.draggable-anchor').on('mousedown', function (e) {
      _this.onDragStart(e);

      return false;
    }); // Если картинка не кропнутая то определяем ее ориентацию для нормального отображения

    this.html.find('.image').find('img').on('load', function (e) {
      var $this = $(this);

      if ($this.hasClass('isNotCrop')) {
        if ($this.get(0).width > $this.get(0).height && $this.get(0).height / $this.get(0).width < 0.665) {
          $this.addClass('isHorizontalImg');
        }
      }
    });
  }

  _createClass(SortableCard, [{
    key: "updateImage",
    value: function updateImage() {
      var url = this.getImageSrc();

      if (!url) {
        return;
      }

      this.addImage(url);
    }
  }, {
    key: "addImage",
    value: function addImage(url) {
      // проверяем кропнутая картинка или нет (страница портфолио)
      if (this.data.urlBig && this.data.url && this.data.urlBig == this.data.url) {
        this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop');
      } // проверяем кропнутая картинка или нет (страница создание/редактирования кворка)
      else if (this.data.cover && this.data.cover.urlBig && this.data.cover.url && this.data.cover.urlBig == this.data.cover.url) {
          this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop');
        } // проверяем кропнутая картинка или нет (страница создание/редактирования кворка при первом добавлении изображения)
        else if (this.data.urlBig === null && this.data.urlBig === null) {
            this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop');
          }

      this.html.find('.image').addClass('uploaded').find('img').attr('src', url);
    }
  }, {
    key: "applyErrors",
    value: function applyErrors(errors) {
      var _this2 = this;

      this.hasError = errors.length > 0;
      this.backendErrors = {};
      var errorText = '';
      var errorVideoText = t('Видео требует исправлений!');
      var errorImageText = t('Исправьте ошибки в портфолио!');
      var errorVideo = false;
      var errorImage = false;
      $.each(errors, function (k, v) {
        if ('position' in v) {
          if (!(v.target in _this2.backendErrors)) {
            _this2.backendErrors[v.target] = {};
          }

          _this2.backendErrors[v.target][v.position] = v.text;
        } else {
          if (!(v.target in _this2.backendErrors)) {
            _this2.backendErrors[v.target] = [];
          }

          _this2.backendErrors[v.target].push(v.text);
        }

        if (v.target == 'images' || v.target == 'cover') {
          errorImage = true;
        }

        ;

        if (v.target == 'videos' || v.target == 'video') {
          errorVideo = true;
        }

        ;
      });

      if (window.portfolioType == 'photo') {
        if (errorImage) {
          errorText = '<div>' + errorImageText + '</div>';
        }

        if (errorVideo) {
          errorText += '<div>' + errorVideoText + '</div>';
        }
      } else if (window.portfolioType == 'video') {
        if (errorVideo) {
          errorText = '<div>' + errorVideoText + '</div>';
        }

        if (errorImage) {
          errorText += '<div>' + errorImageText + '</div>';
        }
      }

      this.updateErrorVisuals(errorText);
    }
  }, {
    key: "updateErrorVisuals",
    value: function updateErrorVisuals(errorText) {
      if (this.hasError) {
        this.html.addClass('error');
        this.html.find('.draggable-block_error').html(errorText);
      } else {
        this.html.removeClass('error');
        this.html.find('.draggable-block_error').html('');
      }
    }
  }, {
    key: "showProgress",
    value: function showProgress() {
      this.updateProgress(0);
      this.html.addClass('loading');
    }
  }, {
    key: "hideProgress",
    value: function hideProgress() {
      this.html.removeClass('loading');
    }
  }, {
    key: "updateProgress",
    value: function updateProgress(percent) {
      this.html.find('.progress div').css({
        'width': percent + '%'
      });
    }
  }]);

  return SortableCard;
}();

var Portfolio =
/*#__PURE__*/
function (_SortableCard) {
  _inherits(Portfolio, _SortableCard);

  function Portfolio(data) {
    var _this3;

    _classCallCheck(this, Portfolio);

    _this3 = _possibleConstructorReturn(this, _getPrototypeOf(Portfolio).call(this));
    _this3.blank = false;
    _this3.deleteModal = $('.portfolio-delete-modal-confirm');
    _this3.data = {
      id: null,
      draftHash: null,
      cover: {
        id: null,
        crop: null,
        hash: null,
        url: null
      },
      title: '',
      images: [],
      videos: [],
      description: ''
    };

    if (data) {
      $.extend(_this3.data, data);
      _this3.addToListAfterLoad = false;
    } else {
      _this3.blank = true;
    }

    _this3.backendErrors = {};

    _this3.updateImage();

    _this3.html.find('.upload').on('click', function () {
      _this3.onEdit();
    });

    _this3.html.find('.delete').on('click', function () {
      _this3.deleteModal.modal('show'); // Подтвердить удаление


      _this3.deleteModal.find('.js-confirm-portfolio-delete').off().on('click', function () {
        _this3.portfolioDelete();

        _this3.deleteModal.modal('hide');
      }); // Отмена удаления


      _this3.deleteModal.find('.js-continue-portfolio-delete').off().on('click', function () {
        _this3.deleteModal.modal('hide');
      });
    });

    return _this3;
  }

  _createClass(Portfolio, [{
    key: "portfolioDelete",
    value: function portfolioDelete() {
      if (window.portfolioList.modal.page != window.portfolioList.modal.PAGE_KWORK) {
        var formData = new FormData();
        formData.append('portfolio_id', this.data.id);
        formData.append('unlink', 'true');
        $.ajax({
          url: '/portfolio/delete',
          data: formData,
          async: true,
          contentType: false,
          processData: false,
          type: 'POST',
          complete: function complete(jXhr, status) {
            var rj = {};

            try {
              rj = JSON.parse(jXhr.responseText);
            } catch (e) {}

            if ('success' in rj && rj.success == true) {
              window.location = window.location.href;
            }
          }
        });
      } else {
        this.onDelete();
      }
    }
  }, {
    key: "getImageSrc",
    value: function getImageSrc() {
      if (this.data.cover && this.data.cover.url) {
        return this.data.cover.url;
      }

      return null;
    }
  }, {
    key: "parseBackendErrors",
    value: function parseBackendErrors(errors) {
      var _this4 = this;

      this.backendErrors = {};
      $.each(errors, function (k, v) {
        if (!v.target in _this4.backendErrors) _this4.backendErrors[v.target] = [];
        _this4.backendErrors[v.target] = v.text;
      });
    }
  }, {
    key: "getData",
    value: function getData(data) {
      var portfolioData = $.extend(true, {}, data || this.data);
      delete portfolioData.cover.url;
      delete portfolioData.cover.urlBig;
      delete portfolioData.cover.urlDefault;
      delete portfolioData.cover.hash;
      $.each(portfolioData.images, function (k, v) {
        portfolioData.images[k] = v.id;
      });
      return portfolioData;
    }
  }]);

  return Portfolio;
}(SortableCard);
var Image =
/*#__PURE__*/
function (_SortableCard2) {
  _inherits(Image, _SortableCard2);

  function Image(data) {
    var _this5;

    _classCallCheck(this, Image);

    _this5 = _possibleConstructorReturn(this, _getPrototypeOf(Image).call(this));
    _this5.data = {
      id: null,
      hash: null,
      url: null,
      urlBig: null
    };

    if (data) {
      $.extend(_this5.data, data);
      _this5.addToListAfterLoad = false;
    }

    _this5.updateImage();

    _this5.progress = _this5.html.find('.progress');
    _this5.progressBar = _this5.progress.find('div');
    _this5.fileUploader = new appJs_file_uploader_js__WEBPACK_IMPORTED_MODULE_0__["FileUploader"]();
    _this5.fileUploader.url = '/portfolio/upload_image';
    _this5.fileUploader.fileName = 'file';
    _this5.fileUploader.maxSize = 10485760;
    _this5.fileUploader.minImageWidth = 660;
    _this5.fileUploader.minImageHeight = 440;
    _this5.fileUploader.maxImageWidth = 6000;
    _this5.fileUploader.maxImageHeight = 6000;
    _this5.fileUploader.mimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (window.kworkId && window.portfolioList.modal.page === window.portfolioList.modal.PAGE_KWORK) {
      _this5.fileUploader.postData.kwork_id = window.kworkId;
    }

    var hashes = window.portfolioList.getImagesHashes().concat(window.portfolioList.getCoverHashes());

    if (window.portfolioList.modal.portfolio.cover.hash) {
      hashes.push(window.portfolioList.modal.portfolio.cover.hash);
    }

    if (window.firstPhotoHash) {
      hashes.push(window.firstPhotoHash);
    }

    if (hashes.length) {
      $.each(hashes, function (k, v) {
        _this5.fileUploader.postData['hashes[' + k + ']'] = v;
      });
    }

    var portfolioId = window.portfolioList.modal.portfolio.id;

    if (portfolioId) {
      _this5.fileUploader.postData.portfolio_id = portfolioId;
    }

    _this5.fileUploader.onLoad = function (base64) {
      _this5.uploadableBase64 = base64;

      _this5.onLoadStart();

      _this5.onChangeState();

      _this5.updateImage();

      _this5.showProgress();

      if (_this5.addToListAfterLoad) {
        _this5.onLoad();

        _this5.onChangeState();

        _this5.addToListAfterLoad = false;
      }
    };

    _this5.fileUploader.onProgress = function (percent) {
      _this5.updateProgress(percent);
    };

    _this5.fileUploader.onSuccess = function (data) {
      _this5.data.id = data.id;
      _this5.data.url = _this5.uploadableBase64;
      _this5.data.urlBig = _this5.uploadableBase64;
      _this5.data.hash = data.hash;
      _this5.uploadableBase64 = null;

      _this5.onChangeState();

      _this5.hideProgress();

      _this5.onSuccess();
    };

    _this5.fileUploader.onError = function (text) {
      if (!_this5.addToListAfterLoad && !_this5.data.url) {
        _this5.onDelete();

        _this5.onChangeState();

        _this5.onError(text);

        return;
      }

      _this5.uploadableBase64 = null;

      _this5.onChangeState();

      _this5.updateImage();

      _this5.hideProgress();

      _this5.onError(text);
    };

    _this5.fileUploader.onFail = function (text) {
      _this5.fileUploader.onError(text);
    };

    _this5.html.find('.upload').on('click', function () {
      _this5.fileUploader.upload();
    });

    _this5.html.find('.delete').on('click', function () {
      _this5.fileUploader.abort();

      _this5.onDelete();
    });

    return _this5;
  }

  _createClass(Image, [{
    key: "getImageSrc",
    value: function getImageSrc() {
      if (this.uploadableBase64) {
        return this.uploadableBase64;
      } else if (this.data.url) {
        return this.data.url;
      }

      return null;
    }
  }]);

  return Image;
}(SortableCard);

/***/ }),

/***/ "./public_html/js/pages/kwork-view/bootstrap.js":
/*!******************************************************!*\
  !*** ./public_html/js/pages/kwork-view/bootstrap.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// Редактирование портфолио через ajax
__webpack_require__(/*! appJs/portfolio-upload/ajax-portfolio-modal.js */ "./public_html/js/app/portfolio-upload/ajax-portfolio-modal.js");

/***/ }),

/***/ 1:
/*!************************************************************!*\
  !*** multi ./public_html/js/pages/kwork-view/bootstrap.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\pages\kwork-view\bootstrap.js */"./public_html/js/pages/kwork-view/bootstrap.js");


/***/ })

/******/ });