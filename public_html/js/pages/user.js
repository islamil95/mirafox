$(document).on('submit', '#profile-cover-change-form', function(e){
	var coverFileName = $($(this).find('.js-add-photo').find('input[type="file"]')[1]).val();
	if (coverFileName == '') {
		$(this).find('.profile_error_container').show();
		$(this).find('.profile_error_container').text(t('Загрузите изображение'));
		return false;
	}
	$(this).find('.profile_error_container').hide();
	return true;
});

$(document).ready(function () {
	if ($('.js-message-body').length) {
		window.initReviewForm();
	}
});

var coverPhoto = new KworkPhotoModule();
ReviewsModule.init({
	id: profile_user_id,
	onPage: 12,
	onPageStart: 5,
	entity: 'user',
	baseUrl: ORIGIN_URL
});
coverPhoto.init({
	maxCount: 1,
	fileWrapperClass: 'file-wrapper-block-profile-cover',
	fileInputName: 'cover-photo',
	selectorBlock: '.profile-cover-change-block',
	widthRatio: 1366,
	heightRatio: 206,
	minWidth: 1366,
	minHeight: 206
});

AddFilesModule.init(Object.assign({}, config.files, {'input-name': 'fileInput', 'maxCount': 1}));

 function showChangeProfileCover() {
	$(".profile-cover-change-block").slideToggle("fast");
	coverPhoto.cancelModule();
}
	

