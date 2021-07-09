/* global requestProject, requestKwork, conversationImageUrl,
 conversationActorUserAvatar, conversationActorUserName, ordersBetweenUsers,
 dialogFilesEdit, dialogFiles, Utils, showInboxAllowModal, receiverId
*/

var formData = new FormData;
var isViewOfferKwork = false;
var isShowOfferCustomKwork = false;
var messageSending = false;
var popup_ordersList = "";
var csSubmitForm = null;
var csIsMobile = false;
var csPreloaderTimeout = null;
var csTimerPeriods = [5, 15, 30, 60];
var csErrorTimeout;
var csTimerTimeout;
var csIsSubmitting = false;
var csSubmitNow = false;
window.csSend = function() {};
window.bus.$on('forceMessageSend', function() {
	window.csSend();
});
var newMessageForm = $('#new_message');

if (!window.isChat) {
if (offer.isOrderStageTester) {
	var $stages = $('.js-stages-data');

	if ($stages.data('actor-type') === 'worker') {

		window.offerStages.init({
			actorType: $stages.data('actor-type'),
			stages: $stages.data('stages'),
			classDisableButton: $stages.data('button-disable-class'),
			turnover: turnover,
			pageType: $stages.data('page-type'),
			offer: {
				orderId: 0,
				lang: offer.lang,
				stageMinPrice: offer.stageMinPrice,
				customMinPrice: offer.customMinPrice,
				customMaxPrice: offer.customMaxPrice,
				offerMaxStages: offer.offerMaxStages,
				stagesPriceThreshold: offer.stagesPriceThreshold,
			},
			controlEnLang: controlEnLang,
		});

		window.offerStages.generationStages();
	}
}

window.OfferIndividualModule.init();
}

function csActivateMessageField() {
	if (isMobile()) {
		return;
	}
	$('#message_body').focus();
}

function csPlanError(xhr, action, formData, count) {
	var index = count || 0;
	csErrorTimeout = setTimeout(function() {
		csSendQueue = [];
		csUpdateSendQueue();
		xhr.abort();
		var time = csTimerPeriods[index] || 0;
		if (!time) {
			xhr.onload();
			return;
		}
		window.conversationApp.$refs.conversationMessagesList.setTimer(time);
		window.csSend = function() {
			if (csTimerTimeout) {
				clearTimeout(csTimerTimeout);
			}
			window.conversationApp.$refs.conversationMessagesList.setTimer(0);
			xhr.open('post', action, true);
			xhr.send(formData);
			csPlanError(xhr, action, formData, index + 1);
		}
		csTimer(time, window.csSend);
	}, 15000);
}

function csTimer(count, fn) {
	if (count < 1) {
		fn();
		return;
	}
	window.conversationApp.$refs.conversationMessagesList.setTimer(count);
	count--;
	csTimerTimeout = setTimeout(csTimer, 1000, count, fn);
}

function messageSubmit(form, isMobileSubmit) {
	csSubmitNow = true;
	setTimeout(function() {
		csSubmitNow = false;
	}, 0);
	if (csIsSubmitting) {
		return;
	}
	csIsMobile = isMobileSubmit;
	csSubmitForm = form;
	if (window.showInboxAllowModal === true && window.receiverId) {
		window.popupAllowConversations();
		return false;
	}

	showPopupOrdersList(form);
}

newMessageForm.submit(function (e) {
	e.preventDefault();
	messageSubmit($(this));
	return false;
});

$(document).on('click', '#allowConversations', function () {
	window.showInboxAllowModal = false;
	if (window.receiverId) {
		$.post("/inbox_user_allow_conversation/" + window.receiverId, "json");
	}
	showPopupOrdersList(csSubmitForm);
});

$("#suggestKworkToggle").on('click', function () {
	suggestKworkToggle(true);
});
$("#suggestCustomKworkToggle").on('click', function () {
	suggestCustomKworkToggle(true);
});


