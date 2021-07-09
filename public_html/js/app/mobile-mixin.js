export default {
	data() {
		return {
			mobileVersion: false,
		};
	},

	created() {
		$(window).on('resize', () => {
			this.updateMobileStatus();
		});
		this.updateMobileStatus();
	},

	methods: {
		updateMobileStatus() {
			this.mobileVersion = jQuery(window).width() < 768;
		},
	},
}
