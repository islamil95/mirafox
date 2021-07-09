var TRACKS_WRAPPER_ID = '#tracks-wrapper';
var TRACK_ID_PREFIX = "#track-id-";

var CS_CHECK_INTERVAL = 100;

var SLIDE_DURATION = 150;

var dialogFilesEdit;

// Ассоциативный массив по id сообщению для редактирования сообщений.
var trackEditMessages = [];

jQuery(function() {
	$(window).resize(function () {
		var w = $(window).width();
		if (w < 768) {
			$('.order-extras-mobile .order-extra-item').each(function () {
				var inputChecked = $(this).find('.styled-checkbox:checked');
				var kworkCountWrapper = $(this).find('.kwork-count-wrapper, .kwork-count-wrapper-volume');
				if (inputChecked.length) {
					kworkCountWrapper.show();
				} else {
					kworkCountWrapper.hide();
				}
			});
		}
	});
});

PullOrderTrack = function () {
	this._waitTimer = 0;

	this.orderId = 0;

	this.init();
};

PullOrderTrack.prototype._onNewTrack = function (data, pushEvent) {
	var _self = this;

	if (data["order_id"] != _self.orderId) {
		return false;
	}

	var params = {
		orderId: _self.orderId,
		lastTrackId: window.appTracks.$refs.trackList.getLastTrackId(),
		newTrackType: data['newTrackType'],
		forceReplace: data['forceReplace'],
		withData: true,
	};

	_self._getNewTracks(params, pushEvent);
};

var trackSavedElements = {};

PullOrderTrack.prototype._getNewTracks = function (params, pushEvent) {
	var _self = this;

	if(typeof window.redirectInit !== "undefined" && window.redirectInit) {
		return;
	}

	$.post("api/track/getnewtracks", params, function (response) {
		if (response.success) {
			if (!Utils.isEmptyObject(response.tracksToAdd)) {
				window.appTracks.$refs.trackList.applyContent(response.tracksToAdd);
			}

			if (!Utils.isEmptyObject(response.tracksToReplace)) {
				window.appTracks.$refs.trackList.applyContent(response.tracksToReplace);
			}

			if (!Utils.isEmptyObject(response.other)) {
				for (var key in response.other) {
					var responseHtml = response.other[key];

					switch (key) {
						case 'sidebar-order-state':
								document.dispatchEvent(new CustomEvent('sidebar-order-state', {detail: responseHtml}));
							break;

						case 'order-files':
							// Блок с файлами в сайдбаре
							renderSidebarFiles(responseHtml);

							break;

						case 'arbitrage':
								document.dispatchEvent(new CustomEvent('sidebar-arbitrage-loaded', {detail: responseHtml}));
							break;

						default:
							break;
					}

				}
			}

			var trackFormHashNew = response.formHtmlMD5;
			var form = $('#track-form');
			var isChangedForm = (typeof trackFormHash === 'undefined')
				|| (typeof trackFormHashNew === 'undefined')
				|| (trackFormHash !== trackFormHashNew);

			var savedValues = {};
			if (isChangedForm) {
				// Сохраняем текст из textarea
				$('textarea').each(function (i, e) {
					savedValues[$(e).attr("id")] = $(e).val();
				});
				// Сохраняем текущие опции
				var $oldSuggestExtras = $('<div/>').append($('#suggestExtras'));
				// Копируем кнопку отправить сообщение, там тоже может быть информация
				var $cloneTrackSendMessage = $('#track-send-message-button');

				// Сохраняем элементы, которые не должны переписываться новым html
				form.find('.safe-container').each(function(k, v) {
					var $el = $(v);
					var elName = $el.data('name');
					trackSavedElements[elName] = [];
					$el.children().each(function(k2, v2) {
						trackSavedElements[elName].push($(v2).detach());
					});
				});

				if (pushEvent !== 'refresh_track') {
					alertUpdateOrder();
				}

				form.html(response.formHtml);

				// Восстанавливаем элементы, которые не должны переписываться новым html
				var $newSafeContainers = form.find('.safe-container');
				$newSafeContainers.each(function(k, v) {
					var $el = $(v);
					var elName = $el.data('name');
					if (elName in trackSavedElements) {
						$el.html('');
						$.each(trackSavedElements[elName], function(k2, v2) {
							$el.append(v2);
						});
					}
				});

				$('.js-tooltip-update').find('.tooltipster').tooltipster(TOOLTIP_CONFIG);
				$('.stage-inprogress-confirm').find('.tooltipster').tooltipster(TOOLTIP_CONFIG);
				trackFormHash = trackFormHashNew; // запоминаем новый хэш форм

				if ('undefined' !== typeof cancelReasons) {
					trackCancelModule.init({
                        "reinit": "true",
						"initialButton": ".js-cancel-order-initial-button",
						"mainPopupTemplate": ".js-cancel-order-main-popup-template",
						"subTypePopupTemplate": ".js-cancel-order-subtype-popup-template",
						"mainPopupClass": "popup-order-cancel",
						"subTypePopupClass": "subtype-popup-order-cancel",
						"reasons": cancelReasons
					});
				}

				// Вставляем сохраненные опции
				$('#suggestExtras').replaceWith($oldSuggestExtras.find('#suggestExtras'));
				// Вставляем сохраненную кнопку
				$('#track-send-message-button').replaceWith($cloneTrackSendMessage);

				if ($('.message-submit-button').is(':visible')) {
					window.appTracks.$refs.trackList.updateVisibleFormMessage(true);
				}
			}

			Track = response.Track;
			//$(window).trigger('load');
			newContentProcess(document); // обработчики
			TrackUtils.startShow(typeForm);

			if (isChangedForm) {
				if (window.draft) {
					window.draft.skipThisFrame();
				}

				// вставляем сохранённый текст
				$('textarea').each(function (i, e) {
					var text = savedValues[$(e).attr("id")] || '';
					$(e).val(text);
					if(text !== null && text.length > 0 && $('textarea:visible').length === 0) {
						form.find('.inactive').trigger('click');
					}
				});
				form.find('textarea:visible').trigger('input');

				// Заново инициализируем текстовый редактор
				// TODO: Проверка на isFocusGroupMember на время тестирования. После - удалить проверку
				if (config.track.isFocusGroupMember) {
					initMessageBodyTrumbowyg();
				}
			}

			$('.js-online-icon').onlineWidget();
			$(pullIsTypeMessage.selectorInfoTypeMessage).hide();

			$('.js-tooltip-update').find('.tooltipster').tooltipster(TOOLTIP_CONFIG);

			// если трек в процессе отмены, то убираем сообщение о необходимости: начать работу над заказом, сдать работу, предоставить данные
			if (Track.isCancelRequest || Track.orderStatus === 3) {
				closeMessageByName('send-in-work');
				closeMessageByName('add-instructions');
				closeMessageByName('send-check-work');
			}

			var $workTimeWarning = $('.js-work-time-warning');
			if (response.isShowWorkTimeWarning) {
				$workTimeWarning.show();
				$workTimeWarning.find('.message--warning').html(response.workTimeWarning);
			} else {
				$workTimeWarning.hide();
			}
			countboxBehavior();

			getMessageTrackByOrderId(Track.orderId);
			document.dispatchEvent(new CustomEvent('new-tracks-loaded',{detail:response}));

		}
	}, "json");
};

PullOrderTrack.prototype._setEvents = function () {
	var _self = this;
	if (PULL_MODULE_ENABLE) {
		PullModule.on(PULL_EVENT_NEW_ORDER_TRACK, function (data) {
			_self._onNewTrack(data, PULL_EVENT_NEW_ORDER_TRACK);
		});
		PullModule.on(PULL_EVENT_REFRESH_TRACK, function (data) {
			_self._onNewTrack(data, PULL_EVENT_REFRESH_TRACK);
		});
		PullModule.on(PULL_EVENT_REMOVE_DRAFT, function (data) {
			if (!data || !data.order_id || !window.track || data.order_id != window.track.orderId) {
				return;
			}
			if (window.draft) {
				if(!window.draft.isManualRemove) {
					window.draft.clear();
					window.draft.clearForm();
				}
				window.draft.isManualRemove = false;
			}
		});
	}
	hintInit();
};

PullOrderTrack.prototype.init = function () {
	var _self = this;

	$(document).ready(function () {
		_self.orderId = Track.orderId;
		_self._setEvents();
	});
};


PullOrderUpdated = function () {
	this.orderId = 0;

	this.init();
};

PullOrderUpdated.prototype._onUpdated = function (data) {
	var _self = this;

	if (data["order_id"] != _self.orderId) {
		return false;
	}

	var params = {
		orderId: _self.orderId,
		action: data['action']
	};
	$.extend(params, data);

	_self._getUpdates(params);
};

PullOrderUpdated.prototype._getUpdates = function (params) {
	$.post("/track/order/getupdates", params, function (response) {
		if (response.success) {
			if (!Utils.isEmptyObject(response.orderHtml)) {

				alertUpdateOrder();

				var $orderData = $('#order-data').html(response.orderHtml);
				$orderData.find('.tooltipster').tooltipster(TOOLTIP_CONFIG);
				$orderData.find(".chosen_select").chosen({width: "108px", disable_search: true});
				newContentProcess($orderData);
				window.TrackStages.updateChangedStages();
			}
		}
	}, "json");
};

PullOrderUpdated.prototype._setEvents = function () {
	var _self = this;
	if (PULL_MODULE_ENABLE) {
		PullModule.on(PULL_EVENT_ORDER_UPDATED, function (data) {
			_self._onUpdated(data);
		});
	}
};

PullOrderUpdated.prototype.init = function () {
	var _self = this;

	$(document).ready(function () {
		_self.orderId = Track.orderId;
		_self._setEvents();
	});
};


PullTrackChanged = function () {
	this.init();
};

PullTrackChanged.prototype.init = function () {
	var _self = this;

	$(document).ready(function () {
		_self._setEvents();
	});
};

PullTrackChanged.prototype._onChanged = function (data) {
	var trackId = data['trackId'];

	var params = {
		trackId: trackId,
		orderId: Track.orderId
	};
	$.extend(params, data);

	$.post("api/track/getchangedtrack", params, function (response) {
		if (response.success) {
			var $track = $(TRACK_ID_PREFIX + trackId);
			// По изменениям работаем только с существующим на странице треком.
			if ($track.length) {
				if (!Utils.isEmptyObject(response.trackHtml)) {
					// Если потомков больше одного, значит есть вложенные треки, заменяем первого потомка
					var $resultTrack = $(response.trackHtml);
					var $children = $track.children();
					if (config && config.track && config.track.isFocusGroupMember) {
						window.appTracks.$refs.trackList.replaceTrackHTML($resultTrack.data('track-id'), response.trackHtml);
					} else {
						if ($children.length > 1 && !$('.track--compact').length) {
							$children.first().replaceWith($resultTrack.children());
							// Заменяем классы на новые
							$track.attr('class', $resultTrack.attr('class'));
						} else {
							$track.replaceWith($resultTrack);
						}
					}
					newContentProcess($(TRACK_ID_PREFIX + trackId));
				}
				// Возможен вариант удаления, пока не прорабатывался.
				else {
					$track.remove();
					readTrackService.updateTitleUnreadCount();
				}
			}

			// Блок с файлами в сайдбаре
			renderSidebarFiles(response.filesBlockHtml);
		}
	}, "json");
};

PullTrackChanged.prototype._setEvents = function () {
	var _self = this;
	if (PULL_MODULE_ENABLE) {
		PullModule.on(PULL_EVENT_TRACK_CHANGED, function (data) {
			_self._onChanged(data);
		});
	}
};


var readTrackService = new ReadTrackService({
	'itemIdPrefix': TRACK_ID_PREFIX
});

var pullOrderTrack = new PullOrderTrack();
var pullOrderUpdated = new PullOrderUpdated();
var pullTrackChanged = new PullTrackChanged();
var pullIsTypeMessage;
$(function() {
	var messageBodySelector = '.track-page__wrap-message_body .trumbowyg-editor';
	// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - удалить содержимое проверки и проверку
	if (!config.track.isFocusGroupMember) {
		messageBodySelector = '#message_body1';
	}
	pullIsTypeMessage = new PullIsTypeMessage(messageBodySelector, '#info-type-message-bottom, #info-type-message', Track.opponentId, Track.orderId);
});

var genInputId = new IDGenerator('_id');

function preAjaxSendTrackMessage(form) {
	var $submitButton = $("[type=submit]", form);

	if (!$submitButton.hasClass("btn_disabled")) {
		$submitButton.addClass('btn_disabled').prop('disabled', true);
		ajaxSendTrackMessage(form);
	}
}

var _getFileUploader = function() {
	if (isMobile() && window.appFilesMobile) {
		return window.appFilesMobile;
	}
	return window.appFiles;
}

var activateMessageField = function() {
	if (isMobile()) {
		return;
	}
	window.appTracks.$refs.trackList.updateVisibleFormMessage(true);

	$('#message_body1').focus();
}

function renderSidebarFiles(html) {
	if (html !== null) {
		$('#sidebar-files-container').html(html);
		document.dispatchEvent(new CustomEvent('sidebar-files-rerendered'));
	}
}