function showPopupOrdersList(form) {
	$(".js-send-order-message-radio").off("change");
	var message_type = form.find("[name=message_type]").val();
	if (!message_type && ($('.ordersList').length > 0 || popup_ordersList.length > 0)) {
		if (!popup_ordersList.length) {
			popup_ordersList = $('.ordersList').children().html();
			$('.ordersList').remove();
		}
		show_popup(popup_ordersList);
		$('.popup_content #ordersBetweenChoose').click(function () {
			var hasSelectedOrderRadio = $(".js-popup-select-order-radio").find("input:radio:checked").length > 0;
			if (hasSelectedOrderRadio) {
				ajaxSendInboxMessage(form);
			} else {
				$(".js-send-order-message-error").text(t("Выберите заказ для отправки сообщения"));
				$(".js-send-order-message-radio").on("change", function () {
					$(".js-send-order-message-error").text("");
				});
			}
			return false;
		});
	} else {
		ajaxSendInboxMessage(form);
	}
}

function ajaxSendInboxMessage(form) {
	var appFilesComponent;
	if (!csIsMobile) {
		appFilesComponent = window.appFiles;
	} else {
		appFilesComponent = window.appFilesMobile;
	}

	var isOffer = false;
	
	if (form.find("[name=message_type]").val() === 'offer_kwork' || form.find("[name=message_type]").val() === 'offer_custom_kwork') {
		isOffer = true;
	}

	var files = appFilesComponent.files;

	var messageBody = form.find('.message_body').val().trim();

	if (messageSending || (!messageBody && appFilesComponent.files.length < 1 && !isOffer)) {
		return;
	}

	if (isViewOfferKwork && offer.isOrderStageTester) {
		messageBody = form.find('[name=description]').val();
	} else if (isShowOfferCustomKwork && offer.isOrderStageTester) {
		messageBody = '';
	}

	var localMessageId = 0;
	var filesBackup = [];
	
	messageSending = true;
	var sendButton = $('.btn-send-message').eq(0);
	OfferIndividualModule.btnDisable();
	
	var sendData = new SendData();
	sendData.append('message_body', messageBody);
	sendData.append('submg', '1');
	sendData.append('msgto', form.find("[name=msgto]").val());
	sendData.append('want_id', form.find("[name=want_id]:checked").val());
	sendData.append('message_id', form.find("[name=message_id]").val());
	sendData.append('allowDialog', form.find("[name=allowDialog]").val());
	sendData.append('quoteId', form.find("[name=quoteId]").val());
	sendData.append('user_csrf', form.find("[name=user_csrf]").val());
	if (requestKwork > 0) {
		sendData.append('kworkId', requestKwork);
	}
	if (requestProject > 0) {
		sendData.append('projectId', requestProject);
	}
	if (window.offerId) {
		sendData.append('offerId', window.offerId);
	}
	
	var message_type = form.find("[name=message_type]").val();
	sendData.append('message_type', message_type);
	if (message_type === 'offer_kwork') {
		sendData.append('kwork_id', form.find("select[name=kwork_id]").val());
		sendData.append('kwork_count', form.find("select[name=kwork_count]").val());
		sendData.append('kwork_package_type', form.find("[name=kwork_package_type]").val());
		$(".js-order-extras__count").each(function () {
			sendData.append($(this).attr("name"), $(this).val());
		});
		$(".order-extras__input:checked").each(function () {
			sendData.append('gextras[]', $(this).val());
		});
		$(".order-new-extras__input").each(function () {
			sendData.append('customExtraName[]', $(this).val());
		});
		$(".js-new-extra-count").each(function () {
			sendData.append('customExtraCount[]', $(this).val());
		});
		$(".js-new-extra-days").each(function () {
			sendData.append('customExtraTime[]', $(this).val());
		});
		$(".js-new-extra-price").each(function () {
			sendData.append('customExtraPrice[]', $(this).val());
		});
	} else if (message_type === 'offer_custom_kwork') {
		sendData.append('kwork_name', form.find("#request-kwork-name").val());
		sendData.append('kwork_duration', form.find("#request-kwork-duration").val());
		sendData.append('kwork_price', form.find("#request-kwork-price").val());
		sendData.append('kwork_category', form.find("select[name=offer_category]").val());
		if (offer !== undefined && offer.isOrderStageTester) {
			sendData.append('description', form.find(".js-kwork-description").val());

			window.OfferIndividualModule.setSerialize($('.js-new-offer-radio.offer-payment-type__radio-item--active').data('type'));

			$("[name^=stages]:not(.no-serialize)").each(function () {
				var $el = $(this);
				sendData.append($el.attr("name"), $el.val());
			});
	}
	}

	sendData.append('orderId', $(".js-popup_ordersList input[name=orderId]:checked").val());

	$.each(files, function(k, v) {
		sendData.append('conversations-files[new][]', v.file_id);
	});
	var action = form.attr('action');
	var xhr = new XMLHttpRequest();
	xhr.open('post', action, true);

	var additionalData = {};
	if (window.offerData) {
		additionalData.offer = window.offerData;
	}

	var hidden = false;
	if (!isOffer && window.ordersBetweenUsers && window.ordersBetweenUsers.length > 0) {
		hidden = true;
	}
	var quoteObject = form.find('.js-message-quote').length ? {
		id: form.find("[name=quoteId]").val(),
		message: form.find(".message-quote__text").text(),
		author: {
			username: form.find(".message-quote__login").text()
		},
	} : null;

	localMessageId = window.conversationApp.$refs.conversationMessagesList.addMessage({
		message: messageBody,
		files: files,
		isOffer: isOffer,
		hidden: hidden,
		additional: additionalData,
		quote: quoteObject,
	});
	if (typeof window.appSidebar !== 'undefined' && typeof window.appSidebar.$refs.conversationFiles !== 'undefined') {
		window.appSidebar.$refs.conversationFiles.addFiles(files);
	}

	if (!isOffer) {
		filesBackup = appFilesComponent.$refs.fileUploader.saveFiles();
		appFilesComponent.$refs.fileUploader.clearFiles();
		$('#message_body').val('').trigger('input');
		// очищаем цитату
		$('.js-message-quote-wrap').empty();
		calc_input_text('#message_body');
		if (csIsMobile) {
			$('#mobile_message').val('').trigger('input');
			setTimeout(function() { 
				$('html, body').animate({'scrollTop': $(document).height()});
				$('#mobile_message').trigger('input');
			}, 0);
		}
	}
	
	var sendMessageKey = generateRandomKey(8);
	sendData.append('messageKey', sendMessageKey);

	window.conversationApp.$refs.conversationMessagesList.addSendingCount();
	csIsSubmitting = true;
	$('.message_body').addClass('deactivated').prop('disabled', true);

	xhr.onload = function () {
		csIsSubmitting = false;
		$('.message_body').removeClass('deactivated').prop('disabled', false);
		if (csErrorTimeout) {
			clearTimeout(csErrorTimeout);
		}
		// Удаляем из очереди на отправку
		$.each(csSendQueue, function(k, v) {
			if (v.data.messageKey == sendMessageKey) {
				csSendQueue.splice(k, 1);
				return false;
			}
		});
		csUpdateSendQueue();
		// Обнуляем таймаут появляения прелоадера
		if (csPreloaderTimeout) {
			clearTimeout(csPreloaderTimeout);
		}
		window.conversationApp.$refs.conversationMessagesList.setTimer(-1);
		window.conversationApp.$refs.conversationMessagesList.removeSendingCount();
		var returnedSuccess = false;
		if (xhr.status == 200) {
			var result = xhr.responseText;
			try {
				result = JSON.parse(result);
				if (result.alreadySent || result.MID) {

					// Скролл в конец переписки при добавлении предложения
					if (window.isChat) {
						chatModule.offerToggle('close');
						setTimeout(function () {
							chatModule.scrollToEnd();
						}, 200);
					}

					if (window.offerId) {
						window.history.replaceState({} , '', window.location.href.replace('&offerId=' + window.offerId, ''));
						window.offerId = null;
						window.offerData = null;
					}
					if (result.alreadySent && result.message) {
						result = result.message;
					}
					window.conversationApp.$refs.conversationMessagesList.readAllNotOwnMessagesBefore(localMessageId);
					if (result.MID) {
						result.localId = localMessageId;
						window.conversationApp.$refs.conversationMessagesList.applyServerData([result]);
					} else {
						window.conversationApp.$refs.conversationMessagesList.markAsSended(localMessageId);
					}

					if (isViewOfferKwork) {
						suggestKworkToggle(true);
					}
					if (isShowOfferCustomKwork) {
						suggestCustomKworkToggle(true);
					}
					returnedSuccess = true;

					if (isMobile() && $('.page-conversation').length > 0 && !window.isChat) {
						$('#block_message').addClass('m-hidden');
						$('#block_message_new').show();
					}
				} else if (result.notAuthorized) {
					show_login();
				} else if (result['status'] === 'error') {
					if (result.code === 201) {
						phoneVerifiedOpenModal(function () {
							$('.message-form-control__error').html('').hide();
							toggleSubmitButton("enabled");
						});
					}
					var error_message = result.response;
					if (isOffer) {
						OfferIndividualModule.showBackendError(result);
					} else {
					if (result.errors) {
						error_message += ':';
						for (var i in result.errors) {
							error_message += ' ' + result.errors[i].text;
						}
					}
					csSkipFrame();
					$('.message-form-control__error').html(error_message).show();
					toggleSubmitButton('disabled');
					}
				} else if (result['status'] === 'success' && result['redirect']) {
					location.href = result['redirect'];
				}
			} catch (e) {}
			if (returnedSuccess) {
				sendButton.val(t('Отправить'));
			}
			if ($('#message_body').val().length === 0) {
				OfferIndividualModule.btnDisable();
			}
		}
		messageSending = false;
		if (!returnedSuccess) {
			if (messageBody) {
				form.find('.message_body').val(messageBody).trigger('input').trigger('keydown');
				if (csIsMobile) {
					window.csDelayedResize();
				}
			}
			window.conversationApp.$refs.conversationMessagesList.deleteLocalMessage(localMessageId);
			if (!isOffer) {
				appFilesComponent.$refs.fileUploader.restoreFiles(filesBackup);
			}
		}
		csActivateMessageField();
	};

	csSendQueue.push({
		time: Math.floor(Date.now() / 1000),
		action: action,
		data: sendData.data,
		additional: additionalData,
	});
	csUpdateSendQueue();
	var formData = sendData.getFormData();
	xhr.send(formData);
	csPreloaderTimeout = setTimeout(function() {
		window.conversationApp.$refs.conversationMessagesList.setTimer(0);
	}, 800);
	csPlanError(xhr, action, formData);
}

