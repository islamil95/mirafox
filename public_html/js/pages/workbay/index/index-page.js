/**
 * Инициализация слайдера статистики для мобильной версии
 *
 * @param element Класс слайдера
 * @param widthChange Максимальная ширина при которой появляется слайдер
 * @return {boolean}
 */
let initWorkBaySlider = function(element, widthChange = 992) {
	let $slider = $(element);

	if (!element || !$slider.length) {
		return false;
	}

	let _initSlider = function() {
		let _width = $(window).width();
		let _slickSettings = {
			dots: true,
			infinite: true,
			speed: 300,
			slidesToShow: 1,
			slidesToScroll: 1,
			autoplay: true,
			autoplaySpeed: 3000,
			arrows: true,
			prevArrow: '<button type="button" class="slick-prev"><span class="fa fa-angle-left"></span></button>',
			nextArrow: '<button type="button" class="slick-next"><span class="fa fa-angle-right"></span></button>'
		};

		if (_width < widthChange) {
			if (!$slider.hasClass('slick-initialized')) {
				return $slider.slick(_slickSettings);
			}
		} else {
			if ($slider.hasClass('slick-initialized')) {
				$slider.slick('unslick');
			}
			return false;
		}
	};

	_initSlider();
	$(window).resize(function() {
		_initSlider();
	});
};

let initFooterMobile = function() {
	let $footerSlide = $('.footer-workbay__slide');

	$footerSlide.on('click tap', '.footer-workbay__title', function() {
		if ($(window).width() >= 992) {
			return false;
		}

		let $parent = $(this).closest('.footer-workbay__slide');

		if ($parent.hasClass('active')) {
			$parent.removeClass('active');
		} else {
			$('.footer-workbay__slide').removeClass('active');
			$parent.addClass('active');
		}
	});
};

/**
 * Document ready
 */
$(function() {
	initWorkBaySlider('.js-workbay-statistics-slider', 767);
	initWorkBaySlider('.js-workbay-advantages-slider');
	initFooterMobile();
});