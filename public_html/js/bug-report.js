/**
 * Сообщения об ошибках
 */
 jQuery(function ($) {

	// Один раз показывать пользователям уведомление о возможности отправки
	// сообщений об ошибках
	/*
	if (!getCookie('bug_report')) {
		showNoticePopup();

		var date = new Date();
		date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
		setCookie('bug_report', 1, { expires: date, path: '/' });
	}
	*/

	// При нажатии Ctrl+Enter показывать окно для отправки сообщения об ошибке
	$(document).keydown(function (e) {
		if ((e.keyCode == 10 || e.keyCode == 13) && e.ctrlKey) {
			var focused = $(':focus');
			var submitting = (typeof csSubmitNow != 'undefined' ? csSubmitNow : null);
			if (submitting || focused.length > 0 && focused.hasClass('js-message-input')) {
				return;
			}
			showReportPopup();
		}
	});

	/**
	 * Показать уведомление о возможности отправки сообщений об ошибках
	 */
	function showNoticePopup() {
		var title = 'Kwork is still in Beta';
		var content = 'There may be some errors on the site. Please tell us about them. Just press CTRL + ENTER and describe the error. We will fix everything!';

		var html =
			'<h1 class="popup__title">' + title + '</h1>' +
			'<hr class="gray mt15 mb15" />' +
			'<div class="mt15 mb15">' + content + '</div>' + 
			'<button class="green-btn mt0 h45 popup__button popup-close-js">OK</button>';

		show_popup(html);
	}

	/**
	 * Показать окно для отправки сообщения об ошибке
	 */
	function showReportPopup() {
		var title = 'Report a bug or a mistake';

		var html =
			'<h1 class="popup__title">' + title + '</h1>' +
			'<hr class="gray mt15 mb15" />' +
			'<div class="mt15 mb15">' +
				'<textarea class="wMax" id="bug-report-message" style="height: 200px;"></textarea>' +
			'</div>' + 
			'<button class="green-btn mt0 h45 popup__button" id="bug-report-send">Send</button>';

		show_popup(html);

		$('#bug-report-message').focus();

		$('#bug-report-send').click(function () {
			var message = $('#bug-report-message').val();

			if (message === '') {
				alert('Please write something');
				return;
			}

			sendReport(message, function () {
				remove_popup();
			});
		});
	}

	/**
	 * Отправить сообщение об ошибке
	 *
	 * @param string message Текст сообщения
	 * @param function success Коллбэк при успешной отправке
	 */
	function sendReport(message, success) {
		function showError(error) {
			alert(error || 'Error');
		}

		$.post('/api/bugreport/sendreport', { url: location.href, message: message })
			.done(function (data) {
				try {
					if (data.success) {
						success();
					} else {
						showError(data.error);
					}
				} catch (e) {
					window.console && console.error(e.name + ': "' + e.message + '"');
					showError();
				}
			})
			.fail(function () {
				showError();
			});
	}

});
