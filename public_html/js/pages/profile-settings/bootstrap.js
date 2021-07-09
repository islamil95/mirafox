require('appJs/bootstrap.js');

Vue.component("interview-request", require('./interview-request.vue').default);

// удаление аккаунта
Vue.component("delete-account", require('./delete-account.vue').default);

window.app = new Vue({
	el: '#app',
	data() {
		return {
			collapseArr: []
		}
	},
});