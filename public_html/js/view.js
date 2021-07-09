var isMakeOrder = false;
var slideMobileModule;
var slideDesktopModule;

function changeChosenDirectionOnOpen() {
	$('.chosenselect').on('chosen:showing_dropdown', function () {
		var self = $(this)[0].nextSibling;
		if ((self.getBoundingClientRect().top + self.scrollHeight) >= window.innerHeight) {
			self.classList.add('drop-top');
		}
	}).on('chosen:hiding_dropdown', function () {
		var self = $(this)[0].nextSibling;
		self.classList.remove('drop-top');
	})
}

$(window).ready(function () {
	$('.chosenselect').chosen({width: "108px", disable_search: true});
	changeChosenDirectionOnOpen();


	var responsiveDots = {
		768: {items: 3},
		1280: {items: 5}
	}; 

	$('.other .kwork-small-carousel').kworkCarousel({
		staticFirst: true,
		margin: 0,
		items: 9999999,
		responsive: responsiveDots
	});
	$('.recommend .kwork-small-carousel').kworkCarousel({
		staticFirst: true,
		margin: 0,
		items: 21,
		responsive: {
			768: {
				items: 3
			}
		}
	});
	var preventIconHeartClick = false;
	$('body').on('touchend', '.js-icon-heart', function (e) {
		if ($(e.target).hasClass('icon-heart_hover')) {
			preventIconHeartClick = true;
			return;
		}
	});

	$('body').on('click', '.js-icon-heart', function (e) {
		if ($(e.target).hasClass('icon-heart_hover')) {
			if (preventIconHeartClick) {
				setTimeout(function() {
					preventIconHeartClick = false;
				}, 1000);
				return;
			}
		}
		var $favCount = $('.js-fav-count');
		var cnt = parseInt($favCount.html(), 10);

		var $bookmark = $('.js-bookmark').filter('[data-pid="' + post_id + '"]');

		$('.js-kwork-control[data-id="' + post_id + '"] .js-icon-heart').toggleClass('hidden');
		var state = false;
		if (window.bookmarkXhr && post_id in window.bookmarkXhr) {
			state = window.bookmarkXhr[post_id].state;
		} else {
			state = $bookmark.hasClass('active');
		}
		if (state) {
			bookmark(post_id, false);
			if (cnt > 0) {
				$favCount.html(cnt - 1);
			}
		} else {
			$favCount.html(cnt + 1);
			bookmark(post_id, true);
		}

		return false;
	});
	//Сворачивание, разворачивание блока Предупреждение
	$(".requiredInfo-risks-link-show").on("click", function () {
		$(this).find('img').toggleClass('rotate180');
		$(".requiredInfo-risks-block-text").toggleClass("hidden");
	});
	$(".requiredInfo-risks-link-hide").on("click", function () {
		$(".requiredInfo-risks-link-show").find('img').removeClass("rotate180");
		$(".requiredInfo-risks-block-text").addClass("hidden");
	});
	$(document).on('click', '.order-extras__select-block', function () {
		$('#order-extras_' + $(this).data('id') + ', #order-extras_' + $(this).data('id') + '_right').prop('checked', true);
		recalculatePrice('side');
	});
	$(document).on("click", ".order-extra-item", function (e) {
		var checkbox = $(this).find("input[type='checkbox']");
		var kworkCountWrapper = $(this).find('.kwork-count-wrapper');
		if (checkbox.prop("checked")) {
			if (!$(e.target).is('.chosen-container') && $(e.target).closest('.chosen-container').length === 0) {
				checkbox.prop("checked", false);
			}
			kworkCountWrapper.fadeOut(150);
		} else {
			checkbox.prop("checked", true);
			kworkCountWrapper.fadeIn(150);
		}
		recalculatePrice();
	});
	$(document).on("click", ".kwork-count-wrapper", function (e) {
		return false;
	});
	$(document).on("click", ".order-extra-item-right", function (e) {
		var checkbox = $(this).find("input[type='checkbox']");
		if (checkbox.prop("checked")) {
			if (!$(e.target).is('.chosen-container') && $(e.target).closest('.chosen-container').length === 0) {
				checkbox.prop("checked", false);
			}
		} else {
			checkbox.prop("checked", true);
		}
		recalculatePrice('side');
	});
	$(document).on('click', '.js-cannotorder', function () {
		can_not_order_popup(jsCategoryName, jsCategorySeo);
	});

	// изначально был отрендерен desktop слайдер, но не mobile
	var isDesktopSliderRendered = $('.kwork_slider_desktop .sliderImage').length;

	var $sliderSelector;

	if (isDesktopSliderRendered) {
		 $sliderSelector = $('.kwork_slider_desktop');
	} else {
		 $sliderSelector = $('.kwork_slider_mobile');
	}

	// сохраняяем базовый html слайдера,
	// для возможности реинициализации сладера заново при изменении типа экрана (mobile/desktop):
	var kworkSliderBaseHtml = $sliderSelector.html();

	initSlider(
		$('body').hasClass('is_mobile'),
		kworkSliderBaseHtml
	);

	$('body').bind('screenTypeChanged', function(event, isMobile) {
		initSlider(isMobile, kworkSliderBaseHtml);
	});

	$('.kwork_slider_desktop #slider_desktop').on('beforeChange', function (event, slick, currentSlide, nextSlide) {
		$('.kwork_slider_desktop #slider_desktop').addClass('kwork-slider_preload')
		$('.kwork_slider_desktop .sliderItem').css({visibility: "hidden"})
		$('.kwork_slider_desktop .sliderItem[data-index="' + currentSlide + '"]').css({visibility: "visible"})
		$('.kwork_slider_desktop .sliderItem[data-index="' + nextSlide + '"]').css({visibility: "visible"})
	});

	$('.kwork_slider_desktop #slider_desktop .slick-slide img').on('load', function(){
		$('.kwork_slider_desktop #slider_desktop').removeClass('kwork-slider_preload');
	});

	$(document).on('click', '.kwork-count>div, .kwork-count-link a, .kwork-count__link', function (e) {
		e.stopPropagation();

		var input, inputVal;
		var parentLi = $(this).parents('li');

		if ($(this).hasClass('js-kwork-count-link')) {
			input = $(this).closest('.kwork-count').find('input');
		} else {
			input = $(this).siblings('input');
		}
		inputVal = parseInt(input.val());

		if ($(this).hasClass('kwork-count_minus')) {
			if (inputVal === 1) {
				if ($(this).hasClass('kwork-count__link')) {
					parentLi.trigger('click');
				}
				return false;
			}
			inputVal -= 1;
			input.val(inputVal);
		} else {
			if (inputVal >= parseInt(input.attr('data-max'))) {
				return false;
			}
			inputVal += 1;
			input.val(inputVal)
		}

		parentLi.find('select').val(inputVal);
		parentLi.find('.styled-checkbox').prop('checked', false);
		parentLi.trigger('click');

		recalculatePrice();
	});

	init_package_tabs();
	$(document).on('click','.package_tabs>*',function(){
		$('.package_tabs .active').removeClass('active');
		$(this).addClass('active');
		$(this).parents('.b-package').removeClass('show-standard').removeClass('show-medium').removeClass('show-premium');
		$(this).parents('.b-package').addClass('show-'+$(this).attr('data-type'))
	});

	$(document).on('input', '.js-volume-order', function () {
		kworkCount($(this), true);
	});

    $('.view-packages-cell').hover(function () {
	    var index = parseInt($(this).index()) + 1;
	    if (index > 1) {
		    $('.view-packages-cell:nth-child(' + index + ')').addClass('highlighted');
	    }

    }, function () {
	    var index = parseInt($(this).index()) + 1;
	    $('.view-packages-cell:nth-child(' + index + ')').removeClass('highlighted');
    });

	$(window).resize(function () {
		if ($('.package_tabs>div').hasClass('active') === false) {
			init_package_tabs();
		}

		var w = $(window).width();
		if (w < 768) {
			$('.order-extras-mobile .order-extra-item').each(function () {
				var inputChecked = $(this).find('.styled-checkbox:checked');
				var kworkCountWrapper = $(this).find('.kwork-count-wrapper, .kwork-count-wrapper-volume');
				if (inputChecked.length) {
					kworkCountWrapper.show();
				} else {
					kworkCountWrapper.hide();
				}
			});
		}
	});
});

