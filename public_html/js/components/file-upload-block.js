var fileUploadBlockSetError = null;

$(document).ready(function() {

	$('.file-upload-block').each(function(k, v) {
		var xhr = null;
		var loading = false;
		var block = $(v), fileId = block.find('input[type="hidden"]'), fileInput = block.find('input[type="file"]'), progressBar = block.find('.upload-progress div'), errorBlock = $('.file-upload-block__error');
		var maxSize = block.data('maxSize');

		function openUploadDialogue() {
			fileInput.val('');
			fileInput.trigger('click');
		}
		
		function updateBlockState() {
			if(fileId.val()) {
				block.addClass('loaded');
			} else {
				block.removeClass('loaded');
			}
		}

		function setError(message) {
			errorBlock.text(message);
		}
		fileUploadBlockSetError = setError;
	
		$('.file-upload-block .file-wrapper-block-rectangle').on('click', function() {
			openUploadDialogue();
		});
	
		$('.file-upload-block .file-upload-block__upload').on('click', function() {
			openUploadDialogue();
		});
	
		$('.file-upload-block .file-upload-block__change').on('click', function() {
			openUploadDialogue();
		});
	
		$('.file-upload-block .file-upload-block__cancel').on('click', function() {
			if(xhr) {
				xhr.abort();
			}
			block.removeClass('loading');
			updateBlockState();
		});

		$('.file-upload-block .file-upload-block__delete').on('click', function() {
			fileId.val('');
			updateBlockState();
		});
	
		fileInput.on('change', function(e) {
			var tg = $(e.delegateTarget);
			
			errorBlock.text('');

			var file = tg.get(0).files[0];
			if(file.size > maxSize) {
				errorBlock.text(t('Превышен максимально допустимый размер файла'));
				return;
			}

			block.removeClass('loaded').addClass('loading');
			block.find('.upload-progress div').css({'width': '0%'});

			var data = new FormData();
			data.append('upload_files', file);
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
							block.find('.upload-progress div').stop().animate({'width': percentComplete+'%'}, 100);
						}
					}, false);
					return lXhr;
				},
				url:'/demofile-upload',
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
					block.removeClass('loading');
					if('success' in rj && rj.success === false) {
						errorBlock.text(t(rj.data));
					}
					if('success' in rj && rj.success === true) {
						fileId.val(rj.data);
					}
					updateBlockState();
				}
			});
		});

		updateBlockState();

	});

});