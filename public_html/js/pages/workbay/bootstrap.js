// из-за него на странице чата вохникает ошибка в подключении file-upload
// require('appJs/bootstrap.js');

Vue.component('mobile-menu', require('./mobile-menu.vue').default);
// Мобильное меню
window.appMobileMenu = new Vue({
	el: '#app-mobile-menu',
});
