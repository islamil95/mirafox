require('appJs/bootstrap.js');

Vue.component("quick-faq", require('appJs/quick-faq.vue').default);

const app = new Vue({ 
    el: "#quick-faq"
});