$(document).ready(() => {
	$("#mobile_message_form").submit(function(e) {
		e.preventDefault();
		messageSubmit($(this), true);
		return false;
	});
});