function ajaxSendTrackMessage(form) {
	var isPopup = form.data('ispopup');
	var uploader = isPopup === 1 ? dialogFilesEdit : null;
	var trackId = (isPopup) ? form.find('input[name=trackId]').val() : null;
	var $submitButton = form.find('[type=submit]:not(#new-desktop-submit)');
	if ($submitButton.length === 0) {
		$submitButton = $('#track-send-message-button');
	}

	if (isMobile() && $('#mobile_message').length > 0) {
		$('#message_body1').val($('#mobile_message').val());
	}

	// Обрабатываем поле сообщения, убираем теги и замняем эможи на код
	if ($('#message_body1').hasClass('trumbowyg-textarea')) {
		$('#message_body1').val(window.emojiReplacements.preSubmitMessage($('#message_body1').val()));
	}

	var sendData = new SendData();
	sendData.serializeForm(form[0]);

	if (form.find('input[name=orderId]').length === 0) {
		sendData.append('orderId', Track.orderId);
	}

	var addedOptions = ('options' in openedSuggestions)
		&& ($('[name="gextras[]"]:checked', form).length > 0
			|| $('.extrasping-settings').find('#extratime').length > 0)
				|| ('package-level' in openedSuggestions && $('#suggestExtras .js-package-price .styled-checkbox:checked').length > 0);

	var volumeInput = $('.js-volume-order');
	if (volumeInput.length && !volumeInput.val() && volumeInput.closest('.order-extra-item').find('.styled-checkbox').prop('checked')) {
		addedOptions = false;
		volumeInput.focus();
	}

	if (addedOptions) {
		sendData.append('suggestextrassubmited', 1);
	}

	sendData.append('is_ajax', true);
	sendData.append('need_ajax', true);

	// Храним список файлов для оптимистичного интерфейса
	var optimisticUiFiles = [];
	if (!uploader) {
		var filesUploader = _getFileUploader();
		optimisticUiFiles = _.clone(filesUploader.files);
		$.each(filesUploader.files, function(k, v) {
			if (v.file_is_remote) {
				sendData.append('similar_files[]', v.file_id);
			} else {
				sendData.append('file-input[new][]', v.file_id);
			}
		});
	}

	var xhr = new XMLHttpRequest();
	var action = form.attr('action');
	xhr.open('post', action, true);

	if (window.draft) {
		window.draft.clear();
		window.draft.clearForm();
	}

	if ($('#message_body1').hasClass('trumbowyg-textarea')) {
		$('#message_body1').trumbowyg('html', '');
		$('#message_body1').trigger('input');
	} else {
		$('#message_body1').val('').trigger('input');
	}

	if ($('#mobile_message').hasClass('trumbowyg-textarea')) {
		$('#mobile_message').trumbowyg('html', '');
	} else {
		$('#mobile_message').val('');
	}

	$('.js-message-quote-wrap').empty();

	if (uploader) {
		filesUploader.clear();
	} else {
		filesUploader.$refs.fileUploader.clearFiles();
	}

	if (window.messageForm) {
		window.messageForm.updateCommentBoxState();
	}

	// инициализация отптимистичный интерфейс
	var optimisticUi = new OptimisticUi(xhr, action, sendData, optimisticUiFiles, $submitButton);

	window.messageForm.setBusy(true);
	xhr.onload = function () {
		var isSuccess = false;
		$submitButton.removeClass('btn_disabled').prop('disabled', false);
		form.find('#message_body_len').text(0);
		if (xhr.status == 200) {
			var result = xhr.responseText;
			if (isPopup) {
				form.closest('.popup').find('.popup-close-js:first').trigger('click');
				if (result.tracks) {
					window.appTracks.$refs.trackList.applyContent(result.tracks);
				}
				return;
			}

			//Если были предложены существующие или кастомные опции
			if (addedOptions) {
				if (window.draft) {
					window.draft.clear();
				}
				location.reload();
				return;
			}

			if (window.draft) {
				window.draft.clearForm();
			}

			formData = new FormData();

			$('.fileInputs', form).remove();
			haveFiles = false;
			$("#track-send-message-button").removeAttr('disabled');
			try {
				result = JSON.parse(result);

				if (result.tracks) {
					activateMessageField();

					isSuccess = true;
					window.appTracks.$refs.trackList.applyContent(result.tracks, function() {
						_.forEach(result.tracks, function(v, k) {
							var trackEl = $('#track-id-' + v.id);
							if (trackEl.hasClass('report-message')) {
								removeReport();
								hintInit();
							}
						});
					});
				} else {
					switch (result['status']) {
						case "error":
							if (form.find('.js-message-error').length) {
								var errorMessage = "";
								if (result['errors'] && result['error'].length > 0) {
									errorMessage = result['errors'][0]['text'] + "\n";
								} else {
									errorMessage = result['response'];
								}
								form.find('.js-message-error').text(errorMessage);
							} else {
								alert(result['response']);
							}
							break;
						case "success":
							isSuccess = true;
							if (result['type'] == "report_update") {
								editReport(result['trackId'], true, result['message']);
							}
							break;
					}
				}

			} catch (e) {}
		}
		if (isSuccess) {
			optimisticUi.end();
		} else {
			optimisticUi.fail();
		}
		window.messageForm.setBusy(false);
	};

	var formData = sendData.getFormData();
	xhr.send(formData);

	// Запускаем оптимистичный интерфейс если это простое текстовое сообщение
	if(sendData.get('action') == "text" && !addedOptions) {
		optimisticUi.start();
	}
}

function showArbitrageCheckPopupClick(event) {
	var $this = $(this);

	var action = $this.data('track_type');
	var orderId = $this.data('order_id');

	var url = window.location.origin + "/arbitrage?track_type=" + action + "&order_id=" + orderId;
	window.location.href = url;
}

var similarData = similarData || {'baseInput': false, 'lastAction': 'none', 'orderDataCache': {}};

similarData.getFileIcoType = function (fileName) {
	var len = fileName.length;
	var symb3 = fileName.substr(len - 3, len).toLowerCase();
	var symb4 = fileName.substr(len - 4, len).toLowerCase();
	var ico = '';
	var allow = true;
	if ($.inArray(symb3, ['doc', 'xls', 'rtf', 'txt']) != -1 || $.inArray(symb4, ['docx', 'xlsx']) != -1) {
		ico = 'doc';
	} else if ($.inArray(symb3, ['zip', 'rar']) != -1) {
		ico = 'zip';
	} else if ($.inArray(symb3, ['png', 'jpg', 'gif', 'psd']) != -1 || $.inArray(symb4, ['jpeg']) != -1) {
		ico = 'image';
	} else if ($.inArray(symb3, ['mp3', 'wav', 'avi']) != -1) {
		ico = 'audio';
	} else {
		ico = 'zip';
	}
	return ico;
}

similarData.showOrderData = function (data) {

	if($('#message_body1').hasClass('trumbowyg-textarea')) {
		$('#message_body1').trumbowyg('html', data.message);
		$('#message_body1').trigger('input');
	} else {
		$('#message_body1').val(data.message).trigger('input');
	}

	$('.similar_files_data').html('');
	var $this = this;
	Object.keys(data.files).map(function (objectKey, index) {
		var fileName = data.files[objectKey].file_name;
		var html = '<div class="mb5 file-item" id="fileName_id' + objectKey + '"><i class="ico-file-' + $this.getFileIcoType(fileName) + ' dib v-align-m"></i><span class="dib v-align-m ml10">' + fileName + '</span><a class="remove-file-link" onclick="similarData.delSimilarFiles(\'_id' + objectKey + '\')"></a>\n\
		<input type="hidden" name="similar_files[]" value="' + objectKey + '">\n\
		</div>';
		$('.similar_files_data').append(html);

	});
};
similarData.loadOrderData = function (id) {
	if (typeof this.orderDataCache[id] !== 'undefined') {
		this.showOrderData(this.orderDataCache[id]);
	} else {
		var $this = this;
		$.post('/api/order/getorderprovideddata', {'orderId': id}, function (answer) {
			$this.orderDataCache[id] = answer;
			$this.showOrderData($this.orderDataCache[id]);
		}, 'JSON');
	}
};
similarData.loadInputData = function () {
	if($('#message_body1').hasClass('trumbowyg-textarea')) {
		$('#message_body1').trumbowyg('html', this.baseInput);
		$('#message_body1').trigger('input');
	} else {
		$('#message_body1').val(this.baseInput).trigger('input');
	}
	$('.similar_files_data').html('');
};
similarData.saveInputData = function () {
	if (this.lastAction === 'none') {
		this.baseInput = $('#message_body1').val();
	}
};
similarData.saveLastAction = function (action) {
	this.lastAction = action;
};
similarData.delSimilarFiles = function (id) {
	var $fileName = $('#fileName' + id);
	if ($fileName.parent().find('.file-item').length) {
		$fileName.parents('form').find('textarea').prop('required', true);
	}
	$fileName.remove();
	$('#fileInput' + id).remove();
};

var SITE = SITE || {};

SITE.fileInputs = function () {

	var $this = $(this),
		$val = $this.val(),
		valArray = $val.split('\\'),
		newVal = valArray[valArray.length - 1],
		$button = $this.parents('.file-wrapper').find('.button'),
		$fakeFile = $this.parents('.file-wrapper').find('.file-holder span');
	if (newVal !== '') {
		$('.block-help-image-js').show();
		$button.text($('.file-wrapper label').attr('data-init-text'));
		if ($fakeFile.length === 0) {
			$button.parents('.file-wrapper').prepend('<span class="file-holder"><span>' + newVal + '</span></span>');
		} else {
			$fakeFile.text(newVal);
		}
	}
};

/**
 * Cancel order
 * @type {{init, load}}
 */
var trackCancelModule = (function () {

	var _initialButton;
	var _mainPopupTemplate;
	var _subTypePopupTemplate;
	var _mainPopup;
	var _subTypePopup;
	var _chosedReasonKey;

	var _mainPopupClass;
	var _subTypePopupClass;

	var _cancelReasons;
	var _initialized;

	var _init = function (params) {
		if (_initialized && params["reinit"] != 'true') {
			return;
		}
		_initialButton = params["initialButton"];
		_mainPopupTemplate = $('body').find(params["mainPopupTemplate"]);
		_subTypePopupTemplate = $(params["subTypePopupTemplate"]);

		_mainPopupClass = params["mainPopupClass"];
		_subTypePopupClass = params["subTypePopupClass"];

		_cancelReasons = params["reasons"];

		if (!$(_mainPopupTemplate).length) {
			return;
		}
		_initialized = true;

		_setInitEvents();
	};

	var _setInitEvents = function () {
		$(document).on('click', _initialButton, function () {
			_showMainPopup.apply(this);
			$('.popup-order-cancel select').chosen({width: "100%", disable_search: true}).change(function () {
				$('.popup-order-cancel .chosen-single').removeClass('select-styled--error')
			});
		});
	};

	var _showMainPopup = function () {
		var content = _mainPopupTemplate.html();
		show_popup(content, _mainPopupClass);
		_mainPopup = $("." + _mainPopupClass);

		// fix styled-checkbox
		_mainPopup.find('.styled-checkbox').each(function(index) {
			var $input = jQuery(this);
			var $label = $input.find('+ label');

			if ($label.length > 0) {
				var id = $input.attr('id') + '-' + index;

				$input.attr('id', id);
				$label.attr('for', id);
			}
		});

		_mainPopup.find('.js-popup-cancel-order__action').val($(this).data('action'));
		_mainPopup.find("input[name='reason']").each(function () {
			$(this).attr("id", "reason-" + $(this).val());
		});
		_mainPopup.find(".js-tooltip").tooltip();

		if (_mainPopup.find('.js-popup-cancel-form__reason').length > 0) {
			$('.order-cancel-form__comment').hide();
		}

		_mainPopup.find('.js-popup-cancel-form__reason').on('change', function () {
			var val = $(this).val();

			//предлагаем перейти в арбитраж при третьей и более отмене заказа покупателем
			var isArbitrageAsk = false;
			var reasonsWithoutConfirmation = [
				'payer_ordered_by_mistake',
				'payer_time_over',
				'payer_inflated_price'
			];
			if (_mainPopup.find('#reason-payer_time_over').length) {
				reasonsWithoutConfirmation.push('payer_worker_cannot_execute_correct');
				reasonsWithoutConfirmation.push('payer_no_communication_with_worker');
			}

			if (Track.orderStatus !== 2 &&_mainPopupTemplate.hasClass('isPayer') && Track.inprogressCancelRejectCounter > 1 && reasonsWithoutConfirmation.indexOf(val) === -1) {
				_mainPopup.find('.js-inprogress-cancel-default, .popup-close-js').addClass('hidden');
				_mainPopup.find('.js-inprogress-cancel-reject').removeClass('hidden');
				_mainPopup.find('.js-cancel-order-submit').removeClass('red-btn').addClass('green-btn');
				isArbitrageAsk = true;
			}

			if (val.length > 0 && !_cancelReasons[val]["subtypes"]) {
				$('.order-cancel-form__comment').slideDown(50);
				var $textArea = $('.order-cancel-form__comment textarea');
				if ($(this).data('isPayer')) {
					_mainPopup.find('input[name=hide_all_user_kworks]').prop("checked", $(this).data('isPayerUnrespectful'));
					if (val === 'payer_other_no_guilt') {
						$textArea.addClass('js-required');
						if (isArbitrageAsk && $textArea.val().length === 0) {
							$textArea.val('\u00A0');
						}
					}
					else {
						$textArea.removeClass('js-required');
					}
				}
				else {
					$textArea.addClass('js-required');
				}
			}
			else {
				$('.order-cancel-form__comment').slideUp(50);
			}

			//показываем подробный текст подсказки продавцам
			var requestNotCorrespond = $(this).parents('.clearfix').next('.request-not-correspond_theme_popup');
			if (val.length > 0 && (val === 'worker_payer_is_dissatisfied' || val === 'worker_force_cancel')) {
				$('.request-not-correspond_theme_popup').slideUp(150);
				requestNotCorrespond.find('.request-not-correspond__title-icon').toggleClass('request-not-correspond__title-icon_rotate');
				requestNotCorrespond.children('.request-not-correspond__more-text').show();
				requestNotCorrespond.slideDown(150);
			} else {
				$('.request-not-correspond_theme_popup').slideUp(150);
			}
		});

		_mainPopup.find(".js-cancel-order-submit").on("click", _mainPopupSubmited);
	};
	var _mainPopupSubmited = function (e) {
		var chosedReasonKey = _mainPopup.find(".js-cancel-order-reason-input:checked").val();
		var chosedReason = _cancelReasons[chosedReasonKey];
		if (chosedReason && chosedReason["subtypes"]) {
			//Есть подпричины
			_chosedReasonKey = chosedReasonKey;
			_showSubTypePopup(chosedReasonKey);
		}
		else {
			//Подпричин нет
			var form = _mainPopup.find("form");
			if (checkFormModule.checkForm(form)) {
				$(this).attr("disabled", "disabled");
				$(this).addClass("disabled");
			}
			if (window.draft) {
				window.draft.clear();
			}
			form.submit();
		}
	};
	var _showSubTypePopup = function () {
		var content = _subTypePopupTemplate.html();
		show_popup(content, _subTypePopupClass + " popup-order-cancel-subtype", false);
		_subTypePopup = $("." + _subTypePopupClass);
		_subTypePopup.find(".js-subreasons").hide();
		_subTypePopup.find(".js-subreasons[data-parent-reason=" + _chosedReasonKey + "]").show();
		_subTypePopup.find("input[name='reason']").each(function () {
			$(this).attr("id", "reason-" + $(this).val());
		});
		_subTypePopup.find("input[name='reason']").on("click", _subReasonChosed);
		_subTypePopup.find(".js-tooltip").tooltip();
		_subTypePopup.find(".js-cancel-order-back").on("click", _showMainPopup);
		_subTypePopup.find(".js-cancel-order-parent-reason-input").val(_chosedReasonKey);
		//_subTypePopup.find(".js-link-back").on("click", _showMainPopup);
	};
	var _subReasonChosed = function (e) {
		_subTypePopup.find(".js-cancel-order-submit").removeClass("disabled");
		_subTypePopup.find(".js-cancel-order-submit").prop("disabled", false);
		_subTypePopup.find(".js-subreason-help").addClass("hidden");
		_subTypePopup.find(".js-subreason-help[data-subreason-help=" + e.target.value + "]").removeClass("hidden");
		if (_cancelReasons[_chosedReasonKey]["subtypes"][e.target.value]["additional"] && !_cancelReasons[_chosedReasonKey]["subtypes"][e.target.value]["allowed"]) {
			_subTypePopup.find(".js-cancel-order-additional i").text(_cancelReasons[_chosedReasonKey]["subtypes"][e.target.value]["additional"]);
		}
		else {
			_subTypePopup.find(".js-cancel-order-additional i").text("");
		}
		if (_cancelReasons[_chosedReasonKey]["subtypes"][e.target.value]["allowed"]) {
			_subTypePopup.find(".js-cancel-order-submit").show();
			_subTypePopup.find(".js-cancel-order-back").hide();
			_subTypePopup.find(".js-cancel-order-comment").show();
		}
		else {
			_subTypePopup.find(".js-cancel-order-back").show();
			_subTypePopup.find(".js-cancel-order-submit").hide();
			_subTypePopup.find(".js-cancel-order-comment").hide();
		}
		_subTypePopup.find(".popup__buttons").removeClass("hidden");
	};
	return {
		init: _init
	}
})();

