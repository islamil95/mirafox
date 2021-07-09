function billFormSetEvents() {
	var $form = $("#billForm");
	if (!$form.length) {
		return false;
	}

	$form.find('.bill-form-input').each(function () {
		$(this).bind('keyup paste blur', function () {
			billInputCheck($form, this);
			checkBillFormProceed();
		});
	});

	$form.find('.bill-form-input').each(function () {
		billInputCheck($form, this);
	});

	$("#bill_need_physical").on("change", tooglePostAdress);

	$(".js-save-bill-form").on("submit", sendBillForm);

	checkBillFormProceed();
}

function checkBillFormProceed() {
	var $form = $("#billForm");
	if (!$form.length) {
		return false;
	}

	var proceed = true;
	$form.find('.check_bill').each(function () {
		if ($(this).hasClass('hidden') && (!$(this).hasClass('check_bill_kpp') && (!$(this).hasClass('check_bill_post_address') || $("#bill_need_physical").prop("checked") === true))) {
			proceed = false;
		}
	});

	if (!proceed) {
		$form.find('.bill-form-proceed-button').addClass('disabled').prop('disabled', true);
	} else {
		$form.find('.bill-form-proceed-button').removeClass('disabled').prop('disabled', false);
	}
}

function billInputCheck($form, input) {
	if ($(input).val() === '') {
		$form.find('.check_' + $(input).attr('id')).addClass('hidden');
	} else {
		$form.find('.check_' + $(input).attr('id')).removeClass('hidden');
	}
	if ($(input).val() === '' && $(input).attr('id') === 'bill_kpp') {
		$form.find('.check_' + $(input).attr('id')).addClass('hidden');
	} else if ($(input).val() !== '' && $(input).attr('id') === 'bill_kpp') {
		$form.find('.check_' + $(input).attr('id')).removeClass('hidden');
	}
}

function tooglePostAdress()
{
	var checked = $(this).prop("checked");
	var $blockPostAdress = $("#bill_block_post_address");
	var $adressInput = $("#bill_post_address");
	if (checked)
	{
		$adressInput.prop("disabled", false).addClass("js-required");
		$blockPostAdress.removeClass("hidden");

	} else {
		$adressInput.prop("disabled", true).removeClass("js-required");
		$blockPostAdress.addClass("hidden");
	}
	checkBillFormProceed();
}

function sendBillForm(e) {
	var that = this;
	var button =  $(that).find('.bill-form-proceed-button');
	var formDisabled = button.prop("disabled");
	if (formDisabled === true) {
		e.preventDefault();
		return false;
	}
	button.addClass("disabled").prop("disabled", true);
	$.ajax({
		type: "POST",
		data: $(that).serialize(),
		url: "/bill_get",
		success: function(response) {
			if (response.success) {
				location.href = response.redirect_url;
			} else {
				button.removeClass("disabled").prop("disabled", false);
				var textErrors = "<ul><li>" + response.errors.join("</li><li>") + "</li></ul>";
				$(".fox_error").removeClass("hidden").find(".fox_error_content").html(textErrors);

			}
		}
	});
	e.preventDefault();
	return false;
}

billFormSetEvents();