import { FileUploader } from 'appJs/file-uploader.js';

class SortableCard {
	constructor() {
		this.addToListAfterLoad = true;
		this.uploadableBase64 = null;
		this.hasError = false;
		this.textError = '';

		this.html = $(`<div class="draggable-block item">
			<div class="clickable">
				<div class="image draggable-anchor">
					<div class="thumbnail-img-load">
						<div class="ispinner-lite"></div>
					</div>
					<img src="" alt="">
					<div class="progress">
						<div></div>
					</div>
				</div>
				<div class="controls">
					<a class="upload">` + t('Изменить') + `</a>
					<a class="delete">` + t('Удалить') + `</a>
				</div>
				<div class="draggable-block_error"></div>
			</div>
		</div>`);
				
		this.html.find('.draggable-anchor').on('mousedown', (e) => {
			this.onDragStart(e);
			return false;
		});
		
		// Если картинка не кропнутая то определяем ее ориентацию для нормального отображения
		this.html.find('.image').find('img').on('load', function(e) {
			let $this = $(this);
			if ($this.hasClass('isNotCrop')) {
				if ($this.get(0).width > $this.get(0).height && $this.get(0).height / $this.get(0).width < 0.665) {
					$this.addClass('isHorizontalImg');
				}	
			}
		});
	}
	
	updateImage() {
		let url = this.getImageSrc();
		if (!url) {
			return;
		}
		this.addImage(url);
	}

	addImage(url) {
		// проверяем кропнутая картинка или нет (страница портфолио)
		if (this.data.urlBig && this.data.url && (this.data.urlBig == this.data.url)) { 
			this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop'); 
		}
		// проверяем кропнутая картинка или нет (страница создание/редактирования кворка)
		else if (this.data.cover && this.data.cover.urlBig && this.data.cover.url && (this.data.cover.urlBig == this.data.cover.url)) { 
			this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop'); 
		}
		// проверяем кропнутая картинка или нет (страница создание/редактирования кворка при первом добавлении изображения)
		else if (this.data.urlBig === null && this.data.urlBig === null) {
			this.html.find('.image').addClass('uploaded').find('img').addClass('isNotCrop'); 
		}
		this.html.find('.image').addClass('uploaded').find('img').attr('src', url);
	}

	applyErrors(errors) {
        this.hasError = (errors.length > 0);
        this.backendErrors = {};
        let errorText = '';
        let errorVideoText = t('Видео требует исправлений!');
        let errorImageText = t('Исправьте ошибки в портфолио!');
        let errorVideo = false;
        let errorImage = false;
		$.each(errors, (k, v) => {
			if('position' in v) {
				if(!(v.target in this.backendErrors)) {
					this.backendErrors[v.target] = {};
				}
				this.backendErrors[v.target][v.position] = v.text;
			} else {
				if(!(v.target in this.backendErrors)) {
					this.backendErrors[v.target] = [];
				}
				this.backendErrors[v.target].push(v.text);
			}

            if (v.target =='images' || v.target =='cover') {
                errorImage = true;
            };

            if (v.target =='videos' || v.target =='video') {
                errorVideo = true;
            };

		});

        if (window.portfolioType == 'photo') {
            if (errorImage) {
                errorText='<div>'+ errorImageText +'</div>';
            }

            if (errorVideo) {
                errorText+='<div>'+ errorVideoText +'</div>';
            }
        }  else if (window.portfolioType == 'video') {
            if (errorVideo) {
                errorText='<div>'+ errorVideoText +'</div>';
            }
            if (errorImage) {
                errorText+='<div>'+ errorImageText +'</div>';
            }
        }


		this.updateErrorVisuals(errorText);
	}

    updateErrorVisuals(errorText) {
        if(this.hasError) {
            this.html.addClass('error');
            this.html.find('.draggable-block_error').html(errorText);
        } else {
            this.html.removeClass('error');
            this.html.find('.draggable-block_error').html('');
        }
    }

	showProgress() {
		this.updateProgress(0);
		this.html.addClass('loading');
	}

	hideProgress() {
		this.html.removeClass('loading');
	}

	updateProgress(percent) {
		this.html.find('.progress div').css({'width': percent + '%'});
	}
}
export class Portfolio extends SortableCard {
	constructor(data) {
		super();
		
		this.blank = false;

		this.deleteModal = $('.portfolio-delete-modal-confirm');

		this.data = {
			id: null,
			draftHash: null,
			cover: {
				id: null,
				crop: null,
				hash: null,
				url: null,
			},
			title: '',
			images: [],
			videos: [],
			description: '',
		};
		if (data) {
			$.extend(this.data, data);
			this.addToListAfterLoad = false;
		} else {
			this.blank = true;
		}

		this.backendErrors = {};

		this.updateImage();

		this.html.find('.upload').on('click', () => {
			this.onEdit();
		});

		this.html.find('.delete').on('click', () => {
			this.deleteModal.modal('show');
			
			// Подтвердить удаление
			this.deleteModal.find('.js-confirm-portfolio-delete').off().on('click', () => {
				this.portfolioDelete();
				this.deleteModal.modal('hide');
			});

			// Отмена удаления
			this.deleteModal.find('.js-continue-portfolio-delete').off().on('click', () => {
				this.deleteModal.modal('hide');
			});
		});
	}

