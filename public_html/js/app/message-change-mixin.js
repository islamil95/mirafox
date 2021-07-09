export default {
	computed: {
		canEdit() {
			let item = this.track;
			if (!item.editable) {
				return false;
			}
			if(item.unread){
				return true;
			}
			if (item.time > this.readedChangeDisableTime) {
				return true;
			}
			item.editable = false;
			return false;
		},

		canRemove() {
			let item = this.track;
			if (!item.removable) {
				return false;
			}
			if(item.unread){
				return true;
			}
			if (item.time > this.readedChangeDisableTime) {
				return true;
			}
			item.removable = false;
			return false;
		},

		/**
		 * Можно ли цитировать сообщение
		 * @returns {boolean|default.props.isVisibleFormMessage|{default, type}}
		 */
		canQuote() {
			let sendInstructionBtn = $('.js-send-instruction-link').length,
				commentForm = $('.js-comment-box').length,
				messageBtn = $('.js-individual-message__popup-link').length;
			// проверяем есть ли кнопка предоставления информации по заказу или форма для ответа
			if (sendInstructionBtn > 0 || commentForm < 1 || messageBtn > 0) {
				return this.isVisibleFormMessage;
			} else {
				return true;
			}
		},
	},
}