window.ad_product = {
	"id": post_id, // required
	"vendor": vendor,
	"price": price,
	"url": productUrl,
	"picture": pictureUrl,
	"name": productName,
	"category": productCategory
};

$(document).ready(function () {
	ReviewsModule.init({
		id: post_id,
		onPage: 12,
		onPageStart: 5,
		entity: 'kwork',
		baseUrl: baseUrl
	});
	
	if (!isPackage) {
		// Считаем кол-во кворков т.к у нас есть минимальное значение
		kworkCount($('.js-volume-order').eq(0), false);
	} else {
		// Проходимся по всему пакету и выставляем правильную цену
		kworkCount($('input#table-volume-standard').eq(0), false);
		kworkCount($('input#table-volume-medium').eq(0), false);
		kworkCount($('input#table-volume-premium').eq(0), false);
	}
	if(isRecalculatePrice) {
		recalculatePrice('side');
	}
	if(showBalancePopup) {
		show_balance_popup(balanceRefillAmount, 'kwork');
	}
	if (isUserKworkAndOnPause) {
		var message = noticeMessage;
		if (isLowPortfolioPauseReason) {
			message = lowPortfolioMessage;
		}
		show_message('notice', message);
	}
	recalculatePrice('', true);
	recalculatePrice();
	$('a.edit-kwork-button.has-offers').click(function () {
		change_kwork_offer_confirm($(this).attr('href'));
		return false;
	});
});

