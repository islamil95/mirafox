(function($) {
	$.extend({
		popup: function(popupHtml, pars) {
			pars = pars ? pars : {};

			var container=$('<div class="kwork-popup"></div>');
			container.append(popupHtml);
			$('body').append(container);
			$('body').css({'overflow': 'hidden', 'width': 'calc(100% - 17px)'});
			$('.header_top').css({'padding-right': '17px'});

			container.on('click', function(e) {
				if (e.target != e.currentTarget) return;
				close();
			});
	
			container.find('.js-close').on('click', function(e) {
				close();
			});

			function close() {
				if('onclose' in pars)
					pars.onclose();
				container.remove();
				$('body').css({'overflow': '', 'width': ''});
				$('.header_top').css({'padding-right': ''});
			}

			return container;
		}
	});
}( jQuery ));