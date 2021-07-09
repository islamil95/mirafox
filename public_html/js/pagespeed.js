/**
 * Отложенная подгрузка стилей
 * @param {string} src
 */
function loadStyleSheet(src) {
	if (document.createStyleSheet) {
		document.createStyleSheet(src);
	}
	else {
		var stylesheet = document.createElement('link');
		stylesheet.href = src;
		stylesheet.rel = 'stylesheet';
		stylesheet.media = 'screen';
		document.getElementsByTagName('head')[0].appendChild(stylesheet);
	}
}

/**
 * Отложенная подгрузка изображений при загрузке страницы
 * @param {string} el
 * @param animation
 */
function lazyLoadImg(el, animation) {
	jQuery(el).each(function () {
		if (jQuery(this) + ':not(.lazy-load_initialized') {
			var lazyLoadImage = jQuery(this);

			if (lazyLoadImage.hasClass('lazy-load_webp')) {
				//современный формат изображений webp + поддержка старых браузеров
				var childrens = lazyLoadImage.children();
				childrens.each(function () {
					var child = jQuery(this);
					child.attr('src', child.data('src'));
					child.attr('srcset', child.data('srcset'));
				});
			} else {
				//обычный формат изображений
				var lazyLoadImageSrc = lazyLoadImage.data('src');
				var lazyLoadImageSrcset = lazyLoadImage.data('srcset');

				if (lazyLoadImageSrc) {
					lazyLoadImage.attr('src', lazyLoadImageSrc);
					if (lazyLoadImageSrcset) {
						lazyLoadImage.attr('srcset', lazyLoadImageSrcset);
					}
				}
			}

			if (animation) {
				lazyLoadImage.on('load', function () {
					lazyLoadImage.addClass('lazy-load_initialized');
				});
			} else {
				lazyLoadImage.addClass('lazy-load_initialized');
			}
		}
	});
}

/**
 * Отложенная подгрузка изображений при скролле
 */
function lazyLoadImgScroll() {
	var _getScrollTop = getScrollTop();
	if (_getScrollTop > 0) {
		jQuery('html, body').scrollTop(_getScrollTop + 1);
	} else {
		jQuery('.lazy-load_scroll-wrapper').each(function () {
			if (jQuery(window).height() > jQuery(this).offset().top) {
				jQuery('html, body').scrollTop(1);
			}
		});
	}

	window.addEventListener('scroll', function () {
		if (jQuery('.lazy-load_scroll-wrapper:not(.lazy-load_scroll_initialized)').length === 0) {
			return;
		}
		jQuery('.lazy-load_scroll-wrapper:visible').each(function () {
			var el = jQuery(this);
			if (jQuery(window).scrollTop() > (el.offset().top - jQuery(window).height())) {
				lazyLoadImg(el.find('.lazy-load_scroll'), false);
				el.addClass('lazy-load_scroll_initialized');
			}
		});
	});
}

/**
 * Отложенная подгрузка изображений при попадании во viewport
 */
function lazyLoadImgViewport() {
	var visibleCusongsBlocks = jQuery('.lazy-load_scroll:not(.lazy-load_initialized)').withinviewport({
		sides: 'top bottom',
		bottom: '-100',
	});
	if (visibleCusongsBlocks.length === 0) {
		return false;
	} else {
		visibleCusongsBlocks.each(function () {
			lazyLoadImg(jQuery(this), false);
		});
	}
}

/**
 * Отложенная подгрузка контента после загрузки всех стилей
 * @param {string} el
 */
function lazyLoadContent(el) {
	setTimeout(function () {
		jQuery(el).each(function () {
			jQuery(this).addClass('styles-initialized');
		});
	}, 250);
}

/**
 * Узнать, насколько проскроллена страница
 * @return int
 */
function getScrollTop()	{
	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}

/**
 * Проверить поддержку webp браузером
 */
function canUseWebP() {
	var elem = document.createElement('canvas');

	if (!!(elem.getContext && elem.getContext('2d'))) {
		if (elem.toDataURL('image/webp').indexOf('data:image/webp') === 0) {
			document.getElementsByTagName('body')[0].className += ' is_webp';
		}
	}
}