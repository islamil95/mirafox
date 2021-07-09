class SendForm {
	constructor(selector) {
		this.formId = generateRandomKey();
		this.selector = selector;
		this.altGraphPressed = false;
		this.sendOnUpload = true;
		this.lengthCalcBlock = null;
		this.lengthCalcNumber = null;
		this.sendButton = null;
		this.lastScrollbarWidth = 0;
		this.initialized = false;

		if (!this.initDynamic()) {
			return;
		}

		this.submitMode = 0;
		let currentOption = $('.message-send-current-options input[name="option_submitmode"]:checked');
		if (currentOption.length > 0) {
			this.submitMode = currentOption.attr('value');
		}

		if (!window.isChat || (window.isChat && jQuery(window).width() > 767)) {
			$(document).on('keyup', (e) => {
				if (e.key == 'AltGraph') {
					this.altGraphPressed = false;
				}
			});

			$(document).on('keydown', (e) => {
				if (e.key == 'AltGraph') {
					this.altGraphPressed = true;
				}
				
				if ((!this.desktopMessageField.is(':focus') && !this.desktopMessageField.hasClass('trumbowyg-textarea')) ||
				(!this.desktopMessageField.siblings(".trumbowyg-editor").hasClass('is-focus') && this.desktopMessageField.hasClass('trumbowyg-textarea'))) {
					return;
				}

				let code = e.keyCode || e.which;
				if (code == 13) {
					if ((this.submitMode == 1 && !e.shiftKey && !e.altKey && !e.ctrlKey && !this.altGraphPressed) || (this.submitMode == 0 && e.ctrlKey)) {
						if (typeof TrackUtils != 'undefined') {
							TrackUtils.forceSend();
						}
						this.submitFormButton.trigger('click');
						e.preventDefault();
						return false;
					}
					
					if (e.altKey) {
						let fieldVal = this.desktopMessageField.val();
						let fieldCursorPos = this.desktopMessageField[0].selectionEnd;
						fieldVal = fieldVal.substring(0, fieldCursorPos) + '\n' + fieldVal.substring(fieldCursorPos);
						this.desktopMessageField.val(fieldVal).focus();
						this.desktopMessageField[0].selectionEnd = fieldCursorPos + 1;
						this.desktopMessageField.trigger('input');
					}
				}
			});
		}

		if (PULL_MODULE_ENABLE) {
			PullModule.on(PULL_EVENT_MESSAGE_SUBMIT_MODE_CHANGED, (data) => {
				if (data.formId == this.formId) {
					return;
				}
				let mode = parseInt(data.mode);
				this.submitMode = mode;
				this.updateTooltipTemplate();
			});
		}

		window.bus.$on('messageSubmitModeChange', () => {
			let newSubmitMode = $('.tooltipster-box input[name="option_submitmode"]:checked').attr('value');
			this.submitMode = newSubmitMode;
			this.updateTooltipTemplate();
			$.post('/api/user/setmessagesubmitmode', {'mode': newSubmitMode, 'formId': this.formId});
		});

		$(window).on('resize', () => {
			this.growDesktopField();
			this.updateScrollbarPadding();
		});

		this.handleNewDesktopContent();
		this.initialized = true;
	}

	updateScrollbarPadding() {
		let $desktopMessageField = this.desktopMessageField;
		// TODO:7584 удалить проверку после тестового переуда
		if (this.desktopMessageField.hasClass('trumbowyg-textarea')) {
			$desktopMessageField = this.desktopMessageField.siblings(".trumbowyg-editor");
		}
		let scrollbarWidth = 0;
		if ($desktopMessageField.length > 0) {
			scrollbarWidth = $desktopMessageField[0].offsetWidth - $desktopMessageField[0].clientWidth;
		}
		if (scrollbarWidth == this.lastScrollbarWidth || !this.sendButton || this.sendButton.length <= 0) {
			return;
		}
		let posRight = scrollbarWidth;
		if (posRight > 0) {
			posRight -= 1;
		}
		this.sendButton.css({'right': (posRight + 8) + 'px'});
		this.lastScrollbarWidth = scrollbarWidth;
	}

	calcMessageLength(text) {
		let len = text.trim().length;
		if (this.lengthCalcBlock) {
			if (len >= 3500) {
				this.lengthCalcNumber.text(len);
				this.lengthCalcBlock.show();
			} else {
				this.lengthCalcBlock.hide();
			}
		}
	}

	updateTooltipTemplate() {
		$('#message-send-switch-tooltip input').prop('checked', false).removeAttr('checked');
		$('#message-send-switch-tooltip input[value="' + this.submitMode + '"]').prop('checked', true).attr('checked', '');
		if (this.sendButton && this.sendButton.length > 0 && this.sendButton.hasClass('tooltipstered')) {
			this.sendButton.tooltipster('option', 'updateAnimation', null).tooltipster('content', $('#message-send-switch-tooltip').html());
		}
	}

	initDynamic() {
		this.form = $(this.selector);
		if (this.form.length < 1) {
			return false;
		}

		this.desktopMessageInputRow = this.form.find('.mf-message-input');

		this.desktopMessageField = this.form.find('.js-message-input');
		if (this.desktopMessageField.length < 1) {
			return false;
		}
				
		this.submitFormButton = $(this.form.data('submitButton'));

		this.lengthCalcBlock = this.form.find('.mf-length-calc');
		if (this.lengthCalcBlock.length > 0) {
			this.lengthCalcNumber = this.lengthCalcBlock.find('span');
		} else {
			this.lengthCalcBlock = null;
			this.lengthCalcNumber = null;
		}

		if (!this.desktopMessageField.data('msfInit')) {
			this.desktopMessageField.on('input', () => {
				this.handleNewDesktopContent();
			});
			this.desktopMessageField.on('tbwchange', () => {
				this.handleNewDesktopContent();
			});
			this.desktopMessageField.on('tbwinit', () => {
				this.handleNewDesktopContent();
			});
			this.desktopMessageField.on('focus', () => {
				this.desktopMessageInputRow.addClass('focused');
			});
			this.desktopMessageField.on('blur', () => {
				this.desktopMessageInputRow.removeClass('focused');
			});
			this.desktopMessageField.on('tbwfocus', () => {
				this.desktopMessageInputRow.addClass('focused');
			});
			this.desktopMessageField.on('tbwblur', () => {
				this.desktopMessageInputRow.removeClass('focused');
			});
			this.desktopMessageField.data('msfInit', true);
		};
		
		this.desktopMessageFieldGrowEl = this.form.find('.message-body-sizer');

		this.plusButton = this.form.find('.mf-plus');

		if (!this.plusButton.data('msfInit')) {
			this.plusButton.find('.js-add-files').on('click', () => {
				window.appFiles.$refs.fileUploader.select();
			});
			this.plusButton.data('msfInit', true);
		}

		this.sendButton = this.form.find('.box-submit');

		return true;
	}

	tryToSubmitForm() {
		if (this.form.length < 1) {
			return true;
		}

		if (this.submitMode == 1) {
			return false;
		}

		return true;
	}

	handleNewDesktopContent() {
		let text = this.desktopMessageField.val();
		this.calcMessageLength(window.emojiReplacements.preSubmitMessage(text));
		this.growDesktopField(text);
		this.updateScrollbarPadding();

		if(this.initialized) {
			this.growDesktopField(text);
		}
	}

	growDesktopField(text = null) {
		if (this.desktopMessageFieldGrowEl) { 
			if (text === null) {
				text = this.desktopMessageField.val();
				if (this.desktopMessageField.hasClass('trumbowyg-textarea')) {
					text = this.desktopMessageField.trumbowyg('html');
				}
			}
			let currentHeight = this.desktopMessageField.outerHeight();
			this.desktopMessageFieldGrowEl.text(text);
			let newHeight = this.desktopMessageFieldGrowEl.outerHeight();
			this.desktopMessageField.css({'height': newHeight + 'px'});
			
			if (isMobile()) {
				return;
			}
			
			let offset = newHeight - currentHeight;
			let scrolledEl = $(window);
			if (window.isChat && scrolledEl.width() > 767) {
				scrolledEl = jQuery('.kwork-conversation__list .scrolly-viewport');
			}
			let currentScroll = scrolledEl.scrollTop();
			let newScroll = currentScroll + offset;

			if (offset >= 6) {
				scrolledEl.scrollTop(newScroll);
			} else if (window.isChat && scrolledEl.width() > 767) {
				newScroll = currentScroll + offset;
				scrolledEl.scrollTop(newScroll);
			}

			if (!window.isChat) {
				setTimeout(() => {
					let updatedScroll = scrolledEl.scrollTop();
					let topPos = this.desktopMessageFieldGrowEl.offset().top;
					let peak = updatedScroll + scrolledEl.height() - 150;
					if (topPos > peak) {
						scrolledEl.scrollTop(topPos - scrolledEl.height() + 150);
					}
				}, 0);
			}
		}
	}

	updateUploadButton(state) {
		if (!this.plusButton || !this.plusButton.length) {
			return;
		}
		if (state) {
			this.form.removeClass('no-upload');
		} else {
			this.form.addClass('no-upload');
		}
		this.form.find('.js-message-input').trigger('input');
	}
}

$(document).ready(() => {
	window.sendForm = new SendForm('.mf-form');
});
