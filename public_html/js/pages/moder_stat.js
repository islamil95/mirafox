jQuery(function ($) {
	$('.js-moder-stat-link').click(showAuthModersStatPopup);

	/**
	 * Функция показа попапа статистики авторизованного модератора
	 * @returns {undefined}
	 */
	function showAuthModersStatPopup() {
		var popupWidth = window.innerHeight / 2 > 1000 ? window.innerHeight / 2 : 1000;
		var popupHeight = window.innerHeight / 1.5 > 400 ? window.innerHeight / 1.5 : 400;
		window.open('/view_auth_moder_stat',
			'Статистика модерации', "width=" + popupWidth + ",\n\
                                        height=" + popupHeight + ",\n\
                                        top=" + ((screen.height - popupHeight) / 2) + ",\n\
                                        left=" + ((screen.width - popupWidth) / 2) + "\n\
                                        resizable=yes,\n\
                                        status=yes,\n\
                                        location=no");
	}
});
