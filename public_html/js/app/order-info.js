$(window).load(function () {
	$(document).on('click', '.js-link-description-order', function () {
		var $this = $(this);
		var $table = $this.closest('.order-info');
		var $arrows = $table.find('.js-link-description-order img');
		var $arrow = $this.find('img');

		$table.toggleClass('order-info--hide');
		$arrow.toggleClass('rotate180');

		if ($this.data('type') === 'hide') {
			$table.addClass('order-info--hide');
			$arrows.removeClass('rotate180');
			$arrow.addClass('rotate180');

			var trackHead = $this.closest('.track-head');
			if (trackHead.length) {
				$('html, body').animate({
					scrollTop: trackHead.offset().top
				}, 1);
			}
		}
	});

})
