export default {
	data() {
		return {
			readedChangeDisableTime: 0,
		};
	},

	created() {
		this.updateChangeDisableTime();
		setInterval(() => {
			this.updateChangeDisableTime();
		}, 1000);
	},
	
	methods: {
		updateChangeDisableTime() {
			let currentTime = Utils.getServerTime();
			this.readedChangeDisableTime = currentTime - 60 * 2;
		},
	},
}