class QuoteMessage {

	constructor() {

		this.TYPE_PAGE_TRACK = 'track';
		this.TYPE_PAGE_CONVERSATION = 'conversations';

		this.selectors = {
			messageQuoteWrap: 'js-message-quote-wrap',
			messageQuoteBlock: 'js-message-quote',
			messageQuoteRemove: 'js-message-quote-remove',
			messageQuoteText: 'js-message-quote-text',
			messageTextInput: 'js-message-input-focus',
		};

		this.event();
	}

	event() {
		$(document)
			.off('click', '.' + this.selectors.messageQuoteRemove)
			.on('click', '.' + this.selectors.messageQuoteRemove, e => this.quoteMessageRemove(e))

			.off('click', '.' + this.selectors.messageQuoteBlock)
			.on('click', '.' + this.selectors.messageQuoteBlock, e => this.onQuoteScroll(e))
	}

	init(type_page) {
		this.type_page = type_page;
	}

	/**
	 * Цитироваие сообщения
	 * @param quoteId
	 * @param quoteText
	 * @param quoteUsername
	 */
	quoteMessage(quoteId, quoteText, quoteUsername) {
		let htmlQuote = '<div class="' + this.selectors.messageQuoteBlock + ' message-quote">' +
				'<div class="message-quote__login">' + quoteUsername + '</div>' +
				'<div class="message-quote__text">' + quoteText + '</div>' +
				'<div class="' + this.selectors.messageQuoteRemove + ' message-quote__remove"></div>' +
				'<input type="hidden" name="quoteId" value="' + quoteId + '">' +
			'</div>';

		$('.' + this.selectors.messageQuoteWrap).html('').append(htmlQuote);

		this.quoteFormScroll();
	}

	/**
	 * Удалить цитируемое сообщение
	 * @param e
	 */
	quoteMessageRemove(e) {
		// если уитируемое сообщение около формы отправки сообщения
		if ($(e.target).closest('.' + this.selectors.messageQuoteWrap).length) {
			$(e.target).closest('.' + this.selectors.messageQuoteWrap).empty();
		} else {
			$(e.target).closest('.' + this.selectors.messageQuoteBlock).remove();
		}
	}

	/**
	 * Скролить до формы с цитатой
	 */
	quoteFormScroll() {
		if ((isMobile() || $(window).width() < 768) && config.track.isFocusGroupMember) {
			this.onFocusFormMessage();
			return;
		}

		$('html, body').animate({
			scrollTop: $('.' + this.selectors.messageQuoteWrap).offset().top - $('.header_top').outerHeight()
		}, 800, this.onFocusFormMessage.bind(this));
	}

	/**
	 * Берем поле ввода в фокус
	 */
	onFocusFormMessage() {
		if ($('.' + this.selectors.messageTextInput).hasClass('trumbowyg-editor')) {
			window.trumbowygCaret.focusCaret($('.' + this.selectors.messageTextInput));
		} else {
			$('.' + this.selectors.messageTextInput).focus();
		}
	}

	/**
	 * Скролить до цитируемого сообщения
	 * @param e
	 */
	onQuoteScroll(e) {
		// при нажатии на "удалить" не скролим
		if ($(e.target).hasClass(this.selectors.messageQuoteRemove)) {
			return;
		}

		let quoteId = $(e.currentTarget).data('quote-id');

		if (this.type_page === this.TYPE_PAGE_TRACK) {
			this.onQuoteScrollTrack(quoteId, e);
		} else if (this.type_page === this.TYPE_PAGE_CONVERSATION) {
			this.onQuoteScrollConversation(quoteId);
		}
	}

	/**
	 * Скролить до цитируемого сообщения в заказах
	 * @param quoteId
	 */
	onQuoteScrollConversation(quoteId) {

		if (window.app.$refs.conversationMessagesList.$refs['message' + quoteId]) {
			this.messageQuoteScrollConversation(quoteId);

			return;
		}

		window.app.$refs.conversationMessagesList.showMoreMessages();

		// ждем когда прогрузятся все сообщения и скролим до нужного
		let showAllInterval = setInterval(() => {
			// если найдено цитируемое сообщение, то скролим до него
			if (window.app.$refs.conversationMessagesList.$refs['message' + quoteId]) {

				this.messageQuoteScrollConversation(quoteId);

				clearInterval(showAllInterval);
			}
		}, 300);
	}

	/**
	 * Скролить до цитируемого сообщения в диалогах
	 * @param quoteId
	 * @param e
	 */
	onQuoteScrollTrack(quoteId, e) {
		let $messageQuote = $('.text_message[data-message-quote-id="' + quoteId + '"]');

		if ($messageQuote.is(':visible')) {
			this.messageQuoteScrollTrack($messageQuote);

			return;
		}

		showTrackAll(e);

		// ждем когда прогрузятся все сообщения и скролим до нужного
		let showAllInterval = setInterval(() => {
			// если найдено цитируемое сообщение, то скролим до него
			if ($messageQuote.is(':visible')) {
				this.messageQuoteScrollTrack($messageQuote);

				clearInterval(showAllInterval);
			}
		}, 300);
	}

	/**
	 * Скролить до цитируемого сообщения в заказах
	 * @param $messageQuote
	 */
	messageQuoteScrollTrack($messageQuote) {
		$('html, body').animate({
			scrollTop: $messageQuote.offset().top - $('.header_top').outerHeight()
		}, 400);

		// делаем выделение цитируемого сообщения
		$messageQuote.addClass('hover');
		setTimeout(() => {
			$messageQuote.removeClass('hover');
		}, 2000);
	}

	/**
	 * Скролить до цитируемого сообщения в диалогах
	 * @param quoteId
	 */
	messageQuoteScrollConversation(quoteId) {
		window.app.$refs.conversationMessagesList.scrollToMessage(quoteId);

		// делаем выделение цитируемого сообщения
		$(window.app.$refs.conversationMessagesList.$refs['message' + quoteId][0].$el).addClass('hover');
		setTimeout(() => {
			$(window.app.$refs.conversationMessagesList.$refs['message' + quoteId][0].$el).removeClass('hover');
		}, 2000);
	}

	/**
	 * Обрезка цитаты
	 * @param $wrap
	 */
	messageQuoteCropEllipsis($wrap) {
		multiEllipsis($wrap.find('.' + this.selectors.messageQuoteText));
	}
}

window.QuoteMessage = new QuoteMessage();
