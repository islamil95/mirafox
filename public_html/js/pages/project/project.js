$(document).on('click', '.js-send-offer', (e) => {
	window.OfferModal.init({
		price: $(e.target).data('offer-price'),
		lang: $(e.target).data('offer-lang'),

		action: $(e.target).data('offer-action'),
		orderId: $(e.target).data('offer-order-id'),
		form: {
			order_id: $(e.target).data('offer-order-id'),
			id: $(e.target).data('offer-want-id'),
			user_csrf: $(e.target).data('user-csrf'),
		},

		stages: $(e.target).data('offer-stages'),
		duration: $(e.target).data('offer-duration'),
		initialDuration: $(e.target).data('offer-initial-duration'),
		stageMaxIncreaseDays: $(e.target).data('offer-stages-max-increase-days'),
		stageMaxDecreaseDays: $(e.target).data('offer-stages-max-decrease-days'),
		stageMinPrice: $(e.target).data('offer-stage-min-price'),
		customMinPrice: $(e.target).data('offer-custom-min-price'),
		customMaxPrice: $(e.target).data('offer-custom-max-price'),
		offerMaxStages: $(e.target).data('offer-max-stages'),
		stagesPriceThreshold: $(e.target).data('offer-stages-price-threshold'),
		controlEnLang: $(e.target).data('control-en-lang'),
		countStages: $(e.target).data('count-stages'),
	});

	/**
	 * Если продавец прислал предложение меньше 4000 руб., сразу происходит заказ
	 */
	if (parseInt($(e.target).data('offer-price')) < parseInt($(e.target).data('offer-stages-price-threshold'))) {
		window.OfferModal.confirm();
	} else {
		window.OfferModal.showModal();
	}
});

// обновляем данные в инфо о предложении
/**
 * @param orderId
 * @param updateData
 * @param {number} updateData.price
 * @param {array} updateData.stages
 * @param {number} updateData.kworkDays
 * @param {number} updateData.stageMaxDecreaseDays
 */
window.OfferModal.updateContent = (orderId, updateData) => {
	let $offerItem = $('.js-offer-item').filter('[data-order-id="' + orderId + '"]');

	let $buttonSendOffer = $offerItem.find('.js-send-offer');
	let $orderTotalPrice = $offerItem.find('.js-order-total-price');
	let $orderDuration = $offerItem.find('.js-order-duration');
	let $countStages = $offerItem.find('.js-count-stages');
	let $tableStagesInfo = $offerItem.find('.js-stages-info');
	let $tableBodyStagesInfo = $tableStagesInfo.find('tbody');
	let $trBodyStagesInfo = $tableBodyStagesInfo.find('tr:first-child');

	// обновляем итоговую стоимость заказа
	$orderTotalPrice.html(Utils.priceFormatWithSign(updateData.price, $buttonSendOffer.data('offer-lang'), '&nbsp;', '<span class="rouble">Р</span>'));
	// обновляем кол-во задач
	$countStages.html(updateData.stages.length + ' ' + declension(updateData.stages.length, t('задача'), t('задачи'), t('задач')));
	// обновляем срок
	$orderDuration.html(updateData.kworkDays + ' ' + Utils.declOfNum(updateData.kworkDays, [t('день'), t('дня'), t('дней')]));

	// обновляем данные задач для модального окна
	$buttonSendOffer.data("offer-stages", updateData.stages);
	$buttonSendOffer.data("offer-duration", updateData.kworkDays * 86400);
	$buttonSendOffer.data("offer-stages-max-decrease-days", updateData.stageMaxDecreaseDays);
	$buttonSendOffer.data("offer-price", updateData.price);

	// если инфо о предложении открыта
	if ($tableStagesInfo.length) {

		// ощищаем таблицу задач
		$tableBodyStagesInfo.html('');

		// заполняем таблицу задач отредактированными задачами
		for (let i = 1; i <= updateData.stages.length; i++) {
			$.each(updateData.stages, (k, v) => {
				if (i === parseInt(v.number)) {
					let $tr = $trBodyStagesInfo.clone();
					$tr.find('td:eq(0)').html(v.number);
					$tr.find('td:eq(1)').html(v.title);
					$tr.find('td:eq(2)').html(Utils.priceFormatWithSign(v.payer_price, $buttonSendOffer.data('offer-lang'), '&nbsp;', '<span class="rouble">Р</span>'));

					$tableBodyStagesInfo.append($tr);
				}
			});
		}
	}
};

/**
 * Фильтр для показа суммы заработка продавца на бирже
 * за год | за все время
 */
jQuery(function () {
	jQuery('.js-income-filter')
		.chosen({width: '120px', disable_search: true})
		.on('change', function () {
			jQuery('.js-payer-income').toggleClass('hidden');
		});
});