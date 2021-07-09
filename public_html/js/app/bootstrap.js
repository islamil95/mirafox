// Lodash
window._ = require("lodash");

// He (кодирование/раскодирование спец. символов html)
window.he = require("he");

// Axios
window.axios = require("axios");

// VueJs
window.Vue = require('vue');

// VueJs шина для обмена сообщениями
window.bus = new Vue();

// Объявление компонента collapse для раскрывающихся блоков
Vue.component("collapse-list", require("appJs/collapse-list.vue").default);