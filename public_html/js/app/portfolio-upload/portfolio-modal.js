import { SortableCardList } from './sortable-card-list.js';
import { FileUploader } from 'appJs/file-uploader.js';

export class PortfolioModal {

	constructor() {
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
			'videos': [],
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
		this.block.find('.' + this.classes.selectOrder).on('keyup change chosen:showing_dropdown', (e) => {
			this.formatChosenList();
		});
		let orderParent = this.block.find('.' + this.classes.selectOrder).parent();
		orderParent.on('click', (e) => {
			if (!orderParent.hasClass('loaded')) {
				this.getFullOrders(this.portfolio.kwork_id, (data) => {
					this.renderOrders(data.orders, null, false, true);
					$(e.target).find('select').trigger('mousedown').trigger('chosen:open');
					this.formatChosenList();
				});
			}
		});
	}

	/**
	 * Форматирование выпадающего списка, добавление переносов строк
	 */
	formatChosenList() {
		$('.chosen-results .active-result').html(function() {
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
	
	initSortableContainer() {
		this.sortableContainer = new SortableCardList(this.block.find('.sortable-card-list')[0]);
		this.sortableContainer.onChangeState = () => {
			this.updateSaveButton();
			this.updateImages();
			this.validateImages();
		}
		
		this.sortableContainer.onSuccess = (item) => {
			this.updateCoverMini();
			// если обложек ранее не было
			// если выбрана загруженная обложка, то картинку в нарезке не меняем
			// при загрузке каждого нового изображения картинку в нарезке не меняем. Меняем только при загрузке первого изображения портфолио
            this.portfolioItem.backendErrors.cover = [];
			if (
				!this.portfolio.cover.urlBig ||
				(
					this.portfolio.cover.urlDefault !== this.portfolio.cover.urlBig
					&& this.portfolio.images.length === 1
				)
			) {
				this.updateCoverUrl(item.data.urlBig);
				this.updateCoverIdPortfolioImage(item.data.id);
				this.updateCoverBlock();
				this.portfolio.cover.type = this.COVER_IMAGE_TYPE_IMAGE;
			}

			this.setCoverMiniSelected(this.portfolio.cover.urlBig);
		}
		
		this.sortableContainer.onDelete = (item) => {
			this.updateImages();
			this.selectRandomCover(item.data.urlBig, true);
		}
	}

	/**
	 * Инициализация для обложки
	 */
	initCover() {
		this.coverUploader = new FileUploader();
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
		this.coverUploader.mimeTypes = [
			'image/jpeg',
			'image/png',
			'image/gif'
		];
		this.coverUploader.errors.unknown = t('Произошла ошибка. Пожалуйста, попробуйте еще раз.');

		// callbacks
		this.coverUploader.onError = (text) => {
			this.hideCoverProgress();
			this._unlockSaveBtn();

			this.rollbackCover();
			this.errorCover(text || this.coverUploader.errors.unknown);
		};
		this.coverUploader.onLoad = (base64) => {
			this.base64 = base64;
			this.updateCoverBlock(this.base64);
			this.showCoverProgress();
			this._lockSaveBtn();
		};
		this.coverUploader.onProgress = (percent) => {
			this.updateCoverProgress(percent);
		};
		this.coverUploader.onSuccess = (data) => {
            this.portfolioItem.backendErrors.cover = [];

			this.portfolio.cover.id = data.id;
			this.portfolio.cover.hash = data.hash;
			this.portfolio.cover.urlDefault = this.base64;
			this.portfolio.cover.crop = null;
			this.portfolio.cover.type = this.COVER_IMAGE_TYPE_UPLOAD;
			this.updateCoverMini();
			this.updateCoverUrl(this.base64);
			this.updateCoverIdPortfolioImage();
			this.setCoverMiniSelected(this.base64);
			this.errorCover();
			this.hideCoverProgress();
			this._unlockSaveBtn();
		}
		this.coverUploader.onFail = (errors) => {
			this.hideCoverProgress();
			this._unlockSaveBtn();

			let errorText = '';
			$.each(errors, (k, v) => {
				errorText += v + '<br>';
			});

			this.rollbackCover();
			this.errorCover(errorText);
		};

		// event

		// минимальные ограничения на кроп область
		this.block.find('.' + this.classes.coverImage).on('zoom', 'img', (e) => {
			let data = this.coverCropper.cropper('getData');
			if (e.originalEvent.detail.ratio > e.originalEvent.detail.oldRatio
				&& (data.width < this.MIN_WIDTH_CROP_BOX || data.height < this.MIN_HEIGHT_CROP_BOX)) {
				e.preventDefault();
			}
		});
		// загрузка обложки
		this.block.find('.' + this.classes.coverUploadField).on('click', () => {
			this.updateCoverCrop();
			this.coverUploader.upload();
		});
		// загрузка обложки
		this.block.find('.' + this.classes.coverBtnUpload).on('click', () => {
			this.updateCoverCrop();

			let portfolioId = window.portfolioList.modal.portfolio.id;
			if (portfolioId) {
				this.coverUploader.postData.portfolio_id = portfolioId;
			}

			let hashes = this.getImagesHashes().concat(this.getCoverHashes());
			if (this.portfolio.cover.hash) {
				hashes.push(this.portfolio.cover.hash);
			}
			if (window.firstPhotoHash) {
				hashes.push(window.firstPhotoHash);
			}
			if (hashes.length) {
				$.each(hashes, (k, v) => {
					this.coverUploader.postData['hashes[' + k + ']'] = v;
				});
			}

			this.coverUploader.upload();
		});
	}

	/**
	 * Откатить изменения по обложке
	 */
	rollbackCover() {
		this.defaultCoverField();
		// Очистка примера превью
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
	clearCover() {
		this.defaultCoverField();
		this.clearExampleCardPreview();
		this.hideCoverUpload();
	}

	/**
	 * Если выбрано переданое изображение, то выбрать другое. Если нет вариантов обложки, то очищаем нарезку 
	 * @param url
	 * @param afterDelete
	 */
	selectRandomCover(url, afterDelete) {
		this.updateCoverMini();
		if (
			this.block.find('.' + this.classes.coverImage).find('img[src="' + url + '"]').length && this.coverMini.length
			||
			(this.coverMini[0] && this.coverMini[0].url)
		) {
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
				setTimeout(() => {
					this.updateYoutubeCover();
				}, 0);
			}
		}
	}

	/**
	 * Привести в изначальное состояние область для загрузки обложки
	 */
	defaultCoverField() {
		this.block
			.find('.' + this.classes.coverImage)
			.find('> *:not(.progress)')
			.remove();
		this.showCoverField();
		this.hideCoverImage();
		this.destroyCropper();
	}

	/**
	 * Обновить обложку
	 */
	updateCoverBlock(coverUrl = '') {
		this.destroyCropper();
		this.showCoverUpload();
		this.errorCover();

		let coverImage = this.block.find('.' + this.classes.coverImage);
		let img = coverImage.find('img');
		let url = coverUrl || this.portfolio.cover.urlBig;
		let cropData = this.portfolio.cover.crop ? this.portfolio.cover.crop : '';

		this.showCoverImage();
		this.hideCoverField();

		if (img.length) {
			img.attr('src', url);
		} else {
			coverImage.append(
				this._cropImgTpl(url)
			);
		}

		coverImage.find('img').ready(() => {
			this.initCropper(cropData);
		});
		
		this.setCoverMiniSelected(url);
	}

	/**
	 * Инициализация кроппера
	 * 
	 * @param {object} cropData Позиционировани кроппа
	 */
	initCropper(cropData = null) {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		let img = coverImage.find('img');

		if (cropData) {
			// get natural img size :)
			$('<img>').attr('src', img.attr('src')).load((e) => {
				let naturalWidth = e.target.width;
				let naturalHeight = e.target.height;

				this.cropperConfig.data = {
					x: cropData.x * naturalWidth,
					y: cropData.y * naturalHeight,
					width: Math.round(cropData.w * naturalWidth),
					height: Math.round(cropData.h * naturalHeight)
				};
				
				this.coverCropper = img.cropper(this.cropperConfig);
			});
		} else {
			this.cropperConfig.data = {};
			this.coverCropper = img.cropper(this.cropperConfig);
		}
	}

	/**
	 * Получить кропнутое изображение (base64)
	 */
	getImgCropper() {
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
	destroyCropper() {
		if (this.coverCropper) {
			this.coverCropper.cropper('destroy');
			this.coverCropper = '';
		}
	}

	/**
	 * Получить данные для кроп-обложки
	 */
	getCropperData() {
		// w - размер ширины кропа делится на ширину изображения
		// h - размер высоты кропа делится на высоту изображения
		// x - сдвиг по ширине кроп области от левого края изображения делится на ширину изображения
		// y - сдвиг по высоте кроп области от верхнего края изображения делится на высоту изображения

		if (this.coverCropper) {
			try {
				let data = this.coverCropper.cropper('getData');
				let imgData = this.coverCropper.cropper('getImageData');

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
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Установить ошибку для обложки
	 * @param {*} text Текст ошибки (пустое значение очистить ошибку)
	 */
	errorCover(text = '') {
		let coverError = this.block.find('.' + this.classes.coverError);
		coverError.html(text);
	}

	/**
	 * Показать текст если обложка загружена
	 */
	showCoverTextLoad() {
		this.coverTextNotLoad.hide();
		this.coverTextLoad.show();
	}

	/**
	 * Показать текст если обложка не загружена
	 */
	showCoverTextNotLoad() {
		this.coverTextNotLoad.show();
		this.coverTextLoad.hide();
	}
	
	/**
	 * Показать нарезку обложки
	 */
	showCoverUpload() {
		this.coverUploadGrid.addClass('show-cover-upload');
	}

	/**
	 * Скрыть нарезку обложки
	 */
	hideCoverUpload() {
		this.coverUploadGrid.removeClass('show-cover-upload');
		this.hideCoverMini();
	}

	/**
	 * Показать картинку обложки
	 */
	showCoverImage() {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		coverImage.show();
	}

	/**
	 * Скрыть картинку обложки
	 */
	hideCoverImage() {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		coverImage.hide();
	}
	
	/**
	 * Показать миниатюрки обложки
	 */
	showCoverMini() {
		this.block.find('.' + this.classes.coverMini).show();
		this.coverUploadGrid.addClass('show-cover-mini');
	}

	/**
	 * Скрыть миниатюрки обложки
	 */
	hideCoverMini() {
		this.block.find('.' + this.classes.coverMini).hide();
		this.coverUploadGrid.removeClass('show-cover-mini');
	}

	/**
	 * Показать области загрузки обложки
	 */
	showCoverField() {
		let coverUploadField = this.block.find('.' + this.classes.coverUploadField);
		coverUploadField.show();
	}

	/**
	 * Скрыть области загрузки обложки
	 */
	hideCoverField() {
		let coverUploadField = this.block.find('.' + this.classes.coverUploadField);
		coverUploadField.hide();
	}

	showCoverProgress() {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		this.updateCoverProgress(0);
		coverImage.addClass('loading');
	}

	hideCoverProgress() {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		coverImage.removeClass('loading');
	}

	updateCoverProgress(percent) {
		let coverImage = this.block.find('.' + this.classes.coverImage);
		coverImage.find('.progress div').css({'width': percent + '%'});
	}

	/**
	 * очистить миниатюрные обложки
	 */
	clearCoverMini() {
		this.coverMini = [];
		
		let coverMini = this.block.find('.' + this.classes.coverMini);

		if (coverMini.hasClass('slick-initialized')) {
			coverMini.slick('unslick');
		}
		
		coverMini.html('');
		
	}

	/**
	 * обновить миниатюрные обложки
	 */
	updateCoverMini() {
		this.clearCoverMini();
		if (this.portfolio.cover && this.portfolio.cover.urlDefault) {
			this.coverMini.push({id: 0, url: this.portfolio.cover.urlDefault, type: this.COVER_IMAGE_TYPE_UPLOAD});
		}

		// обновление миниатюрных облоек из картинок портфолио
		$.each(this.portfolio.images, (k, v) => {
			if (v.urlBig) {
				this.coverMini.push({id: v.id, url: v.urlBig, type: this.COVER_IMAGE_TYPE_IMAGE});
			}
		});

		if (this.portfolio.cover.urlFromVideo) {
			this.coverMini.push({id: 0, url: this.portfolio.cover.urlFromVideo, type: this.COVER_IMAGE_TYPE_VIDEO})
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
	addCoverMini() {
		$.each(this.coverMini, (k, v) => {
			this.block.find('.' + this.classes.coverMini).append(
				'<div class="preview">' +
				'<img src="' + v.url + '" alt="" data-id-image-portfolio="' + v.id + '"' + (v.type ? ' data-type="' + v.type + '"' : '') + '>' +
				'</div>'
			);
	
			// выбор обложки из мини вариантов
			this.block.find('.' + this.classes.coverMini).find('.preview').on('click', (e) => {
				this.updateCoverUrl($(e.target).attr('src'));
				this.updateCoverIdPortfolioImage($(e.target).data('idImagePortfolio'));
				this.updateCoverBlock();
				this.portfolio.cover.type = $(e.target).data('type');
			});
		});
	}

	/**
	 * выделить выбранное изображение 
	 * @param url
	 */
	setCoverMiniSelected(url) {
		let preview = this.block.find('.' + this.classes.coverMini).find('.preview');
		preview.removeClass('selected');
		preview.find('img').filter('[src="' + url + '"]').parents('.preview').addClass('selected');
	}
	
	initCoverSlick() {
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
				nextArrow: '<span class="cover-upload-mini__slick cover-upload-mini__slick-next"><i class="fa fa-angle-right"></i></span>',
			});
		}
	}

	/**
	 * Список событий
	 */
	events() {
		// Закрытие модального окна "Да, закрыть"
		this.confirmBlock.find('.' + this.classes.btnCloseConfirm).on('click', (e) => {
			this.confirmBlock.modal('hide');

			this._modalHide();
		});

		// Закрытие модального окна "Продолжить"
		this.confirmBlock.find('.' + this.classes.btnCloseContinue).on('click', (e) => {
			this.confirmBlock.modal('hide');

			$('body').addClass('modal-open');
			this.block.fadeIn(300);
		});

		// Событие на закрытие модального окна
		this.block.on('hide.bs.modal', (e) => {
			// окно подтверждения
			if (this.confirmAtClose) {
				this.updateAll();

				if (this.isChangedPortfolio() === false) {
					return;
				}
				
				this.setDefaultTitle();

				e.preventDefault();

				this.block.fadeOut(300);
				this.confirmBlock.modal('show');
			}
		});

		// Подсчет количества символов
		this.block.find('.' + this.classes.fieldCounter).on('input', (e) => {
			this.countCharacters($(e.target));
		});

		// Обработка ввода заголовка
		this.titleInput.on('input', () => {
			this.updateTitle();
			this.validateTitle();
			this.setExampleCardName();
		});

		// Сохранение портфолио
		this.block.find('.' + this.classes.btnSave).on('click', (e) => {
			if ($(e.target).hasClass('js-save-portfolio-for-draft')) {
				// Сохраняем черновик
				this.save(true);
			} else {
				this.save();
			}
		});

		// Перерасчёт координат при полном показе модального окна
		this.block.on('shown.bs.modal', () => {
			this.sortableContainer.updateOffsets();
			this.sortableContainer.updateAllBlockCoordinates();
		});

		// Удаление поля youtube
		this.block.on('click', '.' + this.classes.youtubeRemove, (e) => {
			var field = $(e.target).closest('.' + this.classes.fieldYoutube);
			this.youtubeRemove(field.find('input'));
		});

		// Слушаем поля youtube, для добавления новых полей
		this.block.on('input', '.' + this.classes.fieldYoutube + ' input', (e) => {
			this.youtubeEvents(
				$(e.target)
			);
		});

		// Событие при смене кворка
		this.block.on('change', '.' + this.classes.selectKwork, (e) => {
			this.kworkChange();
		});

		// Событие при смене заказа
		this.block.on('change', '.' + this.classes.selectOrder, (e) => {
			this.orderChange();
		});

		// Событие при смене родительской рубрики
		this.block.on('change', '.' + this.classes.selectParentCategories, (e) => {
			this.portfolio.attributes_ids = [];
			this.renderCategories();
			this.updateCategory();
			this.validateCategory();
			this.portfolio.attributes_ids = [];
			this.block.find('.' + this.classes.attributes).html('');
			this.renderAttributes();
			this.renderKworksAndOrders();
			this.setExampleCardCategory();
			this.selectBlockVideosOrImages();
		});

		// Событие при смене подрубрик
		this.block.on('change', '.' + this.classes.selectCategories, (e) => {
			this.updateCategory();
			this.validateCategory();
			this.portfolio.attributes_ids = [];
			this.block.find('.' + this.classes.attributes).html('');
			this.renderAttributes();
			this.renderKworksAndOrders();
			this.setExampleCardCategory();
			this.selectBlockVideosOrImages();
		});

		this.block.find('.' + this.classes.attributes).on('change', 'input:radio', (e) => {
			let attributeId = $(e.target).val();
			let $parent = $(e.target).closest('.js-field-block');
			let level = $parent.data('level');
			$parent.children('.js-attribute-section-block').empty();
			this.updateAttributes();
			this.renderAttributes(level, attributeId);
			this.renderKworksAndOrders();
		});

		this.block.find('.' + this.classes.attributes).on('change', 'input:checkbox', (e) => {
			let attributeId = $(e.target).val();
			let $parent = $(e.target).closest('.js-field-block');
			let level = $parent.data('level');
			this.updateAttributes();
			if ($(e.target).prop("checked")) {
				this.renderAttributes(level, attributeId);
			} else {
				$parent.children('.js-attribute-section-block').find('.js-field-block[data-parent-id='+attributeId+']').remove();
			}
			this.renderKworksAndOrders();
		});
		
		this.block.on('click', '.' + this.classes.linkShowImages, () => {
			this.showBlockImages();
		});

		this.block.on('click', '.' + this.classes.linkShowVideos, () => {
			this.showBlockVideos();
		});
	}

	kworkChange(updateOrders = true) {
		this.updateKwork();
		this.hideError(this.block.find('.' + this.classes.kworkError));

		let kworkId = this.portfolio.kwork_id;
		let kwork = this.kworksList[parseInt(kworkId)];

		if (updateOrders) {
			if (!kworkId) {
				this.getKworks((response) => {
					this.renderOrders([], null, response.hasOrders);
				});
			} else {
				this.getOrders(kworkId, (data) => {
					this.renderOrders([], null, data.hasOrders);
				});
			}
		}

		if (!kwork) {
			return;
		}

		let parentCategoryId = null;
		this.portfolio.category_id = kwork.category;
		$.each(this.additionalData.categories, (k, v) => {
			if (v.CATID == this.portfolio.category_id) {
				parentCategoryId = v.parent;
				return false;
			}
		});

		this.renderParentCategories(parentCategoryId);
		this.renderCategories(this.portfolio.category_id);
		
		this.portfolio.attributes_ids = kwork.attributesIds;
		
		// Очищаем выбранные атрибуты
		this.block.find('.' + this.classes.attributes).html('');
		this.renderAttributes(null);
	}

	orderChange() {
		let selectKwork = this.block.find('.' + this.classes.selectKwork);

		this.updateOrder();
		let orderId = parseInt(this.portfolio.order_id);
		let kworkId = "null";
		if (orderId) {
			let order = this.ordersList[parseInt(this.portfolio.order_id)];
			kworkId = order.PID;
		}
		selectKwork.val(kworkId);
		selectKwork.trigger("chosen:updated");
		this.kworkChange(false);
	}

	renderKworksAndOrders() {
		this.getKworks((response) => {
			this.renderKworks(response.kworks);
			this.renderOrders([], null, response.hasOrders);
		});
	}

	/**
	 * Наполнение формы
	 */
	updateContent() {
		// Очистка примера превью
		this.clearExampleCardPreview();
		// Генерация случайных показателей, для примера карточки
		this.generateExampleCardCnt();

		// Заголовок формы
		this.block.find('.js-modal-title').text(
			(this.portfolio && this.portfolio.id) 
				? t('Редактировать работу в Портфолио') 
				: t('Добавить работу в Портфолио')
		);

		// Название работы
		this.block.find('input[name="title"]').val(he.decode(this.portfolio.title));
		this.setExampleCardName();

		this.sortableContainer.loadItems(this.portfolio.images);
		
		this.block.find('.' + this.classes.videos).html('');
		if (this.portfolio.videos.length) {
			$.each(this.portfolio.videos, (k, v) => {
				this.youtubeAdd(v);
			});
		}
		this.youtubeAdd();
		this.youtubeVisibleRemoveBtn();

		// пересчитаем счетчики
		this.countCharacters();

		// обложка
		this.defaultCoverField();
		
		let coverUrl;
		let coverId = 0;

		let assignedId = this.portfolio.cover.idPortfolioImage || -1;

		let coverPortfolioImage = this.portfolio.images.find(x => x.hash === this.portfolio.cover.hash || x.id == assignedId);
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
			
				let categoryId = this.portfolio.category_id || null;
				let parentCategoryId = null;

				$.each(this.additionalData.categories, (k, v) => {
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
				this.renderAttributes();

				// Kворк
				if (!$.isEmptyObject(this.additionalData.kworks)) {
					this.renderKworks(this.additionalData.kworks, this.portfolio.kwork_id);
				}
				this.setExampleCardCategory();
			}

			// Заказ
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
	edit(portfolio, additional = {}) {
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

	setTitleErrors(fromInput) {
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

	updateTitle() {
		this.setTitleErrors(true);
		this.portfolio.title = this.titleInput.val();
	}

	updateCategory() {
		this.portfolio.category_id = this.block.find('.' + this.classes.selectCategories).val();
	}

	updateAttributes() {
		this.portfolio.attributes_ids = [];
		
		this.block.find('.' + this.classes.attributes).find(':checked').each((k, v) => {
			let el = $(v);
			let $attrList = el.closest('.attribute-list');
			let multipleMaxCount = $attrList.data('multiple-max-count');
			let checkedSize = $attrList.find('input[type="checkbox"]:checked').size();
			if(multipleMaxCount > 0){
				if (checkedSize >= multipleMaxCount){
					$attrList.find('input[type="checkbox"]').not(':checked').prop('disabled', true).parent().css('opacity', '0.5');
				}else {
					$attrList.find('input[type="checkbox"]').not(':checked').prop('disabled', false).parent().css('opacity', '1');
				}
			}
			this.portfolio.attributes_ids.push(el.val());
		});
	}

	updateKwork() {
		this.portfolio.kwork_id = null;
		if (this.fixedKworkId) {
			this.portfolio.kwork_id = this.fixedKworkId;
			return;
		}
		if (this.fieldKworks.is(':visible')) {
			this.portfolio.kwork_id = parseInt(this.block.find('.' + this.classes.selectKwork).val()) || null;
		}
	}

	updateOrder() {
		this.portfolio.order_id = null;
		if (this.fieldOrders.is(':visible')) {
			this.portfolio.order_id = parseInt(this.block.find('.' + this.classes.selectOrder).val()) || null;
		}
	}

	updateCoverCrop() {
		this.portfolio.cover.crop = this.getCropperData();
	}

	/**
	 * Обновление параметра урла обложки
	 * @param url
	 */
	updateCoverUrl(url) {
		this.portfolio.cover.url = url;
		this.portfolio.cover.urlBig = url;
	}

	/**
	 * id изображения портфолио, если обложка берется из изображений портфолио
	 * @param id
	 */
	updateCoverIdPortfolioImage(id = 0) {
		this.portfolio.cover.idPortfolioImage = id;
	}

	/**
	 * Обновляем hash обложки
	 * @param hash
	 */
	updateCoverHash(hash = null) {
		this.portfolio.cover.hash = hash;
	}

	updateImages() {
		let images = [];
		$.each(this.sortableContainer.items, (k, v) => {
			images.push(v.data);
		});
		this.portfolio.images = images;
	}

	updateVideos() {
		let videos = [];
		this.block.find('.youtube-field input').each((k, v) => {
			let el = $(v);
			let link = el.val().trim();
			if (link.length > 0) {
				videos.push(link);
			}
		});
		this.portfolio.videos = videos;
	}

	setDefaultTitle() {
		if (!this.portfolio.title) {
			this.portfolio.title = this.getPortfolioNumber();
		}
	}
	
	getPortfolioNumber() {
		if (this.portfolio.workNum) {
			return t('Работа №') + this.portfolio.workNum;
		}
		return !$.isEmptyObject(this.additionalData) && this.additionalData.workNum ? t('Работа №') + this.additionalData.workNum : '';
	}

	/**
	 * Отображаем блок видео или изображений в зависимости от типа портфолио
	 */
	selectBlockVideosOrImages() {
		let _images = '<span class="js-portfolio-images-link">' + t('изображения') + '</span>';
		let _video = '<span class="js-portfolio-videos-link">' + t('видео') + '</span>';

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
	
	isTypeVideo() {

		let categoryId = this.portfolio.category_id;
		let portfolioType;

		if (categoryId) {
			$.each(this.additionalData.categories, (k, v) => {
				if (v.CATID == categoryId) {
					portfolioType = v.portfolio_type;
				}
			});
		}
		
		if (
			(
				(this.page == this.PAGE_KWORK || this.page == this.PAGE_ORDER) && this.portfolioType == this.PORTFOLIO_TYPE_VIDEO
			)
			|| (portfolioType == this.PORTFOLIO_TYPE_VIDEO)
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Отображаем блок изображений и скрываем блок видео
	 */
	showBlockImages() {
		this.fieldImages.show();
		this.fieldVideos.hide();

		this.block.find('.' + this.classes.linkShowImages)
			.removeClass('portfolio-images-videos-link');
		this.block.find('.' + this.classes.linkShowVideos)
			.addClass('portfolio-images-videos-link');

		this.block.find('.' + this.classes.linkShowImages)
			.parents('.' + this.classes.field).find('.tooltipster').removeClass('hidden');
	}

	/**
	 * Отображаем блок видео и скрываем блок изображений
	 */
	showBlockVideos() {
		this.fieldImages.hide();
		this.fieldVideos.show();

		this.block.find('.' + this.classes.linkShowImages)
			.addClass('portfolio-images-videos-link');
		this.block.find('.' + this.classes.linkShowVideos)
			.removeClass('portfolio-images-videos-link');

		this.block.find('.' + this.classes.linkShowImages)
			.parents('.' + this.classes.field).find('.tooltipster').addClass('hidden');
	}

	concatErrors(field) {
		let errors = [];
		if (field in this.portfolioItem.backendErrors) {
			errors = errors.concat(this.portfolioItem.backendErrors[field]);
		}
		if (field in this.frontendErrors) {
			errors = errors.concat(this.frontendErrors[field]);
		}
		let result = [];
		$.each(errors, (k, v) => {
			if ($.inArray(v, result) == -1) {
				result.push(v);
			}
		});
		return result.join('<br />');
	}

	showError(field, errorText, invisible = false) {
		field.html(errorText);
		if(invisible || errorText.length > 0) {
			this.errorFields.push(field);
		}
	}

	hideError(field) {
		field.html('');
		let index = this.errorFields.indexOf(field);
		if (index === undefined) {
			return;
		}
		this.errorFields.splice( index, 1 );
	}

	validateTitle() {
		this.frontendErrors.title = [];
		let errorText = this.concatErrors('title');
		if (errorText.length > 0) {
			this.titleInput.addClass('input-error-portfolio');
		} else {
			this.titleInput.removeClass('input-error-portfolio');
		}
		this.showError(this.block.find('.' + this.classes.titleError), errorText)
	}

	validateCategory() {
		this.frontendErrors.category = [];
		let errorText = this.concatErrors('category');
		this.showError(this.block.find('.' + this.classes.categoryError), errorText);
		this.portfolioItem.backendErrors.category = [];
	}

	validateAttributes() {
		this.frontendErrors.attributes = [];

		let errorText = this.concatErrors('attributes');
		this.showError(this.block.find('.' + this.classes.attributesError), errorText);
		this.portfolioItem.backendErrors.attributes = [];
	}

	validateKwork() {
		this.frontendErrors.kwork = [];
		let errorText = this.concatErrors('kwork');
		this.showError(this.block.find('.' + this.classes.kworkError), errorText);

	}

	validateCover() {
		this.frontendErrors.cover = [];
		if (!this.portfolioItem.blank) {
			if (!this.portfolio.cover.url && (!this.isTypeVideo() || !this.portfolio.cover.urlFromVideo)) {
				this.frontendErrors.cover.push(
					t('Необходимо загрузить обложку')
				);
			}
		}
		let errorText = this.concatErrors('cover');
		this.showError(this.block.find('.' + this.classes.coverError), errorText);
	}

	validateImages() {
		this.frontendErrors.images = [];
		if (!this.portfolioItem.blank && this.page == this.PAGE_KWORK) {
			if (this.portfolioType == this.PORTFOLIO_TYPE_PHOTO && this.portfolio.images.length < 1) {
				this.frontendErrors.images.push(
					t('Загрузите изображение')
				);
			}
		}
		let imageErrors = this.portfolioItem.backendErrors.image;
		if (imageErrors && Object.keys(imageErrors).length > 0) {
			if (!('images' in this.portfolioItem.backendErrors)) {
				this.portfolioItem.backendErrors.images = [];

			}

			$.each(this.sortableContainer.items, (k, v) => {
				v.hasError = k in imageErrors;
				v.updateErrorVisuals();

				this.portfolioItem.backendErrors.images.push(
					imageErrors[k]
				);
			});
		}
		let errorText = this.concatErrors('images');
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
    getErrors() {
        this.errorFields=[];
        let items = this.block.find('.' + this.classes.videos).find('.youtube-field input.input-error-portfolio');
        if (items.length) {
            this.errorFields.push(items);
        } else {
            this.errorFields=[];
            this.frontendErrors.videos = [];
        }
    }

    /**
     * Проверка Youtube Url на бэке
     * @param item  Input элемент val которого передается в бэк
     */
    backendValidateVideos(item) {
        let val = item.val().trim();
        let errorField = item.parent().find('.portfolio-error');
        let _this = this;
        $.ajax({
            type: 'POST',
            url: '/api/portfolio/youtubelinkvalidate?url=' + val,
			data: {
            	kwork_id: this.portfolio.kwork_id || null,
            	portfolio_id: this.portfolio.id || null
			},
            success: (response) => {
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
            error: (e) => {
            },
			complete: () => {
               _this.getErrors();
               _this.updateVideos();
               _this.updateYoutubeCover();
            }
        });
    }

	validateVideos(saveCheck, curItem) {
		if (!this.portfolioItem.blank) {
			let videosCount = 0;
			let emptyCount = 0;

			this.block.find('.' + this.classes.fieldYoutube + ' input').each((k, v) => {
				let input = $(v);

                if (input.val().trim().length > 0) {
                    videosCount++;
                } else {
                    emptyCount++;
                }

			});

            if (curItem){
            	let index = this.block.find('.' + this.classes.fieldYoutube).index(curItem.parents('.youtube-field'));
                let valid = this._youtubeValidation(curItem, index, saveCheck);
                if (!valid) {
                    this.youtubeRemoveEmptyFiels();
                } else {
                    this.backendValidateVideos(curItem);
				}
            }


			if ((this.page == this.PAGE_KWORK || this.PAGE_MY_PORTFOLIO) && this.portfolioType == this.PORTFOLIO_TYPE_VIDEO && videosCount < 1) {
                let item = this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input');
                let errorField = item.parent().find('.portfolio-error');
                errorField.text(
                    t('Необходимо указать ссылку на видео')
                );
				this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input').addClass('input-error-portfolio');
                this.youtubeRemoveEmptyFiels();
			}
            if ((this.page == this.PAGE_KWORK || this.PAGE_MY_PORTFOLIO) && this.portfolioType == this.PORTFOLIO_TYPE_PHOTO && videosCount < 1) {
                let item = this.block.find('.' + this.classes.videos).find('.youtube-field:first-child input');
                let errorField = item.parent().find('.portfolio-error');
                errorField.text('');
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
	updateAll() {
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

	moveToError() {
		let errorBlock = this.errorFields[0];
		if(errorBlock) {
			let errorPosition = this.block.scrollTop() + errorBlock.offset().top - this.block.offset().top;
			let screenToErrorPosition = errorPosition - $(window).height() / 2;
			this.block.animate({scrollTop: screenToErrorPosition}, 200);
		}
	}

	/**
	 * Сохранение портфолио
	 * @param saveForDraft boolean При сохранении черновика не валидируем форму.
	 */
	save(saveForDraft = false) {
		this.updateAll();
        this.getErrors();
        let photoError = false;
        let videoError = false;
		if (saveForDraft === false) {
			this.validateAll(true);

			let hasErrors = false;

            $.each(this.errorFields, (k, v) => {
                if (v.length > 0) {
                	if(v.selector.includes("youtube-field")){
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
                }  else if (this.portfolioType == 'video') {
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

        if (!this.errorFields.length){
            this.portfolioItem.html.removeClass('error');
            this.portfolioItem.html.find('.draggable-block_error').text('');
        }

		// Превью обложки
		let coverPreview = this.getImgCropper();
		if (coverPreview) {
			this.portfolio.cover.url = coverPreview;
		}

		// Удаляем обложку от видео, если выбрана обложка другого типа
		if (this.portfolio.cover.type != this.COVER_IMAGE_TYPE_VIDEO) {
			delete this.portfolio.cover.urlFromVideo;
		}

		if (this.page === this.PAGE_KWORK) {
			this.saveSucess(saveForDraft);
		} else {
			this.setDefaultTitle();
			this._lockSaveBtn();

			let portfolioData = this.portfolioItem.getData(this.portfolio);
			if (this.orderId) {
				portfolioData["order_id"] = this.orderId;
			}

			let formData = new FormData();
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
				complete: (jXhr, status) => {
					var rj = {};
					try {
						rj = JSON.parse(jXhr.responseText);
					} catch(e) {}
					if ('success' in rj && rj.success == true) {
						if (this.reloadAfterSave == true) {
							window.location = window.location.href;
							return;
						}

						if (this.page == this.PAGE_ORDER && !this.portfolio.id) {
							window.location = window.location.href;
						} else if (this.page == this.PAGE_MY_PORTFOLIO) {
							this.updatePortfolioCard(rj.data.id);
							this.saveSucess();
						} else {
							this.saveSucess();
						}
					} else {
						this.portfolioItem.applyErrors(rj.data);
						this.validateAll();
						this.moveToError();
						this._unlockSaveBtn();
					}
				}
			});

		}

		if (this.additionalData && this.additionalData.workNum) {
			this.additionalData.workNum++;
		}
	}

	saveSucess(saveForDraft = false) {
		// Если сохранение черновика и работа пустая, то не сохраняем
		if (!this.portfolio.draftHash && !this.portfolio.id) {
			this.portfolio.draftHash = Date.now();
		}
		if (saveForDraft === true && !this.portfolio.title && !this.portfolio.images.length
			&& !this.portfolio.videos.length && this.portfolio.cover.hash === null) {
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
	countCharacters(item) {
		if (item) {
			this._countCharacters(item);
		} else {
			$(this.block).find('.' + this.classes.fieldCounter).each((k, v) => {
				this._countCharacters($(v));
			});
		}
	}

	/**
	 * Добавление поля youtube
	 * 
	 * @param {string} value Ссылка на видео
	 */
	youtubeAdd(value = '') {
		// Должно работать только для полей ручного наполнения
		let focusInput = this.block.find('.' + this.classes.videos).find('input:focus');
		if (focusInput.length) {
			let focusInputValue = focusInput.val();
			if (Youtube.isUrl(focusInputValue) == false) {
				return;
			}
		}
		// end

		var videoContent = this.block.find('.' + this.classes.videos);
		var fields = this.block.find('.' + this.classes.videos).find('.' + this.classes.fieldYoutube);
		var fieldsEmpty = videoContent.find('.' + this.classes.fieldYoutube + ' input').filter(function() {
			return this.value === '';
		});
		
		if (fields.length >= this.MAX_YOUTUBE || fieldsEmpty.length > 0) {
			return false;
		}

		videoContent.append(`
			<div class="` + this.classes.fieldYoutube + `">
				<input type="text"
					 class="input styled-input f14 wMax"'
					 placeholder="` + t("Ссылка на видео с YouTube") + `"
					 value="` + value + `" />
				<div class="js-youtube-remove youtube-remove"><i class="kwork-icon icon-close"></i></div>
				<div class="portfolio-error"></div>
			</div>
		`);

		this.youtubeVisibleRemoveBtn();
	}

	/**
	 * Удаление поля youtube
	 * 
	 * @param {*} item Элемент для удаления (input)
	 */
	youtubeRemove(item) {
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
			let errorField = item.parent().find('.portfolio-error');
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
	youtubeEvents(item) {
		// Убираем дубликаты
		this.youtubeRemoveDuplicate(item);

		// Показывать/скрывать кнопку удаления
		this.youtubeVisibleRemoveBtn();

		// Валидация
		this.validateVideos(null, item);

        this.updateYoutubeCover();
	}

	/**
	 * Добавить превью ютуб ролика в нарезку если не загружена обложка и тип портфолио "видео"
	 * 
	 * показывать превью видео в мниатюрках, если после добавления видео загрузили обложку или изображения портфолио
	 */
	updateYoutubeCover() {

		if (
			(this.portfolio.images.length || this.portfolio.cover.urlDefault) 
			&& !this.portfolio.cover.urlFromVideo
		) {
			return;
		}

		delete this.portfolio.cover.urlFromVideo;

		if (!this.isTypeVideo() || this.frontendErrors.videos.length) {
			this.selectRandomCover();
			return;
		}

		let items = this.block.find('.' + this.classes.fieldYoutube + ' input');
		
		if (items.length === 1 && !items.val()) {
			this.selectRandomCover();
			return;
		}
		let value = $(items[0]).val();
		let thumb = Youtube.thumb(value, 'max');
		// превью видео проверяется на наличие нужного размера и после этого добавляется в нарезку
		this.setYoutubeThumb(thumb);
	}

	/**
	 * Превью видео проверяется на наличие нужного размера
	 * @param thumb
	 */
	setYoutubeThumb(thumb) {
		let img = new Image();
		img.onload = () => {
			let thumb;
			
			if (img.width > this.MIN_WIDTH_CROP_BOX && img.height > this.MIN_HEIGHT_CROP_BOX) {
				thumb = img.src;
			}
			this.showYoutubeCoverThumb(thumb);
		};
		img.src = thumb;
	}

	/**
	 * Превью видео добавляется в нарезку
	 * @param thumb
	 */
	showYoutubeCoverThumb(thumb) {

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
	youtubeRemoveDuplicate(item) {
		if (!item || item.length == 0) {
			return false;
		}

		let value = item.val();
		let tplValArray = [];

		if (value == '') {
			return null;
		}

		let items = this.block.find('.' + this.classes.fieldYoutube + ' input');
		items.not(item).each((k, v) => {
			let val = $(v).val();
			if (val != '') {
				tplValArray.push(val)
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
	youtubeVisibleRemoveBtn() {
		let items = $('.' + this.classes.videos + ' .' + this.classes.fieldYoutube + ' input');

		items.each((k, v) => {
			let value = $(v).val();
			let removeBtn = $(v).closest('.' + this.classes.fieldYoutube).find('.' + this.classes.youtubeRemove);

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
	youtubeRemoveEmptyFiels(withoutAdd=false) {
		let items = $('.' + this.classes.videos + ' .' + this.classes.fieldYoutube + ' input');

		items.each((k, v) => {
			let input = $(v);
			let value = input.val();
			let field = input.closest('.' + this.classes.fieldYoutube);
			let isFocus = input.is(':focus');

			if (value.length == 0 && !isFocus) {
				field.remove();
			}
		});

        if(!withoutAdd) {
            this.youtubeAdd();
        }
	}

	/**
	 * Логика расчета количества символов input/textarea
	 * 
	 * @param {*} item Элемент для подстчета кол-ва знаков
	 */
	_countCharacters(item) {
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
	_youtubeValidation(input, pos, saveCheck) {
		if (!input) {
			return;
		}

		if (!window.Youtube) {
			console.error('Youtube is not defined ("youtube-thumbnail.js")');
			return;
		}
		let val = input.val().trim();
        let errorField = input.parent().find('.portfolio-error');

		if(!val.length > 0){
            input.removeClass(this.classes.inputClassError);
            this.frontendErrors.videos = [];
            errorField.text('');
            return;
		}

		let urlCollision = false;

		if (this.checkVideoUrl) {
			urlCollision = !this.checkVideoUrl(val, pos);
		}

		if (pos in (this.portfolioItem.backendErrors.video || {})) {
			input.addClass(this.classes.inputClassError);
			errorField.html(this.portfolioItem.backendErrors.video[pos]);
			return false;
		} else if (urlCollision) {
			input.addClass(this.classes.inputClassError);
			errorField.text(
				t('Данное видео уже загружено в рамках другой работы')
			);
			return false;
		} else if (val && !Youtube.isUrl(val)) {
			input.addClass(this.classes.inputClassError);
            errorField.text(
                t('Указанная ссылка не является ссылкой на видео с youtube.com')
            );
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Валидация данных
	 */
	validateAll(saveCheck) {
		this.validateTitle();
		this.validateCategory();
		this.validateAttributes();
		this.validateKwork();
		this.validateCover();
		this.validateImages();
		this.validateVideos(saveCheck);
	}

	updateSaveButton() {
		if (this.isFormValid()) {
			this._unlockSaveBtn();
		} else {
			this._lockSaveBtn();
		}
	}

	/**
	 * Валидация данных
	 */
	isFormValid() {
		if (!this.sortableContainer.isReady()) {
			return false;
		}
		return true;
	}

	/**
	 * Заблокировать кнопку "Сохранить"
	 */
	_lockSaveBtn() {
		var btn = this.block.find('.' + this.classes.btnSave);

		btn
			.attr('disabled', 'disabled')
			.addClass(this.classes.btnDisabled);
	}

	/**
	 * Разблокировать кнопку "Сохранить"
	 */
	_unlockSaveBtn() {
		var btn = this.block.find('.' + this.classes.btnSave);

		btn
			.removeAttr('disabled')
			.removeClass(this.classes.btnDisabled);
	}

	/**
	 * Открытие модального окна
	 */
	_modalShow() {
		this.confirmAtClose = true;

		this.setTitleErrors();

		this.block.modal('show');
	}

	/**
	 * Закрытие модального окна
	 */
	_modalHide() {
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
	_cropImgTpl(url = '') {
		return '<div class="cover-image__cropper"><img src="' + url + '"></div>';
	}

	/**
	 * Отрисовать список Категорий
	 * 
	 * @param {*} currentId Выбрать заказ из списка
	 */
	renderParentCategories(currentId = null) {
		let selectCategories = this.block.find('.' + this.classes.selectParentCategories);
		selectCategories.html('');

		let categories = this.additionalData.parentCategories;

		selectCategories.append(
			'<option disabled="" selected="selected" hidden="">'
				+ t('Выберите категорию')
			+ '</option>'
		);

		$.each(categories, (k, v) => {
			selectCategories.append(
				'<option value="' + v.CATID + '" data-parent="' + v.parent + '">' + v.name + '</option>'
			);
		});

		this.selectOption(selectCategories, currentId);
		
		// обновление списка для chosen селекта
		selectCategories.trigger('chosen:updated');
	}

	/**
	 * Отрисовать список подкатегорий
	 * 
	 * @param {*} currentId Выбрать заказ из списка
	 */
	renderCategories(currentId = null) {
		let selectCategories = this.block.find('.' + this.classes.selectCategories);
		let parentCategorieId = this.block.find('.' + this.classes.selectParentCategories).find('option:selected').val();
		selectCategories.html('');

		let categories = this.additionalData.categories;

		let categoriesCount = 0;
		$.each(categories, (k, v) => {
			if (v.parent != parentCategorieId) {
				return true;
			}

			selectCategories.append(
				'<option value="' + v.CATID + '" data-parent="' + v.parent + '">' + v.name + '</option>'
			);

			categoriesCount++;
		});
		if (categoriesCount < 1) {
			selectCategories.parent().addClass('empty-list');
		} else {
			selectCategories.parent().removeClass('empty-list');
		}
		
		this.selectOption(selectCategories, currentId);

		// обновление списка для chosen селекта
		selectCategories.trigger('chosen:updated');
	}

	/**
	 * Отрисовать список кворков
	 * 
	 * @param {*} kworks Список кворков
	 * @param {*} currentId Выбрать заказ из списка
	 */
	renderKworks(kworks = null, currentId = null) {
		let selectKwork = this.block.find('.' + this.classes.selectKwork);

		selectKwork.html(
			'<option value="null">' + t('Не выбрано') + '</option>'
		);

		if ($.isEmptyObject(kworks)) {
			this.fieldKworks.hide();
		} else {
			this.fieldKworks.show();

			$.each(kworks, (k, v) => {
				this.kworksList[parseInt(v.PID)] = v;
				selectKwork.append(
					'<option value="' + v.PID + '">' + v.gtitle + '</option>'
				);
			});
		}

		this.selectOption(selectKwork, currentId);

		// обновление списка для chosen селекта
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
	renderOrders(orders = {}, currentId = null, hasOrders = false, isFull = false) {
		let selectOrder = this.block.find('.' + this.classes.selectOrder);

		if (isFull) {
			this.block.find('.' + this.classes.selectOrder).parent().addClass('loaded');
		} else {
			this.block.find('.' + this.classes.selectOrder).parent().removeClass('loaded');
		}

		selectOrder.html(
			'<option value="null">' + t('Не выбрано') + '</option>'
		);

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
				$.each(orders, (k, v) => {
					this.ordersList[parseInt(v.OID)] = v;

					let dateDone = new Date(v.date_done).toLocaleString() || null;
					dateDone = dateDone ? t('принят:') + ' ' + dateDone: '';
		
					let payerUserName = v.payerUsername || null;
					payerUserName = payerUserName ? t('покупатель:') + ' ' + payerUserName : '';
		
					let separete = (dateDone && payerUserName) ? '; ' : '';
		
					let value = v.kwork_title
						+ ' ('
						+ dateDone
						+ separete
						+ payerUserName
						+ ')';
		
					selectOrder.append(
						'<option' + (v.OID == this.portfolio.order_id ? ' selected' : '') + ' value="' + v.OID + '">' + value + '</option>'
					);
				});
			}
		}
		
		this.selectOption(selectOrder, currentId);
		
		// обновление списка для chosen селекта
		selectOrder.trigger('chosen:updated');
	}

	/**
	 * Выбрать опцию из выпадающего списка
	 * 
	 * @param {*} select Элемент селекта
	 * @param {int|string} value Выбор по значению
	 */
	selectOption(select = null, value = null) {
		if (!select || !value) {
			return false;
		}

		select
			.find('option')
			.prop('selected', false)
			.removeAttr('selected');

		select
			.find('option[value="' + value + '"]')
			.prop('selected', true);
	}

	/**
	 * Получить список заказов по кворку
	 * 
	 * @param {*} kworkId 
	 * @param {*} successCallback 
	 */
	getOrders(kworkId = null, successCallback) {
		let selectOrder = this.block.find('.' + this.classes.selectOrder);

		this.startSelectLoader(selectOrder);
		if (this.getOrdersXhr) {
			this.getOrdersXhr.abort();
		}
		this.getOrdersXhr = $.ajax({
			type: 'POST',
			url: '/portfolio/load_form_orders',
			data: {
				kworkId: kworkId,
			},
			dataType: 'json',
			success: (response) => {
				this.stopSelectLoader(selectOrder);

				if (response.success) {
					if (successCallback) {
						successCallback(response.data);
					}
				} else {
					// TODO view error
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
	getFullOrders(kworkId = null, successCallback) {
		let selectOrder = this.block.find('.' + this.classes.selectOrder);

		this.startSelectLoader(selectOrder);
		if (this.getOrdersXhr) {
			this.getOrdersXhr.abort();
		}
		this.getOrdersXhr = $.ajax({
			type: 'POST',
			url: '/portfolio/load_form_orders_full',
			data: {
				kworkId: kworkId,
			},
			dataType: 'json',
			success: (response) => {
				this.stopSelectLoader(selectOrder);

				if (response.success) {
					if (successCallback) {
						successCallback(response.data);
					}
				} else {
					// TODO view error
				}
			}
		});
	}

	/**
	 * Получить список кворков
	 * 
	 * @param {*} successCallback
	 */
	getKworks(successCallback) {
		if (this.kworksOrdersXhr) {
			this.kworksOrdersXhr.abort();
		}
		
		let categoryId = this.portfolio.category_id;
		let attributesId = this.portfolio.attributes_ids;
		
		if (!categoryId) {
			return false;
		}
	
		let selectKwork = this.block.find('.' + this.classes.selectKwork);
		let selectOrder = this.block.find('.' + this.classes.selectOrder);

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
			success: (response) => {
				this.stopSelectLoader(selectKwork);
				this.stopSelectLoader(selectOrder);

				if (response.success) {
					if (successCallback) {
						successCallback(response.data);
					}
				} else {
					// TODO view error
				}
			}
		});
	}

	/**
	 * Запуск лоадера для селекта
	 * 
	 * @param {*} select Селект
	 */
	startSelectLoader(select) {
		this.stopSelectLoader(select);
		let parent = select.parent();
		let chosen = parent.addClass('empty-list');
		
		parent.append(
			'<div class="js-loader select-loader">'
				+ '<img src="' + Utils.cdnImageUrl("/ajax-loader.gif") + '">' + t('Загрузка...')
			+ '</div>'
		);
	}

	/**
	 * Останавливаем лоадер для селекта
	 * 
	 * @param {*} select Селект
	 */
	stopSelectLoader(select) {
		let parent = select.parent();
		let loader = parent.find('.js-loader');
		loader.remove();
		parent.removeClass('empty-list');
	}

	/**
	 * Запрос атрибутов с сервера
	 * 
	 * @param int[] ids Идентификаторы
	 * @param function callback Колбэк после загрузки всех данных
	 */
	requestAttributes(ids, callback) {
		if (ids.length <= 0) {
			callback();
			return;
		}
		let id;
		// Выбираем все загруженные атрибуты
		// Ищем в массиве уже имеющийся id атрибута
		let $inputs = this.block.find('.' + this.classes.attributes + ' input');
		$($inputs).each(function() {
			let val = parseInt($(this).attr('value'));
			let key = ids.indexOf(val);
			if(key != -1) {				
				id = ids.splice(key, 1)[0];
				return false;
			}
		});
		// Если не нашли атрибу, берем первый
		if(!id) {
			id = ids.shift();
		}
		if(!id && !this.portfolio.category_id) {
			callback();
			return;
		}
		let data = {
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
			success: (response) => {
				if (response.success) {					
					let $appendBlock = this.block.find('.' + this.classes.attributes + ' input[value=' + id + ']').closest('.js-field-block').children('.js-attribute-section-block');
					if(!$appendBlock.length) {
						$appendBlock = this.block.find('.' + this.classes.attributes);
					}
					$appendBlock.append(response.html);
					
					this.requestAttributes(ids, callback);
				} else {
					// TODO view error
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
	renderAttributes(level, attributeId) {
		this.attributesLoaded = false;
		let attributes;
		if (level) {
			attributes = [this.portfolio.attributes_ids[this.portfolio.attributes_ids.length - 1]];
		} else {
			attributes = [null].concat(this.portfolio.attributes_ids || []);
		}
		if(attributeId) {
			attributes = [attributeId];
		}
		this.requestAttributes(attributes, () => {			
			this.block.find('.js-field-block').each((k, v) => {
				$(v).attr('data-level', k + 1);
			});
			if (this.portfolio.attributes_ids) {
				$.each(this.portfolio.attributes_ids, (k, v) => {
					this.block.find('.js-field-block input[value="' + v + '"]').prop('checked', true);
				});
			}
			this.updateAttributes();
			this.validateAttributes();
			this.attributesLoaded = true;
		});
	}

	/**
	 * Разница объектов
	 * 
	 * @param {*} obj1
	 * @param {*} obj2
	 */
	getObjectChanges(obj1, obj2) {
		let changes = {};

		for (var prop in obj2) {
			if (!obj1 || obj1[prop] != obj2[prop]) {
				if (typeof obj2[prop] == "object") {
					let c = this.getObjectChanges(obj1[prop], obj2[prop]);
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
	sortInArray(obj) {
		let sort = {};

		for (var value in obj) {
			let type = Object.prototype.toString.call(obj[value]);
			
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
	isChangedPortfolio() {
		let rawData = Object.assign({}, this.portfolioItem.data);
		let changeData = Object.assign({}, this.portfolio);

		rawData.coverHash = rawData.cover.hash;
		changeData.coverHash = changeData.cover.hash;

		delete rawData.cover;
		delete changeData.cover;

		rawData = this.sortInArray(rawData);
		changeData = this.sortInArray(changeData);

		let diff = this.getObjectChanges(rawData, changeData);

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
	allowableRangeForCrop(a, b) {
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
	updatePortfolioCard(portfolioId) {
		if (this.page != this.PAGE_MY_PORTFOLIO) {
			return false;
		}

		let portfolioList = $('.portfolio-list-collage');
		let actualPortfolioCard = '';

		if (!this.portfolio.id) {
			window.location = window.location.href;
			return;
		}

		$.ajax({
			type: 'GET',
			url: '/portfolio_card/' + portfolioId,
			dataType: 'html',
			success: (response) => {
				actualPortfolioCard = response;
				if (this.portfolio.id) {
					// edit
					let portfolioCard = portfolioList.find('.portfolio-card-collage[data-id="' + portfolioId + '"]');
					portfolioCard.replaceWith(actualPortfolioCard);
				} else {
					// new
					// вот тут что-то нужно придумать
					// с корректным добавлением и убрать дубли на пагинацию
					portfolioList.prepend(actualPortfolioCard);
					portfolioList.find('.portfolio-card-collage').last().remove();
				}
			},
			error: (e) => {
			}
		});
	}

	/**
	 * Генерация случайных показателей, для примера карточки
	 */
	generateExampleCardCnt() {
		let min = 100;
		let max = 999;
		let views = Math.round(min - 0.5 + Math.random() * (max - min + 1));
		let likes = Math.round(views * 0.6);
		let comments = Math.round(likes * 0.25);

		this.exampleCardCntViews.text(views);
		this.exampleCardCntLikes.text(likes);
		this.exampleCardCntComments.text(comments);
	}

	/**
	 * Установить название для карточки примера
	 */
	setExampleCardName() {
		let placeholder = this.exampleCardName.data('isEmpty');
		let name = this.portfolio.title || this.getPortfolioNumber() || placeholder;

		this.exampleCardName.html(name);
	}

	/**
	 * Установить категорию для карточки примера
	 */
	setExampleCardCategory() {
		let placeholder = this.exampleCardCategory.data('isEmpty');
		let categoryId = this.portfolio.category_id;
		let categoryName = '';

		if (categoryId) {
			$.each(this.additionalData.categories, (k, v) => {
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
	clearExampleCardPreview() {
		this.exampleCardThumbnail.find('img').remove();
		this.exampleCardThumbnail.css({
			overflow: 'hidden'
		});
	}
}