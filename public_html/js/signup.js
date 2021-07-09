$(window).load(function() {
	var trackClientId = getTrackClientId();
	$("#fox_signup_form").append("<input type=\"hidden\" name=\"track_client_id\" value=\"" + trackClientId + "\" />");

	setTimeout(function () {
		$("#fox_signup_form").find("a.vk, a.fb").each(function(i) {
			$(this).attr("href", $(this).attr("href") + addRefParam());
		});
	}, 750);
});

function updateUserRole() {
	var t=$('#fox_signup_form input[name="userType"]:checked');
	var f=$('#fox_signup_form .signup-country-block');
	if(t.attr('value') == '2') {
		f.show();
	} else {
		f.hide();
	}
}

$(document).ready(function () {
	$("#fox_signup_form #user_email").change(function() {
		var $emailError = $("#fox_signup_form .email-entry-error");
		$emailError.text("");
		var badMailHost = checkBadEmailDomains($(this).val());
		if(badMailHost !== false && badMailHost.length > 1) {
			$emailError.text(t("{{0}} не принимает сообщения от Kwork. Используйте другой email, пожалуйста", [upstring(badMailHost)]));
		}
	});

	$('input[name="userType"]').on('change', updateUserRole);
	updateUserRole();

	var $selects = $('#fox_signup_form .signup-country-field select');
		$selects.chosenCompatible("destroy");
		$selects.chosenCompatible({
			width: '100%',
			disable_search: true
		}).change(function(e) {
			var t = $(e.delegateTarget);
			var sw = $('#fox_signup_form .signup-country-warning');
			if($.inArray(parseInt(t.val()), countriesWithoutWithdrawal) != -1) {
				sw.show();
			} else {
				sw.hide();
			}
		});
	$('.signup-country-field').find('.chosen-results').unbind('mousewheel');
});