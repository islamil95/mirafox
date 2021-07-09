/**
 * #6318 Расчет комиссии Kwork в зависимости от оборота с конкретным клиентом
 *
 * @global array commissionRanges Диапазоны комиссий
 *
 * @param float price Стоимость услуги
 * @param float turnover Оборот с клиентом
 *
 * @return object
 *   - float price Стоимость услуги
 *   - float turnover Оборот с клиентом
 *   - array ranges Диапазоны оборотов
 *   - float priceKwork Величина комиссии
 *   - float priceWorker Доля продавца
 */
function calculateCommission(price, turnover) {
	var priceWorker = price - price * window.commission / 100;
	var priceKwork = price - priceWorker;

	return {
		price: price,
		// https://stackoverflow.com/questions/11832914/round-to-at-most-2-decimal-places-only-if-necessary
		priceKwork: +priceKwork.toFixed(2),
		priceWorker: +priceWorker.toFixed(2),
		turnover: turnover,
		ranges: {},
	};
}

/**
 * #6318 Расчет цены для покупателя по цене для продавца
 * в зависимости от оборота между ними
 *
 * @global array commissionRanges Диапазоны комиссий
 *
 * @param float priceWorker Цена для продавца
 * @param float turnover Оборот с клиентом
 *
 * @return object
 *   - float price Стоимость услуги
 *   - float turnover Оборот с клиентом
 *   - array ranges Диапазоны оборотов
 *   - float priceKwork Величина комиссии
 *   - float priceWorker Доля продавца
 */
function calculateCommissionByPriceWorker(priceWorker, turnover) {
	var ranges = JSON.parse(JSON.stringify(commissionRanges));

	var price = 0;
	var priceKwork = 0;

	var currentPriceWorker = priceWorker;
	var currentTurnover = turnover;

	for (var i = 0; i < ranges.length; i++) {
		var range = ranges[i];

		if (range.maxTurnover === null) {
			range.maxTurnover = Infinity;
		}

		if (currentTurnover < range.maxTurnover) {
			var rangePart = range.maxTurnover - currentTurnover;

			range.priceWorker = Math.min(currentPriceWorker, rangePart * (1 - range.percentage));
			range.price = range.priceWorker / (1 - range.percentage);
			range.priceKwork = range.price - range.priceWorker;

			price += range.price;
			priceKwork += range.priceKwork;

			currentPriceWorker -= range.priceWorker;
			currentTurnover += range.price;

			if (currentPriceWorker <= 0) {
				break;
			}
		}
	}

	return {
		// https://stackoverflow.com/questions/11832914/round-to-at-most-2-decimal-places-only-if-necessary
		price: +price.toFixed(2),
		priceKwork: +priceKwork.toFixed(2),
		priceWorker: priceWorker,
		turnover: turnover,
		ranges: ranges,
	};
}

/**
 * Тестирование функции calculateCommissionByPriceWorker
 * с помощью функции calculateCommission
 */
/* var tests = [
	// price, turnover
	[10000, 0],
	[10000, 25000],
	[10000, 40000],
	[10000, 400000],
	[40000, 0],
	[40000, 20000],
	[40000, 40000],
	[40000, 400000],
	[400000, 0],
	[400000, 20000],
	[400000, 40000],
	[400000, 400000],
	[99999.99, 0],
	[100000.01, 0],
];
$.each(tests, function (i, test) {
	var commission1 = calculateCommission(test[0], test[1]);
	var commission2 = calculateCommissionByPriceWorker(commission1.priceWorker, test[1]);
	if (commission2.price === test[0]) {
		console.log(commission2.price + ' === ' + test[0]);
	} else {
		console.error(commission2.price + ' !== ' + test[0]);
	}
}); */