var MessageFormModule = (function () {

	var _sendingComplete = function() {
		var $messageBlock = $('.js-comment-box');
		$messageBlock.data('leftButton', null);
	}

	var _openMessageForm = function (el) {
		var $messageBlock = $('.js-comment-box');
		var leftButton = null;
		var allButtons = null;
		if (typeof el == 'undefined') {
			leftButton = $messageBlock.data('leftButton');
			if (!leftButton) {
				return;
			}
			allButtons = $messageBlock.data('allButtons');
		} else {
			leftButton = $(el);
			allButtons = $(el).parent().find('.js-toggle-message-button');
		}

		if (!$messageBlock.is(":visible")) {
			$messageBlock.data('leftButton', leftButton);
			$messageBlock.data('allButtons', allButtons);
			if (Utils.isSafariBrowser()) {
				$messageBlock.show(50);
			} else {
				$messageBlock.slideDown(50);
			}
		}

		if (leftButton.data('action')) {
			$('.js-send-message-action').val(leftButton.data('action'));
			if (leftButton.data('action') == 'instruction') {
				$('#is_instruction').show();
				$('#is_text').hide();
			} else {
				$('#is_instruction').hide();
				$('#is_text').show();
			}
		}
		allButtons.addClass('inactive');
		leftButton.removeClass('inactive');
	};

	var _closeMessageForm = function(form) {
		var $messageBlock = $('.js-comment-box');
		var leftButton = $messageBlock.data('leftButton');
		if (!leftButton) {
			return;
		}
		$messageBlock.hide();
		$messageBlock.data('allButtons').removeClass('inactive');
		$messageBlock.data('leftButton').addClass('inactive');
	}

	var _init = function (params) {

	};
	return {
		init: _init,
		openMessageForm: _openMessageForm,
		closeMessageForm: _closeMessageForm,
		sendingComplete: _sendingComplete,
	}
})();

var TrackUtils = (function() {
	var _isForced = false;
	var _forceSend = function() {
		_isForced = true;
	}

	var _startShow = function(aTypeForm) {
		var ret;
		switch(aTypeForm) {
			case 'pass-work':
				ret = _startPassWork();
				break;

			case 'only-message':
				ret = _startOnlyMessage();
				break;

			default:
				ret = false;
				break;
		}

		return ret;
	};

	var _onSendMessageButtonClick = function (e) {
		if (!_isForced && $(e.target).hasClass('disabled')) {
			return false;
		}
		_isForced = false;
		TrackUtils.sendMessageFromPassWorkForm(e);
		return false;
	};

	var _onPassWorkButtonClick = function () {
		$('.js-inprogress-confirm').modal('show');
	};

	/**
	 * Отправка работы на проверку
	 * @param e
	 * @private
	 */
	var _onInprogressConfirmSubmit = function (e) {
		if (typeof(yaCounter32983614) !== 'undefined'){
			yaCounter32983614.reachGoal('START-WORK-DONE');
		}

		$('.js-inprogress-confirm').modal('hide');
		_submitWorkFromPassWorkForm(e);
	};

	var _onInprogressConfirmCheckbox = function () {
		if ($('.js-inprogress-confirm-checkbox').is(':checked')) {
			$('.js-inprogress-confirm-submit').prop('disabled', false).removeClass('disabled');
		} else {
			$('.js-inprogress-confirm-submit').prop('disabled', true).addClass('disabled');
		}
	};

	var _startPassWork = function () {
		$('div[data-form=send-message-js]').hide();
		$('div[data-form=pass-work-js]').hide();
		$('.track-form-js').hide();

		$('#track-send-message-button')
			.off('click', _onSendMessageButtonClick)
			.on('click', _onSendMessageButtonClick)
			.off('mouseenter mouseleave')
			.show();

		$('.js-track-inprogress-check-button').show();

		$('.js-track-pass-work-button')
			.off('click', _onPassWorkButtonClick)
			.on('click', _onPassWorkButtonClick)
			.show();

		// нажатие на чекбокс подтверждения, что согласен сдать работу
		$(document)
			.off('click', '.js-inprogress-confirm-checkbox', _onInprogressConfirmCheckbox)
			.on('click', '.js-inprogress-confirm-checkbox', _onInprogressConfirmCheckbox);

		// отправка работы на проверку
		$(document)
			.off('click', '.js-inprogress-confirm-submit', _onInprogressConfirmSubmit)
			.on('click', '.js-inprogress-confirm-submit', _onInprogressConfirmSubmit);

		$('.send-message-js').show();
		return true;
	};

	var _onSendMessageFromPassWorkFormButtonClick = function (e) {
		if (!_isForced && $(e.target).hasClass('disabled')) {
			return false;
		}
		_isForced = false;
		TrackUtils.sendMessageFromPassWorkForm(e);
		return false;
	};

	var _startOnlyMessage = function () {
		$('div[data-form=send-message-js]').hide();
		$('div[data-form=pass-work-js]').hide();
		$('.track-form-js').hide();

		$('#message_body1').removeClass('js-required');

		$('.js-track-pass-work-button').hide();

		$('#track-send-message-button')
			.off('click', _onSendMessageFromPassWorkFormButtonClick)
			.on('click', _onSendMessageFromPassWorkFormButtonClick)
			.show();

		$('.send-message-js').show();
		return true;
	};

	// start  pass-work
	// Сдать выполненную работу
	var _sendMessageFromPassWorkForm = function (e) {
		if (!_checkHasContent()) {
			e.preventDefault();
			return false;
		}

		var messageField = ((isMobile() && $('#mobile_message').length > 0) ? $('#mobile_message') : $('#message_body1'));
		var $passAction =$('#fm_pass_action');
		var lastAction = $passAction.val();
		var $submitButton = $("#track-send-message-button");
		$passAction.val('text');
		$passAction.closest("form").attr("action", "/track/action/text");
		$submitButton.addClass('btn_disabled').prop('disabled', true);
		if(!$submitButton.hasClass("option-request")){
			ajaxSendTrackMessage($('#form_pass_work'));
		}else{
			if($('#message_send_ajax').val() == 1) {
				ajaxSendTrackMessage($('#form_pass_work'));
			} else {
				if (window.draft) {
					window.draft.clear();
				}
				$('#form_pass_work').submit();
			}
		}
		$passAction.closest("form").attr("action", "/track/action/" + lastAction);
		$passAction.val(lastAction);

		if(messageField.hasClass('trumbowyg-textarea')) {
			messageField.trumbowyg('html', '');
			messageField.trigger('input');
		} else {
			messageField.val('').trigger('input');
		}

	};

	/**
	 * Отправка работы на проверку
	 * @param e
	 * @returns {boolean}
	 * @private
	 */
	var _submitWorkFromPassWorkForm = function (e) {
		var $passAction =$('#fm_pass_action');
		var lastAction = $passAction.val();
		$passAction.closest("form").attr("action", "/track/action/worker_inprogress_check");
		$passAction.val('worker_inprogress_check');
		if($('#message_send_ajax').val() == 1) {
			ajaxSendTrackMessage($('#form_pass_work'));
		} else {
			if (window.draft) {
				window.draft.clear();
			}
			$('#form_pass_work').submit();
		}

		$passAction.val(lastAction);
		$passAction.closest("form").attr("action", "/track/action/" + lastAction);

		if($('#message_body1').hasClass('trumbowyg-textarea')) {
			$('#message_body1').trumbowyg('html', '');
			$('#message_body1').trigger('input');
		} else {
			$('#message_body1').val('').trigger('input');
		}

		$('.js-track-pass-work-button, #suggestExtras').hide();

		// при отправке этапов на проверку принудительно обнуляем хэш формы
		trackFormHash = undefined;
	};
	// end  pass-work

	var _checkHasContent = function() {
		if (_checkHasExtraData()) {
			return true;
		}
		return _checkHasContentDesktop();
	}

	_cachedHasExtraData = null;
	_cachedHasExtraDataTimeout = null;

	var _clearExtraDataCache = function() {
		_cachedHasExtraData = null;
	}

	var _checkHasExtraData = function(cached) {
		var hasExtraData = false;
		if (cached && _cachedHasExtraData !== null) {
			hasExtraData = _cachedHasExtraData;
		} else {
			hasExtraData = (getCurrentSuggestPrice() > 0);
			if (cached) {
				if (_cachedHasExtraDataTimeout) {
					clearTimeout(_cachedHasExtraDataTimeout);
					_cachedHasExtraDataTimeout = null;
				}
				_cachedHasExtraData = hasExtraData;
				_cachedHasExtraDataTimeout = setTimeout(function() {
					_cachedHasExtraData = null
					_cachedHasExtraDataTimeout = null;
				}, 0);
			}
		}
		return hasExtraData;
	}

	var _checkHasContentDesktop = function(filesStateOnly) {
		if (window.appTracks && window.appTracks.$refs && window.appTracks.$refs.trackList.isBusy()) {
			return false;
		}
		var filesState = (!window.appFiles || window.appFiles.ready);
		if (filesStateOnly) {
			return filesState;
		} else {
			var messageBody = $('#message_body1');
			var messageBodyVal = $('#message_body1').val();
			if($('#message_body1').hasClass('trumbowyg-textarea')) {
				messageBodyVal = window.emojiReplacements.preSubmitMessage(messageBodyVal);
			}
			if (!filesState || (messageBody.length > 0 && messageBodyVal.trim() == '' && window.appFiles && window.appFiles.files.length < 1)) {
				return false;
			}
		}

		return true;
	}

	// start  Instruction

	// Отправить информацию по заказу
	var _submitInstructionForm = function (e) {
		var $passAction =$('#fm_pass_action');
		var lastAction = $passAction.val();
		$passAction.val('instruction');
		$passAction.closest("form").attr("action", "/track/action/instruction");

		if($('#message_send_ajax').val() == 1) {
			ajaxSendTrackMessage($('#form_pass_work'));
		} else {
			if (window.draft) {
				window.draft.clear();
			}
			$('#form_pass_work').submit();
		}

		$passAction.val(lastAction);
		$passAction.closest("form").attr("action", "/track/action/" + lastAction);
		$('#message_body1').val('').attr('placeholder', '').trigger('input');

		$('.request-not-correspond_theme_order-buyer:first').fadeOut(150);
		$('.track-item').addClass('tr-quotable');
		setTimeout(function(){
			$('.track-item').addClass('tr-quotable');
		}, 1000);
	};
	// end  Instruction

	var _defSubmitPassWorkForm = function() {
		if (window.sendForm) {
			if (!window.sendForm.tryToSubmitForm()) {
				return false;
			};
		}
		var $form_pass_work = $('#form_pass_work');
		ajaxSendTrackMessage($form_pass_work);
	};

	var _init = function(params) {};
	return {
		init:_init,
		forceSend: _forceSend,
		startShow: _startShow,
		sendMessageFromPassWorkForm: _sendMessageFromPassWorkForm,
		submitInstructionForm: _submitInstructionForm,
		defSubmitPassWorkForm: _defSubmitPassWorkForm,
		checkHasExtraData: _checkHasExtraData,
		clearExtraDataCache: _clearExtraDataCache,
		checkHasContent: _checkHasContent,
		checkHasContentDesktop: _checkHasContentDesktop,
		onInprogressConfirmSubmit: _onInprogressConfirmSubmit,
	}
})();

