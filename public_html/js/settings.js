var SITE = SITE || {};

SITE.fileInputs = function () {

    var $this = $(this),
        $val = $this.val(),
        valArray = $val.split('\\'),
        newVal = valArray[valArray.length - 1],
        $button = $this.parents('.file-wrapper').find('.button'),
        $fakeFile = $this.parents('.file-wrapper').find('.file-holder span');
    if (newVal !== '') {
        var reader = new FileReader();
        $('.block-help-image-js').show();
        $button.text($('.file-wrapper label').attr('data-init-text'));
        if ($fakeFile.length === 0) {
            $button.parents('.file-wrapper').find('.file-wrapper-block').append('<span class="file-holder"><span>' + newVal + '</span></span>');
        } else {
            $fakeFile.text(newVal);
        }
    }
};


$(document).on('click', '.js-solar-card-verify', function () {
	var html = $('.solar-card-verify-popup-content').html();
	show_popup(html, 'popup-card-confirm', '', 'popup-card-confirm', true);

	var popup = $(".popup.popup-card-confirm");
	popup.find('select[name="phone_country_code"]').chosen({width: '180px', disable_search: true});
	phoneVerifyEnable = true;

	popup.find("#popup_fname, #popup_lname").on("keydown", validateCardData);
	popup.find('select[name="phone_country_code"]').on("change", function () {
		popup.find("#popup_phone").trigger("input");
	});
	popup.find("#popup_phone").on("input", validatePhoneNumber);
	popup.find("#solar_card_full_number").on("input", validateCardFullNumber);
});
$(document).on('click', '.js-solar-card-reverify', function () {
    $('.solar-card-reverify-form').submit();
});

$(document).on('click', '.js-phone-verify', function () {
    if (!phoneVerifyEnable) {
        return false;
    }
    var that = this;
    $(this).closest('#form-card-verify').find('.phone-verify-error-block').addClass('hidden');
    $(this).closest('#form-card-verify').find('.phone-verify-code-error').addClass('hidden');

    var popup_phone =  $(this).closest('#form-card-verify').find('#popup_phone').val().trim();
    var phone_country_code = $(this).closest('#form-card-verify').find('#popup_phone_country_code').val().trim();

    if(!isPhoneNumberValid(phone_country_code, popup_phone)) {
		phoneVerifyShowErrorMessage(t('Введите корректный номер телефона'), that);
		return false;
	}

    $.ajax( {
        type: "post",
        data: {
            popup_phone: popup_phone,
            phone_country_code: phone_country_code
        },
        url: '/api/user/sendphoneverifycode',
        success: function( data, textStatus, xrf ) {
            $(that).closest('#form-card-verify').find('.phone-verify-link').addClass('mt-2').text(t('Выслать SMS еще раз'));
            if (data.result === 'success') {
                phoneVerifyShowCodeField(that);
            } else {
                if (data.error === 'wait') {
                    phoneVerifyTimeLeftErrorMessage(data.error_text, that);
                    phoneVerifyStartTimeLeftTimer(data.time_left, that);
                } else {
                	if(data.error === "sms_apptemps_exceed") {
                        $(that).closest("#form-card-verify").find(".js-phone-verify").addClass("hidden");
                        $(that).closest("#form-card-verify").find(".phone-verify-code-block__input").addClass("hidden");
					}
                    phoneVerifyShowErrorMessage(data.error_text, that);
                }
            }
        }
    } );
});
$(document).on('click', '.js-phone-code-check', function () {
    var that = this;
    $(this).closest('#form-card-verify').find('.phone-verify-code-error').addClass('hidden');
    $.ajax( {
        type: "post",
        data: {
            verify_code: $(this).closest('#form-card-verify').find('#popup_confirm_code').val()
        },
        url: '/api/user/checkverifycode',
        success: function( data, textStatus, xrf ) {
            if (data.result === 'success') {
                $('.js-phone-code-check').closest('#form-card-verify').find('.phone-verify-block1').addClass('hidden');
                $('.js-phone-code-check').closest('#form-card-verify').find('.phone-verify-block2').removeClass('hidden').find('.phone-verify-number').text(data.phone);
                $(that).closest('#form-card-verify').find('.phone-verify-block2').find('.phone-verify-autoredirect-message').removeClass('hidden');
                phoneVerifyStartAutoRedirectTimer(5, that);
            } else {
                $(that).closest('#form-card-verify').find('.phone-verify-code-error').removeClass('hidden').html(data.error);
                if (data.error_type === 'apptempts_exceed') {
                    $(that).closest('#form-card-verify').find('.phone-verify-link').addClass('bold');
                    $(that).closest('#form-card-verify').find('.phone-verify-code-block__input').addClass('hidden');
                }
            }
        }
    } );
});