/**
 * Установка значения количества заказываемых кворков при вводе объема
 *
 * $input jquery объект текстового поля
 * is_formatting_input {bool} нужно ли обновлять поле форматированными данными
 * для случаев, кода нужно вычислить количество в заказе(при загрузки страницы)
 */ 
function kworkCount($input, is_formatting_input) {
	// Установка значения количества заказываемых кворков при вводе объема
	var volume = $input.val();
	volume = (volume === undefined) ? "" : volume;
	var decimals = 1;
	volume = volume.replace(/[\s]/gim, '');
	var delimiter = hasMark(volume);
	volume = parseFloat(volume.replace(",", "."));
	var duration = $input.data('duration');

	var packageType = $input.data('packageType');

	if (isNaN(volume)) {
		volume = "";
	}	
	
	var kworkCount = 1;
	if (volume != Math.ceil(volume)) {
		decimals = 1;
	} else {
		decimals = 0;
	}
	// Получаем значение делителя и преобразовываем в число
	var multiplier = parseFloat($input.attr('data-volume-multiplier'));
	var minVolume = parseFloat($input.attr('data-min-volume'));
	if (isNaN(minVolume)) {
		minVolume = multiplier;
	}
	var virtualVolume = (minVolume > volume) ? minVolume : volume;
	if (virtualVolume > multiplier) {
		kworkCount = Math.ceil(virtualVolume / multiplier);
	}

	var maxKworkCount = parseInt($input.attr('data-max-count'));
	var maxKworkVolume = parseInt($input.attr('data-max'));
	var volumeInput;
	if (kworkCount > maxKworkCount) {
		kworkCount = maxKworkCount;
		volume = kworkCount * multiplier;
		if(is_formatting_input) {
			volumeInput = Utils.numberFormat(volume, decimals, delimiter, " ");
			if (volume < 1) {
				volumeInput = 1;
			}
			$input.val(volumeInput);
		}
	}
	var otherInputsSelector = '.js-volume-order:not(#' + $input.attr('id') + ')';
	if (packageType) {
		otherInputsSelector += '[data-package-type="' + packageType + '"]';
	}
	if (is_formatting_input) {
		if (volume === "") {
			$(otherInputsSelector).val("");
		} else {
			volumeInput = Utils.numberFormat(volume, decimals, delimiter, " ");
			if (volume < 1) {
				volumeInput = 1;
			} else if (volume > maxKworkVolume) {
				volumeInput = maxKworkVolume;
			}
			$(otherInputsSelector).val(volumeInput);
		}
	}

	if (packageType) {
		var price = $input.data('price');
		
		if (price) {
			if (!volume) {
				volume = 1;
				virtualVolume = (minVolume > volume) ? minVolume : volume;
				if (multiplier < 1) {
					kworkCount = Math.ceil(virtualVolume / multiplier);
					if (kworkCount > maxKworkCount) {
						virtualVolume = maxKworkCount * multiplier;
					}
				}
			}
			if (virtualVolume > maxKworkVolume) {
				virtualVolume = maxKworkVolume;
			}
			

			// Округляем стоимость кворка с фиксированным объемом кратно 50 руб. / $1
			price = kworkCostRound(price, virtualVolume, multiplier, kworkLang, 'view');
			$('.package-' + packageType + '-price_value').html(Utils.numberFormat(price, 0, delimiter, " "));
			
			// Показываем время на выполнение для пакетов
			if(duration !== undefined) {
				var totalTime = getVolumedDuration(duration, multiplier, virtualVolume, 'view');
				$('.package-' + packageType + '-duration').html(totalTime + ' ' + declension(totalTime, t('день'), t('дня'), t('дней')));
			}
		}
	}

	$('#kworkcntside').val(kworkCount).trigger("chosen:updated");
	$('#kwork_count_mobile').val(kworkCount);
	$('#kworkcnt').val(kworkCount).change();
}