function Timer(time, containerName, startTime, stop) {
    if (this.running && !Track.isCancelRequest) {
        return;
    }
	this.time = time;

	if ($('#' + containerName)) {
		this.container = containerName;
	} else {
		return false;
	}
	this.curTime = startTime;

	function execTimer(time, stop) {
		var amount = time - this.curTime;
		if (amount <= 0) {
			setExpire();
		} else {
			setTime(amount);
			if (stop !== true) {
				this.curTime++;
				var timer = setTimeout(function () {
					execTimer(time);
				}, 1e3);
				$('#' + containerName).data('timer', timer);
                this.running = true;
			}
			else {
				var timer = $('#' + containerName).data('timer');
				if (timer) {
					clearTimeout(timer);
				}
                this.running = false;
			}
		}
	}

	function setExpire() {
		var out = "<div class='countbox-num'><div id='countbox-days1' class='clockExpire'><span></span>0</div><div id='countbox-days2' class='clockExpire'><span></span>0</div><div id='countbox-days-text'>" + t("Дней") + "</div><div class='colon'></div></div>" +
			"<div class='countbox-num'><div id='countbox-hours1' class='clockExpire'><span></span>0</div><div id='countbox-hours2' class='clockExpire'><span></span>0</div><div id='countbox-hours-text'>" + t("Часов") + "</div><div class='colon'></div></div>" +
			"<div class='countbox-num'><div id='countbox-mins1' class='clockExpire'><span></span>0</div><div id='countbox-mins2' class='clockExpire'><span></span>0</div><div id='countbox-mins-text'>" + t("Минут") + "</div><div class='colon'></div></div>" +
			"<div class='countbox-num' style='width: 137px;'><div id='countbox-secs1' class='clockExpire'><span></span>0</div><div id='countbox-secs2' class='clockExpire'><span></span>0</div><div id='countbox-secs-text'>" + t("Секунд") + "</div></div>";
		var container = document.getElementById(this.container)
		if (container)
			container.innerHTML = out;
	}

	function setTime(amount) {
		var out = "";
		var days = Math.floor(amount / 86400);
		var days1 = (days >= 10) ? days.toString().charAt(0) : '0';
		var days2 = (days >= 10) ? days.toString().charAt(1) : days.toString().charAt(0);
		amount = amount % 86400;
		var hours = Math.floor(amount / 3600);
		var hours1 = (hours >= 10) ? hours.toString().charAt(0) : '0';
		var hours2 = (hours >= 10) ? hours.toString().charAt(1) : hours.toString().charAt(0);
		amount = amount % 3600;
		var mins = Math.floor(amount / 60);
		var mins1 = (mins >= 10) ? mins.toString().charAt(0) : '0';
		var mins2 = (mins >= 10) ? mins.toString().charAt(1) : mins.toString().charAt(0);
		amount = amount % 60;
		var secs = Math.floor(amount);
		var secs1 = (secs >= 10) ? secs.toString().charAt(0) : '0';
		var secs2 = (secs >= 10) ? secs.toString().charAt(1) : secs.toString().charAt(0);
		var daysTpl = "";
		if (days > 0) { // навсякий случай сделал проверку на перспективу, но нужно будет корректировать css для вывода дней
			daysTpl = "<div class='countbox-num'><div id='countbox-days1'><span></span>" + days1 + "</div><div id='countbox-days2'><span></span>" + days2 + "</div><div id='countbox-days-text'>" + t("Дней") + "</div><div class='colon'></div></div>";
		}
		out = daysTpl + "" +
			"<div class='countbox-num'><div id='countbox-hours1'><span></span>" + hours1 + "</div><div id='countbox-hours2'><span></span>" + hours2 + "</div><div id='countbox-hours-text'>" + t("Часов") + "</div><div class='colon'></div></div>" +
			"" +
			"<div class='countbox-num'><div id='countbox-mins1'><span></span>" + mins1 + "</div><div id='countbox-mins2'><span></span>" + mins2 + "</div><div id='countbox-mins-text'>" + t("Минут") + "</div><div class='colon'></div></div>" +
			"" +
			"<div class='countbox-num' style='width: 137px;'><div id='countbox-secs1'><span></span>" + secs1 + "</div><div id='countbox-secs2'><span></span>" + secs2 + "</div><div id='countbox-secs-text'>" + t("Cекунд") + "</div></div>";
		var container = document.getElementById(this.container)
		if (container)
			container.innerHTML = out;
	}

	execTimer(this.time, stop);
}

function togglePortfolioItem(checkbox) {
	var $portfolioItem = $('.portfolio_item');
	if (checkbox.checked) {
		$portfolioItem.find('.portfolio_item_block').slideToggle("fast");
		$portfolioItem.find('.portfolio_item_description').slideToggle("fast");
		$portfolioItem.removeClass('portfolio_item--toggled');
	}
	else {
		$portfolioItem.find('.portfolio_item_block').slideToggle("fast");
		$portfolioItem.find('.portfolio_item_description').slideToggle("fast");
		$portfolioItem.addClass('portfolio_item--toggled');
	}
}

function showAgreeCancelReasonPopup() {
	var popupContent = $('.js-popup-confirm-cancel-reason__container').html();
	if (!popupContent) {
		return false;
	}
	show_popup(popupContent);
}

function showRemoveRatingPopup() {
	var popupContent = $('.js-popup-delete-rating__container').html();
	if (!popupContent) {
		return false;
	}
	show_popup(popupContent);
}

function confirmCancelReasonOrder(button) {
	var userSelect = parseInt(($(button).data('agree'))) === 1 ? 'agree' : 'disagree';
	$('input[name="agree_reason"]').val(userSelect);
	if (typeof (yaCounter32983614) !== 'undefined') {
		yaCounter32983614.reachGoal('CANCEL-ORDER-TWO');
	}
	if (window.draft) {
		window.draft.clear();
	}
	$('#confirm-cancel-reason-form').submit();
}

function removeOrderedExtraConfirmation(extraId) {
	var html = '' +
		'<div>' +
		'<form action="/track/action/remove_extra" method="post">' +
		'<input type="hidden" name="extra_id" value="' + extraId + '" />' +
		'<input type="hidden" name="orderId" value="' + Track.orderId + '" />' +
		'<input type="hidden" name="action" value="remove_extra" />' +
		'<h1 class="popup__title f26">' + t("Подтверждение удаления") + '</h1>' +
		'<hr class="gray" style="margin-bottom:32px;">' +
		'<div style="display:inline-block;width:100%;">' +
		'<p class="f15 pb50 ml10">' + t("Удалить дополнительную опцию?") + '</p>' +
		'<button class="popup__button red-btn" onclick="if (window.draft) {	window.draft.clear(); } $(this).closest(\'form\').submit();">' + t("Удалить") + '</button>' +
		'<button class="popup__button white-btn pull-right popup-close-js" onclick="return false;">' + t("Отменить") + '</button></div>' +
		'</div>' +
		'</form>' +
		'</div>';
	show_popup(html, 'popup_w500');
}

function customOptionsDaysSelectHtml() {
	return '<select id="extratime" class="js-new-extra-select js-new-extra-days textthree" name="customExtraTime[]">' +
		'<option value="0"> ' + t('{{0}} дней', [0]) + ' </option>' +
		'<option value="1"> ' + t('{{0}} день', [1]) + ' </option>' +
		'<option value="2"> ' + t('{{0}} дня', [2]) + ' </option>' +
		'<option value="3"> ' + t('{{0}} дня', [3]) + ' </option>' +
		'<option value="4"> ' + t('{{0}} дня', [4]) + ' </option>' +
		'<option value="5"> ' + t('{{0}} дней', [5]) + ' </option>' +
		'<option value="6"> ' + t('{{0}} дней', [6]) + ' </option>' +
		'<option value="7"> ' + t('{{0}} дней', [7]) + ' </option>' +
		'<option value="8"> ' + t('{{0}} дней', [8]) + ' </option>' +
		'<option value="9"> ' + t('{{0}} дней', [9]) + ' </option>' +
		'<option value="10"> ' + t('{{0}} дней', [10]) + ' </option>' +
		'</select>';
}

function customOptionsPricesSelectHtml() {
	var optionsHtml = '';

	$.each(window.optionPrices, function (i, opt) {
		optionsHtml += '<option data-seller-value="' + opt.priceWithCommission + '" data-value="' + opt.price + '" value="' + opt.price + '">';
		optionsHtml += opt.priceWithCommission;
		optionsHtml += '</option>';
	});

	return '<select name="customExtraPrice[]" class="js-new-extra-select js-new-extra-price textthree">' + optionsHtml + '</select>';
}

function addCustomOption() {
	if ('undefined' === typeof optionPrices || !optionPrices.length) {
		return;
	}

	var html =
		'<li class="order-extras__item extrasping-settings extra-field-block-css extra-edit-block-css custom">' +
		'<input name="customExtraName[]" maxlength="50" value="" class="textthree styled-input f14 pull-left m-wMax m-border-box" type="text">' +
		'<div class="new-order-extras__select-block">' +
		'<div class="pull-left custom-option-price">' + customOptionsPricesSelectHtml() + '</div>' +
		'<div class="pull-left custom-option-days">' + customOptionsDaysSelectHtml() + '</div>' +
		'<div class="dib pull-left" style="background-color:transparent;"></div>' +
		'<div class="new-order-extras__delete del-opt cur mt8 ml10 dib" data-id="0" title="' +
		t('Удалить') + '">' +
		'<i class="ico-close-12"></i>' +
		'</div>' +
		'</div>' +
		'<div class="clear"></div>' +
		'</li>';

	var $html = $(html);

	$html.find('.js-new-extra-price').chosen({width: "100px", disable_search: true});
	$html.find('.js-new-extra-days').chosen({width: "90px", disable_search: true});
	$('.order-extras-list-bottom').append($html);
	updateExtraPrices();
	newContentProcess($html);
}

function removeCustomOption(target) {
	$(target).closest('li').remove();
	updateExtraPrices();
	calculateSuggestExtrasTotal();
}

var openedSuggestions=[];

function suggestOptionsToggle(target) {
	var suggestion = target.data('suggestion');
	if(suggestion in openedSuggestions) {
		delete openedSuggestions[suggestion];
	} else {
		openedSuggestions[suggestion] = true;
	}

	$('.suggest-blocks').toggleClass('d-none', (Object.keys(openedSuggestions).length < 1));

	var suggestionBlock = target.closest('#suggestExtras').find('.'+suggestion);
	if (suggestion === 'options' && suggestionBlock.css('display') !== 'none') {
		suggestionBlock.find('.styled-checkbox').prop('checked', false);
	}
	else if (suggestion === 'package-level' && suggestionBlock.css('display') !== 'none') {
		suggestionBlock.find('.styled-checkbox').prop('checked', false);
	}

	suggestionBlock.toggle();
	target.find('.icon').toggle();
	var $submitButton = $("#track-send-message-button");
	var $messageBody = $("#message_body1");
	if (Object.keys(openedSuggestions).length < 1) {
		$('.track-send-control').removeClass('is-offer');
		$submitButton.val(t('Отправить сообщение'));
		$submitButton.removeClass("option-request");
		$messageBody.prop('required', true);
	} else {
		$('.track-send-control').addClass('is-offer');
		$submitButton.val(t('Предложить'));
		$submitButton.addClass("option-request");
		$messageBody.removeAttr('required');
		calculateSuggestExtrasTotal();
	}
	if (window.toggleSubmitButton) {
		window.toggleSubmitButton();
	}
}

function delNameFiles(id) {
	if ($('#fileName' + id).parent().find('.file-item').length == 1) {
		$('#fileName' + id).parent().parent().parent().find('textarea').prop('required', true);
	}
	$('#fileName' + id).remove();
	$('#fileInput' + id).remove();
}

function js_scrollToInstructions() {
	$('html, body').animate({
		scrollTop: $("#send-instruction-modal .send-instruction__button").offset().top - 100
	}, 200);
}

function js_scrollToCheckWork() {
	var checkBlock = $(".checkWork-js:last");
	if (!checkBlock.is(':visible')) {
		$(".show-track-action").click();
	}
	$('html, body').animate({
		scrollTop: checkBlock.offset().top
	}, 200);
}

function js_sendInWork() {
	if (window.draft) {
		window.draft.clear();
	}
	$('#sendInWork_js').submit();
}

function js_scrollToSendCheck() {
	$('html, body').animate({
		scrollTop: $(".track-pass-work-button").offset().top
	}, 200);
}

function js_scrollToSendReview() {
	$('html, body').animate({
		scrollTop: $("[data-form=send-review-js]").offset().top
	}, 200);
}

function scrollToFirstUnreadTrack() {

	var $firstUnread = $('.unread').first();

	var $trackToScroll = null;
	if ($firstUnread.length) {
		// Если есть непрочитанные то скроллим к первому непрочитанному
		$trackToScroll = $firstUnread;
	} else {

		// Иначе к последнему сообщению
		$trackToScroll = $('.step-block-order_item:last');
	}

	if ($trackToScroll && $trackToScroll.length) {

		var scrollTop = getElementTopToScroll($trackToScroll);
		$('html, body').animate({
			scrollTop: scrollTop,
		}, 200);
		return true;
	}

	return false;
}


function showNameFiles(input, form) {
	haveFiles = true;
	if (input.files && input.files[0]) {
		var counter = $(".file-item", "#list-files" + form).size();
		var cnt = Object.keys(input.files).length;
		// В safari Object.keys(input.files) добавляет в конец массива 'length'
		{
			if (Object.keys(input.files)[cnt - 1] == 'length') {
				cnt--;
			}
		}
		for (var i = 0; i < cnt; i++) {
			if (!(counter + i + 1 <= config.files.maxCount)) {
				alert(t('Превышено максимальное количество файлов.'));
				input.value = '';
				return false;
			}
			if (input.files[i].size > config.files.maxSizeReal) {
				alert(t('Размер файла не должен превышать {{0}} МБ', [config.files.maxSize]));
				input.value = '';
				return false;
			}
		}
		for (var i = 0; i < cnt; i++) {
			var fileName = input.files[i].name;
			var size = input.files[i].size;
			var len = fileName.length;
			var symb3 = fileName.substr(len - 3, len).toLowerCase();
			var symb4 = fileName.substr(len - 4, len).toLowerCase();
			var ico = '';
			if ($.inArray(symb3, ['doc', 'xls', 'rtf', 'txt']) != -1 || $.inArray(symb4, ['docx', 'xlsx']) != -1) {
				ico = 'doc';
			} else if ($.inArray(symb3, ['zip', 'rar']) != -1) {
				ico = 'zip';
			} else if ($.inArray(symb3, ['png', 'jpg', 'gif', 'psd']) != -1 || $.inArray(symb4, ['jpeg']) != -1) {
				ico = 'image';
			} else if ($.inArray(symb3, ['mp3', 'wav', 'avi']) != -1) {
				ico = 'audio';
			} else {
				ico = 'zip';
			}
			var id = genInputId.getID();
			input.id = 'fileInput' + id;
			input.className = 'fileInputs';
			var rightText = "";
			rightText = "<a class='remove-file-link' onclick=\"delNameFiles('" + id + "')\"></a>";
			html = "<div class='mb5 file-item' id='fileName" + id + "'><i class='ico-file-" + ico + " dib v-align-m'></i><span class='dib v-align-m ml10" + (allow ? "" : " color-red") + "'>" + fileName + "</span>" + rightText + "</div>";
			$("#list-files" + form).append(html);
			var listFileInput = document.createElement("input");
			listFileInput.type = "hidden";
			listFileInput.id = 'fileInput' + id;
			listFileInput.name = 'inputName_' + id;
			listFileInput.value = "sizeFile" + fileName.replace('"', '\"').replace("'", "\'") + size;
			$("#list-files" + form).append(listFileInput);
			formData.append('fileInput[]', input.files[i]);
		}
		$(input).after('<input onchange="showNameFiles(this, ' + form + ')" name="fileInput[]" type="file" multiple/>');
		$(input).hide();
		$('#message_body' + form).removeAttr('required');
	}
}

function _trackUpgradePackage(el) {
	var $checkbox = $(el).siblings('input');
	var price = $checkbox.data('price');
	var type = $checkbox.data('type');

	var $otherCheckbox = $(el).parents('.order-info').find('.js-upgrade-package input').not($checkbox);

	var $form = $('.js-upgrade-package-form');
	var $button = $form.find('button');

	$otherCheckbox.prop('checked', false);

	if ($checkbox.prop('checked')) {
		$form.find("[name='upgrade_package_type']").val('');
		$button.find('span').text(0);
		$button.addClass('disabled').prop('disabled', true);
	} else {
		$form.find("[name='upgrade_package_type']").val(type);
		$button.find('span').text(price);
		$button.removeClass('disabled').prop('disabled', false);
	}
}

