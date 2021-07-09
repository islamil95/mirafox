var analyticsModule = (function () {

	d3.selection.prototype.first = function () {
		return d3.select(this[0][0]);
	};
	d3.selection.prototype.last = function () {
		var last = this.size() - 1;
		return d3.select(this[0][last]);
	};

	var _dataCache = {
		'statistic': {},
		'line-charts': {},
		'levels': {}
	};

	var $_preloader,
		_isNewUser,
		_period;

	var LEVEL_NAME = {
		excellent: t('Отлично'),
		good: t('Хорошо'),
		satisfactorily: t('Средне'),
		bad: t('Плохо'),
		bad_time: t('Медленно'),
		excellent_time: t('Очень быстро')
	};

	var _getDonutReviewValues = function (period) {
		var metrics = _dataCache['statistic'][period];
		var good = 0, bad = 0, empty = 0, repeatWOReview = 0, noReviews = 0;

		if (metrics.hasOwnProperty('review_good')) {
			good = metrics['review_good'].value ^ 0;
		}

		if (metrics.hasOwnProperty('repeat_sales_wo_reviews')) {
			repeatWOReview = metrics['repeat_sales_wo_reviews'].value ^ 0;
		}

		if (metrics.hasOwnProperty('review_bad')) {
			bad = metrics['review_bad'].value ^ 0;
		}

		if (metrics.hasOwnProperty('review_empty')) {
			empty = metrics['review_empty'].value ^ 0;
		}

		if (good + bad + empty + repeatWOReview == 0) {
			noReviews = -1;
		}

		var total = good + bad + empty + repeatWOReview;

		var goodColumns = [
			[t('Положительных'), good * 100 / total],
			[t('Повторные заказы'), repeatWOReview * 100 / total]
		];
		goodColumns.sort(_sortColumns);

		var badColumns = [
			[t('Отрицательных'), bad * 100 / total],
			[t('Без отзыва'), empty * 100 / total]
		];
		badColumns.sort(_sortColumns);

		noReviews = [[t('Нет заказов'), noReviews]];
		$.merge(badColumns, noReviews);

		return $.merge(goodColumns, badColumns, [t('Нет заказов'), noReviews]);
	};

	var _sortColumns = function (a, b) {
		return a[1] < b[1] ? 1 : -1;
	};

	var _getDonutCancellationValues = function (period) {
		var metrics = _dataCache['statistic'][period];
		var done = 0, cancellation = 0, autoCancel = 0, expirationCancel = 0, noOrders = 0;

		if (metrics.hasOwnProperty('done_orders')) {
			done = metrics['done_orders'].value ^ 0;
		}

		if (metrics.hasOwnProperty('cancellation')) {
			cancellation = metrics['cancellation'].value ^ 0;
		}

		if (metrics.hasOwnProperty('auto_cancel')) {
			autoCancel = metrics['auto_cancel'].value ^ 0;
		}

		if (metrics.hasOwnProperty('expiration_cancel')) {
			expirationCancel = metrics['expiration_cancel'].value ^ 0;
		}

		if (done + cancellation + autoCancel + expirationCancel == 0) {
			noOrders = -1;
		}

		var total = done + cancellation + autoCancel + expirationCancel + noOrders;

		var goodColumns = [
			[t('Выполнено'), done * 100 / total]
		];
		goodColumns.sort(_sortColumns);

		var badColumns = [
			[t('Отказы'), cancellation * 100 / total],
			[t('Автоотмены'), autoCancel * 100 / total],
			[t('Просрочка с отменой'), expirationCancel * 100 / total]
		];
		badColumns.sort(_sortColumns);

		noOrders = [[t('Нет заказов'), noOrders]];
		$.merge(badColumns, noOrders);

		return $.merge(goodColumns, badColumns, [[t('Нет заказов'), noOrders]]);
	};

	var _generateDonutChart = function (period, type) {
		var columns, selector, colorPattern;
		switch (type) {
			case 'reviews':
				selector = '.js-analytics__donut--reviews';
				columns = _getDonutReviewValues(period);
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
				selector = '.js-analytics__donut--cancellation';
				columns = _getDonutCancellationValues(period);
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

	var _setLineChart = function (period, lineType) {
		var lineData = _dataCache['line-charts'][period],
			selector, chartData, lineColor;

		var dateBase = Object.keys(lineData['base-sales'])[0];
		var dateExtra = Object.keys(lineData['extra-sales'])[0];

		var minTime = new Date(dateExtra ? dateExtra : Date.now()).getTime();
		if (typeof dateBase != 'undefined') {
			minTime = Math.min(new Date(dateBase).getTime(), minTime);
		}

		switch (lineType) {
			case 'base-sales':
				selector = '.js-line-chart--base-sales';
				lineColor = '#70c4dc';
				chartData = lineData['base-sales'];
				break;
			case 'extra-sales':
				selector = '.js-line-chart--extra-sales';
				lineColor = '#5fa242';
				chartData = lineData['extra-sales'];
				break;
		}

		_generateLineChart(chartData, selector, lineColor, minTime);
	};

	var _generateLineChart = function (chartData, selector, lineColor, minTime) {
		var $$chartObject = {};

		var line = ['data'];
		var keys = Object.keys(chartData);
		var timeValues = _getLineChartTimeValues(minTime, keys.length);
		for (var i = 0; i < keys.length; i++) {
			var item = chartData[keys[i]];
			line.push(item.sum);
		}

		keys.unshift('x');

		var chart = c3.generate({
			bindto: selector,
			data: {
				x: 'x',
				columns: [
					keys,
					line
				],
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
			size: {
				height: 200
			},
			color: {
				pattern: [lineColor]
			},
			tooltip: {
				contents: function (d) {
					var dateFrom = d[0].x,
						days = dateFrom.getDate() < 10 ? '0' + dateFrom.getDate() : dateFrom.getDate(),
						month = dateFrom.getMonth() < 9 ? '0' + (dateFrom.getMonth() + 1) : dateFrom.getMonth() + 1,
						strDate = dateFrom.getFullYear() + '-' + month + '-' + days,
						salesTitle = Utils.declOfNum(chartData[strDate].count, [t('продажа'), t('продажи'), t('продаж')]);

					if (lang == 'en') {
						salesTitle = Utils.declOfNumEn(chartData[strDate].count, [t('продажа'), t('продажи')]);
					}

					var dateTo = new Date(d[0].x);
					dateTo.setDate(dateTo.getDate() + 6);
					var daysTo = dateTo.getDate() < 10 ? '0' + dateTo.getDate() : dateTo.getDate();

					if (!chartData.hasOwnProperty(strDate)) {
						return;
					}
					var currencyString = Utils.numberFormat(chartData[strDate].sum, 0, '.', ' ');
					if(actor_lang == 'ru') {
						currencyString += ' <span class="rouble">Р</span>';
					}else{
						currencyString = '<span class="usd">$</span>' + currencyString;
					}

					return '' +
						'<div class="chart-tooltip">' +
						'   <span class="chart-tooltip__value">'
						+ Utils.numberFormat(chartData[strDate].count, 0, '.', ' ') + ' ' + salesTitle + ', ' +
						currencyString + '</span><br>' +
						'   <span class="chart-tooltip__date">' +
						days + ' ' + _getMonthName(dateFrom.getMonth() + 1) + ' ' +
						' - ' +
						daysTo + ' ' + _getMonthName(dateTo.getMonth() + 1) + ' ' + dateTo.getFullYear() +
						'   </span>' +
						'</div>';
				}
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
						},
						values: timeValues.values
					},
					padding: {
						left: timeValues.padding.left,
						right: timeValues.padding.right
					},
					height: 35,
					min: timeValues.min,
					max: timeValues.max
				},
				y: {
					min: 0
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
				{axis: 'x', class: 'background-block'}
			],
			legend: {
				show: false
			},
			line: {
				connectNull: true
			},
			onrendered: function () {
				$$chartObject = this;

				var ticks = $$chartObject.axes.x.selectAll('.tick text tspan');
				ticks.last().attr('dx', '-35');
				var lastDate = new Date(timeValues.max);
				lastDate.setDate(lastDate.getDate() + 6);
				ticks.last().text(lastDate.getDate() + ' ' + _getMonthName(lastDate.getMonth() + 1));
				//ticks.first().attr('dx', '-35');
			}
		});
	};

	var _getFullWeekMonday = function (date) {
		date = new Date(date);

		var day = date.getDay(),
			diff = date.getDate() + (day == 1 ? 0 : day == 0 ? 1 : 7 - day + 1);

		var newDate = new Date(date.setDate(diff));
		newDate.setHours(0);
		newDate.setMinutes(0);
		newDate.setMilliseconds(0);
		return newDate;
	};

	var _getLastWeekDay = function (date, dayNum, decrease) {
		date = new Date(date);

		var day = date.getDay(),
			diff;

		if (decrease == true) {
			if (dayNum == 0) {
				diff = date.getDate() - day;
			} else {
				diff = date.getDate() - day - (day == 0 ? 0 : 7 - dayNum);
			}
		} else {
			if (dayNum == 0) {
				diff = date.getDate() + (day == 0 ? 0 : 7 - dayNum);
			} else {
				diff = date.getDate() + 7 - dayNum;
			}
		}

		var newDate = new Date(date.setDate(diff));
		newDate.setHours(0);
		newDate.setMinutes(0);
		newDate.setMilliseconds(0);
		return newDate;
	};

	var _getLineChartTimeValues = function (minTime, valuesCount) {
		var result = {};
		var currentDate = new Date();

		if (valuesCount == 1) {
			currentDate.setDate(currentDate.getDate() + 1);
			result.max = _getLastWeekDay(currentDate, 0, true).getTime();
		} else {
			result.max = _getLastWeekDay(currentDate, 1, true).getTime();
		}

		if (_period > 0) {
			if(_period == 1) {
				currentDate.setDate(currentDate.getDate() - 4*7); //Если период - месяц, показывать всегда предыдущие четыре недели
			}
			else {
				currentDate.setMonth(currentDate.getMonth() - _period);
			}
			currentDate.setDate(currentDate.getDate() - 7);
			result.min = _getFullWeekMonday(currentDate).getTime();
		} else {
			result.min = minTime;
		}
		result.values = [
			result.min,
			result.min + (result.max - result.min) / 2,
			result.max
		];

		var rightMargin = (result.max - result.min) * 3 / 100;
		if (valuesCount == 1) {
			rightMargin += 1000 * 60 * 60 * 24;
		}

		var extLeft = 0;
		if (_period == 0) {
			extLeft = 180 * 60 * 60 * 24;
		}
		result.padding = {
			left: (result.max - result.min) * 3 / 100 + extLeft,
			right: rightMargin
		};

		return result;
	};

	var _setPeriod = function () {
		_showPreloader();
		_period = $(this).val().toString();
		if (_dataCache['statistic'].hasOwnProperty(_period)) {
			_setStatistic();
			return;
		}
		$('.js-preloader--analytics').show();
		$.get('/api/statistic/getstatistic?period=' + _period, function (response) {
			if (response.success == true) {
				_dataCache['statistic'][_period] = response.data.statistic;
				_dataCache['line-charts'][_period] = response.data['line-charts'];

				if (_period == 0) {
					_dataCache['total-sum'] = response.data['total_sum'];
				}

				_setStatistic();
			}
		}, 'json');
	};

	var _setEvents = function () {
		$('.js-analytics-period').on('change', _setPeriod);

		$.tooltipster.on('ready', function(event) {
			var $tooltip = $(event.instance.elementOrigin());
			var $tooltipContent = $('.tooltipster-content');

			$tooltipContent.find('.js-analytics-tooltip__period').text(_getTextPeriod(_period));

			$tooltipContent.find('.js-analytics-tooltip__hint').each(function () {
				var metricName = $(this).data('metric');
				var metric = _getMetricValue(metricName);

				var text = _fillTooltipValueText(metricName, metric);
				$(this).html(text);

				if (typeof metric == 'undefined' || metric['value_def'] == 0) {
					$('.js-hide-if-null--' + metricName).hide();
				} else {
					$('.js-hide-if-null--' + metricName).show();
				}
			});

			var cancellation = 0, autoCancel = 0, expirationCancel = 0;

			if (typeof _getMetricValue('cancellation') == 'object' && _getMetricValue('cancellation').hasOwnProperty('value_def')) {
				cancellation = _getMetricValue('cancellation').value_def ^ 0
			}
			if (typeof _getMetricValue('auto_cancel') == 'object' && _getMetricValue('auto_cancel').hasOwnProperty('value_def')) {
				autoCancel = _getMetricValue('auto_cancel').value_def ^ 0;
			}
			if (typeof _getMetricValue('expiration_cancel') == 'object' && _getMetricValue('expiration_cancel').hasOwnProperty('value_def')) {
				expirationCancel = _getMetricValue('expiration_cancel').value_def ^ 0;
			}

			if (cancellation == 0 && autoCancel == 0 && expirationCancel == 0) {
				$('.js-hide-if-null--metric-cancel').hide();
			}

			$tooltip.tooltipster('reposition');
		});

		$(document).on('show.tooltip', '.js-analytics-admin-tooltip', function () {
			var that = $(this),
				$tooltip = that.data('tooltip').tooltip;

			if (!$tooltip.text().length) {

				var $preloader = $('<div class="preloader__ico preloader__ico_tooltip"></div>');
				that.tooltip('setContent', $preloader);

				$.get('/api/statistic/getlastorders?type=' + $(this).data('type') + '&graphic_type=' + $(this).data('graphicType'), function (response) {
					if (response) {
						var orderLinks = [];
						for (var i = 0; i < response.length; i++) {
							orderLinks.push('<a href="/track?id=' + response[i] + '">' + response[i] + '</a>');
						}

						var $container = $('<div style="max-width: 200px;">');

						orderLinks = orderLinks.length ? orderLinks.join(', ') : 'Таких заказов нет';
						that.tooltip('setContent', $container.append(orderLinks));
					} else {
						that.tooltip('setContent', '<span>Нет данных</span>');
					}
				}, 'json');
			}
		});
	};

	var _setPlural = function (value, $block) {
		var pluralForms = $block.data('plural');
		if (pluralForms == null) {
			return;
		}

		$block.text(Utils.declOfNum(value, pluralForms));
	};

	var _clearLevels = function ($el) {
		$el.removeClass('analytics-value--excellent analytics-value--good analytics-value--bad analytics-value--satisfactorily')
			.removeClass('analytics-value-bg--excellent analytics-value-bg--good analytics-value-bg--bad analytics-value-bg--satisfactorily')
	};

	var _addLevel = function ($el, level, type) {
		switch (type) {
			case 'text':
				if (($el.hasClass('js-metric__level-name--work_speed') || $el.hasClass('js-metric__level-name--reaction_time') || $el.hasClass('js-metric__level-name--response_time'))
					&& (level == 'excellent' || level == 'bad')) {
					level += '_time';
				}
				$el.text(LEVEL_NAME[level]);
				break;
			case 'background':
				$el.addClass(_getLevelClass(type, level));
				break;
			case 'color':
			default:
				$el.addClass(_getLevelClass(type, level));
				break;
		}
	};

	var _getLevelClass = function (type, level) {
		switch (type) {
			case 'background':
				return 'analytics-value-bg--' + level;
			case 'color':
			default:
				return 'analytics-value--' + level;
		}
	};

	var _getMetricValue = function (metricName) {
		return _dataCache['statistic'][_period][metricName];
	};

	var _getTextPeriod = function (period) {
		var periods = {
			0: t('всё время'),
			1: t('месяц'),
			3: t('квартал'),
			12: t('год')
		};

		return periods[period];
	};

	var _setStatistic = function () {
		var keys = Object.keys(_dataCache['statistic'][_period]),
			periodValues = _dataCache['statistic'][_period];

		$('.js-analytics-tooltip__period').text(_getTextPeriod(_period));

		if (_isNewUser.default) {
			$('.js-metric__level-name').hide();
		}

		if (_isNewUser['responsibility']) {
			$('.js-metric__level-name--done_orders_percent').hide();
		}
		if (_isNewUser['reviews']) {
			$('.js-metric__level-name--review_rating').hide();
		}

		for (var i = 0; i < keys.length; i++) {
			var metricName = keys[i],
				metric = periodValues[keys[i]];
			$('.js-metric__value--' + metricName).html(metric.value);
			_setPlural(metric.value, $('.js-metric__title--' + metricName));

			var $levelBlock = $('.js-metric__level--' + metricName);
			_clearLevels($levelBlock);
			if (!_isNewUser.default) {
				_addLevel($levelBlock, metric.level, 'color');
			}
			_addLevel($('.js-metric__level-name--' + metricName), metric.level, 'text');

			$levelBlock.removeClass('hidden');
			if (parseInt(metric.value) < 1) {
				$levelBlock.addClass('hidden');
			}

			var $bgBlock = $('.js-metric__level-bg--' + metricName);
			_clearLevels($bgBlock);
			_addLevel($bgBlock, metric.level, 'background');

			$('.js-metric__percent--' + metricName).width(metric.percent + '%');

			if (metricName == 'sales_count') {
				if (metric.value == 0) {
					$('.js-analytics__block--lines').hide();
				} else {
					$('.js-analytics__block--lines').show();
				}
			} else if (metricName == 'views') {
				if(metric.period > 0)
					$('.js-analytics-tooltip__period--views').text(_getTextPeriod(metric.period));
				else
					$('.js-analytics-tooltip__period--views').text(_getTextPeriod(12));
			} else if (metricName == 'conversion') {
				if(metric.period > 0)
					$('.js-analytics-tooltip__period--conversion').text(_getTextPeriod(metric.period));
				else
					$('.js-analytics-tooltip__period--conversion').text(_getTextPeriod(12));
			}
		}

		if (typeof _getMetricValue('work_speed') != 'object' || (_getMetricValue('work_speed').value_def ^ 0) == 0) {
			$('.js-hide-if-null--work_speed').hide();
		} else {
			$('.js-hide-if-null--work_speed').show();
		}

		if (typeof _getMetricValue('reaction_time') != 'object' || (_getMetricValue('reaction_time').value_def ^ 0) == 0) {
			$('.js-hide-if-null--reaction_time').hide();
		} else {
			$('.js-hide-if-null--reaction_time').show();
		}

		if (typeof _getMetricValue('response_time') != 'object' || (_getMetricValue('response_time').value_def ^ 0) == 0) {
			$('.js-hide-if-null--response_time').hide();
		} else {
			$('.js-hide-if-null--response_time').show();
		}

		_setLineChart(_period, 'base-sales');
		_setLineChart(_period, 'extra-sales');

		_generateDonutChart(_period, 'reviews');
		_generateDonutChart(_period, 'cancellation');

		$('.total-sum-block__value').html(_dataCache['total-sum']);
		_setPlural(_dataCache['total_sum'], $('.total-sum__currency'));

		_hidePreloader();
	};

	var _fillTooltipValueText = function (metricName, metric) {
		switch (metricName) {
			case 'done_orders_percent':
				if (_isNewUser['responsibility']) {
					return '';
				}
				break;
			case 'review_rating':
				if (_isNewUser['reviews']) {
					return '';
				}
				break;
			default:
				if (_isNewUser['default']) {
					return '';
				}
		}

		var textParams = _getTooltipValueText(metricName),
			lessMore, percent;

		if (typeof metric == 'undefined') {
			lessMore = textParams.bad;
			percent = 99;
			metric = {
				level: 'bad',
				value: 0,
				'value_def': 0
			};
		} else if (metric['user_percent'].good < 40) {
			lessMore = textParams.bad;
			percent = metric['user_percent'].bad;
		} else {
			lessMore = textParams.good;
			percent = metric['user_percent'].good;
			if (metricName == 'done_orders_percent' && percent == 100) {
				lessMore = textParams.good100;
			}
		}

		var replaceParams = {
			'bad-good': lessMore,
			'value': '<span class="bold ' + _getLevelClass('color', metric.level) + '">' + metric.value + '</span>',
			'user_percent': '<span class="bold ' + _getLevelClass('color', metric.level) + '">' + percent + '%' + '</span>',
			'period': _getTextPeriod(_period),
			'hidden': metric['value_def'] == 0 ? 'hidden' : ''
		};

		var text = textParams.text;
		if (textParams.hasOwnProperty('zero') && metric['value'] == 0) {
			text = textParams.zero;
		}

		return text.replace(/({{.*?}})/g, function (str, p) {
			var param = p.replace(/([{}]*)?/g, '');
			return replaceParams[param];
		});
	};

	var _getTooltipValueText = function (metricName) {
		var texts = {
			avg_bill: {
				text: t('Ваш средний чек {{value}}.<span class="{{hidden}}"> Это {{bad-good}}, чем у {{user_percent}} продавцов в системе в тех же категориях, в которых находятся ваши кворки.</span><br>') +
				t('Повышайте средний чек, предлагая покупателям дополнительные опции.'),
				bad: t('меньше'),
				good: t('больше')
			},
			extra_orders_sum_percent: {
				text: t('Продажа доп.опций принесла вам {{value}} от общего дохода за {{period}}'),
				bad: t('меньше'),
				good: t('больше')
			},
			review_rating: {
				text: t('Отзывы на ваши кворки {{bad-good}}, чем у {{user_percent}} продавцов в системе.'),
				bad: t('хуже'),
				good: t('лучше')
			},
			done_orders_percent: {
				text: t('Ваша ответственность {{bad-good}}, чем у {{user_percent}} продавцов в системе.'),
				bad: t('хуже'),
				good: t('лучше'),
				good100: t('равна или лучше')
			},
			reaction_time: {
				text: t('Время вашей реакции на заказ - {{value}}. Это {{bad-good}}, чем у {{user_percent}} продавцов.'),
				bad: t('медленнее'),
				good: t('быстрее'),
				zero: t('У вас нет выполненных заказов за {{period}}')
			},
			response_time: {
				text: t('Время вашей реакции на обращения от новых клиентов - {{value}}. Это {{bad-good}}, чем у {{user_percent}} продавцов.'),
				bad: t('медленнее'),
				good: t('быстрее'),
				zero: t('У вас нет выполненных заказов за {{period}}')
			},
			conversion: {
				text: t('Конверсия ваших кворков {{bad-good}}, чем у {{user_percent}} продавцов.'),
				bad: t('хуже'),
				good: t('лучше')
			},
			work_speed: {
				text: t('Среднее время выполнения вами заказов составляет {{value}}. Это {{bad-good}}, чем у {{user_percent}} продавцов.'),
				bad: t('медленнее'),
				good: t('быстрее')
			}
		};

		return texts[metricName];
	};

	var _showPreloader = function () {
		$_preloader.removeClass('hidden');
	};

	var _hidePreloader = function () {
		$_preloader.addClass('hidden');
	};

	return {
		init: function (userData, period, isNewUser) {
			_period = period;
			_isNewUser = isNewUser;
			$_preloader = $('.js-preloader--analytics');

			_dataCache['statistic'][_period] = userData['statistic'];
			_dataCache['line-charts'][_period] = userData['line-charts'];
			_dataCache['total-sum'] = userData['total_sum'];

			_setStatistic();

			$('.analytics__container').css('opacity', '1');
			_setEvents();
		}
	}
})();