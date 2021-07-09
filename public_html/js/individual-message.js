$(document).on('click', '#allowConversationsIM', function () {
	window.showInboxAllowModal = false;
	if (window.receiverId) {
		$.post("/inbox_user_allow_conversation/" + window.receiverId, "json");
	}
	individualMessageModule.showMessage();
});

var individualMessageModule = (function () {

	// Подразумевается что заказ возможен только между одноязычными пользователями либо у русскоязычного продавца

	var $_popup = {};
	var inputBudget = {};
	var inputDuration = {};
	var inputMessage = {};
	var submitEnabled = false;
	var uploader;
	var ajaxInprogress = false;

	var messageBody = '#message_body';
	if (window.isChat) {
		messageBody = '#message_body_offer';
	}

	var _showMainPopup = function () {
		$(".popup-user").addClass("popup");
		var $popup = $('#js-popup-individual-message__container');
		$popup.show();
		$popup.find('.overlay').click(function() {
			$popup.hide();
		});

		//блокируем скролл документа на мобильных
		changeBodyScrollbar('lock');

		$_popup = $popup.find('.popup-individual-message');
		inputBudget = $_popup.find("input[name=budget]");
		inputDuration = $_popup.find("select[name=kwork_duration]");
		inputMessage = $_popup.find("textarea[name=message_body]");
		$(".js-send-order-message-radio").on("change", function () {
			if (_isOrderSelect()) {
				_unsetError();
			}
		});
		inputMessage.on("change", function () {
			if ($(this).val() !== "") {
				_unsetError();
			}
		});
		inputDuration.on("change", function () {
			_unsetError();
			if (_issetOrdersFields()) {
				$(".js-orders-between-users-block").hide(50);
			} else {
				$(".js-orders-between-users-block").show(50);
			}
		});
		inputBudget.on("change", function () {
			_unsetError();
			if (_issetOrdersFields()) {
				$(".js-orders-between-users-block").hide(50);
			} else {
				$(".js-orders-between-users-block").show(50);
			}
		});
		inputBudget.on('change keyup blur', function () {
			$(this).val($(this).val().replace(/[^0-9]/ig, ''));
		});
		if (this.dataset && this.dataset.type) {
			$_popup.find('input[name=message_type]').val(this.dataset.type);
			if (this.dataset.type !== 'individual_message') {
				$_popup.find('.orders-between-user-block').hide();
			}
		}
		$_popup.css('max-width', '600px');

	};

	var _hasMoreOneOrders = function () {
		return $_popup.find(".js-send-order-message-radio").length > 1;
	};

	var _isOrderSelect = function () {
		return $(".js-send-order-message-radio:checked").length > 0;
	};

	var _issetOrdersFields = function () {
		return inputBudget.val() !== "" || inputDuration.val() !== "";
	};

	var _setError = function (text) {
		$(".js-individual-message-error").html(text).removeClass("hidden");
	};

	var _unsetError = function () {
		$(".js-individual-message-error").html("").addClass("hidden");
	};

	var _issetMessage = function () {
		return inputMessage.val().length > 0;
	};
	var _isIndividualMessage = function () {
		return $_popup.find('input[name=message_type]').val() === 'individual_message';
	};
	var _isCorrectBudget = function () {
		var budget = parseInt(inputBudget.val());
		return isNaN(budget) || (budget >= minBudget && budget <= maxBudget);
	};

	var _setSubmitButton = function () {
		var messageBodyValue = $(messageBody).val();

		submitEnabled =
			messageBodyValue > '' &&
			StopwordsModule._testContacts(messageBodyValue).length === 0 &&
			uploader.canSave() &&
			ajaxInprogress === false;

		if (window.isChat && submitEnabled) {
			submitEnabled = $("#budget").val() !== "" && $("#request-kwork-duration").val() !== "";
		}

		if (submitEnabled) {
			$("#js-popup-individual-message__container .btn-disable-toggle").prop("disabled", false).removeClass("disabled");
		} else {
			$("#js-popup-individual-message__container .btn-disable-toggle").prop("disabled", true).addClass("disabled");
		}
	};
	var _setInitEvents = function () {
		$(document).on('click', '.js-individual-message__popup-link', function () {
			if (isPageNeedSmsVerification === 1) {
                $.ajax({
                    url: '/check_payer_phone_verification',
                    type: 'GET',
                    context: this,
                    success: function (result) {
                        if (!result.success) {
                            phoneVerifiedOpenModal();
                        } else {
							if (config && config.chat && config.chat.isFocusGroupMember) {
								if (window.hasConversation) {
									document.location.href = window.chatRedirectUrl;
									return false;
								}
								jQuery('.js-chat-redirect-url').attr('data-url', window.chatRedirectUrl);
							}

                            if (window.showInboxAllowModal === true && window.receiverId) {
                                window.popupAllowConversations("allowConversationsIM");
                                return false;
                            } else {
                                individualMessageModule.showMessage();
                            }
						}
                    }
                });
                return false;
            }

			if (config && config.chat && config.chat.isFocusGroupMember) {
				if (window.hasConversation) {
					document.location.href = window.chatRedirectUrl;
					return false;
				}
				jQuery('.js-chat-redirect-url').attr('data-url', window.chatRedirectUrl);
			}

			if (window.showInboxAllowModal === true && window.receiverId) {
				window.popupAllowConversations("allowConversationsIM");
				return false;
			} else {
				individualMessageModule.showMessage();
			}
		});

		var popup = $('#js-popup-individual-message__container');
		popup.find('.popup-close, .overlay').on('click', function () {
			popup.hide();

			//разблокируем скролл документа на мобильных
			changeBodyScrollbar('unlock');
		});
	};
	return {
		init: function () {
			uploader = new FileUploader({
				files: {},
				selector: '#load-files-conversations',
				input: {
					name: 'conversations-files'
				},
				buttonDisabled: '.js-uploader-button-disable',
			});
			_setSubmitButton();
			_setInitEvents();
		},
		showMessage: function () {
			$(".js-individual-message-form").off("submit");
			_showMainPopup.apply(document.getElementsByClassName("js-individual-message__popup-link")[0]);
			$(".js-individual-message-form").on("submit", function (e) {
				e.preventDefault();
				var formData = $(this);
				if (_hasMoreOneOrders() && !_issetOrdersFields() && _issetMessage() && !_isOrderSelect() && _isIndividualMessage()) {
					_setError(t("Выберите заказ"));
					return false;
				}
				if (!_isCorrectBudget()) {
					_setError(t('Стоимость может быть от {{0}} до {{1}}', [
						Utils.priceFormatWithSign(minBudget, offerLang),
						Utils.priceFormatWithSign(maxBudget, offerLang)
					]));
					return false;
				}
				ajaxInprogress = true;
				_setSubmitButton();
                $.ajax({
                    url: "/sendmessage",
                    method: "post",
                    data: formData.serialize(),
                    dataType: "json",
                    success: function (response) {
						if (window.isChat) {
							ajaxInprogress = false;
							if (response.status === "error") {
								_setError(response.response);
								return false;
							} else {
								if (response.MID) {
									window.conversationApp.$refs.conversationMessagesList.applyServerData([response]);
								}
								chatModule.individualMessageToggle('close');
								setTimeout(function () {
									chatModule.scrollToEnd();
								}, 200);
							}
							_setSubmitButton();
							return false;
						}
                        if (response.code && response.code == 201) {
                            phoneVerifiedOpenModal();
                            ajaxInprogress = false;
                            _setSubmitButton();
                        } else {
                            if (response.status === "success") {
                            	if (config && config.chat && config.chat.isFocusGroupMember && !response.redirect.match(/track/gi)) {
									location.href = jQuery('.js-chat-redirect-url').attr('data-url');
									return false;
								}
                                location.href = response.redirect;
                                return;// чтобы не включилась кнопка
                            } else if (response.status === "error") {
                                _setError(response.response);
                            }
                            ajaxInprogress = false;
                            _setSubmitButton();
							if (config && config.chat && config.chat.isFocusGroupMember) {
								location.href = jQuery('.js-chat-redirect-url').attr('data-url');
							}
                        }
                    },
                    error: function () {
                        ajaxInprogress = false;
                        _setSubmitButton();
                    }
                });
			});

			$(document).off("input")
				.on("input", messageBody + ", #request-kwork-duration, #budget", function () {
				_setSubmitButton();
			});
		}
	};
})();

$(window).load(function () {
	individualMessageModule.init();
	// Смысл двух нижеприведенных строчек в изменении ширины выпадающего списка в 122px, вместо 120px, которые заданы по-умолчанию
	// Если 122px не нужны, то эти строки можно удалить, т.к. chosen инициализируется в app/offer-individual.js:
	// $(_selectors.customKwork.duration).chosen(...);
	// Из-за того, что chosen инициализируется из двух мест и эта инициализация происходит асинхронно, то необходимо уничтожать предыдущую
	// инициализацию, если она была раньше текущего момента
	$("#request-kwork-duration").chosen("destroy");
	$("#request-kwork-duration").chosen({width: "122px", disable_search: true, display_disabled_options: false});
});
