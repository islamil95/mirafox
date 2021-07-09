<template>
	<div class="conversation-messages-list">
		<new-messages-circle class="desktop"></new-messages-circle>
		<div v-if="complainModalShown">
			<b-modal :modal-class="'cm-complain-modal'" v-model="complainModalShown" @hide="onHideModal" :title="t('Отправить жалобу')" size="md" no-fade>
				<div class="modal-message">
					<div class="cm-tip" v-html="complainTip"></div>
					<div class="cm-title">{{ t('На пользователя') }}</div>
					<input v-model="messageToComplain.mfrom" type="text" class="styled-input" readonly />
					<div class="cm-title">{{ t('На сообщение') }}</div>
					<textarea v-html="messageToComplain.message" class="styled-input" readonly></textarea>
					<div class="cm-title">{{ t('Ваш комментарий') }}</div>
					<textarea v-model="complainComment" class="styled-input" :placeholder="t('Комментарий к жалобе')"></textarea>
				</div>
				<div slot="modal-footer">
					<button class="button button-success cm-save-button" @click="complainSubmit">{{ t('Отправить') }}</button>
				</div>
			</b-modal>
		</div>
		<div v-if="deleteModalShown">
			<b-modal
					:static="true" class="delete-modal b-modal-dark-backdrop vue-b-modal" v-model="deleteModalShown" @hide="onHideModal" :title="t('Подтверждение удаления')" size="md" centered>
				<div class="modal-message">{{ t('Вы действительно хотите удалить сообщение?') }}</div>
				<div slot="modal-footer">
					<button class="green-btn" @click="deleteModalShown = false">{{ t("Не удалять") }}</button>
					<button class="white-btn" @click="deleteSubmit">{{ t("Удалить") }}</button>
				</div>
			</b-modal>
		</div>
		<div v-if="!userBlocked && !isChat" class="controls">
			<a class="white-btn" @click="toArchive" v-if="(!isNew && !isArchive)">{{ t('Поместить в архив') }}</a>
		</div>
		<div v-if="!userBlocked && moreMessagesShow" class="t-align-c mb10 conversation-messages-list__show-more-messages">
			<span class="conversation-messages-list__show-more-messages__text">{{ showMoreMessagesText }}</span>
			<button @click="showMoreMessages" class="btn-link" type="button">{{ showMoreMessagesTextBtn }}</button>
			<div v-if="moreMessagesLoaderShow" class="conversation-messages-list__show-more-messages__wrap-ispinner">
				<div class="ispinner ispinner--gray ispinner--animating ispinner--large">
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
					<div class="ispinner__blade"></div>
				</div>
			</div>
		</div>
		<div v-if="!messages.length && !emptyInfoBlock && !userBlocked" v-html="getInfoBlockHtml(true)"></div>
		<div class="messages-list">
			<div v-for="(v, k) in messagesChunks" :key="k" class="conversation-messages-day">
				<div v-if="v[0].hasInfoBlockSingle && v[0].infoBlockPosition === 'top'" v-html="getInfoBlockHtml(true)"></div>
				<div class="conversation-date_separator t-align-c">
					<div class="dib mb-8 pl10 pr10 bodybg f12 color-gray" style="background-color:transparent;">{{ isChat ? chatDates[k] : k }}</div>
					<hr class="gray m0">
				</div>
				<conversation-message :user-csrf="userCsrf" :timerSeconds="timerSeconds" :isOnline="isOnline" :ref="'message' + v2.MID" v-for="(v2, k2) in v" :key="k2" :message="v2" :getInfoBlockHtml="getInfoBlockHtml" :conversationMessageProps="conversationMessageProps" :unreadCount="unreadCount" @complain="messageComplain(v2)" @delete="messageDelete(v2)" @quote="messageQuote(v2)" @rate="messageRate(v2, $event)" @cancel="messageCancel(v2)" @accept="messageAccept(v2)" @refuse="messageRefuse(v2)" @mark_take_away="messageMarkTakeAway(v2)" @unremovable="closeDeleteModal" @scrollToMessage="scrollToMessage"></conversation-message>
				<div v-if="v[0].hasInfoBlockSingle && v[0].infoBlockPosition === 'bottom'" :class="{ 'mb-10': hasOrdersBetweenUsers }" v-html="getInfoBlockHtml(true)"></div>
			</div>
		</div>
		<div v-if="isChat && !userBlocked">
			<!-- Доступность переписки между пользователями и Блок c активными заказами между пользователями -->
			<chat-private-message-status
					ref="chatPrivateMessageStatus"
					:chatPrivateMessageStatusProps="chatPrivateMessageStatusProps">
			</chat-private-message-status>
		</div>
	</div>
</template>
<style>
	.delete-modal.b-modal-dark-backdrop.vue-b-modal .modal-backdrop{
		opacity: .75;
	}
