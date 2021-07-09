<template>
	<div>
		<div class="chat__search" :class="{'chat__search_focused': isChatSearchFocus || searchQuery.length }">
			<div class="chat__search-inner">
				<div v-if="loaderSearchShow" class="chat__search-loader">
					<chat-loader :isLoaderSmall="true"></chat-loader>
				</div>
				<div v-show="!loaderSearchShow" class="chat__search-button chat__search-button_submit" :title="t('Найти')" @click="chatListSearch">
					<i class="fa fa-search"></i>
				</div>
				<input class="chat__search-input"
					   type="text"
					   v-model.trim="searchQuery"
					   @focus="isChatSearchFocus = true"
					   @blur="isChatSearchFocus = false"
					   @keyup.enter="chatListSearch"
					   :placeholder="t('Поиск')">
				<div v-if="isChatSearched || searchQuery.length"
					 class="chat__search-button chat__search-button_reset"
					 :title="t('Очистить')"
					 @click="chatListReset">
					<i class="fl-close"></i>
				</div>
			</div>
		</div>
		<div class="chat__list-wrapper">
			<div v-if="chatSearchNotFound" class="chat__list-item chat__list-item_nohover db t-align-c">
				{{ chatSearchNotFound }}
			</div>
			<scrolly v-if="isDesktop && chatList.length > 0 && !chatSearchNotFound"
					 class="vertical-scrollbar"
					 :passive-scroll="true"
					 @scrollchange="handleScroll">
				<scrolly-viewport>
					<transition-group name="flip-list" tag="ul" class="chat__list">
						<chat-list-item
								v-for="(item, index) in chatList"
								:ref="'chatListItem' + item.USERID"
								:key="item.USERID"
								:item="item"
								:chatSelected="chatSelected"
								:chatWarningContent="chatWarningContent">
						</chat-list-item>
					</transition-group>
				</scrolly-viewport>
				<scrolly-bar axis="y"></scrolly-bar>
			</scrolly>
			<div v-else-if="chatList.length > 0 && !chatSearchNotFound" class="chat__scroll" @scroll="handleScroll">
				<transition-group name="flip-list" tag="ul" class="chat__list">
					<chat-list-item
							v-for="(item, index) in chatList"
							:ref="'chatListItem' + item.USERID"
							:key="item.USERID"
							:item="item"
							:chatSelected="chatSelected"
							:chatWarningContent="chatWarningContent">
					</chat-list-item>
				</transition-group>
			</div>
			<div v-if="chatListLoaderShow" class="chat__list-loader">
				<chat-loader></chat-loader>
			</div>
		</div>
		<div class="chat__sound"
			 @mouseover="isChatSoundHover = true"
			 @mouseleave="isChatSoundHover = false"
			 @click="setMessageSound">
			<div class="chat__sound-icon">
				<img v-show="isChatSoundHover && isMessageSoundOn" src="/images/chat/chat-bell_active-hover.svg" width="20" height="26" alt="">
				<img v-show="!isChatSoundHover || !isMessageSoundOn"
					 :src="'/images/chat/chat-bell_' + (isMessageSoundOn ? 'active' : 'inactive') + '.svg'"
					 width="20"
					 height="26"
					 alt="">
			</div>
			<div class="chat__sound-link">
				{{ chatSoundLinkContent }}
			</div>
		</div>
		<!-- Предзагружаем иконки -->
		<div class="chat__preloader">
			<span class="fa fa-pencil"></span>
			<img src="/images/chat/chat-bell_active.svg" width="20" height="26" alt="">
			<img src="/images/chat/chat-bell_active-hover.svg" width="20" height="26" alt="">
			<img src="/images/chat/chat-bell_inactive.svg" width="20" height="26" alt="">
		</div>
	</div>
