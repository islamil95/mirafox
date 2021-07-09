require('appJs/bootstrap.js');
require('appJs/live-tabs.js');
require('moduleJs/profile/scrollProfile.js');

Vue.component("interview", require('./interview.vue').default);

const app = new Vue({
	el: '#app'
});