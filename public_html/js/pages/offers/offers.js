$(document).ready(() => {

	// Переход к нужной карточке и разворачивание её
	let hash = $(location).attr('hash');
	if (hash.startsWith('#project')) {
		hash = hash.substr(8);
		let card = $('.js-card-' + hash);
		if (card.length < 1) {
			return;
		}
		let parent = card.parent().find('.js-offer-container');
		if (parent.length > 0) {
			parent.show();
		}
		$(window).scrollTop(card.offset().top - $('body .header').height());
	}
	
});