	portfolioDelete() {
		if (window.portfolioList.modal.page != window.portfolioList.modal.PAGE_KWORK) {
			let formData = new FormData();
			formData.append('portfolio_id', this.data.id);
			formData.append('unlink', 'true');
			$.ajax({
				url: '/portfolio/delete',
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
						window.location = window.location.href;
					}
				}
			});
		} else {
			this.onDelete();
		}
	};

	getImageSrc() {
		if (this.data.cover && this.data.cover.url) {
			return this.data.cover.url;
		}
		return null;
	}

	parseBackendErrors(errors) {
		this.backendErrors = {};
		$.each(errors, (k, v) => {
			if(!v.target in this.backendErrors) this.backendErrors[v.target] = [];
			this.backendErrors[v.target] = v.text;
		});
	}

	getData(data) {
		let portfolioData = $.extend(true, {}, data || this.data);
		delete portfolioData.cover.url;
		delete portfolioData.cover.urlBig;
		delete portfolioData.cover.urlDefault;
		delete portfolioData.cover.hash;
		$.each(portfolioData.images, (k, v) => {
			portfolioData.images[k] = v.id;
		});
		return portfolioData;
	}
}

export class Image extends SortableCard {
	constructor(data) {
		super();

		this.data = {
			id: null,
			hash: null,
			url: null,
			urlBig: null,
		};
		if(data) {
			$.extend(this.data, data);
			this.addToListAfterLoad = false;
		}

		this.updateImage();
		
		this.progress = this.html.find('.progress');
		this.progressBar = this.progress.find('div');

		this.fileUploader = new FileUploader();
		this.fileUploader.url = '/portfolio/upload_image';
		this.fileUploader.fileName = 'file';
		this.fileUploader.maxSize = 10485760;
		this.fileUploader.minImageWidth = 660;
		this.fileUploader.minImageHeight = 440;
		this.fileUploader.maxImageWidth = 6000;
		this.fileUploader.maxImageHeight = 6000;
		this.fileUploader.mimeTypes = [
			'image/jpeg',
			'image/png',
			'image/gif'
		];

		if (window.kworkId && window.portfolioList.modal.page === window.portfolioList.modal.PAGE_KWORK) {
			this.fileUploader.postData.kwork_id = window.kworkId;
		}

		let hashes = window.portfolioList.getImagesHashes().concat(window.portfolioList.getCoverHashes());
		if (window.portfolioList.modal.portfolio.cover.hash) {
			hashes.push(window.portfolioList.modal.portfolio.cover.hash);
		}
		if (window.firstPhotoHash) {
			hashes.push(window.firstPhotoHash);
		}
		if (hashes.length) {
			$.each(hashes, (k, v) => {
				this.fileUploader.postData['hashes[' + k + ']'] = v;
			});
		}

		let portfolioId = window.portfolioList.modal.portfolio.id;
		if (portfolioId) {
			this.fileUploader.postData.portfolio_id = portfolioId;
		}

		this.fileUploader.onLoad = (base64) => {
			this.uploadableBase64 = base64;
			this.onLoadStart();
			this.onChangeState();
			this.updateImage();
			this.showProgress();
			if(this.addToListAfterLoad) {
				this.onLoad();
				this.onChangeState();
				this.addToListAfterLoad = false;
			}
		};
		
		this.fileUploader.onProgress = (percent) => {
			this.updateProgress(percent);
		};

		this.fileUploader.onSuccess = (data) => {
			this.data.id = data.id;
			this.data.url = this.uploadableBase64;
			this.data.urlBig = this.uploadableBase64;
			this.data.hash = data.hash;
			this.uploadableBase64 = null;
			this.onChangeState();
			this.hideProgress();
			this.onSuccess();
		};
		
		this.fileUploader.onError = (text) => {
			if(!this.addToListAfterLoad && !this.data.url) {
				this.onDelete();
				this.onChangeState();
				this.onError(text);
				return;
			}
			this.uploadableBase64 = null;
			this.onChangeState();
			this.updateImage();
			this.hideProgress();
			this.onError(text);
		};

		this.fileUploader.onFail = (text) => {
			this.fileUploader.onError(text);
		};

		this.html.find('.upload').on('click', () => {
			this.fileUploader.upload();
		});

		this.html.find('.delete').on('click', () => {
			this.fileUploader.abort();
			this.onDelete();
		});
	}

	getImageSrc() {
		if(this.uploadableBase64) {
			return this.uploadableBase64;
		} else if (this.data.url) {
			return this.data.url;
		}
		return null;
	}
}