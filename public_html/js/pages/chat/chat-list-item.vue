<template>
	<li class="chat__list-item"
		:class="{'chat__list-item_active': isChatSelected, 'chat__list-item_inactive': this.isActorBlocked && !this.item.is_support}"
		@click="loadConversation">
		<div class="chat__list-avatar">
			<user-avatar :url="item.pic" :username="item.username" :background="item.avatarColor"></user-avatar>
			<i v-if="!item.is_support && item.is_online"
			   class="kwork-user-status chat__user-status kwork-user-status_online js-online-icon"></i>
		</div>
		<div class="chat__list-content">
			<div class="chat__list-info">
				<div class="chat__list-user" :class="{'chat__list-user_bold': isChatUnread}">
					{{ (item.is_support ? t('Служба поддержки') : item.username) }}
				</div>
				<div class="chat__list-date">{{ chatDate }}</div>
			</div>
			<div class="chat__list-message-wrapper" :class="{mr30: isChatUnread}">
				<div v-if="item.is_draft" class="chat__list-draft">
					{{ t('Черновик') }}
				</div>
				<div v-if="hasChatWarning"
					 class="chat__list-message tooltipster"
					 :class="{'chat__list-message_bold': isChatUnread, 'chat__list-message_warning tooltip': hasChatWarning}"
					 data-tooltip-side="bottom"
					 :data-tooltip-text="chatWarningContent"
					 v-html="chatMessage">
				</div>
				<div v-else
					 class="chat__list-message"
					 :class="{'chat__list-message_bold': isChatUnread}"
					 v-html="chatMessage">
				</div>
			</div>
		</div>
		<div v-if="hasNotDeliveredMessages"
			 class="chat__list-informer chat__list-informer_has_warning tooltipster"
			 data-tooltip-side="bottom"
			 :data-tooltip-text="t('Есть неотправленные сообщения')"
		>!</div>
		<div v-else-if="isChatUnread" class="chat__list-informer">
			{{ item.unread_count }}
		</div>
	</li>
</template>

<script>
	import i18nMixin from 'appJs/i18n-mixin'; // Локализация
	import dateMixin from 'appJs/date-mixin'; // Функции с датами
	import userAvatar from 'appJs/user-avatar.vue'; // Аватар

	export default {
		components: {
			'user-avatar': userAvatar,
		},

		mixins: [i18nMixin, dateMixin],

		props: {
			item: {
				type: Object,
				required: true,
			},
			chatSelected: {
				type: Number,
				required: true
			},
			chatWarningContent: {
				type: String,
				required: true
			},
		},

		data() {
			return {
				i18n: {
					en: {
						'Служба поддержки': 'Support Team',
						'Сообщение удалено пользователем': 'The user has deleted this message',
						'Пустое сообщение': 'Empty message',
						'Вы': 'You',
						'Нужен быстрый ответ!': 'Need a quick response!',
						'в течение {{0}}': 'within [{{0}}]',
						'Черновик': 'Draft',
						'Есть неотправленные сообщения': 'There are unsent messages',
					},
				},

				isActorAvailableAtWeekends: false,
				isWeekends: false,
				isActorBlocked: false,
			}
		},

		computed: {
			/**
			 * Проверяет, выбран ли чат
			 * @returns Boolean
			 */
			isChatSelected: function () {
				return this.chatSelected === this.item.USERID;
			},

			/**
			 * Выводит дату последнего сообщение в чате
			 * @returns {*}
			 */
			chatDate: function () {
				return this.getDateWithTime(this.item.time);
			},

			/**
			 * Выводит текст последнего сообщение в чате
			 * @returns {*}
			 */
			chatMessage: function () {
				let message = '';

				if (this.hasChatWarning) {
					message = t('Нужен быстрый ответ!');
				} else if (this.item.status === 'deleted' && this.item.sended === 1) {
					message = t('Сообщение удалено пользователем');
				} else if (!this.item.message) {
					message = t('Пустое сообщение');
				} else {
					if (parseInt(this.item.user_id) !== parseInt(this.item.MSGFROM)) {
						message += t('Вы')+ ': ';
					}
					message += _.truncate(this.item.message.replace(/\\(.)/mg, "$1").replace(/<\/?[a-zA-Z]+>/gi,''), {
						length: 50,
						omission: '',
					});
				}

				return message;
			},

			/**
			 * Проверяет наличие непрочитанных сообщений
			 */
			isChatUnread: function () {
				return this.item.unread_count > 0;
			},

			/**
			 * Проверяет, есть ли недоставленные сообщения
			 * @returns boolean
			 */
			hasNotDeliveredMessages: function () {
				//такого функционала пока нет :)
				return false;
			},

			/**
			 * Проверяет наличие предупреждения о срочном ответе
			 * @returns boolean
			 */
			hasChatWarning: function () {
				return this.item.warning_message_id > 0 && (this.isActorAvailableAtWeekends || (!this.isActorAvailableAtWeekends && !this.isWeekends));
			},

		},

		created: function () {
			this.i18nInit();
			this.isActorAvailableAtWeekends = window.isActorAvailableAtWeekends || false;
			this.isWeekends = window.isWeekends || false;
			this.isActorBlocked = window.userBlocked || false;
		},

		methods: {
			/**
			 * Открывает чат с выбранным собеседником
			 */
			loadConversation: function () {
				if (this.isActorBlocked && !this.item.is_support) {
					return false;
				}

				// Запоминаем позицию скролла списка диалогов на мобильном
				if (jQuery(window).width() < 768) {
					window.chatScrolled = jQuery(window).scrollTop();
				}

				window.bus.$emit('loadConversation', this.item.USERID);
			},
		},
	}
</script>