function suggestKworkToggle(isClear) {
	var target = $('#suggestKworkToggle'),
		messageWrapper = jQuery('#block_message'),
		btnSendMessage = jQuery('.btn-send-message'),
		messageTextarea = jQuery('.js-message-body');

	isViewOfferKwork = !isViewOfferKwork;
	$('.js-individual-kwork').find('.js-active-kwork').toggleClass('hidden');
	$('#block_message').toggleClass('kwork-offer');
	jQuery('.message-form-control__error').text('');

	if (!isViewOfferKwork) {
		$('input[name=message_type]').val('');

		btnSendMessage.val(t('Отправить'));
		if (messageWrapper.hasClass('page-conversation__theme_one-button')) {
			messageWrapper.removeClass('active');
			btnSendMessage.addClass('hidden-tmp');
			messageTextarea.addClass('hidden-tmp');
		}

		$('#change-to-custom-kwork, #change-to-kwork-choose').addClass('hidden');
        target.text(t('Предложить кворк'));
		$('.js-show-with-offer').addClass("hidden");
		$('#mobile_message').css('height','34px');
		$('.js-sub-category-select').prop('required', false).prop('disabled', false);
		var appFilesComponent;
		if (!csIsMobile) {
			appFilesComponent = window.appFiles;
		} else {
			appFilesComponent = window.appFilesMobile;
		}
		appFilesComponent.$refs.fileUploader.clearFiles();
		toggleSubmitButton();

		if (offer.isOrderStageTester) {
			$('.js-individual-kwork-description').addClass('hidden');

			if (!window.isChat) {
				messageTextarea.removeClass('hidden');
			}
		}

		if (isClear) {
			clearSuggestKwork();
		}
	} else {
		$('input[name=message_type]').val('offer_kwork');

		btnSendMessage.val(t('Предложить'));
		if (messageWrapper.hasClass('page-conversation__theme_one-button')) {
			messageWrapper.addClass('active');
			btnSendMessage.removeClass('hidden-tmp');
			messageTextarea.removeClass('hidden-tmp');
			if (jQuery(window).width() < 768 || isMobile()) {
				jQuery('html, body').stop().animate({scrollTop: messageWrapper.offset().top - 50}, 500);
			}
		}

		$('#change-to-custom-kwork').removeClass('hidden');
        target.text(t('Отменить'));
		$('.js-show-with-offer').removeClass("hidden");
		$('.js-sub-category-select').prop('required', false).prop('disabled', false);
		$('#request-kwork-id').trigger('change');
		toggleSubmitButton();

		if (offer.isOrderStageTester) {
			$('.message_body').val('');

			$('.js-individual-kwork-description').removeClass('hidden');
			$('.js-individual-kwork-description-title').text(t('Предложение кворка'));
			$('.js-individual-kwork-description-custom').addClass('hidden');
			$('.js-individual-kwork-description-active').removeClass('hidden');

			if (!window.isChat) {
				messageTextarea.addClass('hidden');
			}
		}
	}
}

