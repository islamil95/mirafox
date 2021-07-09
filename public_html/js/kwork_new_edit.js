function KworkPhotoModule() {
	var MAX_PHOTO_SIZE = 10485760,
		MIN_PHOTO_WIDTH = 660,
		MIN_PHOTO_HEIGHT = 440,
		MAX_PHOTO_WIDTH = 6000,
		MAX_PHOTO_HEIGHT = 6000,
		WIDTH_RATIO = 3,
		HEIGHT_RATIO = 2,
		OLD_ID_DEFAULT_NAME = 'old-photo[]';

	var $_addPhotoBlock = {},
		$_resizeImage = {},
		$_photoBlocks = [],
		_photoCount = 0,
		_options = {
			maxCount: 0
		},
		self,
		ias;

	var classes = {
		resizeImage: 'js-photo-resize__image',
		resizeText: 'js-photo-resize__text',
		thumbImage: 'thumb-img',
		photoSizeInput: 'js-photo-size',
		deleteLink: 'js-delete',
		fileInput: 'js-file-input',
		deletePhotoInput: 'js-delete-photo',
		oldPhotoIdInput: 'js-old-photo-id',
		idPhotoInput: 'js-portfolio-image-id',
		changedPhotoInput: 'js-portfolio-image-changed',
		portfolioImageId: 'js-portfolio-image-id'
	};

	/**
	 * Возможные загружаемые файлы
	 * @type Array
	 */
	var avaibleExtensionsFirstPhoto = [
		"jpeg",
		"jpg",
		"png",
	];
	var avaibleFormatsFirstPhoto = [
		"jpg",
		"jpeg",
		"png",
	];
	var avaibleExtensionsPortfolio = [
		"jpeg",
		"jpg",
		"png",
	];
	var avaibleFormatsPortfolio = [
		"jpg",
		"png",
	];

	var _setBlockEvents = function ($block) {
		$block.off('click', '.js-file-add-button').on('click', '.js-file-add-button', function () {
			$block.find('.' + classes.fileInput).trigger('click');
		});

		$block.off('change', '.' + classes.fileInput).on('change', '.' + classes.fileInput, {'block': $block}, _fileSelectEvent);

		$block.off('click', '.js-delete').on('click', '.js-delete', {'block': $block}, function (e) {
			self.clearImage(e.data.block);
		});
	};

	/**
	 * Проверка на расширение
	 * @param {string} filename
	 * @param {string} blockType
	 * @returns {Boolean}
	 */
	var _fileCheckExtension = function (filename, blockType) {
		var ext = filename.split(".");
		ext = ext[ext.length-1].toLowerCase();

		var avaibleExtensions;
		if (blockType === 'first-photo') {
			avaibleExtensions = avaibleExtensionsFirstPhoto;
		}
		if (blockType === 'portfolio') {
			avaibleExtensions = avaibleExtensionsPortfolio;
		}

		if (!avaibleExtensions || !in_array(ext, avaibleExtensions)) {
			return false;
		}

		return true;
	};

	/**
	 * Получение допустимых форматов изображений по типу блока
	 * @param {string} blockType
	 * @returns {Array}
	 */
	var _getAvaiblePhotoFormats = function (blockType) {
		if (blockType === 'first-photo') {
			return avaibleFormatsFirstPhoto;
		}
		if (blockType === 'portfolio') {
			return avaibleFormatsPortfolio;
		}
		return [];
	};

	var _fileSelectEvent = function (e) {
		var files = e.target.files;
		var uploaded = false;

		$(e.target).closest('.js-file-wrapper-block-container').find('.add-photo__progress').hide();

		var blockType;
		if ($(e.target).closest('.js-first-photo-block').length) {
			blockType = 'first-photo';
		} else if ($(e.target).closest('.js-portfolio-field').length || $(e.target).closest('.js-photo__block').length) {
			blockType = 'portfolio';
		}

		for (var i = 0, file; file = files[i]; i++) {
			var targetBlock = e.data.block;
			if (!file.type.match('image.*')) {
				continue;
			}

			if (!_checkFileSize(file.size)) {
				self.clearImage(e.data.block);
				_setError(t('Размер файла не должен превышать 10 МБ'), e.data.block);
				continue;
			}

			if (!_fileCheckExtension(file.name, blockType)) {
				self.clearImage(e.data.block);
				var avaiblePhotoFormats = _getAvaiblePhotoFormats(blockType);
				_setError('Неверный формат изображения. Поддерживаемые форматы: ' + avaiblePhotoFormats.join(", "), e.data.block);
				continue;
			}

			(function (file, e, uploaded) {
				var element = e;
				if (files[0]) { // проверяем на реальный тип файла (багфикс для gif изображений, которым поменяли расширение)
					var fileReader = new FileReader(),
						blob = files[0];
						
					fileReader.onloadend = function (e) {
						var arr = (new Uint8Array(e.target.result)).subarray(0, 4),
							header = "";
						for (var i = 0; i < arr.length; i++) {
							header += arr[i].toString(16);
						}

						if (header == '47494638') { // это символьный номер любого gif файла
							var avaiblePhotoFormats = _getAvaiblePhotoFormats(blockType);
							self.clearImage(targetBlock);
							_setError('Неверный формат изображения. Поддерживаемые форматы: ' + avaiblePhotoFormats.join(", "), targetBlock);
							$('.js-save-kwork').addClass('disabled');
							$('.js-file-add-button').removeAttr('style');
						} else {
							_readPhotoForPreview(file, element.data.block, function () {
								if (!uploaded && $(element.target).data('pre-upload')) {
									uploaded = true;
									var uploadType = $(element.target).data('pre-upload-type');
									_uploadAndCheckImage(uploadType, element.target, file, element.data.block);
								}
							});
							$('.js-save-kwork').removeClass('disabled');
						}
					};
					fileReader.readAsArrayBuffer(blob);
				}
			})(file, e, uploaded);
		}
	};

	var _uploadAndCheckImage = function (uploadType, input, file, block) {
		var $input = $(input);

		var $buttonSave;
		if (uploadType === 'kwork-first-photo') {
			$buttonSave = $input.closest('.kwork-save-step').find('.js-next-step-btn');
		} else if (uploadType === 'kwork-portfolio') {
			$buttonSave = $input.closest('.modal-dialog').find('.js-save-popup');
		}

        if ($buttonSave) {
            $buttonSave.prop('disabled', true).addClass('btn_disabled');
        }

		var uploadImageData = _collectUploadImageData(uploadType, input, file);

		var xhr = new XMLHttpRequest();

		var progressBar = _showUploadImageProgressBar(uploadType, input, xhr);

		xhr.addEventListener('load', function () {
			try {
				var data = JSON.parse(xhr.responseText);
				window.firstPhoto = xhr.responseText;
				window.firstPhotoHash = JSON.parse(window.firstPhoto).data.hash;
			} catch (e) {
				console.error('Некорректный ответ json');
				progressBar.error();
				return;
			}

			if (!data.success) {
				progressBar.ready();
				self.clearImage(block);
				_setError(data.data[0], block);
				return;
			}

			uploadImageData.ready(data);
			progressBar.ready();
		});

		xhr.addEventListener('error', progressBar.error);
		xhr.addEventListener('abort', progressBar.error);
		xhr.addEventListener('loadend', function () {
			if($buttonSave) {
                $buttonSave.prop('disabled', false).removeClass('btn_disabled');
			}
		});

		var method;
		if (uploadType === 'kwork-first-photo') {
			method = '/temp-image-upload';
		} else if (uploadType === 'kwork-portfolio' || uploadType === 'track-portfolio') {
			method = '/api/kwork/saveportfolioimage';
		} else if (uploadType === 'with-progressbar') {
            method = '/temp-image-upload';
		}

		xhr.open('POST', method);
		xhr.send(uploadImageData.formData);
	};

	var _collectUploadImageData = function (uploadType, input, file) {
		var $input = $(input);

		var formData = new FormData();
		var onReady = $.noop;

		if (uploadType === 'kwork-first-photo' || uploadType === 'kwork-portfolio') {
			var isDraft;
			if (typeof window.kworkId !== 'undefined') {
				isDraft = false;
				formData.append('kwork_id', window.kworkId);
			} else {
				isDraft = true;
				formData.append('kwork_id', window.draftId || null);
				var categoryId = $('.js-category_sub').val();
				formData.append('category_id', categoryId);
			}
		}

		if (uploadType === 'kwork-first-photo') {

			var $imagePath = $input.siblings('.js-kwork-photo-path-input').removeAttr('name');
			formData.append('validator', 'KworkCover');

			var hashes = window.portfolioList.getImagesHashes().concat(window.portfolioList.getCoverHashes());
			$.each(hashes, (k, v) => {
				formData.append('hashes[' + k + ']', v);
			});

			onReady = function (data) {
				if (isDraft) {
					window.draftId = data.kwork_id;
				}
				$imagePath.prop('name', 'first_photo_path').val(data.image_path);
				$input.data('hash', data.image_hash).attr('data-hash', data.image_hash);
			};

		} else if (uploadType === 'kwork-portfolio') {

			var portfolioPosition = $input.data('position');
			formData.append('portfolio_position', portfolioPosition);

			var $portfolioWrapper = $('.js-portfolio-item-wrapper[data-position="' + portfolioPosition + '"]');
			var $portfolioId = $portfolioWrapper.find('input[name="portfolio[' + portfolioPosition + '][id]"]');
			formData.append('portfolio_id', $portfolioId.val() || null);

			var $imageWrapper = $input.closest('.file-wrapper');
			var $imageId = $imageWrapper.find('.' + classes.idPhotoInput);

			var $photoBlock = $input.closest('.js-photo__block');
			var $imageWrappers = $photoBlock.find('.file-wrapper');
			var imagePosition = $($imageWrappers).index($imageWrapper) + 1;
			formData.append('image_position', imagePosition);

			onReady = function (data) {
				if (isDraft) {
					window.draftId = data.kwork_id;
				}
				$portfolioId.val(data.portfolio_id);
				$imageId.val(data.image_id);
				$imageWrapper.find('.' + classes.changedPhotoInput).val(1);
				$input.data('hash', data.image_hash).attr('data-hash', data.image_hash);
			};

		}

		$('input.' + classes.fileInput).each(function(i, otherInput) {
			if (otherInput === input) return;
			var hash = $(otherInput).data('hash')
			if (typeof hash === 'string' && hash.length) {
				formData.append('hashes[]', hash);
			}
		});

		formData.append('file', file);

		return {
			formData: formData,
			ready: onReady,
		};
	};

	var _showUploadImageProgressBar = function (uploadType, input, xhr) {
		var $input = $(input);

		var onReady = $.noop;

		if (uploadType === 'kwork-first-photo') {

			var $imageContainer = $input.closest('.js-add-photo').find('.js-kwork-preview-resize');
			var $progressBar = _showPortfolioImageProgressBar($imageContainer, 'first-photo');

			onReady = function () {
				$progressBar.empty().hide();
			};

		} else if (uploadType === 'kwork-portfolio') {

			var $imageContainer = $input.closest('.js-file-wrapper-block-container');
			var $imageRectangle = $imageContainer.find('.file-wrapper-block-rectangle').css('background', '#000');
			var $imageThumb = $imageRectangle.find('.' + classes.thumbImage).css('opacity', 0.4);
			var $progressBar = _showPortfolioImageProgressBar($imageContainer, 'portfolio');

			onReady = function () {
				$imageThumb.css('opacity', '');
				$progressBar.empty().hide();
			};

		} else if (uploadType === 'track-portfolio') {

			var $imageContainer = $input.closest('.js-file-wrapper-block-container');
			var $imageRectangle = $imageContainer.find('.file-wrapper-block-rectangle').css('background', '#000');
			var $imageThumb = $imageRectangle.find('.' + classes.thumbImage).css('opacity', 0.4);
			var $progressBar = _showPortfolioImageProgressBar($imageContainer, 'portfolio');

			onReady = function () {
				$imageThumb.css('opacity', '');
				$progressBar.empty().hide();
			};
		} else if (uploadType === 'with-progressbar') {
            var $imageContainer = $input.closest('.js-file-wrapper-block-container');
            var $progressBar = _showPortfolioImageProgressBar($imageContainer, 'settings-page');

            onReady = function () {
                $progressBar.empty().hide();
            };

		}

		xhr.upload.onprogress = function (e) {
			var percentage = (e.loaded / e.total * 100) ^ 0;
			$progressBar.progressTo(percentage);
		};

		var onError = function () {
			_showPortfolioImageProgressBarError($progressBar);
		};

		return {
			ready: onReady,
			error: onError,
		};
	};

	var _showPortfolioImageProgressBar = function ($parent, type) {
		var height = 10;

		var stylePortfolio = {
			position: 'absolute',
			left: 1,
			right: 1,
			height: height,
			bottom: 1,
			borderRadius: '0 1px 1px 0',
			overflow: 'hidden',
		};

        var styleSettings = {
            position: 'absolute',
            left: 1,
            right: 1,
            height: height,
            bottom: 1,
            borderRadius: '0 1px 1px 0',
            overflow: 'hidden',
            'z-index': 1,
        };

		var styleFirstPhoto = {
			position: 'absolute',
			left: 0,
			right: 0,
			height: height,
			bottom: 0,
			overflow: 'hidden',
		};

		var style =
			type === 'portfolio' ? stylePortfolio :
			type === 'first-photo' ? styleFirstPhoto :
			type === 'settings-page' ? styleSettings :
			{};

		var $progressBar = $parent.children('.add-photo__progress');

		if ($progressBar.length) {
			$progressBar.empty().show();
		} else {
			$progressBar = $('<div class="add-photo__progress" />')
				.data('progressBarHeight', height)
				.css(style)
				.appendTo($parent);
		}

		$progressBar.LineProgressbar({
			height: height,
			percentage: 0,
			duration: 100,
			ShowProgressCount: false,
			fillBackgroundColor: '#1abc9c',
		});

		return $progressBar;
	};

	var _showPortfolioImageProgressBarError = function ($progressBar) {
		var height = $progressBar.data('progressBarHeight');

		$progressBar.LineProgressbar({
			height: height,
			percentage: 100,
			duration: 0,
			ShowProgressCount: false,
			fillBackgroundColor: '#cc0000',
		});
	};

	var _readPhotoForPreview = function (f, $block, callback) {
		var reader = new FileReader();

		reader.onload = (function () {
			return function (e) {
				var image = new Image();
				image.src = e.target.result;
				_setError('', $block);

				image.onload = function () {
					if (!_checkMinImageResolution(image) || !_checkMaxImageResolution(image)) {
						$_resizeImage.imgAreaSelect({
							onSelectChange: function () {
								return true;
							},
							parent: _options.iasParent
						});
						$_resizeImage
							.hide()
							.imgAreaSelect({remove: true,parent: _options.iasParent})
							.removeAttr('src');
						$_resizeText.hide();

						self.clearImage($block);
						if (!_checkMinImageResolution(image)) {
							_setError(t('Размер изображения должен быть не меньше {{0}}х{{1}} пикселей.', [MIN_PHOTO_WIDTH, MIN_PHOTO_HEIGHT]), $block);
						} else if (!_checkMaxImageResolution(image)) {
							_setError(t('Размер изображения должен быть не больше {{0}}х{{1}} пикселей.', [MAX_PHOTO_WIDTH, MAX_PHOTO_HEIGHT]), $block);
						}
						return false;
					}

					$_resizeImage.attr('src', e.target.result).show();
					$_resizeText.show();
					var $img = $('<img class="' + classes.thumbImage + '">')
						.attr('src', e.target.result)
						.css('background', '#fafafa');
					$block.find('.' + _options.fileWrapperClass).html('').append($img);

					var $kworkPreviewWrapper = $block.find('.kwork-preview-wrapper');
					if ($kworkPreviewWrapper.length) {
						$kworkPreviewWrapper.hide();
					}

					ias = $_resizeImage.css('display', 'block').imgAreaSelect({
						handles: true,
						aspectRatio: _options.widthRatio + ":" + _options.heightRatio,
						persistent: true,
						parent: _options.iasParent,
						onSelectChange: function (img, coordinates) {
							_setPreview(coordinates, $block);
						},
						instance: true
					});

					var ratio = _options.widthRatio / _options.heightRatio;
					var w_need = _options.minWidth * (100 / $_resizeImage[0].naturalWidth) / 100;
					var min_w_selection = $_resizeImage.width() * w_need;
					var min_h_selection = min_w_selection / ratio;
					var max_w_selection = Math.min($_resizeImage.width(), $_resizeImage.height() * ratio);
					var max_h_selection = Math.min($_resizeImage.height(), $_resizeImage.width() / ratio);

					if($_resizeImage[0].naturalWidth == MIN_PHOTO_WIDTH) {
						min_w_selection = max_w_selection;
					}
					if($_resizeImage[0].naturalHeight == MIN_PHOTO_HEIGHT) {
						min_h_selection = max_h_selection;
					}

					ias.setSelection(0, 0, max_w_selection, max_h_selection);
					ias.setOptions({show: true, minWidth: min_w_selection, minHeight: min_h_selection});
					ias.update();
					_setPreview(ias.getSelection(), $block);
					self.markAsNotDelete($block);
					$_resizeImage.data('imageBlock', $block);
					$block.data('naturalSizes', {
						width: $_resizeImage[0].naturalWidth,
						height: $_resizeImage[0].naturalHeight
					});
					callback();
				};
			};
		})(f);

		reader.readAsDataURL(f);
	};

	var _setError = function (text, $block) {
		var $addPhoto = $($block).closest('.js-add-photo');
		var errorBlock = $addPhoto.find('.js-add-photo_error');

		if ($block.find('.kwork-preview-wrapper').length) {
			$block.find('.kwork-preview-wrapper').show();

			window.firstPhoto = {};
			window.firstPhotoHash = $addPhoto.find('input[name="old-first-photo-hash"]').val();
		}

		errorBlock.html(text);
		if (!text) {
			$block = errorBlock.parents('.js-field-block').first();
			if ($block) {
				$block.removeClass('kwork-save-step__field-value_error');
			}
		}
	};

	var _checkFileSize = function (size) {
		return size <= MAX_PHOTO_SIZE;
	};

	var _checkMinImageResolution = function (img) {
		return !(img.width < MIN_PHOTO_WIDTH || img.height < MIN_PHOTO_HEIGHT);
	};

	var _checkMaxImageResolution = function (img) {
		return !(img.width > MAX_PHOTO_WIDTH || img.height > MAX_PHOTO_HEIGHT);
	};

	var _setPreview = function (coordinates, $block) {
		var rx = $($block).find('.' + _options.fileWrapperClass).width() / coordinates.width;
		var ry = $($block).find('.' + _options.fileWrapperClass).height() / coordinates.height;

		var imageMaxWidth = $_resizeImage.width();
		var imageMaxHeight = $_resizeImage.height();
		var xPercent = coordinates.x1 / imageMaxWidth;
		var yPercent = coordinates.y1 / imageMaxHeight;
		var x2Percent = coordinates.x2 / imageMaxWidth;
		var y2Percent = coordinates.y2 / imageMaxHeight;

		$block.find('.' + classes.thumbImage).css({
			width: Math.round(rx * imageMaxWidth) + 'px',
			height: Math.round(ry * imageMaxHeight) + 'px',
			marginLeft: '-' + Math.round(rx * coordinates.x1) + 'px',
			marginTop: '-' + Math.round(ry * coordinates.y1) + 'px'
		});

		var inputCoordinates = {
			'x': xPercent,
			'y': yPercent,
			'w': Math.abs(xPercent - x2Percent),
			'h': Math.abs(yPercent - y2Percent),
			'x1': coordinates.x1,
			'y1': coordinates.y1,
			'x2': coordinates.x2,
			'y2': coordinates.y2,
			'minW': imageMaxWidth,
			'minH': imageMaxHeight
		};

		$block.find('.' + classes.photoSizeInput).val(JSON.stringify(inputCoordinates));
		$block.data('sizes', inputCoordinates);
	};

	var _setStartImages = function () {
		if (typeof _options == 'undefined' || !_options.hasOwnProperty('photos') || Object.keys(_options.photos).length == 0) {
			self.addRow();
		} else {
			for (var i = 0; i < _options.photos.length; i++) {
				self.addPhoto(_options.photos[i]);
			}

			if (typeof _options.maxCount === 'undefined' || _options.maxCount > 1) {
				var emptyBlocks = 3 - _options.photos.length % 3;

				for (i = 0; i < emptyBlocks; i++) {
					self.addPhoto(0);
				}
			}
		}

		_checkExcessBlocks();
	};

	var _hideResizeBlock = function () {
		$_resizeImage.removeAttr('src').hide();
		$_resizeImage.imgAreaSelect({remove: true});
		$_resizeText.hide();
	};

	var _setVisibilityAddRowButton = function () {
		if (_photoCount >= _options.maxCount) {
			$(_options.selectorBlock).find('.js-add-photo-row-btn').hide();
		} else {
			$(_options.selectorBlock).find('.js-add-photo-row-btn').show();
		}
	};

	var _checkExcessBlocks = function () {
		for (var i = _options.maxCount; i < $_photoBlocks.length; i++) {
			$($_photoBlocks[i]).addClass('hidden');
			_hideResizeBlock();
		}

		for (i = 0; i < _options.maxCount; i++) {
			$($_photoBlocks[i]).removeClass('hidden');
		}
	};

	return {
		init: function (options) {
			self = this;
			self.setDedfaults(options);

			_setStartImages();
			self.checkDocumentChanged();
		},
		setDedfaults: function(options) {
			self = this;
			_options = options;

			if (!_options.maxCount) {
				_options.maxCount = 3;
			}
			if (!_options.minWidth) {
				_options.minWidth = MIN_PHOTO_WIDTH;
			}
			else {
				MIN_PHOTO_WIDTH = _options.minWidth;
			}
			if (!_options.minHeight) {
				_options.minHeight = MIN_PHOTO_HEIGHT;
			}
			else {
				MIN_PHOTO_HEIGHT = _options.minHeight;
			}
			if (!_options.widthRatio) {
				_options.widthRatio = WIDTH_RATIO;
			}
			if (!_options.heightRatio) {
				_options.heightRatio = HEIGHT_RATIO;
			}

			if (!options.oldIdInputName) {
				_options.oldIdInputName = OLD_ID_DEFAULT_NAME;
			}

			if (!options.sizeInputName) {
				_options.sizeInputName = _options.fileInputName + '-size[]';
			}
			if (!options.iasParent) {
				_options.iasParent = '.all_page';
			}

			$_addPhotoBlock = $(_options.selectorBlock).find('.js-add-photo');
			$_resizeImage = $(_options.selectorBlock).find('.' + classes.resizeImage);
			$_resizeText = $(_options.selectorBlock).find('.' + classes.resizeText);
			$(_options.selectorBlock).find('.js-add-photo-row-btn').on('click', self.addRow);
		},
		reInit: function(options) {
			self = this;
			self.setDedfaults(options);

			$_addPhotoBlock.find('.add-photo__file-wrapper').each(function(i, e) {
				var $block = jQuery(this);

				_setBlockEvents($block);
				$_photoBlocks[i] = $block;
			});

			self.checkDocumentChanged();
		},
		addRow: function () {
			for (var i = 0; i < 3; i++) {
				self.addPhoto();
			}
			_hideResizeBlock();
		},
		addPhoto: function (photo) {
			if (_photoCount >= _options.maxCount && typeof photo == 'undefined') {
				return;
			}

			var photoBlockHtml = $_addPhotoBlock.find('.js-template-photo-block').html();
			var $photoBlock;

			$photoBlock = $(photoBlockHtml);
			var $fileInput = $photoBlock.find('.' + classes.fileInput);
			$fileInput.attr('name', _options.fileInputName + '[]');
			if (photo && photo.id) {
				$fileInput.data('hash', photo.hash).attr('data-hash', photo.hash);
				$photoBlock.find('.' + classes.deletePhotoInput).attr('name', 'delete-photo[' + photo.id + ']');
			}
			$photoBlock.find('.' + classes.photoSizeInput).attr('name', _options.sizeInputName);
			$photoBlock.find('.' + classes.idPhotoInput).attr('name', _options.idInputName);

			_setBlockEvents($photoBlock);

			if (typeof photo != 'undefined' && typeof photo == 'object') {
				$photoBlock.find('.' + classes.oldPhotoIdInput).val(photo.id).attr('name', _options.oldIdInputName);
				$photoBlock.find('.' + _options.fileWrapperClass).css({
					'background': 'url(' + photo.value + ')',
					'background-size': '100%',
					'background-repeat': 'no-repeat',
				});

				this.markAsNotDelete($photoBlock);
			}

			$_addPhotoBlock.find('.js-photo__block').append($photoBlock);
			$_photoBlocks[_photoCount] = $photoBlock;
			_photoCount += 1;

			_setVisibilityAddRowButton();
		},
		markAsDelete: function ($block) {
			var $addBtn = $block.find('.js-file-add-button');

			$addBtn.each(function() {
				var $btn = jQuery(this);

				var addBtn_initText = $btn.data('initText');
				var addBtn_editText = $btn.data('editText');

				if (addBtn_initText) $btn.text(addBtn_initText);
			});

			$block.find('.' + classes.portfolioImageId).val('');

			$block.find('.' + classes.deletePhotoInput).val(1);
			$block.find('.' + classes.deleteLink).addClass('hidden');

			$block.find('.' + classes.changedPhotoInput).val(1);
		},
		markAsNotDelete: function ($block) {
			var $addBtn = $block.find('.js-file-add-button');

			$addBtn.each(function() {
				var $btn = jQuery(this);

				var addBtn_initText = $btn.data('initText');
				var addBtn_editText = $btn.data('editText');

				if (addBtn_editText) $btn.text(addBtn_editText);
			});

			$block.find('.' + classes.deletePhotoInput).val(0);
			$block.find('.' + classes.deleteLink).removeClass('hidden');
		},
		clearImage: function ($block) {
			$block.find('.' + classes.thumbImage).remove();
			if (_options.fileWrapperClass !== 'file-wrapper-block-rectangle') {
				$block.find('.' + _options.fileWrapperClass).removeAttr('style');
			}
			$block.find('.' + classes.photoSizeInput).val('');
			$block.find('.' + classes.fileInput).val('')
				.removeData('hash').removeAttr('data-hash');
			$block.data('sizes', null);
			$block.data('naturalSizes', null);
			if ($_resizeImage.data('imageBlock') == $block) {
				_hideResizeBlock();
			}

			self.markAsDelete($block);
		},
		hideResizeBlock:_hideResizeBlock,
		checkSubmitSize: function (e) {
			for (var i = 0; i < $_photoBlocks.length; i++) {
				var $photoBlock = $_photoBlocks[i];
				if ($photoBlock.find('.' + classes.fileInput).val().length) {
					var percentSizes = $photoBlock.data('sizes');
					var naturalSizes = $photoBlock.data('naturalSizes');

					if (Math.ceil(naturalSizes.width * percentSizes.w) < MIN_PHOTO_WIDTH || Math.ceil(naturalSizes.height * percentSizes.h) < MIN_PHOTO_HEIGHT) {
						e.preventDefault();
						_setError(t('Размер выделенного участка изображения {{0}} должен быть не меньше {{1}}х{{2}} пикселей.', [(i + 1), MIN_PHOTO_WIDTH, MIN_PHOTO_HEIGHT]), $photoBlock);
						return false;
					}
				}
			}
		},
		hasSelectedPhoto: function () {
			var oldPhotoVal = '';

			if ($_photoBlocks[0].find('.' + classes.oldPhotoIdInput).length) {
				oldPhotoVal = $_photoBlocks[0].find('.' + classes.oldPhotoIdInput).val().length
			}

			if (oldPhotoVal || $_photoBlocks[0].find('.' + classes.fileInput).val().length) {
				return true;
			}

			return false;
		},
		changeMaxPhoto: function (maxCount) {
			_options.maxCount = maxCount ^ 0;

			if (_options.maxCount == 0) {
				_options.maxCount = 3;
			}

			_checkExcessBlocks();
			_setVisibilityAddRowButton();
		},
		getPhotoSize: function () {
			var totalSize = 0;

			for (var i = 0; i < $_photoBlocks.length; i++) {
				var file = $($_photoBlocks[i]).find('.' + classes.fileInput)[0].files[0];
				if (typeof file != 'undefined') {
					totalSize += file.size || file.fileSize;
				}
			}

			return totalSize;
		},
		cancelModule: function () {
			for (var i = 0; i < $_photoBlocks.length; i++) {
				self.clearImage($_photoBlocks[i]);
			}
		},
		checkDocumentChanged: function () {
			var lastHeight = document.body.scrollHeight, newHeight;
			(function run(){
				newHeight = document.body.scrollHeight;
				if( lastHeight != newHeight )
					self.reDraw();
				lastHeight = newHeight;

				if( self.onElementHeightChangeTimer )
					clearTimeout(self.onElementHeightChangeTimer);

				self.onElementHeightChangeTimer = setTimeout(run, 200);
			})();
		},
		reDraw: function () {
			if (typeof ias != 'undefined') {
				ias.update();
			}
		}
	}
}
