/**
 * Класс ReviewFormComponent отправляет отзыв/ответ, 
 * отображает прогресс набора, 
 * не дает набрать символов больше положенного
 * отображает отправленные отзывы/ответы
 *
 * @param {jquery object} reviewForm - форма отзыва/ответа.
 */
function ReviewFormComponent(reviewForm) {
	
	this.reviewForm = reviewForm;
	
	this.messageBodyClass = '.js-message-body';
	
	this.messageBody = this.reviewForm.find(this.messageBodyClass);
	this.reviewFormClass = '.send-review-js';
	this.errorClass = '.field-error';
	
	this.lastHtml = '';
	
	this.checkAjax = null;
	
	this.checkTimer = null;
	
	this.spaceAdded = false;
	
	this.reviewErrors = {
		'bad_words': t('Исключите запрещенные слова: '),
		'duplicate_symbols': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
		'big_word': t('Превышена максимальная длина слов'),
		'small_word': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
		'word_mistakes': t('Необходимо исправить ошибки или опечатки в тексте.\nСлова с ошибками подчеркнуты красным.'),
		'duplicate_description': t('Слишком много повторов'),

		'not_authorized': t('Пользователь не авторизован'),
		'not_found_review': t('Не найден отзыв'),
		'not_allowed': t('Пользователю не разрешено редактировать/оставлять комменатрий к отзыву'),
		'not_valid_text': t('Невалидный текст'), /*в поле data.extra содержит пояснение почему не валидно*/
		'not_created': t('Не удалось сохранить ответ'),
		'not_edited': t('Не удалось отредактировать ответ'),

		'too_fast_comment': t('Прошло слишком мало времени с вашего предыдущего комментария'),
	};
	
	this.init();
}
/**
 * Инициализируем класс
 * 
 */
ReviewFormComponent.prototype.init = function () {	
	this.messageBody.trumbowyg({
		lang: 'ru',
		semantic: false,
		fullscreenable: false,
		closable: false,
		btns: [],
		removeformatPasted: true
		}).on('tbwfocus', function() {
			trumbowygInit($(this));
		});		
	this.reviewForm.find('.trumbowyg-textarea').attr('name', 'comment');
	
	this._setEvents();
	
	this._switchSubmitButton(false);
	
	this.messageBody.trigger('input');
	
	this.reviewForm.data('initialized', true);
};
/**
 * Инициализируем события формы
 * 
 */
ReviewFormComponent.prototype._setEvents = function () {	
	var _self = this;
	_self.messageBody.on('input', function() {
		_self._inputComment()
	});

	_self.messageBody.on('blur', function() {
		var val = _self.messageBody.text();
		if (val.length > 0) {
			if (!_self.spaceAdded) {
				_self.spaceAdded = true;
				_self.messageBody.trigger('input');
			}
		} else {
			_self.messageBody.trumbowyg('html', '');
		}
	});

	_self.reviewForm.off('submit').on('submit', function(e) {
		e.preventDefault();
		_self._submit();
		
	});
};
/**
 * Оброботка отправки на сервер отзыва/(ответа на отзыв)
 * @param textarea
 */
ReviewFormComponent.prototype._submit = function () {	
	var _self = this;
	_self._cancelCurrentCheck();

	_self._switchSubmitButton(false);

	$.ajax({
		type: "POST",
		url: _self.reviewForm.data('action'),
		data: _self.reviewForm.serializeArray(),
		dataType: 'json',
		success: function (rj) {
			if (rj.success) {
				if (rj.data && rj.data.badgePopup) {
					$.popup(rj.data.badgePopup, {
						onclose: function() {
							document.location.reload();
						}
					});
				} else {
					var wrapAppendComment;
					if (_self.reviewForm.attr('data-portfolio-id')) {
						wrapAppendComment = jQuery('.portfolio-large__comments .gig-reviews-list');
						if (wrapAppendComment.length) {
							_self.messageBody.trumbowyg('html', '');
							_self.reviewForm.find(_self.errorClass).html('');
							wrapAppendComment.prepend(rj.comments.html);
						}
					} else {
						wrapAppendComment = _self.reviewForm.data('append-comment');
						if (wrapAppendComment !== undefined) {
							$(wrapAppendComment).html(rj.data.html);
						}
					}

					// Если надо удалить элементы
					var removeSelectors = _self.reviewForm.data('remove-selectors');				 
					if (removeSelectors !== undefined) {
						$(removeSelectors).remove();
					}						
					window.initReviewForm();	
				}
			} else {
				_self._handleErrors(rj);
				_self._switchSubmitButton(true);
			}
		},
		error: function () {
			_self._switchSubmitButton(true);
		}
	});
};
/**
 * Оброботчик набора текста включает:
 * не даем набрать больше положенного текста,
 * показываем прогресс набора текста,
 * проверка на ошибки
 * @param textarea
 */
