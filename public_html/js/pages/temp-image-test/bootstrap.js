require('appJs/bootstrap.js');

Vue.component("temp-image-test", require('./temp-image-test.vue').default);

const app = new Vue({ 
    el: '#app'
});