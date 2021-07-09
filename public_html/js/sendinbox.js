function toggleSubmitButton(type, lock) {
	var suggestValid = true;
	var suggestPresent = false;
	var messageValid = false;
	var hasStopWord = false;
	var filesIsReady = true;
	var textField;
	var attachedFiles;
	var isMobileSend = isMobile();
	if (window.isChat) {
		isMobileSend = false;
	}
	if (!isMobileSend || isViewOfferKwork || isShowOfferCustomKwork) {
		filesIsReady = window.appFiles.ready;
		textField = $('#message_body');
		attachedFiles = window.appFiles.files;
	} else {
		filesIsReady = window.appFilesMobile.ready;
		textField = $('#mobile_message');
		attachedFiles = window.appFilesMobile.files;
	}

    var messageType = $('input[name=message_type]').val();
    if(messageType){
	    suggestPresent = true;
        if ((messageType === "offer_kwork" ||messageType === "offer_custom_kwork") && !OfferIndividualModule.validateIndividualKwork(true)){
	        suggestValid = false;
        }
    }

	if (textField.val().trim() || attachedFiles.length > 0) {
		messageValid = true;
	}

	hasStopWord = StopwordsModule._testContacts(textField.val()).length === 0;

	if ((messageValid || suggestPresent) && type !== 'disabled' && suggestValid && hasStopWord && filesIsReady && !$('.message-form-control__error').text()) {
		$('.btn-send-message').removeClass('disabled').prop('disabled', false);
	} else {
		$('.btn-send-message').addClass('disabled').prop('disabled', true);
	}
}

var csSkippingFrame = false;
function csSkipFrame() {
	csSkippingFrame = true;
	setTimeout(function() {
		csSkippingFrame = false;
	}, 0);
}

$('#message_body, #mobile_message').on('input', function () {
	if (csIsSubmitting) {
		return false;
	}
	if (!csSkippingFrame) {
		$('.message-form-control__error').text('').hide();
	}

	var messageText = $(this).val();
	var messageDesktop = 'message_body';
	var messageMobile = 'mobile_message';
	if ($(this).hasClass('control-en-alt')) {
		messageText = messageText.replace(/[А-Яа-яЁё]/g, '');
		$(this).val(messageText);
	}
	if (!window.isChat) {
		if ($(this).attr('id') === messageDesktop) {
			$('#' + messageMobile).val(messageText);
		} else {
			$('#' + messageDesktop).val(messageText);
			calc_input_text('#' + messageDesktop);
		}
	}

	toggleSubmitButton();
});
$(window).on('changeFileCount.fileUploader', toggleSubmitButton);
$(document).on('input', 'input[name="customExtraName[]"]', toggleSubmitButton);

function calc_input_text(obj) {
	if (window.isChat) {
		return false;
	}
	var text = $(obj).val();
	text = text.trim();
	var len = text.length;
	var bigLength = (len >= 3500);
	$("#message_body_len").text(len).parent().toggleClass('big-length', bigLength);
}

$('#message_body').keyup(function () {
	calc_input_text(this);
});
$('#message_body').keydown(function () {
	calc_input_text(this);
});
calc_input_text('#message_body');