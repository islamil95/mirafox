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
/******/ 	return __webpack_require__(__webpack_require__.s = 22);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./public_html/js/components/file-uploader.js":
/*!****************************************************!*\
  !*** ./public_html/js/components/file-uploader.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 *
 * @param o {{
 *              files: [],
 *              isMultiple: {Bool},
 *              lang: {
 *                  withFiles: {String},
 *                  withoutFiles: {String},
 *                  hint: {String}
 *              },
 *              input: {
 *                  name: {String},
 *
 *              },
 *              selector: {string}
 *          }}
 * @returns {{}}
 * @constructor
 */
window.FileUploader = function (o) {
  "use strict";

  var $ = window.jQuery;
  var _options = {
    lang: {
      withFiles: t('Прикрепить ещё файл'),
      withoutFiles: t('Прикрепить файл')
    },
    errors: {
      "default": t('Ошибка загрузки изображения'),
      file_size_exceed: t("Файл больше {{0}}Мб не отправится. Используйте файлообменник, например, <a href=\"https://disk.yandex.ru/\" target=\"_blank\">Яндекс.Диск</a>.", [config.files.maxSize]),
      no_file_uploaded: t('Не загружен файл'),
      not_allowed: t('Тип файла недопустим для загрузки'),
      user_non_authorized: t('Пользователь не авторизирован'),
      invalid_filename: t('Название файла должно быть на английском')
    },
    isMultiple: true,
    maxFiles: config.files.maxCount,
    maxSize: config.files.maxSize
  };
  var __blocks = {};
  var __totalFilesCount = 0;
  var __fileBlocks = [];

  var __self;

  var __allowUpload = true;

  this.__construct = function (o) {
    $.extend(_options, o);
    $.extend(_options.lang, {
      hint: t('до {{0}} файлов не более {{1}} Мб', [_options.maxFiles, _options.maxSize])
    });
    __self = this;
    __blocks.container = $(_options.selector);

    var allowUpload = __blocks.container.data('allowUpload');

    if (typeof allowUpload !== 'undefined') {
      __allowUpload = !!allowUpload;
    }

    __setFileInput();

    __setFileListBlock();

    __setAvailFiles();

    if (typeof _options.files !== 'undefined' && _options.files.length) {
      __setAddButtonLabel(_options.lang.withFiles);
    } else {
      __setAddButtonLabel(_options.lang.withoutFiles);
    }

    this.__setEvents();
  };
  /*
  *	@param  {array} files - список файлов
  *	@param  {array} isNew - файл счиать как новыми(влияеть на тип удаления, удалять сразу из списка или только после отправки)
  */


  var __update = function __update(files) {
    var isNew = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    _options['files'] = files;

    __setFileBlocks(isNew);

    if (typeof _options.files !== 'undefined' && _options.files.length) {
      __setAddButtonLabel(_options.lang.withFiles);
    } else {
      __setAddButtonLabel(_options.lang.withoutFiles);
    }
  };

  var __maxFileCount = function __maxFileCount() {
    if (_options.hasOwnProperty('isMultiple') && _options.isMultiple === false) {
      return 1;
    }

    return _options.maxFiles ^ 0;
  };

  this.__maxFileCount = __maxFileCount;

  var __totalFiles = function __totalFiles(action) {
    if (isNaN(__totalFilesCount)) {
      __totalFilesCount = 0;
    }

    if (action === 'inc') {
      __totalFilesCount++;

      if (__totalFilesCount >= __maxFileCount()) {
        __blocks.fileAddButton.hide();
      }

      $(window).trigger('changeFileCount.fileUploader');
      return __totalFilesCount;
    } else if (action === 'dec') {
      __totalFilesCount--;

      if (__totalFilesCount < __maxFileCount()) {
        __blocks.fileAddButton.show();
      }

      $(window).trigger('changeFileCount.fileUploader');
      __totalFilesCount = __totalFilesCount < 0 ? 0 : __totalFilesCount;
      return __totalFilesCount;
    }

    return __totalFilesCount;
  };

  this.__totalFiles = __totalFiles;

  var __setFileInput = function __setFileInput() {
    var $fileInput = $('<input type="file" class="file-uploader__file-input">');

    if (_options.isMultiple) {
      $fileInput.prop('multiple', true);
    }

    __blocks.fileInput = $fileInput;
    var $fileInputContainer = $('<label class="file-uploader__add d-flex_kwork-edit flex-wrap justify-content-between align-items-baseline">');
    var $fileAddButtonContainer = $('<div class="mr5">');
    $fileAddButtonContainer.append(__blocks.fileInput);
    $fileInputContainer.append($fileAddButtonContainer);
    $fileAddButtonContainer.append('<i class="icon ico-clip file-uploader__add-ico"></i>');
    var $fileAddButtonLabel = $('<span class="file-uploader__add-text"></span>');
    $fileAddButtonContainer.append($fileAddButtonLabel);

    if (_options.lang.hasOwnProperty('hint')) {
      var $hint = $('<span class="file-uploader__add-hint">' + _options.lang.hint + '</span>');
      $fileInputContainer.append($hint);
    }

    __blocks.fileAddButton = $fileInputContainer;
    __blocks.fileAddButtonLabel = $fileAddButtonLabel;

    __blocks.container.append($fileInputContainer);

    if (!__allowUpload) {
      __blocks.fileAddButton.hide();
    }
  };

  var __setFileListBlock = function __setFileListBlock() {
    __blocks.loadFileList = $('<div class="load-file__list">');

    __blocks.container.append(__blocks.loadFileList);
  };

  this.__setEvents = function () {
    var self = this;

    __blocks.fileInput.on('change', function () {
      __setAddButtonLabel(_options.lang.withFiles);

      for (var i = 0; i < this.files.length; i++) {
        var totalFiles = __totalFiles();

        var maxFileCount = __maxFileCount();

        if (totalFiles >= maxFileCount) {
          break;
        }

        if (this.files[i]) {
          var fileBlock = new FileUploaderItem(self, this.files[i], _options, true);

          __blocks.loadFileList.append(fileBlock.get());

          __totalFiles('inc');

          fileBlock.upload();

          __fileBlocks.push(fileBlock);
        }
      }

      __blocks.container.trigger('fileUploader.change');

      $(this).val('');

      if (!self.__canSave()) {
        var interval = setInterval(function () {
          if (self.__canSave()) {
            clearInterval(interval);

            self.__enableButton();

            fileBlock.onChangeFile();
          }
        }, 100);

        self.__disableButton();

        fileBlock.onChangeFile();
        return false;
      }

      return false;
    });
  };

  var __setAvailFiles = function __setAvailFiles() {
    if (typeof _options.files == 'undefined' || !_options.files.length) {
      return;
    }

    for (var i = 0; i < _options.files.length; i++) {
      var file = _options.files[i];

      if (file) {
        var fileBlock = new FileUploaderItem(__self, file, _options);

        __blocks.loadFileList.append(fileBlock.get());

        __totalFiles('inc');
      }
    }
  };
  /*
   * Вариация функции _setAvailFiles заполняем еще и массив __fileBlocks 
   * (для корректной работы если у нас есть прикрепленные файлы)
  */


  var __setFileBlocks = function __setFileBlocks() {
    var isNew = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

    if (typeof _options.files == 'undefined' || !_options.files.length) {
      return;
    }

    for (var i = 0; i < _options.files.length; i++) {
      var file = _options.files[i];

      if (file) {
        var fileBlock = new FileUploaderItem(__self, file, _options, isNew);

        __blocks.loadFileList.append(fileBlock.get());

        __totalFiles('inc');

        __fileBlocks.push(fileBlock);
      }
    }
  };

  var __setAddButtonLabel = function __setAddButtonLabel(text) {
    __blocks.fileAddButtonLabel.text(text);
  };

  this.__construct(o);

  this.__canSave = function () {
    for (var i = 0; i < __fileBlocks.length; i++) {
      var fBlock = __fileBlocks[i];

      if (fBlock.status() == 'load' || fBlock.checkError()) {
        return false;
      }
    }

    return true;
  };

  this.__hasErrors = function () {
    for (var i = 0; i < __fileBlocks.length; i++) {
      var fBlock = __fileBlocks[i];

      if (fBlock.checkError()) {
        return true;
      }
    }

    return false;
  };

  this.__canUpload = function (canUpload) {
    if (typeof canUpload !== 'undefined') {
      __allowUpload = canUpload;

      __blocks.fileAddButton.toggle(__allowUpload);
    }

    return __allowUpload;
  };

  this.__clear = function () {
    var keys = Object.keys(__fileBlocks);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];

      __fileBlocks[key].destroy();

      delete __fileBlocks[key];
    }

    __fileBlocks = [];

    __setAddButtonLabel(_options.lang.withoutFiles);

    return true;
  };

  this.__removeFileBlock = function (fileBlockName) {
    __fileBlocks = __fileBlocks.filter(function (e) {
      return e.getFileName() != fileBlockName;
    });
  };

  this.__getOptions = function () {
    return _options;
  };
  /**
   * Блокировать кнопку отправки формы
   * @private
   */


  this.__disableButton = function () {
    if (typeof _options.buttonDisabled !== 'undefined') {
      $(_options.buttonDisabled).prop('disabled', true).attr('disabled', true).addClass('disabled btn_no-hover');
    }
  };
  /**
   * Разблокировать кнопку отправки формы
   * @private
   */


  this.__enableButton = function () {
    if (typeof _options.buttonDisabled !== 'undefined') {
      $(_options.buttonDisabled).prop('disabled', false).attr('disabled', false).removeClass('disabled btn_no-hover');
    }
  };

  return {
    totalFiles: __totalFiles,
    hasErrors: this.__hasErrors,
    canSave: this.__canSave,
    canUpload: this.__canUpload,
    clear: this.__clear,
    update: __update
  };
};