function suggestCustomKworkToggle(isClear) {
	var target = $('#suggestCustomKworkToggle'),
		messageWrapper = jQuery('#block_message'),
		btnSendMessage = jQuery('.btn-send-message'),
		messageTextarea = jQuery('.js-message-body'),
        el = $('.js-individual-kwork-description').find('.js-content-editor'),
		elText = el.siblings('.js-kwork-description');

	isShowOfferCustomKwork = !isShowOfferCustomKwork;
	$('#block_message').toggleClass('individual-offer');
	$('.js-individual-kwork').find('.js-custom-kwork').toggleClass('hidden');
	jQuery('.message-form-control__error').text('');

	if (!isShowOfferCustomKwork) {
		OfferIndividualModule.btnEnable();
        el.addClass('js-ignore-min');
        elText.addClass('js-ignore-min');
        onInputEditor(el, null, elText.val().length === 0);
		if (isClear) {
			clearSuggestKwork();
		}

		var appFilesComponent;
		if (!csIsMobile) {
			appFilesComponent = window.appFiles;
		} else {
			appFilesComponent = window.appFilesMobile;
		}
		appFilesComponent.$refs.fileUploader.clearFiles();

		$('input[name=message_type]').val('');

		btnSendMessage.val(t('Отправить'));
		if (messageWrapper.hasClass('page-conversation__theme_one-button')) {
			messageWrapper.removeClass('active');
			btnSendMessage.addClass('hidden-tmp');
			messageTextarea.addClass('hidden-tmp');
		}

		$('#change-to-custom-kwork, #change-to-kwork-choose').addClass('hidden');
        target.text(t('Индивидуальное предложение'));
		$('.js-show-with-offer').addClass("hidden");
		$('.message_body').val('');
		$('.message_body__placeholder').show();
		$('#mobile_message').css('height','34px');
		$('.js-sub-category-select').prop('required', false).prop('disabled', false);

		toggleSubmitButton();
		if (offer.isOrderStageTester) {
			$('.js-individual-kwork-description').addClass('hidden');

			if (!window.isChat) {
				messageTextarea.removeClass('hidden');
			}
			$('#app-files').removeClass('hidden');
		}

		$(document).find('.kwork-category-select').show();
	} else {
        el.removeClass('js-ignore-min');
        elText.removeClass('js-ignore-min');
        onInputEditor(el, null, elText.val().length === 0);
		$('input[name=message_type]').val('offer_custom_kwork');

		btnSendMessage.val(t('Предложить'));
		if (messageWrapper.hasClass('page-conversation__theme_one-button')) {
			messageWrapper.addClass('active');
			btnSendMessage.removeClass('hidden-tmp');
			messageTextarea.removeClass('hidden-tmp');
			if (jQuery(window).width() < 768 || isMobile()) {
				jQuery('html, body').stop().animate({scrollTop: messageWrapper.offset().top - 50}, 500);
			}
		}

		$('#change-to-kwork-choose').removeClass('hidden');
        target.text(t('Отменить'));
		$('.js-show-with-offer').removeClass("hidden");

		OfferIndividualModule.validateIndividualKwork(true);

		if (offer.isOrderStageTester) {
			$('.message_body').val('');

			$('.js-individual-kwork-description').removeClass('hidden');
			$('.js-individual-kwork-description-title').text(t('Индивидуальное предложение'));
			$('.js-individual-kwork-description-custom').removeClass('hidden');
			$('.js-individual-kwork-description-active').addClass('hidden');

			if (!window.isChat) {
				messageTextarea.addClass('hidden');
			}
			$('#app-files').addClass('hidden');
	}
}
}