</template>
<script>
	import i18nMixin from 'appJs/i18n-mixin'; // Локализация
	import chatLoader from 'moduleJs/chat/chat-loader.vue'; // Иконка загрузки чата
	import chatListItem from 'moduleJs/chat/chat-list-item.vue'; // Отдельный диалог
	import cdnMixin from 'appJs/cdn-mixin.js'; // Приведение относительных ссылок к абсолютным (CDN)
	import { Scrolly, ScrollyViewport, ScrollyBar } from 'appJs/vue-scrolly.min.js'; // Кастомный скролбар
	import Draft from 'appJs/draft.js'; // Черновик

	export default {
		components: {
			'chat-loader': chatLoader,
			'chat-list-item': chatListItem,
			Scrolly,
			ScrollyViewport,
			ScrollyBar,
		},

		mixins: [i18nMixin, cdnMixin],

		data() {
			return {
				i18n: {
					en: {
						'Поиск': 'Search',
						'Найти': 'Find',
						'Очистить': 'Clean',
						'По запросу «{{0}}» ничего не найдено': 'No results found for query «{{0}}»',
						'Быстрый ответ': 'Quick response',
						'Покупатели надеются на скорейший ответ. Чем <strong>быстрее вы отвечаете</strong>, тем <strong>выше шанс продать</strong> услугу. Если вы несколько раз не отвечаете на новые обращения покупателей, то кворки блокируются на несколько дней.': 'Buyers expect a <strong>quick response. The faster you answer, the higher the chance to sell the service</strong>. If you repeatedly ignore new messages from buyers, your kworks will be blocked for several days.',
						'Внимание! <strong>Требование обязательного ответа</strong> действует только на <strong>первое личное сообщение</strong> от нового покупателя или того, с кем вы не преписывались более 20 дней.': 'Attention! <strong>The answer is required</strong> only for <strong>the first private message</strong> from a new buyer (or in case if you had the last conversation more than 20 days ago).',
						'Входящие - Kwork': 'Inbox - Kwork',
						'Диалог со службой поддержки': 'Chat with the Support team',
						'Диалог с': 'Chat with',
						'Внимание! Пользователь общается только на английском языке.': 'Attention! The user communicates only in English.',
						'У вас есть несколько активных заказов у данного пользователя. Выберите, к какому заказу относится это сообщение': 'You have several active orders from this user. Select to which order this message applies',
						'Выбрать': 'Select',
						'Напишите, как вы будете решать задачу клиента': 'How you will solve the buyer\'s task',
						'Выберите подкатегорию': 'Select a sub-category',
						'Также вы можете <span class=\"link_local\">предложить покупателю</span> один из своих активных кворков': 'Also, you can <span class=\"link_local\">offer the buyer</span> one of your active kworks',
						'Создать <span class=\"link_local\">индивидуальное предложение</span> для покупателя': 'Create <span class=\"link_local\">an individual offer</span> for the buyer',
						'Да, проект «{{0}}»': 'Yes, the project «{{0}}»',
						'Это предложение по проекту покупателя на бирже?': 'Is this a proposal for a project buyer on the exchange?',
						'Если данный заказ связан с проектом покупателя на бирже, на который вы оставили отклик, то выберите соответствующую опцию ниже. Это позволит правильно учитывать статистику по заказу.': 'If this order is made for a buyer\'s project on the exchange, on which you left a response, then select the appropriate option below. This will allow us to correctly account for the statistics.',
						'Нет': 'No',
						'печатает': 'is typing',
					},
				},

				searchQuery: '',
				loaderSearchShow: false,
				isChatSearchFocus: false,
				isChatSoundHover: false,
				chatSearchNotFound: null,
				isChatSearched: false,

				isMessageSoundOn: false,
				chatSelected: 0,
				chatList: [],
				chatListPage: null,
				chatListItemsPerPage: null,
				chatListLoaderShow: false,
				chatListScrollDisabled: false,
				chatOnlineUsersLocal: [],

				supportUserId: null,
				actorId: null,
				isDesktop: true,

				axiosRequests: {
					chatList: null,
					messagesList: null,
				},
			}
		},

		computed: {
			hoverMessageSound: function () {

			},
			/**
			 * Выводит текст включения/выключения звуковых уведомлений
			 */
			chatSoundLinkContent: function () {
				return t('{{0}} звуковые уведомления', [this.isMessageSoundOn ? t('Включены') : t('Выключены')]);
			},

			/**
			 * Тултип необходимости срочного ответа
			 * Передается в компоненты: chat-list-item и conversation-message
			 */
			chatWarningContent: function () {
				return '<div class="tooltip-chat">'
					+ '<div class="tooltip-chat__title">' + t('Быстрый ответ') + '</div>'
					+ '<div class="mt5">' + t('Покупатели надеются на скорейший ответ. Чем <strong>быстрее вы отвечаете</strong>, тем <strong>выше шанс продать</strong> услугу. Если вы несколько раз не отвечаете на новые обращения покупателей, то кворки блокируются на несколько дней.') + '</div>'
					+ '<div class="mt8">' + t('Внимание! <strong>Требование обязательного ответа</strong> действует только на <strong>первое личное сообщение</strong> от нового покупателя или того, с кем вы не преписывались более 20 дней.') + '</div>'
					+ '</div>';
			},
		},

		created: function () {
			this.i18nInit();

			this.isMessageSoundOn = MESSAGE_SOUND_ENABLE === 1;
			this.chatList = window.chatList || [];
			this.chatListPage = 1;
			this.chatListItemsPerPage = window.chatListItemsPerPage || 50;
			this.chatOnlineUsersLocal = window.chatOnlineUsers || [];

			this.supportUserId = window.supportUserId || null;
			this.actorId = parseInt(window.actorId) || null;

			//Заполняет массив собеседников онлайн
			this.updateChatOnlineUsers(this.chatList, 'USERID', 'is_online');

			// Событие выбора диалога
			window.bus.$on('loadConversation', (userId, messageText = '') => {
				let isLoadedByLink = false; // Означает, что переход был внутри чата, а не по ссылке
				this.loadConversation(userId, messageText, isLoadedByLink);
			});

			// Событие прочитанности диалога
			window.bus.$on('setChatRead', (userId, readedMessagesCount) => {
				this.setChatRead(userId, readedMessagesCount);
			});

			// Событие смены онлайн-статуса собеседника в списке диалогов и шапке диалога
			window.bus.$on('updateChatOnlineUsers', (userId, userOnlineStatus) => {
				this.updateChatOnlineUsers('', userId, userOnlineStatus);
			});

			// Событие запуска эффекта переключения списка диалогов / выбранного диалога на мобильных
			window.bus.$on('setMobileEffects', () => {
				this.setMobileEffects();
			});

			// Событие сброса выбранного чата
			window.bus.$on('chatReset', () => {
				this.chatReset();
			});
		},

		mounted: function () {
			if (PULL_MODULE_ENABLE && this.chatListPage === 1) {
				jQuery(document).ready(() => {
					let _this = this;

					// Новое сообщение
					PullModule.on(PULL_EVENT_NEW_INBOX, function (response) {
						_this.onPush(response, 'new');
					});

					// Последнее сообщение отредактировано
					PullModule.on(PULL_EVENT_INBOX_MESSAGE_EDIT, function (response) {
						_this.onPush(response, 'edit');
					});

					// Последнее сообщение удалено
					PullModule.on(PULL_EVENT_INBOX_MESSAGE_DELETE, function (response) {
						_this.onPush(response, 'delete');
					});

					// Отправка своего сообщения
					PullModule.on(PULL_EVENT_INBOX_SENT, function (response) {
						_this.onPush(response, 'sent');
					});
				});
			}

			this.$nextTick(function() {
				window.addEventListener('resize', this.setDesktopState);
				this.setDesktopState();

				// Подгружаем выбранный диалог, если заход по прямой ссылке
				if (window.conversationUserId) {
					let isLoadedByLink = true; // Означает, что переход был по ссылке, а не внутри чата
					this.loadConversation(window.conversationUserId, (window.defaultMessage || ''), isLoadedByLink);
				}
			});
		},

		beforeDestroy() {
			window.bus.$off('loadConversation');
			window.bus.$off('setChatRead');
			window.bus.$off('updateChatOnlineUsers');
			window.bus.$off('setMobileEffects');
			window.bus.$off('chatReset');

			window.removeEventListener('resize', this.setDesktopState);
		},

		methods: {
			/**
			 * Проверяет декстопную версию сайта
			 */
			setDesktopState: function () {
				this.isDesktop = jQuery(window).width() > 767;
			},

			/**
			 * Поиск по списку диалогов
			 */
			chatListSearch: _.debounce(function () {
				if (!this.searchQuery.length) {
					return false;
				}
				this.loaderSearchShow = true;

				let data = new FormData();
				data.append('searchQuery', this.searchQuery);

				// Отменяем предыдущий аякс-запрос
				this.axiosRequestCancel('messagesList');
				this.axiosRequests['messagesList'] = axios.CancelToken.source();
				axios
					.post('/getdialogs', data, {cancelToken: this.axiosRequests['messagesList'].token})
					.then((response) => {
						if (response.data.success) {
							if (response.data.data.rows.length) {
								this.chatSearchNotFound = null;
								this.chatList = response.data.data.rows;
							} else {
								this.chatSearchNotFound = t('По запросу «{{0}}» ничего не найдено', [this.searchQuery]);
							}
							this.isChatSearched = true;
						}
					})
					.catch((error) => {})
					.finally(() => {
						this.loaderSearchShow = false;
					});
			}, 300),

			/**
			 * Сброс списка диалогов в начальное состояние (как при открытии страницы)
			 */
			chatListReset: _.debounce(function () {
				if (!this.searchQuery.length) {
					return false;
				}
				if (!this.isChatSearched) {
					this.searchQuery = '';
					return false;
				}
				this.chatList = [];

				// Отменяем предыдущий аякс-запрос
				this.axiosRequestCancel('messagesList');
				this.axiosRequests['messagesList'] = axios.CancelToken.source();
				axios
					.post('/getdialogs', '', {cancelToken: this.axiosRequests['messagesList'].token})
					.then((response) => {
						if (response.data.success) {
							this.searchQuery = '';
							this.isChatSearched = false;
							this.chatSearchNotFound = null;
							this.chatList = response.data.data.rows;
						}
					})
					.catch((error) => {})
					.finally(() => {});
			}, 300),

			/**
			 * Пагинация при скролле
			 *
			 * @param scrollLayout
			 */
			handleScroll: _.debounce(function (scrollLayout) {
				if (chatList.length >= this.chatListItemsPerPage && !this.blockOnScrollFunction) {
					let scrolledEl = jQuery('.chat__list-wrapper .scrolly-viewport'),
						chatListHeight = jQuery('.chat__list').outerHeight(true),
						chatListBottom = (chatListHeight - scrolledEl.outerHeight(true)) / 2;

					if (!this.isDesktop) {
						scrolledEl = jQuery(window);
						chatListBottom = (chatListHeight - (window.outerHeight > 0 ? window.outerHeight : window.innerHeight)) / 2;
					}

					if (scrolledEl.scrollTop() > chatListBottom) {
						this.blockOnScrollFunction = true;
						this.chatListLoaderShow = true;

						let data = new FormData();
						data.append('page', this.chatListPage + 1);
						axios
							.post('/getdialogs', data)
							.then((response) => {
								this.chatListLoaderShow = false;
								if (response.data.success) {
									let chatListNextPage = response.data.data.rows;
									if (!_.isEmpty(chatListNextPage)) {
										this.chatList = [...this.chatList, ...chatListNextPage];
										this.chatListPage++;
										if (chatListNextPage.length >= this.chatListItemsPerPage) {
											this.blockOnScrollFunction = false;
										}
									}
								}
							})
							.catch((error) => {
								this.blockOnScrollFunction = false;
								this.chatListLoaderShow = false;
							})
							.finally(() => {});
					}
				}
			}, 200),

			/**
			 * Обработка пушей
			 * @param response
			 * @param pushType
			 */
			onPush: function (response, pushType = '') {
				let conversationUserId = parseInt(this.actorId === response.from ? response.to_user_id : response.from);

				if (pushType === 'sent') {
					if (!response.inbox_id || parseInt(response.to_user_id) !== conversationUserId) {
						return false;
					}
				}
				if (pushType === 'new') {
					if (parseInt(response.from) !== conversationUserId) {
						return false;
					}
				}

				let data = new FormData();
				let chatListShow = this.chatList.length;
				if (chatListShow > this.chatListItemsPerPage) {
					data.append('limit', chatListShow);
				}
				axios
					.post('/getdialogs', data)
					.then((response) => {
						if (response.data.success) {
							let chatListActual = response.data.data.rows;
							if (!_.isEmpty(chatListActual)) {
								this.chatList = chatListActual;

								//Заполняем массив собеседников онлайн
								this.updateChatOnlineUsers(this.chatList, 'USERID', 'is_online');
							}
						}
					})
					.catch((error) => {})
					.finally(() => {});
			},

			/**
			 * Управляет звуковыми уведомлениями
			 */
			setMessageSound: function() {
				let messageSoundState = MESSAGE_SOUND_ENABLE === 1 ? 0 : 1;
				MESSAGE_SOUND_ENABLE = messageSoundState;
				this.isMessageSoundOn = !this.isMessageSoundOn;

				let data = new FormData();
				data.append('message_sound', MESSAGE_SOUND_ENABLE);
				axios
					.post('/api/inbox/setmessagesound', data)
					.then((response) => {
						if (response.data.status === 'success') {
							localStorage.setItem('change-sound-message', MESSAGE_SOUND_ENABLE);
						} else {
							MESSAGE_SOUND_ENABLE = messageSoundState;
							this.isMessageSoundOn = !this.isMessageSoundOn;
						}
					})
					.catch((error) => {
						MESSAGE_SOUND_ENABLE = messageSoundState;
						this.isMessageSoundOn = !this.isMessageSoundOn;
					})
					.finally(() => {});
			},

			/**
			 * Меняет состояние прочитанности диалога
			 * @param userId
			 * @param readedMessagesCount
			 */
			setChatRead: function (userId, readedMessagesCount) {
				let index = _.findIndex(this.chatList, {'USERID': parseInt(userId)});
				if (index !== -1) {
					this.$set(this.chatList[index], 'unread_count', parseInt(this.chatList[index].unread_count) - parseInt(readedMessagesCount));
				}
			},

			/**
			 * Заполняет массив собеседников онлайн
			 * @param data
			 * @param userId
			 * @param userOnlineStatus
			 */
			updateChatOnlineUsers: function (data, userId, userOnlineStatus) {
				if (_.isEmpty(data)) {
					let index = this.chatOnlineUsersLocal.indexOf(userId);
					let chatListIndex = _.findIndex(this.chatList, {'USERID': parseInt(userId)});

					if (index === -1) {
						if (userOnlineStatus) {
							this.chatOnlineUsersLocal.push(parseInt(userId));
						}
					} else {
						if (!userOnlineStatus) {
							this.chatOnlineUsersLocal.splice(index, 1);
						}
					}

					if (chatListIndex !== -1) {
						this.$set(this.chatList[chatListIndex], 'is_online', userOnlineStatus);
					}
				} else {
					_.forEach(data, (v, k) => {
						let index = this.chatOnlineUsersLocal.indexOf(v[userId]);
						if (index === -1) {
							if (v[userOnlineStatus]) {
								this.chatOnlineUsersLocal.push(parseInt(v[userId]));
							}
						} else {
							if (!v[userOnlineStatus]) {
								this.chatOnlineUsersLocal.splice(index, 1);
							}
						}
					});
				}
			},

			/**
			 * Скрывает формы:
			 * инд. предложения
			 * предложения кворка
			 * запроса инд. кворка
			 */
			hideForms: function () {
				jQuery(function () {
					chatModule.offerToggle('close');
					chatModule.individualMessageToggle('close');
				});
			},

			/**
			 * Сбрасывает выбранный чат
			 */
			chatReset: function () {
				// DOM
				jQuery('.chat').removeClass('chat_loaded-by-link chat_selected');
				jQuery('#message_form_wrapper').addClass('hidden');

				// Заголовок
				document.title = t('Входящие - Kwork');

				// Урл в адресной строке
				history.replaceState({}, null, '/inbox');

				// Состояние чата в компонентах
				window.bus.$emit('setChatState', false, false);

				// Выбранный диалог в списке диалогов
				this.chatSelected = 0;
			},

			/**
			 * Запускает эффект переключения списка диалогов / выбранного диалога на мобильных
			 */
			setMobileEffects: function () {
				let chat = jQuery('.chat'),
					chatSelectedClass = 'chat_selected';

				if (chat.hasClass(chatSelectedClass)) {
					if (jQuery(window).width() < 768) {
						this.chatReset();
						jQuery('body').removeClass('compensate-for-scrollbar compensate-for-scrollbar-m');
						jQuery(window).scrollTop(window.chatScrolled);
						window.chatScrolled = 0;
					}
					chat.removeClass(chatSelectedClass);
				} else {
					chat.addClass(chatSelectedClass);
				}
			},

			/**
			 * Заполняет данные для формы отправки сообщения / индивидуального предложения
			 * Увы, на jQuery (согласовано). Кто желает в рефакторинг, милости просим в conversation_bit.js
			 * @param params
			 * @param messageText
			 * @param isLoadedByLink
			 */
			loadMessageForm: function (params, messageText = '', isLoadedByLink = false) {
				let messageInput = jQuery('#message_body'),
					messageForm = jQuery('#new_message'),
					messageFormWrapper = jQuery('#message_form_wrapper'),
					messageWriteWrap = jQuery('.write-wrap'),
					conversationWrapper = jQuery('.chat__conversation-wrapper'),
					conversationWrapperWithoutFooter = 'chat__conversation-wrapper_without_footer',
					individualMessage = jQuery('#js-popup-individual-message__container');

				// Скрытые поля форм
				messageForm.find('.js-msgto').val(params.conversationUserId);
				messageForm.find('.js-kwork-id').val(params.kworkId);
				individualMessage.find('.js-msgto').val(params.conversationUserId);

				// Информер пользователя о вводе сообщение собеседником
				jQuery(pullIsTypeMessage.selectorInfoTypeMessage).hide();
				jQuery('#info-type-message-bottom').attr('data-user', params.u.username).text(params.u.username + ' ' + t('печатает'));
				window.pullIsTypeMessage = new PullIsTypeMessage('#message_body', '#info-type-message-bottom[data-user="' + params.u.username + '"]', window.receiverId);

				// Заказы между собеседниками
				let chatOrderListHtml = '',
					chatOrderList = jQuery('.js-chat-orders-list'),
					chatOrderListWrapper = chatOrderList.closest('.js-orders-between-users-block');
				if (!_.isEmpty(params.ordersBetweenUsers)) {
					_.forEach(params.ordersBetweenUsers, (v, k) => {
						chatOrderListHtml += '<div class="break-word mt5">' +
							'<input type="radio" name="orderId" id="orderId' + v['id'] + '" value="' + v['id'] + '" class="js-send-order-message-radio styled-radio">' +
							'<label for="orderId' + v['id'] + '">' + v['kwork_title'] + '</label>' +
							'</div>';
					});
					chatOrderListWrapper.removeClass('hidden');
				} else {
					chatOrderListWrapper.addClass('hidden');
				}
				chatOrderList.html(chatOrderListHtml);

				// Индивидуальный заказ продавцу
				jQuery('#budget').attr('placeholder', Utils.priceFormat(params.customMinPrice) + ' - ' + Utils.priceFormat(params.customMaxPrice));

				// Категории для индивидуального предложения
				let categoriesSelect = jQuery('.offer-individual__category .js-category-select');
				if (params.isOrderButtonEnabled && categoriesSelect.children('option').length <= 1) {
					let categoriesHtml = '',
						subCategoriesHtml = '';

					_.forEach(params.categories, (v, k) => {
						categoriesHtml += '<option value="' + k + '">' + t(v.name) + '</option>';

						if (v.cats) {
							subCategoriesHtml += '<div data-category-id="' + v.id + '" class="js-sub-category-wrap hidden">' +
								'<select class="js-sub-category-select select-styled select-styled--thin long-touch-js" name="" autocomplete="off">' +
								'<option selected disabled value="">' + t('Выберите подкатегорию') + '</option>';
								_.forEach(v.cats, (v2, k2) => {
									subCategoriesHtml += '<option value="' + v2.id + '">' +
										t(v2.name) +
									'</option>';
								});
								subCategoriesHtml += '</select>' +
								'</div>';
						}
					});
					if (categoriesHtml.length) {
						categoriesSelect.append(categoriesHtml).trigger('chosen:updated');
					}
					if (subCategoriesHtml.length) {
						jQuery('.js-offer-individual-item[data-target="sub_category"]').html(subCategoriesHtml);
						jQuery('.offer-individual__category .js-sub-category-select').chosen({
							width: '100%',
							disable_search: true,
						});
					}
				}

				// Предложение кворка
				let kworksSelect = jQuery('#request-kwork-id');
				if ('kworks' in params && !_.isEmpty(params.kworks) && kworksSelect.children('option').length === 0) {
					let switcherHtml = '';
					let kworksHtml = '';
					switcherHtml = '<div id="change-to-kwork-choose" class="mt20 hidden noselect text-center">' +
							t('Также вы можете <span class=\"link_local\">предложить покупателю</span> один из своих активных кворков') +
						'</div>' +
						'<div id="change-to-custom-kwork" class="mt20 hidden noselect text-center">' +
							t('Создать <span class=\"link_local\">индивидуальное предложение</span> для покупателя') +
						'</div>';

					kworksHtml += '<option selected disabled></option>';
					_.forEach(params.kworks, (v, k) => {
						kworksHtml += '<option ' +
							'data-time="' + v.days + '" ' +
							'data-rate="' + v.rate + '" ' +
							'data-package="' + v.is_package + '" ' +
							'data-price="' + Math.round(v.price) + '" ' +
							'data-workerprice="' + Math.round(v.workerPrice) + '" ' +
							'data-base-volume="' + v.volume + '" ' +
							(v.isTimeVolume ? 'data-time-volume="' + v.isTimeVolume + '" ' : '') +
							'value="' + v.PID + '">' +
							_.upperFirst(v.gtitle.toString()) +
							'</option>';
					});

					jQuery('.js-chat-offer-kwork-switcher').html(switcherHtml);
					kworksSelect.append(kworksHtml).trigger('chosen:updated');
				}

				// Задачи индивидуального предложения из conversation_bit.js
				if (window.isOrderStageTester && !('options' in window.offerStages)) {
					let $stages = jQuery('.js-stages-data');
					if ($stages.data('actor-type') === 'worker') {
						window.offerStages.init({
							actorType: $stages.data('actor-type'),
							stages: $stages.data('stages'),
							classDisableButton: $stages.data('button-disable-class'),
							turnover: turnover,
							pageType: $stages.data('page-type'),
							offer: {
								orderId: 0,
								lang: offer.lang,
								stageMinPrice: offer.stageMinPrice,
								customMinPrice: offer.customMinPrice,
								customMaxPrice: offer.customMaxPrice,
								offerMaxStages: offer.offerMaxStages,
								stagesPriceThreshold: offer.stagesPriceThreshold,
							},
							controlEnLang: controlEnLang,
						});
						window.offerStages.generationStages();
					}
				}
				window.OfferIndividualModule.init();

				// Предложение по проекту покупателя на бирже
				let wantsHtml = '';
				if ('indirectlyWants' in params && !_.isEmpty(params.indirectlyWants)) {
					_.forEach(params.indirectlyWants, (v, k) => {
						wantsHtml += '<div>' +
							'<input name="want_id" class="styled-radio" id="want_radio_' + k + '" type="radio" value="' + k + '">' +
							'<label for="want_radio_' + k + '">' +t('Да, проект «{{0}}»', [v]) + '</label>' +
							'</div>';
					});

					if (wantsHtml.length) {
						wantsHtml = '<div class="js-show-with-offer hidden">' +
							'<div>' + t('Это предложение по проекту покупателя на бирже?') + ' <span class="tooltipster" data-tooltip-text="' + t('Если данный заказ связан с проектом покупателя на бирже, на который вы оставили отклик, то выберите соответствующую опцию ниже. Это позволит правильно учитывать статистику по заказу.') + '"><span class="tooltip_circle tooltip_circle--hover ml5">?</span></span></div>' +
							wantsHtml +
							'<div><input name="want_id" class="styled-radio" id="want_radio_empty" type="radio" value=""><label for="want_radio_empty">' + t('Нет') + '</label></div>' +
							'</div>';
					}
				}
				jQuery('.js-chat-wants').html(wantsHtml);

				// Обновляем текст в форме
				let messageTextNew = '';
				if (messageText.length) {
					// Если передавали какой-то текст, добавляем его в поле ввода
					messageTextNew = messageText;
				} else if (!_.isEmpty(params.defaultMessage)) {
					// Если есть дефолтное сообщение или черновик, добавляем его в поле ввода
					messageTextNew = params.defaultMessage;
				}
				messageInput.val(messageTextNew);
				setTimeout(function () {
					messageInput.trigger('input');
				});

				// Показываем / прячем кнопку инд. предложения / запроса в форме отправки
				let buttonOffer = jQuery('.chat__button_offer'),
					mfMessage = jQuery('.mf-message');
				if (params.isOrderButtonEnabled) {
					buttonOffer.removeClass('d-none-important');
					mfMessage.addClass('mf-message_theme_offer');
					messageWriteWrap.removeClass('pb0');
				} else {
					buttonOffer.addClass('d-none-important');
					mfMessage.removeClass('mf-message_theme_offer');
					messageWriteWrap.addClass('pb0');
				}

				// Показываем / прячем форму отправки
				if (params.privateMessageStatus !== true || !_.isEmpty(params.ordersBetweenUsers)) {
					conversationWrapper.addClass(conversationWrapperWithoutFooter);
					messageFormWrapper.addClass('hidden');
					messageInput.blur();
				} else {
					conversationWrapper.removeClass(conversationWrapperWithoutFooter);
					messageFormWrapper.removeClass('hidden');
					messageInput.focus();
				}
			},

			/**
			 * To wait for a browser repaint, you need to use “double” requestAnimationFrame
			 */
			doubleRaf: function (callback) {
				requestAnimationFrame(() => {
					requestAnimationFrame(callback)
				})
			},

			/**
			 * Отменяет предыдущий аякс-запрос
			 *
			 * @param requestName
			 */
			axiosRequestCancel: function (requestName) {
				if (this.axiosRequests[requestName]) {
					this.axiosRequests[requestName].cancel();
				}
			},

			/**
			 * Показывает выбранный диалог
			 * @param userId
			 * @param messageText
			 * @param isLoadedByLink
			 */
			loadConversation: function (userId, messageText = '', isLoadedByLink = false) {
				userId = parseInt(userId);

				// Отменяем предыдущий аякс-запрос
				this.axiosRequestCancel('chatList');

				// Помечаем выбранный диалог
				if (this.chatSelected === userId) {
					return false;
				}
				this.chatSelected = userId;
				this.loadConversationDebounce(userId, messageText, isLoadedByLink);
			},
			loadConversationDebounce: _.debounce(function (userId, messageText = '', isLoadedByLink = false) {
				this.chatSelected = userId;

				// Прячем лишние формы
				this.hideForms();

				// Очищаем список загруженных, но не добавленных в диалог файлов
				if ('appFiles' in window) {
					window.appFiles.$refs.fileUploader.clearFiles();
				}

				// Если юзер неверный
				if (!userId || userId === this.actorId) {
					// Сбрасываем состояние чата
					this.chatReset();
					return false;
				}

				// Мобильная версия
				this.setMobileEffects();

				// Меняем состояние чата
				window.bus.$emit('setChatState', true);

				let data = new FormData();
				data.append('userId', userId);
				data.append('allUnread', 'true');
				data.append('limit', '20');

				this.axiosRequests['chatList'] = axios.CancelToken.source();
				axios
					.post('/inbox_more_messages', data, {cancelToken: this.axiosRequests['chatList'].token})
					.then((response) => {
						if (response.data.success) {
							const params = response.data.data.params;
							const messages = response.data.data.messages;

							if (_.isEmpty(messages)) {
								// Сбрасываем состояние чата
								this.chatReset();
								return false;
							}

							//Заполняем массив собеседников онлайн
							this.updateChatOnlineUsers('', params.conversationUserId, (params.conversationUserId in params.isOnline));

							let chatWarningMessages = [];
							_.forEach(this.chatList, (v, k) => {
								if (v.warning_message_id > 0) {
									chatWarningMessages.push(v.warning_message_id);
								}
							});

							// Меняем заголовок
							document.title = params.isSupportDialog ?
								t('Диалог со службой поддержки') :
								t('Диалог с') + ' ' + params.u.username;

							// Меняем урл в адресной строке
							if (!isLoadedByLink) {
								history.replaceState({}, null, '/inbox/' + params.u.username.toLowerCase());

								// Сбрасываем значения, переданные по прямым ссылкам
								window.requestKwork = 0;
								window.offerId = undefined;
								window.offerData = undefined;
							}

							// Предложение
							window.offer = {
								lang: params.offerLang || 'ru',
								isOrderStageTester: window.isOrderStageTester || 0,
								kworkPackages: params.kworkPackages ? JSON.parse(params.kworkPackages) : [],
								maxKworkCount: window.maxKworkCount || 0,
								multiKworkRate: window.multiKworkRate || 0,
								customMinPrice: params.customMinPrice || 0,
								customMaxPrice: params.customMaxPrice || 0,
								stageMinPrice:  params.stageMinPrice || 0,
								stagesPriceThreshold: params.stagesPriceThreshold || 0,
								offerMaxStages: window.offerMaxStages || 0,
								customPricesOptionsHtml: params.customPricesOptionsHtml || '',
							};
							if (offerId in params) {
								window.offerId = params.offerId;
							}
							if (offerData in params) {
								window.offerData = params.offerData;
							}
							window.offerLang = params.offerLang || 'ru';
							window.controlEnLang = params.controlEnLang ? 1 : 0;
							window.turnover = params.turnover;
							window.commissionRanges = JSON.parse(params.commissionRanges);
							window.receiverId = parseInt(userId) || null;
							window.showInboxAllowModal = params.showInboxAllowModal;
							window.minPrices = params.minPrices;
							window.stageMinPrices = params.stageMinPrices;
							window.minBudget = params.customMinPrice || 0;
							window.maxBudget = params.customMaxPrice || 0;
							window.isPageNeedSmsVerification = params.isPageNeedSmsVerification || false;
							window.conversationUserId = params.conversationUserId || null;
							window.userAvatarColors = params.userAvatarColors || {};

							// Шапка выбранного диалога
							let chatConversationHeaderParams = {
								isChatSelected: true,

								isSupportDialog: params.isSupportDialog || false,
								isOrderButtonEnabled: params.isOrderButtonEnabled || false,
								userId: userId,
								userName: params.u.username || '',
								userFullName: params.u.fullname || '',
								userProfileUrl: params.u.profileUrl || '',
								userAvatarUrl: params.u.profilepicture || '',
								userLastDate: params.u.lastDate || '',
								payerLevelBadge: params.userPayerLevelBadge || null,
								userOrdersCount: {
									worker: parseInt(params.workerOrdersCount) || null,
									payer: parseInt(params.payerOrdersCount) || null,
								},
								privateMessageStatus: params.privateMessageStatus || false,
								hasOrdersBetweenUsers: !_.isEmpty(params.ordersBetweenUsers),
								actorIsPayer: window.actorIsPayer || false,

								hasConversationFiles: !_.isEmpty(params.files_dialog) || false,
								conversationFiles: params.files_dialog || [],
							};

							// Переписка
							let conversationMessagesListParams = {
								origTitle: document.title,
								countMoreMessages: params.countMessages - messages.length,
								kbClosed: params.kbClosed || false,
								isArchive: params.isArchive || 0,
								conversationUserId: params.conversationUserId || null,
								messages: messages || [],
								infoBlock: params.lastDoneOrderWithMessages || {},
								emptyInfoBlock: _.isEmpty(params.lastDoneOrderWithMessages),
								hasOrdersBetweenUsers: !_.isEmpty(params.ordersBetweenUsers),
								isLoaderShow: false,
								isChatSelected: true,
								conversationMessageProps: {
									conversationUserId: params.conversationUserId || null,
									isOrderStageTester: window.isOrderStageTester || false,
									userAvatarColors: params.userAvatarColors || {},
									chatWarningMessages: chatWarningMessages || [],
									chatWarningContent: this.chatWarningContent || '',
									messagesSeleted: [],
								},
								chatPrivateMessageStatusProps: {
									privateMessageStatus: params.privateMessageStatus,
									privateMessageStatuses: params.privateMessageStatuses,
									ordersBetweenUsers: params.ordersBetweenUsers,
									isSupportDialog: params.isSupportDialog,
									userName: params.u.username || '',
								},
							};

							// Переписка
							let chatMessagesListParams = {
								isChatSelected: true,
								chatProps: conversationMessagesListParams,
							};

							// Файлы выбранного диалога
							let conversationFilesParams = {
								files: params.files_dialog || [],
							};
							window.conversationFiles = params.files_dialog || [];

							// Подгружаем данные шапки выбранного диалога
							window.bus.$emit('loadChatConversationHeader', chatConversationHeaderParams);

							// Подгружаем файлы выбранного диалога
							window.bus.$emit('loadConversationFiles', conversationFilesParams);

							// Подгружаем данные переписки выбранного диалога
							window.bus.$emit('loadChatMessagesList', chatMessagesListParams);

							// Форма отправки сообщения / индивидуального предложения / кворка
							this.loadMessageForm(params, messageText, isLoadedByLink);

							this.doubleRaf(() => {
								// Минимальная высота контентной части на мобильных
								chatModule.appMinHeight();

								// Скролл к первому непрочитанному сообщению
								window.bus.$emit('chatScrollToFirstUnread');

								// Инициализация черновика
								window.draft = new Draft({
									mode: 'inbox',
									messageFields: ['#message_body'],
									fileUploaders: window.fileUploaders,
								});
							});
						}
					})
					.catch((error) => {
						this.chatSelected = 0;
					})
					.finally(() => {
						// Меняем состояние чата
						window.bus.$emit('setChatState', false);
					});
			}, 200),
		},
	}
</script>
