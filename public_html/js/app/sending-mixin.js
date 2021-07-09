export default {
	data() {
		return {
			sendingCount: 0,
		};
	},
	
	created() {
		// Событие, чтобы дочерние компоненты могли менять количество отправлений
		window.bus.$on('addSendingCount', () => {
			this.addSendingCount();
		});	
		window.bus.$on('removeSendingCount', () => {
			this.removeSendingCount();
		});

		$(window).on('beforeunload', () => {
			if (this.sendingCount > 0) {
				return 'Не все сообщения отправлены. Покинуть страницу?';
			}
		});
	},

	methods: {
		addSendingCount() {
			this.sendingCount++;
		},

		removeSendingCount() {
			this.sendingCount--;
			if (this.sendingCount < 0) {
				this.sendingCount = 0;
			}
		},
	},
}
