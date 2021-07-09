var $view = $('.portfolio-view-data');
var nextpage = $view.data("nextpage");
var items_per_page = $view.data("itemsperpage");
var total = $view.data("total");
window.ad_category = $view.data("CATID");   // required

$(document).ready(() => {
	portfolioCard.setBgLoad(true);
	portfolioCard.setCloseAction((id) => {
		let card = $('.js-portfolio-card[data-id="' + id + '"]');
		if (card.length > 0 && !card.is(':within-viewport')) {
			$('html, body').stop().animate({scrollTop: card.offset().top - $(window).height() / 2}, 1000);
		}
	});

	/**
	 * Скрываем кнопку "Показать еще" на странице списка портфолио в каталоге,
	 * если больше нечего показывать
	 */
	if (nextpage * items_per_page >= total) {
		$('.loadPortfolios').addClass('hidden');
	}

	/**
	 * Устанавливаем режимы модуля portfolioCardModule
	 * 1. Отображение всех портфолио по идентификаторам в модальном окне
	 * 2. Автоматический пересчет идентификаторов портфолио, загруженных на странице
	 */
	portfolioCard.setMode('portfolio');
	portfolioCard.setUpdatingIdsMode();
});