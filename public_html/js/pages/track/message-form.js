class MessageForm {
	constructor() {
		this.newForm = false;
		this.desktopMessageField = null;
		this.desktopSubmitButton = null;
		this.desktopNewSubmitButton = null;
		this.windowInitialized = false;
		this.isBusy = false;
		this.errorObserver = null;
		this.updateCommentBoxTimeout = null;
	}

	setBusy(state) {
		this.isBusy = state;
		window.toggleSubmitButton();
	}

	handleMessageInput() {
		let text = this.desktopMessageField.val();
		if (this.desktopMessageField.hasClass('trumbowyg-textarea')) {
			text = this.desktopMessageField.trumbowyg('html');
		}
		
		window.toggleSubmitButton();
	}

	handleMobileMessageInput() {
		let text = this.mobileMessageField.val();
		if (this.mobileMessageField.hasClass('trumbowyg-textarea')) {
			text = this.mobileMessageField.trumbowyg('html');
		}

		if (!this.messageFieldNoRecursion) {
			this.messageFieldNoRecursion = true;
			
			if (this.desktopMessageField.hasClass('trumbowyg-textarea')) {
				this.desktopMessageField.trumbowyg('html', text);
				this.desktopMessageField.trigger('tbwchange');
			} else {
				this.desktopMessageField.val(text).trigger('input');
			}
			
			setTimeout(() => {
				this.messageFieldNoRecursion = false;
			}, 0);
		}

		window.toggleSubmitButton();
	}

	initStatic() {
		window.toggleSubmitButton = (state) => {
			let fileldState = (window.appTracks && window.appTracks.$refs && window.appTracks.$refs.trackList.isBusy());
			this.desktopMessageField.prop('disabled', fileldState);
			if (this.desktopMessageField.hasClass('trumbowyg-textarea')) {
				if (fileldState) {
					this.desktopMessageField.trumbowyg('disable');
				}
				else {
					this.desktopMessageField.trumbowyg('enable');					
				}
			}

			let hasContentDesktop = TrackUtils.checkHasContentDesktop(!this.newForm);
			let hasExtraData = TrackUtils.checkHasExtraData();

			if (this.desktopNewSubmitButton.length) {
				if (!this.isBusy && state !== false && (hasExtraData || hasContentDesktop)) {
					this.desktopNewSubmitButton.removeClass('disabled').prop('disabled', false);
				} else {
					this.desktopNewSubmitButton.addClass('disabled').prop('disabled', true);
				}
			}
			
			desktopButtonActive = (this.newForm ? hasExtraData : hasContentDesktop);
			if (!this.isBusy && state !== false && desktopButtonActive) {
				this.desktopSubmitButton.removeClass('disabled');
			} else {
				this.desktopSubmitButton.addClass('disabled');
			}
		}

		this.newForm = true;
	}

	initDynamic() {
		this.form = $('.mf-comment-box');
		this.desktopMessageField = $('#message_body1');
		this.desktopSubmitButton = $('#track-send-message-button');
		this.desktopNewSubmitButton = $('#new-desktop-submit');
		
		if (this.desktopNewSubmitButton.length < 1) {
			return;
		}

		if (!this.desktopMessageField.data('mfInit')) {
			this.desktopMessageField.on('input', () => {
				this.handleMessageInput();
			});
			this.desktopMessageField.on('tbwchange', () => {
				this.handleMessageInput();
			});
			this.desktopMessageField.data('mfInit', true);
		}

		if (this.desktopNewSubmitButton.length && !this.desktopNewSubmitButton.data('mfInit')) {
			this.desktopNewSubmitButton.on('click', (e) => {
				e.preventDefault();
				TrackUtils.forceSend();
				this.desktopSubmitButton.trigger('click');
				return false;
			});
			this.desktopNewSubmitButton.data('mfInit', true);
		}
		
		if (this.errorObserver) {
			this.errorObserver.disconnect();
		}
		this.errorObserver = new MutationObserver(() => {
			if (this.updateCommentBoxTimeout) {
				clearTimeout(this.updateCommentBoxTimeout);
			}
			this.updateCommentBoxTimeout = setTimeout(() => {
				this.updateCommentBoxState();
			}, 0);
		});
		this.errorObserver.observe($('.js-stopwords-error-wrapper')[0], {
			childList: true,
			subtree: true,
		});

		window.toggleSubmitButton();
	}

	updateCommentBoxState() {
		let height = this.form.height();
		this.form.toggleClass('m-clamped', (height < 5));
	}
}

window.messageForm = new MessageForm();

$(document).ready(() => {
	window.messageForm.initStatic();
	window.messageForm.initDynamic();
});
