$(function () {
	$(".js-query-item__text").each(function () {

		var $el = $(this).clone(false);
		$el.css({visibility: "hidden", position: "absolute", "width": "auto"});
		$el.appendTo("body");
		var width = $el.width();
		$el.remove();

		if (width < 315) {
			$(this).closest(".js-query-text-block--short")
			.removeClass("query-item__text-block--short")
			.find(".js-query-item__more").hide();
		}
	});

	$(".js-query-text-block--short").on("click", function () {
		$(this).removeClass("query-item__text-block--short");
		$(this).find(".js-query-item__text").removeClass("query-item__text--short");
		$(this).find(".js-query-item__more").hide();
	});

	$(".js-query-item__delete").on("click", function () {
		delete_offer_confirm($(this).data("id"));
	});

	$(".js-show-offer").click(function () {
		var $offer = $(this).closest(".js-offer-want-container").find(".js-offer-container");
		if ($offer.is(":visible")) {
			$offer.slideUp();
		} else {
			$offer.slideDown();
		}
	});
});

function deleteOffer(id) {
	$.post("/api/offer/deleteoffer", {"id": id}, function (response) {
		if (response.success == true) {
			location.reload();
		}
	}, "json");
}

function delete_offer_confirm(id) {
	var html = "" +
		"<div>" +
		"<input type=\"hidden\" name=\"id\" value=\"" + id + "\" />" +
		"<input type=\"hidden\" name=\"action\" value=\"delete\" />" +
		"<h1 class=\"popup__title\">" + t("Подтверждение удаления") + "</h1>" +
		"<hr class=\"gray\" style=\"margin-bottom:32px;\">" +
		"<div class='overflow-hidden'>" +
		"<p class=\"f15 pb50 ml10\">" + t("Удалить предложение?") + "</p>" +
		"<button class=\"popup__button red-btn mt20 f16\" onclick=\"deleteOffer(" + id + ")\">" + t("Удалить") + "</button>" +
		"<button class=\"popup__button white-btn mt20 pull-right popup-close-js f16\" onclick=\"return false;\">" + t("Отменить") + "</button></div>" +
		"</div>" +
		"</div>";
	show_popup(html);
}

jQuery(function() {
	// Подсвечиваем подозрительный отзыв
	var params = getUrlParams();
	if (window.location.pathname === '/offers' && params !== undefined && params.offerId !== undefined) {
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}

		var $offerItemContent = jQuery('.js-offer-item[data-offer-id="' + params.offerId + '"]').closest('.js-offer-container');
		var $offerItem = $offerItemContent.closest('.js-offer-want-container');

		$offerItemContent.css({
			boxShadow: '0 0 0 3px #f15b5b'
		}).show();

		setTimeout(function () {
			$('html').animate({
				scrollTop: $offerItem.offset().top - 100
			}, 200);
		}, 200);
	}
});