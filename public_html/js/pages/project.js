function loadKworkDetails(target) {
	var $offerBlock = $(target).closest(".js-offer-item");
	var kworkId = $offerBlock.data("id");
	var orderId = $offerBlock.data("order-id");
	var url = base_url + "/api/kwork/getdetails";
	var kworkDetailsNode = $offerBlock.find(".kworkDetails");
	var description = false;
	kworkDetailsNode.find(".preloader--project-container").show();
	var $container = $offerBlock.find(".preloader--project-container");
	kworkDetailsNode.find(".js-description-button-show").hide();
	$container.preloader("show");
	$offerBlock.find(".js-dots").hide();
	$offerBlock.find(".js-offer-comment").removeClass("cur");

	if (kworkDetailsNode.data("loaded") == 0) {
		$.ajax({
			url: url,
			type: "post",
			dataType: "json",
			data: {"kworkId": kworkId, "orderId": orderId},
			success: function (response) {
				if (response.status == "success") {
					var description = response.content.kwork.gdesc;
					var instructions = response.content.kwork.ginst;
					var reviews = response.content.reviews;
					var trackOrder = response.content.track_order;
					var files = response.content.files;
					kworkDetailsNode.data("loaded", "1");
					kworkDetailsNode.find(".js-description").data("descriptionLess", kworkDetailsNode.find(".description").html());
					if (description) {
						kworkDetailsNode.find(".js-description").data("descriptionMore", description);
						kworkDetailsNode.find(".js-description").html(description);
					} else {
						kworkDetailsNode.find('.js-more-description').addClass('hidden');
					}
					kworkDetailsNode.find(".instructions").html(instructions);
					if (files.length > 0) {
						kworkDetailsNode.find(".files").html(files);
						kworkDetailsNode.find(".files").prev().show();
					}
					if (response.content.kwork.is_package == 1 || !response.content.kwork.gwork) {
						kworkDetailsNode.find(".js-gwork").parent().hide();
					} else {
						kworkDetailsNode.find(".js-gwork").text(response.content.kwork.gwork);
					}
					if (reviews.html.length == 0) {
						kworkDetailsNode.find(".reviews").text("Нет отзывов");
					} else {
						kworkDetailsNode.find(".reviews").removeClass("mb10");
						kworkDetailsNode.find(".reviews").html(reviews.html);
					}
					kworkDetailsNode.find(".track-order").html(trackOrder.html);
					kworkDetailsNode.find(".js-reviews").data("params", {
						id: kworkId,
						onPage: 12,
						onPageStart: 5,
						entity: "kwork",
						baseUrl: base_url
					});
					kworkDetailsNode.find(".js-reviews").reviewWidget();
					if (!description) {
						kworkDetailsNode.find(".description").html(kworkDetailsNode.find(".description").data("descriptionMore"));
					}
					$container.html("").hide();
					kworkDetailsNode.find(".more").show();
					$offerBlock.find(".js-description-button-hide").show();
					kworkDetailsNode.addClass("mb20");
				}
			}
		});
	} else {
		if (!description) {
			kworkDetailsNode.find(".description").html(kworkDetailsNode.find(".description").data("descriptionMore"));
		}
		$container.html("").hide();
		kworkDetailsNode.find(".more").show();
		$offerBlock.find(".js-description-button-hide").show();
		kworkDetailsNode.addClass("mb20");
	}
}
function hideKworkDetails(target) {
	var $offerBlock = $(target).closest(".js-offer-item");
	var kworkDetailsNode = $offerBlock.find(".kworkDetails");
	$offerBlock.find(".js-dots").show();
	$offerBlock.find(".js-offer-comment").addClass("cur");
	kworkDetailsNode.find(".more").hide();
	kworkDetailsNode.find(".description").html(kworkDetailsNode.find(".description").data("descriptionLess"));
	$offerBlock.find(".js-description-button-hide").hide();
	kworkDetailsNode.removeClass("mb20");
	kworkDetailsNode.find(".preloader--project-container").hide();
}

