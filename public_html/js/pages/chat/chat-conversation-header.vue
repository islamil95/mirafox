<template>
	<div v-if="isChatSelected" class="kwork-conversation__header">
		<transition name="fade">
			<div v-if="!isLoaderShow" class="kwork-conversation__header-column">
				<!-- Иконка "Назад" на мобильных -->
				<div class="kwork-conversation__header-back-link" @click="backToChatList">
					<i class="fa fa-angle-left fa-2x"></i>
				</div>
				<!-- Аватар -->
				<user-avatar v-if="isSupportDialog" :url="userAvatarUrl" :username="userName"></user-avatar>
				<a v-else class="kwork-conversation__header-user-link" :href="userProfileUrl">
					<user-avatar :url="userAvatarUrl" :username="userName"></user-avatar>
					<i v-if="!isSupportDialog && isUserOnline"
					   class="kwork-user-status chat__user-status chat__user-status_mobile kwork-user-status_online js-online-icon"></i>
				</a>
				<div class="kwork-conversation__header-title">
					<!-- Логин -->
					<span v-if="isSupportDialog" class="bold kwork-conversation__header-title-link">{{ t('Cлужба поддержки') }}</span>
					<a v-else :href="userProfileUrl" class="chat__link kwork-conversation__header-title-link">
						<span class="bold">{{ userName }}</span>
						<span v-if="userFullName" class="kwork-conversation__header-full-name">
							(<span>{{ userFullName }}</span>)
						</span>
						<!-- Уровень пользователя -->
						<img v-if="payerLevelBadge"
							 class="tooltipster v-align-m m-hidden ml6"
							 width="20"
							 height="20"
							 :src="cdnImageUrl('/badge/' + payerLevelBadge.id + 's.png')"
							 :data-tooltip-text="payerLevelBadge.name"
							 data-tooltip-side="bottom">
					</a>
					<!-- Онлайн-статус -->
					<div v-if="isUserOnline" class="text-green m-hidden">{{ t('Онлайн') }}</div>
					<div v-else class="text-gray m-hidden">
						{{ t('Оффлайн') }}
						<span v-if="userLastDate" class="user-last-date">
							({{ userLastDate }})
						</span>
					</div>
					<!-- Список заказов на мобильной версии -->
					<div v-if="hasUserOrders" class="chat__tooltip-wrapper m-visible">
						<div class="chat__tooltip-wrapper-title">
							{{ t('Список заказов') + ' (' + (userOrdersCount.worker + userOrdersCount.payer) + ')' }}
							<i class="fa fa-lg fa-angle-down ml5"></i>
						</div>
						<div class="chat__tooltip">
							<div class="chat__tooltip-inner">
								<div class="kwork-conversation__orders-list">
									<a v-if="userOrdersCount.payer > 0"
									   class="kwork-conversation__orders-list-item"
									   :href="'/manage_orders?s=all&filter_user_id=' + userId"
									   @click="changeUserType(1)">
										<span v-html="viewOrdersText('payer')" class="m-hidden"></span>
										<span v-html="viewOrdersText('payer', true)" class="m-visible"></span>
									</a>
									<a v-if="userOrdersCount.worker > 0"
									   class="kwork-conversation__orders-list-item"
									   :href="'/orders?s=all&filter_user_id=' + userId"
									   @click="changeUserType(1)">
										<span v-html="viewOrdersText('worker')" class="m-hidden"></span>
										<span v-html="viewOrdersText('worker', true)" class="m-visible"></span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</transition>
		<transition name="fade">
			<div v-if="!isLoaderShow" class="kwork-conversation__header-column">
				<div class="kwork-conversation__header-user-lists">
					<!-- Список файлов -->
					<div class="chat__tooltip-wrapper" :class="{hidden: !hasConversationFiles}">
						<div class="chat__tooltip-wrapper-title">
							<i class="fl-folder"></i>
							<span class="ml5 m-hidden">{{ t('Файлы') }}</span>
						</div>
						<div class="chat__tooltip chat__tooltip_position_right">
							<div class="chat__tooltip-inner">
								<conversation-files ref="conversationFiles"></conversation-files>
							</div>
						</div>
					</div>
					<!-- Список заказов на десктопной версии -->
					<div v-if="hasUserOrders" class="chat__tooltip-wrapper m-hidden">
						<div class="chat__tooltip-wrapper-title">
							<i class="fl-basket mr5"></i>
							{{ t('Список заказов') + ' (' + (userOrdersCount.worker + userOrdersCount.payer) + ')' }}
						</div>
						<div class="chat__tooltip" :class="{'chat__tooltip_position_right': !hasOfferButton()}">
							<div class="chat__tooltip-inner">
								<div class="kwork-conversation__orders-list">
									<a v-if="userOrdersCount.payer > 0"
									   class="kwork-conversation__orders-list-item"
									   :href="'/manage_orders?s=all&filter_user_id=' + userId"
									   @click="changeUserType(1)">
										<span v-html="viewOrdersText('payer')" class="m-hidden"></span>
										<span v-html="viewOrdersText('payer', true)" class="m-visible"></span>
									</a>
									<a v-if="userOrdersCount.worker > 0"
									   class="kwork-conversation__orders-list-item"
									   :href="'/orders?s=all&filter_user_id=' + userId"
									   @click="changeUserType(1)">
										<span v-html="viewOrdersText('worker')" class="m-hidden"></span>
										<span v-html="viewOrdersText('worker', true)" class="m-visible"></span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Кнопка "Создать заказ" -->
				<div v-if="hasOfferButton()"
					 class="kwork-button kwork-button_theme_green-filled kwork-conversation__header-kwork-button chat__button_offer"
					 :class="{'js-individual-message__popup-link': actorIsPayer}">
					<span class="kwork-button__icon">
						<span class="rouble">Р</span> –
					</span>
					{{ t('Создать заказ') }}
				</div>
			</div>
		</transition>
	</div>