var deleteTrackFile = function ($target, fileId) {
	if (!confirm(t('Вы уверены?'))) {
		return false;
	}

	$.ajax({
		type: "POST",
		url: '/api/file/delete',
		data: {'file_id': fileId},
		success: function (response) {
			if (response.success) {
				var $track = $target.closest('.step-block-order_item');
				$target.closest('.file-item').remove();

				if ($track.hasClass('text_message')
					&& !$track.find('.breakwords').text().length
					&& !$track.find('.file-item').length) {
					$track.remove();
				}
			}
		}
	});
};

var toggleMessageButtonAction = function (event) {
	var $this = $(this);
	$this.parent().find('.js-toggle-message-button').addClass('inactive');
	$this.removeClass('inactive');
};

var sendMessageJsClick = function (event) {
	$('.send-review-js').hide();
};

var hideConversationClick = function (event) {
	event.stopImmediatePropagation();
	if (config && config.track && config.track.isFocusGroupMember) {
		$('.show-conversation').toggleClass('shown');
		window.appTracks.$refs.trackList.toggleHiddenConversation();
	} else{
		if ($('.show-conversation').hasClass('shown')) {
			$('.show-conversation img').removeClass('rotate180');
			$('.hidable-message').addClass('hide-by-conversation');
			$('.show-conversation').removeClass('shown');
		} else {
			$('.show-conversation img').addClass('rotate180');
			$('.hidable-message').removeClass('hide-by-conversation');
			$('.show-conversation').addClass('shown');
		}
	}
}; 

var addCustomOptionClick = function (event) {
	event.stopImmediatePropagation();
	addCustomOption();
	return false;
};

var removeCustomOptionClick = function (event) {
	event.stopImmediatePropagation();
	removeCustomOption(this);
};

var addPackageStopClick = function (event) {
	if (window.draft) {
		window.draft.clear();
	}
	$('#addpackagestop').submit();
};

var upgradePackageStopClick = function (event) {
	if (window.draft) {
		window.draft.clear();
	}
	$(event.currentTarget).closest('form').submit();
};

var kworkDescriptionClick = function (event) {
	var $this = $(this).toggleClass('clicked');
	if ($this.hasClass('clicked')) {
		$this.text(t('Свернуть описание')).next().slideDown(SLIDE_DURATION);
	}
	else {
		$this.text(t('Развернуть описание')).next().slideUp(SLIDE_DURATION);
	}
};

var jsKworkCommentEditClick = function (event) {
	$('.kwork-comment-edit__link').trigger('click');
};

var kworkCommentEditClick = function (event) {
	var $this = $(this).toggleClass('clicked');
	if ($this.hasClass('clicked')) {
		$this.text(t('Отменить редактирование')).next().slideDown(SLIDE_DURATION);
	} else {
		$this.text(t('Редактировать отзыв')).next().slideUp(SLIDE_DURATION);
	}
};

var deletePortfolioClick = function (event) {
	var content = $('.js-popup-delete-portfolio__container').html();
	show_popup(content, 'popup-delete-portfolio');
};

var ratingCommentEditClick = function (event) {
	var $this = $(this).toggleClass('clicked');
	if ($this.hasClass('clicked')) {
		$this.text(t('Отменить редактирование')).next().slideDown(SLIDE_DURATION);
	}
	else {
		$this.text(t('Редактировать ответ')).next().slideUp(SLIDE_DURATION);
	}
};

var trackExtrasItemClick = function (event) {
	var id = $(this).data('id');
	var $checkbox = $(this).find('.styled-checkbox[data-id=' + id + ']');
	var $kworkCountWrapper = $(this).find('.kwork-count-wrapper');
	var $kworkCountWrapperVolume = $(this).find('.kwork-count-wrapper-volume');
	var w = $(window).width();

	if ($checkbox.prop('checked')) {
		var $target = $(event.target);
		if (!$target.is('.chosen-container, input[type="text"]') && $target.closest('.chosen-container, input[type="text"]').length === 0) {
			$checkbox.prop('checked', false);
		}
		if (w < 768) {
			$kworkCountWrapper.fadeOut(150);
			$kworkCountWrapperVolume.fadeOut(150);
		}
	}
	else {
		$checkbox.prop('checked', true);
		if (w < 768) {
			$kworkCountWrapper.fadeIn(150);
			$kworkCountWrapperVolume.fadeIn(150);
			$kworkCountWrapperVolume.find('.volume_mobile').focus();
		}
	}

	calculateBuyExtrasTotal();
};

var trackUpgradePackage = function () {
	_trackUpgradePackage($(this));
};

var trackDescriptionLabel = function () {
	var $this = $(this);
	var $table = $this.closest('.order-info');
	var id = $this.data('id');

	$table.find('.js-toggle-description-block[data-id="' + id + '"]').toggleClass('active').fadeToggle(150);
	$this.find('img').toggleClass('rotate180');

	$this.toggleClass('show-description');
	if ($table.find('.show-description').length) {
		$table.find('.js-upgrade-package-title').show();
	} else {
		$table.find('.js-upgrade-package-title').hide();
	}
};

var trackDescriptionLabelMobile = function () {
	var _this = $(this);
	var table = _this.closest('.order-info');
	var id = _this.data('id');
	var checkbox = table.find('.js-upgrade-package[data-id="' + id + '"] .styled-checkbox');
	var description = table.find('.js-toggle-description-block[data-id="' + id + '"]');

	table.find('.js-toggle-description-block:not([data-id="' + id + '"])').hide().removeClass('active');
	table.find('.js-toggle-description-label:not([data-id="' + id + '"]) img').removeClass('rotate180');

	if (description.hasClass('active')) {
		description.hide().removeClass('active');
	} else {
		description.addClass('active').fadeIn(150);
	}
	table.find('.js-toggle-description-label[data-id="' + id + '"] img').toggleClass('rotate180');

	_trackUpgradePackage(table.find('.js-upgrade-package[data-id="' + id + '"] .order-info__title-spoiler'));
	checkbox.prop('checked', !checkbox.prop('checked'));
};

/**
 * Событие изменения количества предлагаемых опций
 */
function onChangeExtraPrice() {
	updateExtraPrices();
	calculateSuggestExtrasTotal();
}

/**
 * Обновление стоимости опций для продавца с учетом текущего оборота
 *
 * @global turnover Суммарный оборот между продавцом и покупателем
 * @global orderPrice Cтоимость заказа
 */
function updateExtraPrices() {
	// Учитываем текущую стоимость заказа в обороте
	var currentTurnover = 0;

	// Предложение повысить уровень пакета
	var $upgradePackageCheckbox = $('#form_pass_work .js-package-price .styled-checkbox:checked');
	if ($upgradePackageCheckbox.length) {
		var $upgradePackage = $upgradePackageCheckbox.closest('.js-package-price');
		var upgradePackagePrice = parseFloat($upgradePackage.data('package-buyer-price-full'));
		currentTurnover += upgradePackagePrice;
	}

	// Предложение опций
	$('#form_pass_work .order-extra-item, #form_pass_work .order-extras__item.custom').each(function() {
		var $item = $(this);

		// Опции кворка
		if ($item.is('.order-extra-item')) {
			var $checkbox = $item.find('.styled-checkbox');
			var $select = $item.find('.chosen_select');
			var $volumeInput = $item.find('.js-volume-order');
			var extraCount = 0;
			var extraPrice = 0;

			// Очистить поле ввода при снятии галочки чекбокса
			if (!$checkbox.is(':checked')) {
				$volumeInput.val('')
			}

			var price = parseFloat($checkbox.data('price'));
			var commission = calculateCommission(price, currentTurnover);
			$checkbox.data("priceWorker", commission.priceWorker);

			if ($select.length) {
				$select.find('option').each(function() {
					var count = parseInt($(this).prop('value'));
					var commission = calculateCommission(price * count, currentTurnover);
					var workerSumString = Utils.priceFormatWithSign(commission.priceWorker, Track.kworkLang, ' ', 'Р');
					$(this)
					.text(count + ' (' + workerSumString + ')')
					.data('price-worker', commission.priceWorker)
					.attr('data-price-worker', commission.priceWorker);
				});

				$select.trigger('chosen:updated');
				extraCount = parseInt($select.val());

				$item.find('.option-item__price-value').text($select.find('option:selected').data('price-worker'));

				extraPrice = price * extraCount;
			} else if ($volumeInput.length && $volumeInput.data('multiplier') > 0) {
				var multiplier = $volumeInput.data('multiplier');

				var extraVolume = clearPriceStr($volumeInput.val());
				if (extraVolume && multiplier) {
					//пересчитываем объем для временных рубрик
					extraVolume = getVolumeInKworkType($volumeInput, extraVolume);

					// Округляем стоимость кворка с фиксированным объемом кратно 50 руб. / $1
					extraPrice = kworkCostRound(price, extraVolume, multiplier, Track.kworkLang, 'track');
				} else {
					if($volumeInput.val().length){ 
						if (extraPrice === 0) {
							extraPrice = Track.kworkLang === "ru" ? 50 : 1;
						} else {
							extraPrice = price;
						}
					}
				}

				extraCount = Math.ceil(clearPriceStr($volumeInput.val()) / multiplier);
				var commission = calculateCommission(extraPrice, currentTurnover);
				$volumeInput.data('price-worker', commission.priceWorker)
					.attr('data-price-worker', commission.priceWorker);
			}

			if ($checkbox.is(':checked')) {
				currentTurnover += extraPrice;
			}
		}
	});
	processCustomOptionsPrices(currentTurnover);
}

/**
 * Процесс проверки цен доп. опций
 * @param currentTurnover
 */
function processCustomOptionsPrices(currentTurnover) {
	// Удалим все подсказки про опции чтобы избежать дублей
	$(".max-custom-options-tooltip").remove();

	// Посчитаем максимальную допустимую сумму доп. опций для продавца с учётом комиссии
	var commissionMax = calculateCommission(window.maxOptionsSum, currentTurnover);

	// Пересчитаем суммы для продавца по всем селектам цен доп. опций с учётом комиссии
	var $customExtras = $(".order-extras-list li.custom");
	var $selects = $customExtras.find(".js-new-extra-price");
	$selects.find("option").each(function() {
		var price = parseFloat($(this).data('value'));
		var commission = calculateCommission(price, currentTurnover);
		var workerSumString = Utils.priceFormatWithSign(commission.priceWorker, Track.kworkLang, ' ', 'Р');
		$(this)
			.text(workerSumString)
			.data("seller-value", commission.priceWorker)
			.attr("data-seller-value", commission.priceWorker);
	});
	$selects.trigger("chosen:updated");

	// Посчитаем сумму доп. опций
	var totalCustom = calculateCustomExtrasPrices();
	var totalKwork = 0;
	$("li.order-extra-item.kwork-extra input.order-extras__input").each(function () {
		totalKwork += $(this).data("priceWorker");
	});
	var total = totalCustom + totalKwork;

	// Если заказана хотябы одна опция, добавим подсказу
	if (totalCustom > 0) {
		var workerMaxSumString = Utils.priceFormatWithSign(commissionMax.priceWorker, Track.kworkLang, " ", "руб.");
		var workerTotalSumString = Utils.priceFormatWithSign(total, Track.kworkLang, " ", "руб.");
		var tooltip = "Общая цена всех доп. опций - до " + workerMaxSumString + " Сейчас " + workerTotalSumString;
		$customExtras
			.last()
			.parent()
			.append("<div class='max-custom-options-tooltip'>" + tooltip + "</div>");
	}

	var $submitButton = $("#track-send-message-button");
	if (total <= commissionMax.priceWorker) {
		// Если сумма проходит проверку, то активируем кнопку отправки предожения
		$submitButton.removeClass("btn_disabled").prop("disabled", false);
		// Уберем красную обводку с цен доп. опций
		$customExtras.removeClass("custom-price-error");
		// Если сумма проходит все проверки, то показываем кнопку добавления новой опции
		$("#add-custom-option").show();
	} else {
		// Если сумма доп. опций превышает допустимую, то:
		// - отключить кнопку отправки предложения
		$submitButton.addClass("btn_disabled").prop("disabled", true);
		// - досветить цены доп. опций красным
		$customExtras.addClass("custom-price-error");
		// - подсветим подсказку красным если она ещё не красная и добавим текст
		$(".max-custom-options-tooltip")
			.addClass("error")
			.html(tooltip + "<br>Уменьшите стоимость предыдущих опций, чтобы добавить еще одну");
		// - спрячем кнопку добавления опции
		$("#add-custom-option").hide();
		// - удалить пустые строки с опциями
		for (var i = 0; i < $customExtras.length; i++) {
			var $customExtra = $($customExtras[i]);
			if (!$($customExtras[i]).find('input[type="text"]').val()) {
				$customExtra.remove();
			}
		}
	}
}

function getCurrentSuggestPrice() {
	var price = calculateExtrasTotal('price-worker', true);
	price += calculateCustomExtrasPrices();
	price += calculatePackagePrice();
	return price;
}

/**
 * Подсчет цены предлагаемых опций
 */
function calculateSuggestExtrasTotal() {
	if (window.toggleSubmitButton) {
		window.toggleSubmitButton();
	}
	var price = getCurrentSuggestPrice();

	var $button = $('#track-send-message-button');
	if ($button.hasClass('option-request')) {
		if (price > 0) {
			$button.val(t("Предложить услуги на") + " " + Utils.priceFormatWithSign(price, Track.kworkLang, " ", "руб."));
		} else {
			$button.val(t("Предложить"))
		}
		$('.track-send-control').addClass('is-offer');
	}
}

/**
 * Подсчет цены выбранных опций
 * @param priceField поле из data по которуму берется цена price|priceWorker
 * @returns {number}
 */
function calculateExtrasTotal(priceField, forWorker) {
	if (forWorker && !('options' in openedSuggestions)) {
		return 0;
	}
	var price = 0;
	var extras = $('.order-extras-list li input[type=checkbox]:checked');

	for (var i = 0; i < extras.length; i++) {
		var $extra = $(extras[i]);
		var multiplier = 1;
		var $extraCountInput = $('#extra_count' + $extra.data('id'));

		if ($extraCountInput.data('multiplier') && $extraCountInput.data('multiplier') > 0) {
			multiplier = $extraCountInput.data('multiplier');
			var extraPrice = clearPriceStr($extra.data(priceField));
			var extraCount = clearPriceStr($extraCountInput.val());
			var extraVolume = extraCount;

			extraCount = Math.ceil(extraCount / multiplier);
			if (priceField === 'price-worker' && $extraCountInput.data('price-worker')) {
				price += $extraCountInput.data('price-worker');
			} else {
				if (extraVolume && multiplier) {
					//пересчитываем объем для временных рубрик
					extraVolume = getVolumeInKworkType($extraCountInput, extraVolume);

					// Округляем стоимость кворка с фиксированным объемом кратно 50 руб. / $1
					price += kworkCostRound(extraPrice, extraVolume, multiplier, Track.kworkLang, 'track');
				} else {
					if($extraCountInput.val().length){
						if (price === 0 ) {
							price = Track.kworkLang === "ru" ? 50 : 1;
						} else {
							price += extraPrice;
						}
					}
				}
			}
		} else {
			var $option = $extraCountInput.find('option:selected');
			price += parseFloat($option.data(priceField));

			$('#mobile_extra_count' + $extra.data('id')).val($extraCountInput.val());
		}
	}

	return price;
}

