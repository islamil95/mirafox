(function () {
	if (window.pluso)
		if (typeof window.pluso.start == "function")
			return;
	if (window.ifpluso == undefined) {
		window.ifpluso = 1;
		var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
		s.charset = 'UTF-8';
		s.async = true;
		s.src = ('https:' == window.location.protocol ? 'https' : 'http') + '://share.pluso.ru/pluso-like.js';
		var h = d[g]('body')[0];
		h.appendChild(s);
	}
})();

function loadCards() {
	var container = $('.cusongs');
	var buttonMore = $('.loadKworks');
	buttonMore.addClass('onload');
	$.ajax({
		method: 'post',
		dataType: 'html',
		data: {
			action: 'loadCards',
			offset: buttonMore.data('offset')
		},
		success: function(response) {
			buttonMore.parent().remove();
			container.append(response);
		}
	});
}