/**
 * Уже используется в общем поиске в шапке
 * public_html/js/pages/general-search/bootstrap.js
 *
 * require('appJs/bootstrap.js');
 */

Vue.component("general-search-index", require('./general-search-index.vue').default);

const app = new Vue({ 
    el: '#app'
});