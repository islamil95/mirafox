var isCloneAccept = false;
var ajaxCreateOfferSemafor = false;
$(document).ready(function () {
	$(".info-block").infoBlock();
	$('#request-kwork-id').trigger('change');

	if (offer.isOrderStageTester) {
		var $stages = $('.js-stages-data');
		window.offerStages.init({
			actorType: $stages.data('actor-type'),
			stages: $stages.data('stages'),
			classDisableButton: $stages.data('button-disable-class'),
			turnover: turnover,
			pageType: $stages.data('page-type'),
			offer: {
				orderId: 0,
				lang: offer.lang,
				stageMinPrice: offer.stageMinPrice,
				customMinPrice: offer.customMinPrice,
				customMaxPrice: offer.customMaxPrice,
				offerMaxStages: offer.offerMaxStages,
				stagesPriceThreshold: offer.stagesPriceThreshold,
			},
			controlEnLang: controlEnLang,
		});

		window.offerStages.generationStages();
	}

	window.OfferIndividualModule.init();

	//Переключение предожить кворк / индивидуальное предложение
	$("#change-to-kwork-choose, #change-to-custom-kwork").click(function () {
		$(".js-custom-kwork, .js-active-kwork, .send-request__block-title, .info-block__text-inner").toggleClass("hidden");
		if ($(".js-custom-kwork").hasClass("hidden")) {
			$("#offer-submited-type").val("kwork");
			$('#request-kwork-id').trigger('change');
		} else {
			$("#offer-submited-type").val("custom");
		}

		OfferIndividualModule.validateIndividualKwork();
	});

	$(".want-change-text, .popup-clone-find .popup-close").click(function () {
		$(".popup-clone-find").hide();
	});

	// Отправить как есть
	$(".send-as-is").click(function () {
		$(".popup-clone-find").hide();
		isCloneAccept = true;
		goToAddOffer();
	});

	$("#offer_kwork_form").on("submit", function (e) {
		e.preventDefault();
		OfferIndividualModule.btnDisable();
		
		if(isCloneAccept) {
			goToAddOffer();
		} else {
			$.ajax({
				url:"/projects/check_is_template",
				method: "POST",
				data: {
					description: $(".js-kwork-description").val(),
					wantid: $("#want-id").val()
				},
				dataType: "json",
				success: function (response) {
					if(response.data.error){
						// Шаблонный текст
						$(".popup-clone-find").show();
					}else{
						goToAddOffer();
					}
				},
				complete: function () {
					OfferIndividualModule.validateIndividualKwork();
				}
			});
		}
	});

	var _selectors = {
		want: {
			container: '.js-want-container',
			linkMore: '.js-want-link-toggle-desc',
			blockMore: '.js-want-block-toggle',
			files: '.files-list',
		}
	};
	$(document).on('click', _selectors.want.linkMore, function () {
		$(this).closest(_selectors.want.container).find(_selectors.want.blockMore).toggleClass('hidden');
		$(this).closest(_selectors.want.container).find(_selectors.want.files).toggleClass('hidden');
	});
});

function goToAddOffer() {

	var paymentType = $('.js-new-offer-radio.offer-payment-type__radio-item--active').data('type');

	if (paymentType === 'stages') {
		window.offerStages.reSaveStages();
		if (window.offerStages.checkDisableButtonSend()) {
			window.offerStages.disableButtonSend();

			return;
		}
	}

	var params = window
		.location
		.search
		.replace("?", "")
		.split("&")
		.reduce(
			function (p, e) {
				var a = e.split("=");
				p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
				return p;
			},
			{}
		);

	window.OfferIndividualModule.setSerialize(paymentType);

	var formDataSerialized = $("#offer_kwork_form :not(.no-serialize)").serialize();

	var addedParams = ["a","c","page"];

	for (var i in addedParams) {
		if (params[addedParams[i]]) {
			formDataSerialized += "&"+ addedParams[i] +"=" + params[addedParams[i]];
		}
	}
	if (ajaxCreateOfferSemafor) {
		return;
	}
	ajaxCreateOfferSemafor = true;
	var offerId = parseInt($("#offer-id").val());
	if (offerId > 0){
		$.ajax({
			url: "/api/offer/editoffer",
			method: "POST",
			data: formDataSerialized,
			dataType: "json",
			success: function (response) {
				if (response.success === true) {
					location.href = "/offers";
				} else {
					OfferIndividualModule.showBackendError(response);
				}
			},
			error: function () {
				OfferIndividualModule.validateIndividualKwork();
			},
			complete: function() {
				ajaxCreateOfferSemafor = false;
				OfferIndividualModule.validateIndividualKwork();
			}
		});
	} else {
		$.ajax({
			url:"/api/offer/createoffer",
			method: "POST",
			data: formDataSerialized,
			dataType: "json",
			success: function (response) {
				if (response.success === true) {
					location.href = response.redirect;
				} else {
					OfferIndividualModule.showBackendError(response);

					// Верификация продавцов по мобильному телефону
					if (response.code && response.code == 201) {
						$(".js-individual-kwork-error").text("");

						phoneVerifiedOpenModal(function() {
							$('#offer_kwork_form').find('[name="submit"]')
								.removeClass("disabled")
								.prop("disabled", false)
								.click();
						});
					}
				}
			},
			error: function () {
				OfferIndividualModule.validateIndividualKwork();
			},
			complete: function() {
				ajaxCreateOfferSemafor = false;
			}
		});
	}
}
