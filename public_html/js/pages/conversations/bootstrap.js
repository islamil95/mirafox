require('appJs/bootstrap.js');

// Работа с текстом
require('appJs/text.js');

require('appJs/order-info.js');

import ValidatableForm from 'appJs/validatable-form.js';
require('appJs/stages/offer-stages.js');
require('appJs/stages/offer-stage-modal.js');

// цитирование сообщения
require('appJs/quote-message.js');

Vue.component('price-with-currency', require('appJs/price-with-currency.vue').default);
Vue.component('file-uploader', require('appJs/file-uploader.vue').default);
Vue.component('conversation-files', require('./conversation-files.vue').default);
Vue.component('conversation-messages-list', require('./conversation-messages-list.vue').default);
Vue.component('conversation-message', require('./conversation-message.vue').default);
Vue.component('offer-quote', require('./offer-quote.vue').default);
Vue.component('new-messages-circle', require('appJs/new-messages-circle.vue').default);
Vue.component('edit-message', require('./edit-message.vue').default);

// Список прикрепленных файлов
Vue.component('file-list', require('appJs/file-list.vue').default);

// Форма отправки сообщения
require('appJs/message-send-form.js');

// Мобильная форма отправки сообщения
require('appJs/mobile-message-form.js');

// Отправка сообщения в мобильной версии переписки
require('./mobile-message-send.js');

// Черновик трека
import Draft from 'appJs/draft.js';

// Просмотрщик изображений
Vue.component('image-viewer', require('appJs/image-viewer.vue').default);

if (window.isChat) {
	// функции разных тип замен эмоджи #7584
	require('appJs/emoji/emoji-replacements.js');

	// Компоненты чата
	Vue.component('chat-list', require('moduleJs/chat/chat-list.vue').default);
	Vue.component('chat-conversation-header', require('moduleJs/chat/chat-conversation-header.vue').default);
	Vue.component('chat-messages-list', require('moduleJs/chat/chat-messages-list.vue').default);
	Vue.component('chat-private-message-status', require('moduleJs/chat/chat-private-message-status.vue').default);

	window.appChatList = new Vue({
		el: '#app-chat-list'
	});
}

window.app = new Vue({
	el: '#app'
});
window.conversationApp = window.app;
if (window.isChat) {
	// Переписка в чате находится в компоненте chatMessagesList
	window.conversationApp = window.app.$refs.chatMessagesList;
	// Файлы диалога в чате находятся в компоненте chatConversationHeader
	window.appSidebar = window.app.$refs.chatConversationHeader;
} else {
	window.appSidebar = new Vue({
		el: '#app-sidebar',
	});

	window.appMobile = new Vue({
		el: '#app-mobile',
	});
}

jQuery(document).ready(() => {
	if ($('#message_form_wrapper').length) {
		window.offerForm = new ValidatableForm('#message_form_wrapper', {
			onUpdate: window.OfferIndividualModule.validateIndividualKwork,
		});
	}
	
	// Инициализация черновика
	// Для чата инициализируем при открытии диалога
	window.draft = new Draft({
		mode: 'inbox',
		messageFields: ['#message_body', '#mobile_message'],
		fileUploaders: window.fileUploaders,
	});
});
