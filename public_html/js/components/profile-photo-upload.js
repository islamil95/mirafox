function changeProfileAvatar() {
    var MAX_PHOTO_SIZE = 10485760,
        MIN_PHOTO_WIDTH = 200,
        MIN_PHOTO_HEIGHT = 200;

    var block = $('.js-user-avatar-container'),
        fileId = block.find('input[type="hidden"]'),
        fileInput = $('#js-user-avatar-input'),
        progressBar = block.find('.js-user-avatar-progress'),
        progressBarBg = block.find('.js-user-avatar-progress div'),
        errorBlock = $('.js-add-photo_error');
    var imageData;
    var xhr = null;

    function base64ImageToBlob(str) {
        // extract content type and base64 payload from original string
        var pos = str.indexOf(';base64,');
        var type = str.substring(5, pos);
        var b64 = str.substr(pos + 8);

        // decode base64
        var imageContent = atob(b64);

        // create an ArrayBuffer and a view (as unsigned 8-bit)
        var buffer = new ArrayBuffer(imageContent.length);
        var view = new Uint8Array(buffer);

        // fill the view, using the decoded base64
        for(var n = 0; n < imageContent.length; n++) {
            view[n] = imageContent.charCodeAt(n);
        }

        // convert ArrayBuffer to Blob
        var blob = new Blob([buffer], { type: type });

        return blob;
    }

    var _checkFileSize = function (size) {
        return size <= MAX_PHOTO_SIZE;
    };

    var _checkMinImageResolution = function (img) {
        return !(img.width < MIN_PHOTO_WIDTH || img.height < MIN_PHOTO_HEIGHT);
    };

	jQuery(document).on('touchstart', '.js-file-add-button', function () {
		jQuery('.user-avatar_add-photo-button').tooltipster('disable');
		var tappedClassName = 'js-file-add-button-tapped',
			_this = jQuery(this);
		if (!_this.hasClass(tappedClassName)) {
			_this.addClass(tappedClassName);
			disableCurrentClick = true;
		} else {
			setTimeout(function () {
				_this.removeClass(tappedClassName);
			}, 500);
		}
	});

	jQuery(document).on('touchstart', function (e) {
		if (!jQuery(e.target).closest('.js-user-avatar-container').length) {
			jQuery('.js-file-add-button').removeClass('js-file-add-button-tapped');
		}
	});

    jQuery(document).on('click', '.js-file-add-button', function () {
		if (disableCurrentClick) {
			return false;
		}
        openUploadDialogue();
    });

    function openUploadDialogue() {
        fileInput.val('');
        fileInput.trigger('click');
    }

    fileInput.on('change', function (e) {
        var tg = $(e.delegateTarget);

        errorBlock.text('');

        var file = tg.get(0).files[0];

        //Проверяем вес картинки
        if (!_checkFileSize(file.size)) {
            errorBlock.text(t('Размер файла не должен превышать 10 МБ'));
            return false;
        }

        //Кладем картинку в canvas
        var reader = new FileReader();
        var canvas = document.getElementById('js-user-avatar-canvas');
        var ctx = canvas.getContext('2d');

        function scaleToFill(img) {
            // get the scale
            var scale = Math.max(canvas.width / img.width, canvas.height / img.height);
            // get the top left position of the image
            var x = (canvas.width / 2) - (img.width / 2) * scale;
            var y = (canvas.height / 2) - (img.height / 2) * scale;
            ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

            imageData = canvas.toDataURL({
                format: 'jpeg',
                quality: 0.8
            });

            $(document).trigger('conversionCompleted', imageData);
        }

        reader.onload = function (e) {
            var img = new Image();
            img.onload = function () {
                canvas.width = 200;
                canvas.height = 200;
                //Проверяем минимальные размеры картинки
                if (!_checkMinImageResolution(img)) {
                    errorBlock.text(t('Размер изображения должен быть не меньше {{0}}х{{1}} пикселей.', [MIN_PHOTO_WIDTH, MIN_PHOTO_HEIGHT]));
                    return false;
                }
                scaleToFill(this);
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(file);
    });

    $(document).on('conversionCompleted', function() {
        block.addClass('loading');
        progressBarBg.css({'width': '0%'});

        var data = new FormData();
        data.append('file', base64ImageToBlob(imageData));
        if(xhr) {
            xhr.abort();
        }
        xhr = $.ajax({
            xhr: function() {
                var lXhr = new window.XMLHttpRequest();
                lXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = e.loaded / e.total;
                        percentComplete = parseInt(percentComplete * 100);
                        progressBar.show();
                        progressBarBg.stop().animate({'width': percentComplete+'%'}, 100);
                    }
                }, false);
                return lXhr;
            },
            url: '/user/update_avatar',
            data: data,
            async: true,
            contentType: false,
            processData: false,
            type: 'POST',
            complete: function(jXhr, status) {
                var rj = {};
                try {
                    rj = JSON.parse(jXhr.responseText);
                } catch(e) {}

                if('success' in rj && rj.success === false) {
                    errorBlock.text(t(rj.data));
                }
                if ('success' in rj && rj.success === true) {

                    progressBar.hide();
                    $( ".js-file-add-button").mousemove(function(){
                        block.removeClass('loading');
                    });
					if($('.user-col-profile .js-user-avatar__picture').length) {
						$('.user-col-profile .js-user-avatar__picture, .logoutheader .js-user-avatar__picture').attr('src', rj.data.src);
					} else {
						$('.user-col-profile .js-user-avatar_block, .logoutheader .js-user-avatar_block').html('<img class="user-avatar__picture js-user-avatar__picture rounded" src="' + rj.data.src + '" >');
					}

                    fileId.val(rj.data);
                }
            }
        });
    });
}