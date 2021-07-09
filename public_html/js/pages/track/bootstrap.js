import TrackManager from "./TrackManager";
import TrackHistory from "./track_history";
import UserStatus from "../../app/components/UserStatus";

require('appJs/bootstrap.js');

// Инфо заказа
require('appJs/order-info.js');

// Форма загрузки портфолио
require('appJs/portfolio-upload/bootstrap.js');

// Оптимистичный интерфейс для обычных сообщений в заказе
require('./optimistic-ui');

// Цитирование сообщения
require('appJs/quote-message.js');

// Список треков
Vue.component('track-list', require('./track-list.vue').default);

// Список прикрепленных файлов
Vue.component('file-list', require('appJs/file-list.vue').default);

// вставка эможи в текстовый редактор
require('appJs/trumbowyg-caret.js');

// Класс для инициализации плагина trumbowyg
import EmojiTrumbowyg from 'appJs/emoji/emoji-trumbowyg.js';

// Класс для инициализации плагина trumbowyg
require('appJs/trumbowyg-caret.js');

// Черновик трека
import Draft from 'appJs/draft.js';

// Загрузчик файлов
Vue.component('file-uploader', require('appJs/file-uploader.vue').default);

import LoadSendInstructionModal from "./LoadSendInstructionModal";

import Toggler from '../../app/components/toggler';

// Панель с emoji(подключается отдельно для возможности рендерить emoji-btn через jquery
Vue.component('emoji-panel', require('appJs/emoji/emoji-panel.vue').default);

// Кнопка которая показывае панель с эможи
Vue.component('emoji-btn', require('appJs/emoji/emoji-btn.vue').default);

// Получем объек загрузчика фалов, чтобы использовать в файле edit-message-track.js
import fileUploaderComponent from '../../app/file-uploader.vue';

window.fileUploaderComponent = fileUploaderComponent;

// Загрузчик файлов
Vue.component('file-uploader', fileUploaderComponent);

// Форма отправки сообщения
require('appJs/message-send-form.js');

// Работа с текстом
require('appJs/text.js');

// Просмотрщик изображений
Vue.component('image-viewer', require('appJs/image-viewer.vue').default);

// Форма отправки сообщения трека
require('./message-form.js');

// Мобильная форма отправки сообщения
require('appJs/mobile-message-form.js');

// Модальное окно
import { BModal } from "bootstrap-vue";
Vue.component('b-modal', BModal);

// Попап при нажатии "Заказать еще"
require('moduleJs/orders/order-more-modal.js');

// Кружок-уведомление о новых сообщениях
Vue.component('new-messages-circle', require('appJs/new-messages-circle.vue').default);

window.initFormComponents = function() {
	let trackMaxFilesCount = parseInt(config.track.fileMaxCount || config.files.maxCount);
	let trackMaxFilesSize = parseInt(config.track.fileMaxSize || config.files.maxSize);

	if ($('#app-files').length) {
		if (!window.appFiles) {
			// Блок с загрузчиком файлов в нижней форме
			window.appFiles = new Vue({
				el: '#app-files',

				data: {
					files: [],
					ready: true,
					trackMaxFilesCount: trackMaxFilesCount,
					trackMaxFilesSize: trackMaxFilesSize,
					secondUserId: parseInt(window.Track.opponentId),
					dragNDropBlocked: false,
				},

				computed: {
					desktopDragNDropEnable() {
						return !this.dragNDropBlocked;
					}
				},

				methods: {
					setDragNDropBlocked(status) {
						this.dragNDropBlocked = status;
					},

					onChange(state) {
						this.ready = state;
						if (window.draft) {
							window.draft.activateByInput({
								uploader: this,
							});
						}
						if (window.sendForm) {
							window.sendForm.updateUploadButton(window.appFiles.$refs.fileUploader.isUploadAviable);
						}
						if (window.toggleSubmitButton) {
							window.toggleSubmitButton();
						}
					},
				},
			});
		}
	}

	if ($('#app-files-mobile').length > 0) {
		if (!window.appFilesMobile) {
			window.appFilesMobile = new Vue({
				el: '#app-files-mobile',
				data: {
					files: [],
					ready: true,
					trackMaxFilesCount: trackMaxFilesCount,
					secondUserId: parseInt(window.Track.opponentId),
					desktopUploader: (window.appFiles ? window.appFiles.$refs.fileUploader : null),
				},
				methods: {
					onChange(state) {
						this.ready = state;
						if (window.draft) {
							window.draft.activateByInput({
								uploader: this,
							});
						}
						window.toggleSubmitButton();
					},
				},
			});
		}
	}

	// TODO: Проверка на isFocusGroupMember на время тестирования. После - удалить содержимое проверки
	if (config.track.isFocusGroupMember) {
		if ($('#app-emoji-btn').length > 0) {
			if (!window.appEmojiBtn) {
				window.appEmojiBtn = new Vue({
					el: '#app-emoji-btn',
					methods: {
						onChange(code) {
							// Событие которое передает unicod выбранной эможи
							$(this.$el).trigger( "emoji-panel.click", [ code ] );
						}
					}
				});
			}
		}
	}
}