</template>
<script>
	import i18nMixin from 'appJs/i18n-mixin'; // Локализация
	import userAvatar from 'appJs/user-avatar.vue'; // Аватар
	import cdnMixin from "appJs/cdn-mixin.js"; // Приведение относительных ссылок к абсолютным (CDN)

	export default {
		components: {
			'user-avatar': userAvatar,
		},

		mixins: [i18nMixin, cdnMixin],

		data() {
			return {
				i18n: {
					en: {
						'Cлужба поддержки': 'Support team',
						'Онлайн': 'Online',
						'Оффлайн': 'Offline',
						'более месяца': 'more than a month',
						'Список заказов': 'List of orders',
						'<strong>{{0}}</strong> для {{1}}, <strong>как продавца</strong>': '<strong>{{0}}</strong> for {{1}}, <strong>as seller</strong>',
						'<strong>{{0}}</strong> от {{1}}, <strong>как покупателя</strong>': '<strong>{{0}}</strong> from {{1}} <strong>as buyer</strong>',
						'{{0}} <strong>как с продавцом</strong>': '{{0}} <strong>as seller</strong>',
						'{{0}} <strong>как с покупателем</strong>': '{{0}} <strong>as buyer</strong>',
						'Создать заказ': 'Create order',
					},
				},

				userStatusClasses: {
					online : 'kwork-user-status_online',
					offline : 'kwork-user-status_offline',
				},

				isLoaderShow: false,
				isChatSelected: false,
				isSupportDialog: false,

				userId: null,
				userName: '',
				userProfileUrl: '',
				userLastDate: null,
				userOrdersCount: {},
				chatOnlineUsersLocal: [],

				hasConversationFiles: false,
			}
		},

		computed: {
			/**
			 * Проверяет, онлайн ли пользователь
			 * @return boolean
			 */
			isUserOnline: function () {
				return this.chatOnlineUsersLocal.indexOf(this.userId) !== -1;
			},

			/**
			 * Проверяет, были ли заказы между пользователями
			 * @return boolean
			 */
			hasUserOrders: function () {
				return !this.isSupportDialog && (this.userOrdersCount.worker > 0 || this.userOrdersCount.payer > 0);
			},
		},

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();
			this.chatOnlineUsersLocal = window.chatOnlineUsers || [];

			// Событие подгрузки данных шапки выбранного диалога
			window.bus.$on('loadChatConversationHeader', (newValues) => {
				this.loadChatConversationHeader(newValues);
			});

			// Событие смены состояния чата
			window.bus.$on('setChatState', (loaderShowState, isChatSelected) => {
				this.setChatState(loaderShowState, isChatSelected);
			});

			// Событие подгрузки файлов выбранного диалога
			window.bus.$on('hasConversationFiles', (newValue) => {
				this.hasConversationFiles = newValue;
			});
		},

		/**
		 * Отключает прослушку событий перед уничтожением компонента
		 */
		beforeDestroy() {
			window.bus.$off('loadChatConversationHeader');
			window.bus.$off('hasConversationFiles');
			window.bus.$off('setChatState');
		},

		methods: {
			/**
			 * Проверяет, доступна дли кнопки "Сделать заказ" для текущей переписки
			 * @return boolean
			 */
			hasOfferButton: function () {
				return this.privateMessageStatus === true && this.isOrderButtonEnabled && !this.hasOrdersBetweenUsers;
			},

			/**
			 * Выводит текст с количеством общих с пользователем заказов
			 * @param userType
			 * @param isMobile
			 * @return String
			 */
			viewOrdersText: function(userType, isMobile = false) {
				let text, count, resultHtml;

				if (userType === 'worker') {
					text = '<strong>{{0}}</strong> для {{1}}, <strong>как продавца</strong>';
					if (isMobile) {
						text = '{{0}} <strong>как с продавцом</strong>';
					}
					count = this.userOrdersCount.worker;
				} else {
					text = '<strong>{{0}}</strong> от {{1}}, <strong>как покупателя</strong>';
					if (isMobile) {
						text = '{{0}} <strong>как с покупателем</strong>';
					}
					count = this.userOrdersCount.payer;
				}

				resultHtml = t(text, [count + ' ' + declension(count, 'заказ', 'заказа', 'заказов'), this.userName]);
				if (isMobile) {
					resultHtml = t(text, [count + ' ' + declension(count, 'заказ', 'заказа', 'заказов')]);
				}

				return resultHtml;
			},

			/**
			 * Переключает тип пользователя Покупатель / Продавец
			 * @param userType
			 */
			changeUserType: function(userType) {
				axios
					.get('/change_usertype?usertype=' + userType)
					.then(() => {})
					.catch((error) => {})
					.finally(() => {
						if (typeof (yaCounter32983614) !== 'undefined') {
							if (parseInt(userType) === 1) {
								yaCounter32983614.reachGoal('CHANGE-TYPE-ZAKAZCHIK');
							} else {
								yaCounter32983614.reachGoal('CHANGE-TYPE-ISPOLNITEL');
							}
						}
					});
			},

			/**
			 * Запускает эффект переключения списка диалогов / выбранного диалога на мобильных
			 */
			backToChatList: function () {
				window.bus.$emit('setMobileEffects');
			},

			/**
			 * Меняет состояние чата
			 */
			setChatState: function (loaderShowState, isChatSelected = null) {
				this.isLoaderShow = loaderShowState;
				if (isChatSelected !== null) {
					this.isChatSelected = isChatSelected;
				}
			},

			/**
			 * Подгружает данные шапки выбранного диалога
			 * @param newValues
			 */
			loadChatConversationHeader: function(newValues) {
				_.assignIn(this, newValues);
			},
		},
	}
</script>