function recalculatePrice(place, preCalc) {
	if (isPackage) {
		return;
	}

	if (place !== 'side') {
		place = '';
	}

	var $kworkCountElement = $("#kworkcnt" + place);
	var $kworkCountMobileElement = $('#kworkcntmobile');
	var count = $kworkCountElement.val() ? $kworkCountElement.val() : 1;

	if ($kworkCountMobileElement.is(":visible") && place.length === 0) {
		count = parseInt($kworkCountMobileElement.val()) ? parseInt($kworkCountMobileElement.val()) : 1;
	}

	var totalPrice = price * count;

	if ($("input[name='is_quick']").prop("checked")) {
		totalPrice *= 2;
	}

	if (!preCalc) {
		var totalPriceString = Utils.priceFormat(totalPrice, kworkLang);
		var totalPriceStringLength = totalPriceString.toString().length;
		var orderButton = $('#newextform' + (place ? place : "side") + ' a.order-block-cart_btn');
		if (totalPriceStringLength === 6) {
			orderButton.addClass("f18-1050");
		}
		else if (totalPriceStringLength > 6) {
			orderButton.addClass("f18");
		} else {
			orderButton.removeClass("f18 f18-1050w");
		}

		$('.newordext').html(totalPriceString);

		if ($kworkCountMobileElement.is(":visible") && place.length === 0) {
			count = $kworkCountMobileElement.val();
		} else {
			$kworkCountMobileElement.val(count);
		}

		$('#kworkcnt').val(count).trigger("chosen:updated");
		$('#kworkcntside').val(count).trigger("chosen:updated");
	}
}

function recalculateQuick(totalPrice, totalTime) {
}

function PopUpShow() {
	$("#popup1").show();
}

function PopUpHide() {
	$("#popup1").hide();
}

function checkQuickPackage(packageType, check, quickPrice, price) {
}
function make_order() {
	if (isMakeOrder) {
		return false;
	}
	isMakeOrder = true;

	var formData = checkKworkFormData(jQuery('#newextformside'));

	$.ajax({
		type: "POST",
		url: '/api/order/create',
		data: formData,
		dataType: 'json',
		success: function (response) {
			if (response.dataLayer) {
				GTM.pushDataLayers(response.dataLayer);
			}

			if (response.success === true)
			{
				$('.js-make-order').attr('disabled', 'disabled').addClass('make-order-disable');
				$('.js-make-order').removeAttr('href');

				setTimeout(function () {
					location.href = response.redirect;
				}, 1000);
			} else {
				if (response.error === "purse"){
					show_balance_popup(response.purse_amount, 'kwork', undefined, response.orderId);
				}
				isMakeOrder = false;
			}
		},
		error: function() {
			isMakeOrder = false;
		}
	});
}
function make_package_order(kworkId, type, isCart, obj, callback) {
	if(typeof callback === "undefined") {
		callback = function () {}
	}
	if (isMakeOrder) {
		return false;
	}
	isMakeOrder = true;
	if (typeof obj === 'undefined') {
		var isQuick = $(".package_card_quick-option__value").find("input[name='" + type + "_duration']:checked").val();
	} else {
		var isQuick = $("#package_" + type + "_quick").is(':checked');
	}
	var data = {
		kworkId: kworkId,
		packageType: type,
		isCart: isCart,
		isQuick: isQuick ? '1' : '',
		user_csrf: $("input[name=user_csrf]").val(),
	};

	var $volumeInput = $('.js-volume-order[data-package-type="' + type + '"]');
	if ($volumeInput.length && $volumeInput.val()) {
		data.volume = $volumeInput.val().replace(/[\s]/gim, '');
	} else {
		data.volume = 1;
	}
	
	// Проверяем на минимальный объем
	var minVolume = parseFloat($volumeInput.attr('data-min-volume'));
	if (!isNaN(minVolume) && minVolume > data.volume) {
		data.volume = minVolume;
	}

	var additionalVolumeType = jQuery('.js-additional-volume-types');
	if (additionalVolumeType.length) {
		data.additional_volume_type_id = additionalVolumeType.val();
	}

	if (!isCart && CartModule !== undefined) {
		CartModule.preventNextShow();
	}
	$.ajax({
		type: "POST",
		url: '/api/order/packagecreate',
		data: data,
		success: function (result) {
			if (result.dataLayer) {
				GTM.pushDataLayers(result.dataLayer);
			}

			if (result.success) {
				callback();
				if (isCart) {
					isMakeOrder = false;
					return true;
				}

				setTimeout(function () {
					window.location.replace(result.redirect);
				}, 1000);
			} else {
				callback();
				if (result.error == 'purse') {
					if (USER_ID) {
						// Если пользователь авторизован сразу пополнение баланса показывам
						show_balance_popup(result.purse_amount, 'kwork', undefined, result.orderId);
					} else {
						// Если он сразу после быстрой регистрации то редирект делаем чтобы страница у него обновилась
						setTimeout(function () {location.replace(result.redirect); }, 1000);
						return true;
					}
				} else {
					show_message('error', t('Произошла ошибка. Пожалуйста, попробуйте еще раз.'));
				}
				isMakeOrder = false;
			}
		},
		error: function(){
			isMakeOrder = false;
		}
	});
}

