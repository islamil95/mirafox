/**
 * http://jsfiddle.net/gEhjZ/95/
 */
var	saveSelection = function(containerEl) {
	var doc = containerEl.ownerDocument, win = doc.defaultView;
	var range = win.getSelection().getRangeAt(0);
	var preSelectionRange = range.cloneRange();
	preSelectionRange.selectNodeContents(containerEl);
	preSelectionRange.setEnd(range.startContainer, range.startOffset);
	var start = preSelectionRange.toString().length;
	return {
		start: start,
		end: start + range.toString().length
	}
};
var	restoreSelection = function(containerEl, savedSel) {
	var doc = containerEl.ownerDocument, win = doc.defaultView;
	var charIndex = 0, range = doc.createRange();
	range.setStart(containerEl, 0);
	range.collapse(true);
	var nodeStack = [containerEl], node, foundStart = false, stop = false;
	while (!stop && (node = nodeStack.pop())) {
		if (node.nodeType == 3) {
			var nextCharIndex = charIndex + node.length;
			if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
				range.setStart(node, savedSel.start - charIndex);
				foundStart = true;
			}
			if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
				range.setEnd(node, savedSel.end - charIndex);
				stop = true;
			}
			charIndex = nextCharIndex;
		} else {
			var i = node.childNodes.length;
			while (i--) {
				nodeStack.push(node.childNodes[i]);
			}
		}
	}
	var sel = win.getSelection();
	sel.removeAllRanges();
	sel.addRange(range);
};