/**
 * Получение цены всех выбранных пользовательских опций в предложении опций
 *
 * @returns {number}
 */
function calculateCustomExtrasPrices() {
	if (!('options' in openedSuggestions)) {
		return 0;
	}
	var price = 0;
	var customExtras = $('.order-extras-list li.custom');
	for (var i = 0; i < customExtras.length; i++) {
		var $customExtra = $(customExtras[i]);
		if ($customExtra.find('input[type="text"]').val()) {
				price += clearPriceStr($customExtra.find('select option:selected').data('sellerValue'));
		}
	}
	return price;
}

/**
 * Получение цены повышения выбранного пакета
 *
 * @returns {number}
 */
function calculatePackagePrice() {
	if (!('package-level' in openedSuggestions)) {
		return 0;
	}
	var price = 0;
	var packageChecked = $('#suggestExtras .js-package-price .styled-checkbox:checked');
	if (packageChecked.length > 0) {
		price = parseFloat(packageChecked.closest('.js-package-price').data('package-worker-price'));
	}
	return price;
}

/**
 * Подсчет общей цены дополнительных заказываемых опций для продавца
 * и включение выключение кнопки дозаказа основываясь на цене
 */
function calculateBuyExtrasTotal() {
	var price = calculateExtrasTotal('price');
	var $button = $('#addextrastop button.submit');
	$button.find('span').text(Utils.priceFormat(price, Track.kwork_lang));

	if (price > 0) {
		$button.removeClass('disabled').prop('type', 'submit');
	} else {
		$button.addClass('disabled').prop('type', 'button');
	}
}

var ratingBlockReviewGoodClick = function (event) {
	$('.rating-block-review .active').removeClass('active');
	$(this).addClass('active');
	$('#rating-block-review_good-js').prop('checked', true);
};

var ratingBlockReviewBadClick = function (event) {
	$('.rating-block-review .active').removeClass('active');
	$(this).addClass('active');
	$('#rating-block-review_bad-js').prop('checked', true);
};

var trackGroupBtnDataFormClick = function (event) {
	$('.track-form-js').hide();
	$('.' + $(this).attr('data-form')).show();
};

var acceptOrderJsClick = function (event) {
	$('#accept-order').val('1');
	if (window.draft) {
		window.draft.clear();
	}
	$('#accept-refuse-order-js').submit();
};

var attachClick = function (context, el, fn) {
	if ($(el).length) {
		var event = 'click';
		$(context).off(event, el, fn).on(event, el, fn);
	}
};

var showTrackAll = function (event) {
	event.preventDefault();
	$('.show-track .loader').removeClass('hide');

    var params = window
        .location
        .search
        .replace('?','')
        .split('&')
        .reduce(
            function(p,e){
                var a = e.split('=');
                p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                return p;
            },
            {}
        );

	$.ajax( {
		type: 'get',
		url: '/track_show_hidden?id=' + params['id'],
		success: function(msg) {
			$('.show-track .loader').addClass('hide');
			$('.show-track').addClass('hide');
			$('#tracks-ajax-loader').html(msg);
			$('.step-block-order_item').removeClass('hide');
			newContentProcess(document);
		}
	});
};

var confirmOrder = function (e) {
	e.preventDefault();
	if ('undefined' !== typeof yaCounter32983614) {
		yaCounter32983614.reachGoal('WORK-DONE');
	}

	if (window.draft) {
		window.draft.clear();
	}

	var $popupConfirmForm = $('.js-track-form__popup-confirm-form');

	// копируем поле сообщения в форму отправки
	var messageVal = $("#message_body1").val();
	$popupConfirmForm.find('[name="message"]').val(messageVal);

	// скрываем попап о подтверждении заказа
	hideConfirmInprogressDonePopup();

	// отправляем запрос о завершение заказа и получаем данные для отображения информации о покупателе и заказа
	sendAjaxConfirmOrder($popupConfirmForm);
}

var formSubmited = false;

var newContentProcess = function (context) {
	if($('.is-first-dialog-message').length) {
		attachClick(context, '.hide-conversation, .show-conversation', hideConversationClick);
	}

	if (window.messageForm) {
		window.messageForm.initDynamic();
	}

	if (window.sendForm) {
		window.sendForm.initDynamic();
	}

	if (window.draft) {
		window.draft.updateContent();
	}

	if (window.initFormComponents) {
		window.initFormComponents();
	}

	// Добавляем действия над сообщениями.
	$('.text_message:not(.has-operations)', context).each(function () {
		addItemOperations($(this));
	});

	$('.text_message', context).each(function () {
		window.QuoteMessage.messageQuoteCropEllipsis($(this));
	});

	var packageLevelButton = $('#suggestExtras span[data-suggestion="package-level"]');
	if ($('input[name=upgradePackagesSuggested]').length) {
		packageLevelButton.hide();
	} else {
		packageLevelButton.show();
	}

	if (typeof window.initReviewForm != 'undefined') {
		window.initReviewForm();
	}

	window.QuoteMessage.init(window.QuoteMessage.TYPE_PAGE_TRACK);

	attachItemOperations(context);

	attachClick(context, '#arbitrage-link', showArbitrageCheckPopupClick);
	attachClick(context, '#add-custom-option', addCustomOptionClick);
	attachClick(context, '.new-order-extras__delete', removeCustomOptionClick);

	attachClick(context, '.show-track-action', showTrackAll);
	attachClick(context, '.js-toggle-message-button', toggleMessageButtonAction);
	attachClick(context, "[data-form=send-message-js]", sendMessageJsClick);

	attachClick(context, '#addpackagestop .submit', addPackageStopClick);
	attachClick(context, '.upgradepackagestop .submit', upgradePackageStopClick);

	attachClick(context, '.kwork-description__link', kworkDescriptionClick);

	attachClick(context, '.js-kwork-comment-edit__link', jsKworkCommentEditClick);
	attachClick(context, '.kwork-comment-edit__link', kworkCommentEditClick);

	attachClick(context, '.js-track-form__delete-portfolio', deletePortfolioClick);

	attachClick(context, '.kwork-rating-comment-edit__link', ratingCommentEditClick);

	attachClick(context, '.track-extras-list li', trackExtrasItemClick);
	attachClick(context, '.track-extras-list .kwork-count-wrapper', function () {
		return false;
	});
	attachClick(context, '.track-extras-list .kwork-count-wrapper-volume', function (e) {
		if ($(window).width() < 768) {
			return false;
		}
	});

	attachClick(context, '.js-cancel-track__button', showAgreeCancelReasonPopup);

	attachClick(context, '.js-kwork-comment-remove__link', showRemoveRatingPopup);

	attachClick(context, '.js-track-form__popup-confirm-link', showConfirmInprogressDonePopup);

	attachClick(context, '.rating-block-review_good', ratingBlockReviewGoodClick);
	attachClick(context, '.rating-block-review_bad', ratingBlockReviewBadClick);

	attachClick(context, '.track-page-group-btn>[data-form]', trackGroupBtnDataFormClick);

	attachClick(context, '#accept-order-js', acceptOrderJsClick);

	attachClick(context, '.kwork-count>div, .kwork-count-link a, .kwork-count__link', kworkExtrasPlusMinus);

	attachClick(context, '.question-block-track-page', questionBlockTrackPageMobileClickHide);

	if ($(window).width() < 768) {
		attachClick(context, '.js-toggle-wrapper', trackDescriptionLabelMobile);
		attachClick(context, '.order-info__title-spoiler', function (e) {
			e.preventDefault();
		});
	} else {
		attachClick(context, '.js-upgrade-package label', trackUpgradePackage);
		attachClick(context, '.js-toggle-description-label', trackDescriptionLabel);
	}

	$('.js-track-form__popup-confirm-form').on('submit', function (e) {
		e.preventDefault();
	});

	$('.js-track-form__popup-confirm-submit').on('click', function (e) {
		e.preventDefault();
		if ('undefined' !== typeof yaCounter32983614) {
			yaCounter32983614.reachGoal('WORK-DONE');
		}

		if (window.draft) {
			window.draft.clear();
		}

		var $popupConfirmForm = $('.js-track-form__popup-confirm-form');

		// копируем поле сообщения в форму отправки
		var messageVal = $("#message_body1").val();
		// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - оставить содержимое if
		if (config.track.isFocusGroupMember) {
			$popupConfirmForm.find('[name="message"]').val(window.emojiReplacements.preSubmitMessage(messageVal));
		} else {
			$popupConfirmForm.find('[name="message"]').val(messageVal);
		}


		// скрываем попап о подтверждении заказа
		hideConfirmInprogressDonePopup();

		// отправляем запрос о завершение заказа и получаем данные для отображения информации о покупателе и заказа
		sendAjaxConfirmOrder($popupConfirmForm);
	});

	attachClick(context, '.js-track-form__popup-confirm-submit', confirmOrder);

	// отправка на доработку
	attachClick(context, '.js-track-reject', function(){
		if (!formSubmited) {
			formSubmited = true;
			if ('undefined' !== typeof yaCounter32983614) {
				yaCounter32983614.reachGoal('WORK-NOT-DONE');
			}
			var $form = $('.js-track-reject-form');
			// копируем поле сообщения в форму отправки на доработку
			var messageVal = $("#message_body1").val();

			// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - оставить содержимое if
			if (config.track.isFocusGroupMember) {
				messageVal = window.emojiReplacements.preSubmitMessage($("#message_body1").val());
			}

			$form.find('[name="message"]').val(messageVal);

			// очищаем черновик
			if (window.draft) {
				window.draft.clear();
			}

			// копируем файлы в форму отправки на доработку
			var filesUploader = _getFileUploader();
			$.each(filesUploader.files, function(k, v) {
				$form.append('<input type="hidden" name="file-input[new][]" value="' + v.file_id + '">');
			});
			filesUploader.$refs.fileUploader.clearFiles();

			// отправляем форму
			$form.submit();
		}
	});

	$('.track-packages-list select', context).change(function (e) {
		var $this = $(this);
		var price = $this.data('price') * $this.find('option:checked').val();
		$('#addpackagestop button.submit span').text(price);
	});


	// Обязательно здесь, чтобы не рушилась очередь событий(иначе после обновления формы ломается расчет опций)
	$(document).off('input keyup keydown','#addExtrasList .js-volume-order', calculateBuyExtrasTotal)
	.on('input keyup keydown','#addExtrasList .js-volume-order', calculateBuyExtrasTotal);

	$(document).off('click input keyup keydown','#form_pass_work .order-extra-item, #form_pass_work .order-extras__item.custom, #form_pass_work .js-volume-order', onChangeExtraPrice).
	on('click input keyup keydown','#form_pass_work .order-extra-item, #form_pass_work .order-extras__item.custom, #form_pass_work .js-volume-order', onChangeExtraPrice);

	toggleLoyalityLate();
	$('.js-online-icon').onlineWidget();
	if (window.messageForm) {
		window.messageForm.updateCommentBoxState();
	}
};

//фикс для разворачивающихся списков
var questionBlockTrackPageMobileClickHide = function (e) {
	if ($(window).width() < 768 && !$(e.target).parents('.question-block-track-page_hidden').length && !$(e.target).hasClass('question-block-track-page_hidden')) {

		if ($(this).attr('id') === 'help-blocks' && $('.question-block-track-page_hidden').hasClass('m-db') === false) {
			$('html, body').animate({scrollTop: $('#help-blocks').offset().top - 58}, 300);
		}

		$(this).find('.question-block-track-page_hidden').toggleClass('m-db');
		$(this).find('.ico-arrow-down').toggleClass('m-rotate180');
	}
};

//Плюс/Минус для "Закажите дополнительно"
var kworkExtrasPlusMinus = function (e) {
	e.stopPropagation();

	var input, inputVal;
	var parentLi = $(this).parents('li');

	if ($(this).hasClass('js-kwork-count-link')) {
		input = $(this).closest('.kwork-count').find('input');
	} else {
		input = $(this).siblings('input');
	}
	inputVal = parseInt(input.val());

	if ($(this).hasClass('kwork-count_minus')) {
		if (inputVal === 1) {
			if ($(this).hasClass('kwork-count__link')) {
				parentLi.trigger('click');
			}
			return false;
		}
		inputVal -= 1;
		input.val(inputVal);
	} else {
		if (inputVal >= parseInt(input.attr('data-max'))) {
			return false;
		}
		inputVal += 1;
		input.val(inputVal);
	}

	parentLi.find('select').val(inputVal).trigger('chosen:updated');
	parentLi.find('.styled-checkbox').prop('checked', false);
	parentLi.click();
};

var countboxBehavior = function () {
	var $countbox = $('#countbox');

	if (Track.hasCountbox) {
		if (!$countbox.length) {
			$('#tracks-wrapper').prepend('<div id="countbox"></div>');
		}

		// Если обратный отсчет должен выводиться, но мы в состояние предложения отмены заказа,
		// то выводить выводим, но останавливаем.
		if (Track.isCancelRequest) {
			Timer(Track.deadline, "countbox", Track.stopedTimeCancelRequest, true);
		}
		else {
			Timer(Track.deadline, "countbox", Track.time, false);
		}
	}
	else {
		if ($countbox.length) {
			$countbox.remove();
		}
	}
};


function removeMessage() {
	var $msg = $(this).closest('.text_message');
	var itemId = $msg.data('track-id');
	var html = '';
	$('.js-remove-message-check-modal input[name="itemId"]').val(itemId);
	$('.js-remove-message-check-modal input[name="orderId"]').val(Track.orderId);
	$('.js-remove-message-check-modal').modal('show');
}

