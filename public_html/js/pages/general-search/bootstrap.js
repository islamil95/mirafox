// Rangy SelectionSaveRestore
window.rangy = require('rangy');
window.rangySelectionSaveRestore = require('rangy/lib/rangy-selectionsaverestore');

// функции разных типов работы с заменой эможи
require('appJs/emoji/emoji-replacements.js');

require('appJs/bootstrap.js');

Vue.component("general-search", require('./general-search.vue').default);

const generalSearch = new Vue({ 
    el: '#general-search'
});

const generalSearchMobile = new Vue({ 
    el: '#general-search-mobile'
});