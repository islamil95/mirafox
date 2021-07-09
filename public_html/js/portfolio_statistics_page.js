// portfolio statistics page
window.portfolioChart = [];

var setPortfolioLineChart = function(chartData) {
	var $$chartObject = {};

	window.portfolioChart[chartData['name']] = c3.generate({
		bindto: chartData['selector'],
		padding: {
			left: 30,
			right: 30
		},
		size: {
			height: 200
		},
		color: {
			pattern: [chartData['lineColor']]
		},
		data: {
			x: 'x',
			columns: chartData['columns'],
			onmouseover: function (d) {
				var circle = $$chartObject.getCircles(d.index);
				circle.style("fill", '#FFF')
					.style("stroke", $$chartObject.color)
					.style("stroke-width", 3);
			},
			onmouseout: function (d) {
				var circle = $$chartObject.getCircles(d.index);
				circle.style("fill", $$chartObject.color)
					.style("stroke-width", 0);
			}
		},
		onrendered: function () {
			$$chartObject = this;
		},
		axis: {
			x: {
				type: 'timeseries',
				tick: {
					fit: false,
					outer: true,
					count: 3,
					format: function (x) {
						return x.getDate() + ' ' + _getMonthName(x.getMonth() + 1);
					}
				}
			}
		},
		point: {
			r: 1.5,
			focus: {
				expand: {
					enabled: true,
					r: 7
				}
			}
		},
		regions: [
			{
				axis: 'x',
				class: 'background-block'
			}
		],
		legend: {
			show: false
		},
		line: {
			connectNull: true
		},
		tooltip: {
			contents: function (d) {
				var count = d[0].value,
					dateFrom = d[0].x,
					days = dateFrom.getDate() < 10 ? '0' + dateFrom.getDate() : dateFrom.getDate(),
					month = dateFrom.getMonth() < 9 ? '0' + (dateFrom.getMonth() + 1) : dateFrom.getMonth() + 1,
					strDate = dateFrom.getFullYear() + '-' + month + '-' + days,
					salesTitle = count || 0;

				if (chartData['name'] == 'portfolioChartViews') {
					salesTitle += ' ' + Utils.declOfNum(count, [t('просмотр'), t('просмотра'), t('просмотров')]);
				} else if (chartData['name'] == 'portfolioChartLike') {
					salesTitle += ' ' + Utils.declOfNum(count, [t('лайк'), t('лайка'), t('лайков')]);
				} else if (chartData['name'] == 'portfolioChartComments') {
					salesTitle += ' ' + Utils.declOfNum(count, [t('комментарий'), t('комментария'), t('комментариев')]);
				}

				var dateTo = new Date(d[0].x);
				dateTo.setDate(dateTo.getDate() + 6);
				var daysTo = dateTo.getDate() < 10 ? '0' + dateTo.getDate() : dateTo.getDate();

				return '' +
					'<div class="chart-tooltip">'
						+ '<span class="chart-tooltip__value">'
							+ salesTitle
						+ '</span><br>'
						+ '<span class="chart-tooltip__date">'
							+ days + ' ' + _getMonthName(dateFrom.getMonth() + 1) + ' '
							+ ' - ' + daysTo + ' ' + _getMonthName(dateTo.getMonth() + 1) + ' ' + dateTo.getFullYear()
						+ '</span>'
					+ '</div>';
			}
		},
	});
};

var _getMonthName = function (month) {
	var months = {
		1: t('января'),
		2: t('февраля'),
		3: t('марта'),
		4: t('апреля'),
		5: t('мая'),
		6: t('июня'),
		7: t('июля'),
		8: t('августа'),
		9: t('сентября'),
		10: t('октября'),
		11: t('ноября'),
		12: t('декабря')
	};

	return months[month];
};

/**
 * Обновление информации на графике страницы статистики "Мои работы"
 * @param {string} chartName Имя графика
 * @param {array} dataColumns Данные для 
 */
var updatePortfolioLineChart = function(chartName, dataColumns) {
	if (!chartName) {
		return false;
	}

	window.portfolioChart[chartName].load({
			columns: dataColumns
	});
};

/**
 * Установить новые значения для статистики
 * @param {object} data Данные для полей (Работ загружено, Просмотров, Лайков)
 */