$(document).on('submit', '#form-card-verify', function (e) {
    var that = this;
	var formDisabled = $(that).data("disabled");
    if (formDisabled === true) {
        e.preventDefault();
        return false;
    }

	var fnameVal = $(that).find('#popup_fname').val();
	var lnameVal = $(that).find('#popup_lname').val();
	if (!fnameVal.length || !lnameVal.length) {
		e.preventDefault();
		$(that).find('.error-block').removeClass('hidden').html('Введите имя и фамилию');

		return false;
	}

    var numberCardVal = $(that).find('#solar_card_full_number').val();
    if (numberCardVal.length < 16 || numberCardVal.length > 20) {
		e.preventDefault();
		$(that).find('.error-block').removeClass('hidden').html('Укажите полный номер карты, от 16 до 20 цифр.');

		return false;
	}

    $(that).data("disabled", true);
    $.ajax( {
        type: "post",
        data: $(that).serialize(),
        url: '/settings_card_verify',
        success: function( response, textStatus, xrf ) {
            if (response.result === true) {
                location.href = response.redirect_url;
            } else {
                $(that).data("disabled", false);
                if (response.error) {
					$(that).find('.error-block').removeClass('hidden').html(response.error);
                } else if (response.errors) {
                    for (var i = 0; i < response.errors.length; i++) {
                        var error = response.errors[i];
                        if (error.target !== '') {
                            $(that).find('.' + error.target).removeClass('hidden').html(error.text);
                        }
                    }
                }
            }
        }
    } );
    e.preventDefault();
    return false;
});
$(document).on('focus', '.phone-verify-code', function(){
    $(this).closest('.phone-verify-code-block').find('.phone-verify-code-error').addClass('hidden');
});
$(document).on('focus', '#popup_fname, #popup_lname', function(){
    $(this).closest('#form-card-verify').find('.fio-error-block').addClass('hidden');
});
$(document).on('focus', '#popup_fname, #popup_lname, #solar_card_full_number', function(){
    $(this).closest('#form-card-verify').find('.error-block').addClass('hidden');
});
$(document).on('focus', '#popup_phone', function(){
    $(this).closest('#form-card-verify').find('.phone-verify-error-block').addClass('hidden');
});
var phoneVerifyIntervalId;
var phoneVerifyEnable = true;
function phoneVerifyStartTimeLeftTimer(timeLeft, button)
{
    clearInterval(phoneVerifyIntervalId);
    phoneVerifyEnable = false;
    phoneVerifyIntervalId = setInterval(function(){
        $(button).closest('#form-card-verify').find('.phone-verify-error').find('.phone-verify-error-timer').text(timeLeft);
        timeLeft--;
        if (timeLeft < 1) {
            clearInterval(phoneVerifyIntervalId);
            phoneVerifyEnable = true;
            $(button).closest('#form-card-verify').find('.phone-verify-error').addClass('hidden');
        }
    }, 1000);
}
function phoneVerifyStartAutoRedirectTimer(time, button)
{
    var intervalId = setInterval(function(){
        $(button).closest('#form-card-verify').find('.phone-verify-block2').find('.phone-verify-redirect-timer').text(time);
        time--;
        if (time < 1) {
            clearInterval(intervalId);
            $(button).closest('#form-card-verify').find('.phone-verify-block2').find('.phone-verify-autoredirect-message').addClass('hidden');
            $(button).closest('#form-card-verify').submit();
        }
    }, 1000);
}
function phoneVerifyTimeLeftErrorMessage(message, button)
{
    $(button).closest('#form-card-verify').find('.phone-verify-error').html(message).removeClass('hidden');
}
function phoneVerifyShowCodeField(button)
{
    $(button).closest('#form-card-verify').find('.phone-verify-error-block').addClass('hidden');
    $(button).closest('#form-card-verify').find('.phone-verify-code-block').removeClass('hidden');
    $(button).closest('#form-card-verify').find('.phone-verify-code-block__input').removeClass('hidden');
}
function phoneVerifyShowErrorMessage(message, button)
{
    $(button).closest('#form-card-verify').find('.phone-verify-error-block').html(message).removeClass('hidden');
}

function validatePhoneNumber() {
	//Удаляем все нежелательные символы сразу при вводе
	var phone_country_code = $(this).closest('#form-card-verify').find('#popup_phone_country_code').val().trim();
	var phone = $(this).val();
	var validPhone = phone.replace(/[^0-9]/gim, '');
	if(phone_country_code === '7') {
		//для России не даём ввести больше 10 символов
		validPhone = validPhone.substr(0, 10);
	}
	$(this).val(validPhone);
}

function validateCardFullNumber() {
	//Удаляем все нежелательные символы сразу при вводе
	var number = $(this).val();
	var validateNumber = number.replace(/[^0-9]/g, '');
	validateNumber = validateNumber.substr(0, 20);

	$(this).val(validateNumber);
}

function validateCardData(e) {
	if(actor_lang === "en") {
		// разрешаем вводить только латиницу некоторые символы
		if(e.key.match(/[^A-z\s-]/gi)) {
			return false;
		}
	}
}

function isPhoneNumberValid(contryCode, pnoneNumber) {
	var valid = true;
	contryCode = contryCode.replace(/[^0-9]/gim, '');
	pnoneNumber = pnoneNumber.replace(/[^0-9]/gim, '');
	var clearNumber = contryCode + pnoneNumber;
	if(contryCode === '7' && pnoneNumber.length !== 10 ) {
		valid = false;
	} else if(clearNumber.length < 10 || clearNumber.length > 20) {
		valid = false;
	}
	return valid;
}

