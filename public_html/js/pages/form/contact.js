function ContactForm(url) {
	var map = {
		username: '.input_name-js',
		email: '.input_email-js',
		supportMessage: '.input_message-js',
		captcha: '.input_captcha-js'
	};

	function _init() {
		var form = $('.help-form-container form');

		form.on('submit', function (e) {
			e.preventDefault();

			$('.form-message').remove();

			$(this).find('.input_captcha-js .input-error').hide();

			if ($(this).find('.input-error').is(':visible')) {
				return false;
			}

			$.post(url, $(this).serialize())
				.done(function (response) {
					if (response.data.redirect) {
						return window.location.replace(response.data.redirect);
					}

					if (response.data.errors) {
						$.each(response.data.errors, function (field, error) {
							if (field === 'captcha') {
								$('.captcha-container').slideDown(300);
							}
							inputValidator.showError(map[field], error.join(', '))
						});

						return;
					}
					var container = $('<div class="form-message" />');

					if (response.success) {
						form[0].reset();

						container.addClass('fox_success');
						container.append('<p>' + response.data.message + '</p>');
					} else {
						container.addClass('fox_error');
						container.append('<p>' + response.data.error + '</p>');
					}

					$('.form-messages').append(container);
					$("html, body").animate({ scrollTop: 0 }, 800);
					$('.captcha-container').slideUp(300);
				})
				.fail(function (response, status, error) {
					var container = $('<div class="form-message" />');
					container.addClass('fox_error');
					container.append('<p>' + error + '</p>');
					$("html, body").animate({ scrollTop: 0 }, 800);
					$('.captcha-container').slideUp(300);
				});


			return false;
		});
	}

	return {
		init: _init,
	};
}