var setPortfolioStatisticsCount = function(data) {
	var $uploaded = jQuery('#statistics-uploaded');
	var $views = jQuery('#statistics-views');
	var $like = jQuery('#statistics-like');
	var $comments = jQuery('#statistics-comments');

	$uploaded.text(
		Utils.priceFormat(data['uploaded'] || 0)
	);
	$views.text(
		Utils.priceFormat(data['views'] || 0)
	);
	$like.text(
		Utils.priceFormat(data['like'] || 0)
	);
	$comments.text(
		Utils.priceFormat(data['comments'] || 0)
	);

	$uploaded.parent().find('.portfolio-statistics-title').html(Utils.declOfNum(data['uploaded'], [t('Работа загружена'), t('Работы загружено'), t('Работ загружено')]));
	$views.parent().find('.portfolio-statistics-title').html(Utils.declOfNum(data['views'], [t('Просмотр'), t('Просмотра'), t('Просмотров')]));
	$like.parent().find('.portfolio-statistics-title').html(Utils.declOfNum(data['like'], [t('Лайк'), t('Лайка'), t('Лайков')]));
	$comments.parent().find('.portfolio-statistics-title').html(Utils.declOfNum(data['comments'], [t('Комментарий'), t('Комментария'), t('Комментариев')]));
};

var loadPagingData = function(page) {
	var data = {};
	$('.portfolio-statistics-filter select').each(function () {
		data[$(this).attr('name')] = $(this).val();
	});

	var order = $('.portfolio-statistics-order.active');
	data.order_field = order.data('name');
	data.order_direction = order.data('direction');
	data.page = page || 1;

	var esc = encodeURIComponent;
	var query = Object.keys(data)
		.map(function(k) {return esc(k) + '=' + esc(data[k]);})
		.join('&');

	var url = '/portfolio_load_statistics?' + query;
	var history = '/portfolio_statistics?' + query;

	window.history.pushState({urlPath:history}, "", history);

	$.get(url, function (response) {
		if (!response.success) {
			return;
		}
		setPortfolioStatisticsCount({
			uploaded: response.data.uploaded,
			views: response.data.views,
			like: response.data.likes,
			comments: response.data.comments,
		});

		if (response.data.graphs.views) {
			updatePortfolioLineChart('portfolioChartViews', response.data.graphs.views);
		}
		if (response.data.graphs.likes) {
			updatePortfolioLineChart('portfolioChartLike', response.data.graphs.likes);
		}
		if (response.data.graphs.comments) {
			updatePortfolioLineChart('portfolioChartComments', response.data.graphs.comments);
		}
		$('.portfolio-statistics-list-container').html(response.data.html || "");
	});
};

jQuery(function() {

	jQuery('.js-chosen').chosen({
		disable_search: true
	});

	jQuery('body').on('change', '.js-chosen', function (){
		loadPagingData();
	});

	$('.portfolio-statistics-list-container').on('click', '.portfolio-statistics-order', function () {
		var map = {
			desc: 'asc',
			asc: 'desc'
		};
		var newDirection = 'desc';
		if ($(this).hasClass('active')) {
			newDirection = map[$(this).data('direction')];
		}
		$(this).data('direction', newDirection);

		$('.portfolio-statistics-order').removeClass('active');
		$(this).addClass('active');

		loadPagingData();
	});

	// График - Просмотры
	setPortfolioLineChart({
		name: 'portfolioChartViews',
		selector: '.js-portfolio-chart-views',
		lineColor: '#70c4dc',
		columns: [
			['x'],
			['count']
		]
	});

	// График - Лайки
	setPortfolioLineChart({
		name: 'portfolioChartLike',
		selector: '.js-portfolio-chart-like',
		lineColor: '#5fa242',
		columns: [
			['x'],
			['count']
		]
	});
	// График - Комментарии
	setPortfolioLineChart({
		name: 'portfolioChartComments',
		selector: '.js-portfolio-chart-comments',
		lineColor: '#5fa242',
		columns: [
			['x'],
			['count']
		]
	});

	if (graphs !== undefined) {
		if (graphs.views) {
			updatePortfolioLineChart('portfolioChartViews', graphs.views);
		}
		if (graphs.likes) {
			updatePortfolioLineChart('portfolioChartLike', graphs.likes);
		}
		if (graphs.comments) {
			updatePortfolioLineChart('portfolioChartComments', graphs.comments);
		}
	}

	jQuery(window).bind('load', function () {
		var hash = window.location.hash.substr(1);
		if (hash === "review") {
			jQuery('html, body').animate({
				scrollTop: jQuery(".stat-works-view").offset().top - jQuery(".header_top").height()
			}, 200);
		}

	});
});

portfolioCard.setMode('portfolio');
portfolioCard.setAllIds($('.portfolio-all-ids').val());