var slideMobileModule = new SlideModule();
var slideDesktopModule = new SlideModule();

    $(window).load(function(){
        setTimeout(function(){
        slideMobileModule.init({
            sliderName: '.kwork_slider_mobile #slider_mobile',
            settings: {
                lazyLoad: 'ondemand',
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: false,
                infinite: true,
                sliderCount:true,
                adaptiveHeight: true
            }},200);
        });
        slideDesktopModule.init({
            sliderName: '.kwork_slider_desktop #slider_desktop',
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
});

var PortfolioTooltip = (function (document) {

    var __initTooltip = function ($portfolioBlock) {
        var img = new Image(),
            src = $portfolioBlock.data('src'),
            youtubeId = $portfolioBlock.data('youtube-id');

        var $tooltip = $('<div class="review__portfolio-tooltip portfolio-tooltip">');
        var $container = $('<div class="portfolio-tooltip__container">');
        var $portfilioImgBlock = $('<div class="portfolio-tooltip__img-block">');
        var $portfolioTitle = $('<div class="portfolio-tooltip__title">').text($portfolioBlock.data('title'));
        var $portfolioReviewBlock = $('<div class="portfolio-tooltip__review-block fs13">');
		

        $tooltip.append($container);

        $container.append($portfolioTitle);
		
		
		if (youtubeId) {
            $container.append('<div class="js-popup__inner-content" style="width:100%">' +                
                '<iframe class="portfolio-tooltip__video" src="https://www.youtube.com/embed/' + youtubeId + '" frameborder="0" allowfullscreen></iframe>' +
				'</div>');
        } else {
			$container.append($portfilioImgBlock);
			$portfilioImgBlock.html('<span class="preloader__ico preloader__ico_small portfolio-tooltip__preloader">');
			if ($portfolioBlock.data('review')) {
				var userName = '<span class="semibold">' + $portfolioBlock.data('username') + ': </span>';
				$portfolioReviewBlock.html(userName + '"' + he.encode($portfolioBlock.data('review')) + '"');
				$container.append($portfolioReviewBlock);
			}

			img.onload = function () {
				var $img = $('<img class="portfolio-tooltip__image">').attr('src', src);
				$portfilioImgBlock.addClass('portfolio-tooltip__img-block_loaded');
				$portfilioImgBlock.html($img);
			};
			img.src = src;
		}

        $portfolioBlock.data('portfolioTooltip', $tooltip);
        $portfolioBlock.prepend($tooltip);
    };

    /**
     * Получить дозволенные границы для тултипа по высоте
     *
     * @returns {{top: number, bottom: number, avail: number}}
     * @private
     */
    var __getBorders = function(){
        var top = ($(window).scrollTop() ^ 0) + 84 + 50;
        var bottom = Utils.getWindowOffset() - 10;

        return {
            top: top,
            bottom: bottom,
            avail: bottom - top
        }
    };

    /**
     * Получить размер тултипа
     *
     * @param $tooltip
     * @returns {{imgBlockHeight: number, height: number, width: number}}
     * @private
     */
    var __getTooltipSize = function ($tooltip) {
        var height = $tooltip.height();
        var paddings = $tooltip.outerHeight() - height;
        var reviewHeight = $tooltip.find('.portfolio-tooltip__review-block').outerHeight(true);
        var titleHeight = $tooltip.find('.portfolio-tooltip__title').outerHeight(true);
        var maxHeight = __getBorders().avail - paddings - reviewHeight;
        var imgHeight = height - reviewHeight - titleHeight - 2;

        var width = $tooltip.width();

        if (imgHeight > maxHeight) {
            width = (width * maxHeight / imgHeight);
            height = maxHeight + paddings + reviewHeight + titleHeight;
        }else{
            maxHeight = imgHeight;
        }

        return {
            imgBlockHeight: maxHeight - 10,
            height: height ^ 0,
            width: width ^ 0
        };
    };

    var __showTooltip = function () {
        if (!$(this).data('portfolioTooltip')) {
            __initTooltip($(this));
        }
        $(this).addClass('review-portfolio_hover');
        var $tooltip = $(this).data('portfolioTooltip');
		$tooltip.attr('style', '');
        $tooltip.removeClass('hidden');

        $tooltip.css('top', '0');

        var tooltipSize = __getTooltipSize($tooltip);

        // $tooltip.find('.portfolio-tooltip__img-block').height(tooltipSize.imgBlockHeight);
        $tooltip.find('.portfolio-tooltip__image').width(tooltipSize.width - 12).height('auto');

        var tooltipTop = ((tooltipSize.height - $(this).height()) / 2);

        var minOffset = __getBorders().top;

        var tooltipMaxTop = $(this).offset().top - Math.max(minOffset, $(this).offset().top - tooltipTop);

        tooltipTop = Math.max(tooltipMaxTop, 0);

        var maxOffset = Math.min(Utils.getWindowOffset() - 20, $(this).offset().top + tooltipSize.height);
        var tooltipMinTop = tooltipSize.height - (maxOffset - $(this).offset().top);

        tooltipTop = Math.max(tooltipMinTop, tooltipTop);

        tooltipTop = Math.min(tooltipTop, tooltipSize.height - 30);

        $tooltip.width(tooltipSize.width).height(tooltipSize.height);
        $tooltip.css({
            'top': '-' + tooltipTop + 'px',
            'left': '-' + (tooltipSize.width + 10) + 'px'
        });
    };

    var __setTooltipArrow = function(){
        if(Utils.getWindowOffset() - ($(this).offset().top + 95) < 0){
            $(this).addClass('review-portfolio_half');
        }else{
            $(this).removeClass('review-portfolio_half');
        }
    };

    var __hideTooltip = function () {
        $(this).removeClass('review-portfolio_hover');		
		//Если есть iframe в тултипе значит это видео
		var $iframe = $(this).find('iframe');
		if($iframe.length) {
			//Обновляем Iframe что-бы остановить видео и что-бы при повторном открытие тултипа отображалось корректо
			var iframeSrc = $iframe.attr("src");
			$iframe.attr("src", iframeSrc);
		}
        if (typeof $(this).data('portfolioTooltip') != 'undefined') {
            $(this).data('portfolioTooltip').addClass('hidden');
        }
    };

    var __showPopup = function () {
        var youtubeId = $(this).data('youtubeId');
        var review = '';
        if ($(this).data('review')) {
            var userName = '<span class="semibold">' + $(this).data('username') + ': </span>';
            review =
                '<hr class="gray mt20 balance-popup__line">' +
                '<div>' +
                userName + '"' + $(this).data('review') + '"' +
                '</div>';
        }

        var html = '';
        if (youtubeId) {
            html = '' +
                '<div class="js-popup__inner-content" style="width:650px">' +
                '<h2 class="popup__title" style="font-size: 22px;line-height: 30px;">' + $(this).data('title') + '</h2>' +
                '<hr class="gray mt20 balance-popup__line">' +
                '<div class="clear" style="margin-bottom:32px;"></div>' +
                '<div style="display:inline-block;width:100%;">' +
                '<iframe width="650" height="480" src="https://www.youtube.com/embed/' + youtubeId + '" frameborder="0" allowfullscreen></iframe>' +
                '</div>' +
                review +
                '</div>';
        } else {
            html = '' +
                '<div class="js-popup__inner-content" style="width:650px">' +
                '<h2 class="popup__title" style="font-size: 22px;line-height: 30px;">' + $(this).data('title') + '</h2>' +
                '<hr class="gray mt20 balance-popup__line">' +
                '<div class="clear" style="margin-bottom:32px;"></div>' +
                '<div style="display:inline-block;width:100%;">' +
                '<img src="' + $(this).data('src') + '">' +
                '</div>' +
                review +
                '</div>';
        }

        show_popup(html, null, true);
    };

    var __setEvents = function () {
        if (window.matchMedia("(max-width:767px)").matches || isMobile()) {
            $(document).on('click', '.js-review-portfolio', __showPopup);
        } else {
            $(document).on('mouseenter', '.js-review-portfolio', function(){
                var that = this;

                if(Utils.getWindowOffset() - ($(this).offset().top + 30) < 0){
                    return false;
                }

                clearTimeout($(that).data('timer_hide')^0);

                var timer = setTimeout(function(){
                    __showTooltip.apply(that);
                    __setTooltipArrow.apply(that);
                }, 100);
                $(that).data('timer_show', timer);
            });

            $(document).on('mouseleave', '.js-review-portfolio', function(){
                var that = this;

                clearTimeout($(that).data('timer_show')^0);

                var timer = setTimeout(function(){
                    __hideTooltip.apply(that);
                }, 100);
                $(that).data('timer_hide', timer);
            });

            $(document).on('click', '.js-review-portfolio_video', __showPopup);
        }
    };

    return {
        init: function () {
            __setEvents();
        }
    }
})(document);

function filterPortfolio(category_id) {
	isLockBtnMorePortfolio(true);
	$.post(
		"/user/portfolio",
		{
			category: category_id,
			userId: profile_user_id,
			portfoliosIds: '',
			limit: 12
		},
		function (response) {
			if (response.success) {
				$('.portfolio-list-collage').html(response.data.content);

				isLockBtnMorePortfolio(false);
				isShowBtnMorePortfolio(response.data.portfolio_have_next);

				if (response.data.portfolioAllIds.length > 0) {
					portfolioCard.setAllIds(response.data.portfolioAllIds.join(','));
				}
			}
		}
	);
}

/**
 * Получить фортфолио для категории
 * @param categoryId Номер категории
 */
function loadPortfolioForCategory(categoryId) {
	if (categoryId === undefined || categoryId === null) {
		return false;
	}

	var portfoliosIds = [];
	var $parent = $('.portfolio-list-collage-wrapper[data-portfolio-category-id="' + categoryId + '"]');
	var $content = $parent.find('.portfolio-list-collage');
	var $btnShowMore = $parent.find('.js-show-more-public-portfolio');

	$content.find('.portfolio-card-collage').each(function() {
		var id = $(this).data('id');
		if (id) {
			portfoliosIds.push(id);
		}
	});

	isLockBtnMorePortfolio(true, $btnShowMore);
	$.post(
		"/user/portfolio",
		{
			portfoliosIds: portfoliosIds.length > 0 ? portfoliosIds.join(',') : '',
			category: categoryId,
			userId: profile_user_id,
			limit: 12
		},
		function (response) {
			if (response.success) {
				$content.append(response.data.content);

				isLockBtnMorePortfolio(false, $btnShowMore);
				isShowBtnMorePortfolio(response.data.portfolio_have_next, $btnShowMore);
			}
		}
	);
}

/**
 * Получаем список кворков по нажатию на кнопку "Показать еще"
 * @param el
 */
function loadPortfolioPublic(el) {
	var portfoliosIds = [];
	var $portfolioList = $('.portfolio-list-collage');
	var category_id = $('#portfolio-filter option:selected').val();

	$portfolioList.find('.portfolio-card-collage').each(function() {
		var id = $(this).data('id');
		if (id) {
			portfoliosIds.push(id);
		}
	});

	isLockBtnMorePortfolio(true);
	$.post(
		"/user/portfolio",
		{
			portfoliosIds: portfoliosIds.length > 0 ? portfoliosIds.join(',') : '',
			category: category_id,
			userId: profile_user_id,
			limit: 12
		},
		function (response) {
			if (response.success) {
				$portfolioList.append(response.data.content);

				isLockBtnMorePortfolio(false);
				isShowBtnMorePortfolio(response.data.portfolio_have_next);

				if (response.data.portfolioAllIds.length > 0) {
					portfolioCard.setAllIds(response.data.portfolioAllIds.join(','));
				}
			}
		}
	);
}

/**
 * Показывать ли кнопку "Показать еще" для публичного портфолио
 */
function isShowBtnMorePortfolio(show, $el) {
	if (show === null || show === undefined) {
		return false;
	}
	var $btn = $el ? $el : $('.js-show-more-public-portfolio');
	var $parent = $btn.parent();

	if (show === 1 || show === true) {
		$parent.show();
	} else {
		$parent.hide();
	}
}

/**
 * Заблокировать или разблокировать кнопку "Показать еще" публичного портфолио
 * @param lock
 */
function isLockBtnMorePortfolio(lock, $el) {
	if (lock === null || lock === undefined) {
		return false;
	}

	var $btn = $el ? $el : $('.js-show-more-public-portfolio');

	if (lock === 1 || lock === true) {
		$btn.addClass('onload').prop('disabled', true);
	} else {
		$btn.removeClass('onload').prop('disabled', false);
	}
}

$(function() {
	PortfolioTooltip.init();

	if (Object.keys(window.portfolioAllIds).length) {
		portfolioCard.setAllIds(window.portfolioAllIds[0].join(','));
	}

	$('#portfolio-filter').chosen({
		disable_search: true,
		width: '100%'
	});

	$('body').on('click', '.js-get-public-portfolio', function() {
		var $el = $(this);
		var categoryId = $el.data('categoryId');

		$('.portfolio-list-collage-wrapper').hide();
		$('.portfolio-list-collage-wrapper[data-portfolio-category-id="' + categoryId + '"]').show();

		if (Object.keys(window.portfolioAllIds).length) {
			portfolioCard.setAllIds(window.portfolioAllIds[categoryId].join(','));
		}

		portfolioLazy(
			$('.portfolio-list-collage-wrapper[data-portfolio-category-id="' + categoryId + '"]')
		);
	});
});

// Тестовая ленивая загрузка для работ портфолио
// Если утвердят, вынести отдельно

/**
 * Отложенная загрузка для работ портфолио
 * @param $parent Родитель изображений
 */
function portfolioLazy($parent) {
	if (!$parent) {
		return;
	}

	var _lazy = 'portfolio-lazy';

	$parent.find('.' + _lazy).each(function() {
		var $img = $(this);
		var src = $img.data('src');
		var srcset = $img.data('srcset');

		if (src) {
			$img.attr('src', src);
		}
		if (srcset) {
			$img.attr('srcset', srcset);
		}

		$img.removeClass(_lazy);
		$img.removeAttr('data-src data-srcset');
	});
}

portfolioCard.setMode("portfolio");