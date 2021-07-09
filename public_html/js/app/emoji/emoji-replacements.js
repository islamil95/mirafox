/**
 * Класс содержит функции разных тип замен эможи
 */
export class EmojiReplacements {

	constructor() {
		// Регулярное выражение тега каретки
		this.rangyRe = /(\<span[^]*?id="selectionBoundary[^>]*\>[^]*?<\/span\>)/gi;
		
		// Список шорт кодов(регулярное выражение) и соответствующий код эможи
		// Каждая регулярна начинается с () запомнинаем пустой символ, так как для ;) нужно запоминать символы которые мы не приобразовываем
		this.shortcode = [
			{
				// <3
				reg: /&lt;3/g,
				code: "2764",
			},
			{
				// </3
				reg: /&lt;\/3/g,
				code: "1f494",
			},
			{
				// 8)
				reg: /8\)/g,
				code: "1f60e",
			},
			{
				// :|
				reg: /:\|/g,
				code: "1f610",
			},
			{
				// :o)
				reg: /:o\)/g,
				code: "1f435",
			},
			{
				// =) =-)
				reg: /=\)|=-\)/g,
				code: "1f603",
			},
			{
				// :D :-D
				reg: /:D|:-D/g,
				code: "1f601",
			},
				// ;) ;-) заменяется отдельной функцией, так как баг из-за спецсимволов
			{
				// :> :->
				reg: /:&gt;|:-&gt;/g,
				code: "1f606",
			},
			{
				// :o :-o
				reg: /:o|:-o/g,
				code: "1f62e",
			},
			{
				// >:( >:-(
				reg: /&gt;:\(|&gt;:-\(/g,
				code: "1f620",
			},
			{
				// :) (: :-)
				reg: /:\)|\(:|:-\)/g,
				code: "1f642",
			},
			{
				// :( ): :-(
				reg: /:\(|\):|:-\(/g,
				code: "2639",
			},
			{
				// :p :-p :b :-b
				reg: /:p|:-p|:b|:-b/g,
				code: "1f61b",
			},
				// ;p ;-p ;b ;-b заменяется отдельной функцией, так как баг из-за спецсимволов
			{
				// :* :-*
				reg: /:\*|:-\*/g,
				code: "1f48b",
			},
		];
	}	
	
	
	/**
	 * Отдельная функция для замены эможи так как не правильно отображаются рядом со спецсимволами ;);-)
	 * @param {string} html - текст сообщения
	 */
	replaceCodeTo1f609(html) {
		let newHtml = html;
		// Ищем ошибочный смайл, если перед ) идут спецсимволы типа &nbsp;
		let reg = /([&][^ ;]+;)(\))/g;
		newHtml = newHtml.replace(reg, '$1&#041;');
		// Ищем ошибочный смайл, если перед -) идут спецсимволы типа &nbsp;
		reg = /([&][^ ;]+;)(-\))/g;	
		newHtml = newHtml.replace(reg, '$1&#8211;&#041;');
		// Ищем настоящие смайлы
		reg = /;\)|;-\)/g;
		newHtml = newHtml.replace(reg, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/1f609.svg") + '" alt="&#x1f609;" />');
		// Обратно меняем спецсимволы
		newHtml = newHtml.replace(/&#8211;/g, '-');
		newHtml = newHtml.replace(/&#041;/g, ')');
		return newHtml;	
	}
	
	/**
	 * Отдельная функция для замены эможи так как не правильно отображаются рядом со спецсимволами ;p ;-p ;b ;-b
	 * @param {string} html - текст сообщения
	 */	
	replaceCodeTo1f61c(html) {
		let newHtml = html;
		// Ищем ошибочный самйл, если перед p идет спецсимволы типа &nbsp;
		let reg = /([&][^ ;]+;)(p)/g;
		newHtml = newHtml.replace(reg, '$1&#80;');
		// Ищем ошибочный самйл, если перед -p идет спецсимволы типа &nbsp;
		reg = /([&][^ ;]+;)(-p)/g;	
		newHtml = newHtml.replace(reg, '$1&#8211;&#80;');
		// Ищем ошибочный самйл, если перед b идет спецсимволы типа &nbsp;
		reg = /([&][^ ;]+;)(b)/g;
		newHtml = newHtml.replace(reg, '$1&#98;');
		// Ищем ошибочный самйл, если перед -b идет спецсимволы типа &nbsp;
		reg = /([&][^ ;]+;)(-b)/g;	
		newHtml = newHtml.replace(reg, '$1&#8211;&#98;');
		// Ищем настоящие смайл
		reg = /();p|;-p|;b|;-b/g;
		newHtml = newHtml.replace(reg, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/1f61c.svg") + '" alt="&#x1f61c;" />');
		// Обратно меняем спецсимволы
		newHtml = newHtml.replace(/&#8211;/g, '-');
		newHtml = newHtml.replace(/&#98;/g, 'b');
		newHtml = newHtml.replace(/&#80;/g, 'p');
		return newHtml;	
		
	}	
	
	/**
	 * Отдельная функция для замены эможи так как не правильно отображаются при вставки ссылки :/ :-/ :\ :-\
	 * @param {string} html - текст сообщения
	 */
	replaceCodeTo1f615(html) {
		let newHtml = html;		
		// Ищем ошибочный самйл, если перед / идет http: или https:
		let reg = /(http[s]*:)(\/)/gi;
		newHtml = newHtml.replace(reg, '$1&#47;');
		// Ищем ошибочные смайлы в путях файловой системы
		reg = /([a-z]:)(\\)([^\\:*?« ]*)/gi;
		newHtml = newHtml.replace(reg, '$1&#92;$3');
		
		reg = /:\/|:-\/|:\\|:-\\/g;
		newHtml = newHtml.replace(reg, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/1f615.svg") + '" alt="&#x1f615;" />');
		
		newHtml = newHtml.replace(/&#92;/g, '\\');
		newHtml = newHtml.replace(/&#47;/g, '/');
		return newHtml;			
	}	
	
	/**
	 * Отдельная функция для замены эможи так как не правильно отображаются при вставки ссылки :/ :-/ :\ :-\
	 * @param {string} html - текст сообщения
	 */
	replaceCodeTo1f627(html) {
		let newHtml = html;		
		// Ищем ошибочные смайлы в путях файловой системы
		let reg = /([a-z])(:)(\\[^\\:*?« ]*)/gi;
		newHtml = newHtml.replace(reg, '$1&#58;$3');
		
		reg = /D:/g;
		newHtml = newHtml.replace(reg, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/1f627.svg") + '" alt="&#x1f627;" />');
		
		newHtml = newHtml.replace(/&#58;/g, ':');
		return newHtml;			
	}	
	
	/**
	 * Заменяем шорткоды эможи которые требуют особой обработки
	 * @param {string} html - текст сообщения
	 */	
	replaceSpecificСode(html) {
		let newHtml = html;	
		
		// Вырезаем каретку
		let cutCaret = this.cutCaret(newHtml);		
		newHtml = cutCaret.html;
		
		newHtml = this.replaceCodeTo1f609(newHtml);
		newHtml = this.replaceCodeTo1f61c(newHtml);
		newHtml = this.replaceCodeTo1f615(newHtml);
		newHtml = this.replaceCodeTo1f627(newHtml);
		
		// Вставляем каретку обратно так чтобы она не оказался внутри названия тега		
		newHtml = this.insertCaret(cutCaret, newHtml);
		
		return newHtml;
	}
	
	/**
	 * Формируем новый текст с изображениями эможи если есть шорткоды.
	 * @param {string} html - текст сообщения
	 */
	replaceCodeToEmoje(html) {
		let newHtml = html;
		for (let element of this.shortcode) {
			newHtml = newHtml.replace(element.reg, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/" + element.code + ".svg") + '" alt="&#x' + element.code + ';" />');
		}
		return newHtml;		
	}	
	
	/**
	 * Заменяем </div><img /> на <img/><div>
	 * @param {string} html - текст сообщения
	 */
	divImgToImgDiv(html) {
		let reg = /(\<br[^>]*><\/div\>|<\/div\>)(\<img[^>]*\>)/gi;
		html = html.replace(reg, "$2$1" );
		reg = /(\<br[^>]*><\/div\>|<\/div\>)(\<span[^]*?id="selectionBoundary[^>]*\>[^]*?<\/span\>)/gi;
		return html.replace(reg, "$2$1" );
	}
	
	/**
	 * Убираем лишние атрибуты
	 * @param {string} html - текст сообщения
	 */
	clearAttrs(html) {
		let newHtml = html;
		
		let reg = /alt="[^"]*"/gi;		
		newHtml = newHtml.replace(reg, "alt=\"\"");
		
		reg = /draggable="[^"]*"/gi;		
		newHtml = newHtml.replace(reg, "");
		
		return newHtml;
	}	
	
	/**
	 * Вырезаем каретку и возращаем позицию
	 * @param {string} html - текст сообщения
	 */
	cutCaret(html) {	
		let res = this.rangyRe.exec(html);
		if(res) {
			res.html = html.replace(this.rangyRe, "");
		} else {
			// Если каретки нет то предаем тот же html
			res = {html: html};
		}
		return res;		
	}
	
	/**
	 * Проверяем наличие каретки
	 * @param {string} html - текст сообщения
	 */
	hasCaret(html) {
		return this.rangyRe.test(html);
	}	
	
	/**
	 * Вставляем текст перед кареткой
	 * @param {string} html - текст сообщения
	 */
	insertBeforeCaret(html, insertText) {
		return html.replace(this.rangyRe, insertText+"$1");
	}
	
	/**
	 * Вставляем каретку так чтобы она не оказался внутри названия тега
	 * @param {object} cutCaret результат функции this.cutCaret
	 * @param {string} html - текст сообщения
	 */
	insertCaret(cutCaret, html) {
		// Если корретки нет, то ни чего не делаем
		if(cutCaret.index === undefined) {
			return html;
		}
		let newHtml = html;
		newHtml = newHtml.substring(cutCaret.index);
		
		let beginIndexTag = newHtml.search("<");
		let endIndexTag = newHtml.search(">");
		let insertIndex = cutCaret.index;
		if(endIndexTag < beginIndexTag || (beginIndexTag == -1 && endIndexTag != -1)) {
			insertIndex = insertIndex + endIndexTag + 1;
		}
		
		newHtml = html.substring(0, insertIndex) + cutCaret[0] + html.substring(insertIndex);
		
		return newHtml;		
	}
	
	/**
	 * Заменяем изображения emoji на код для бэкенда, и убираем все теги и раставляем перенос строки
	 * @param {string} html - текст
	 */
	preSubmitMessage(html) {
		html = this.imgToShortcode(html);
		
		// Ищем все путые <div> с переносом строки внутки и заменяем на один перенос строки
		let reg = new RegExp('<div[^>]*><br[^>]*></div>');
		html = html.replace(reg, '\n');
		
		// Убираем <br> в <br></div>
		reg = new RegExp('<br[^>]*>(</div>)', 'g');
		html = html.replace(reg, '$1');
		
		// Все остальные заменяем с переносом строки
		reg = new RegExp('<div[^>]*>(.*?)</div>', 'g');
		html = html.replace(reg, '$1\n');	
		
		// Ищем br в конце и удаляем его
		reg = new RegExp('<br[^>]*>$');
		html = html.replace(reg, '');
		
		// Ищем br и заменяем на перенос строки
		reg = new RegExp('<br[^>]*>', 'g');
		html = html.replace(reg, '\n');
		
		// Убираем все теги
		reg = new RegExp('<[^>]*>', 'g');
		html = html.replace(reg, '');	
		html = _.unescape(html);
		
		// убираем неразрывные пробелы
		html = html.replace(/&nbsp;/g, ' ');	
		
		// убираем перенос в конце строки
		html = html.replace(/\n*$/, '');	
		
		return html;	
	}
	
	
	/**
	 * Заменяем изображения emoji на код
	 * @param {string} html - текст
	 */
	imgToShortcode(html) {
		let reg = new RegExp('<img[^>]*src="[^"]*/([^/]*).svg[^>]*>', 'g');
		return html.replace(reg, '[:$1]');
	}
	
	
	/**
	 * Заменяем изображения emoji на код
	 * @param {string} html - текст
	 */
	spanToshortcode(html) {
		let reg = new RegExp('<span[^>]*class="[^"]*message-emoji-icon_([^"]*)".*?</span>', 'g');
		return html.replace(reg, '[:$1]');
	}
	
	/**
	 * Заменяем код на emoji
	 * @param {string} html - текст
	 */
	shortcodeToImg(html) {		
		let reg = /\[:([^\]]*)\]/g;
		let res = html.match(reg);
		if (res) {
			for (let row of res) {
				let code = row.replace(/\[:([^\]]*)\]/g, "$1");
				let unicode = this.codeToUnicode(code);
				let regexp = new RegExp("\\[:("+code+")\\]", "g");
				html = html.replace(regexp, '<img class="message-emoji" src="' + Utils.cdnImageUrl("/emoji/$1.svg") + '" alt="'+unicode+'">');
			}
		}
		return html;
	}
	
	/**
	 * Заменяем код на emoji
	 * @param {string} code - строка кода эможи
	 */
	
	codeToUnicode(code) {
		return '&#x'+code.replace(/-/g, ';&#x') + ';'
	}
	
	/**
	 * Заменяем шорткод на emoji
	 * @param {string} html - текст
	 */
	shortcodeToSpan(html) {
		let reg = /\[:([^\]]*)\]/g;
		let res = html.match(reg);
		if (res) {
			for (let row of res) {
				let code = row.replace(/\[:([^\]]*)\]/g, "$1");
				let unicode = this.codeToUnicode(code);
				let regexp = new RegExp("\\[:("+code+")\\]", "g");
				html = html.replace(regexp, '<span class="message-emoji-icon message-emoji-icon_$1"><img src="' + Utils.cdnImageUrl("/emoji/emoji-blank.png") + '" alt="'+unicode+'"></span>');
			}
		}
		return html;
	}
}

// Глобальный класс, можно применять к любому редактору Trumbowyg	
window.emojiReplacements = new EmojiReplacements();