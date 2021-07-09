var SITE = SITE || {};

var dialogFilesEdit;

$(window).load(function () {
	$('.js-popup-tooltip_conv').on('click', function () {
		var content = $('.js-popup-bundle-overlay_conv__container').html();
		show_popup(content, 'popup-lesson');
	});

	$('.js-user-online-block').onlineWidget();
});

var pullIsTypeMessage = new PullIsTypeMessage('#message_body, #mobile_message', '#info-type-message-bottom, .info-type-message', window.receiverId);
