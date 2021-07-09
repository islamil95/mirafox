var popup_ordersList = "";
var delayedTimeout = null;
window.csDelayedResize = function() {};

$(document).ready(function () {
	let mobileForm = $('#block_message_new');
	if (!mobileForm.length) {
		return;
	}

	$('#block_message_new .box-file-list > .file-uploader__add').on('click', function() {
		window.appFilesMobile.$refs.fileUploader.select();
	});

    updateInboxPaddingBottom();

    var observe = function (element, event, handler) {
        element.addEventListener(event, handler, false);
    };

    initTextarea('mobile_message');
    function initTextarea(textarea_id) {
        var text = document.getElementById(textarea_id);
        function resize () {
            text.style.height = 'auto';
            text.style.height = text.scrollHeight + 'px';
            updateInboxPaddingBottom();
		}
		/* 0-timeout to get the already changed text */
		function delayedResize() {
			if (delayedTimeout) {
				clearTimeout(delayedTimeout);
			}
			window.setTimeout(resize, 0);
			updateInboxPaddingBottom();
		}
		
		csDelayedResize = delayedResize;
		window.csDelayedResize = csDelayedResize;

        $('#mobile_message').on('input', delayedResize);
        document.getElementById('mobile_message_form').addEventListener('submit', delayedResize);

        resize();

        $(window).resize(function () {
            delayedResize();
        });
	}

	var csMobileOverflowEnable = false;
    $(window).on('touchstart', function (e) {
        if ($(e.target).closest('#block_message_new').length === 1) {
			$('body').css('overflow', 'hidden');
			csMobileOverflowEnable = true;
        }
    });
    $(window).on('touchend', function (e) {
		if (csMobileOverflowEnable) {
			$('body').css('overflow', '');
		}
    });

    $('.box-kwork-toggle').on('click', function () {
        var kwork_block = $('#block_message');
        var message_type = $('[name="message_type"]').val();

        if (kwork_block.hasClass('m-hidden')) {
            kwork_block.removeClass('m-hidden');
            $('#block_message_new').hide();
            if (message_type === '') {
                if ($(this).hasClass('box-kwork-add-kwork')) {
                    suggestKworkToggle();
                } else {
                    suggestCustomKworkToggle();
                }
            }
            if ($(this).hasClass('box-kwork-add-kwork') === false) {
				$('#suggestCustomKworkToggle').removeClass('hidden');
				$('#suggestKworkToggle').addClass('hidden');
			}
            $('html, body').animate({scrollTop: kwork_block.offset().top - 50}, 500);
        } else {
            kwork_block.addClass('m-hidden');
        }
    });

    $(document).on('click', '#suggestCustomKworkToggle, #suggestKworkToggle', function(e) {
        if ($(window).width() < 768 && $('[name="message_type"]').val() === '') {
            $('#block_message').addClass('m-hidden');
            $('#block_message_new').show();
        }
	});
	
	$(window).resize(function () {
		updateInboxPaddingBottom();
	});
	
	function updateInboxPaddingBottom() {
		if (isMobile() || $(window).width() <= 767) {
			var messageBlockHeight = $('.block-message').outerHeight();
			$('.inbox-messages-js').css('padding-bottom', messageBlockHeight + 'px');
	
			/**
			 * в iOS есть многолетний баг с полем textarea:
			 * отступ слева при фокусе больше на 3 пикселя,
			 * поэтому уменьшаем отступ
			 */
			if (jQuery.browser.ios === true) {
				jQuery('#mobile_message').css('padding-left', '2px');
			}
		}
	}
});