function additionalErrorHandle(html, mistakes) {
	var htmlWords = {};

	var doubleBrokenWord = html.match(/([a-zа-яё\-_]*)<span id="selectionBoundary[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>([a-zа-яё\-_]*?)<span id="selectionBoundary[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>([a-zа-яё\-_]*)/i);
	if (doubleBrokenWord) {
		htmlWords[doubleBrokenWord[1] + doubleBrokenWord[2] + doubleBrokenWord[3]] = doubleBrokenWord[0];
	} else {
		var regexp = /([a-zа-яё\-_]*)<span id="selectionBoundary[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>([a-zа-яё\-_]*)/gi;
		var r;
		while (r = regexp.exec(html)) {
			htmlWords[r[1] + r[2]] = r[0];
		}
	};

	$.each(htmlWords, function(k2, v2) {
		html = html.replace(v2, '__htmlword-' + k2 + '__');
	});
	
	$.each(mistakes, function(k, v) {
		var word = v;
		if (word in htmlWords) {
			var regexp = new RegExp('(^|[^a-zа-яё_-])(' + '__htmlword-' + word.replace(/[|\\{}()[\]^$+*?.]/g, '\\$&') + '__' + ')($|[^a-zа-яё_-])', 'gi');
			html = html.replace(regexp, '$1<word-error>$2</word-error>$3');
		}
		var regexp = new RegExp('(^|[^a-zа-яё_-])(' + word.replace(/[|\\{}()[\]^$+*?.]/g, '\\$&') + ')($|[^a-zа-яё_-])', 'gi');
		html = html.replace(regexp, '$1<word-error>$2</word-error>$3');
	});

	$.each(htmlWords, function(k2, v2) {
		html = html.replace('__htmlword-' + k2 + '__', v2);
	});

	return html;
}

/**
 * Получить длину строки без тегов
 *
 * @param html
 * @returns {number}
 * @private
 */
var getTextLengthWithoutTags = function (html) {
	return getTextWithoutTags(html)
		.length ^ 0;
};


/**
 * Получить строку без тегов
 *
 * @param html
 * @returns {number}
 * @private
 */
var getTextWithoutTags = function (html) {
	return window.htmlToText(html)
		.replace(/<\/?[^>]*>/gi, '')
		.replace(/&nbsp;/gi, " ")
		.replace(/([ ]\n)|([ ]\r\n)/gi, '\n')	// Убираем все пробельные символы перед переносом строки
		.replace(/((\r\n)+)|(\n)+/gi, '\n')		// Заменяем двойные или более переносы строки на один
		.replace(/!\r\n/gi,'\n')							// Заменяем переносы с \n на \r\n
		.replace(/[ ][ ]+/g, " ")								// Убираем двойные или более пробелы
		.trim();
};

function trumbowygInit(t) {
	if(t.trumbowyg('html') == '') {
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

function saveBondaries(tagsHtml) {
	var boundaries = {};
	var html = tagsHtml.replace(/<span id="selectionBoundary_([^"]+)[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>/gi, function(f, p1) {
		boundaries[p1] = f;
		return '##boundary_' + p1 + '##';
	});
	return {
		html: html,
		boundaries: boundaries,
	};
}

function restoreBondaries(html, boundaries) {
	html = html.replace(/##boundary_([^#]+)##/gi, function(f, p1) {
		if (p1 in boundaries) {
			return boundaries[p1];
		}
		return '';
	});
	return html;
}

function applyWordErrors(oldHtml, mistakes) {
	var html = oldHtml;
	html = html.replace(/<\/?word-error>/gi, '');

	var handledErrors = [];

	var errorId = -1;
	var errorWord = '';
	var errorWordCaret = 0;
	var errorWordStart = 0;
	var rangyCrop = false;
	var openTag = '<word-error>';
	var closeTag = '</word-error>';
	var errorTags = [];
	var spaceTags = new RegExp('<[^>]*?(?:p|br|li|div)[^>]*?>', 'i');
	var rangyOpenTag = new RegExp('<span id="selectionBoundary[^]+?>', 'i');
	var rangyCloseTag = new RegExp('</span[^>]*?>', 'i');
	var pos = 0;
	var tag = '';
	var mode = 0;

	for (var i = 0; i < html.length; i++) {
		var char = html[i];
		switch(mode) {
			case 0:
				if (char == '<') {
					tag += char;
					mode = 1;
					break;
				}
				if (!errorWord) {
					$.each(mistakes, function(k, v) {
						if (v[0] == pos) {
							errorWordStart = -1;
							errorId = k;
							errorWord = v[1];
							errorWordCaret = 0;
							return false;
						}
					});
				}
				if (errorWord) {
					if (char == errorWord[errorWordCaret]) {
						if (errorWordStart < 0) {
							errorWordStart = i;
						}
						errorWordCaret++;
						if (errorWordCaret >= errorWord.length) {
							errorTags.push([errorWordStart, i + 1]);
							handledErrors.push(errorId);
							errorId = -1;
							errorWord = '';
						}
					}
				}
				if (!rangyCrop) {
					pos++;
				}
				break;
			case 1:
				tag += char;
				if (char == '>') {
					if (rangyCrop && rangyCloseTag.test(tag)) {
						rangyCrop = false;
					} else if (rangyOpenTag.test(tag)) {
						rangyCrop = true;
					} else if (spaceTags.test(tag)) {
						pos++;
					}
					tag = '';
					mode = 0;
				}
				break;
		}
	}

	var newHtml = '';
	for (var i = 0; i < html.length; i++) {
		$.each(errorTags, function(k, v) {
			if (i == v[0]) {
				newHtml += openTag;
			} else if (i == v[1]) {
				newHtml += closeTag;
			}
		});
		newHtml += html[i];
	}

	var unhandledMistakes = [];
	$.each(mistakes, function(k, v) {
		if ($.inArray(k, handledErrors) == -1) {
			unhandledMistakes.push(v[1]);
		}
	});
	
	unhandledMistakes.filter(function(el, index, arr) {
		return index === arr.indexOf(el);
	});

	if (unhandledMistakes.length > 0) {
		newHtml = additionalErrorHandle(newHtml, unhandledMistakes);
	}

	return newHtml;
}


/**
 * Удалить все теги и стили
 * @param tagsHtml
 * @param saveWordError
 * @returns {*}
 */
function removeTags(tagsHtml, saveWordError, saveLines, saveLineBreaks, saveControlChars) {
	var bd = saveBondaries(tagsHtml);
	var html = bd.html;

	if (saveLines) {
		html = html.replace(/<(\/?(?:p|br|div))[^>]*?>/g, '##$1##');
	}

	if (!saveControlChars) {
		html = html.replace(/[\x00-\x1F\x7F-\x9F\uFEFF]/g, '');
	}
	if (!saveLineBreaks) {
		html = html.replace(/\r\n/g, '\n');
		html = html.replace(/\n/g, '');
	}
	html = html.replace(/<style[^>]*>[^]+?<\/style>/gi, '');

	if (saveWordError) {
		html = html.replace(/(?!<\/?(word-error)>)<\/?[\s\w="-:&;?]+>/gi, "");
	} else {
		html = html.replace(/<\/?[^>]*>/gi, '');
	}

	html = html.replace(/&nbsp;/gi, " ");
	html = restoreBondaries(html, bd.boundaries);

	if (saveLines) {
		html = html.replace(/##(\/?(?:p|br|div))##/g, '<$1>');
	}

	return html;
}

/**
 * Считаем количество символов
 * @param $input
 */
function setCurrentInputSymbolCount($input, current, onInit) {
	var min = $input.data('min');
	var max = $input.data('max');

	if (!$input.hasClass('js-ignore-min') && current < min) {
		text = t('{{0}} из {{1}} минимум', [current, min]);
	} else {
		text = t('{{0}} из {{1}} максимум', [current, max]);
	}

	validateSymbolCount($input, current, null, onInit);
}

/**
 * Проверка количества символов на минимальное и максимальное кол-во
 * 
 * @param $input
 * @param $hint 
 * @param currentCount 
 */
function validateSymbolCount($input, currentCount, $hint, onInit) {
	var min = $input.data('min');
	var max = $input.data('max');

	if (min || max) {
		var vfBlock = $input.closest('.vf-block');
		if (vfBlock.length > 0) {
			var $hintCurrent;
			if ($hint) {
				$hintCurrent = $hint;
			} else {
				$hintCurrent = vfBlock.find('.offer-individual__item-hint');
			}
			if ($hintCurrent.length > 0) {
				$hintCurrent.text(text);

				if (((!$input.hasClass('js-ignore-min') && currentCount < min) || currentCount > max) && !onInit) {
					$hintCurrent.addClass('color-red');
				} else {
					$hintCurrent.removeClass('color-red');
				}
			}
		}		
	}
}

/**
 * Набор текста
 */
function onInputEditor(that, needCleanup, onInit) {
	var el = $(that);
	
	if (needCleanup === true) {
		var keepedSelection = null;
		try {
			keepedSelection = rangySelectionSaveRestore.saveSelection();
		} catch(e) {}
		var string = el.html();
		string = removeTags(string, false, true, true, true);
		el.html(string);
		if (keepedSelection) {
			try {
				rangySelectionSaveRestore.restoreSelection(keepedSelection);
			} catch(e) {}
		}
	}

	var string = el.html();
	var en = el.data('en');
	if (en) {
		newString = string.replace(/[А-Яа-яЁё]/g, '');
		if (newString != string) {
			el.html(newString);
		}
		string = newString;
	}

	var max = el.data('max');
	var editorLength = getTextLengthWithoutTags(string);
	var savedValue = el.data('savedValue') || '';
	var diff = editorLength - getTextLengthWithoutTags(savedValue);

	// если для поля задано максимальное значение и оно превышено не даём вводить и вставлять текст
	if (max && (editorLength > max) && (diff >= 0)) {
		var selection = saveSelection(el[0]);

		selection.end = selection.end - diff;
		selection.start = selection.start - diff;

		el.html(savedValue);
		restoreSelection(el[0], selection);

		return;
	} else {
		var $contentStorage = el.siblings('.js-content-storage');
		var textVal = getTextWithoutTags(string);
		$contentStorage.val(textVal).trigger('input');
	}

	el.data('savedValue', string);
	setCurrentInputSymbolCount(el, editorLength, onInit);
}

$(document).ready(function() {
	$(document).on('paste drop', '.of-form .js-content-editor, .js-editor', function(e) {
		var that = this;
		var clipboardContent = null;
		var isHtml = null;
		try {
			document.execCommand('insertText', false, (e.originalEvent || e).clipboardData.getData('text/plain'));
			e.stopPropagation();
			e.preventDefault();
			onInputEditor(that);
		} catch(e) {
			setTimeout(function() { onInputEditor(that, true); }, 0);
		}
	});
	$('.of-form .js-content-editor, .js-editor').each(function (index, el) {
		onInputEditor(el, null, true);
	});
	$(document).on('input', '.of-form .js-content-editor, .js-editor', function() { onInputEditor(this); });
	$(document).on('focus', '.of-form .js-content-editor', function(e) {
		$(e.target).closest('.vf-block').find('.kwork-save-step__field-hint').addClass('active');
	});
	$(document).on('blur', '.of-form .js-content-editor', function(e) {
		var el = $(this);
		var string = el.html();

		var editorLength = getTextLengthWithoutTags(string);
		if (editorLength > 0) {
			$(e.target).closest('.vf-block').find('.offer-individual__item-hint').removeClass('active').removeClass('unactivated');
			var $contentStorage = el.siblings('.js-content-storage');
			var textVal = getTextWithoutTags(string);
			if ($contentStorage.val() !== textVal) 
				$contentStorage.val(textVal);
		}
	});
});