$('document').ready(() => {
	// Основное приложение
	window.app = new Vue({
		el: '#app',
	});

	// Список треков
	window.appTracks = new Vue({
		el: '#app-tracks',
	});

	window.initFormComponents();

	if ($('#send-instruction-modal').length) {
		(new LoadSendInstructionModal()).init();
	}

	window.newMessagesCircle = new Vue({
		el: '#new-messages-circle',
	});
	
	// Инициализация черновика
	window.draft = new Draft({
		mode: 'track',
		messageFields: ['#message_body1'],
		fileUploaders: [window.appFiles],
	});

	if (config && config.track && config.track.isFocusGroupMember) {
		if (document.querySelector('.track--compact')) {
			let trackManager = new TrackManager(document.querySelector('.track--compact'));
			trackManager.init();
		}
	}
	$('.track--info__user .fadeble').each((key,item)=>{
		if(item.scrollWidth > item.offsetWidth){
			item.classList.add('oneline-faded');
		}
	});
	//Запускает Класс который нужен для Истории заказа в сайдбаре
	function initHistory() {
		$.each($('.track--progress'), (k, instance )=> {
			(new TrackHistory(instance).init());
		});
	}

	//Инициализирует Toggler классы для всех блоков с классом toggler
	function togglerInit() {
		$.each($('.toggler'), (k, instance )=> {
			(new Toggler(instance)).init();
		});
	}

	togglerInit();
	initHistory();

	//При изменений блока файлов с сайдабра, надо заново инициализировать тогглеры
	document.addEventListener('sidebar-files-rerendered', e => {
		togglerInit();
	});
	document.addEventListener('sidebar-order-state', e => {
		let html = e.detail;
		let item = $(html);
		let state = $('.track--info').html(item);
		$('.track--info .tooltipster').tooltipster(TOOLTIP_CONFIG);
	});

	$(document).on('mouseleave', '.progress-line--item__content.tooltipster', function() { $(this).tooltipster('close') });

	//При добавлении вопроса с арбитражем, инициализируется Toggler
	document.addEventListener('sidebar-arbitrage-loaded', e => {

		if(!document.querySelector('#track--questions__item-arbitrage')){
			$('#track--questions__item-send-somewhere').before(e.detail);
			togglerInit();
		}
	});

	//При загрузке новых данных для сайдабара, надо заново добавлять Toggler-ы и TrackHistory, т.к. это уже новый DOM
	document.addEventListener('new-tracks-loaded', evt => {
		let data = evt.detail;
		let trackHistoryHtml = data.other['track-history'];
		$('.track--progress').replaceWith(trackHistoryHtml); 
		initHistory();
		togglerInit();
	});

	//Инициализируем UserStatus
	document.querySelectorAll('.js-user-online-block').forEach((item)=>{
		(new UserStatus(item)).init();
	});
	
});

	
// Инициализируем Trumbowyg для эможи
window.initEmojiTrumbowyg = function($this) {
	new EmojiTrumbowyg($this);
}
