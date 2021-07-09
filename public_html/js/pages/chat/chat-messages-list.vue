<template>
	<div class="kwork-conversation__list page-conversation">
		<div v-if="isLoaderShow" class="chat__conversation-empty chat__conversation-empty_has_loader t-align-c">
			<chat-loader></chat-loader>
		</div>
		<div v-else-if="!isChatSelected" class="chat__conversation-empty">
			<img src="/images/chat/chat-conversation-empty.svg" height="70" alt="">
			<div class="chat__conversation-empty-title">{{ t('Выберите чат') }}</div>
			<div class="f16">
				{{ t('Кликните по собеседнику слева, чтобы читать и отправлять ему сообщения.') }}
			</div>
		</div>
		<scrolly v-else-if="isDesktop" class="vertical-scrollbar" :passive-scroll="true">
			<scrolly-viewport>
				<conversation-messages-list
						ref="conversationMessagesList"
						:is_archive="false"
						:chat-props=chatProps
						:user-csrf="userCsrf">
				</conversation-messages-list>
			</scrolly-viewport>
			<scrolly-bar axis="y"></scrolly-bar>
		</scrolly>
		<conversation-messages-list v-else
				ref="conversationMessagesList"
				:is_archive="false"
				:chat-props=chatProps
				:user-csrf="userCsrf">
		</conversation-messages-list>
	</div>
</template>
<script>
	import i18nMixin from 'appJs/i18n-mixin'; // Локализация
	import chatLoader from 'moduleJs/chat/chat-loader.vue'; // Иконка загрузки чата
	import { Scrolly, ScrollyViewport, ScrollyBar } from 'appJs/vue-scrolly.min.js'; // Кастомный скроллбар

	export default {
		components: {
			'chat-loader': chatLoader,
			Scrolly,
			ScrollyViewport,
			ScrollyBar,
		},

		mixins: [i18nMixin],
		props: {
			userCsrf: "",
		},

		data() {
			return {
				i18n: {
					en: {
						'Выберите чат': 'Select the chat',
						'Кликните по собеседнику слева, чтобы читать и отправлять ему сообщения.': 'Click on the interlocutor on the left to read and send him messages.',
					},
				},

				isLoaderShow: false,
				isChatSelected: false,
				chatProps: {},
				isDesktop: true,
			};
		},

		created: function() {
			// Инициализировать mixin локализации
			this.i18nInit();

			// Событие смены состояния чата
			window.bus.$on('setChatState', (loaderShowState, isChatSelected) => {
				this.setChatState(loaderShowState, isChatSelected);
			});

			// Событие подгрузки данных переписки выбранного диалога
			window.bus.$on('loadChatMessagesList', (newValues) => {
				this.loadChatMessagesList(newValues);
			});
		},

		mounted: function () {
			this.$nextTick(function() {
				window.addEventListener('resize', this.setDesktopState);
				this.setDesktopState();
			});
		},

		beforeDestroy() {
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
			 * Меняет состояние чата
			 * @param loaderShowState
			 * @param isChatSelected
			 */
			setChatState: function (loaderShowState, isChatSelected = null) {
				this.isLoaderShow = loaderShowState;
				if (isChatSelected !== null) {
					this.isChatSelected = isChatSelected;
				}
			},

			/**
			 * Подгружает данные переписки выбранного диалога
			 * @param newValues
			 */
			loadChatMessagesList (newValues) {
				_.assignIn(this, newValues);
			},
		},
	}
</script>