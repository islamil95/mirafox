require('oldJs/popupAllowConversations.js');
require('appJs/offer-individual.js');

import mobileMixin from 'appJs/mobile-mixin.js';

window.appFiles = new Vue({
	el: '#app-files',

	mixins: [mobileMixin],

	data: {
		files: [],
		ready: true,
		secondUserId: parseInt(window.conversationUserId),
		dragNDropBlocked: false,
	},

	computed: {
		desktopDragNDropEnable() {
			return !this.dragNDropBlocked;
		}
	},
	
	methods: {
		onChange(state) {
			if (window.draft) {
				window.draft.activateByInput({
					uploader: this,
				});
			}
			this.ready = state;
			if (window.sendForm) {
				window.sendForm.updateUploadButton(window.appFiles.$refs.fileUploader.isUploadAviable);
			}
			toggleSubmitButton();
		},
	},
});

if (!window.isChat) {
window.appFilesMobile = new Vue({
	el: '#app-files-mobile',
	data: {
		files: [],
		ready: true,
		secondUserId: parseInt(window.conversationUserId),
		desktopUploader: (window.appFiles ? window.appFiles.$refs.fileUploader : null),
	},
	methods: {
		onChange: function(state) {
			if (window.draft) {
				window.draft.activateByInput({
					uploader: this,
				});
			}
			this.ready = state;
			toggleSubmitButton();
		},
	},
});
}

window.fileUploaders = [window.appFiles, window.appFilesMobile];