function init_package_tabs() {
	var packageTabs = $('.package_tabs>*');
    if (packageTabs && packageTabs.length && packageTabs.is(':visible')){
		var mediumItem = $('.package_tabs>div[data-type="medium"]');
		var parent = mediumItem.closest('.b-package');
		$('.package_tabs .active').removeClass('active');
		mediumItem.addClass('active');
		parent.removeClass('show-standard show-premium').addClass('show-medium')
    }
}

//попап с причинами и пояснениями модерации кворка
function showKworkRejectedReasonspopup() {
	var _rejectReasons = rejectReasons;

	var reasons = '';
	for (var i in _rejectReasons) {
		if (_rejectReasons[i].description.length > 0) {
			reasons += '<li class="moder-reasons-list__item">';
			if (_rejectReasons[i].name_user.length > 0) {
				reasons += _rejectReasons[i].name_user + '<br>';
			}
			reasons += _rejectReasons[i].description + '<br>';
			reasons += '</li>';
		} else if(_rejectReasons[i].name_user.length > 0) {
			reasons += '<li class="moder-reasons-list__item">';
			reasons += _rejectReasons[i].name_user;
			reasons += '</li>';
		}
	}

	var content = '' +
		'<h1 class="popup__title">' + t('Почему и как исправить?') + '</h1>'+
		'<hr class="gray mt20">'+
		'<div>' +
		'<ul class="moder-reasons-list">' +
		reasons +
		'</ul>' +
		'</div>';
	show_popup(content, 'popup_kwork-rejected-reasons');
}

function initSlider(isMobile, kworkSliderBaseHtml) {
	var $kworkSliderMobile = $('.kwork_slider_mobile');
	var $kworkSliderDesktop = $('.kwork_slider_desktop');

	if (isMobile) {
		$kworkSliderDesktop.hide();

		if (!$kworkSliderMobile.find('.sliderImage').length) {
			$kworkSliderMobile.html(kworkSliderBaseHtml);

			$kworkSliderMobile.find('.kwork-slider').attr('id', 'slider_mobile');

		}

		if (!slideMobileModule) {
			initMobileSlider();
		}

		$('.kwork_slider_mobile_counter').show();

		$kworkSliderMobile.show();
	} else {
		$kworkSliderMobile.hide();

		if (!$kworkSliderDesktop.find('.sliderImage').length) {
			$kworkSliderDesktop.html(kworkSliderBaseHtml);

			$kworkSliderDesktop.find('.kwork-slider').attr('id', 'slider_desktop');
		}

		if (!slideMobileModule) {
			initDesktopSlider();
		}

		$('.kwork_slider_mobile_counter').hide();

		$kworkSliderDesktop.show();
	}
}