</style>
<script>
	import i18nMixin from 'appJs/i18n-mixin';  // Локализация
	import timerMixin from 'appJs/timer-mixin.js';  // Таймер
	import dateMixin from 'appJs/date-mixin.js';  // Форматирование даты
	import sendingMixin from 'appJs/sending-mixin.js';  // Счётчик отправки запросов
	import cdnMixin from 'appJs/cdn-mixin.js';  // Приведение относительных ссылок к абсолютным (CDN)

	// Модальные диалоги
	import { BModal, VBModal } from "bootstrap-vue";
	Vue.component("b-modal", BModal);
	Vue.directive("b-modal", VBModal);

	export default {
		mixins: [i18nMixin, dateMixin, timerMixin, sendingMixin, cdnMixin],
		props: {
			'is_archive': {},
			chatProps: {
				type: Object,
				required: false,
			},
			userCsrf: "",
		},
		data() {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'Отправить жалобу': 'Send a complaint',
						'Вы можете пожаловаться на сообщение пользователя в случаях, если это несанкционированная реклама (СПАМ), сообщение содержит оскорбительные&nbsp;/&nbsp;грубые выражения, нарушает законодательство или правила сайта': 'A complaint about a user message can only be sent when it contains unauthorized advertising (SPAM), inappropriate language, violation of any existing law, or Kwork website rules',
						'На пользователя': 'About a user',
						'На сообщение': 'About a message',
						'Ваш комментарий': 'Your comment:',
						'Комментарий к жалобе': 'Complaint comments',
						'Отправить': 'Send',
						'Редактирование': 'Editing',
						'Сохранить': 'Save',
						'Отменить': 'Cancel',
						'Подтверждение удаления': 'Delete confirmation',
						'Удалить сообщение?': 'Delete this message?',
						'Удалить': 'Delete',
						'Отменить': 'Cancel',
						'Поместить в архив': 'Move to archive',
						'Ваше сообщение принято, модератор рассмотрит его в ближайшее время.': 'Your message has been accepted and will be viewed by a moderator shortly.',
						'Не все сообщения отправлены. Покинуть страницу?': 'Not all messages have been sent. Leave the page?',
						'Загрузить еще {{0}}': 'Download more {{0}}',
						'Загрузить все': 'Download all',
						'последнее': 'last',
						'сообщение': 'messages',
						'сообщения': 'messages',
						'сообщений': 'messages',
						'Показаны {{0}} {{1}} {{2}}.': 'Showing {{0}} {{1}} {{2}}.',
					}
				},

				actorId: 0,
				conversationUserId: null,
				messages: [],
				freeLocalMessageId: 1,

				complainModalShown: false,
				messageToComplain: {},
				complainComment: '',
				isNew: true,
				messageToEdit: {},

				deleteModalShown: false,
				messageToDelete: {},

				actorIsVirtual: false,
				messageTypes: {},
				isOnline: {},
				origTitle: '',
				messagesToRead: [],
				attentionTimer: null,
				kbClosed: false,
				lastWatchedMessage: null,
				handledMessagesCount: 0,
				isArchive:this.is_archive,
				countMoreMessages: 0,
				
				moreMessagesLoaderShow: false,
				eventsInterval: null,

				// Для чата
				isChat: false,
				isLoaderShow: false,
				isChatSelected: false,
				chatDates: {},
				conversationMessageProps: {},
				chatPrivateMessageStatusProps: {},
			};
		},

		computed: {
			complainTip: function() {
				return this.t('Вы можете пожаловаться на сообщение пользователя в случаях, если это несанкционированная реклама (СПАМ), сообщение содержит оскорбительные&nbsp;/&nbsp;грубые выражения, нарушает законодательство или правила сайта');
			},
			unreadCount: function() {
				let messageCount = 0;
				_.forEach(this.messages, (v, k) => {
					if (v.unread == 1 && v.MSGTO == this.actorId) {
						messageCount++;
					}
				});
				return messageCount;
			},

			messagesChunks: function() {
				// Добавляем в this.messages необходимые данные для вывода инфоблока о заказе
				if (this.messages.length) {
					this.addInfoBlock();
				}

				// Получаем индекс первого непрочитанного сообщения
				let firstUnreadMessage = _.findIndex(this.messages, {'unread': '1', 'MSGTO': this.actorId});

				let chunks = {};
				_.forEach(this.messages, (v, k) => {
					let date = this.getDate(v.time);
					let dateChat = this.getDateWithoutYear(v.time);
					if (!(date in chunks)) {
						chunks[date] = [];
						this.chatDates[date] = [];
					}
					chunks[date].push(v);
					this.chatDates[date] = dateChat;

					// Добавляем сообщению флаг непрочитанности
					v.isFirstUnread = k === firstUnreadMessage;
				});
				return chunks;
			},
			
			moreMessagesShow: function () {
				return (this.countMoreMessages > 0) ? this.countMoreMessages : false;
				
			},
			
			showMoreMessagesTextBtn: function () {				
				return (this.countMoreMessages > 50) ? this.t("Загрузить еще {{0}}", ["50"]) : this.t("Загрузить все"); 
				
			},
			
			showMoreMessagesText: function () {			
				let pluralWord1 = declension(this.messages.length, "последнее", "последние", "последние");
				let pluralWord2 = declension(this.messages.length, "сообщение", "сообщения", "сообщений");
				return this.t("Показаны {{0}} {{1}} {{2}}.", [this.t(pluralWord1), this.messages.length, this.t(pluralWord2)]);				
			},
		},

		watch: {
			messages() {
				if (this.handledMessagesCount == this.messages.length) {
					return;
				}
				this.handledMessagesCount = this.messages.length;
				if (this.messages.length) {
					this.isNew = false;
				}
				this.$nextTick(() => {
					this.setMessagesToRead();
					this.scrollToUnread(false, true, true);
				});
			},
		},

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();

			this.actorIsVirtual = window.actorIsVirtual || false;
			this.isOnline = window.isOnline || {};
			this.conversationUserId = window.conversationUserId;
			this.messages = window.conversationMessages || [];
			// Расчитываем сколько сообщений можно показать
			this.countMoreMessages = window.countMessages - this.messages.length;
			this.actorId = window.actorId || 0;
			this.userBlocked = window.userBlocked || false;
			this.messageTypes = window.conversationMessageTypes || {};
			this.kbClosed = window.kbClosed || false;
			this.infoBlock = window.lastDoneOrderWithMessages || {};
			this.emptyInfoBlock = _.isEmpty(window.lastDoneOrderWithMessages);
			this.hasOrdersBetweenUsers = !_.isEmpty(window.ordersBetweenUsers);

			// Для чата
			this.isChat = window.isChat || false;
			this.conversationMessageProps = {
				conversationUserId: window.conversationUserId || null,
				isOrderStageTester: offer.isOrderStageTester || false,
				userAvatarColors: window.userAvatarColors || {},
			};
			if (this.isChat) {
				_.assignIn(this, this.chatProps);
			}

			if (!this.kbClosed) {
				_.forEachRight(this.messages, (v, k) => {
					if (v.kb_article_id && v.article) {
						v.kbOpened = true;
						return false;
					}
				});
			}

			window.bus.$on('bgSendMessages', (result) => {
				this.applyServerData(result);
			});

			window.bus.$on('scrollToUnread', () => {
				this.scrollToUnread(false, false, false, true);
			});
			
			// Событие, чтобы дочерние компоненты могли менять сообщение
			window.bus.$on('updateMessage', (message) => {
				this.updateMessage(message);
			});

			// Событие скрола переписки
			window.bus.$on('chatScrollToFirstUnread', () => {
				this.chatScrollToFirstUnread();
			});

			$(document).ready(() => {
				PullModule.on(PULL_EVENT_REMOVE_DRAFT, (data) => {
					if (!data || !data.user_id || data.user_id != window.conversationUserId) {
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
			});

			$(window).load(() => {
				if (!this.isChatSelected && this.isChat) {
					return false;
				}

				PullModule.addOnlineUserChannel(this.conversationUserId);
				PullModule.on(PULL_EVENT_IS_ONLINE, (data) => {
					let status = (data.status == 'online' ? 1 : 0);
					this.$set(this.isOnline, data.userId, status);
				});

				// Прокручиваем скролл к первому непрочитанному сообщению
				let finded = this.scrollToUnread(false, false);
				// Если непрочитанных нет - то просто в конец
				if (!finded) {
					let scrollTopTo = (!isMobile() ? $("#bottomFormLink").offset().top - $(window).height() + 400 : $(document).height());
					$('html, body').animate({
						scrollTop: scrollTopTo,
					}, 200);
				}
			});

			$(window).on('focus', () => {
				$('body').removeClass('js-tab-blur');
			});

			$(window).on('blur', () => {
				$('body').addClass('js-tab-blur');
			});

			$(window).load(() => {
				if (window.QuoteMessage) {
					window.QuoteMessage.init(window.QuoteMessage.TYPE_PAGE_CONVERSATION);
				}
			});
		},

		mounted: function() {
			this.origTitle = document.title;

			$('.conversations_top').removeClass('hidden');

			$(document).ready(() => {
				PullModule.on(PULL_EVENT_NEW_INBOX, this.onNewInbox);
				PullModule.on(PULL_EVENT_INBOX_SENT, this.onInboxSent);
				PullModule.on(PULL_EVENT_INBOX_MESSAGE_EDIT, this.onEditMessage);
				PullModule.on(PULL_EVENT_READ_INBOX, this.onReadInterlocutorInbox);
				PullModule.on(PULL_EVENT_INBOX_MESSAGE_DELETE, this.onDeleteMessage);

				this.setEvents();
			});
		},

		/**
		 * Отключает прослушку событий перед уничтожением компонента
		 */
		beforeDestroy() {
			window.bus.$off('bgSendMessages');
			window.bus.$off('scrollToUnread');
			window.bus.$off('updateMessage');
			window.bus.$off('chatScrollToFirstUnread');

			$(document).off('click', () => {
				if (this.attentionTimer){
					this.stopAttention();
				}
			});

			$(window).off('scroll mousemove focus', this.scrollMousemoveFocusEvent);

			$('.js-alt-send').off('focus click', () => {
				this.setReadInMessages();
				this.stopAttention();
			});

			clearInterval(this.eventsInterval);
		},

		methods: {
			updateNewMessagesCount() {
				let messageCount = 0;
				_.forEachRight(this.messages, (v, k) => {
					if (this.lastWatchedMessage && v.MID == this.lastWatchedMessage) {
						return false;
					}
					if (v.unread == 1 && v.MSGTO == this.actorId) {
						messageCount++;
					}
				});
				if (!messageCount) {
					_.last(this.messages)
				}
				window.bus.$emit('updateMessagesCount', messageCount);
			},

			isMessageOnScreen(mid) {
				let tabBlurCheck = $('body').hasClass('js-tab-blur');
				if (tabBlurCheck) {
					return 0;
				}
				let el = this.$refs['message' + mid][0];
				if (!el) {
					return 0;
				}
				el = $(el.$el);
				let headerEl = el.find('.header-c');
				let headerCheck = (headerEl.withinviewportbottom && headerEl.withinviewportbottom({bottom: 30}).length > 0);
				if (!headerCheck) {
					return 0;
				}
				let footerEl = el.find('.cm-message-status');
				let footerCheck = (footerEl.withinviewporttop && footerEl.withinviewporttop({top: 85}).length > 0);
				if (!footerCheck) {
					return 1;
				}
				return 2;
			},

			isUnreadMessage(v) {
				if (v.unread == 1 && v.MSGTO == this.actorId) {
					return true;
				}
				return false;
			},

			scrollToMessage(mid, onlyDown = false) {
				if (this.isChat) {
					let isMobile = jQuery(window).width() < 768;
					let scrollToEl = jQuery(this.$refs['message' + mid][0].$el);
					let scrollingEl, scrollingPosition;

					if (isMobile) {
						scrollingEl = jQuery(window);
						scrollingPosition = scrollToEl.offset().top - (jQuery(window).height() - scrollToEl.outerHeight(true)) / 2;
					} else {
						scrollingEl = jQuery('.kwork-conversation__list .scrolly-viewport');
						scrollingPosition = scrollToEl.position().top - (jQuery('.kwork-conversation__list').outerHeight(true) - scrollToEl.outerHeight(true)) / 2;
					}

					if (onlyDown) {
						if (scrollingEl.scrollTop() > scrollingPosition) {
							return;
						}
					}
					if (isMobile) {
						scrollingEl = jQuery('html, body');
					}
					scrollingEl.animate({scrollTop: scrollingPosition}, 0);
				} else {
					let scrollTop = getElementTopToScroll($(this.$refs['message' + mid][0].$el));
					if (onlyDown) {
						let currentScroll = $(window).scrollTop();
						if (currentScroll > scrollTop) {
							return;
						}
					}
					$('html, body').animate({
						scrollTop: scrollTop,
					}, 200);
				}
			},

			messageUnreadCheck(v, ifVisible = false) {
				return (this.isUnreadMessage(v) && (!ifVisible || this.isMessageOnScreen(v.MID) >= 2));
			},

			scrollToUnread(toLast = false, ifVisible = false, onlyDown = false, skipWatched = false) {
				let unreadMessage = null;
				let messageFound = false;
				if (!toLast) {
					_.forEach(this.messages, (v, k) => {
						if (skipWatched && !messageFound && this.lastWatchedMessage) {
							if (v.MID == this.lastWatchedMessage) {
								messageFound = true;
							}
							return true;
						}
						if (this.messageUnreadCheck(v, ifVisible)) {
							unreadMessage = v;
							return false;
						}
					});
				} else {
					_.forEachRight(this.messages, (v, k) => {
						if (this.messageUnreadCheck(v, ifVisible)) {
							unreadMessage = v;
							return false;
						}
					});
				}
				if (unreadMessage) {
					this.scrollToMessage(unreadMessage.MID, onlyDown);
					return true;
				} else {
					return false;
				}
			},

			scrollMousemoveFocusEvent: function() {
				if (this.unreadCount > 0) {
					Utils.throttle(this.stopAttention, 500);
				}
				if (this.attentionTimer){
					this.stopAttention();
				}
				this.throttleSetMessagesToRead();
			},

			setEvents: function() {
				this.throttleSetMessagesToRead = Utils.throttle(this.setMessagesToRead, 100, false, true);

				// Таймер для откладывания загрузки своих сообщений
				this.defferedSelfMessagesTimer = null;

				$(document).on('click', () => {
					if (this.attentionTimer){
						this.stopAttention();
					}
				});

				$(window).on('scroll mousemove focus', this.scrollMousemoveFocusEvent);

				$('.js-alt-send').on('focus click', () => {
					this.setReadInMessages();
					this.stopAttention();
				});

				this.eventsInterval = setInterval(() => {
					if (!this.messagesToRead.length) {
						return;
					}

					if (this.attentionTimer){
						this.stopAttention();
					}

					_.forEach(this.messagesToRead, (v, k) => {
						let finded = false;
						_.forEachRight(this.messages, (v2, k2) => {
							if (v2.MID == v) {
								finded = true;
							}
							if (finded && this.actorId == v2.MSGTO) {
								this.$set(v2, 'unread', 0);
							}
						});
					});
					this.sendReadMessages();
					this.updateTitleUnreadCount();
				}, 300);

				this.throttleSetMessagesToRead();
			},

			markAsSended: function(localId) {
				_.forEach(this.messages, (v, k) => {
					if (v.localId == localId) {
						v.MID = -1;
					}
				});
			},

			setMessagesToRead: function() {
				let messageIds = [];
				let lastWatchedMessage = null;
				_.forEach(this.messages, (v, k) => {
					if (v.unread == 1 && v.MID && v.MSGTO == this.actorId) {
						let msgOnScreen = this.isMessageOnScreen(v.MID);
						if (msgOnScreen > 0) {
							lastWatchedMessage = v.MID;
						}
						if (msgOnScreen >= 2) {
							lastWatchedMessage = v.MID;
							if (!this.actorIsVirtual) {
								if (v.readTimeout) {
									return;
								}
								v.readTimeout = setTimeout(() => {
									this.messagesToRead.push(v.MID);
								}, 1500);
							}
						} else {
							if (v.readTimeout) {
								clearTimeout(v.readTimeout);
							}
							v.readTimeout = null;
						}
					}
				});
				this.lastWatchedMessage = lastWatchedMessage;
				this.updateNewMessagesCount();
			},

			/**
			 * Отметить прочитанными все сообщения собеседника у которых идентификатор меньше поданного
			 * @param messageFrom Идентификатор сообщения выше которого нужно отмечать
			 */
			readAllNotOwnMessagesBefore: function(messageFrom) {
				let found = false;
				_.forEachRight(this.messages, (v, k) => {
					if (v.localId == messageFrom) {
						found = true;
					}
					if (v.MSGFROM != this.actorId && found) {
						v.unread = 0;
					}
				});
			},

			onNewInbox(response) {
				if (this.conversationUserId != response.from) {
					return;
				}
				var params = {
					interlocutorId: this.conversationUserId,
					lastMessageId: this.getLastExistedMessageMID(),
				};
				Api.request('inbox/getnewmessages', params, (response) => {
					this.applyServerData(response.messagesData);
					this.updateTitleUnreadCount();
					this.startAttention();
					$(pullIsTypeMessage.selectorInfoTypeMessage).hide();
				});
			},

			/**
			 * Обработка события inbox_sent
			 * (когда текущий пользователь отправил собеседнику сообщение)
			 */
			onInboxSent: function(response) {
				if (!response.inbox_id) {
					return;
				}
				// Если сообщение было отправлено не тому пользователю, с которым диалог - ничего не делаем
				if (response.to_user_id != this.conversationUserId) {
					return;
				}

				// Загружаем свои сообщния, с задержкой
				this.loadNewCurrentUserMessages(response.inbox_id);
			},

			/**
			 * Загрузка новых своих сообщений, с задержкой если необходимо
			 * @param MID Идентификатор сообщения которое было отправлено текущим пользователем
			 */
			loadNewCurrentUserMessages: function(MID) {
				// Если сообщение уже на странице то не делаем запрос
				if (this.isMessageOnPageByMID(MID)) {
					return;
				}
				if (this.sendingCount) {
					// Если в данный момент идет отправка сообщений - отложим загрузку
					clearTimeout(this.defferedSelfMessagesTimer);
					let that = this;
					this.defferedSelfMessagesTimer = setTimeout(function () {
						that.loadNewCurrentUserMessages(MID);
					}, 1000);
				} else {
					// Загружаем сообщения от последнего имеющего MID
					var params = {
						interlocutorId: this.conversationUserId,
						lastMessageId: this.getLastExistedMessageMID(),
					};
					Api.request('inbox/getnewmessages', params, (response) => {
						this.applyServerData(response.messagesData);
					});
				}
			},

			/**
			 * Получение последнего существующего MID на странице
			 */
			getLastExistedMessageMID(own = false) {
				let MID = null;
				_.forEachRight(this.messages, (v, k) => {
					if (v.MID && (!own || v.MSGFROM == this.actorId)) {
						MID = v.MID;
						return false;
					}
				});
				return MID;
			},

			onReadInterlocutorInbox: function(response) {
				var messageFrom = response.messageId;
				if (response.messages) {
					messageFrom = Math.max.apply(null, response.messages);
				}
				this.setReadInMessages(messageFrom, response.to_user_id);
			},

			/**
			 * Пристутствует ли сообщение на странице (по MID)
			 */
			isMessageOnPageByMID: function(MID) {
				let found = false;
				_.forEachRight(this.messages, (v, k) => {
					if (v.MID == MID) {
						found = true;
						return false;
					}
				});
				return found;
			},

			/**
			 * Обработка события push edit_message (собеседник или текущий пользователь отредактировал сообщение)
			 */
			onEditMessage: function(response) {
				// Если сообщения нет на странице никакие запросы не шлем
				if (!this.isMessageOnPageByMID(response.mid)) {
					return;
				}
				Api.request('inbox/getmessage', {messageId: response.mid}, (message) => {
					this.updateMessage(message.data);
				});
			},

			onDeleteMessage: function(response) {
				this.deleteMessage(response.mid, response.need_replace);
				this.updateTitleUnreadCount();
			},

			startAttention: function() {
				if (this.attentionTimer) {
					return;
				}

				let i = 0;
				this.attentionTimer = setInterval(() => {
					if (i % 2) {
						this.updateTitleUnreadCount();
					} else {
						document.title = '*** ' + this.origTitle;
					}
					i++;
				}, 1000);
			},

			stopAttention: function() {
				if (this.attentionTimer) {
					clearInterval(this.attentionTimer);
				}
				this.attentionTimer = null;
				this.updateTitleUnreadCount();
			},

			updateTitleUnreadCount: function() {
				let newTitle = this.origTitle;
				if (this.unreadCount > 0) {
					newTitle = '[' + this.unreadCount + '] ' + newTitle;
				}
				document.title = newTitle;
			},

			sendReadMessages: function() {
				var params = {
					interlocutorId: this.conversationUserId,
					messages: this.messagesToRead,
				};
				Api.request('inbox/readmessages', params, function () {});

				// Отмечаем прочитанным диалог в списке
				if (this.isChat) {
					window.bus.$emit('setChatRead', this.conversationUserId, this.messagesToRead.length);
				}

				this.messagesToRead = [];
			},

			setReadInMessages: function(messageFrom, to_user_id = null) {
				if (!messageFrom) {
					return;
				}
				_.forEachRight(this.messages, (v, k) => {
					if (
						(v.MSGFROM == this.actorId && v.MID <= messageFrom && !to_user_id)
						|| (v.MSGFROM == this.actorId && v.MID <= messageFrom && to_user_id && to_user_id == v.MSGTO)
					) {
						v.unread = 0;
					}
				});
			},

			applyServerData: function(messages) {
				_.forEach(messages, (v, k) => {
					let added = false;
					_.forEach(this.messages, (v2, k2) => {
						if ((v.localId && v2.localId == v.localId) || (v.MID && v.MID == v2.MID)) {
							added = true;
							this.$set(this.messages, k2, v);
							return false;
						}
					});
					if (added) {
						return true;
					}

					this.messages.push(v);
					if (v.filesArray) {
						window.appSidebar.$refs.conversationFiles.addFiles(v.filesArray);
					}
				});
				if (window.draft) {
					window.draft.updateLastMessageId();
				}
			},

			addMessage: function(data) {
				let type = null;
				let mid = null;
				if (data.isOffer) {
					type = this.messageTypes.offerKworkNew;
				} else if (data.type) {
					type = data.type;
					mid = -1;
				}
				let hidden = data.hidden || false;
				let localId = this.freeLocalMessageId;
				this.isArchive = 0;
				this.messages.push({
					MID: mid,
					localId: localId,
					profilepicture: window.actorAvatar,
					quote: data.quote,
					quote_id: data.quote ? data.quote.id : null,
					MSGFROM: this.actorId,
					MSGTO: this.conversationUserId,
					mfrom: window.actorLogin,
					time: _.now() / 1000,
					// Сколько секунд назад было отправлено сообщение
					time_since_create: 0,
					rawMessage: _.escape(data.message || ''),
					filesArray: data.files || [],
					unread: 1,
					type: type,
					hidden: hidden,
					additional: data.additional || {},
				});
				this.freeLocalMessageId++;

				// Скролл в конец переписки при добавлении нового сообщения в чате
				if (this.isChat && !data.isOffer) {
					Vue.nextTick(() => {
						chatModule.scrollToEnd();
					});
				}

				return localId;
			},

			updateMessage(message) {
				_.forEach(this.messages, (v, k) => {
					if (v.MID == message.MID) {
						if (v.filesArray) {
							window.appSidebar.$refs.conversationFiles.removeFiles(v.filesArray);
						}
						if (message.filesArray) {
							window.appSidebar.$refs.conversationFiles.addFiles(message.filesArray);
						}
						this.$set(this.messages, k, message);
						return false;
					}
				});
			},

			deleteLocalMessage: function(localId) {
				_.forEach(this.messages, (v, k) => {
					if (v.localId == localId) {
						if(v.filesArray) {
							window.appSidebar.$refs.conversationFiles.removeFiles(v.filesArray);
						}
						this.messages.splice(k, 1);
						return false;
					}
				});
			},

			deleteMessage: function(id, needReplace = false) {
				_.forEach(this.messages, (v, k) => {
					if (v.MID == id) {
						if(v.filesArray) {
							window.appSidebar.$refs.conversationFiles.removeFiles(v.filesArray);
						}
						if (needReplace) {
							this.$set(v, 'status', 'deleted');
							this.$set(v, 'sended', 1);
						} else {
							this.messages.splice(k, 1);
						}
						return false;
					}
				});
			},

			messageComplain: function(message) {
				this.messageToComplain = message;
				this.complainComment = '';
				this.complainModalShown = true;
			},

			messageDelete: function(message) {
				this.messageToDelete = message;
				this.deleteModalShown = true;
			},

			messageQuote: function(message) {
				let messageText = message.message;
				if (messageText === '') {
					$.each(message.files, (k, v) =>  {
						messageText += v.fname + ', ';
					});
					messageText = messageText.replace(/, $/g, '');
				}

				window.QuoteMessage.quoteMessage(message.MID, messageText, message.mfrom);
			},

			messageMarkTakeAway: function(message) {
				if (message.sendingTakeAway) {
					return;
				}
				message.sendingTakeAway = true;
				let data = new FormData();
				data.append("messageId", message.MID);
				axios.post("/sendmessage/mark_take_away", data).then((r) => {
					if (r.data.success) {
						message.takeAway = true;
					}
				}).catch(() => {}).then(() => {
					message.sendingTakeAway = false;
				});
			},

			messageRate: function(message, event) {
				if (message.rateSending) {
					return;
				}
				message.rateSending = true;
				let check = (event.access == 2);
				axios.get('/api/inbox/setscore?inboxId=' + message.MID + '&score=' + event.score + '&check=' + check).then(() => {
					message.support_score = {
						score: event.score,
						access: event.access,
					};
				}).catch(() => {}).then(() => {
					message.rateSending = false;
				});
			},

			messageCancel: function(message) {
				if (message.canelSending) {
					return;
				}
				message.canelSending = true;
				let data = new FormData();
				data.append('inboxId', message.MID);
				data.append('action', 'workerDeclineOffer');
				data.append('orderId', message.offerOrderData.orderData.OID);
				data.append('page', $('#currentPage').text());
				axios.post('/inbox_order', data).then(() => {
					message.offerOrderData.status = 'cancel';
					// Скролл в конец переписки
					if (this.isChat) {
						Vue.nextTick(() => {
							chatModule.scrollToEnd();
						});
					}
				}).catch(() => {}).then(() => {
					message.canelSending = false;
				});
			},

			messageRefuse: function(message) {
				if (message.refuseSending) {
					return;
				}
				message.refuseSending = true;
				let data = new FormData();
				data.append('inboxId', message.MID);
				data.append('action', 'payerDeclineOffer');
				data.append('orderId', message.offerOrderData.orderData.OID);
				data.append('page', $('#currentPage').text());
				axios.post('/inbox_order', data).then(() => {
					message.offerOrderData.status = 'cancel';
				}).catch(() => {}).then(() => {
					message.refuseSending = false;
				});
			},

			complainSubmit: function() {
				if (this.messageToComplain.compainSending) {
					return;
				}
				this.messageToComplain.compainSending = true;
				let data = new FormData();
				data.append('submg', '1');
				data.append('id', this.messageToComplain.MID);
				data.append('comment', this.complainComment);
				data.append('page', $('#currentPage').text());
				this.sendingCount++;
				axios.post('/sendmessage/complain', data).then(() => {
					this.complainModalShown = false;
					show_message('success', '<p>' + t('Ваше сообщение принято, модератор рассмотрит его в ближайшее время.') + '</p>');
					$(window).scrollTop(0);
				}).catch(() => {}).then(() => {
					this.sendingCount--;
					this.messageToComplain.compainSending = false;
				});
			},

			deleteSubmit: function() {
				if (this.messageToDelete.deleteSending) {
					return;
				}
				this.messageToDelete.deleteSending = true;
				let data = new FormData();
				data.append('action', 'remove');
				data.append('id', this.messageToDelete.MID);
				this.sendingCount++;
				let url = '';
				if (this.isChat) {
					url = '/inbox_remove_message';
				}
				axios.post(url, data).then((r) => {
					const messageIndex = this.messages.indexOf(this.messageToDelete);
					if (messageIndex !== -1) {
						this.messages.splice(messageIndex, 1);
					}
					window.appSidebar.$refs.conversationFiles.removeFiles(this.messageToDelete.filesArray);
					this.deleteModalShown = false;
					if (r.data && r.data.closeConversation) {
						$('.js-send-error-assign-cancel').removeClass('hidden');
						$('#block_message').addClass('hidden');
						$('#block_message_new').addClass('hidden');
						$('.sidebarArea').addClass('hidden');
					}
					if (window.isChat) {
						if (!this.messages.length) {
							// Сбрасываем выбранный чат, если сообщений в диалоге не осталось
							window.bus.$emit('chatReset');
						}
					}
				}).catch(() => {}).then(() => {
					this.messageToDelete.deleteSending = false;
					this.sendingCount--;
				});
			},

			toArchive: function() {
				let data = new FormData();
				data.append('auid', this.conversationUserId);
				data.append('subarc', '1');
				axios.post('/inbox', data).then(() => {
					window.location.href = '/inbox';
				});
			},

			onHideModal: function () {
				$('body').attr('data-modal-open-count', 0)
					.removeClass('modal-open')
					.css({'padding-right': 0});
			},

			/**
			 * Html-шаблон инфоблока о заказе
			 */
			getInfoBlockHtml: function(hasHeader) {
				let infoBlockHtml = '';
				const infoBlockText = {
					short: '{{0}} вы работали с {{1}} над общим заказом, и переписка велась внутри этого заказа.',
					long: 'C {{0}} по {{1}} вы работали с {{2}} над общим заказом, и переписка велась внутри этого заказа.',
				};

				if (hasHeader) {
					infoBlockHtml += '<div class="conversation-date_separator t-align-c mb40">' +
							'<div class="dib mb-8 pl10 pr10 bodybg f12 color-gray" style="background-color: transparent;">' +
							this.infoBlock['startDay'] + ' ' + this.infoBlock['startMonth'] + ' ' + this.infoBlock['startYear'] +
							'</div>' +
							'<hr class="gray m0">' +
							'</div>';
				}

				infoBlockHtml += '<div class="conversation-info-block t-align-c sppbox p10-20 ' + (hasHeader ? 'mb40' : 'mt5') + '">' +
						'<div class="d-flex justify-content-between align-items-center">' +
						'<div class="h36 mr15 t-align-l"><img src="' + Utils.cdnImageUrl('/icon_cart_circle.png') + '" alt="" width="36" height="36"></div>' +
						'<div class="t-align-l">';

				if (this.infoBlock['startDay'] === this.infoBlock['endDay'] && this.infoBlock['startMonth'] === this.infoBlock['endMonth']) {
					infoBlockHtml += t(infoBlockText.short, [
						this.infoBlock['endDay'] + ' ' + this.infoBlock['endMonth'] + ' ' + this.infoBlock['endYear'],
						this.infoBlock['interlocutor']
					]);
				} else if (this.infoBlock['startMonth'] === this.infoBlock['endMonth'] && this.infoBlock['startYear'] === this.infoBlock['endYear']) {
					infoBlockHtml += t(infoBlockText.long, [
						this.infoBlock['startDay'],
						this.infoBlock['endDay'] + ' ' + this.infoBlock['endMonth'] + ' ' + this.infoBlock['endYear'],
						this.infoBlock['interlocutor']
					]);
				} else if (this.infoBlock['startYear'] === this.infoBlock['endYear']) {
					infoBlockHtml += t(infoBlockText.long, [
						this.infoBlock['startDay'] + ' ' + this.infoBlock['startMonth'],
						this.infoBlock['endDay'] + ' ' + this.infoBlock['endMonth'] + ' ' + this.infoBlock['endYear'],
						this.infoBlock['interlocutor']
					]);
				} else {
					infoBlockHtml += t(infoBlockText.long, [
						this.infoBlock['startDay'] + ' ' + this.infoBlock['startMonth'] + ' ' + this.infoBlock['startYear'],
						this.infoBlock['endDay'] + ' ' + this.infoBlock['endMonth'] + ' ' + this.infoBlock['endYear'],
						this.infoBlock['interlocutor']
					]);
				}

				infoBlockHtml +=	' <a href="/track?id=' + this.infoBlock['orderId'] + '" class="link">' + t('Перейти в заказ!') + '</a>';
				infoBlockHtml += '</div></div></div>';

				return infoBlockHtml;
			},

			/**
			 * Добавляем в this.messages необходимые данные для вывода инфоблока о заказе
			 */
			addInfoBlock: function() {
				let messagesByDate = [];
				let	messagesDates = [];

				_.forEach(this.messages, (v, k) => {
					let messageDate = this.getDate(v.time);
					if (!(messageDate in messagesDates)) {
						messagesDates[messageDate] = [];
					}
					messagesDates[messageDate].push({
						index: k,
						time: v.time,
					});

					if (this.getDate(this.infoBlock.startTime) === this.getDate(v.time)) {
						messagesByDate.push({
							index: k,
							time: v.time,
						});
					}
				});

				if (messagesByDate.length) {
					// Если в переписке есть дата, совпадающая с перепиской в последнем заказе
					// Вставляем инфоблок в сообщения
					const itemFirst = messagesByDate[0];
					const itemLast = messagesByDate[messagesByDate.length - 1];

					if (this.infoBlock.startTime <= itemFirst.time) {
						this.messages[itemFirst.index]['hasInfoBlock'] = true;
						this.messages[itemFirst.index]['infoBlockPosition'] = 'top';
					} else if (this.infoBlock.startTime >= itemLast.time) {
						this.messages[itemLast.index]['hasInfoBlock'] = true;
						this.messages[itemLast.index]['infoBlockPosition'] = 'bottom';
					} else {
						_.forEach(messagesByDate, (v, k) => {
							let nextIndex = k + 1;
							let itemThis = messagesByDate[k];
							if (this.infoBlock.startTime >= v.time && this.infoBlock.startTime < messagesByDate[nextIndex].time) {
								this.messages[itemThis.index]['hasInfoBlock'] = true;
								this.messages[itemThis.index]['infoBlockPosition'] = 'bottom';
								return false;
							}
						});
					}
				} else {
					// Если в переписке нет даты, совпадающей с перепиской в последнем заказе
					// Вставляем инфоблок обособленно
					const messagesDatesIndexes = Object.keys(messagesDates);
					const itemFirst = messagesDates[messagesDatesIndexes[0]][0];
					const itemLast = messagesDates[messagesDatesIndexes[messagesDatesIndexes.length - 1]][0];

					if (this.infoBlock.startTime < itemFirst.time) {
						this.messages[itemFirst.index]['hasInfoBlockSingle'] = true;
						this.messages[itemFirst.index]['infoBlockPosition'] = 'top';
					} else if (this.infoBlock.startTime > itemLast.time) {
						this.messages[itemLast.index]['hasInfoBlockSingle'] = true;
						this.messages[itemLast.index]['infoBlockPosition'] = 'bottom';
					} else {
						let k = 0;
						for (let messagesDatesIndex in messagesDates) {
							let nextIndex = k + 1;
							let itemThis = messagesDates[messagesDatesIndex][0];

							if (this.infoBlock.startTime > itemThis.time && this.infoBlock.startTime < messagesDates[messagesDatesIndexes[nextIndex]][0].time) {
								this.messages[itemThis.index]['hasInfoBlockSingle'] = true;
								this.messages[itemThis.index]['infoBlockPosition'] = 'bottom';
								return false;
							}

							k++;
						}
					}
				}
			},

			/**
			 * Загрузка предыдущих сообщений
			 */
			showMoreMessages: function() {				
				let data = new FormData();
				data.append('userId', this.conversationUserId);
				data.append('offset', this.messages.length);
				if (this.isChat) {
					// Экономная экономия :) Получаем только список сообщений и онлайн-статус пользователей
					data.append('onlyMessages', true);
				}
				this.moreMessagesLoaderShow = true;
				axios.post('/inbox_more_messages', data).then((response) => {
					if (response.data.success) {
						if (this.isChat) {
							// Меняет онлайн-статус собеседника в списке диалогов и шапке диалога
							window.bus.$emit('updateChatOnlineUsers', this.conversationUserId, (this.conversationUserId in response.data.data.params.isOnline));
						}
						this.moreMessagesLoaderShow = false;
						if (response.data.data.messages.length) {
							// Запоминаем высоту сайта
							let oldDocumentHeight = Math.max(
							  document.body.scrollHeight, document.documentElement.scrollHeight,
							  document.body.offsetHeight, document.documentElement.offsetHeight,
							  document.body.clientHeight, document.documentElement.clientHeight
							);
							if (this.isChat) {
								let chatScrollEl = isMobile() ? document.body : jQuery('.kwork-conversation__list .scrolly-viewport')[0];
								oldDocumentHeight = Math.max(
									chatScrollEl.scrollHeight,
									chatScrollEl.offsetHeight,
									chatScrollEl.clientHeight
								);
							}
							// Добавление сообщений в начало списка
							this.messages = [...response.data.data.messages, ...this.messages];
							this.countMoreMessages -= response.data.data.messages.length;		
							Vue.nextTick(() => {
								// Запоминаем новую высоту сайта
								let newDocumentHeight = Math.max(
								  document.body.scrollHeight, document.documentElement.scrollHeight,
								  document.body.offsetHeight, document.documentElement.offsetHeight,
								  document.body.clientHeight, document.documentElement.clientHeight
								);
								if (this.isChat) {
									let chatScrollEl = isMobile() ? document.body : jQuery('.kwork-conversation__list .scrolly-viewport')[0];
									newDocumentHeight = Math.max(
										chatScrollEl.scrollHeight,
										chatScrollEl.offsetHeight,
										chatScrollEl.clientHeight
									);
									if (jQuery(window).width() > 767) {
										chatScrollEl.scrollBy(0, newDocumentHeight - oldDocumentHeight);
										return false;
									}
								}
								// Скролим к месту до нажатие кнопки показать сообщения
								window.scrollBy(0, newDocumentHeight - oldDocumentHeight);
							});							
						}
					}
				});
			},
			
			/**
			 * Закрываем модальное окно о удалении собщения
			*/
			closeDeleteModal: function() {
				this.deleteModalShown = false;
			},

			/**
			 * Скролл к первому непрочитанному сообщению
			 */
			chatScrollToFirstUnread: function () {
				if (!this.isChat) {
					return false;
				}

				if (!this.isLoaderShow) {
					// Прокручиваем скролл к первому непрочитанному сообщению, на мобильных - в конец
					let finded;
					if (jQuery(window).width() < 768) {
						finded = false;
					} else {
						finded = this.scrollToUnread(false, false);
					}
					// Если непрочитанных нет - то просто в конец
					if (!finded) {
						chatModule.scrollToEnd();

						let article = jQuery('.article');
						if (article.length) {
							article.imagesLoaded(function () {
								chatModule.scrollToEnd();
							});
						}
					}
				}
			},
		},
	}
</script>