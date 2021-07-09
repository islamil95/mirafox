require('appJs/bootstrap.js');

Vue.component('confirm-card-number', require('./confirm-card-number.vue').default);

window.app = new Vue({
	el: '#app',
	data: {
		modalShow: false,
	}
});