function clearSuggestKwork() {
	// сброс формы и ошибок
	newMessageForm.find('.color-red').text('');
	newMessageForm.find('.js-stopwords-warning-container').text('');

	$('.js-individual-kwork .description-field.js-content-editor').html('');
	$('#message_body, #mobile_message, #request-kwork-id, #request-kwork-package-type, .js-kwork-description').val('');
	$('.message_body__placeholder').show();
	$('#new_message select').val('').trigger('chosen:updated');
	$('#new_message input:checkbox').prop('checked', false);

	$('#kwork-price-description, #offer-description-hint').text('');
	$('#addextras').html('');
	$('.order-extras, .send-request__total').hide();

	$('.js-stage-total-price-block').addClass('hidden');
	$('.js-stages').html('');

	$('.js-kwork-price').val('');
	$('.js-kwork-title').val('').siblings('.js-content-editor').html('');
	$('.js-field-input-description').val('').siblings('.js-content-editor').html('');
	$('.js-kwork-volume').val('');

	$("input[name=want_id]").prop('checked', false);

	if (offer.isOrderStageTester) {
		window.offerStages.generationStages();
	}

	$('.js-category-select').val('').trigger('chosen:updated');
	$('.js-sub-category-wrap').addClass('hidden');
	$('.js-sub-category-select').attr("disabled", false);
	$('.js-sub-category-select').trigger("chosen:updated");

	window.OfferIndividualModule.clearShowErrorForBlock();
	window.OfferIndividualModule.hidePaymentType();
	window.OfferIndividualModule.selectPaymentType(null);
}

