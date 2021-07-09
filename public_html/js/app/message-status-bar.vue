<template>
	<div class="cm-message-status">
		<div>
			<div v-if="!sended && timerSeconds == 0" class="preloader__ico"></div>
			<div v-if="!sended && timerSeconds == 0" class="preloader-text">{{ t('Отправка...') }}</div>
			<div v-if="!sended && timerSeconds > 0" class="cm-status-warning">
				<div class="cm-warning-icon t-danger-icon"></div>
				<div class="cm-not-sent">{{ t('Не отправлено') }}.</div>
				<div class="cm-send-timer t-loading-dots">{{ sendTimer }}</div>
				<div class="cm-send" @click="sendMessage">{{ t('Отправить') }}</div>
			</div>
		</div>
		<div class="cm-message-status__icon">
			<div v-if="own && sended">
				<div v-if="unread" class="cm-check cm-check-sended" :title="t('Доставлено')"></div>
				<div v-else class="cm-check cm-check-read" :title="t('Прочитано')"></div>
			</div>
		</div>
	</div>
</template>

<script>
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";

	export default {
		mixins: [i18nMixin],
		
		props: {
			showOnIncoming: {
				type: Boolean,
				default: true,
			},

			unread: {
				type: Boolean,
				default: false,
			},

			sended: {
				type: Boolean,
				default: false,
			},

			timerSeconds: {
				type: Number,
				default: -1,
			},

			own: {
				type: Boolean,
				default: false,
			},
		},

		data() {
			return {
				i18n: {
					en: {
						'Отправка...': 'Sending...',
						'Не отправлено': 'Not sent',
						'Повторная отправка через ': 'The message will be resent in ',
						'с': 's',
						'Отправить': 'Send',
						'Доставлено': 'Delivered',
						'Прочитано': 'Read',
					},
				},
			};
		},

		computed: {
			sendTimer: function() {
				return this.t('Повторная отправка через ') + this.timerSeconds + ' ' + this.t('с');
			},
		},

		created: function() {
			this.i18nInit();
		},

		methods: {
			sendMessage: function() {
				window.bus.$emit('forceMessageSend');
			},
		}
	}
</script>