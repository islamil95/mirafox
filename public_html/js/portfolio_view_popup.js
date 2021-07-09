var portfolioCardModule = function() {
	var pageUrl = null;
	var classes = {
		popup: 'popup-portfolio',
		controlPrev: 'js-portfolio-prev',
		controlNext: 'js-portfolio-next',
		controlOverlay: 'js-portfolio-control-overlay',
		wrapperContent: 'wrapper-content',
		fixBottom : 'fix-bottom',
		content: 'portfolio-large__content',
		preview: 'js-portfolio-preview',
		viewPage: 'portfolio-view-page',
		portfolioCard: 'js-portfolio-card'
	};
	var _portfolioMode;
	/**
	 * Режим пересчета автоматического пересчета идентификаторов
	 * @type {boolean}
	 * @private
	 */
	var _updatingIdsMode = false;
	var allIds;
	var _players = [];
	var _overlayClose = false;
	var _scrollBarWidth = Utils.getScrollBarWidth() || 0;
	var _startPortfolioId = 0;
	var _currentPortfolioId = 0;
	var _getXhr = null;

	var commentErrors = {
		'delete_comment': t('Ошибка при удалении комментария'),
	};

	var _setPortfolioId = function() {
		_portfolioIds = {
			all: null,
			current: null,
			prev: null,
			next: null
		}; // сбрасываем старые значения

		/**
		 * Если установлена опция обновления идентификаторов портфолио,
		 * то производим перерасчет
		 */
		if (true === _updatingIdsMode) {
			_updateIds();
		}

		var $content = jQuery('.' + classes.popup).find('.popup_content');
		var dataPortfolioAllIds = allIds || $content.data('portfolioAllIds');
		var dataCurrentId = $content.data('portfolioId');
		
		if (dataPortfolioAllIds) {
			dataPortfolioAllIds += '';
			_portfolioIds['all'] = dataPortfolioAllIds.split(',');
		}
		if (dataCurrentId) {
			_portfolioIds['current'] = dataCurrentId;
		}
		if (_portfolioIds['all']) {
			var portfolioCurrentKey = null;
			var portfolioCnt = 0;

			jQuery.each(_portfolioIds['all'], function(index, value) {
				if (value == _portfolioIds['current']) {
					portfolioCurrentKey = index;
				}
				portfolioCnt++;
			});

			if ((portfolioCurrentKey + 1) <= (portfolioCnt - 1)) {
				_portfolioIds['next'] = _portfolioIds['all'][portfolioCurrentKey + 1];
			} else {
				var bgLoad = false;
				//Этот функционал для страницы gallery
				if (window._bgLoad !== undefined && $('.loadPortfolios').length) {
					bgLoad = (_bgLoad && !$('.loadPortfolios').hasClass('hidden'));
				} 				
				_portfolioIds['next'] = (bgLoad ? 'bgLoad' : _portfolioIds['all'][0]);
			}
			if ((portfolioCurrentKey - 1) >= 0) {
				_portfolioIds['prev'] = _portfolioIds['all'][portfolioCurrentKey - 1];
			} else {
				_portfolioIds['prev'] = _portfolioIds['all'][portfolioCnt - 1];
			}
		}
	};

	var _get = function(method, id) {
		if (!method || !id) {
			return false;
		}
		
		_openPopup();
		var data = {};
		if (_portfolioMode) {
			data.mode = _portfolioMode;
		}
		if (allIds) {
			data.ids = allIds;
		}

		if (id == 'bgLoad') {
			window.loadMoreFunction(false, function(newUrl) {
				pageUrl = newUrl;
			}, pageUrl, true);
			return;
		}

		_getXhr = $.ajax({
			type: 'POST',
			url: method + id,
			dataType: 'html',
			data: data,
			success: function(data) {
				_currentPortfolioId = id;
				_openPopup(data);
				//Обновляем просмотр в карточке портфолио
				_updateViews();
			},
			error: function() {
				_errorPopup();
			},
			complete: function() {
				_getXhr = null;
			}
		});
	};

	/**
	 * Отрендерит работу из кворка
	 * @param {int} portfolioId ID - портфолио
	 */
	var _getPortfolio = function(portfolioId) {
		if (portfolioId == null || portfolioId == undefined || portfolioId == '') {
			return false;
		}

		if (window.loadMoreXhr) {
			window.loadMoreXhr.abort();
		}

		_get('/portfolio_large/', portfolioId);
	};

	/**
	 * Отрендерит первую работу из кворка
	 * @param {int} kworkId ID - квокра
	 */
	var _getFirstPortfolio = function(kworkId) {
		if (kworkId == null || kworkId == undefined || kworkId == '') {
			return false;
		}

		_get('/kwork_first_portfolio/', kworkId);
		_openPopup();
	};

	/**
	 * Вставить/заменить содержимое окна
	 * @param {string} content Содержимое для модального окна
	 */
	var _content = function(content) {
		// Удаляем элементы управления работой
		jQuery('.' + classes.popup).find('.bg-overlay').remove();

		if (jQuery('.' + classes.popup).length) {
			jQuery('.' + classes.popup).find('.wrapper-content').html(content);

			// Прилипание кнопки лайка при загрузке страницы и скролле
			_likePosition();

			// Создаем видимую область подгрузки изображения работы
			_preloadPortfolioImg();
		} else {
			jQuery('body').append(
				'<div class="popup-portfolio portfolio_large" style="display: none;">'
					+ '<div class="popup-portfolio-content">'
						+ '<a href="javascript: portfolioCard.close();" class="js-popup-close popup-portfolio__close">'
							+ '<span class="kwork-icon icon-close"></span>'
						+ '</a>'
						+ '<div class="wrapper-content">'
							+ content
						+ '</div>'
					+ '</div>'
				+ '</div>'
			);

			var widthScroll = Utils.getScrollBarWidth();
			var $btnClose = $('.js-popup-close');

			if (widthScroll) {
				$btnClose.css({
					right: parseInt($btnClose.css('top')) + widthScroll
				});
			}

			// Блокируем скролл всего документа
			lockBodyForPopup();

			// Показываем попап
			$('.' + classes.popup).fadeIn(200);
		}
		_eventOpen();

		// Переносим все элементы управления работой в новый блок,
		// который позволит прокручивать страницу при наведении на них курсор
		jQuery('<div class="bg-overlay"></div>').insertBefore(
			jQuery('.popup-portfolio .wrapper-content')
		);
		jQuery('.control, .control-overlay').appendTo('.bg-overlay');
	};

	/**
	 * Отрисовывает модальное окно с портфолио
	 * @param {string} content Содержимое модального окна (если не передать, то отобразится лоадер)
	 */
	var _openPopup = function(content) {
		if (content == null || content == undefined || content == '') {
			content = '<div class="portfolio-loader">' +
					'<div class="ispinner ispinner--gray ispinner--animating ispinner--large">' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
						'<div class="ispinner__blade"></div>' +
					'</div>'
			+ '</div>';
		}

		if (!pageUrl) {
			pageUrl = window.location.href;
		}

		_content(content);
	};

	/**
	 * Отрисовывает модальное окно с ошибкой для портфолио
	 * @param {string} comsgErrorntent Текст ошибки
	 */
	var _errorPopup = function(msgError) {
		if (msgError == null || msgError == undefined || msgError == '') {
			msgError = t('Произошла ошибка.<br> Повторите попытку позже.');
		}

		if (msgError) {
			var content = '<div class="portfolio-error">'
					+ '<a href="javascript: portfolioCard.close();" class="js-popup-close popup-close">'
						+ '<span class="kwork-icon icon-close"></span>'
					+ '</a>'
					+ '<img src="' + Utils.cdnImageUrl("/collage/default_category@x1.jpg") + '">'
					+ msgError
				+ '</div>';
		}

		_content(content);
	};

	/**
	 * Закрытие модального окна
	 */
	var _closePopup = function() {
		if (_getXhr) {
			_getXhr.abort();
		}
		var $isModal = jQuery('body').find('.popup-portfolio.portfolio_large');

		if (!$isModal.length) {
			return false;
		}

		// Возвращаем прежний URL
		_setPortfolioUrl('pageUrl');
		pageUrl = null;

		// Отменяем блокировку скролла всего документа
		unlockBodyForPopup();

		jQuery('.' + classes.popup).hide();

		var delay = setInterval(function() {
			jQuery('.' + classes.popup).remove();
			clearInterval(delay);
		}, 350);

		if (typeof _closeAction !== 'undefined') {
			if (_startPortfolioId != _currentPortfolioId) {
				_closeAction(_currentPortfolioId);
			}
		}
	};

	/**
	 * Режим отображения
	 * Вешаем нужный нам класс на родителя
	 */
	var _mode = function() {
		var $popup = jQuery('.' + classes.popup);
		var popupMode = $popup.find('.popup_content').data('mode');
		if (popupMode) {
			$popup.addClass(popupMode);
		}
	};


	/**
	 * Обновляем все: лайки, комменты
	 * @param {number} portfolioId - Id портфолио
	 */
	var _updatePortfolioAbout = function(portfolioId) {
		var $portfolio = $('.portfolio-large');
		var totalLikes = parseInt($portfolio.data('porfolio-likes'));
		var totalComments = parseInt($portfolio.data('porfolio-comments'));

		var $detailComments = $('.portfolio-card-collage[data-id="'+portfolioId+'"] .detail_comments');
		var $detailLikes = $('.portfolio-card-collage[data-id="'+portfolioId+'"] .detail_likes');
		//Показываем скрываем комментарии
		if(totalComments > 0) {
			$detailComments.removeClass('hidden');
			$detailComments.find('.count').html(totalComments);
		} else {
			$detailComments.addClass('hidden');
		}
		//Показываем скрываем лайки
		if(totalLikes > 0) {
			$detailLikes.removeClass('hidden');
			$detailLikes.find('.count').html(totalLikes);
		} else {
			$detailLikes.addClass('hidden');
		}
	};


	/**
	 * Обновляем просмотры
	 */
	var _updateViews = function() {
		var $portfolio = $('.portfolio-large');
		var portfolioId = $portfolio.data('portfolio-id');
		var totalViews = parseInt($portfolio.data('porfolio-views'));

		var $detailViews = $('.portfolio-card-collage[data-id="'+portfolioId+'"] .detail_views');
		if(!$portfolio.hasClass('portfolio-large_has_view')) {
			totalViews++;
			if(totalViews > 0) {
				$detailViews.removeClass('hidden');
			}
			$detailViews.find('.count').html(totalViews);
		}
	}



	/**
	 * Cобытия после открытие окна
	 */
	var _eventOpen = function(skip) {
		skip = !skip ? false : true;

		var $modal = jQuery('.' + classes.popup);
		var $content = $modal.find('.' + classes.wrapperContent);

		if (skip == false) {
			// Прокручиваем в самое начало
			$modal.scrollTop(0);

			// Режим отображения
			_mode();

			// Устанавливаем список всех работ
			_setPortfolioId();

			// Изменяем URL
			_setPortfolioUrl(_portfolioIds['current']);

			if (_portfolioIds.all && _portfolioIds.all.length > 1) {
				// Отображаем элементы навигации
				if (_portfolioIds.prev) {
					jQuery('.' + classes.controlPrev).show();
				}
				if (_portfolioIds.next) {
					jQuery('.' + classes.controlNext).show();
				}
			}
		}

		_showVideo();
		_isShowPortfolioEditField();

		_eventComment();

		// Если оверлэй закрывает окно,
		// то удаляем переключение работ, по оверлэю
		if (_overlayClose) {
			jQuery('.' + classes.controlOverlay).remove();
		}

		// Магия отображения/скрытия блоков под изображениями/видео
		var countComments = parseInt(jQuery('.portfolio-large__comments').attr('data-total') || 0);
		var hasOneVideo = jQuery('.portfolio-large').hasClass('js-one-video');

		if ((USER_ID && hasOneVideo) || (!USER_ID && hasOneVideo && countComments)) {
			jQuery('.portfolio-large__aside').removeClass('portfolio-large__aside_theme_column');
		}
	};

	var _showVideo = function () {

		var $el = $('.iframe-ya-player');
		
		_players = [];
		
		$el.each(function () {
			if ($(this).length) {
				var player = new YT.Player($(this).attr('id'), {
					videoId: $(this).data('ykey'),
					events: {
						'onReady': _onPlayerReady,
						'onStateChange': _onPlayerStateChange,
					}
				});

				_players.push(player);
			}
		});

		if (jQuery('.portfolio-large').hasClass('js-one-video')) {
			setTimeout(
				_likePositionScroll(false),
				250
			);
		}
	};
	
	var _onPlayerReady = function (event) {
		if ($(event.target.getIframe()).parent().data('autoplay')) {
			event.target.playVideo();
		}
	};
	
	var _onPlayerStateChange = function (event) {
		if (event.data == YT.PlayerState.PLAYING) {
			var temp = event.target.getVideoUrl();
			for (var i = 0; i < _players.length; i++) {
				if (_players[i].getVideoUrl() != temp)
					_players[i].pauseVideo();
			}
		}
	};
	
	var _isShowPortfolioEditField = function(param) {
		var $portfolioEditField = jQuery('.' + classes.popup + ', .' + classes.viewPage).find('.js-portfolio-edit-field');

		if (param == true) {
			$portfolioEditField.show();
			return;
		}
		if (param == false) {
			$portfolioEditField.hide();
			return;
		}

		if (window.portfolioList != undefined) {
			$portfolioEditField.show();
		} else {
			$portfolioEditField.hide();
		}
	};

	var _eventComment = function() {
		var $parent = jQuery('.js-portfolio-comments');
		var $moreBtn = $parent.find('.more-btn-blue');

		_showNextButton($parent);

		$moreBtn.on('click', function() {
			_loadMoreComments($parent);
		});

		$parent.on('click', '.js-delete-comment', function (e) {
			e.preventDefault();

			var $deleteButton = jQuery(this);
			var $comment = $deleteButton.closest('li');

			$deleteButton.addClass('disabled').prop('disabled', true).blur();

			var commentId = $comment.data("comment-id");

			$.ajax({
				type: "POST",
				url: '/api/portfolio/deletecomment',
				data: {
					id: commentId,
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						var onPage = $parent.data('onpage') - 1;
						var total = $parent.data('total') - 1;
						var portfolioId = $parent.data('portfolio-id');
						$('.portfolio-large').data('porfolio-comments', total);

						$parent.data('onpage', onPage);
						$parent.data('total', total);

						$comment.remove();

						var $counter = jQuery('.js-portfolio-comments-counter');
						var $parentItem = $counter.closest('.portfolio-like-views__item');

						$counter.text(total);
						$parentItem.attr('data-count', total||0);
						_isShowAboutStats();

						if (total === 0) {
							jQuery('.js-portfolio-comments-counter-wrapper').addClass('hidden');
							jQuery('.portfolio-large__comments-block').removeClass('portfolio-large__comments-block_auth');
						}

						//Обновляем показатели портфолио
						_updatePortfolioAbout(portfolioId);
					} else {
						_handleErrors(response.result);
					}
				},
				complete: function() {
					if ($comment.length) {
						$deleteButton.removeClass('disabled').prop('disabled', false);
					}
				}
			});
		});
	};

	var _handleErrors = function(v) {
		var error = '';
		if ('validError' in v) {
			error = commentErrors[v.validError] || '';
		}
		jQuery('.field-error').html(error).addClass('mt10');
	};

	var _showNextButton = function($parent) {
		var total = $parent.data("total");
		var onPage = $parent.data("onpage");
		if (total > onPage) {
			$parent.find('.more-btn-blue').show();
		}
	};

	var _loadMoreComments = function ($parent) {
		$parent.find('.more-btn-blue').hide();

		var portfolioId = $parent.data("portfolioId");
		var onPage = $parent.data("onpage");

		var offset = onPage;
		if ($parent.hasClass('js-has-review')) {
			offset--;
		}

		var data = {
			portfolio_id: portfolioId,
			offset: offset,
		};
		$.get('/api/portfolio/loadcomments', data, function(response) {
			$parent.find('.gig-reviews-list').append(response.html);

			onPage += response.count;
			if ($parent.hasClass('js-has-review')) {
				response.total++;
			}
			$parent.data('onpage', onPage);
			$parent.data('total', response.total);

			var $counter = jQuery('.js-portfolio-comments-counter');
			var $parentItem = $counter.closest('.portfolio-like-views__item');

			$counter.text(response.total);
			$parentItem.attr('data-count', response.total||0);
			_isShowAboutStats();

			_showNextButton($parent);
		}, 'json');
	};

	/**
	 * Событие на клавишу ESC
	 */
	var _onEscHandler = function(e) {
		e = e || window.e;
		var keyCode = e.which || e.keyCode;

		if (keyCode === 27) {
			_closePopup();
		}
	};

	/**
	 * События на стрелки влево/вправо
	 */
	var _onArrowHandler = function(e) {
		e = e || window.e;
		var keyCode = e.which || e.keyCode;
		var $popup = jQuery('.' + classes.popup);
		var popupIsset = $popup.length;
		var textFiled = $popup.find('#message_body');

		if (!popupIsset) {
			return;
		}

		if (textFiled.is(':focus')) {
			return;
		}

		// go to the right
		if (keyCode === 39 && _portfolioIds['next']) {
			_getPortfolio(_portfolioIds['next']);
		}
		// go to the left
		if (keyCode === 37 && _portfolioIds['prev']) {
			_getPortfolio(_portfolioIds['prev']);
		}
	};

	/**
	 * Задаем пути в URL браузера
	 * @param {*} portfolioId - Id портфолио (если выставить 'pageUrl' - вернет прежний URL)
	 */
	var _setPortfolioUrl = function(portfolioId) {
		if (!portfolioId) {
			return;
		}

		// возвращаем прежний URL страницы
		if (portfolioId == 'pageUrl') {
			history.replaceState({}, null, pageUrl);
			return;
		}

		history.replaceState({}, null, '/portfolio/' + portfolioId);
	};

	/**
	 * Возвратить прежний урл страницы
	 */
	function _setDefaultPortfolioUrl() {
		_setPortfolioUrl('pageUrl');
		pageUrl = null;
	}

	/**
	 * Обновить список идентификаторов портфолио на странице по селектору карточек.
	 * Использвуется на странице каталога
	 * @private
	 */
	var _updateIds = function() {
		var currentIds = $("." + classes.portfolioCard).map(function() {
			return $(this).data('id');
		}).get();

		allIds = currentIds.join();
	};

	/**
	 * Прилипание кнопки лайка при скролле
	 *
	 * @param isStandAlone
	 */
	var _likePositionScroll = function (isStandAlone) {
		var portfolioLikeFixed = jQuery('.portfolio-large__like-wrapper'),
			portfolio = jQuery('.portfolio-large'),
			portfolioBottom = 0,
			portfolioHeight,
			scrolledEl;

		if (portfolio.length && portfolioLikeFixed.length) {
			portfolioHeight = portfolio.outerHeight(true);

			if (isStandAlone) {
				scrolledEl = jQuery(window);
				portfolioBottom += portfolio.offset().top;
			} else {
				scrolledEl = jQuery('.' + classes.popup);
			}
			portfolioBottom += portfolioHeight - scrolledEl.height();

			if (portfolioHeight > jQuery(window).height()) {
				portfolioLikeFixed.addClass('portfolio-large__like-wrapper_fixed');
			} else {
				portfolioLikeFixed.removeClass('portfolio-large__like-wrapper_fixed');
			}

			scrolledEl.on('scroll resize', function() {
				portfolioHeight = portfolio.outerHeight(true);
				portfolioBottom = portfolioHeight - scrolledEl.height();
				if (isStandAlone) {
					portfolioBottom += portfolio.offset().top;
				}
				if (jQuery(this).scrollTop() > portfolioBottom) {
					portfolioLikeFixed.removeClass('portfolio-large__like-wrapper_fixed');
				} else {
					portfolioLikeFixed.addClass('portfolio-large__like-wrapper_fixed');
				}
			});
		}
	};

	/**
	 * Прилипание кнопки лайка при загрузке страницы
	 */
	var _likePosition = function () {
		var isStandAlone = !!jQuery('.' + classes.viewPage).length,
			portfolioItem = jQuery('.' + (isStandAlone ? classes.viewPage : classes.popup) + ' .content-item:not(.portfolio-video-wrapper)'),
			portfolioItemImg = jQuery(portfolioItem[portfolioItem.length - 1]).find('img');

		if (!isStandAlone && !jQuery('.portfolio-large').hasClass('js-one-video')) {
			portfolioItemImg.on('load', function () {
				_likePositionScroll(isStandAlone);
			});
		} else {
			setTimeout(
				_likePositionScroll(isStandAlone),
				250
			);
		}
	};

	/**
	 * Создает видимую область подгрузки изображения работы
	 */
	var _preloadPortfolioImg = function() {
		jQuery('.content-item:not(.portfolio-video-wrapper)').each(function() {
			var _this = jQuery(this),
				itemImg = _this.find('.has-loader'),
				itemLoader = _this.find('.content-item__loader');

			itemImg.one('load', function() {
				itemLoader.remove();
				itemImg.removeClass('has-loader');
			}).each(function() {
				if (this.complete) {
					jQuery(this).load();
				}
			});
		});
	};

	/**
	 * Показ полного описания работы
	 */
	var _showFullDescription = function () {
		jQuery(this).closest('.portfolio-about__description').find('.js-about-description').toggleClass('hidden');
	};

	var _init = function() {
		var _self = this;
		var $body = jQuery('body');

		if (_overlayClose) {
			// Закрытие по оверлею
			var closeSelects = '.' + classes.popup
				+ ', .' + classes.wrapperContent
				+ ', .' + classes.fixBottom;

			$body
				.off('click touchend', closeSelects)
				.on('click', closeSelects, function(e) {
					if (e.target !== this) {
						return;
					}
					_closePopup();
				});
		}

		// Закрытие по нажатию на ESC
		$body
			.off('keyup', _onEscHandler)
			.on('keyup', _onEscHandler);

		// Переключение между работ по стрелочкам с клавиатуры
		$body
			.off('keyup', _onArrowHandler)
			.on('keyup', _onArrowHandler);

		$body
			.on('click', '.js-portfolio-like', function () {
				if ($(this).attr('disabled')) {
					return false;
				}
				$('.js-portfolio-like').attr('disabled', true);

				var portfolioId = $(this).data('id');
				var portfolio = $('.portfolio-large');
				var counter = $('.js-portfolio-like-counter');
				var counterNew = parseInt(counter.text());
				var parentItem = counter.closest('.portfolio-like-views__item');

				if (portfolio.hasClass('portfolio-large_has_like')) {
					counterNew -= 1;
					if (counterNew < 0) {
						counterNew = 0;
					}

				} else {
					counterNew += 1;
				}
				portfolio.data('porfolio-likes', counterNew);
				counter.text(counterNew);
				parentItem.attr('data-count', counterNew||0);
				_isShowAboutStats();

				portfolio.toggleClass('portfolio-large_has_like');

				$.ajax({
					type: 'POST',
					url: '/portfolio_like/' + portfolioId,
					success: function (data) {
						$('.js-portfolio-like').attr('disabled', false);
						if(data.success === true) {
							//Обновляем показатели портфолио
							_updatePortfolioAbout(portfolioId);
						}

					}
				});
			});

		// Показ полного описания работы
		$body
			.off('click touchend', '.js-about-description', _showFullDescription)
			.on('click', '.js-about-description', _showFullDescription);

		// Возвратить прежний урл страницы
		$body
			.off('click touchend', '.js-default-portfolio-url', _setDefaultPortfolioUrl)
			.on('click', '.js-default-portfolio-url', _setDefaultPortfolioUrl);

		// Функции для страницы просмотра работы
		if (jQuery('.' + classes.viewPage).length) {
			// Прилипание кнопки лайка при загрузке страницы и скролле
			_likePosition();

			// Создаем видимую область подгрузки изображения работы
			_preloadPortfolioImg();
		}
	}();

	var _isShowAboutStats = function() {
		var $aboutStats = jQuery('.portfolio-about__stats');
		var countHiddenItem = $aboutStats.find('.portfolio-like-views__item[data-count!="0"]').length || 0;

		if (countHiddenItem) {
			$aboutStats.removeClass('hidden');
		} else {
			$aboutStats.addClass('hidden');
		}
	};

	return {
		getPortfolio: function(portfolioId, dontChangeId) {
			if (!dontChangeId) {
				_startPortfolioId = portfolioId;
			}
			_getPortfolio(portfolioId);
		},
		getFirstPortfolio: function(kworkId) {
			_getFirstPortfolio(kworkId);
		},
		close: function() {
			_closePopup();
		},
		next: function() {
			if (_portfolioIds['next']) {
				_getPortfolio(_portfolioIds['next']);
			}
		},
		prev: function() {
			if (_portfolioIds['prev']) {
				_getPortfolio(_portfolioIds['prev']);
			}
		},
		setMode: function (mode) {
			_portfolioMode = mode;
		},
		setAllIds: function (ids) {
			allIds = ids;
		},
		setUpdatingIdsMode: function () {
			_updatingIdsMode = true;
		},
		loadMoreComments: function (parent) {
			_loadMoreComments(parent)
		},
		standAlone: function() {
			_eventOpen(true);
		},
		isShowPortfolioEditField: function(param) {
			_isShowPortfolioEditField(param);
		},
		updatePortfolioAbout: function(param) {
			_updatePortfolioAbout(param);
		},
		setBgLoad: function(state) {
			_bgLoad = state;
		},
		setCloseAction: function(fn) {
			_closeAction = fn;
		},
		isShowAboutStats: function() {
			_isShowAboutStats();
		}
	};
};

var portfolioCard = new portfolioCardModule();