$(document).on('focus click', '#popup-textarea, #edit-files-conversations', function () {
	var textarea = document.getElementById('popup-textarea');
	if (textarea.classList.contains('input-styled--error'))
	{
		textarea.classList.remove('input-styled--error');
	}
	if ($('#error-popup-message'))
	{
		$('#error-popup-message').detach();
	}
});

//Переключение на предожить кворк
$(document).on('click', '#change-to-kwork-choose', function () {
	suggestCustomKworkToggle();
	suggestKworkToggle();
});
//Переключение на индивидуальное предложение
$(document).on('click', '#change-to-custom-kwork', function () {
	suggestKworkToggle();
	suggestCustomKworkToggle();
});
//Показывать подкатегории выбранной категрии
$("[name=want_id]").on("click", function() {
	var wantId = parseInt($(this).val());
	if(wantId > 0) {
		$(document).find('.kwork-category-select').hide();
		$(document).find('.js-sub-category-select').attr("disabled", true);
	} else {
		$(document).find('.kwork-category-select').show();
		$(document).find('.js-sub-category-select').attr("disabled", false);
	}

	$('.js-sub-category-select').trigger("chosen:updated");
});

$(document).ready(function() {
	$('#block_message_new').on('touchmove', function(e){
		var el = $(e.target);
		if (!el.is('#mobile_message')) {
			el = el.closest('#mobile_message');
		}
		if (!el.is('#mobile_message') || !el.is(':focus')) {
			return false;
		}
	});
});