function editMessage() {
	var $msg = $(this).closest('.text_message');
	var trackId = $msg.data('track-id');
	// TODO: Проверка на isFocusGroupMember на время тестирования. После - удалить else c содержимым
	var messageText = "";
	if (config.track.isFocusGroupMember) {
		var $breakwords = $msg.find('.breakwords').clone();

		// Убираем все текстовые эможи и вставляем unicode
		$breakwords.find('.message-emoji-icon').each(function() {
			var $this = $(this);
			$this.replaceWith($('img', $this).attr('alt'));
		});

		// Меняем unicode на эмоджи
		twemoji.parse($breakwords.get(0), {
			base: Utils.cdnImageUrl('/'),
			folder: 'emoji',
			ext: '.svg',
			className: 'message-emoji',
		});
		messageText = replaceTextMessage($breakwords.html());
	}
	else {
		messageText = replaceTextMessage($msg.find('.breakwords').html());
	}
	var messageFiles = $msg.find('.track-files').data('jsonFiles');

	// Переходим в режим редактирования сообщения
	trackEditMessages[trackId] = new EditMessageTrack(Track.orderId, trackId);
	trackEditMessages[trackId].beginEdit(messageText, messageFiles ? messageFiles : []);
}

var addItemOperations = function ($item) {
	var isModer = $item.hasClass('moder');

	var html = '<div class="message-head_wrap">';
	if (config && config.track && config.track.isFocusGroupMember) {

		html += '<div class="message-icons message-icons_new floatright" style="display: none">';
		html += '<div class="message-icons-group">';
		if (isModer) {
			html += '<div class="message-icons-item  wrap-remove-item moder-remove"><a href="javascript:void(0);" class="remove-item btn-edit dib" title="' + t('Удалить') + '" style="color: gray;">X</a></div>';
		}
		html += '<div class="message-icons-item  wrap-remove-item"><a href="javascript:void(0);" class="remove-item btn-edit dib" title="' + t('Удалить') + '"><i class="kwork-icon icon-bin"></i></a></div>';
		html += '<div class="message-icons-item  wrap-edit-item"><a href="javascript:void(0);" class="edit-item btn-edit dib" title="' + t('Изменить') + '"><i class="kwork-icon icon-pencil"></i></a></div>';
		html += '<div class="message-icons-item  wrap-quote-item"><a href="javascript:;" onclick="MessageFormModule.openMessageForm(this);" class="quote-item btn-quote dib" title="' + t('Цитировать') + '"><i class="fa fa-quote-right"></i></a></div>';
		html += '</div>';
		html += '</div>'; // .message-icons
		html += '</div>'; // .message-head_wrap

		$item.prepend(html).addClass('has-operations');

	} else{
		html += '<div class="message-icons message-icons_new floatright" style="display: none">';

		if (isModer) {
			html += '<div class="floatright wrap-remove-item moder-remove"><a href="javascript:void(0);" class="remove-item btn-edit pl10 dib" title="' + t('Удалить') + '" style="color: gray;">X</a></div>';
		}

		html += '<div class="floatright wrap-remove-item"><a href="javascript:void(0);" class="remove-item btn-edit pl10 dib" title="' + t('Удалить') + '"><i class="kwork-icon icon-bin"></i></a></div>';
		html += '<div class="floatright wrap-edit-item"><a href="javascript:void(0);" class="edit-item btn-edit pl10 dib" title="' + t('Изменить') + '"><i class="kwork-icon icon-pencil"></i></a></div>';
		html += '<div class="floatright wrap-quote-item"><a href="javascript:;" onclick="MessageFormModule.openMessageForm(this);" class="quote-item btn-quote pl10 dib" title="' + t('Цитировать') + '"><i class="fa fa-quote-right"></i></a></div>';
		html += '</div>'; // .message-icons
		html += '</div>'; // .message-head_wrap

		$item.prepend(html).addClass('has-operations');
	}


	/*
	 * Если можно редактировать или удалять то включаем функционал показа скрытия иконок
	 * сделано что бы на мобильной версии при клике на трек не попадать на иконки
	*/
	$item.on('mouseenter', function() {
		var _self = this;
		$(_self).addClass('isMouseenter');
		if (!$(_self).parents('.is-edit').length) {
			setTimeout(function () {
				if ($(_self).hasClass('isMouseenter')) {
					$('.message-icons', _self).show();
				}
			}, 100);
		}
	});

	$item.on('mouseleave', function() {
		$(this).removeClass('isMouseenter');
		$('.message-icons', this).hide();
	});
};

var attachItemOperations = function (context) {
	$('.remove-item', context).on('click', removeMessage);
	$('.edit-item', context).on('click', editMessage);
	$('.quote-item', context).off('click').on('click', function() {
		var $message = $(this).closest('.text_message');
		var trackId = $message.data('track-id');
		var messageText = replaceTextMessage($message.find('.js-track-text-message').html());
		var messageUsername = $message.find('.js-track-username a,.track--item__title a.t-profile-link').text();

		if (messageText === '') {
			$.each($message.find('.js-track-file-name'), function () {
				messageText += $(this).text() + ', ';
			});
			messageText = messageText.replace(/, $/g, '');
		}

		window.QuoteMessage.quoteMessage(trackId, messageText, messageUsername);
	});
};

$(document).on('submit', '.js-remove-message-form', function () {
	var $form = $(this);
	$.post($form.attr('action'), $form.serialize(), function (response) {
		if (response.success) {
			var itemId = $form.find('.js-form-field-id').val();
			var $message = $(TRACK_ID_PREFIX + itemId);
			if (config && config.track && config.track.isFocusGroupMember) {
				window.appTracks.$refs.trackList.removeTrack(itemId);
			} else {
				var $childrenTrack = $message.children();
				// Проверяем если есть вложенность
				if ($childrenTrack.length > 1) {
					$childrenTrack.first().remove();
				} else {
					$message.remove();
				}
			}
			// Блок с файлами в сайдбаре
			renderSidebarFiles(response.data.sidebarFiles);
		} else {
			alert(response.data.message);
		}

		$('.js-remove-message-check-modal').modal('hide');

	}, 'json');
	return false;
});

$(document).on('submit', '.js-send-edit-message', function (e) {
	e.preventDefault();
	ajaxSendTrackMessage($(this), '#edit-files-conversations');
})

var scrollToHashTrack = function() {
	var hash = window.location.hash;

	var $target = $(hash);

	if($target.length) {
		$('html, body').animate({
			scrollTop: $target.offset().top - 100
		}, 200, function() {
			history.replaceState(null, null, ' ');
		});
	}
}

$(document).ready(function () {
	window.scrollToInit = false;

	if(window.location.pathname === '/track' && window.location.hash) {
		window.scrollToInit = true;
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}
	}
});

$(window).load(function () {
	countboxBehavior();

	readTrackService.init({orderId: Track.orderId, opponentId: Track.opponentId});

	window.bus.$on('scrollToUnread', function() {
		scrollToFirstUnreadTrack();
	});

	var $uploadPortfolio = $('.js-show-track-portfolio');

	setTimeout(function() {
		if(!window.scrollToInit) {
			if (!scrollToFirstUnreadTrack() || $uploadPortfolio.length) {
				var needScroll = getGetParams()['scroll'] == '1';
				var needScrollCheckbox = getGetParams()['scrollCheckbox'] == '1';
				if (needScroll || needScrollCheckbox) {
					if ($uploadPortfolio.length) {
						$('html, body').animate({
							scrollTop: $uploadPortfolio.offset().top
						}, 1);

						$uploadPortfolio.find('.sortable-card-list .upload').click();
					} else if (needScrollCheckbox && $('.js-track-stages-checkbox').length) {
						$('html, body').animate({
							scrollTop: $('.js-track-stages-checkbox').closest('.step-block-order_item').offset().top
						}, 200);

						var newURL = location.href.split("&scrollCheckbox")[0];
						window.history.replaceState('object', document.title, newURL);
					} else {
						$('html, body').animate({
							scrollTop: $(".step-block-order_item:last").offset().top
						}, 200);
					}
				}
			}
		} else {
			scrollToHashTrack();
		}
	}, 200)


	$('.js-user-online-block, .js-online-icon').onlineWidget();

	if ('undefined' !== typeof cancelReasons) {
		trackCancelModule.init({
			"initialButton": ".js-cancel-order-initial-button",
			"mainPopupTemplate": ".js-cancel-order-main-popup-template",
			"subTypePopupTemplate": ".js-cancel-order-subtype-popup-template",
			"mainPopupClass": "popup-order-cancel",
			"subTypePopupClass": "subtype-popup-order-cancel",
			"reasons": cancelReasons
		});
	}

	// одну пустую опцию мы должны иметь сразу.
	if (!$('#foxPostForm').find('li').length) {
		addCustomOption();
	}

	updateExtraPrices();

	newContentProcess(document);

	$(document).on("input keyup keydown", "#message_body", function () {
		var button = $(".btn-disable-toggle");
		if (StopwordsModule._testContacts($(this).val()).length == 0) {
			button.removeClass('disabled').prop('disabled', false);
		} else {
			button.addClass('disabled').prop('disabled', true);
		}
	});

	var upgradePackageEl = '#suggestExtras .js-upgrade-package';
	if ($(window).width() < 768) {
		upgradePackageEl = '#suggestExtras .js-toggle-wrapper-worker';
		$(document).on('click', '#suggestExtras .js-upgrade-package', function (e) {
			e.preventDefault();
		});
	}
	$(document).on('click', upgradePackageEl, upgradePackage);

	// раскрывание блоков о возможном снижении лояльности
	$(document).on('click','.track-loyality-title', function() {
		var $title = $(this);
		var $more = $title.closest('.track-loyality-wrapper').find('.track-loyality-more');
		if ($more.is(':visible')) {
			$more.slideUp(200);
			$title.find('.ico-arrow-down').removeClass('rotate180');
		} else {
			$more.slideDown(200);
			$title.find('.ico-arrow-down').addClass('rotate180');
		}
	});

	// инициализируем текстовый редактор
	// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - удалить проверку
	if (config.track.isFocusGroupMember) {
		initMessageBodyTrumbowyg();
	}
});

/**
 * Управляет видимостью сообщения о возможном снижении лояльности покупателя в связи с просрочкой
 */
function toggleLoyalityLate() {
	if (Track.loyalityLateVisible) {
		$('.loyality-late').removeClass('hidden');
	}
}

function editReport(trackId, lastEdit, message) {
	$("#track-id-" + trackId).find(".hide-edit-action").toggleClass('hide');
	$("#track-id-" + trackId).find(".loadbar").toggleClass('enable');

	if (message) {
		$("#track-id-" + trackId).find(".report-message-block ").html(message);
		$("#track-id-" + trackId).find("#kwork_report_message").html(message);
	}

}

function removeReport() {
	$(".new-report").remove();
}

function hintInit() {
	$('.report-message').each(function () {
		descAreaHint.init($(this).find(".js-field-input-description"), $(this).find(".js-field-input-hint"), 0, 350);
	});
}

$(document).ready(function () {

	$('.js-fixed-tooltip .tooltipster').removeClass('tooltipstered');

	var packageExpandEl = '.js-package-expand';
	if ($(window).width() < 768) {
		packageExpandEl = '.js-toggle-wrapper-worker';
	}
	$(packageExpandEl).on('click', function(e) {
		var t = $(e.delegateTarget);
		var type = t.data('type');

		if (t.hasClass('js-toggle-wrapper-worker')) {
			t = t.find('.js-package-expand');
			type = t.data('type');

			$('.package-option.active:not([data-type="' + type + '"])').removeClass('active').hide();
			$('.js-package-expand.expanded:not([data-type="' + type + '"])').removeClass('expanded');
			$('.js-package-expand:not([data-id="' + type + '"]) .rotate180').removeClass('rotate180');
		}

		var packageOptions = $('.package-option[data-type="' + type + '"]');
		var img=t.children('img');
		if(!t.hasClass('expanded')) {
			t.addClass('expanded');
			img.addClass('rotate180');
			packageOptions.addClass('active').fadeIn(150);
		} else {
			t.removeClass('expanded');
			img.removeClass('rotate180');
			packageOptions.fadeOut(150).removeClass('active');
		}
	});

	var TipsModule = (function () {
		/**
		 * Ссылки на элементы
		 */
		var _dom = {
			$tipsBlock: $(".tips"),
			$commentBlock: $(".comment-block"),
			$otherSumBlock: $(".js-other-sum"),
			$otherSumError: $(".js-other-sum .input-error"),
			$choseBonus: $(".choose-bonus"),
			$message: $("textarea[name='tips_message']"),
			$form: $("#js-tips-send-form"),
			inputs: {
				$sum: $("#tips-sum"),
				$anotherSum: $("#tips-another-sum")
			}
		};

		/**
		 * Тексты ошибок
		 */
		var _errors = {
			"sumEmpty": t("Укажите сумму бонуса"),
			"maxSumError": t("Сумма должна быть не меньше {{min}} и не более {{max}}")
		};

		/**
		 * Возвращает текст ошибки по коду, с параметрами
		 * @param error - код ошибки
		 * @param replaceParams - массив заменяемых параметров
		 * @return {*}
		 */
		var _getErrorText = function (error, replaceParams) {
			var errorText = _errors[error];
			if(replaceParams !== undefined) {
				return errorText.replace(/({{.*?}})/g, function (str, p) {
					var param = p.replace(/([{}]*)?/g, '');
					return replaceParams[param];
				});
			} else {
				return errorText;
			}
		};

		/**
		 * Установка событий
		 */
		var _setEvents = function() {
			_dom.$tipsBlock.on("click", ".choose-bonus li", _choseBonusEvent);
			_dom.$tipsBlock.on("keydown", ".js-other-sum input[name='sum']", _sumInputEvent);
			_dom.$tipsBlock.on("click", ".js-send-bonus", _sendBonusEvent);
			_dom.$tipsBlock.on("click", ".js-cancel-bonus", _cancelBonus);
			_dom.inputs.$anotherSum.on('keydown', _validateNumber);
			_dom.inputs.$anotherSum.on('change', _validateAnotherSum);
		};
 
		/**
		 * Проверка на ввод числа
		 */
		var _validateNumber = function(event) {
			var key = event.which;
			if (event.keyCode === 8 || event.keyCode === 46) {
				return true;
			} else if ( key < 48 || key > 57 ) {
				return false;
			} else {
				return true;
			}
		};

		/**
		 * Валидация суммы другой цены
		 */
		var _validateAnotherSum = function (event) {
			var target = event.target;
			var value = parseInt(target.value);

			if(isNaN(value)) {
				return false;
			} else {
				if(value > target.dataset.max) {
					target.value = target.dataset.max;
				}
				if(value < target.dataset.min) {
					target.value = target.dataset.min;
				}
			}
		}

		/**
		 * Событие выбора бонуса
		 */
		var _choseBonusEvent = function() {
			var $this = $(this);

			if($this.hasClass("green-btn")) {
				return;
			}

			_dom.$choseBonus.find(".green-btn").removeClass("green-btn").addClass("white-btn");
			$this.removeClass("white-btn").addClass("green-btn");

			var isCommentVisible = _dom.$commentBlock.is(":visible");

			if($this.hasClass("js-btn-other-sum")) {
				if(isCommentVisible) {
					_dom.$otherSumBlock.slideDown();
				} else {
					_dom.$otherSumBlock.show();
				}
				_dom.inputs.$anotherSum.val("");
			} else {
				_dom.$otherSumBlock.slideUp();
			}
			if(!isCommentVisible) {
				_dom.$message.val("");
				_dom.$commentBlock.slideDown();
			}

			_dom.$otherSumError.hide();
		};

		/**
		 * Событие ввода в поле Сумма
		 */
		var _sumInputEvent = function(e) {
			// разрешаем вводить только цифры
			if(e.key.match(/[^0-9]/gi) && e.key.length <= 1 && e.ctrlKey === false) {
				return false;
			}
		};

		/**
		 * Событие при клике на кнопку "Отмена"
		 */
		var _cancelBonus = function() {
			_dom.$choseBonus.find(".green-btn").removeClass("green-btn").addClass("white-btn");
			_dom.$otherSumBlock.slideUp();
			_dom.$commentBlock.slideUp();
		};

		/**
		 * Событие отправки бонуса
		 */
		var _sendBonusEvent = function() {
			var sum;
			// делаем валидацию поля Сумма
			if (_dom.inputs.$anotherSum.is(":visible")) {
				var error = false;
				sum = _dom.inputs.$anotherSum.val();
				if(sum === "") {
					_dom.$otherSumError.text(_getErrorText("sumEmpty"));
					error = true;

				} else {
					var inputSum = sum;
					var minSum = _dom.inputs.$anotherSum.data("min");
					var maxSum = _dom.inputs.$anotherSum.data("max");
					if(inputSum < minSum || inputSum > maxSum) {
						_dom.$otherSumError.text(_getErrorText("maxSumError", {min: minSum, max: maxSum}));
						error = true;
					}
				}

				if(error) {
					_dom.$otherSumError.slideDown("fast");
					return;
				} else {
					_dom.$otherSumError.slideUp("fast");
				}
			} else {
				sum = _dom.$choseBonus.find(".green-btn").data("sum");
			}

			// Записываем сумму в скрытый input перед отправкой
			_dom.inputs.$sum.val(sum);

			// Отправка формы (обработка в fox.js если нехватает денег)
			_dom.$form.submit();
		};

		return {
			init: function() {
				_setEvents();
			}
		}
	})();

	TipsModule.init();

});

