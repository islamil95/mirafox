var debounceWaitTime = 2500;

/**
 * Пакетное обновление просмотров услуг биржи проектов
 *
 * @param wantIds
 */
function wantAddViews(wantIds) {
	$.ajax({
		type: 'POST',
		url: '/api/offer/addview',
		data: {
			'wantIds': wantIds
		},
		dataType: 'json',
		success: function() {
			$.each(wantIds, function(index, value) {
				setTimeout(function() {
					$('.js-card-' + value)
						.addClass('js-viewed')
						.find('.query-seen_block')
						.hide()
						.removeClass('hide')
						.fadeIn(150);
				},300 * index);
			});
		}
	});
}

/**
 * Ищем все карточки услуг, которые помещаются на экран целиком
 * и обновляем просмотры этих услуг
 */
function addViewVisibleWantCards() {
	if (Utils.isActiveWindow()) {
		var headerHeight = $('.header').height(); //шапка перекрывает контент, исключаем ее высоту из Viewport
		var visibleWantCards = $('.want-card:not(.js-viewed)').withinviewport({
			sides: 'top bottom',
			top: headerHeight,
		});

		if (visibleWantCards.length > 0) {
			var wantCardIds = [];
			visibleWantCards.each(function (index) {
				var wantCardId = parseInt($(this).data('id'));
				wantCardIds.push(wantCardId);
			});

			wantAddViews(wantCardIds);
		}
	}
}

$(function() {
	/**
	 * Через [debounceWaitTime] миллисекунды после загрузки страницы, остановки скролла или ресайза
	 * проверяем карточки услуг, которые помещаются в экран,
	 * и обновляем просмотры этих услуг
	 *
	 * Событие resize нужно для мобильных девайсов
	 */
	$(window).on('scroll resize load', _.debounce(addViewVisibleWantCards, debounceWaitTime));

	/**
	 * Аналогично для события смены категорий/подкатегорий
	 */
	$(document).on('ajaxSuccess', _.debounce(function(event, xhr, settings) {
		if (settings.url === '/projects') {
			addViewVisibleWantCards();
		}
	}, debounceWaitTime));
});