$(window).load(function () {
	$('.js-lang').on('click', function () {
		if (!$(this).hasClass('info-lang--active')) {
			$('.oferta-table tr td').hide();
			$('.oferta-table tr td:nth-child(' + $(this).data('index') + ')').show();
			$(this).siblings().removeClass('info-lang--active');
			$(this).addClass('info-lang--active');
		}
	});
})
