var page = 1;

function loadKworksPaging() {
	if (!isActor) {
		$('.signup-js:first').trigger('click');
	}
	$.ajax('', {
		type: 'POST',
		dataType: 'json',
		data: {page: page, action: 'loadKworks'},
		success: function (response) {
			page++;
			$('#kworks .cusongs .cusongslist .cusongsblock:last').after(response.html);
			if (response.hideButton) {
				$('.loadKworks').remove();
			}
		}
	});
}

$(function () {
	var slideDesktopModule = new SlideModule();
	slideDesktopModule.init({
		sliderName: '#slider',
		settings: {
			lazyLoad: 'ondemand',
			slidesToShow: 1,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			infinite: true,
			speed: 100,
			fade: true,
			cssEase: 'linear'
		}
	});

	//слайдер таблицы сравнения на мобильных
	$('.land-compare').slick({
		dots: true,
		infinite: true,
		speed: 300,
		slidesToShow: 1,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 3000,
		arrows: false,
	});

	//показ полного текста в футере лэндингов на мобильном
	$(document).on('click', '.land-footer .link_local', function () {
		$(this).closest('.land-footer').addClass('land-footer_fulltext');
	});

	//мобильный футер на лендингах
	var w = $(window).width();
	if (w < 768) {
		$('.footer.is_land .linksBlockTitle').on('click', function () {
			$(this).closest('.linksBlock').toggleClass('active');
		});
	}
});