ReviewFormComponent.prototype._inputComment = function () {	
	var _self = this;
	_self._cancelCurrentCheck();
	var html = _self.messageBody.html();
	var val = _self.messageBody.text();
	
	// Если еще не было ввода, то заполняем текст, который есть сейчас
	if (this.lastHtml == '') {
		// обрезаем по макс. кол символов
		this.lastHtml = val.substring(0, _self.messageBody.data('max-count'));
	}

	if (val.length < 1) {
		_self.reviewForm.find(_self.errorClass).html('');
		_self._switchSubmitButton(false);
	}

	if (val.length > _self.messageBody.data('max-count')) {
		_self.messageBody.html(_self.lastHtml);
		_self.messageBody.trigger('input');
		return;
	}
	// Такой контроль за кол-во сиволов, так-как плагин trumbowyg не умеет устанавливать фокус в конец текста
	_self.lastHtml = html;
	
	this._showProgress(val);
	
	clearTimeout(_self.checkTimer);
	_self.checkTimer = setTimeout(function() {
		_self._checkReviewText();
	}, 1000);
};

/**
 * Визуализируем прогресс набора текста (прогресс бар, кол-во символов, оценка по кол-во текста)
 * 
 */
ReviewFormComponent.prototype._showProgress = function (val) {
	var variants = this.reviewForm.find('.variants');
	
	if (!this.spaceAdded) {
		if (val.indexOf(' ') != -1)
			this.spaceAdded = true;
	}
	var countLetters = val.length;
	variants.find('.count').text(countLetters);
	var partsCount = Math.floor(countLetters / (this.messageBody.data('max-count') / 10));
	if (countLetters >= 10 && countLetters < (this.messageBody.data('max-count') / 10)) partsCount = 1;
	variants.find('.loadbar li div').each(function(k, v) {
		if (partsCount > 0) {
			$(v).addClass('progress-bar-fill');
		} else {
			$(v).removeClass('progress-bar-fill');
		}
		partsCount--;
	});
	if (this.spaceAdded) {
		if (countLetters < 100) {
			variants.attr('data-variant', 'bad');
		} else if (countLetters < 200) {
			variants.attr('data-variant', 'normal');
		} else {
			variants.attr('data-variant', 'good');
		}
	}
};

/**
 * Отменяем текущую проверку на ошибки
 * 
 */
ReviewFormComponent.prototype._cancelCurrentCheck = function () {
	if (this.checkTimer) {
		clearTimeout(this.checkTimer);
	}
	if (this.checkAjax) {
		this.checkAjax.abort();
	}
};
/**
 * Проверяем текст на ошибки
 * 
 */
ReviewFormComponent.prototype._checkReviewText = function () {
	var _self = this;
	var str = _self.messageBody.html();
	if (str.length < 1) return;
	var data = {
		comment: str,
	};

	_self.checkAjax = $.ajax({
		type: "POST",
		url: '/review/check_text',
		data: data,
		dataType: "json",
		success: function(response) {
			_self._handleErrors(response);
		}
	}, 'json');	
};
/**
 * Показываем ошибки
 *
 */
ReviewFormComponent.prototype._handleErrors = function (response) {
	var error = '', good = true;

	if ('result' in response) {
		response = response.result;
	}

	if ('mistakes' in response) {
		var keepedSelection = null;
		try {
			keepedSelection = rangySelectionSaveRestore.saveSelection();
		} catch(e) {}
		var html = this.messageBody.html();
		html = applyWordErrors(html, response.mistakes);
		this.messageBody.trumbowyg('html', html);
		if (keepedSelection) {
			try {
				rangySelectionSaveRestore.restoreSelection(keepedSelection);
			} catch(e) {}
		}
	}

	if ('validError' in response && response.validError) {
		error = this.reviewErrors[response.validError] || '';
		good = false; 
	}

	if ('badWords' in response && response.badWords) {
		error = this.reviewErrors['bad_words'] + response.string;
		good = false;
	}

	if ('emptyString' in response && response.emptyString) {
		error = '';
		good = false;
		if (typeof _self !== 'undefined') {
			_self.messageBody.html('').trigger('input');
		}
	}

	if ('data' in response) {
		error = this.reviewErrors[response.data.reason] || '';
		good = false;
	}

	this._switchSubmitButton(good);

	this.reviewForm.find(this.errorClass).html(error);
};
/**
 * Преключаем кнопку активная/неактивная
 * 
 */
ReviewFormComponent.prototype._switchSubmitButton = function(state) {
	state = (state !== undefined || state !== null || state !== '') ? state : false;

	var button = this.reviewForm.find('.review-submit');
	if (state) {
		button.removeClass('disabled').prop('disabled', false).css({'margin-left': '0px'});
	} else {
		button.addClass('disabled').prop('disabled', true).css({'margin-left': '0.01px'});
	}
};

$(document).ready(function() {
	window.initReviewForm = function() {
		var reviewForm = $('.send-review-js');
		// Инициализируем формы отзывов, только те, которые не инициализированы
		$(reviewForm).each(function() {
			if (!$( this ).data('initialized')) {
				var review = new ReviewFormComponent($( this ));
			}
		});
	};
	window.initReviewForm();
});
