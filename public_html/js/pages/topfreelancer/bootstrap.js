require('appJs/bootstrap.js');

Vue.component("general-search", require('moduleJs/general-search/general-search.vue').default);
// Поиск по кворкам
const generalSearchMobile = new Vue({ 
    el: '#general-search-mobile'
});