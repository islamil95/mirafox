/* ===========================================================
 * trumbowyg.allowTagsFromPaste.js v1.0.2
 * It cleans tags from pasted text, whilst allowing several specified tags
 * http://alex-d.github.com/Trumbowyg
 * ===========================================================
 * Author	: Fathi Anshory (0x00000F5C)
 * Twitter	: @fscchannl
 * Notes:
 *  Обрабатываем текстовое поле так чтобы все строки оборачивались в <p>
 */

(function ($) {
	'use strict';

    function removeLineBreak(text) {
        var res = text.replace(/\r?\n/g, '<br>').split('<br>')
		var html = '';
		if(res.length == 1) {
			return text;
		}
		for(var i=0; i<res.length; i++) {
			if(res[i] != '') {
				html += '<div>' + res[i] + '</div>';
			} else {
				html += '<div><br/></div>';
			}
		}
        return html;
    }
		
	// Проверяем если вставляемый текст вместе с существующем привышает лимит, то обрезаем вставляемый текст
	function cropText(trumbowyg, text) {
		
		text = _.escape(text);
		var html = window.emojiReplacements.preSubmitMessage(trumbowyg.$ed.html()) + text;
		
		var notNeededСharactersCount = html.trim().length - 4000;
		if(notNeededСharactersCount > 0) {
			text = text.slice(0, -notNeededСharactersCount);
		}
		return text;
	}
	
	$.extend(true, $.trumbowyg, {
		plugins: {
			lineBreak: {
				init: function (trumbowyg) {
					trumbowyg.o.removeformatPasted = false;
					trumbowyg.pasteHandlers.push(function (e) {
						e.preventDefault();

                        if (window.getSelection && window.getSelection().deleteFromDocument) {
                            window.getSelection().deleteFromDocument();
                        }

                        try {
                            // IE
                            var text = window.clipboardData.getData('Text');						
							
							text = cropText(trumbowyg, text);

                            try {
                                // <= IE10
                                trumbowyg.doc.selection.createRange().pasteHTML(removeLineBreak(text));
                            } catch (c) {
                                // IE 11
                                trumbowyg.doc.getSelection().getRangeAt(0).insertNode(t.doc.createTextNode(removeLineBreak(text)));
                            }
                            trumbowyg.$c.trigger('tbwchange', e);
                        } catch (d) {
                            // Not IE
							var elem = (e.originalEvent || e);
							var text = elem.clipboardData.getData('text/plain');							
							
							text = cropText(trumbowyg, text);
							
                            trumbowyg.execCmd('insertHTML', removeLineBreak(text));
                        }
					});
				}
			}
		}
	});
})(jQuery);