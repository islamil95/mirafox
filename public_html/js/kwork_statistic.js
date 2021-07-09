var kworkAnalyticsModule = (function () {

	"use strict";

	function KworkAnalytics(selector, data) {
		this.selector = selector;
		this.$el = $(selector);
		this.data = data;
		this.init();
	};

	KworkAnalytics.prototype.init = function () {
        this._setEvents();
	};

	KworkAnalytics.prototype._setStatistic = function (metricName) {
		var metric = this.data[metricName];
		var type;

		switch (metricName) {
			case 'review_rating':
				type = 'reviews';
				break;
			case 'done_orders_percent':
				type = 'cancellation';
				break;
			default:
				return;
		}

		if (!metric.hasOwnProperty('user_percent')) {
			return;
		}

		this._generateDonutChart(type);
	};

    KworkAnalytics.prototype._setEvents = function () {
        var that = this;
        $.tooltipster.on('ready', function(event) {
            var $tooltip = $(event.instance.elementOrigin());
            if (that.$el.data('kworkId') === $tooltip.data('kworkId')) {
                var $dataMetric = $tooltip.data('metric');
                that._setStatistic($dataMetric);
                $tooltip.tooltipster('reposition');

                // Вложенный тултип
                var nestedTooltips = event.instance.content().find('.tooltipster-nested:not(.tooltipstered)');
                if (nestedTooltips !== undefined && nestedTooltips.length) {
                    nestedTooltips.each(function (index, el) {
                        var nestedTooltip = $(el);
                        var contentSelector = nestedTooltip.data('tooltipContent');
                        if (contentSelector) {
                        	// Переопределение конфига для вложенного туотипа
                        	var config = Object.assign({}, TOOLTIP_CONFIG);
                            config.functionReady = function(instanceNested, helperNested) {
                                var stop_close = false;
                                $(helperNested.tooltip).on("mouseenter", function() {
                                    stop_close = true;
                                });
                                instanceNested.on('close', function() {
                                    stop_close = false;
                                });
                                event.instance.on('close', function(event) {
                                    if (stop_close)
                                        event.stop();
                                });
                            };
                            config.content = $(contentSelector).html();
                            config.trigger = 'hover';
                            delete config.functionBefore;

                            $(el).tooltipster(config);
                        }
                    });
                }
            }

        });
    };

	KworkAnalytics.prototype._generateDonutChart = function (type) {
		var columns, selector, colorPattern;
		switch (type) {
			case 'reviews':
				selector = '.tooltipster-base .js-analytics__donut--reviews';
				columns = this._getDonutReviewValues();
				colorPattern = {
					'Положительных': '#66AE47',
					'Повторные заказы': '#66AE47',
					'Отрицательных': '#F5594D',
					'Без отзыва': '#E7E7E7',
					'Нет заказов': '#E7E7E7',
					'Positive': '#66AE47',
					'Repeated orders': '#66AE47',
					'Negative': '#F5594D',
					'No feedback': '#E7E7E7',
					'No orders!': '#E7E7E7',
				};
				break;
			case 'cancellation':
				selector = '.tooltipster-base .js-analytics__donut--cancellation';
				columns = this._getDonutCancellationValues();
				colorPattern = {
					'Выполнено': '#66AE47',
					'Отказы': '#F5594D',
					'Автоотмены': '#F5594D',
					'Просрочка с отменой': '#F5594D',
					'Нет заказов': '#E7E7E7',
					'Completed': '#66AE47',
					'Refusals': '#F5594D',
					'Auto Cancel': '#F5594D',
					'Overdue with cancellation': '#F5594D',
					'No orders!': '#E7E7E7',
				};
				break;
		}

		var chart = c3.generate({
			bindto: selector,
			size: {
				height: 160,
				width: 160
			},
			tooltip: {
				show: false
			},
			legend: {
				show: false
			},
			data: {
				order: null,
				columns: columns,
				colors: colorPattern,
				type: 'donut',
				onmouseover: function (data) {
					if (data.value == -1) {
						return;
					}

					var percent = Math.round(data.ratio * 1000) / 10;
					var label = d3.select(selector + ' text.c3-chart-arcs-title');
					label.html('');
					label.insert('tspan')
						.text(percent + '%').attr('dy', -5).attr('x', 0).attr('fill', '#333')
						.attr('class', 'donut-chart-title-percent');

					if (data.name == 'Повторные заказы') {
						label.insert('tspan')
							.text(t('Без отзыва,')).attr('dy', 15).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
						label.insert('tspan')
							.text(t('повторные')).attr('dy', 10).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
						label.insert('tspan')
							.text(t('заказы')).attr('dy', 10).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
					} else if (data.name == 'Просрочка с отменой') {
						label.insert('tspan')
							.text(t('Просрочка')).attr('dy', 15).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
						label.insert('tspan')
							.text(t('с отменой')).attr('dy', 10).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
					} else {
						label.insert('tspan')
							.text(data.name).attr('dy', 15).attr('x', 0).attr('fill', '#333')
							.attr('class', 'donut-chart-title-name');
					}
				}
			},
			donut: {
				title: '',
				label: {
					show: false
				}
			},
			onrendered: function () {
				var data = {};
				var name = type == 'reviews' ? t('Положительных') : t('Выполнено');
				this.pie(this.filterTargetsToShow(this.data.targets)).forEach(function (t) {
					if (t.data.id === name) {
						data = t;
					}
				});

				var percent = Math.round(data.value * 10) / 10;
				var label = d3.select(selector + ' text.c3-chart-arcs-title');

				label.html('');
				label.insert('tspan')
					.text(percent + '%').attr('dy', -5).attr('x', 0).attr('fill', '#333')
					.attr('class', 'donut-chart-title-percent');

				label.insert('tspan')
					.text(name).attr('dy', 15).attr('x', 0).attr('fill', '#333')
					.attr('class', 'donut-chart-title-name');
			}
		});
	};

	KworkAnalytics.prototype._getDonutReviewValues = function () {
		var metrics = this.data['review_rating'];
		var good = 0, bad = 0, empty = 0, repeatWOReview = 0, noReviews = 0;

		if (metrics.hasOwnProperty('review_good')) {
			good = metrics['review_good'];
		}

		if (metrics.hasOwnProperty('repeat_sales_wo_reviews')) {
			repeatWOReview = metrics['repeat_sales_wo_reviews'];
		}

		if (metrics.hasOwnProperty('review_bad')) {
			bad = metrics['review_bad'];
		}

		if (metrics.hasOwnProperty('review_empty')) {
			empty = metrics['review_empty'];
		}

		if (good + bad + empty + repeatWOReview == 0) {
			noReviews = -1;
		}

		var total = metrics['total'];

		var goodColumns = [
			[t('Положительных'), good * 100 / total],
			[t('Повторные заказы'), repeatWOReview * 100 / total]
		];
		goodColumns.sort(this._sortColumns);

		var badColumns = [
			[t('Отрицательных'), bad * 100 / total],
			[t('Без отзыва'), empty * 100 / total]
		];
		badColumns.sort(this._sortColumns);

		noReviews = [[t('Нет заказов'), noReviews]];
		$.merge(badColumns, noReviews);

		return $.merge(goodColumns, badColumns, [t('Нет заказов'), noReviews]);
	};

	KworkAnalytics.prototype._getDonutCancellationValues = function () {
		var metrics = this.data['done_orders_percent'];
		var done = 0, cancellation = 0, autoCancel = 0, expirationCancel = 0, noOrders = 0;

		if (metrics.hasOwnProperty('done_orders')) {
			done = metrics['done_orders'];
		}

		if (metrics.hasOwnProperty('cancellation')) {
			cancellation = metrics['cancellation'];
		}

		if (metrics.hasOwnProperty('auto_cancel')) {
			autoCancel = metrics['auto_cancel'];
		}

		if (metrics.hasOwnProperty('expiration_cancel')) {
			expirationCancel = metrics['expiration_cancel'];
		}

		if (done + cancellation + autoCancel + expirationCancel == 0) {
			noOrders = -1;
		}

		var total = metrics['total'];

		var goodColumns = [
			[t('Выполнено'), done * 100 / total]
		];
		goodColumns.sort(this._sortColumns);

		var badColumns = [
			[t('Отказы'), cancellation * 100 / total],
			[t('Автоотмены'), autoCancel * 100 / total],
			[t('Просрочка с отменой'), expirationCancel * 100 / total]
		];
		badColumns.sort(this._sortColumns);

		noOrders = [[t('Нет заказов'), noOrders]];
		$.merge(badColumns, noOrders);

		return $.merge(goodColumns, badColumns, [[t('Нет заказов'), noOrders]]);
	};

	KworkAnalytics.prototype._sortColumns = function (a, b) {
		return a[1] < b[1] ? 1 : -1;
	};

	return {
		init: function (userData) {
            if (userData) {
                $('.js-kwork-analytics').each(function () {
                    var kworkId = $(this).data('kworkId');
                    var kworkData = {
                        review_rating: userData['review_rating'][kworkId],
                        done_orders_percent: userData['done_orders_percent'][kworkId],
                    };
                    new KworkAnalytics('.js-kwork-analytics-' + kworkId, kworkData);
                });
            }
		}
	}

})();
