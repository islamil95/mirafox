(function () {
	var script = document.createElement("script");
	script.async = true;
	script.src = widgetUrl;
	document.getElementsByTagName("head")[0].appendChild(script);
})();

$(document).ready(function () {
	var referralUrl = base_url + (USER_ID ? "?ref=" + USER_ID : "");

	$("#notaft").hide();
	$("#message_buf").hide();

	$("#referals-generate-text").on("input", function () {
		if ($(this).val().length == 0) {
			changeButtonType($(".js-partner-btn"), "generate");
			$(".js-partner-btn").addClass("disabled");
		} else {
			$(this).removeClass("input-styled--error");
			$(".js-partner-btn").removeClass("disabled");
		}
	});

	$(".js-partner-btn").on("click", function () {
		if ($(this).hasClass("js-partner-btn-copy") == false) {
			if ($(this).hasClass("disabled")) {
				return;
			}
			generateLink();
			changeButtonType(this, "copy");
		} else {
			copy();
		}
	});

	var params = getGetParams();
	if (params["scroll"] && params["scroll"] == "rules") {
		$("html, body").animate({
			scrollTop: $("#rules").offset().top - 90
		}, 200);
	}

	function generateLink() {
		var $textInput = $("#referals-generate-text");
		var pageUrl = $textInput.val().toString().trim();
		var sign = pageUrl.indexOf("?") === -1 ? "?" : "&";
		var url = pageUrl;
		if(USER_ID) {
			url += sign + "ref=" + USER_ID;
		}
		$textInput.val(url);

		var share = Ya.share2('ya-share2', {
			content: {
				url: url
			}
		});
	}

	function changeButtonType(el, type) {
		if (type == "generate") {
			$(el).addClass("js-partner-btn-generate").removeClass("js-partner-btn-copy").val(t("Сгенерировать ссылку"));
		} else if (type == "copy") {
			$(el).addClass("js-partner-btn-copy").removeClass("js-partner-btn-generate").val(t("Скопировать"));
		}
	}

	$("#parents").change(function () {

		var parentSeo = $(this).val();

		$(".sub_category").addClass("hidden");
		$(".js-sub-cat-container").show();

		if (parentSeo === "all") {
			$(".sub_empty").removeClass("hidden");
			updateIframe();
			return true;
		}

		$(".childs").prop("selectedIndex", 0).addClass("hidden").next(".chosen-container").hide();

		var catSubSelector = ".sub-" + $(this).find("option:selected").data("id");
		if ($(catSubSelector).length > 0) {
			$(catSubSelector).removeClass("hidden");
		}

		updateIframe();
	});

	$(".sub_category").change(updateIframe);

	function updateIframe() {
		var categorySeo = $(".childs:visible").val();
		if (!categorySeo) {
			categorySeo = $("#parents").val();
		}

		var urlParams = "?json=no&category=" + categorySeo;

		$("#widgetExample").html("");

		$.get("/api/userbadge/widgetcode" + urlParams, function (response) {
			$("#widgetCode").text(response);
			$("#copyWidget").text(t("Скопировать код"));
			$("#widgetExample").html(response);
		});
	}

	updateIframe();

	$("#copyWidget").on("click", function (e) {
		e.preventDefault();
		$("#widgetCode").select();
		document.execCommand("copy");
		$(this).text(t("Скопировано в буфер обмена"));
	});

	$(".show_code").on("click", function () {
		var banner = $(this).parent().find("img").clone();
		if (banner.length == 0) {
			banner = $(this).parent().find("object").clone();
			banner.attr("data", base_url + banner.attr("data"));
			banner.find("param[name=\"src\"]").val(base_url + banner.find("param[name=\"src\"]").val());
			banner.find("embed").attr("src", base_url + banner.find("embed").attr("src"));
			banner.attr("onmousedown", "this.parentNode.click();");
			var banner_code = "<a href=\"" + referralUrl + "\" target=\"_blank\">" +
			$("<div></div>").append(banner).html() + "</a>";
		} else {
			banner.attr("src", base_url + banner.attr("src"));
			banner.removeAttr("class");
			banner.attr("alt", t("Kwork.ru - услуги фрилансеров от 500 руб."));
			var banner_code = "<a href=\"" + referralUrl + "\" target=\"_blank\">" +
			$("<div></div>").append(banner).html() + "</a>";
		}
		//$(".banner_code").remove();
		//$(".copy_code").remove();
		$(".wrapper_code").remove();

		var parent = $(this).parent().parent();
		parent.append($("<div class=\"wrapper_code\" style=\"display: none\">" +
			"<textarea class=\"banner_code textarea-styled\" onclick=\"$(this).select()\" readonly>"
				+ banner_code +
			"</textarea>" +
			"<a class=\"db copy_code\" onclick=\"copy_code($(this));\">" +
				"<i class=\"fa fa-fw fa-clipboard\"></i> "
				+ t("Скопировать код") + "" +
			"</a>" +
		"</div>"));

		var wrapperCode = parent.children(".wrapper_code");
		wrapperCode.fadeIn(500).children(".banner_code").select();

		//перематываем к textarea
		$("html, body").stop().animate({
			scrollTop: parent.children(".partner__item:last").offset().top - $(".header_top").height() - 20
		}, 500);

		//добавляем блюр
		partnerItemFilter($(this));
	});

	//прячем код баннера и убираем блюр
	//при клике вне нужной области
	$(document).click(function(event) {
		if ($(event.target).closest("#banners .show_code, #banners .banner_code, #banners .copy_code").length) return;
		$("#banners .filter_blur").removeClass("filter_blur");
		$("#banners .wrapper_code").remove();
		event.stopPropagation();
	});
	//при нажатии на Esc
	$(document).keyup(function(e) {
		if (e.keyCode === 27) {
			$("#banners .filter_blur").removeClass("filter_blur");
			$("#banners .wrapper_code").remove();
			event.stopPropagation();
		}
	});
});

function copy_code($target) {
	$target.parent().find(".banner_code").select();
	document.execCommand("copy");
	$target.text(t("Скопировано в буфер обмена"));
}

function copy() {
	try {
		$("#referals-generate-text").select();
		document.execCommand("copy");
		$("#message_buf").show();
	} catch (e) {
		alert(e);
	}
}

function notaft_message() {
	$("#notaft").show();
}

//блюр и оттенки серого для неактивных баннеров
//в момент клика по ссылке получения кода баннера
function partnerItemFilter(link) {
	$("#banners .partner__item.filter_blur").removeClass("filter_blur");
	$("#banners .partner__item").addClass("filter_blur");

	var parent = $(link).closest(".partner__item");
	parent.removeClass("filter_blur");
	return true;
}