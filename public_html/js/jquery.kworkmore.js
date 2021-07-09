(function( $ ) {
    $.fn.more=function(container) {
		var button=this;
		var full=$(button.data('for'));
		var expandText=button.html();
		var collapseText=button.data('collapse-text');

		full.css({'display': 'none'});

		button.on('click', function() {
			buttonClick();
		});

		function buttonClick() {
			if(!full.data('opened')) {
				full.css({'display': 'block'});
				var offsetTop=full.position().top+container.scrollTop();
				container.animate({scrollTop: offsetTop+'px'});
				full.data('opened', 'true');
				button.html(collapseText);
			} else {
				full.css({'display': 'none'});
				button.html(expandText);
				full.removeData('opened');
			}
		}

		return this;
    };
}( jQuery ));