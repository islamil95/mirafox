var orderId = 0;
var sourceEl = 0;
var isClicked = false;
$(document).ready(function () {

	function changeUrl() {
		if ($(".order-types-menu").html().indexOf("completed") !== -1) {
			window.location = window.location.href.split('?')[0] + "?s=completed";
		} else {
			window.location = window.location.href.split('?')[0];
		}
	}

	$('body').on('click touchstart', '.fos-clear-js', function () {
		$(".order-search-field-js").val('');
		$(this).hide();
	});

	if ($(".order-search-field-js").length > 0) {
		if ($(".order-search-field-js").val().length == 0) {
			$(".fos-clear-js").hide();
		}
	}

	$(".balance-refill-form-js .order-name").keypress(function (e) {
		if (e.which == 13) {
			$(".popup-order-name-js .save-new-name").click();
			return false;
		}
	});

	$(".order-search-field-js").keyup(function () {
		if ($(this).val().length == 0) {
			$(".fos-clear-js").hide();
			if ($(".orders-search-result").length) {
				changeUrl();
			}
		} else {
			$(".fos-clear-js").show();
		}
	});
	$(".change-order-name-js").click(function () {
		orderId = $(this).attr("rel");

		$(".popup-order-name-js .form-entry-error").addClass("hidden");
		$(".popup-order-name-js").fadeIn("fast");
		$(".balance-refill-form-js .order-name").css("height", "65px");
		sourceEl = $(this).parent();

		$(".popup-order-name-js .order-name").val(he.decode(sourceEl.find("a").html()));

	});

	$(".popup-order-name-js .save-new-name").click(function () {

		$(".popup-order-name-js .form-entry-error").addClass("hidden");
		var name = $(".popup-order-name-js .order-name").val();
		if (sourceEl.find("a").html() == name) {
			clearPopupData();
		}
		if (name != "") {
			$.ajax({
				url: "/orders/set_user_order_name",
				type: "POST",
				data: {order_id: orderId, name: name},
				success: function (response) {
					sourceEl.find("a").html(name);
					clearPopupData();
				}
			});
		} else {
			$(".popup-order-name-js .form-entry-error").removeClass("hidden");
		}

	});

	function clearPopupData() {
		sourceEl = 0;
		orderId = 0;
		$(".popup-order-name-js .order-name").val("");
		$(".popup-order-name-js").fadeOut("fast");
	}

	$(".popup-order-name-js .popup-close, .overlay").click(function () {
		$(".popup-order-name-js").fadeOut("fast");
	});
});