var chatModule = (function () {
	var _selectors = {
		form: '#message_body',

		footer: {
			default: '.kwork-conversation__footer',
			hasOffer: 'kwork-conversation__footer_has_offer',
		},

		offer: {
			modal: '#chat-offer-modal',
			type: '#chat-offer-type',
			paymentType: '.js-offer-payment-type',
			button: '.chat__button_offer',
		},

		messageType: '.js-message-type',
		hidden: 'hidden',

		individualMessage: {
			modal: '#chat-individual-message-modal',
			form: '.js-individual-message-form',
		},
	};

	/**
	 * Проверяет мобильную версию сайта
	 */
	var _isMobile = function () {
		return jQuery(window).width() < 768;
	};

	/**
	 * Управляет отображением кнопок инд. предложения в чате
	 * @param state
	 */
	var _offerToggle = function (state) {
		if (state === 'open') {
			if (!jQuery(_selectors.offer.type).val()) {
				suggestCustomKworkToggle();
				jQuery('#conversation-send-message-button').prop('disabled', true).addClass('disabled');
			}
			jQuery(_selectors.offer.paymentType).addClass(_selectors.hidden);
			jQuery(_selectors.footer.default).addClass(_selectors.footer.hasOffer);
			jQuery(_selectors.offer.modal).modal('show');
		} else {
			jQuery(_selectors.footer.default).removeClass(_selectors.footer.hasOffer);
			jQuery('#app-files').removeClass('hidden');
			if (jQuery(_selectors.offer.type).val() === 'offer_custom_kwork') {
				suggestCustomKworkToggle(true);
			} else if (jQuery(_selectors.offer.type).val() === 'offer_kwork') {
				suggestKworkToggle(true);
			}
			if (state === 'close') {
				jQuery(_selectors.offer.modal).modal('hide');
			}
		}
	};

	/**
	 * Управляет отображением кнопок запроса инд. кворка в чате
	 * @param state
	 */
	var _individualMessageToggle = function (state) {
		if (state === 'open') {
			if (window.showInboxAllowModal === true && window.receiverId) {
				return;
			}
			jQuery(_selectors.individualMessage.form).find(_selectors.messageType).val('individual_message');
			jQuery(_selectors.footer.default).addClass(_selectors.footer.hasOffer);
			jQuery(_selectors.individualMessage.modal).modal('show');
		} else {
			jQuery(_selectors.footer.default).removeClass(_selectors.footer.hasOffer);
			jQuery(_selectors.individualMessage.form)[0].reset();
			jQuery('#load-files-conversations .load-file__list').text('');
			jQuery('#request-kwork-duration').trigger('chosen:updated');
			jQuery('#app-files').removeClass('hidden');
			jQuery('body').removeClass('compensate-for-scrollbar-m');
			if (state === 'close') {
				jQuery(_selectors.individualMessage.modal).modal('hide');
			}
		}
	};

	/**
	 * Минимальная высота контентной части на мобильных
	 */
	var _appMinHeight = function () {
		var minHeight, paddingBottom;

		if (_isMobile()) {
			var footerHeight = jQuery('.kwork-conversation__footer').outerHeight();
			minHeight = jQuery(window).height() - (
				jQuery('.header').outerHeight() +
				jQuery('.kwork-conversation__header').outerHeight() +
				footerHeight
			);
			minHeight += 'px';
			paddingBottom = footerHeight + 'px';
		} else {
			minHeight = 'auto';
			paddingBottom = 0;
		}

		jQuery('#app').css('min-height', minHeight);
		jQuery('.chat__conversation-wrapper').css('padding-bottom', paddingBottom);
	};

	/**
	 * Скролл в конец диалога
	 */
	var _scrollToEnd = function () {
		_appMinHeight();

		var scrollingEl = jQuery('.kwork-conversation__list .scrolly-viewport');
		var scrollingPosition = jQuery('.conversation-messages-list').outerHeight(true);

		jQuery('.chat__button_submit.tooltipstered, .message-submit-button.tooltipstered').tooltipster('hide');
		if (scrollingPosition > 0) {
			if (_isMobile()) {
				scrollingEl = jQuery('html, body');
			}
			scrollingEl.animate({scrollTop: scrollingPosition}, 0);
		}
	};

	/**
	 * Прячет/показывает поле ввода сообщения
	 * @param state
	 */
	var _messageFormState = function (state) {
		if (!_isMobile() || jQuery.browser.ios) {
			return false;
		}

		var messageFormWrapper = jQuery('#message_form_wrapper'),
			conversationWrapper = jQuery('.chat__conversation-wrapper'),
			selectors = {
				withoutFooter: 'chat__conversation-wrapper_without_footer',
				blurEnabled: 'js-edit-blur-enabled',
			};

		if (state === 'hide') {
			// Прячем поле ввода сообщения при фокусе в поле редактирования
			if (conversationWrapper.hasClass(selectors.withoutFooter)) {
				return false;
			}
			conversationWrapper.addClass(selectors.withoutFooter).addClass(selectors.blurEnabled);
			messageFormWrapper.addClass(_selectors.hidden);
			_appMinHeight();
		} else if (state === 'show') {
			// Показываем обратно поле ввода сообщения, спрятанное при фокусе в поле редактирования
			if (!conversationWrapper.hasClass(selectors.blurEnabled)) {
				return false;
			}
			conversationWrapper.removeClass(selectors.withoutFooter).removeClass(selectors.blurEnabled);
			messageFormWrapper.removeClass(_selectors.hidden);
			_appMinHeight();
		}
	};

	var _init = function () {
		jQuery(window).on('resize', _.throttle(_appMinHeight, 200));

		jQuery(document).on('click', _selectors.offer.button, function () {
			if (window.actorIsPayer) {
				_individualMessageToggle('open');
			} else {
				_offerToggle('open');
			}
		});
		jQuery(_selectors.offer.modal).on('hide.bs.modal', function () {
			_offerToggle('modalClose');
		});
		jQuery(_selectors.individualMessage.modal).on('hide.bs.modal', function () {
			_individualMessageToggle('modalClose');
		});

		if (_isMobile()) {
			jQuery(window).on('scroll', window.appChatList.$refs.chatList.handleScroll);

			jQuery('.message-submit-button').tooltipster('disable');

			if (!jQuery.browser.ios) {
				jQuery(_selectors.form).on('focus', function () {
					var windowHeight = window.outerHeight > 0 ? window.outerHeight : window.innerHeight,
						windowScroll = Math.ceil(jQuery(window).scrollTop()),
						documentHeight = jQuery(document).height();
					if (windowScroll >= (documentHeight - windowHeight)) {
						setTimeout(_scrollToEnd, 250);
					}
				});
			}
		}
	};

	return {
		offerToggle: _offerToggle,
		individualMessageToggle: _individualMessageToggle,
		appMinHeight: _appMinHeight,
		scrollToEnd: _scrollToEnd,
		messageFormState: _messageFormState,
		init: _init,
	};
})();
if (window.isChat) {
	jQuery(function () {
		chatModule.init();
	});
}
