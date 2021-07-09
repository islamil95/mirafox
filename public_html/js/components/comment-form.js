$(document).ready(function() {

	var commentForm = $('.send-comment-js'), variants = $('.variants'), spaceAdded = false, checkTimer, lastHtml = '', checkAjax = null;

	var reviewErrors = {
		'bad_words': t('Исключите запрещенные слова: '),
		'duplicate_symbols': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
		'big_word': t('Превышена максимальная длина слов'),
		'small_word': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
		'word_mistakes': t('Необходимо исправить ошибки или опечатки в тексте.\nСлова с ошибками подчеркнуты красным.'),
		'duplicate_description': t('Слишком много повторов'),
		'add_comment': t('Ошибка при добавлении комментария'),
		'too_fast_comment': t('Прошло слишком мало времени с вашего предыдущего комментария'),
	};

	function switchSubmitButton(state) {
		if (!state) {
			state = false;
		}

		var button = commentForm.find('.review-submit');
		if (state) {
			button.removeClass('disabled').prop('disabled', false).css({'margin-left': '0px'});
		} else {
			button.addClass('disabled').prop('disabled', true).css({'margin-left': '0.01px'});
		}
	}

	function cancelCurrentCheck() {
		clearTimeout(checkTimer);
		if(checkAjax) {
			checkAjax.abort();
		}
	}

	function _handleErrors(v) {
		var error = '', good = true;
		if('mistakes' in v) {
			var keepedSelection = null;
			try {
				keepedSelection = rangySelectionSaveRestore.saveSelection();
			} catch(e) {}
			var html = commentForm.find('#message_body').html();
			html = applyWordErrors(html, v.mistakes);
			commentForm.find('#message_body').trumbowyg('html', html);
			if (keepedSelection) {
				try {
					rangySelectionSaveRestore.restoreSelection(keepedSelection);
				} catch(e) {}
			}
		}
		if('validError' in v && v.validError) {
			error = reviewErrors[v.validError] || '';
			good = false;
		}
		if('badWords' in v && v.badWords) {
			error = reviewErrors['bad_words'] + v.string;
			good = false;
		}
		switchSubmitButton(good);

		var fieldError = commentForm.find('.field-error');
		fieldError.html(error);
		if (error.length) {
			fieldError.addClass('mt10');
		} else {
			fieldError.removeClass('mt10');
		}
	}

	function checkReviewText() {
		var $msgBody = commentForm.find('#message_body');
		if (!$msgBody.length) {
			return;
		}
		var str = $msgBody.html();
		if(str.length < 1) return;
		var data = {
			comment: str,
		}

		checkAjax = $.ajax({
			type: "POST",
			url: '/review/check_text',
			data: data,
			dataType: "json",
			success: function(response) {
				_handleErrors(response.result);
			}
		}, 'json');
	}

	var _trumbowygInit=function(t) {
		if (t.trumbowyg('html') === '') {
			t.trumbowyg('html', '<p><br></p>');
			var el = t.parent().find('.trumbowyg-editor')[0];
			var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], 0);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}
	};

	commentForm.find('#message_body').trumbowyg({
		lang: 'ru',
		semantic: false,
		fullscreenable: false,
		closable: false,
		btns: [],
		removeformatPasted: true
	}).on('tbwfocus', function() {
		_trumbowygInit($(this));
	});
	commentForm.find('.trumbowyg-textarea').attr('name', 'comment');

	commentForm.find('#message_body').on('input', function(e) {
		cancelCurrentCheck();
		var t = $(e.delegateTarget);
		var html = t.html();
		var val = t.text();
		if(val.length < 1) {
			$('.field-error').html('').removeClass('mt10');
		}
		if(val.length > 500) {
			t.html(lastHtml);
			commentForm.find('#message_body').trigger('input');
			return;
		}
		lastHtml = html;
		if(!spaceAdded) {
			if(val.indexOf(' ') != -1)
				spaceAdded = true;
		}
		var l = val.length;
		variants.find('.count').text(l);
		var partsCount = Math.floor(l / 50);
		if(l >= 10 && l < 50) partsCount = 1;
		variants.find('.loadbar li div').each(function(k, v) {
			if(partsCount > 0) {
				$(v).addClass('progress-bar-fill');
			} else {
				$(v).removeClass('progress-bar-fill');
			}
			partsCount--;
		});
		if(spaceAdded) {
			if(l < 100) {
				variants.attr('data-variant', 'bad');
			} else if(l < 200) {
				variants.attr('data-variant', 'normal');
			} else {
				variants.attr('data-variant', 'good');
			}
		}
		clearTimeout(checkTimer);
		checkTimer = setTimeout(checkReviewText, 1000);
	});

	commentForm.find('#message_body').on('blur', function(e) {
		var t = $(e.delegateTarget);
		var val = t.text();
		if(val.length > 0) {
			if(!spaceAdded) {
				spaceAdded = true;
				t.trigger('input');
			}
		} else {
			t.trumbowyg('html', '');
		}
	});

	commentForm.on('submit', function(e) {
		e.preventDefault();

		cancelCurrentCheck();

		var t = $(e.delegateTarget);
		var comment = commentForm.find('#message_body').html();
		var portfolioId = t.data('portfolioId');

		if(!portfolioId || !comment){
			return;
		}
		$.ajax({
			type: "POST",
			url: '/api/portfolio/addcomment',
			data: {
				portfolio_id: portfolioId,
				comment: comment
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					commentForm.find('#message_body').html('').blur();

					$parent = $('.js-portfolio-comments');

					if ($parent.find('.gig-reviews-list>.item-review').length) {
						$parent.find('.gig-reviews-list>.item-review:last').after(response.comments.html);
					} else {
						$parent.find('.gig-reviews-list').prepend(response.comments.html);
					}

					var onPage = $parent.data('onpage') + response.comments.count;
					var total = parseInt(response.comments.total);
					$('.portfolio-large').data('porfolio-comments', total);
					
					if ($parent.hasClass('js-has-review')) {
						total++;
					}
		
					$parent.data('onpage', onPage);
					$parent.data('total', total);
					//Обновляем показатели портфолио
					portfolioCard.updatePortfolioAbout(portfolioId);

					var $counter = jQuery('.js-portfolio-comments-counter');
					var $parentItem = $counter.closest('.portfolio-like-views__item');

					$counter.text(total);
					$parentItem.attr('data-count', total||0);
					portfolioCard.isShowAboutStats();

					if (total === 1) {
						jQuery('.js-portfolio-comments-counter-wrapper').removeClass('hidden');
						jQuery('.portfolio-large__comments-block').addClass('portfolio-large__comments-block_auth');
					}

					var elScroll;
					if (jQuery('.portfolio-view-page').length) {
						elScroll = jQuery('html, body');
					} else {
						elScroll = $parent.closest('.portfolio_large');
					}
					elScroll.animate({scrollTop: (jQuery('.portfolio-large__comments-block .user-review-list').position().top-100)}, 250);

				} else {
					_handleErrors(response.result);
				}
			}
		});
	});

	commentForm.find('#message_body').trigger('input');

});
