/**
 * Инициализируем плагин trumbowyg и пременяем настройки для отображения и работы с эможи
 */
export default class TrumbowygEmoji {

	constructor($textarea) {
		
		this.emojiShortcodeTimer = false;
		this.trumbowygEditor = $textarea;
		this.notRemoveTagSelector = ":not(div,p,br,img.message-emoji,span.rangySelectionBoundary)";
		this.lastHtml = "";
			
		this.trumbowygEditor.trumbowyg({
			lang: 'ru',
			fullscreenable: false,
			closable: false,
			btns: [],
			removeformatPasted: false,
			imgDblClickHandler: function() {},
			semantic: false,
		})
		.on('tbwinit', () => {
			this.init();
		})
		.on('tbwblur', () => {
			this.tbwblur();
		})
		.on('tbwfocus', () => {
			this.tbwfocus();
		})
		.on('tbwpaste', () => {
			this.tbwpaste();
		})
		.on("tbwchange", () => {
			this.tbwchange();
		});
	}
	// При вставки текста удаляем форматирование
	tbwpaste() {
		window.trumbowygCaret.beginEdit();	
			
		let $trumbowygEditor = this.trumbowygEditor.siblings(".trumbowyg-editor");
		
		// Удаляем все теги
		let $removeTags = $(this.notRemoveTagSelector, $trumbowygEditor);
		while($removeTags.length > 0) {
			let html = $removeTags.eq($removeTags.length-1).html();
			if (html == '') {
				$removeTags.eq($removeTags.length-1).remove();
			} else {
				$removeTags.eq($removeTags.length-1).replaceWith(html);
			}
			$removeTags = $(this.notRemoveTagSelector, $trumbowygEditor);
		}

		twemoji.parse($trumbowygEditor.get(0), {
			base: Utils.cdnImageUrl('/'),
			folder: 'emoji',
			ext: '.svg',
			className: 'message-emoji',
		});
		
		let $caret = $trumbowygEditor.find('.rangySelectionBoundary');
		$trumbowygEditor.scrollTop($caret.css('display', 'block')[0].offsetTop - $trumbowygEditor.innerHeight());
		$caret.find('.rangySelectionBoundary').css('display', 'none');
		
		let html = window.trumbowygCaret.getHtml(this.trumbowygEditor);
		
		window.trumbowygCaret.endEdit(html);
	}
	tbwchange() {
		// Обработка вставок эмоджи (шорткоды и unicode символы)
		if (!this.emojiShortcodeTimer) { 
			this.emojiShortcodeTimer = true;
			setTimeout(() => {				
				window.trumbowygCaret.beginEdit();	
				let $trumbowygEditor = this.trumbowygEditor.siblings(".trumbowyg-editor");
				// Если в тексте только один тег br оборачиваем все в p
				
				let $rangySelectionBoundary = $('span.rangySelectionBoundary',$trumbowygEditor);
				
				let isWrapDiv = $trumbowygEditor.find(':not(img.message-emoji,span.rangySelectionBoundary)').length == 1 && $trumbowygEditor.find('br').length;
				
				// Mеняем p на div	
				$("p", $trumbowygEditor).each(function() {
					$(this).replaceWith('<div>'+$(this).html()+'</div>');
				});
				
				// Удаляем пустые div
				$("div:empty", $trumbowygEditor).remove();
				
				twemoji.parse($trumbowygEditor.get(0), {
					base: Utils.cdnImageUrl('/'),
					folder: 'emoji',
					ext: '.svg',
					className: 'message-emoji',
				});
				
				let html = window.trumbowygCaret.getHtml(this.trumbowygEditor);
				
				html = window.emojiReplacements.replaceSpecificСode(html);

				html = window.emojiReplacements.replaceCodeToEmoje(html);		
				
				if(isWrapDiv) {
					html = '<div>' + html + '</div>';
				}	
				
				html = window.trumbowygCaret.replaceLastHtml(html, this.lastHtml, 4000);
				this.lastHtml = html;
				window.trumbowygCaret.endEdit(html);
				
				window.sendForm.calcMessageLength(window.emojiReplacements.preSubmitMessage(this.trumbowygEditor.trumbowyg('html')));
				
				this.emojiShortcodeTimer = false;
			}, 100);
		}
	}
	
	/**
	 * Удаляем теги если поле пустое
	 */
	tbwblur() {
		let html = this.trumbowygEditor.trumbowyg('html') || '';
		if (
			html == ''
			|| html == '<br>'
			|| html == '</br>'
			|| html == '</ br>'
			|| html == '<div><br></div>'
			|| html == '<div></br></div>'
			|| html == '<div></ br></div>'
		) {
			this.trumbowygEditor.trumbowyg('empty');
		}
	}
	
	
	/**
	 * Добавляем теги что бы на всех устройствах и браузрех был предсказуемый результат
	 */
	tbwfocus() {		
		if(this.trumbowygEditor.trumbowyg('html')=='') {
			this.trumbowygEditor.trumbowyg('html', '<div><br></div>');

			var el = this.trumbowygEditor.parent().find('.trumbowyg-editor')[0];
			var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], 0);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}
	}
	
	init() {	
		this.lastHtml = this.trumbowygEditor.val();
		let $trumbowygEditorTextArea = this.trumbowygEditor;
		// Получаем ссылку на dom с атрибутом contenteditable
		let $trumbowygEditor = this.trumbowygEditor.parents('.trumbowyg-box').find('.trumbowyg-editor');
		
		// Добавляем классы к инициализированному элементу, чтобы срабатывали старые обработчики
		$trumbowygEditor.addClass('js-stopwords-check js-stopwords-check-warning js-alt-send js-message-input-focus');
		
		$trumbowygEditor.on('focus', function() {
			$(this).addClass('is-focus');
		});
		
		$trumbowygEditor.on('blur', function() {
			$(this).removeClass('is-focus');
		});
		
		// Клик на эможи
		$trumbowygEditor.on('click', '.message-emoji', function(e) {
			let $emoji = $(this);
			let emojiHtml = $emoji.get(0).outerHTML;
			
			// Определяем с какой стороны на эможи был клик
			let sideClick = (e.pageX - $emoji.offset().left > $emoji.width()/2) ? 'right' : 'left';
			
			// Получаем фокус на текстовое поле с кареткой
			window.trumbowygCaret.focusCaret($trumbowygEditorTextArea);
			
			// Получем текст с меткой позиции каретки и начинаем режим редактирования
			window.trumbowygCaret.beginEdit();
			let html = window.trumbowygCaret.getHtml($trumbowygEditorTextArea);
			let cutCaret = window.emojiReplacements.cutCaret(html);	
			html = cutCaret.html;			
			
			// Ищем позицию эможи в тексте(конец тега эможи)
			let emojiIndex = -emojiHtml.length;
			// Смотрим на какой по счету эможи кликнули
			let countEmoji = $trumbowygEditor.find('img[src="'+$emoji.attr('src')+'"]').index($emoji)+1;
			for(let i=0; i<countEmoji; i++) {
				emojiIndex = html.indexOf(emojiHtml, emojiIndex + emojiHtml.length);
			}
			// Определяем индекс вставки корретки в зависимости с какой стороны эможи кликнул (слева/справа)
			emojiIndex = (sideClick == 'right') ? emojiIndex + emojiHtml.length : emojiIndex;
			
			// Вставляем каретку полсе элемента
			html = html.substring(0, emojiIndex) + cutCaret[0] + html.substring(emojiIndex);
			
			window.trumbowygCaret.endEdit(html);		
		});
	}
}