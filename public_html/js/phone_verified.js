/**
 * Открытие модального окна на "Верификация продавцов по мобильному телефону"
 * @param {*} successCallback 
 */
var phoneVerifiedOpenModal = function(successCallback) {
	var $modal = jQuery('.js-popup-phone-verified');

	$modal.modal('show');

	if (successCallback) {
		window.phoneVerifiedSuccessCallback = successCallback;
	}
};

/**
 * Выслать sms код
 */
var sendVerifyCode = function(_el) {
	// if (!phoneVerifyEnable) {
	// 	return false;
	// }

	var $el = jQuery(_el);
	var $modal = $el.closest('.js-popup-phone-verified');

	$modal.find('.phone-verify-error-block').addClass('hidden');
	$modal.find('.phone-verify-code-error').addClass('hidden');

	var popup_phone = $modal.find('[name="popup_phone"]').val();

	$.ajax({
			type: "post",
			data: {
				popup_phone: popup_phone,
			},
			url: '/api/user/sendphoneverifycode',
			success: function(data, textStatus, xrf) {
				var response = data;

				if (response.result === 'success') {
					$modal.find('.phone-verify-error-block').addClass('hidden');
                    $modal.find('.js-verified-control_wrapper').addClass('is-send');
				} else {
					if (response.error === 'wait') {
                        $modal.find('.phone-verify-error-block').html(response.error_text).removeClass('hidden');
						_phoneVerifyStartTimeLeftTimer(response.time_left, $modal);
					} else {
						if (response.error === "sms_apptemps_exceed") {
							$el.addClass("hidden");
                            $modal.find('.js-verified-control_wrapper').removeClass('is-send');
						}
						$modal.find('.phone-verify-error-block').html(response.error_text).removeClass('hidden');
					}
				}
			}
	});
};

/**
 * Подтвердить код 
 */
var checkVerifyCode = function(_el) {
	var $el = jQuery(_el);
	var $modal = $el.closest('.js-popup-phone-verified');

	$modal.find('.phone-verify-code-error').addClass('hidden');
	$.ajax({
		type: "post",
		data: {
			verify_code: $modal.find('[name="confirm_code"]').val()
		},
		url: '/api/user/checkverifycode',
		success: function(data, textStatus, xrf) {
			var response = data;

			if (response.result === 'success') {
				$modal.find('.verified-step-1').addClass('hidden');
				$modal.find('.verified-step-2').removeClass('hidden').find('.phone-verify-number').text(response.phone);

				if (window.phoneVerifiedSuccessCallback) {
					setTimeout(function() {
						window.phoneVerifiedSuccessCallback();
					}, 2000);
				}
			} else {
				$modal.find('.phone-verify-code-error').removeClass('hidden').html(response.error);
				if (response.error_type === 'apptempts_exceed') {
					$el.addClass('bold');
					$modal.find('.verified-control-confirm').addClass('hidden');
				}
			}
		}
	});
};

var phoneVerifyIntervalId;
var phoneVerifyEnable = true;
var _phoneVerifyStartTimeLeftTimer = function(timeLeft, $modal) {
	clearInterval(phoneVerifyIntervalId);
	phoneVerifyEnable = false;
	phoneVerifyIntervalId = setInterval(function(){
		$modal.find('.phone-verify-error').find('.phone-verify-error-timer').text(timeLeft);
		timeLeft--;

		if (timeLeft < 1) {
			clearInterval(phoneVerifyIntervalId);
			phoneVerifyEnable = true;
			$modal.find('.phone-verify-error').addClass('hidden');
		}
	}, 1000);
}

$(function () {
    var maskList = $.masksSort($.masksLoad("/js/phone-codes.json?v=1571299078"), ['#'], /[0-9]|#/, "mask");
    var flagsPath = "/images/flags/";
    var defaultCountry = t('Россия');
    var maskOpts = {
        inputmask: {
            definitions: {
                '#': {
                    validator: "[0-9]",
                    cardinality: 1
                }
            },
            showMaskOnHover: true,
            autoUnmask: true,
            placeholder: " ",
        },
        match: /[0-9]/,
        replace: '#',
        list: maskList,
        listKey: "mask",
        onMaskChange: function(maskObj, completed) {
            if (completed) {
                var hint = maskObj.name_ru;
                var countryCode = maskObj.cc;
                if(countryCode) {
                    $('.js-country-flag').css('background-image', 'url('+ flagsPath + countryCode.toLowerCase() + '.png)');
                    $('.js-country-flag').attr('title', hint);
				}
            } else {
            	if($(this).val()=='7') {
                    $('.js-country-flag').css('background-image', 'url('+ flagsPath +'ru.png');
                    $('.js-country-flag').attr('title', defaultCountry);
            	}  else {
                    $('.js-country-flag').css('background-image', 'none');
                    $('.js-country-flag').removeAttr('title');
                }
            }
            $(this).attr("placeholder", $(this).inputmask("getemptymask"));
        }
    };
    $('#customer_phone').inputmasks(maskOpts);
    $('#customer_phone').blur(function() {
    	if($(this).val() === ""){
            $(this).val(7);
		}
    });
});