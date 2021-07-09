$(document).ready(function() {

	var currentState = 0;
	var mSwap = $('.m-swap');

	function updateMSwap() {
		if (window.matchMedia('(max-width: 1019px)').matches) {
			if(currentState == 0) {
				mSwap.filter('.desktop-version').each(function(k, v) {
					var t = $(v);
					mSwap.filter('.tablet-version[data-name="' + t.data('name') + '"]').html(t.children().clone(true, true));
				});
				currentState = 1;
			}
		} else {
			if(currentState == 1) {
				mSwap.filter('.tablet-version').each(function(k, v) {
					var t = $(v);
					mSwap.filter('.desktop-version[data-name="' + t.data('name') + '"]').html(t.children().clone(true, true));
				});
				currentState = 0;
			}
		}
	}
	
	$(window).on('resize', updateMSwap);
	updateMSwap();

});