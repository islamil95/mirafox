export default class Draft {
	constructor(pars) {
		this.mode = pars.mode,
		this.messageFieldsSelectors = pars.messageFields || [],
		this.fileUploaders = pars.fileUploaders;

		this.skipFrame = false;
		this.skipFrameTimeout = null;
		this.sendInterval = null;
		this.sendXhr = null;
		this.firstTimeSkip = true;
		this.lastMessageId = -1;
		this.contentChanged = false;
		// Признак что надо очистить черновик на сервере без очистки полей у пользователя на фронте
		this.isManualRemove = false;
		this.prevVal = '';

		this.updateContent();
		this.restoreDraft();
		
		this.setEvents();
	}

	restoreDraft() {
		this.tryToRestoreDraft(window.draftData || null, true, true);
	}

	updateContent() {
		this.messageBlocks = $(this.messageFieldsSelectors.join(', '));
		this.prevVal = this.messageBlocks.val();
		this.messageBlocks.each((k, v) => {
			let el = $(v);
			if (!el.data('handled')) {
				// TODO: Убрать инпут когда в личный сообщениях и в трэке будет редактро trumbowyg
				el.on('input', () => {
					this.activateByInput({
						field: v,
					});
				});
				el.on('tbwchange', () => {
					this.activateByInput({
						field: v,
					});
				});
				el.data('handled', true);
			}
		});

		this.updateLastMessageId();
	}

	updateLastMessageId() {
		let lastMessageId = 0;
		if (this.mode == 'inbox') {
			lastMessageId = window.conversationApp.$refs.conversationMessagesList.getLastExistedMessageMID(true);
		} else {
			_.forEachRight($('#tracks-wrapper').find('.tr-track'), (v, k) => {
				let el = $(v);
				let id = 0;
				if (el.hasClass('inprogress-check')) {
					id = el.data('trackId');
				} else {
					id = el.find('.text_message').data('trackId');
				}
				if (id && el.data('userId') == window.actorId) {
					lastMessageId = id;
					return false;
				}
			});
		}
		if (this.lastMessageId > -1 && this.lastMessageId != lastMessageId) {
			this.clearForm();
			this.clear();
		}
		this.lastMessageId = lastMessageId;
	}

	skipThisFrame() {
		this.skipFrame = true;
		if (this.skipFrameTimeout) {
			clearTimeout(this.skipFrameTimeout);
		}
		this.skipFrameTimeout = setTimeout(() => {
			this.skipFrame = false;
			this.skipFrameTimeout = null;
		}, 0);
	}

	clearForm() {
		if (!this.messageBlocks.length) {
			return;
		}
		this.skipThisFrame();
		
		if(this.messageBlocks.hasClass('trumbowyg-textarea')) {
			this.messageBlocks.trumbowyg('html', '');	
			this.messageBlocks.trigger('input');	
		} else {	
			this.messageBlocks.val('').trigger('input');
		}
		
		if (window.appFiles && window.appFiles.$refs.fileUploader) {
			window.appFiles.$refs.fileUploader.clearFiles();
		}
	}

	tryToRestoreDraft(savedDraftJson, alreadyParsed = false, filesOnly = false) {
		if (!this.messageBlocks.length) {
			return;
		}
		let success = false;
		let savedDraft = {};
		if (alreadyParsed) {
			savedDraft = savedDraftJson;
		} else {
			try {
				savedDraft = JSON.parse(savedDraftJson);
			} catch (e) {}
		}
		if (!_.isObject(savedDraft)) {
			return false;
		}
		if (!filesOnly && savedDraft && savedDraft.message) {
			this.messageBlocks.val(savedDraft.message);
			if(this.messageBlocks.hasClass('trumbowyg-textarea')) {
				this.messageBlocks.trumbowyg('html', savedDraft.message);		
			}
			success = true;
		}
		if (savedDraft && savedDraft.files) {
			if (window.appFiles && window.appFiles.$refs.fileUploader) {
				window.appFiles.$refs.fileUploader.applyFileList(savedDraft.files, true);
			}
			success = true;
		}
		return success;
	}

	clear(withRemote = false) {
		this.abortSendXhr();
		this.contentChanged = false;
		window.draftData = null;
		if (withRemote) {
			this.removeRemoteDraft();
		}
	}

	abortSendXhr() {
		if (this.sendXhr) {
			this.sendXhr.abort();
			this.sendXhr = null;
		}
	}

	activateByInput(data) {
		if (this.skipFrame) {
			return;
		}
		if (this.firstTimeSkip) {
			this.firstTimeSkip = false;
			return;
		}
		if (data.field) {
			this.skipThisFrame();
			let val = $(data.field).val();
			if($(data.field).hasClass('trumbowyg-textarea')) {
				val = $(data.field).trumbowyg('html');		
			}
			this.messageBlocks.each((k, v) => {
				if (v != data.field) {					
					if($(v).hasClass('trumbowyg-textarea')) {
						$(v).trumbowyg('html', val);	
						$(v).trigger('input');	
					} else {	
						$(v).val(val).trigger('input');
					}
					
				}
			});
			// Если поле ввода пустое, отправляем на сервер
			if(val !== this.prevVal && val === '') {
				this.prevVal = val;
				this.manualRemove();
				return;
			}
			this.prevVal = val;
		}
		this.contentChanged = true;
		if (!this.sendInterval) {
			this.launchInterval();
		}
	}

	launchInterval() {
		this.sendInterval = setInterval(() => {
			this.sendToBackend();
		}, 20000);
	}

	addSubjectId(formData) {
		if (this.mode == 'track') {
			formData.append('orderId', window.track.orderId);	
		} else {
			formData.append('recipientId', window.conversationUserId);		
			// Запоминаем что это сообщение содержит ссылка на кворк
			if (window.requestKwork) {
				formData.append('kworkId', window.requestKwork);
			}
			// Запоминаем что это сообщение содержит предложение на бирже
			if (window.offerId) {
				formData.append('offerId', window.offerId);
			}
		}
	}

	removeRemoteDraft() {
		this.abortSendXhr();
		this.contentChanged = false;
		let formData = new FormData();
		this.addSubjectId(formData);
		formData.append('action', 'remove');
		formData.append('is_draft', '1');
		this.sendXhr = $.ajax({
			url: '/' + this.mode + '/draft/remove',
			data: formData,
			processData: false,
			contentType: false,
			type: 'POST',
			complete: (r) => {
				this.sendXhr = null;
			},
		});
	}
	
	sendToBackend() {
		let action = '/' + this.mode + '/draft/edit';
		if (!this.messageBlocks.length || !this.contentChanged) {
			return;
		}
		let message = this.messageBlocks.eq(0).val();
		if(this.messageBlocks.eq(0).hasClass('trumbowyg-textarea')) {
			message = $('#message_body1').trumbowyg('html');		
		}
		if (!message && !window.appFiles.files.length) {
			this.removeRemoteDraft();
			return;
		}
		let sendData = new SendData();
		this.addSubjectId(sendData);
		// Пенерируем ключ для отложенной отправки если не получилось отправить запрос на сервер
		let draftKey = generateRandomKey(8);
		sendData.append('draftKey', draftKey);
		
		sendData.append('message_send_ajax', '1');
		sendData.append('is_ajax', 'true'),
		sendData.append('action', 'text');
		sendData.append('message', message);
		if (window.appFiles.files) {
			$.each(window.appFiles.files, function(k, v) {
				sendData.append('file-input[new][]', v.file_id);
			});
		}
		
		csSendQueue = [];
		csSendQueue.push({
			time: Math.floor(Date.now() / 1000),
			action: action,
			data: sendData.data,
		});
		csUpdateSendQueue();
		
		
		let formData = sendData.getFormData();
		
		this.abortSendXhr();
		this.contentChanged = false;
		this.sendXhr = $.ajax({
			url: action,
			data: formData,
			processData: false,
			contentType: false,
			type: 'POST',
			complete: (r) => {
				
				// Удаляем из очереди на отправку
				$.each(csSendQueue, function(k, v) {
					if (v.data.draftKey == draftKey) {
						csSendQueue.splice(k, 1);
						return false;
					}
				});
				
				let data = Utils.parseServerResponse(r);
				if (!data.success) {
					this.contentChanged = true;
				}
				this.sendXhr = null;
			},
		});
	}
	
	/**
	 * Отправка на удаление черновика вне таймера
	 */
	manualRemove() {
		this.isManualRemove = true;
		this.sendInterval = null;
		this.removeRemoteDraft();
	}
	
	/**
	 * События
	 */
	setEvents () {
		// Переключаем вкладку
		window.onblur = () => {
			this.sendInterval = null;
			this.sendToBackend();
		};
		// Переход по ссылке и Закрытие вкладки
		window.onunload = () => {
			this.sendInterval = null;
			this.sendToBackend();
		};
	}
}
