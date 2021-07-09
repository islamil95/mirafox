// Страница "Мои работы"
MyPortfolio = {
	page: 1,
	category: 0,
	savePage: function(page) {
		this.page = page;
	},
	getNextPage: function() {
		return this.page + 1;
	},
	getPage: function() {
		return this.page;
	},
	changeCat: function() {
		this.load(this.getCatSelect(), 1);
	},
	getCatSelect: function() {
		return $('select[name="portfolioCategoryFilter"]').val() || 0;
	},
	load: function(category, page) {
		var $content = $('.portfolio-list-collage');
		MyPortfolio.lockBntMore(true);

		$.ajax({
					url: '/portfolioajax',
					type: 'POST',
					data: {
						category: category,
						page: page
			},
			success: function(response) {
				MyPortfolio.lockBntMore(false);

				if (page === 1) {
					$content.html('');
				}
				$content.append(response.data.html);

				MyPortfolio.showBtnMore(response.data.haveNext);

				MyPortfolio.savePage(page);
				if (response.data && response.data.allPortfolioIds) {
					MyPortfolio.initCard(response.data.allPortfolioIds.join(","));
				}
			}
		});
	},
	showBtnMore: function(isShow) {
		var $btn = $('.js-portfolio-more-btn');
		var $parent = $btn.parent();

		if (isShow) {
			$parent.show();
		} else {
			$parent.hide();
		}
	},
	lockBntMore: function(isLock) {
		var $btn = $('.js-portfolio-more-btn');

		if (isLock) {
			$btn.addClass('onload').prop('disabled', true);
		} else {
			$btn.removeClass('onload').prop('disabled', false);
		}
	},
	more: function() {
		this.load(this.getCatSelect(), this.getNextPage());
	},
	initCard: function(Ids) {
		portfolioCard.setMode('portfolio');
		portfolioCard.setAllIds(Ids);
	}
};

$(function() {
	$('.js-portfolio-more-btn').on('click', function() {
		MyPortfolio.more();
	});

	$('select[name="portfolioCategoryFilter"]').on('change', function() {
		MyPortfolio.changeCat();
	});

	MyPortfolio.initCard(
		$('.portfolio-all-ids').val()
	);

	$('[name="portfolioCategoryFilter"]').chosen({
		disable_search: true,
		width: '100%'
	});
});