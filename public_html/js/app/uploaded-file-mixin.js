export default {
	data () {
		return {}
	},

	methods: {
		uploadedFileIcon: function(filename) {
			let ext = filename.substring(filename.lastIndexOf('.') + 1);
			if (_.indexOf(['doc', 'xls', 'rtf', 'txt', 'docx', 'xlsx'], ext) >= 0) {
				return 'ico-file-doc';
			} else if (_.indexOf(['zip', 'rar'], ext) >= 0) {
				return 'ico-file-zip';
			} else if (_.indexOf(['png', 'jpg', 'gif', 'psd', 'jpeg'], ext) >= 0) {
				return 'ico-file-image';
			} else if (_.indexOf(['mp3', 'wav', 'avi'], ext) >= 0) {
				return 'ico-file-audio';
			} else {
				return 'ico-file-zip';
			}
		}
	},
};
