function emailCheck(el) {
	let val = $(el).val();
	if (!val || val.length < 1) {
		return t('Пожалуйста, заполните это поле');
	}
	let regexp = /^\S+@\S+\.\S+$/;
	if (!regexp.test(val)) {
		return t('Адрес электронной почты указан некорректно');
	}
	return '';
}

function validateEmail(el) {
	let text = emailCheck(el);
	$(el).attr('title', text);
	if ($(el).data('showError')) {
		el.setCustomValidity(text);
	}
}

function bindEvents() {
	$(document).on('mouseenter', '.field-tooltip-activator', function() { tooltipShow(this); });
	$(document).on('mouseleave', '.field-tooltip-activator', function() { tooltipHide(this); });

	$('input[name="email"]').on('input', (e) => {
		validateEmail(e.target);
	});

	$('input[name="email"]').on('invalid', (e) => {
		$(e.target).data('showError', true);
		validateEmail(e.target);
	});
}

$(document).ready(() => {
	bindEvents();
	validateEmail($('input[name="email"]')[0]);
});