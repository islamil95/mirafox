export default {
	data() {
		return {
			timerSeconds: -1,
		};
	},
	
	methods: {
		setTimer: function(count) {
			this.timerSeconds = count;
		},
	},
}