function FileUploaderItem(uploadFilesObject, file, options, isNew) {
  var $ = window.jQuery;
  var API_URL = "/api/file/upload";
  var _file = file;
  var _options = options;
  var _fileBlock = {};

  var _xhr;

  var _status;

  var _isNew = isNew === true;

  var _maxSizeReal;

  var __construct = function __construct() {
    _generateFileInfoBlock();

    _maxSizeReal = _options.maxSize * 1048576;
  };

  var _generateFileInfoBlock = function _generateFileInfoBlock() {
    var $fileLine = $('<div class="file-item file-uploader__item">');

    var fileIco = _getFileIco(_file.name);

    $fileLine.append('<i class="file-item__ext ' + fileIco + '">');
    var $fileName = $('<span class="file-item__name">').text(_file.name);
    $fileLine.append($fileName);
    var $deleteButton = $('<a class="file-item__action">');
    $deleteButton.append('<i class="file-item__action-ico">');
    $fileLine.append($deleteButton);
    var $error = $('<div class="file-item__error hidden">');
    $fileLine.append($error);
    var $loader = $('<div class="file-item__loader" style="width: 0">');
    $fileLine.append($loader);
    var $delInput = $('<input type="hidden" name="' + _options.input.name + '[delete][]">');
    var $newInput = $('<input type="hidden" name="' + _options.input.name + '[new][]">');
    _fileBlock = {
      $container: $fileLine,
      $name: $fileName,
      $actionBtn: $deleteButton,
      $deleteInput: $delInput,
      $newInput: $newInput,
      $error: $error,
      $loader: $loader
    };

    _fileBlock.$actionBtn.on('click', function () {
      if (_isNew) {
        if (!_isType('error')) {
          uploadFilesObject.__totalFiles('dec');
        }

        _hideBlock(_isNew);

        if (typeof _xhr !== 'undefined' && _xhr.status == 0) {
          _xhr.abort();
        }

        uploadFilesObject.__removeFileBlock(_file.name);
      } else {
        if (_fileBlock.$container.data('onDelete')) {
          if (uploadFilesObject.__totalFiles() < uploadFilesObject.__maxFileCount()) {
            uploadFilesObject.__totalFiles('inc');

            _unmarkDelete();
          }
        } else {
          var options = uploadFilesObject.__getOptions();

          if (options.deleteCallback) {
            var onSuccessCallback = function onSuccessCallback() {
              _destroy();
            };

            options.deleteCallback(_file, onSuccessCallback);
          } else {
            uploadFilesObject.__totalFiles('dec');

            _markDelete();
          }
        }

        $(_options.selector).trigger('fileUploader.change');
      }

      _onChangeFile();
    });
  };

  var _uploadFile = function _uploadFile() {
    if (!_checkMaxSize()) {
      _setType('error');

      _showError('file_size_exceed');

      uploadFilesObject.__totalFiles('dec');

      return false;
    }

    _xhr = new XMLHttpRequest();
    var formData = new FormData();
    formData.append("upload_files", _file);

    if (typeof kworkLang !== 'undefined') {
      formData.append("lang", kworkLang);
    }

    _xhr.addEventListener("load", _uploadSuccessEvent);

    _xhr.addEventListener("error", _uploadErrorEvent);

    _xhr.addEventListener("abort", _uploadAbortEvent);

    _xhr.upload.onprogress = _uploadProgressEvent;

    _xhr.open("POST", API_URL, true);

    _setStatus('load');

    _xhr.send(formData);
  };

  var _uploadSuccessEvent = function _uploadSuccessEvent() {
    _fileBlock.$loader.addClass('file-item__loader_success').animate({
      opacity: 0
    });

    _fileBlock.$container.data('uploaded', true);

    try {
      var response = JSON.parse(this.responseText);
    } catch (e) {
      response = {
        result: 'error'
      };
    }

    if (response.result === 'success') {
      _addFileIdInput(response.file_id);
    } else {
      _setType('error');

      _showError(response.reason);

      uploadFilesObject.__totalFiles('dec');

      _setStatus('success');
    }
  };

  var _uploadErrorEvent = function _uploadErrorEvent() {
    _setType('error');

    _showError('default');

    uploadFilesObject.__totalFiles('dec');
  };

  var _uploadProgressEvent = function _uploadProgressEvent(e) {
    var widthPercent = e.loaded / e.total * 100 ^ 0;

    _fileBlock.$loader.css('width', widthPercent + '%');
  };

  var _uploadAbortEvent = function _uploadAbortEvent() {
    _setStatus('abort');
  };

  var _addFileIdInput = function _addFileIdInput(id) {
    id = id ^ 0;

    _fileBlock.$newInput.val(id);

    _fileBlock.$container.append(_fileBlock.$newInput);

    _setStatus('success');
  };

  var _getFileIco = function _getFileIco(fileName) {
    var len = fileName.length;
    var symb3 = fileName.substr(len - 3, len).toLowerCase();
    var symb4 = fileName.substr(len - 4, len).toLowerCase();
    var ico = '';

    if ($.inArray(symb3, ['doc', 'xls', 'rtf', 'txt']) != -1 || $.inArray(symb4, ['docx', 'xlsx']) != -1) {
      ico = 'ico-file-doc';
    } else if ($.inArray(symb3, ['zip', 'rar']) != -1) {
      ico = 'ico-file-zip';
    } else if ($.inArray(symb3, ['png', 'jpg', 'gif', 'psd']) != -1 || $.inArray(symb4, ['jpeg']) != -1) {
      ico = 'ico-file-image';
    } else if ($.inArray(symb3, ['mp3', 'wav', 'avi']) != -1) {
      ico = 'ico-file-audio';
    } else {
      ico = 'ico-file-zip';
    }

    return ico;
  };

  var _checkMaxSize = function _checkMaxSize() {
    return _file.size <= _maxSizeReal;
  };

  var _get = function _get() {
    return _fileBlock.$container;
  };

  var _getFileName = function _getFileName() {
    return _file.name;
  };

  var _hideBlock = function _hideBlock(isNew) {
    _fileBlock.$container.animate({
      opacity: 0,
      height: 0,
      margin: 0
    }, 200, function () {
      if (isNew) {
        _fileBlock.$container.remove();

        _fileBlock = null;
      }

      $(_options.selector).trigger('fileUploader.change');
    });
  };

  var _setType = function _setType(type) {
    switch (type) {
      case 'deleted':
        _fileBlock.$container.addClass('file-item_type_deleted');

        break;

      case 'error':
        _fileBlock.$container.addClass('file-item_type_error');

        break;
    }
  };

  var _removeType = function _removeType(type) {
    switch (type) {
      case 'deleted':
        _fileBlock.$container.removeClass('file-item_type_deleted');

        break;

      case 'error':
        _fileBlock.$container.removeClass('file-item_type_error');

        break;
    }
  };

  var _isType = function _isType(type) {
    switch (type) {
      case 'deleted':
        var isType = _fileBlock.$container.hasClass('file-item_type_deleted');

        break;

      case 'error':
        var isType = _fileBlock.$container.hasClass('file-item_type_error');

        break;
    }

    return isType;
  };

  var _showError = function _showError(reason) {
    var text = _options.errors["default"];

    if (_options.errors[reason]) {
      text = _options.errors[reason];
    }

    _fileBlock.$error.html(text).removeClass('hidden');
  };

  var _hideError = function _hideError() {
    _fileBlock.$error.html('').addClass('hidden');
  };

  var _checkError = function _checkError() {
    return _fileBlock.$container.hasClass('file-item_type_error');
  };

  var _markDelete = function _markDelete() {
    if (_file.id) {
      _setType('deleted');

      _fileBlock.$container.append(_fileBlock.$deleteInput);

      _fileBlock.$deleteInput.val(_file.id);

      _fileBlock.$container.data('onDelete', true);
    }
  };

  var _unmarkDelete = function _unmarkDelete() {
    _removeType('deleted');

    _fileBlock.$deleteInput.detach();

    _fileBlock.$container.data('onDelete', false);
  };

  var _setStatus = function _setStatus(status) {
    _status = status;
  };

  var _getStatus = function _getStatus() {
    return _status;
  };

  var _destroy = function _destroy() {
    if (_fileBlock === null) {
      return;
    } else {
      _fileBlock.$container.remove();

      uploadFilesObject.__totalFiles('dec');
    }
  };
  /**
   * Вызов дейсвия после изменения файла (загрузки, удаления)
   * @private
   */


  var _onChangeFile = function _onChangeFile() {
    if (_options.onChange) {
      _options.onChange();
    }
  };

  __construct();

  return {
    upload: _uploadFile,
    get: _get,
    getFileName: _getFileName,
    status: _getStatus,
    destroy: _destroy,
    hideError: _hideError,
    checkError: _checkError,
    onChangeFile: _onChangeFile
  };
}

/***/ }),

/***/ 22:
/*!**********************************************************!*\
  !*** multi ./public_html/js/components/file-uploader.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! D:\server\OpenServer\domains\mr\public_html\js\components\file-uploader.js */"./public_html/js/components/file-uploader.js");


/***/ })

/******/ });