function initMobileSlider() {
	slideMobileModule = new SlideModule();

	slideMobileModule.init({
		sliderName: '.kwork_slider_mobile .kwork-slider',
		settings: {
			lazyLoad: 'ondemand',
			slidesToShow: 1,
			slidesToScroll: 1,
			dots: false,
			arrows: false,
			infinite: true,
			sliderCount: true,
			adaptiveHeight: true,
			mode: 'mobile',
			speed: 100
		}
	});
}

function initDesktopSlider() {
	slideDesktopModule = new SlideModule();

	slideDesktopModule.init({
		sliderName: '.kwork_slider_desktop .kwork-slider',
		settings: {
			accessibility: false,
			lazyLoad: 'ondemand',
			slidesToShow: 1,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			infinite: true,
			speed: 100,
			fade: true,
			cssEase: 'linear',
			mode: 'desktop'
		}
	});
}

jQuery(function() {
	var portfolioCount = jQuery('#slider_desktop, #slider_mobile').find('.sliderItem[data-portfolio-id]').length;

	/**
	 * Открытие карточки просмотра работы
	 */
	function openCardPortfolio($el) {
		if (!$el.length) {
			return false;
		}

		var kworkId = $el.closest('[data-kwork-id]').data('kworkId');
		var potfolioId = $el.data('portfolioId');

		if (potfolioId) {
			portfolioCard.getPortfolio(potfolioId);
		} else {
			if (portfolioCount) {
				portfolioCard.getFirstPortfolio(kworkId);
			}
		}
	};

	jQuery('body').on('click touch', '.kwork_slider__zoom', function() {
		var $parent = jQuery(this).closest('div');
		var $slider = $parent.find('#slider_desktop, #slider_mobile');
		var $el = $slider.find('.slick-active');

		if (!$slider.hasClass('isShowPortfolio')) {
			return;
		}

		openCardPortfolio($el);
	});
	jQuery('#slider_desktop, #slider_mobile').on('click', '.sliderItem', function(e) {
		if ($(e.target).parents('.portfolio-kwork_mini_review').length) {
            $(e.target).parents('.portfolio-kwork_mini_review').toggleClass('hover');
			return;
		}

		var $el = jQuery(this);
		var $slider = $el.closest('#slider_desktop, #slider_mobile');

		if (!$slider.hasClass('isShowPortfolio')) {
			return;
		}

		openCardPortfolio($el)
	});

	//открываем попап с причинами и пояснениями модерации кворка
	$('.js-kwork-rejected-reasons').on('click', function () {
		showKworkRejectedReasonspopup();
	});

	jQuery('.js-additional-volume-types')
		.chosen({width: '80px', disable_search: true})
		.on('change', function () {
			jQuery('#js-kwork-view')
				.find('.js-additional-volume-types')
				.val(jQuery(this).val())
				.trigger('chosen:updated');

			var _this = jQuery(this),
				additionalTime = _this.find('option:selected').data('additionalTime'),
				volumeOrder = jQuery('.js-volume-order'),
				volumeMultiplier,
				minVolume;

			volumeOrder.each(function () {
				var _that = jQuery(this),
					volumeMultiplierDefault = _that.data('volumeMultiplierDefault'),
					minVolumeDefault = _that.data('minVolumeDefault'),
					maxCountDefault = _that.data('maxCountDefault'),
					maxCount = _that.data('maxCount'),
					count = _that.val() ? _that.val() : 1,
					maxVolume,
					placeholder,
					volume,
					volumeDefault;

				if (additionalTime > 0) {
					volumeMultiplier = getMaxVolumeInKworkType(_this, volumeMultiplierDefault);
					minVolume = getMaxVolumeInKworkType(_this, minVolumeDefault);
				} else {
					volumeMultiplier = volumeMultiplierDefault;
					minVolume = minVolumeDefault;
					maxCount = maxCountDefault;
				}
				maxVolume = Math.floor(volumeMultiplier * maxCount) || 1;
				
				volume = (minVolume > volumeMultiplier) ? minVolume : volumeMultiplier;
				
				volumeDefault = (minVolumeDefault > volumeMultiplierDefault) ? minVolumeDefault : volumeMultiplierDefault;
				
				
				placeholder = additionalTime > 0 ? (Math.ceil(volume) || 1) : volumeDefault;
				
				_that
					.attr('data-min-volume', minVolume)
					.attr('data-volume-multiplier', volumeMultiplier)
					.attr('data-max', maxVolume)
					.attr('placeholder', placeholder);
					kworkCount(_that, false);
			});
		});
});