/**
 * Получить паттерн из тегов для замены
 * @returns {RegExp}
 */
var _getPatternTags = function () {
	return /<p>|<\/p>|<li>|<\/li>|<ol>|<\/ol>|<strong>|<\/strong>|<em>|<\/em>|<br>|<\/br>|<div>|<\/div>|<span>|<\/span>|<word-error>|<\/word-error>/gi;
};

/**
 * Получить длину строки без тегов
 * @param {string} html
 * @returns {Number}
 */
var _getTextLengthWithoutTags = function (html)
{
	var pattern = _getPatternTags();
	return _getTextLength(html.replace(pattern, ""));
};

var _getTextLength = function (html)
{
	return html.replace(/&nbsp;/gi, " ").replace(/\s\s+/g, " ").length ^ 0;
};

var _onInputEditor = function (e) {
	var $contentStorage = $(this).siblings('.js-content-storage');
	var string = $(this).html();
	string = string.replace(/(?!<\/?(word-error)>)<\/?[\s\w="-:&;?]+>/gi, ""); // удаляем все теги кроме word-error

	var max = $contentStorage.data("max");
	var editorLength = _getTextLengthWithoutTags(string);
	// если для поля задано максимальное значение и оно превышено не даём вводить и вставлять текст
	if (max && editorLength > max) {
		var diff = editorLength - _getTextLengthWithoutTags($contentStorage.val());
		var selection = saveSelection($(this)[0]);

		selection.end = selection.end - diff;
		selection.start = selection.start - diff;

		$(this).html($contentStorage.val());
		restoreSelection($(this)[0], selection);

		return;
	}

	$contentStorage.val(string);
};

var _onPortfolioFieldsHideError = function (e) {
	var block = $(this).parents('.js-field-block');
	block.find('.kwork-save-step__field-error').text('');
};

//пересчет стоимости предложенных услуг при клике по предложению повышению пакета
var upgradePackage = function () {
	var _this = $(this);

	if (_this.hasClass('js-toggle-wrapper-worker')) {
		_this = _this.find('.js-upgrade-package');
	}

	var packageCheckbox = _this.prev('.styled-checkbox');
	var packageCurrent = _this.closest('.js-package-price');
	var packageId = packageCurrent.data('id');
	var packageName = packageCurrent.data('package-name');
	var packagePrice = packageCurrent.data('package-price');
	var packagePriceBuyer = packageCurrent.data('package-buyer-price');

	var packageDefault = $('#suggestExtras .js-current-package-price');
	var packageDefaultName = packageDefault.data('package-name');
	var packageDefaultPrice = packageDefault.data('package-price');
	var packageDefaultPriceBuyer = packageDefault.data('package-buyer-price');

	var packageNameNew = '';
	var packagePriceNew = 0;
	var packagePriceBuyerNew = 0;

	var orderExtra = $('#foxPostForm .order-extra-item:first');
	var orderExtraLabel = orderExtra.find('label');
	var orderExtraInput = orderExtra.find('.styled-checkbox');
	var orderExtraSelect = orderExtra.find('.chosen_select');
	var orderExtraSelectSize = orderExtra.find('.chosen_select option').length;
	var orderExtraSelectSelected = orderExtra.find('.chosen_select option:selected').index();

	//снимаем все чекбоксы, кроме текущего
	$('#suggestExtras .js-package-price').each(function () {
		if ($(this).data('id') !== packageId) {
			$(this).find('.js-upgrade-package').prev('.styled-checkbox').prop('checked', false);
		}
	});
	if (packageCheckbox.is(':checked')) {
		packageCheckbox.prop('checked', false);
		packageNameNew = packageDefaultName;
		packagePriceNew = packageDefaultPrice;
		packagePriceBuyerNew = packageDefaultPriceBuyer;
	} else {
		packageCheckbox.prop('checked', true);
		packageNameNew = packageName;
		packagePriceNew = packagePrice;
		packagePriceBuyerNew = packagePriceBuyer;
	}

	if (jQuery('.js-volume-order').length === 0) {
		//меняем название пакета в опции "Количество пакетов"
		orderExtraLabel.text(t('Количество пакетов "{{0}}"', [packageNameNew]));
	}

	//актуализируем цены в скрытом инпуте
	orderExtraInput
		.attr('data-price-worker', packagePriceNew).data('price-worker', packagePriceNew)
		.attr('data-price', packagePriceBuyerNew).data('price', packagePriceBuyerNew);

	//актуализируем цену в опции "Количество пакетов"
	var orderExtraSelectHtml = '';
	for (var i = 1; i <= orderExtraSelectSize; i++) {
		orderExtraSelectHtml += '' +
			'<option value="' + i + '"' + ((i - 1) === orderExtraSelectSelected ? ' selected' : '') + '>' +
			i +
			' (' + t('{{0}} Р', [(packagePriceNew * i)]) + ')' +
			'</option>';
	}
	if (orderExtraSelectHtml.length > 0) {
		orderExtraSelect.html(orderExtraSelectHtml).trigger('chosen:updated');
	}

	updateExtraPrices();

	//считаем цену
	calculateSuggestExtrasTotal();
};
/**
 * Показ попапа подтверждения заказа как выполненного
 */
function showConfirmInprogressDonePopup() {
	$('#confirm_inprogress_done_popup_content').modal("show");
}

/**
 * Скрываем попапа подтверждения заказа как выполненного
 */
function hideConfirmInprogressDonePopup() {
	$('#confirm_inprogress_done_popup_content').modal("hide");
}

/**
 * Показ попапа успешной сдачи заказа
 */
function showSuccessOrderPopupContent(workTime, countOrders, level, nextLevelText, badge) {
	$('.js-popup-success-order__work-time').html(workTime);
	$('.js-popup-success-order__count-orders').html(countOrders);
	$('.js-popup-success-order__level').html(level);
	$('.js-popup-success-order__next-level-text').html(nextLevelText);
	if(badge === null) {
		$('.js-popup-success-order__user-badge').hide();
	} else {
		$('.js-popup-success-order__user-badge').attr('src', badge);
	}


	$('#success_order_popup_content').modal("show")
	.on('hide.bs.modal', function (event) {
		location.reload();
	});
}

/**
 * Отправляем запрос о завершение заказа и получаем данные для отображения информации о покупателе и заказа
 */
function sendAjaxConfirmOrder($form) {
	// Показыаем если нет активного модального окна
	if(!$('body').hasClass('modal-open')) {
		lockBodyForPopup();
		showDefaultLoader();
	}
	$.post($form.attr('action'), $form.serialize(), function (response) {
		// Скрываем если нет активного модального окна(иначе глюк с правым отступом у модалки)
		if(!$('body').hasClass('modal-open')) {
			unlockBodyForPopup();
			hideDefaultLoader();
		}
		if (response.success) {
			// Если с сервера пришла ссылка для редиректа, редиректим
			if (response.redirectUrl !== undefined) {
				location = response.redirectUrl;
			}
			else {
				$('.modal').modal('hide');
				// отображаем попап с информацией заказа и уровне покупателя
				showSuccessOrderPopupContent(response.data.workTime, response.data.countOrders, response.data.level, response.data.nextLevelText, response.data.badge);
			}
		}
	});
}

/**
 * Инициализируем текстовый редактор
 */
function initMessageBodyTrumbowyg() {
	initEmojiTrumbowyg($('#message_body1'));
	initEmojiTrumbowyg($('#mobile_message'));
}

/**
 * Вставляем выбранное эможи в редактор Trumbowyg используется и для редактирования
 *
 * @param {object} editor - селектор c инициализированным плагином Trumbowyg.
 * @param {string} code - код эможи.
 */
function emojiInsertToTrumbowyg($editor, code) {
	var $trumbowygEditor = $editor.siblings(".trumbowyg-editor");	
	
	// Получаем предыдущий результат
	window.trumbowygCaret.beginEdit($editor);
	var html = window.trumbowygCaret.getHtml($editor);
	var lastHtml = html;
	window.trumbowygCaret.endEdit(html);
	
	// Заупкаем для вставки эможи
	window.trumbowygCaret.beginEdit($editor);
	
	// Формируем тег эможи
	var unicode = window.emojiReplacements.codeToUnicode(code);
	var $emojiImg = $('<img class="message-emoji" src="' + Utils.cdnImageUrl( "/emoji/" + code + ".svg") + '" alt="'+unicode+'" />');
	
	// Вставляем эможи
	if ($trumbowygEditor.find('.rangySelectionBoundary').length) {
		$trumbowygEditor.find('.rangySelectionBoundary').before($emojiImg);
	} 
	// Если коретки нет
	else if ($trumbowygEditor.find("div:last").length) {
		// Если в конце дива br то добавляем перед
		if(/br[^>]?\>$/.test($trumbowygEditor.find("div:last").html())) {
			$trumbowygEditor.find("div:last").find('br:last').before($emojiImg);
		} else {
			$trumbowygEditor.find("div:last").append($emojiImg);
		}
		
	} 
	else {
		$trumbowygEditor.append($emojiImg);
	}
	
	// Получаем текущий результат
	html = window.trumbowygCaret.getHtml($editor);	
	
	// Следим за превышением лимита символов
	html = window.trumbowygCaret.replaceLastHtml(html, lastHtml, 4000);
	window.trumbowygCaret.endEdit(html);

	$editor.trigger('input');
}

// Если открыто модальное окно и в заказе произошли изменения,
// оповещаем об этом пользователя (текущее окно заменется на алерт с предупреждением)
var alertUpdateOrder = function() {
	if ($('#order-data .modal, #track-form .modal').is(":visible")) {
		$('.modal').modal('hide');
		$('.js-modal-alert-update-order').modal('show');
	}
};

// image modal element
var imageModal = null;

/**
 * Show modal with attached image
 * @param {Number} modalId Current modal unique id
 * @returns {Void}
 */
var showImageModal = function(modalId) {
	imageModal = $('#' + modalId);
	setImageModalHeight();
	// relocate modal in body to avoid header's z-index
	if (imageModal[0].parentElement.nodeName !== 'BODY') {
		$('body').append(imageModal);
	}
	$('body').css('overflow-y', 'hidden');
	imageModal.css('display', 'flex').fadeIn(300);
};

/**
 * Close modal with attached image
 * @returns {Void}
 */
var closeImageModal = function() {
	if (!imageModal) {
		return null;
	}
	$(imageModal).fadeOut(300);
	$('body').css('overflow-y', '');
}


/**
 * Set deferred method on window's resize
 * to avoid browser's excessive load
 * @returns {Void}
 */
var imageModalTimer;
window.onresize = function() {
	if (!imageModal) {
		return null;
	}
	clearTimeout(imageModalTimer);
	imageModalTimer = setTimeout(onWindowResized, 200);
}
function onWindowResized(){
	setImageModalHeight();
}

/**
 * Set modal's wrapper & image max-height
 * equals window.innerHeight
 * @returns {Void}
 */
function setImageModalHeight() {
	var height = window.innerHeight + 'px';
	$(imageModal).find('.track-image-modal__wrapper')[0].style.maxHeight = height;
	$(imageModal).find('img')[0].style.maxHeight = height;
}

// close image modal on keyup to escape
$(document).on('keyup', function(event) {
	var escapeKeyCode = 27;
	if (event.keyCode !== escapeKeyCode) {
		return null;
	}
	closeImageModal()
});

/**
 * Toggle miniature image's condition, fold / unfold
 * @param {Object} event Click event
 * @param int Id файла
 * @returns {Void}
 */
function toggleMiniatureImage(event, fileId) {

	var attachment = {
		element: {},
		selector: 'track-files__header',
		modifier: 'track-files__header_folded'
	}
	var stateObj = $(event.target).parents(".track-files__header");
	var state = stateObj.attr("data-hide");
	if (state == 1) {
		stateObj.attr("data-hide", 0);
		$.post("track_image_show", {id: fileId});
	} else {
		stateObj.attr("data-hide", 1);
		$.post("track_image_hide", {id: fileId});
	}

	// find and set element
	attachment.element = $(event.target).closest('.' + attachment.selector)
	// toggle condition
	attachment.element.toggleClass(attachment.modifier)

}

/**
 * Вставляем выбранное эможи в редактор
 */
$(document).on("emoji-panel.click", ".js-mf-plus__app-emoji-btn", function(event, code) {	
	emojiInsertToTrumbowyg($('#message_body1'), code);
});
