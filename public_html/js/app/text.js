window.htmlToText = function(messageBody, handleImages) {
	messageBody = messageBody.replace(/\r?\n/g, '');
	messageBody = messageBody.replace(/<[\s]*\/[\s]*?img[\s]*?>/g, '');

	let mode = 0;
	let lines = [];
	let image = '';
	let text = '';
	let tag = '';
	for (let i = 0; i < messageBody.length; i++) {
		let symbol = messageBody[i];
		if (mode == 0) {
			if (symbol == '<') {
				tag = symbol;
				mode = 1;
			} else {
				text += symbol;
			}
		} else if (mode == 1) {
			tag += symbol;
			if (symbol == '>') {
				let tagName = tag.match(/^<[\/\s]*?([^\/\s>]+)/)[1];
				if (tagName == 'img') {
					if (handleImages) {
						if (text.length > 0) {
							lines.push(image + text);
							image = '';
							text = '';
						}
						image += tag;
					}
				} else if (tagName == 'br') {
					lines.push(image + text);
					image = '';
					text = '';
				} else if (tagName == 'p' || tagName == 'div') {
					if (text.length > 0) {
						lines.push(image + text);
						image = '';
						text = '';
					}
				}
				mode = 0;
			}
		}
	}
	if (image.length > 0 || text.length > 0) {
		lines.push(image + text);
	}
	messageBody = lines.join('\n');

	if (handleImages) {
		var re = new RegExp('<img[^>]+?' + window.filesMiniatureUrl + '[^>]+?>', 'gi');
		messageBody = messageBody.replace(re, function(str) {
			var dom = $($.parseHTML(str));
			var src = dom.attr('src').replace(window.filesMiniatureUrl + '/', '');
			var id = dom.data('id');
			return '[attached-img id="' + id + '"]';
		});
	}
	
	messageBody = messageBody.replace(/<\/?[^>]*>/gi, '');
	messageBody = he.decode(messageBody);
	messageBody = messageBody.replace(/^[ \r\n\f\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
	return messageBody;
}