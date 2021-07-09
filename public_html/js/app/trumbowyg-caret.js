/**
 * Следит чтобы в плагине trumbowyg не слетала картетка при вставки текста
 *	Пример
 *	var html = window.trumbowygCaret.beginEdit($('#message_body1'));
 *	html = html + 'new text';
 *	window.trumbowygCaret.endEdit(html);
 */
export class TrumbowygCaret {

	constructor() {
		// Регулярное выражение тега каретки
		this.rangyRe = new RegExp('<span id="selectionBoundary_([^"]+)[^]+?class="rangySelectionBoundary"[^]*?>[^]+?<\/span>', 'gi');		
		this.keepedSelection;
		this.$element;
		this.html = "";
		this.area = undefined;
	}
	
	/**
	 * Получаем фокус на текстовую область с кареткой.
	 */	
	focusCaret($element) {
		let editor = $element.siblings(".trumbowyg-editor")[0];
		var s = window.getSelection();
		var r = document.createRange();
		r.setStart(editor, editor.childElementCount);
		r.setEnd(editor, editor.childElementCount);
		s.removeAllRanges();
		s.addRange(r);
	}
	
	/**
	 * Начало работы с кареткой
	 */	
	beginEdit() {		
		// Запоминаем позицию каретки
		try {
			this.keepedSelection = rangySelectionSaveRestore.saveSelection();
		} catch(e) { }
	}
	
	
	/**
	 * Получаем html редактора для редактирования текста вызывается после beginEdit, позволяем между функциями beginEdit и getHtml манипулировать DOM
	 */
	getHtml($element) {
		this.$element = $element;
		let editor = $element.siblings(".trumbowyg-editor")[0];
		this.area = $(editor);
		
		this.html = this.area.html();
		
		let rt = this.replaceRangyTags(this.html);
		this.html = rt.html;
		this.html = this.html.replace(/[\x00-\x1F\x7F-\x9F\uFEFF]/g, '');
		this.html = this.restoreRangyTags(this.html, rt.boundaries);
		return this.html;		
	}
	
	/**
	 * Заменяем текст в редакторе
	 */
	endEdit(newHtml) {
		
		if(newHtml !== this.html) {
			this.area.html(newHtml);
			//this.$element.trumbowyg('html', newHtml);
		}
		
		// Востанавиливаем позицию каретки
		if (this.keepedSelection) {
			try {
				rangySelectionSaveRestore.restoreSelection(this.keepedSelection);
			} catch(e) {}
			// Вставляем текст, иначе при получении текста из редактора может попасть тег <span id="selectionBoundary
			this.html = this.area.html();
			this.$element.val(this.html);
		}
	}
	
	/**
	 * Работа с позицеей каретки
	 */
	replaceRangyTags(html) {
		let boundaries = {};
		html = html.replace(this.rangyRe, function(f, p1) {
			boundaries[p1] = f;
			return '##boundary_' + p1 + '##';
		});
		return {
			html: html,
			boundaries: boundaries,
		};
	}
	
	/**
	 * Работа с позицеей каретки
	 */
	restoreRangyTags (html, boundaries) {
		return html.replace(/##boundary_([^#]+)##/gi, function(f, p1) {
			if (p1 in boundaries) {
				return boundaries[p1];
			}
			return '';
		});
	}	
	
	
	replaceLastHtml(html, lastHtml, max) {						
		// Ограничиваем кол-во символов
		let newCaret = this.rangyRe.exec(html);		
		// Вырезаем каретку так как там невидимый символ
		let newHtml = html.replace(this.rangyRe, '');
		// Очищаем от тегов
		newHtml = window.emojiReplacements.preSubmitMessage(newHtml);
		if(newHtml.trim().length > max) {
			if(newCaret) {
				// Заменяем старую каретку на новую чтобы не сбивалась каретка
				html = lastHtml.replace(this.rangyRe, newCaret[0]);
			} else {
				html = lastHtml.replace(this.rangyRe, '');
			}
		}
		
		return html
	}
}

// Глобальный класс, можно применять к любому редактору Trumbowyg	
window.trumbowygCaret = new TrumbowygCaret();