//Проверка введенных данных
var SettingCheckersModule = (function() {
	var _usernameInput;
	var _usernameErrorField;
	var _nameInput;
	var _lastnameInput;
	var _nameErrorField;
	var _lastnameErrorField;
    var _nameEnInput;
    var _lastnameEnInput;
	var _nameEnErrorField;
	var _lastnameEnErrorField;
	var _emailInput;
	var _emailErrorField;

	var _passwordOldInput;
	var _passwordInput;
	var _passwordErrorField;
	var _passwordOldErrorField;
	var _passwordConfirmInput;
	var _passwordConfirmErrorField;

	var _webmoneyInput;
	var _webmoneyErrorField;
	var _yandexInput;
	var _yandexErrorField;
	var _qiwiInput;
	var _qiwiErrorField;
	var _cardInput;
	var _cardErrorField;

	var _mainSettingsForm;
	var _profileSettingsForm;
	var _paymentsSettingsForm;
	var _paymentsSettingsInput;
	var _paymentsSettingsSubmit;
	var _currentValuePaymentsSettings;

	var _detailsEnInput;
	var _detailsEnErrorField;

	var _detailsDefaultInput;
	var _detailsDefaultErrorField;

	var _serverSideCheckingMainInProgress;
	var _serverSideCheckingPasswordInProgress;
	var _serverSideCheckingPaymentsInProgress;

	var _mainSettingsNeedSubmit;
	var _passwordSettingsNeedSubmit;
	var _paymentsSettingsNeedSubmit;

	var _isUsernameError;
	var _isNameError;
	var _isNameEnError;
	var _isEmailError;
	var _isPasswordError;
	var _isPasswordOldError;
	var _isPasswordConfirmError;
	var _isWebmoneyError;
	var _isQiwiError;
	var _isYandexError;
	var _isCardError;
	var _isDetailsEnError;
	var _isDetailsDefaultError;

	var _init = function(data) {
		_usernameInput = document.querySelector(data["usernameInput"]);
		_usernameErrorField = document.querySelector(data["usernameErrorField"]);
		_nameInput = document.querySelector(data["nameInput"]);
        _lastnameInput = document.querySelector(data["lastnameInput"]);
		_nameErrorField = document.querySelector(data["nameErrorField"]);
		_lastnameErrorField = document.querySelector(data["lastnameErrorField"]);
        _nameEnInput = document.querySelector(data["nameEnInput"]);
        _lastnameEnInput = document.querySelector(data["lastnameEnInput"]);
		_nameEnErrorField = document.querySelector(data["nameEnErrorField"]);
		_lastnameEnErrorField = document.querySelector(data["lastnameEnErrorField"]);
		_emailInput = document.querySelector(data["emailInput"]);
		_emailErrorField = document.querySelector(data["emailErrorField"]);
		_mainSettingsForm = document.querySelector(data["mainSettingsForm"]);
		_profileSettingsForm = document.querySelector(data["profileSettingsForm"]);

		_passwordInput = document.querySelector(data["passwordInput"]);
		_passwordErrorField = document.querySelector(data["passwordErrorField"]);
		_passwordOldInput = document.querySelector(data["passwordOldInput"]);
		_passwordOldErrorField = document.querySelector(data["passwordOldErrorField"]);
		_passwordConfirmInput = document.querySelector(data["passwordConfirmInput"]);
		_passwordConfirmErrorField = document.querySelector(data["passwordConfirmErrorField"]);

		_webmoneyInput = document.querySelector(data["webmoneyInput"]);
		_webmoneyErrorField = document.querySelector(data["webmoneyErrorField"]);
		_yandexInput = document.querySelector(data["yandexInput"]);
		_yandexErrorField = document.querySelector(data["yandexErrorField"]);
		_qiwiInput = document.querySelector(data["qiwiInput"]);
		_qiwiErrorField = document.querySelector(data["qiwiErrorField"]);
		_cardInput = document.querySelector(data["cardInput"]);
		_cardErrorField = document.querySelector(data["cardErrorField"]);
		_paymentsSettingsForm = document.querySelector(data["paymentsSettingsForm"]);
		_paymentsSettingsInput = $(data["paymentsSettingsInput"]);
		_paymentsSettingsSubmit =$(data["paymentsSettingsSubmit"]);

		_detailsEnInput = document.querySelector(data["detailsEnInput"]);
		_detailsEnErrorField = document.querySelector(data["detailsEnErrorField"]);

		_detailsDefaultInput = document.querySelector(data["detailsDefaultInput"]);
		_detailsDefaultErrorField = document.querySelector(data["detailsDefaultErrorField"]);

		_serverSideCheckingMainInProgress = false;
		_serverSideCheckingPasswordInProgress = false;
		_serverSideCheckingPaymentsInProgress = false;

		_mainSettingsNeedSubmit = false;
		_passwordSettingsNeedSubmit = false;
		_paymentsSettingsNeedSubmit = false;

		_isUsernameError = false;
		_isNameError = false;
        _isNameEnError = false;
		_isEmailError = false;
		_isPasswordError = false;
		_isPasswordOldError = false;
		_isPasswordConfirmError = false;
		_isWebmoneyError = false;
		_isQiwiError = false;
		_isCardError = false;
		_isDetailsEnError = false;
		_isDetailsDefaultError = false;

		_currentValuePaymentsSettings = [];

		_setEvents();

		_setCurrentValuePaymentsSettings();
	};
	var _setEvents = function() {
		_usernameInput.addEventListener("input", _checkUsername);
		_usernameInput.addEventListener("focusout", _serverSideCheckUsername);
		_emailInput.addEventListener("input", _checkEmail);
		_emailInput.addEventListener("focusout", _serverSideCheckEmail);
		if(_nameInput) {
			_nameInput.addEventListener("input", _checkName);
		}
        if(_lastnameInput) {
            _lastnameInput.addEventListener("input", _checkLastname);
        }
		if(_nameEnInput) {
			_nameEnInput.addEventListener("input", _checkNameEn);
		}
        if(_lastnameEnInput) {
            _lastnameEnInput.addEventListener("input", _checkLastnameEn);
        }
		if(_detailsEnInput) {
			_detailsEnInput.addEventListener("input", _checkDetailsEn);
			_detailsEnInput.addEventListener("change", _changeDetailsEn);
			_checkDetailsEn();
		}

		if (_detailsDefaultInput) {
            _detailsDefaultInput.addEventListener("focusout", _checkDetailsDefault);
			_detailsDefaultInput.addEventListener("input", _checkDetailsDefault);
            _checkDetailsDefault();
		}

		_passwordInput.addEventListener("input", _checkPasswords);
		_passwordConfirmInput.addEventListener("input", _checkPasswords);
		_passwordInput.addEventListener("focusout", _serverSideCheckPasswords);
		_passwordConfirmInput.addEventListener("focusout", _serverSideCheckPasswords);

		if (_webmoneyInput != undefined) {
			_webmoneyInput.addEventListener("input", _checkWebmoney);
			_webmoneyInput.addEventListener("focusout", _serverSideCheckWebmoney);			
		}
		if (_qiwiInput != undefined) {
			_qiwiInput.addEventListener("input", _checkQiwi);
			_qiwiInput.addEventListener("focusout", _serverSideCheckQiwi);
		}
		if (_yandexInput != undefined) {
			_yandexInput.addEventListener("input", _checkYandex);
			_yandexInput.addEventListener("focusout", _serverSideCheckYandex);
		}
		if (_cardInput != undefined) {
			_cardInput.addEventListener("input", _checkCard);
		}

		_mainSettingsForm.onsubmit = _mainSettingsSubmitted;
		_profileSettingsForm.onsubmit = _profileSettingsSubmitted;
		_paymentsSettingsForm.onsubmit = _paymentsSettingsSubmitted;

		_paymentsSettingsInput.on('input', _paymentsSettingsInputChange);
	};

	/**
	 * Установить текущие значения (номера) финансовых карт
	 */
	var _setCurrentValuePaymentsSettings = function () {
		_paymentsSettingsInput.each(function (k, v) {
			_currentValuePaymentsSettings.push({
				'name': $(v).attr('name'),
				'value': $(v).val(),
			});
		});
	};

	var _mainSettingsSubmitted = function() {
		if(_isUsernameError || _isEmailError || _serverSideCheckingMainInProgress || _isPasswordError || _isPasswordConfirmError) {
			if(_serverSideCheckingMainInProgress) _mainSettingsNeedSubmit = true;
			return false;
		}
		if (_passwordInput.value && _passwordConfirmInput.value) {
			_serverSideChangePasswords();
			return false;
		}
		return true;
	};

    var _profileSettingsSubmitted = function() {
        if (_detailsEnInput) {
            _changeDetailsEn();
        }
        if (_detailsDefaultInput) {
            _checkDetailsDefault();
        }
        if(_isNameError || _isNameEnError ||  _isDetailsEnError || _serverSideCheckingMainInProgress || _isDetailsDefaultError) {
            _scrollToError("main"); return false;
        }
        return true;
    };

	var _paymentsSettingsSubmitted = function() {
		if(_isWebmoneyError || _isQiwiError|| _isYandexError || _isCardError || _serverSideCheckingPaymentsInProgress) {
			if(_serverSideCheckingPaymentsInProgress) _paymentsSettingsNeedSubmit = true;
			_scrollToError("payments"); return false;
		}
		return true;
	};

	/**
	 * Отслеживаем изменения значений (номеров) финансовых карт
	 */
	var _paymentsSettingsInputChange = function () {

		var isChangeValue = false;
		$.each(_currentValuePaymentsSettings, function (k, v) {
			var filterInput = _paymentsSettingsInput.filter('[name="' + v.name + '"]');
			if (filterInput.length && filterInput.val() !== v.value) {
				isChangeValue = true;
				return false;
			}
		});

		if (isChangeValue && !_isQiwiError && !_isYandexError && !_isCardError) {
			_paymentsSettingsSubmit.removeClass('disabled').attr('disabled', false);
		} else {
			_paymentsSettingsSubmit.addClass('disabled').attr('disabled', true);
		}
	};

	var _setError = function(type, text) {
		switch(type) {
			case "field_username":
				_isUsernameError = true;
				_usernameErrorField.textContent = text;
				_usernameErrorField.classList.remove("hidden");
				break;
			case "field_email":
				_isEmailError = true;
				_emailErrorField.textContent = text;
				_emailErrorField.classList.remove("hidden");
				break;
			case "field_name":
				_isNameError = true;
				_nameErrorField.classList.remove("hidden");
				break;
            case "field_lastname":
                _isLastnameError = true;
                _lastnameErrorField.classList.remove("hidden");
                break;
            case "field_nameen":
				_isNameEnError = true;
				_nameEnErrorField.classList.remove("hidden");
				break;
            case "field_lastnameen":
                _isLastnameEnError = true;
                _lastnameEnErrorField.classList.remove("hidden");
                break;
			case "field_password":
				_isPasswordError = true;
				_passwordErrorField.textContent = text;
				$('#settings_form [type="submit"]').addClass('disabled');
				_passwordErrorField.classList.remove("hidden");
				break;
			case "field_old_pass":
				_isPasswordOldError = true;
				_passwordOldErrorField.textContent = text;
				_passwordOldErrorField.classList.remove("hidden");
				break;
			case "field_passwordConfirm":
				_isPasswordConfirmError = true;
				_passwordConfirmErrorField.textContent = text;
				_passwordConfirmErrorField.classList.remove("hidden");
				break;
			case "field_webmoney":
				_isWebmoneyError = true;
				_webmoneyErrorField.textContent = text;
				_webmoneyErrorField.classList.remove("hidden");
				break;
			case "field_qiwi":
				_isQiwiError = true;
				_qiwiErrorField.textContent = text;
				_qiwiErrorField.classList.remove("hidden");
				break;
			case "field_yandex":
				_isYandexError = true;
				_yandexErrorField.textContent = text;
				_yandexErrorField.classList.remove("hidden");
				break;
			case "field_card":
				_isCardError = true;
				_cardErrorField.textContent = text;
				_cardErrorField.classList.remove("hidden");
				break;
			case "field_detailsen":
				_isDetailsEnError = true;
				_detailsEnErrorField.textContent = text;
				_detailsEnErrorField.classList.remove("hidden");
				break;
			case "field_details_default":
				_isDetailsDefaultError = true;
				_detailsDefaultErrorField.textContent = text;
				_detailsDefaultErrorField.classList.remove("hidden");
				break;
		}
	};
	var _unsetError = function(type) {
		switch(type) {
			case "field_username":
				_isUsernameError = false;
				_usernameErrorField.textContent = "";
				_usernameErrorField.classList.add("hidden");
				break;
			case "field_email":
				_isEmailError = false;
				_emailErrorField.textContent = "";
				_emailErrorField.classList.add("hidden");
				break;
			case "field_name":
				_isNameError = false;
				_nameErrorField.classList.add("hidden");
				break;
            case "field_lastname":
                _isLastnameError = false;
                _lastnameErrorField.classList.add("hidden");
                break;
			case "field_nameen":
				_isNameEnError = false;
				_nameEnErrorField.classList.add("hidden");
				break;
            case "field_lastnameen":
                _isLastnameEnError = false;
                _lastnameEnErrorField.classList.add("hidden");
                break;
			case "field_password":
				_isPasswordError = false;
				_passwordErrorField.textContent = "";
				$('#settings_form [type="submit"]').removeClass('disabled');
				_passwordErrorField.classList.add("hidden");
				break;
			case "field_old_pass":
				_isPasswordOldError = false;
				_passwordOldErrorField.textContent = "";
				_passwordOldErrorField.classList.add("hidden");
				break;
			case "field_passwordConfirm":
				_isPasswordConfirmError = false;
				_passwordConfirmErrorField.textContent = "";
				_passwordConfirmErrorField.classList.add("hidden");
				break;
			case "field_webmoney":
				_isWebmoneyError = false;
				_webmoneyErrorField.textContent = "";
				_webmoneyErrorField.classList.add("hidden");
				break;
			case "field_qiwi":
				_isQiwiError = false;
				_qiwiErrorField.textContent = "";
				_qiwiErrorField.classList.add("hidden");
				break;
			case "field_yandex":
				_isYandexError = false;
				_yandexErrorField.textContent = "";
				_yandexErrorField.classList.add("hidden");
				break;
			case "field_card":
				_isCardError = false;
				_cardErrorField.textContent = "";
				_cardErrorField.classList.add("hidden");
				break;
			case "field_detailsen":
				_isDetailsEnError = false;
				_detailsEnErrorField.textContent = "";
				_detailsEnErrorField.classList.add("hidden");
				break;
			case "field_details_default":
				_isDetailsDefaultError = false;
				_detailsDefaultErrorField.textContent = "";
				_detailsDefaultErrorField.classList.add("hidden");
				break;
		}
	};
	var _scrollToError = function(settingsType) {
		var highestField = false;
		if (settingsType === "main") {
			if (!highestField && _isUsernameError) highestField = "field_username";
			else if (!highestField && _isNameError) highestField = "field_name";
			else if (!highestField && _isNameEnError) highestField = "field_nameen";
			else if (!highestField && _isEmailError) highestField = "field_email";
			else if (!highestField && _isDetailsDefaultError) highestField = "field_details_default";
			else if (!highestField && _isDetailsEnError) highestField = "field_detailsen";
			if (!highestField) highestField = "field_username";
		}
		else if (settingsType === "password") {
			if (!highestField && _isPasswordError) highestField = "field_password";
			else if (!highestField && _isPasswordConfirmError) highestField = "field_passwordConfirm";
			if (!highestField) highestField = "field_password";
		}
		else if(settingsType === "payments") {
			if(!highestField && _isWebmoneyError) highestField = "field_webmoney";
			else if(!highestField && _isQiwiError) highestField = "field_qiwi";
			else if(!highestField && _isYandexError) highestField = "field_yandex";
			else if(!highestField && _isCardError) highestField = "field_card";
			if(!highestField) highestField = "field_webmoney";
		}
		_scrollTo(highestField);
	};
	var _scrollTo = function(element) {
		var offset = 0;
		switch(element) {
			case "field_username":
				offset = $(_usernameInput).offset().top;
				break;
			case "field_name":
				offset = $(_nameInput).offset().top;
				break;
            case "field_lastname":
                offset = $(_lastnameInput).offset().top;
                break;
			case "field_nameen":
				offset = $(_nameEnInput).offset().top;
				break;
            case "field_lastnameen":
                offset = $(_lastnameEnInput).offset().top;
                break;
			case "field_email":
				offset = $(_emailInput).offset().top;
				break;
			case "field_password":
				offset = $(_passwordInput).offset().top;
				break;
			case "field_passwordConfirm":
				offset = $(_passwordConfirmInput).offset().top;
				break;
			case "field_webmoney":
				offset = $(_webmoneyInput).offset().top;
				break;
			case "field_qiwi":
				offset = $(_qiwiInput).offset().top;
				break;
			case "field_yandex":
				offset = $(_yandexInput).offset().top;
				break;
			case "field_card":
				offset = $(_cardInput).offset().top;
				break;
			case "field_detailsen":
				offset = $(_detailsEnInput).offset().top;
				break;
			case "field_details_default":
				offset = $(_detailsDefaultInput).offset().top;
				break;
		}
        $('html, body').animate({
            scrollTop: offset - 120
        }, 200);
	};
	var _checkUsername = function() {
            var username = _usernameInput.value;
            if (username === "") {
                _setError("field_username", t('Введите логин'));
                return false;
            }
            if (!username.match(/^[a-zA-Z0-9-_]*$/i)) {
                _setError("field_username", t('Логин может содержать только латинские буквы, цифры и знаки - и _'));
                return false;
            }
            if (username.length < 4) {
                _setError("field_username", t('Логин должен быть не короче 4-х символов'));
                return false;
            }
            if (username.length > userLoginLength) {
                _setError("field_username", t('Логин не может быть длиннее {{0}} символов', [userLoginLength]));
                return false;
            }
            _unsetError("field_username");
            return true;
	};
	var _serverSideCheckUsername = function() {
            _serverSideCheckingMainInProgress = true;
            if(!_checkUsername()) return false;
            $.post("/api/user/checksettingsusername", {username: _usernameInput.value}, function (response) {
                _serverSideCheckingMainInProgress = false;
			if (response.success === false) {
				_setError("field_username", response.message);
				return false;
			}
			else {
                _unsetError("field_username");
                if(_mainSettingsNeedSubmit) {
                	_mainSettingsForm.submit();
                    _mainSettingsNeedSubmit = false;
                }
				return true;
			}
		}, "json");
	};
	var _serverSideCheckEmail = function() {
		if(!_checkEmail()) return false;
		_serverSideCheckingMainInProgress = true;
        $.post("/api/user/checksettingsemail", {email: _emailInput.value}, function (response) {
            _serverSideCheckingMainInProgress = false;
			if (response.success === false) {
				_setError("field_email", response.message);
				return false;
			}
			else {
                _unsetError("field_email");
                if(_mainSettingsNeedSubmit) {
                	_mainSettingsForm.submit();
                    _mainSettingsNeedSubmit = false;
                }
				return true;
			}
		}, "json");
	};
	var _checkEmail = function() {
		var email = _emailInput.value.trim();
        if (email === "") {
            _setError("field_email", t('Введите E-Mail'));
            return false;
        }
        if(!validateEmail(email)) {
        	_setError("field_email", t('Адрес электронной почты указан некорректно'));
        	return false;
		}
		_unsetError("field_email");
        return true;
	};
	var _checkNameEn = function() {
		var enName = _nameEnInput.value;
		if(enName.length > 0){
			var regular = /[^a-zA-Z'\-— ]/g.exec(enName);
			_nameEnInput.value = enName.replace(regular, '');
			if(regular) {
				_setError("field_nameen");
				return false;
			}
			_unsetError("field_nameen");
		}
		return true;
	};
    var _checkLastnameEn = function() {
        var enLastname = _lastnameEnInput.value;
        if(enLastname.length > 0){
            var regular = /[^a-zA-Z'\-— ]/g.exec(enLastname);
            _lastnameEnInput.value = enLastname.replace(regular, '');
            if(regular) {
                _setError("field_lastnameen");
                return false;
            }
            _unsetError("field_lastnameen");
        }
        return true;
    };
	var _checkName = function() {
            var name = _nameInput.value;
            var res = false;
            if(actor_lang === 'en') {
                res = /[^a-zA-Z'\-— ]/g.exec(name);
            } else {
                res = /[^А-Яа-яЁё ]/g.exec(name);
            }
            _nameInput.value = name.replace(res, '');

            if (res) {
                _setError("field_name");
                return false;
            }
            _unsetError("field_name");
            return true;
	};

    var _checkLastname = function() {
        var lastname = _lastnameInput.value;
        var res = false;
        if(actor_lang === 'en') {
            res = /[^a-zA-Z'\-— ]/g.exec(lastname);
        } else {
            res = /[^А-Яа-яЁё ]/g.exec(lastname);
        }
        _lastnameInput.value = lastname.replace(res, '');

        if (res) {
            _setError("field_lastname");
            return false;
        }
        _unsetError("field_lastname");
        return true;
    };

	var _checkPasswords = function() {
		var password = _passwordInput.value;
		var passwordConfirm = _passwordConfirmInput.value;

        if(!password && !passwordConfirm) {
            _unsetError("field_password");
            _unsetError("field_passwordConfirm");
            return true;
        }
		if(!password) {
			_setError("field_password", t('Пожалуйста, введите новый пароль'));
            return false;
		}
		if(!passwordConfirm) {
			_setError("field_passwordConfirm", t('Подтвердите новый пароль'));
			return false;
		}
        if(password.length < 5) {
			_setError("field_password", t('Длина пароля должна быть не менее 5 символов'));
			return false;
		}
		if(passwordConfirm.length < 5) {
        	_setError("field_passwordConfirm", t('Длина пароля должна быть не менее 5 символов'));
            return false;
        }
        if(password !== passwordConfirm) {
            _setError("field_password", t('Пароль и подтверждение пароля не совпадают'));
            _setError("field_passwordConfirm", t('Пароль и подтверждение пароля не совпадают'));
            return false;
        }

        _unsetError("field_password");
		_unsetError("field_passwordConfirm");
		return true;
	};
	var _serverSideCheckPasswords = function() {
		if(!_checkPasswords()) return false;
		_serverSideCheckingPasswordInProgress = true;
		var password = _passwordInput.value;
		var passwordConfirm = _passwordConfirmInput.value;
        $.post("/api/user/checksettingspasswords", {pass1: password, pass2: passwordConfirm}, function (response) {
            _serverSideCheckingPasswordInProgress = false;
			if (response.success === false) {
                _setError("field_password", response.message);
                _setError("field_passwordConfirm", response.message);
                return false;
			}
			else {
				_unsetError("field_password");
				_unsetError("field_passwordConfirm");
                if(_passwordSettingsNeedSubmit) {
                    _passwordSettingsNeedSubmit = false;
                }
				return true;
			}
		}, "json");
	};
	var _serverSideChangePasswords = function() {
		var pass = _passwordInput.value,
			passwordConfirm = _passwordConfirmInput.value,
			oldPassword = _passwordOldInput.value,
			csrf = $("input[name=user_csrf]").val();
			submitBtn = $('#settings_form [type="submit"]');
		submitBtn.addClass('disabled').attr('disabled', true);
		_unsetError("field_old_pass");
        $.post("/settings_change_password", {password: pass, password_confirm: passwordConfirm, old_password:oldPassword, user_csrf: csrf}, function (response) {
			if (response.success === true) {
				$('#change-password-block, #change-pass-success').slideToggle();
				_passwordInput.value, _passwordOldInput.value, _passwordConfirmInput.value = '';
				submitBtn.removeClass('disabled').attr('disabled', false);
			}
			if (response.data[0].target) {
				_setError("field_old_pass", response.data[0].message);
				submitBtn.removeClass('disabled').attr('disabled', false);
			} else {
				submitBtn.addClass('disabled').attr('disabled', true);
			}
		}, "json");
	};
	var _checkWebmoney = function() {
		var webmoney = _webmoneyInput.value;
		var webmoneyPattern = /(R|P)[0-9]*/i;

		if(!webmoney.match(webmoneyPattern) && webmoney !== "") {
			_setError("field_webmoney", t('Введите данные в формате: Rxxxxxxxxxxxx или Pxxxxxxxxxxxx'));
			return false;
		}
		_unsetError("field_webmoney");
		return true;
	};
	var _serverSideCheckWebmoney = function() {
		if(!_checkWebmoney()) return false;
        _serverSideCheckingPaymentsInProgress = true;
		$.post("/api/user/checksettingspayments", {payment:"webmoney", purse:_webmoneyInput.value}, function(response) {
            _serverSideCheckingPaymentsInProgress = false;
			if(response.success === false) {
				_setError("field_webmoney", response.message);
				return false;
			}
			else {
				_unsetError("field_webmoney");
                if(_paymentsSettingsNeedSubmit) {
                	_paymentsSettingsForm.submit();
                    _paymentsSettingsNeedSubmit = false;
                }
				return true;
            }
		}, "json");
	};
	var _checkQiwi = function() {
		var qiwi = _qiwiInput.value;
        var qiwiPattern = /^[0-9]{11,14}$/i;
        if(!qiwi.match(qiwiPattern) && qiwi !== "") {
            _setError("field_qiwi", t('Введите данные в формате: от 11 до 14 цифр'));
            return false;
        }
        _unsetError("field_qiwi");
        return true;
	};
	var _serverSideCheckQiwi = function() {
		if(!_checkQiwi()) return false;
        _serverSideCheckingPaymentsInProgress = true;
		$.post("/api/user/checksettingspayments", {payment:"qiwi", purse:_qiwiInput.value}, function(response) {
            _serverSideCheckingPaymentsInProgress = false;
			if(response.success === false) {
				_setError("field_qiwi", response.message);
				return false;
			}
			else {
				_unsetError("field_qiwi");
                if(_paymentsSettingsNeedSubmit) {
                    _paymentsSettingsForm.submit();
                    _paymentsSettingsNeedSubmit = false;
                }
				return true;
			}
		}, "json");
	};
	var _checkYandex = function() {
		var yandex = _yandexInput.value;
		var yandexPattern = /^[0-9]{11,20}$/i,
		trueEndLength = yandex.length;
		if(!yandex.match(/^\d+$/)) { // проверяем на ввод цифры для граничной длины
			trueEndLength = yandex.length - 1;
		}
        if(!yandex.match(yandexPattern) && yandex !== "" && (yandex.length <= 11 || trueEndLength > 20)) {
			_setError("field_yandex", t('Введите данные в формате: от 11 до 20 цифр'));
			return false;
        }
        _unsetError("field_yandex");
        return true;
	};
	var _serverSideCheckYandex = function() {
		if(!_checkYandex()) return false;
        _serverSideCheckingPaymentsInProgress = true;
		$.post("/api/user/checksettingspayments", {payment:"yandex", purse:_yandexInput.value}, function(response) {
            _serverSideCheckingPaymentsInProgress = false;
			if(response.success === false) {
				_setError("field_yandex", response.message);
				return false;
			}
			else {
				_unsetError("field_yandex");
                if(_paymentsSettingsNeedSubmit) {
                    _paymentsSettingsForm.submit();
                    _paymentsSettingsNeedSubmit = false;
                }
				return true;
			}
		}, "json");
	};

	var _checkCard = function() {
		var card = _cardInput.value.replace(/ /g, '');
		var cardPattern = /^[0-9]{16,19}$/i;
		if (!card.match(cardPattern) && card !== "") {
			_setError("field_card", t('Введите данные в формате: от 16 до 19 цифр'));
			return false;
		}
		_unsetError("field_card");
		return true;
	};

	var _checkDetailsDefault = function() {
        _changeDetailsLimit(_detailsDefaultInput, 'field_details_default');
	};
	var _checkDetailsEn = function() {
		_checkDetailsLimit(_detailsEnInput);

		var enDetails = _detailsEnInput.value;
		if (enDetails.length > 0) {
			var nonEnglishLetterRegexp = /[^\u0000-\u007F–]/g;
			var nonEnglishLetterFind = nonEnglishLetterRegexp.exec(enDetails);
			_detailsEnInput.value = enDetails.replace(nonEnglishLetterRegexp, '');
			if (nonEnglishLetterFind) {
				_setError("field_detailsen", t('Возможен ввод только латинских букв'));
				return false;
			}
			_unsetError("field_detailsen");
		}
		return true;
	};
	var _checkDetailsLimit = function (input) {
        return false;

        var field = $(input).siblings('.js-details-limit-field');
        $(input).val($(input).val().replace(/  +/g, ' '));

        var used = $(input).val().length;
        var min = $(input).data('min');

        if (used < min) {
            field.html(t('{{0}} из {{1}} минимум', [used, min]));
        } else {
            field.html('');
        }
    };

	var _changeDetailsEn = function () {
		_changeDetailsLimit(_detailsEnInput, 'field_detailsen');
	};

    var _changeDetailsLimit = function (input, field_name) {
        var used = $(input).val().length;
        var max = $(input).data('max');

        if (used > max) {
            _setError(field_name, t('Максимальная длина описания - '+ max +' символов'));
            return false;
        }

        _unsetError(field_name);
        return true;
    };
	return {
		init:_init,
	};
})();

SettingCheckersModule.init({
	"usernameInput":".js-username-input",
	"usernameErrorField":".js-username-error-field",
	"nameInput":".js-name-input",
	"nameErrorField":".js-name-error-field",
    "lastnameInput":".js-lastname-input",
    "lastnameErrorField":".js-lastname-error-field",
    "nameEnInput":".js-nameen-input",
	"nameEnErrorField":".js-nameen-error-field",
    "lastnameEnInput":".js-lastnameen-input",
    "lastnameEnErrorField":".js-lastnameen-error-field",
	"emailInput":".js-email-input",
	"emailErrorField":".js-email-error-field",
	"mainSettingsForm":".js-main-settings-form",
	"profileSettingsForm":".js-profile-settings-form",
	"passwordInput":".js-password-input",
	"passwordErrorField":".js-password-error-field",
	"passwordOldInput":".js-password-old-input",
	"passwordOldErrorField":".js-password-old-error-field",
	"passwordConfirmInput":".js-password-confirm-input",
	"passwordConfirmErrorField":".js-password-confirm-error-field",
	"webmoneyInput":".js-webmoney-input",
	"webmoneyErrorField":".js-webmoney-error-field",
	"yandexInput":".js-yandex-input",
	"yandexErrorField":".js-yandex-error-field",
	"qiwiInput":".js-qiwi-input",
	"qiwiErrorField":".js-qiwi-error-field",
	"cardInput":".js-card-input",
	"cardErrorField":".js-card-error-field",
	"paymentsSettingsForm":".js-payments-settings-form",
	"paymentsSettingsInput":".js-payments-settings-input",
	"paymentsSettingsSubmit":".js-payments-settings-submit",
	"detailsEnInput": ".js-detailsen-input",
	"detailsEnErrorField": ".js-detailsen-error-field",
	"detailsDefaultInput": ".js-details-default-input",
	"detailsDefaultErrorField": ".js-details-default-error-field"
});

function switchTabs() {
	//Переключение табов
	$('.js-tabs .js-tab').click(function() {
		var id = $(this).parents('.js-tabs').attr('id');
		var tabId = $(this).data('tab-id');

		$(this).parents('.js-tabs').children('.js-tab').removeClass('active');
		$(this).addClass('active');

		$('.js-tab-content-wrapper[data-id="' + id + '"] .js-tab-content').removeClass('active');
		$('.js-tab-content-wrapper[data-id="' + id + '"]').find('[data-tab-content="' + tabId + '"]').addClass('active');
	});

	var _id = 'settings_tabs';
	var hash = location.hash.substring(1);
	if ($('.js-tab[data-tab-id="' + hash + '"]').length) {
		$('.js-tabs .js-tab').removeClass('active');
		$('.js-tab[data-tab-id="' + hash + '"]').addClass('active');
		$('.js-tab-content-wrapper[data-id="' + _id + '"] .js-tab-content').removeClass('active');
		$('.js-tab-content-wrapper[data-id="' + _id + '"]').find('[data-tab-content="' + hash + '"]').addClass('active');
	}
}

$(function() {
	switchTabs();
});

$(window).on('hashchange load', function () {
	switchTabs();
	initSumSub();
});

function initSumSub() { // инициализируем показ iframe во вкладе верификации
	if (location.hash.substring(1) == 'verification' && $('#idensic iframe').length == 0 && $('#idensic').length) { // запускаем если ны вкладке верификации и не был ранее загружен фрейм
		$.ajax({
			url: "/settings_verification",
			method: "POST",
			dataType: "json",
			success: function (response) {
				if (response.success) {
					idensic.init(
						'#idensic',
						{
							clientId: response.data.clientId,
							accessToken: response.data.accessToken,
							externalUserId: response.data.externalUserId,
							"navConf": {
								"skipWelcomeScreen": true,
								"skipAgreementsScreen": false,
								"skipReviewScreen": false,
								"registration": "disabled"
							},
							"uiConf": {
								"lang": "ru",
							}
							
						},
						function(messageType, payload) {
							if (messageType == 'idCheck.applicantStatus' && payload.reviewStatus != 'pending') {
								// ошибка в документах или верификации по какой то причине
								if (payload.reviewResult.reviewRejectType == 'RETRY' && payload.reviewStatus == "completed") {
									$('.verification-error').html('<div class="mt10 mb20"><h3 class="mb5 text-danger fs20 fw600 lh38">Подтвердить паспортные данные не удалось. Попробуйте еще раз.</h3></div>');
								}
								// в случае если документы имеют мошенеческий характер
								if (payload.reviewResult.reviewRejectType == 'FINAL' && payload.reviewStatus == "completed") {
									$('.verification-error').html('<div class="mt10 mb20"><h3 class="mb5 text-danger fs20 fw600 lh38">Подтвердить паспортные данные не удалось. Обратитесь в службу поддержки.</h3></div>');
								}
								// пользователь находится на странице и ждет подтверждения. Обновляем данные сразу
								if (payload.reviewResult.reviewAnswer == 'GREEN' && payload.reviewStatus == "completed") {
									$('.verification-iframe-holder').html('<div class="mt20 mb20"><h3 class="mb5 text-success fs20 fw600 lh38">Верификация пройдена!</h3><div>Поздравляем! Заказчики больше доверяют верифицированным исполнителям.</div></div>');
									$('.verification-info').hide();
								}
							}
						}
					);
				} else {
					$('.verification-error').html('<div class="mt10 mb20"><h3 class="mb5 text-danger fs20 fw600 lh38">'+ response.data +'</h3></div>');
				}
			},
			error: function (error) {}
		});
	}
}

// данные для "Частые вопросы"
window.app.collapseArr = [
	{
		title: 'Какие требования к документам?',
		content: '<div class="mb10">Проверку можно пройти только по паспорту РФ, загранпаспорт не принимается.</div><div class="mb10">На изображении должен уместиться разворот 2 и 3 страниц паспорта: страница с фотографией и информация о том, кем и когда выдан документ.</div><div class="mb10">Изображение должно быть качественным, без бликов. Не обрабатывайте его в фотошопе или других визуальных редакторах.</div><div class="mb10">Формат изображения — JPG.</div><div>Проверьте, соответствует ли изображение требованиям. Если фотография нужного качества и формата, но вы не можете ее прикрепить, обратитесь в службу поддержки.</div>'
	},
	{
		title: 'Я не похож на себя на фото в паспорте — что делать?',
		content: 'Не переживайте, если фотография в паспорте сделана давно. Если сверка не пройдет автоматически, селфи проверит человек.'
	},
	{
		title: 'Что будет, если я не буду проходить проверку?',
		content: 'Те, кто не подтвердил документы на WorkBay, могут пользоваться сервисом в обычном режиме.'
	},
	{
		title: 'Я не прошел проверку с первого раза — что делать?',
		content: 'Отправлять документы на проверку можно неограниченное количество раз. Подготовьте качественную фотографию и загрузите повторно.'
	},
	{
		title: 'Почему мне отказали в статусе проверенного исполнителя?',
		content: 'Вы не прошли проверку паспорта. По результатам проверки мы оставляем за собой право не присвоить значок или заблокировать профиль.'
	},
];