$(function () {
	$(".info-block").infoBlock();
	$(document).on("click", ".js-offer-item-cancel-link", function () {
		$(this).toggleClass("offers__cancel-link--active");
		$(".offer-item_cancel").toggleClass("hidden");
		if ($(this).hasClass("offers__cancel-link--active")) {
			$(this).data("opened", true);
		} else {
			$(this).data("opened", false);
		}
		$(".offer-item_cancel:first").before(function () {
			return $(".js-offer-item-cancel-link").detach();
		});
		return false;
	});

	function offerShowLinkVisibility() {
		var $offerToggleHiddenLink = $(".js-offer-item-cancel-link");
		if ($(".offer-item_cancel").length > 0) {
			$offerToggleHiddenLink.removeClass("hidden");
		} else {
			$offerToggleHiddenLink.addClass("hidden");
		}
		$(".offer-item_cancel:first").before(function () {
			return $offerToggleHiddenLink.detach();
		});
	}

	$(document).on("click", ".js-hide-offer-btn", function () {
		var $button = $(this);
		var $offer = $(this).closest(".js-offer-item");

		if ($button.hasClass("choosed")) {
			// Если уже был скрыт - убираем скрытие
			$.post(base_url + "/remove_hide_offer/" + $offer.data("offerId"));
			$button.removeClass("choosed");
			$offer.removeClass("offer-item_cancel");
			offerShowLinkVisibility();
		} else {
			// если не был скрыт - скрываем
			$offer.find(".js-highlight-offer").removeClass("choosed");
			$offer.removeClass("offer-item-highlighted");

			// если предложение не архивное, то скрываем под спойлер
			if ($offer.data('is-archived') == 0) {
				$offer.animate({opacity: "0"}, 100);
				$offer.animate({
					height: 0
				}, 150, function () {
					var $offerToggleHiddenLink = $(".js-offer-item-cancel-link");

					if ($offerToggleHiddenLink.data("opened") !== true) {
						$(this).addClass("hidden");
					}

					$offer.removeAttr("style");

					$offerToggleHiddenLink.after($offer);
					$button.addClass("choosed");
					$offer.addClass("offer-item_cancel");
					offerShowLinkVisibility();
				});
			} else {
				$button.addClass("choosed");
				$offer.addClass("offer-item_cancel");
			}

			$.post(base_url + "/api/offer/hideoffer", {"order_id": $offer.data("orderId")});
		}
	});

	$(document).on("click", ".js-highlight-offer", function () {
		var $button = $(this);
		var $offer = $(this).closest(".js-offer-item");
		if ($button.hasClass("choosed")) {
			$.post(base_url + "/remove_offer_highlight/" + $offer.data("offerId"));
		} else {
			$offer.removeClass("offer-item_cancel");
			$offer.find(".js-hide-offer-btn").removeClass("choosed");
			offerShowLinkVisibility();
			$.post(base_url + "/offer_highlight/" + $offer.data("offerId"));
		}

		$button.toggleClass("choosed");
		$offer.toggleClass("offer-item-highlighted");
	});

	$(document).on("click", ".js-want-link-toggle-desc", function () {
		$(this).closest(".js-want-container").find(".js-want-block-toggle").toggleClass("hidden");
		$(this).closest(".js-want-container").find(".files-list").toggleClass("hidden");
	});

	$(".js-want-container").each(function () {
		if ($(this).find(".js-want-link-toggle-desc").length) {
			$(this).find(".files-list").addClass('hidden');
		}
	})

	$(document).on('submit', '.js-form-project-order', function (e) {
		e.preventDefault();

		$.ajax({
			url: $(this).attr('action'),
			type: 'post',
			data: $(this).serialize(),
			dataType: 'json',
			success: function (response) {
				if (!response.success && response.code === 124) {
					show_balance_popup(response.needMoney, 'project', undefined, response.orderId);
				} else if (typeof(response.redirectUrl) !== 'undefined') {
					document.location.href = response.redirectUrl;
				} else {
					document.location.reload();
				}
			},
		});
	});

	// Проматываем к "Предложения продавцов"
	if (window.location.hash === "#view_offers") {
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}

		var $listOffers = jQuery('.js-header-list-offers');

		setTimeout(function () {
			$('html').animate({
				scrollTop: $listOffers.offset().top - 100
			}, 200);
		}, 200);
	}
});
