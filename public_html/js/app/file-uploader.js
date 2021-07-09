var globalUploadBlock;

export class FileUploader {
	constructor(block) {
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
			incorrectType: 'Некорректный тип файла!',
		};

		this.extensions = {
			'image/jpeg': ['jpg', 'jpeg'],
			'image/gif': ['gif'],
			'image/png': ['png'],
		};
		this.mimeTypes = null;

		this.onError = () => {}
		
		if(globalUploadBlock) {
			this.input = globalUploadBlock;
		} else {
			globalUploadBlock = $('<input type="file" class="d-none" />');
			globalUploadBlock.prependTo('body');
			this.input = globalUploadBlock;
			this.input.on('change', (e) => {
				let el = $(e.target);
				el.data('loadHandler').fileUpload(e);
			});
		}
	}

	getExtensions() {
		if (!this.mimeTypes) {
			return '';
		}
		let extensions = [];
		$.each(this.mimeTypes, (k, v) => {
			if (v in this.extensions) {
				extensions = extensions.concat(this.extensions[v]);
			}
		});
		$.each(extensions, (k, v) => {
			extensions[k] = '.' + v;
		});
		return extensions.join(',');
	}
	
	upload() {
		this.input.attr('accept', this.getExtensions());
		this.input.data('loadHandler', this);
		this.input.trigger('click');
	}

	fileUpload() {
		this.abort();
		var file = this.input.get(0).files[0];
		this.input.val('');
		
		if(file.size > this.maxFileSize) {
			if(this.onError) {
				this.onError(t(this.errors.bigFilesize));
			}
			return;
		}

		if (this.mimeTypes) {
			if ($.inArray(file.type, this.mimeTypes) == -1) {
				if(this.onError) {
					this.onError(t(this.errors.incorrectType));
				}
				return;
			}
		}

		this.readPhotoForPreview(file, () => {
			this.fileUploadAjax(file);
		});
	}

	fileUploadAjax(file) {
		var data = new FormData();
		$.each(this.postData, (k, v) => {
			data.append(k, v);
		});
		data.append(this.fileName, file);
		this.xhr = $.ajax({
			xhr: () => {
				var lXhr = new window.XMLHttpRequest();
				lXhr.upload.addEventListener('progress', (e) => {
					if (e.lengthComputable) {
						let percentComplete = parseInt(e.loaded / e.total * 100);
						if(this.onProgress) {
							this.onProgress(percentComplete);
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
			complete: (jXhr, status) => {
				if(this.aborted) {
					return;
				}
				var rj = {};
				try {
					rj = JSON.parse(jXhr.responseText);
				} catch(e) {}
				if('success' in rj && rj.success == true) {
					if(this.onSuccess) {
						this.onSuccess(rj.data);
					}
				} else {
					if(this.onFail) {
						this.onFail(rj.data);
					}
				}
			}
		});
	}

	readPhotoForPreview(f, callback) {
		var reader = new FileReader();

		reader.onload = () => {
			var image = new Image();
			image.src = reader.result;
			image.onload = () => {
				if (!this.checkMinImageResolution(image)) {
					this.onError(t('Размер изображения должен быть не меньше ') + this.minImageWidth + 'x' + this.minImageHeight + t(' пикселей'));
					return;
				}
				if (!this.checkMaxImageResolution(image)) {
					this.onError(t('Размер изображения должен быть не больше ') + this.maxImageWidth + 'x' + this.maxImageHeight + t(' пикселей'));
					return;
				}
				if (this.onLoad) {
					this.onLoad(image.src);
				}
				callback();
			};
		};
		reader.readAsDataURL(f);
	};

	checkMinImageResolution(img) {
		return !(img.width < this.minImageWidth || img.height < this.minImageHeight);
	};

	checkMaxImageResolution(img) {
		return !(img.width > this.maxImageWidth || img.height > this.maxImageHeight);
	};

	abort() {
		if(this.xhr) {
			this.aborted = true;
			this.xhr.abort();
			this.aborted = false;
		}
	}
}