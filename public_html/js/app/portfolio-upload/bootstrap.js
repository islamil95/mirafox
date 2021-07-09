import { SortableCardList } from './sortable-card-list.js';

window.portfolioList = null;

window.portfolioListIsReady = false;

window.initPortfolioList = () => {
	$('.sortable-card-list[data-type="portfolios"]').each((k, v) => {
		let el = $(v);
		if (el.data('sortableList')) {
			return;
		}
		window.portfolioList = new SortableCardList(v);
		el.data('sortableList', window.portfolioList);
	});
}

$(document).ready(() => {
	window.initPortfolioList();
	window.portfolioListIsReady = true;
});
