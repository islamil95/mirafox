var Youtube = (function () {
	'use strict';

	var video, results;

	var getThumb = function (url, size) {
			if (url === null) {
					return '';
			}
			size    = (size === null) ? 'big' : size;
			results = url.match('[\\?&]v=([^&#]*)');
			video   = (results === null) ? url : results[1];

			if (size === 'small') {
					return 'http://img.youtube.com/vi/' + video + '/2.jpg';
			}
			if (size === 'max') {
				return 'http://img.youtube.com/vi/' + video + '/maxresdefault.jpg';
			}
			return 'http://img.youtube.com/vi/' + video + '/0.jpg';
	};

	var isYoutubeUrl = function (url) {
		var p = /^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
		if (url && url.match(p)) {
			return true;
		}
		return false;
	}

	return {
			thumb: getThumb,
			isUrl: isYoutubeUrl
	};
}());